<?php

namespace MediaWiki\Extension\WikiAutomations\Hook;

use BlueSpice\Discovery\Hook\BlueSpiceDiscoveryTemplateDataProviderAfterInit;
use MediaWiki\Config\Config;

class IntegrateDiscoverySkin implements BlueSpiceDiscoveryTemplateDataProviderAfterInit {

	/**
	 * @param Config $config
	 */
	public function __construct( private readonly Config $config ) {
	}

	/**
	 * @inheritDoc
	 */
	public function onBlueSpiceDiscoveryTemplateDataProviderAfterInit( $registry ): void {
		if ( !$this->config->get( 'WikiAutomationsEnableEditUI' ) ) {
			return;
		}
		$registry->register( 'panel/edit', 'ca-edit-automation-source' );
	}
}
