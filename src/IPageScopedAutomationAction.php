<?php

namespace MediaWiki\Extension\WikiAutomations;

use MediaWiki\Page\PageIdentity;
use MediaWiki\Status\Status;

interface IPageScopedAutomationAction extends IAutomationAction {

	/**
	 * @param PageIdentity $page
	 * @return Status
	 */
	public function executeForPage( PageIdentity $page ): Status;
}
