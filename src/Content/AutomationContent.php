<?php

namespace MediaWiki\Extension\WikiAutomations\Content;

use MediaWiki\Content\JsonContent;

class AutomationContent extends JsonContent {

	public function __construct( string $automationData ) {
		parent::__construct( $automationData, 'automation' );
	}

}
