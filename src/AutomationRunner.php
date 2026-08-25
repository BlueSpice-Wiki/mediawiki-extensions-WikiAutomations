<?php

namespace MediaWiki\Extension\WikiAutomations;

use DateTime;
use MediaWiki\Extension\WikiAutomations\Process\RunAutomations;
use MediaWiki\Extension\WikiAutomations\Trigger\PageEventTrigger;
use MediaWiki\Extension\WikiAutomations\Trigger\TimeTrigger;
use MediaWiki\Extension\WikiAutomations\Util\AutomationLogger;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\Status\Status;
use MWStake\MediaWiki\Component\ProcessManager\ManagedProcess;
use MWStake\MediaWiki\Component\ProcessManager\ProcessManager;
use Poliander\Cron\CronExpression;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

final class AutomationRunner implements LoggerAwareInterface {

	/** @var bool */
	private bool $forceSync = false;

	/** @var LoggerInterface */
	private readonly LoggerInterface $logger;

	/**
	 * @param AutomationStore $automationStore
	 * @param AutomationLogger $specialLogLogger
	 * @param ProcessManager $processManager
	 */
	public function __construct(
		private readonly AutomationStore $automationStore,
		private readonly AutomationLogger $specialLogLogger,
		private readonly ProcessManager $processManager
	) {
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 * @param string $triggerKey
	 * @param array $forPages
	 * @param Authority|null $triggeredBy
	 * @param array $triggerData
	 * @return string PID
	 */
	public function scheduleTrigger(
		string $triggerKey, array $forPages, ?Authority $triggeredBy = null, array $triggerData = []
	): string {
		$process = new ManagedProcess( [
			'trigger-automations' => [
				'class' => RunAutomations::class,
				'services' => [ 'WikiAutomations.Runner', 'UserFactory', 'TitleFactory' ]
			]
		] );
		return $this->processManager->startProcess( $process, [
			'triggerKey' => $triggerKey,
			'forPages' => array_map( fn ( PageIdentity $page ) => $page->getId(), $forPages ),
			'triggeredBy' => $triggeredBy?->getUser()->getName(),
			'triggerData' => $triggerData
		] );
	}

	/**
	 * @param string $triggerKey
	 * @param array $forPages
	 * @param Authority|null $triggeredBy
	 * @param array $limitToAutomations
	 * @param array $triggerData
	 * @return array
	 */
	public function trigger(
		string $triggerKey, array $forPages, ?Authority $triggeredBy = null,
		array $limitToAutomations = [], array $triggerData = []
	): array {
		$runStatuses = [];
		$this->logger->debug( 'Triggering automations for trigger key: ' . $triggerKey, [
			'forPages' => array_map( fn ( PageIdentity $page ) => $page->getId(), $forPages ),
			'triggeredBy' => $triggeredBy?->getUser()->getName(),
			'limitToAutomations' => $limitToAutomations
		] );
		foreach ( $this->automationStore->getAutomations( $limitToAutomations ) as $name => $automation ) {
			$status = Status::newGood();
			if ( !$automation->isEnabled() ) {
				$status->setOK( true );
				$status->warning( Message::newFromKey( 'wiki-automations-automation-disabled' ) );
				continue;
			}
			$pages = [];
			$triggers = $automation->getTriggers();
			$hasTriggers = false;
			foreach ( $triggers as $key => $trigger ) {
				if ( $triggerKey !== $key || !$trigger->isEnabled() ) {
					continue;
				}
				if ( $trigger instanceof TimeTrigger ) {
					if ( !$trigger->getTimeExpression() ) {
						continue;
					}
					if ( !$this->checkTimeTrigger( $trigger->getTimeExpression() ) ) {
						//continue;
					}
				}
				if ( $trigger instanceof PageEventTrigger && $forPages ) {
					$trigger->setPages( $forPages );
				}
				$pages = $trigger->providePages( $triggerData );
				if ( !( $trigger instanceof PageEventTrigger ) || !empty( $pages ) ) {
					// Do not trigger if no pages were provided on PageEventTriggers
					$hasTriggers = true;
				}
			}
			if ( !$hasTriggers ) {
				continue;
			}
			// Filter pages
			foreach ( $automation->getPageFilters() as $filter ) {
				if ( empty( $pages ) ) {
					// No pages to filter
					break;
				}
				$pages = array_filter( $pages, fn ( $page ) => $filter->pageFits( $page ) );
			}

			$actionValues = [];
			// Execute actions
			foreach ( $automation->getActions() as $action ) {
				if ( $triggeredBy ) {
					$action->setTriggeredBy( $triggeredBy );
				}
				if ( $triggerData ) {
					$action->setTriggerData( $triggerData );
				}
				if ( $action instanceof IPageScopedAutomationAction ) {
					if ( empty( $pages ) ) {
						$this->logger->warning(
							'Action ' . get_class( $action ) . ' requires pages, but no pages provided after filtering'
						);
						// Action requires a page, but none provided
						$status->setOK( false );
						$status->warning( Message::newFromKey( 'wiki-automations-action-no-pages' ) );
						continue;
					}
					foreach ( $pages as $page ) {
						$actionStatus = $action->executeForPage( $page );
						if ( $actionStatus->isOK() ) {
							$actionValues[] = $actionStatus->getValue();
						} else {
							$status = $status->merge( $actionStatus, true );
						}
					}
				} else {
					$actionStatus = $action->execute();
					if ( $actionStatus->isOK() ) {
						$actionValues[] = $actionStatus->getValue();
					} else {
						$status = $status->merge( $actionStatus, true );
					}
				}
				if ( $status->isOK() ) {
					$status = $status->merge( Status::newGood( $actionValues ), true );
				}
			}
			$this->specialLogLogger->logRun( $name, $status, $triggerKey, $pages );
			$this->logger->info( 'Finished running automation: ' . $name, [
				'status' => $status->isOK() ? 'success' : 'failure',
				'warnings' => $status->getMessages( 'warning' ),
				'errors' => $status->getMessages( 'error' ),
				'actionValues' => $actionValues
			] );
			$runStatuses[$name] = $status;
		}

		return $runStatuses;
	}

	/**
	 * @param string $timeExpression
	 * @return bool
	 */
	public function checkTimeTrigger( string $timeExpression ): bool {
		try {
			$date = new DateTime();
			$date->setTime( $date->format( 'H' ), $date->format( 'i' ), 0 );
			$exp = new CronExpression( $timeExpression );
			if ( $exp->isValid() && $exp->isMatching( $date ) ) {
				return true;
			}
			return false;
		} catch ( \Throwable $ex ) {
			$this->logger->error( 'Error while checking time trigger with expression: ' . $timeExpression, [
				'exception' => $ex
			] );
			return false;
		}
	}

}
