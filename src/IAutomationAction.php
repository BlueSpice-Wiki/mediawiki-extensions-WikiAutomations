<?php

namespace MediaWiki\Extension\WikiAutomations;

use MediaWiki\Permissions\Authority;
use MediaWiki\Status\Status;

interface IAutomationAction extends IAutomationEntity {

	/**
	 * @param Authority $actor
	 * @return void
	 */
	public function setTriggeredBy( Authority $actor ): void;

	/**
	 * @return Status
	 */
	public function execute(): Status;
}
