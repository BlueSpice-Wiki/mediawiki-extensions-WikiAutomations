<?php

namespace MediaWiki\Extension\WikiAutomations\Process;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\WikiAutomations\AutomationRunner;
use MediaWiki\Extension\WikiAutomations\EntityFactory;
use MediaWiki\Extension\WikiAutomations\Util\TriggerCronListManager;
use MediaWiki\User\User;
use MWStake\MediaWiki\Component\ProcessManager\IProcessStep;
use Psr\Log\LoggerInterface;

class TriggerTimeBasedTriggers implements IProcessStep {

	/**
	 * @param AutomationRunner $automationRunner
	 * @param EntityFactory $entityFactory
	 * @param TriggerCronListManager $cronListManager
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		private readonly AutomationRunner $automationRunner,
		private readonly EntityFactory $entityFactory,
		private readonly TriggerCronListManager $cronListManager,
		private readonly LoggerInterface $logger
	) {
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function execute( $data = [] ): array {
		$pages = [];
		// If we decide to run cron every minute to support any possible expression, this will be important
		// This looks into DB for all registered cron expressions and checks which are due
		/*$crons = $this->cronListManager->getAll();

		foreach ( $crons as $automationPageId => $cronExpressions ) {
			foreach ( $cronExpressions as $cronExpression ) {
				if ( $this->automationRunner->checkTimeTrigger( $cronExpression ) ) {
					$pages[$automationPageId] = true;
				}
			}
		}
		if ( empty( $pages ) ) {
			// No due crons
			return [];
		}
		$this->logger->info( 'Found ' . count( $pages ) . ' due time-based triggers to run', [
			'pages' => array_keys( $pages )
		] );
		*/

		RequestContext::getMain()->setAuthority( User::newSystemUser( 'MediaWiki default', [ 'steal' => true ] ) );

		$this->logger->debug( 'Running cron to trigger time-based automations' );
		$triggers = $this->entityFactory->getTriggerKeysOfType( 'time' );
		$res = [];
		foreach ( $triggers as $triggerKey ) {
			$res[$triggerKey] = $this->automationRunner->trigger( $triggerKey, [], null, array_keys( $pages ) );
		}

		if ( $res ) {
			$this->logger->info( 'Due triggers found and executed: {results}', [
				'results' => json_encode( array_keys( $res ) ),
			] );
		} else {
			$this->logger->debug( 'No due triggers found' );
		}
		return $res;
	}
}
