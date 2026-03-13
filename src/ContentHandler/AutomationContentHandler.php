<?php

namespace MediaWiki\Extension\WikiAutomations\ContentHandler;

use Html;
use LogEventsList;
use MediaWiki\Content\Content;
use MediaWiki\Content\JsonContentHandler;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Extension\WikiAutomations\Content\AutomationContent;
use MediaWiki\Extension\WikiAutomations\ContentAction\EditAutomationContent;
use MediaWiki\Extension\WikiAutomations\ContentAction\EditAutomationSource;
use MediaWiki\Extension\WikiAutomations\Exception\EntityNotFoundException;
use MediaWiki\Extension\WikiAutomations\Util\AutomationEditorDataProvider;
use MediaWiki\Language\RawMessage;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Title\Title;
use OOUI\MessageWidget;

class AutomationContentHandler extends JsonContentHandler {

	public function __construct() {
		parent::__construct( 'automation' );
	}

	/**
	 * @return string
	 */
	protected function getContentClass() {
		return AutomationContent::class;
	}

	/**
	 * @return false
	 */
	public function supportsSections() {
		return false;
	}

	/**
	 * @return bool
	 */
	public function supportsCategories() {
		return true;
	}

	/**
	 * @return false
	 */
	public function supportsRedirects() {
		return false;
	}

	/**
	 * @return string[]
	 */
	public function getActionOverrides() {
		return [
			'edit' => EditAutomationContent::class,
			'edit-automation-source' => EditAutomationSource::class,
		];
	}

	/**
	 * @param Content $content
	 * @param ContentParseParams $cpoParams
	 * @param ParserOutput &$parserOutput
	 * @return void
	 * @throws EntityNotFoundException
	 */
	protected function fillParserOutput(
		Content $content, ContentParseParams $cpoParams, ParserOutput &$parserOutput
	) {
		$title = Title::castFromPageReference( $cpoParams->getPage() );

		$parserOutput->addModules( [ 'ext.wikiAutomations.automationPage' ] );

		$containerData = [
			'class' => 'wiki-automation-page',
			'data-action' => 'view',
			'data-automation-id' => $title->getPrefixedText(),
		];
		/** @var AutomationEditorDataProvider $editorDataProvider */
		$editorDataProvider = MediaWikiServices::getInstance()->getService( 'WikiAutomations._EditorDataProvider' );
		$containerData = array_merge( $containerData, $editorDataProvider->provideDataForContent( $content ) );

		$text = '';
		OutputPage::setupOOUI();
		if ( !$containerData['data-enabled'] ) {
			$text .= new MessageWidget( [
				'label' => Message::newFromKey( 'wiki-automations-disabled-warning' )->text(),
				'type' => 'warning'
			] );
		}

		$text .= Html::rawElement( 'div', $containerData );

		$text .= ( new RawMessage(
			"\n\n==" . wfMessage( 'wiki-automations-content-log-section-title' )->text() . "==\n"
		) )->parse();

		$logText = '';
		LogEventsList::showLogExtract( $logText, [ 'ext-wiki-automations' ], $title->getPrefixedText() );
		$text .= $logText;

		$parserOutput->setRawText( $text );
	}
}
