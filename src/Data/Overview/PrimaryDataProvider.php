<?php

namespace MediaWiki\Extension\WikiAutomations\Data\Overview;

use MediaWiki\Extension\WikiAutomations\AutomationStore;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\DataStore\IPrimaryDataProvider;

class PrimaryDataProvider implements IPrimaryDataProvider {

	/**
	 * @param AutomationStore $automationStore
	 */
	public function __construct( private readonly AutomationStore $automationStore ) {
	}

	/**
	 * @inheritDoc
	 */
	public function makeData( $params ) {
		$data = [];
		foreach ( $this->automationStore->getAutomationPages() as $title ) {
			$automation = $this->automationStore->getAutomationByPage( $title );
			$namespaceText = $title->getNamespace() === NS_MAIN ?
				Message::newFromKey( 'blanknamespace' )->text() :
				$title->getNsText();
			$name = preg_replace( '/\.automation$/', '', $title->getText() );

			$data[] = new Record( (object)[
				Record::NAME => $name,
				Record::PAGE_PREFIXED_TEXT => $title->getPrefixedText(),
				Record::NAMESPACE_TEXT => $namespaceText,
				Record::PAGE_URL => $title->getLocalURL(),
				Record::STATUS => $automation && $automation->isEnabled() ?
					Record::STATUS_ENABLED :
					Record::STATUS_DISABLED
			] );
		}
		return $data;
	}
}
