<?php

namespace MediaWiki\Extension\WikiAutomations\Data\Overview;

use MediaWiki\Extension\WikiAutomations\AutomationStore;

class Reader extends \MWStake\MediaWiki\Component\DataStore\Reader {

	/**
	 * @param AutomationStore $automationStore
	 */
	public function __construct( private readonly AutomationStore $automationStore ) {
		parent::__construct();
	}

	/**
	 * @inheritDoc
	 */
	public function getSchema() {
		return new Schema();
	}

	/**
	 * @inheritDoc
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->automationStore );
	}

	/**
	 * @inheritDoc
	 */
	protected function makeSecondaryDataProvider() {
		return null;
	}
}
