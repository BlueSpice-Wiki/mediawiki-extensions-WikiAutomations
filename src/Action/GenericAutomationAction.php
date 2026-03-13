<?php

namespace MediaWiki\Extension\WikiAutomations\Action;

use MediaWiki\Extension\WikiAutomations\AutomationEntity;
use MediaWiki\Extension\WikiAutomations\IAutomationAction;
use MediaWiki\Permissions\Authority;

abstract class GenericAutomationAction extends AutomationEntity implements IAutomationAction {

	/** @var Authority|null */
	protected ?Authority $triggeredBy = null;

	/** @var array */
	protected array $triggerData = [];

	public function setTriggeredBy( Authority $actor ): void {
		$this->triggeredBy = $actor;
	}

	/**
	 * @param array $triggerData
	 * @return void
	 */
	public function setTriggerData( array $triggerData ): void {
		$this->triggerData = $triggerData;
	}
}
