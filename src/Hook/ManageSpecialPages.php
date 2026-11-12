<?php

namespace MediaWiki\Extension\WikiAutomations\Hook;

use MediaWiki\Config\Config;
use MediaWiki\SpecialPage\Hook\SpecialPage_initListHook;

class ManageSpecialPages implements SpecialPage_initListHook {

	/**
	 * @param Config $config
	 */
	public function __construct( private readonly Config $config ) {
	}

	/**
	 * @inheritDoc
	 */
	public function onSpecialPage_initList( &$list ) {
		if ( $this->config->get( 'WikiAutomationsEnableEditUI' ) ) {
			return;
		}
		unset( $list['Automations'] );
	}
}
