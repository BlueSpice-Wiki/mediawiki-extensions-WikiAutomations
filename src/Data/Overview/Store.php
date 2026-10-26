<?php

namespace MediaWiki\Extension\WikiAutomations\Data\Overview;

use MediaWiki\Extension\WikiAutomations\AutomationStore;
use MWStake\MediaWiki\Component\DataStore\IStore;

class Store implements IStore {

	/**
	 * @param AutomationStore $automationStore
	 */
	public function __construct( private readonly AutomationStore $automationStore ) {
	}

	/**
	 * @inheritDoc
	 */
	public function getWriter() {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getReader() {
		return new Reader( $this->automationStore );
	}
}
