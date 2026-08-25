<?php

namespace MediaWiki\Extension\WikiAutomations;

use JsonSerializable;

final class Automation implements JsonSerializable {

	public function __construct(
		private readonly array $triggers,
		private readonly array $pageFilters,
		private readonly array $actions,
		private bool $isEnabled = true
	) {
	}

	/**
	 * @return IAutomationTrigger[]
	 */
	public function getTriggers(): array {
		$triggers = [];
		foreach ( $this->triggers as $triggerDefinition ) {
			if ( $triggerDefinition instanceof IAutomationTrigger ) {
				$triggers[] = $triggerDefinition;
				continue;
			}
			if ( is_array( $triggerDefinition ) && isset( $triggerDefinition['trigger'] ) ) {
				$triggers[] = $triggerDefinition['trigger'];
			}
		}
		return $triggers;
	}

	/**
	 * @return array<int, array{key:string, trigger:IAutomationTrigger}>
	 */
	public function getTriggersWithKeys(): array {
		$triggers = [];
		foreach ( $this->triggers as $index => $triggerDefinition ) {
			if ( $triggerDefinition instanceof IAutomationTrigger ) {
				$triggers[] = [ 'key' => (string)$index, 'trigger' => $triggerDefinition ];
				continue;
			}
			if ( !is_array( $triggerDefinition ) ) {
				continue;
			}
			if ( !isset( $triggerDefinition['key'] ) || !isset( $triggerDefinition['trigger'] ) ) {
				continue;
			}
			$triggers[] = [
				'key' => (string)$triggerDefinition['key'],
				'trigger' => $triggerDefinition['trigger']
			];
		}
		return $triggers;
	}

	/**
	 * @return IPageFilter[]
	 */
	public function getPageFilters(): array {
		return $this->pageFilters;
	}

	/**
	 * @return IAutomationAction[]
	 */
	public function getActions(): array {
		return $this->actions;
	}

	/**
	 * @param bool $enabled
	 * @return void
	 */
	public function setEnabled( bool $enabled ): void {
		$this->isEnabled = $enabled;
	}

	/**
	 * @return bool
	 */
	public function isEnabled(): bool {
		return $this->isEnabled;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize() {
		return [
			'triggers' => array_map( static function ( $trigger ) {
				$data = $trigger['trigger']->getData();
				return [
					'key' => $trigger['key'],
					'data' => $data,
					'enabled' => $trigger['trigger']->isEnabled()
				];
			}, $this->getTriggersWithKeys() ),
			'pageFilters' => array_map( static function ( $filter ) {
				return [
					'data' => $filter->getData(),
					'enabled' => $filter->isEnabled()
				];
			}, $this->getPageFilters() ),
			'actions' => array_map( static function ( $action ) {
				$data = $action->getData();
				$key = $data['key'] ?? null;
				unset( $data['key'] );
				return [
					'key' => $key,
					'data' => $action->getData(),
					'enabled' => $action->isEnabled()
				];
			}, $this->getActions() ),
			'enabled' => $this->isEnabled(),
		];
	}
}
