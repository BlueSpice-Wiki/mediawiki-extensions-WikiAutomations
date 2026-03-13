<?php

namespace MediaWiki\Extension\WikiAutomations;

use MediaWiki\Page\PageIdentity;

interface IPageFilter extends IAutomationEntity {

	/**
	 * @param PageIdentity $page
	 * @return bool
	 */
	public function pageFits( PageIdentity $page ): bool;
}
