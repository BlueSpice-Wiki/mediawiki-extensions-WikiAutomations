<?php

namespace MediaWiki\Extension\WikiAutomations\ContentAction;

use EditAction;
use MediaWiki\Extension\WikiAutomations\Content\AutomationContent;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

class EditAutomationContent extends EditAction {

	/**
	 * @return void
	 * @throws \PermissionsError
	 * @throws \ReadOnlyError
	 * @throws \UserBlockedError
	 */
	public function show() {
		$this->useTransactionalTimeLimit();
		$this->checkCanExecute( $this->getUser() );
		$action = $this->getTitle()->exists() ? 'edit' : 'create';

		$out = $this->getOutput();
		$out->setRobotPolicy( 'noindex,nofollow' );
		$out->disableClientCache();

		$this->getOutput()->setPageTitle(
			// wiki-automations-content-action-edit
			// wiki-automations-content-action-create
			$this->getContext()->msg( 'wiki-automations-content-action-' . $action )
		);

		$containerData = [
			'class' => 'wiki-automation-page',
			'data-action' => $action,
			'data-automation-id' => $this->getTitle()->getPrefixedText(),
		];
		if ( $action === 'edit' ) {
			$containerData = array_merge( $containerData, $this->getAutomationData() );

		}

		$this->getOutput()->addHTML(
			\Html::element( 'div', $containerData )
		);
		$this->getOutput()->addModules( [ 'ext.wikiAutomations.automationPage' ] );
	}

	/**
	 * @return array
	 */
	private function getAutomationData(): array {
		$rev = $this->getArticle()->getPage()->getRevisionRecord();
		if ( !$rev ) {
			return [];
		}
		$content = $rev->getContent( SlotRecord::MAIN );
		if ( !( $content instanceof AutomationContent ) ) {
			return [];
		}

		return MediaWikiServices::getInstance()->getService( 'WikiAutomations._EditorDataProvider' )
			->provideDataForContent( $content );
	}

	/**
	 * @return string
	 */
	public function getRestriction() {
		return 'wikiadmin';
	}
}
