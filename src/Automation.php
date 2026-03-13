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
		return $this->triggers;
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
				return [
					'data' => $trigger->getData(),
					'enabled' => $trigger->isEnabled()
				];
			}, $this->getTriggers() ),
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
