<?php

namespace MediaWiki\Extension\WikiAutomations\Hook;

use MediaWiki\Extension\WikiAutomations\Process\TriggerTimeBasedTriggers;
use MediaWiki\Hook\MediaWikiServicesHook;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\ProcessManager\ManagedProcess;
use MWStake\MediaWiki\Component\WikiCron\WikiCronManager;

class RegisterCron implements MediaWikiServicesHook {

	/**
	 * @param MediaWikiServices $services
	 * @return void
	 */
	public function onMediaWikiServices( $services ) {
		if ( defined( 'MW_PHPUNIT_TEST' ) || defined( 'MW_QUIBBLE_CI' ) ) {
			return;
		}
		/** @var WikiCronManager $cronManager */
		$cronManager = $services->getService( 'MWStake.WikiCronManager' );

		// Run cron once an hour
		$cronManager->registerCron( 'wiki-automations-run-time-triggers', '0 * * * *', new ManagedProcess( [
			'trigger-time' => [
				'class' => TriggerTimeBasedTriggers::class,
				'services' => [
					'WikiAutomations.Runner', 'WikiAutomations.EntityFactory',
					'WikiAutomations.Util.CronListManager', 'WikiAutomations._Logger'
				],
			]
		] ) );
	}
}
