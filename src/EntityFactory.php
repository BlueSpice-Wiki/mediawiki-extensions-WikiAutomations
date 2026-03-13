<?php

namespace MediaWiki\Extension\WikiAutomations;

use MediaWiki\Extension\WikiAutomations\Exception\EntityNotFoundException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Wikimedia\ObjectFactory\ObjectFactory;

final class EntityFactory implements LoggerAwareInterface {

	/** @var LoggerInterface */
	private LoggerInterface $logger;

	/**
	 * @param array $triggerTypes
	 * @param array $triggerRegistry
	 * @param array $pageFilterRegistry
	 * @param array $actionRegistry
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct(
		private readonly array $triggerTypes,
		private readonly array $triggerRegistry,
		private readonly array $pageFilterRegistry,
		private readonly array $actionRegistry,
		private readonly ObjectFactory $objectFactory
	) {
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 * @return array
	 */
	public function getTriggerTypes(): array {
		return $this->triggerTypes;
	}

	/**
	 * @return array
	 * @throws EntityNotFoundException
	 */
	public function listTriggers(): array {
		$triggers = [];
		foreach ( $this->triggerRegistry as $triggerKey => $triggerData ) {
			if ( !isset( $triggerData['type'] ) || !isset( $this->triggerTypes[$triggerData['type']] ) ) {
				throw new \UnexpectedValueException( "Trigger $triggerKey has invalid or missing type" );
			}
			$triggers[$triggerKey] = [
				'message' => $triggerData['message'],
				'type' => $triggerData['type'],
				'layout' => $this->createTrigger( $triggerKey )?->getLayout()?->getSerialized(),
			];
		}
		return $triggers;
	}

	/**
	 * @return array
	 * @throws EntityNotFoundException
	 */
	public function listPageFilters(): array {
		$filters = [];
		foreach ( $this->pageFilterRegistry as $filterKey => $filterData ) {
			$filters[$filterKey] = [
				'message' => $filterData['message'],
				'layout' => $this->createPageFilter( $filterKey )?->getLayout()?->getSerialized(),
			];
		}
		return $filters;
	}

	/**
	 * @return array
	 * @throws EntityNotFoundException
	 */
	public function listActions(): array {
		$actions = [];
		foreach ( $this->actionRegistry as $key => $data ) {
			$actions[$key] = [
				'message' => $data['message'],
				'type' => $data['type'],
				'layout' => $this->createAction( $key )?->getLayout()?->getSerialized(),
			];
		}
		return $actions;
	}

	/**
	 * @param array $data
	 * @return Automation
	 */
	public function automationFromData( array $data ): Automation {
		$triggers = [];
		foreach ( $data['triggers'] ?? [] as $triggerKey => $triggerData ) {
			try {
				$trigger = $this->createTrigger( $triggerKey );
				$trigger->setData( $triggerData['data'] ?? [] );
				$trigger->setEnabled( $triggerData['enabled'] ?? true );
				$triggers[$triggerKey] = $trigger;
			} catch ( EntityNotFoundException $e ) {
				$this->logger->warning( "Automation uses trigger that does not exist", [ 'exception' => $e ] );
				continue;
			}
		}

		$pageFilters = [];
		foreach ( $data['pageFilters'] ?? [] as $filterKey => $filterData ) {
			try {
				$filter = $this->createPageFilter( $filterKey );
				$filter->setData( $filterData['data'] ?? [] );
				$filter->setEnabled( $filterData['enabled'] ?? true );
				$pageFilters[$filterKey] = $filter;
			} catch ( EntityNotFoundException $e ) {
				$this->logger->warning( "Automation uses page filter that does not exist", [ 'exception' => $e ] );
				continue;
			}
		}

		$actions = [];
		foreach ( $data['actions'] ?? [] as $actionDefinition ) {
			try {
				$actionData = $actionDefinition['data'] ?? [];
				$actionData['key'] = $actionDefinition['key'];
				$enabled = $actionDefinition['enabled'] ?? true;
				$action = $this->createAction( $actionDefinition['key'] );
				$action->setData( $actionData );
				$action->setEnabled( $enabled ?? true );
				$actions[] = $action;
			} catch ( EntityNotFoundException $e ) {
				$this->logger->warning( "Automation uses action that does not exist", [ 'exception' => $e ] );
				continue;
			}
		}

		return new Automation( $triggers, $pageFilters, $actions, $data['enabled'] ?? true );
	}

	/**
	 * @param string $type
	 * @return array
	 */
	public function getTriggerKeysOfType( string $type ): array {
		$keys = [];
		foreach ( $this->triggerRegistry as $triggerKey => $triggerData ) {
			if ( isset( $triggerData['type'] ) && $triggerData['type'] === $type ) {
				$keys[] = $triggerKey;
			}
		}
		return $keys;
	}

	/**
	 * @param string $triggerKey
	 * @return IAutomationTrigger
	 * @throws EntityNotFoundException
	 */
	public function createTrigger( string $triggerKey ): IAutomationTrigger {
		if ( !isset( $this->triggerRegistry[$triggerKey] ) ) {
			throw new EntityNotFoundException( 'trigger', $triggerKey );
		}
		if ( !isset( $this->triggerRegistry[$triggerKey]['spec'] ) ) {
			throw new \UnexpectedValueException( "Trigger $triggerKey is missing spec" );
		}
		$instance = $this->objectFactory->createObject( $this->triggerRegistry[$triggerKey]['spec'] );
		if ( !( $instance instanceof IAutomationTrigger ) ) {
			throw new \UnexpectedValueException( "Trigger $triggerKey does not implement IAutomationTrigger" );
		}
		return $instance;
	}

	/**
	 * @param string $key
	 * @return IPageFilter
	 * @throws EntityNotFoundException
	 */
	public function createPageFilter( string $key ): IPageFilter {
		if ( !isset( $this->pageFilterRegistry[$key] ) ) {
			throw new EntityNotFoundException( 'page_filter', $key );
		}
		$instance = $this->objectFactory->createObject( $this->pageFilterRegistry[$key]['spec'] );
		if ( !( $instance instanceof IPageFilter ) ) {
			throw new \UnexpectedValueException( "Page filter $key does not implement IPageFilter" );
		}
		return $instance;
	}

	/**
	 * @param string $key
	 * @return IAutomationAction
	 * @throws EntityNotFoundException
	 */
	public function createAction( string $key ): IAutomationAction {
		if ( !isset( $this->actionRegistry[$key] ) ) {
			throw new EntityNotFoundException( 'action', $key );
		}
		if ( !isset( $this->actionRegistry[$key]['spec'] ) ) {
			throw new \UnexpectedValueException( "Action $key is missing spec" );
		}
		$instance = $this->objectFactory->createObject( $this->actionRegistry[$key]['spec'] );
		if ( !( $instance instanceof IAutomationAction ) ) {
			throw new \UnexpectedValueException( "Action $key does not implement IAutomationAction" );
		}
		return $instance;
	}
}
