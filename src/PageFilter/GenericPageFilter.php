<?php

namespace MediaWiki\Extension\WikiAutomations\PageFilter;

use MediaWiki\Extension\WikiAutomations\AutomationEntity;
use MediaWiki\Extension\WikiAutomations\IPageFilter;
use MediaWiki\Page\PageIdentity;

abstract class GenericPageFilter extends AutomationEntity implements IPageFilter {

	public function pageFits( PageIdentity $page ): bool {
		return true;
	}
}
