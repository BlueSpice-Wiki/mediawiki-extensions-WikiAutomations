<?php

namespace MediaWiki\Extension\WikiAutomations\Trigger;

use MediaWiki\Extension\WikiAutomations\AutomationEntity;
use MediaWiki\Extension\WikiAutomations\IAutomationTrigger;
use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;

abstract class GenericTrigger extends AutomationEntity implements IAutomationTrigger {
	/**
	 * @inheritDoc
	 */
	public function getLayout(): ?IFormSpecification {
		return null;
	}
}
