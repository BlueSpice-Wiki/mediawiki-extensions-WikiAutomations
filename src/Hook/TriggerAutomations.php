<?php

namespace MediaWiki\Extension\WikiAutomations\Hook;

use ManualLogEntry;
use MediaWiki\Extension\WikiAutomations\AutomationRunner;
use MediaWiki\Page\Hook\PageDeleteCompleteHook;
use MediaWiki\Page\Hook\PageUndeleteCompleteHook;
use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;

class TriggerAutomations implements
	PageSaveCompleteHook,
	PageDeleteCompleteHook,
	PageUndeleteCompleteHook
{

	public function __construct(
		private readonly AutomationRunner $automationRunner
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onPageDeleteComplete(
		ProperPageIdentity $page, Authority $deleter, string $reason, int $pageID, RevisionRecord $deletedRev,
		ManualLogEntry $logEntry, int $archivedRevisionCount
	) {
		$this->automationRunner->scheduleTrigger( 'delete', [ $page ], $deleter );
	}

	/**
	 * @inheritDoc
	 */
	public function onPageSaveComplete( $wikiPage, $user, $summary, $flags, $revisionRecord, $editResult ) {
		if ( $editResult->isNew() ) {
			$this->automationRunner->scheduleTrigger( 'create', [ $wikiPage->getTitle() ], $user );
			return;
		}
		$this->automationRunner->scheduleTrigger( 'edit', [ $wikiPage->getTitle() ], $user );
	}

	/**
	 * @inheritDoc
	 */
	public function onPageUndeleteComplete(
		ProperPageIdentity $page, Authority $restorer, string $reason, RevisionRecord $restoredRev,
		ManualLogEntry $logEntry, int $restoredRevisionCount, bool $created, array $restoredPageIds
	): void {
		$this->automationRunner->scheduleTrigger( 'create', [ $page ], $restorer );
	}
}
