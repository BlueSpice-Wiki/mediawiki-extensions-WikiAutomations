<?php

namespace MediaWiki\Extension\WikiAutomations\Hook;

use ManualLogEntry;
use MediaWiki\Extension\WikiAutomations\AutomationStore;
use MediaWiki\Extension\WikiAutomations\Trigger\TimeTrigger;
use MediaWiki\Extension\WikiAutomations\Util\TriggerCronListManager;
use MediaWiki\Hook\PageMoveCompleteHook;
use MediaWiki\Page\Hook\PageDeleteCompleteHook;
use MediaWiki\Page\Hook\PageUndeleteCompleteHook;
use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use MediaWiki\Title\Title;

class UpdateTriggerCronList implements
	PageSaveCompleteHook,
	PageDeleteCompleteHook,
	PageMoveCompleteHook,
	PageUndeleteCompleteHook
{

	public function __construct(
		private readonly TriggerCronListManager $cronListManager,
		private readonly AutomationStore $automationStore
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onPageDeleteComplete(
		ProperPageIdentity $page, Authority $deleter, string $reason, int $pageID,
		RevisionRecord $deletedRev, ManualLogEntry $logEntry, int $archivedRevisionCount
	) {
		$this->cronListManager->deleteForAutomation( $page );
	}

	/**
	 * @inheritDoc
	 */
	public function onPageMoveComplete( $old, $new, $user, $pageid, $redirid, $reason, $revision ) {
		if ( $new->getContentModel() !== 'automation' ) {
			return;
		}
		$this->cronListManager->moveForAutomation( $pageid, $new->getArticleId() );
	}

	/**
	 * @inheritDoc
	 */
	public function onPageSaveComplete( $wikiPage, $user, $summary, $flags, $revisionRecord, $editResult ) {
		if ( $wikiPage->getTitle()->getContentModel() !== 'automation' ) {
			return;
		}
		$this->storeForPage( $wikiPage->getTitle() );
	}

	/**
	 * @inheritDoc
	 */
	public function onPageUndeleteComplete(
		ProperPageIdentity $page, Authority $restorer, string $reason, RevisionRecord $restoredRev,
		ManualLogEntry $logEntry, int $restoredRevisionCount, bool $created, array $restoredPageIds
	): void {
		$title = Title::newFromPageIdentity( $page );
		if ( $title->getContentModel() !== 'automation' ) {
			return;
		}
		$this->storeForPage( $title );
	}

	/**
	 * @param Title $title
	 * @return void
	 */
	private function storeForPage( Title $title ): void {
		$automation = $this->automationStore->getAutomationByPage( $title );
		if ( !$automation ) {
			return;
		}
		$triggers = $automation->getTriggers();
		$crons = [];
		foreach ( $triggers as $trigger ) {
			if ( $trigger instanceof TimeTrigger ) {
				$crons[] = $trigger->getTimeExpression();
			}
		}
		$this->cronListManager->storeForAutomation( $title, array_filter( $crons ) );
	}
}
