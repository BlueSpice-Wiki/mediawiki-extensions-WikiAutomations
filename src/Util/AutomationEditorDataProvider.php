<?php

namespace MediaWiki\Extension\WikiAutomations\Util;

use MediaWiki\Extension\WikiAutomations\Content\AutomationContent;
use MediaWiki\Extension\WikiAutomations\EntityFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;

readonly class AutomationEditorDataProvider {

	/**
	 * @param EntityFactory $entityFactory
	 */
	public function __construct(
		private EntityFactory $entityFactory
	) {
	}

	/**
	 * @param AutomationContent $content
	 * @return array
	 * @throws \MediaWiki\Extension\WikiAutomations\Exception\EntityNotFoundException
	 */
	public function provideDataForContent( AutomationContent $content ): array {
		$data = json_decode( $content->getText(), true );
		/** @var EntityFactory $factory */
		$factory = MediaWikiServices::getInstance()->getService( 'WikiAutomations.EntityFactory' );
		$automation = $factory->automationFromData( $data );

		$automationData = [
			'triggers' => array_map( static function ( $trigger ) {
				return [
					'data' => $trigger->getData(),
					'displayData' => $trigger->getDisplayData(),
					'enabled' => $trigger->isEnabled()
				];
			}, $automation->getTriggers() ),
			'pageFilters' => array_map( static function ( $filter ) {
				return [
					'data' => $filter->getData(),
					'displayData' => $filter->getDisplayData(),
					'enabled' => $filter->isEnabled()
				];
			}, $automation->getPageFilters() ),
			'actions' => array_map( static function ( $action ) {
				$data = $action->getData();
				$key = $data['key'] ?? null;
				unset( $data['key'] );
				return [
					'key' => $key,
					'data' => $action->getData(),
					'displayData' => $action->getDisplayData(),
					'enabled' => $action->isEnabled()
				];
			}, $automation->getActions() ),
			'enabled' => $automation->isEnabled(),
		];
		$result = [
			'data-automation-data' => json_encode( $automationData ),
			'data-enabled' => $automation->isEnabled(),
		];

		$entityInfo = [
			'triggers' => [],
			'filters' => [],
			'actions' => [],
		];
		$triggers = $this->entityFactory->listTriggers();
		foreach ( $triggers as $triggerKey => $data ) {
			$entityInfo['triggers'][$triggerKey] = $this->getDataFromItem( $data );
		}
		$pageFilters = $this->entityFactory->listPageFilters();
		foreach ( $pageFilters as $filterKey => $data ) {
			$entityInfo['filters'][$filterKey] = $this->getDataFromItem( $data );
		}

		$actions = $this->entityFactory->listActions();
		foreach ( $actions as $actionKey => $data ) {
			$entityInfo['actions'][$actionKey] = $this->getDataFromItem( $data );
		}

		$result['data-entity-info'] = json_encode( $entityInfo );

		return $result;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private function getDataFromItem( array $data ): array {
		return [
			'labels' => [
				'message' => Message::newFromKey( $data['message'] )->text(),
				'description' => isset( $data['description'] ) ?
					Message::newFromKey( $data['description'] )->text() : '',
			],
			'layout' => $data['layout'] ?? null,
		];
	}
}
