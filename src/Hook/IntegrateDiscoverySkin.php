<?php

namespace MediaWiki\Extension\WikiAutomations\Hook;

use BlueSpice\Discovery\Hook\BlueSpiceDiscoveryTemplateDataProviderAfterInit;

class IntegrateDiscoverySkin implements BlueSpiceDiscoveryTemplateDataProviderAfterInit {

	/**
	 * @inheritDoc
	 */
	public function onBlueSpiceDiscoveryTemplateDataProviderAfterInit( $registry ): void {
		$registry->register( 'panel/edit', 'ca-edit-automation-source' );
	}
}
