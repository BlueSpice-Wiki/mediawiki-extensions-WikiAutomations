<?php

namespace MediaWiki\Extension\WikiAutomations\Hook;

use MediaWiki\Revision\Hook\ContentHandlerDefaultModelForHook;

class SetAutomationContentModel implements ContentHandlerDefaultModelForHook {

	/**
	 * @inheritDoc
	 */
	public function onContentHandlerDefaultModelFor( $title, &$model ) {
		if ( preg_match( '/\.automation$/', $title->getText() ) && !$title->isTalkPage() ) {
			$model = 'automation';
			return false;
		}
		return true;
	}
}
