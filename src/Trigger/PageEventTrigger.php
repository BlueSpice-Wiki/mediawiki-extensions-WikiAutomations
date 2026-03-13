<?php

namespace MediaWiki\Extension\WikiAutomations\Trigger;

use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;

class PageEventTrigger extends GenericTrigger {

	/** @var array */
	protected array $pages = [];

	/**
	 * @inheritDoc
	 */
	public function getLayout(): ?IFormSpecification {
		return null;
	}

	/**
	 * @param array $pages
	 * @return void
	 */
	public function setPages( array $pages ) {
		$this->pages = $pages;
	}

	/**
	 * @param array $triggerData
	 * @return array|\MediaWiki\Page\PageIdentity[]
	 */
	public function providePages( array $triggerData = [] ): array {
		return $this->pages;
	}
}
