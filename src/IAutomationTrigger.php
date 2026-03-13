<?php

namespace MediaWiki\Extension\WikiAutomations;

use MediaWiki\Page\PageIdentity;
use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;

interface IAutomationTrigger extends IAutomationEntity {

	/**
	 * @return IFormSpecification|null
	 */
	public function getLayout(): ?IFormSpecification;

	/**
	 * @return PageIdentity[]
	 */
	public function providePages( array $triggerData = [] ): array;
}
