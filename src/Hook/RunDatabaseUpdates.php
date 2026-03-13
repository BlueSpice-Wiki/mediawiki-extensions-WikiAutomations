<?php

namespace MediaWiki\Extension\WikiAutomations\Hook;

use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class RunDatabaseUpdates implements LoadExtensionSchemaUpdatesHook {

	/**
	 * @inheritDoc
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$base = __DIR__ . '/../../db';
		$updater->addExtensionTable(
			'wiki_automations_cron',
			$base . '/' . $updater->getDB()->getType() . '/wiki_automations_cron.sql'
		);
	}
}
