<?php

namespace MediaWiki\Extension\WikiAutomations\Util;

use MediaWiki\Page\PageIdentity;
use MediaWiki\Title\Title;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Wikimedia\Rdbms\ILoadBalancer;

class TriggerCronListManager implements LoggerAwareInterface {

	/** @var LoggerInterface */
	private LoggerInterface $logger;

	private const TABLE = 'wiki_automations_cron';

	/**
	 * @param ILoadBalancer $loadBalancer
	 */
	public function __construct(
		private readonly ILoadBalancer $loadBalancer
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
	 * @return array
	 */
	public function getAll(): array {
		$res = $this->loadBalancer->getConnection( DB_REPLICA )->newSelectQueryBuilder()
			->select( [ 'wac_automation_page', 'wac_cron_expression' ] )
			->from( static::TABLE )
			->caller( __METHOD__ )
			->fetchResultSet();

		$final = [];
		foreach ( $res as $row ) {
			if ( !isset( $final[$row->wac_automation_page] ) ) {
				$final[$row->wac_automation_page] = [];
			}
			$final[$row->wac_automation_page][] = $row->wac_cron_expression;
		}
		return $final;
	}

	/**
	 * @param Title $automationPage
	 * @param array $crons
	 * @return void
	 */
	public function storeForAutomation( PageIdentity $automationPage, array $crons ) {
		$this->deleteForAutomation( $automationPage );
		$rows = [];
		foreach ( $crons as $cron ) {
			$rows[] = [
				'wac_automation_page' => $automationPage->getId(),
				'wac_cron_expression' => $cron
			];
		}
		if ( $rows ) {
			$this->loadBalancer->getConnection( DB_PRIMARY )->newInsertQueryBuilder()
				->insert( static::TABLE )
				->rows( $rows )
				->caller( __METHOD__ )
				->execute();
			$this->logger->debug( 'Stored cron entries for automation', [
				'automationPage' => $automationPage->getPrefixedText(),
				'crons' => $crons,
			] );
		}
	}

	/**
	 * @param Title $automationPage
	 * @return void
	 */
	public function deleteForAutomation( PageIdentity $automationPage ) {
		$this->logger->debug( 'Deleting cron entries for page', [
			'automationPage' => $automationPage->getNamespace() . '|' . $automationPage->getDBkey(),
		] );
		$this->loadBalancer->getConnection( DB_PRIMARY )->newDeleteQueryBuilder()
			->deleteFrom( static::TABLE )
			->where( [ 'wac_automation_page' => $automationPage->getId() ] )
			->caller( __METHOD__ )
			->execute();
	}

	/**
	 * @param int $oldId
	 * @param int $newId
	 * @return void
	 */
	public function moveForAutomation( int $oldId, int $newId ) {
		$this->loadBalancer->getConnection( DB_PRIMARY )->newUpdateQueryBuilder()
			->update( static::TABLE )
			->set( [ 'wac_automation_page' => $newId ] )
			->where( [ 'wac_automation_page' => $oldId ] )
			->caller( __METHOD__ )
			->execute();
	}
}
