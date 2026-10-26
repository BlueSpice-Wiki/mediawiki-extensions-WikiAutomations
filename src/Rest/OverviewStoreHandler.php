<?php

namespace MediaWiki\Extension\WikiAutomations\Rest;

use MediaWiki\Extension\WikiAutomations\AutomationStore;
use MediaWiki\Extension\WikiAutomations\Data\Overview\Store;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Rest\HttpException;
use MWStake\MediaWiki\Component\CommonWebAPIs\Rest\QueryStore;
use MWStake\MediaWiki\Component\DataStore\IStore;

class OverviewStoreHandler extends QueryStore {

	/**
	 * @param HookContainer $hookContainer
	 * @param AutomationStore $automationStore
	 */
	public function __construct(
		HookContainer $hookContainer,
		private readonly AutomationStore $automationStore
	) {
		parent::__construct( $hookContainer );
	}

	/**
	 * @return mixed
	 * @throws HttpException
	 */
	public function execute() {
		if ( !$this->getAuthority()->isAllowed( 'edit-wiki-automations' ) ) {
			throw new HttpException( 'Permission denied', 403 );
		}
		return parent::execute();
	}

	/**
	 * @inheritDoc
	 */
	protected function getStore(): IStore {
		return new Store( $this->automationStore );
	}
}
