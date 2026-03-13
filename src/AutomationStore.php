<?php

namespace MediaWiki\Extension\WikiAutomations;

use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Extension\WikiAutomations\Content\AutomationContent;
use MediaWiki\Message\Message;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use Wikimedia\Rdbms\ILoadBalancer;

final class AutomationStore {
	public function __construct(
		private readonly EntityFactory $entityFactory,
		private readonly RevisionLookup $revisionLookup,
		private readonly ILoadBalancer $lb,
		private readonly WikiPageFactory $wikiPageFactory,
		private readonly TitleFactory $titleFactory
	) {
	}

	/**
	 * @param Title $title
	 * @param Automation $automation
	 * @param Authority $actor
	 * @return RevisionRecord
	 */
	public function saveAutomation( Title $title, Automation $automation, Authority $actor ): RevisionRecord {
		$wikiPage = $this->wikiPageFactory->newFromTitle( $title );
		$this->validateTargetTitle( $title );
		$content = new AutomationContent( json_encode( $automation ) );
		$updater = $wikiPage->newPageUpdater( $actor );
		$updater->setContent( SlotRecord::MAIN, $content );
		$revision = $updater->saveRevision( CommentStoreComment::newUnsavedComment( '' ) );
		if ( $revision ) {
			return $revision;
		}
		$messages = [];
		foreach ( $updater->getStatus()->getMessages() as $message ) {
			$messages[] = Message::newFromSpecifier( $message )->text();
		}
		throw new \RuntimeException( 'Failed to save automation: ' . implode( '; ', $messages ) );
	}

	/**
	 * @param Title $page
	 * @return Automation|null
	 */
	public function getAutomationByPage( Title $page ): ?Automation {
		$data = $this->getAutomationDataFromPage( $page );
		if ( !is_array( $data ) ) {
			return null;
		}
		try {
			return $this->entityFactory->automationFromData( $data );
		} catch ( \Throwable $ex ) {
			return null;
		}
	}

	/**
	 * @return \Generator<string, Automation>
	 */
	public function getAutomations( array $automationPageIds = [] ): iterable {
		$automationPages = $this->getAutomationPages( $automationPageIds );

		foreach ( $automationPages as $page ) {
			$data = $this->getAutomationDataFromPage( $page );
			if ( !is_array( $data ) ) {
				continue;
			}
			try {
				yield $page->getPrefixedText() => $this->entityFactory->automationFromData( $data );
			} catch ( \Throwable $ex ) {
				// Log and skip invalid automations
				continue;
			}
		}
	}

	/**
	 * @param Title $page
	 * @return array|null
	 */
	private function getAutomationDataFromPage( Title $page ): ?array {
		$revisionRecord = $this->revisionLookup->getRevisionByTitle( $page );
		if ( !$revisionRecord ) {
			return null;
		}
		$content = $revisionRecord->getSlot( SlotRecord::MAIN )->getContent();
		if ( !$content instanceof AutomationContent ) {
			return null;
		}
		$json = json_decode( $content->getText(), true );
		if ( is_array( $json ) ) {
			return $json;
		}
		return null;
	}

	/**
	 * @param array $automationPageIds
	 * @return array
	 */
	private function getAutomationPages( array $automationPageIds = [] ): array {
		$query = $this->lb->getConnection( DB_REPLICA )->newSelectQueryBuilder()
			->select( [ 'page_id', 'page_namespace', 'page_title' ] )
			->from( 'page' )
			->where( [ 'page_content_model' => 'automation', 'page_is_redirect' => 0 ] )
			->caller( __METHOD__ );

		if ( !empty( $automationPageIds ) ) {
			$query->where( [ 'page_id' => $automationPageIds ] );
		}
		$res = $query->fetchResultSet();

		$automationPages = [];
		foreach ( $res as $row ) {
			$automationPages[] = $this->titleFactory->newFromRow( $row );
		}

		return $automationPages;
	}

	/**
	 * @param Title $title
	 * @return void
	 */
	private function validateTargetTitle( Title $title ): void {
		if ( $title->exists() && $title->getContentModel() !== 'automation' ) {
			throw new \InvalidArgumentException( 'Title exists and is not an automation' );
		}
	}
}
