<?php

namespace MediaWiki\Extension\WikiAutomations\PageFilter;

use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Revision\RevisionLookup;
use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;

class OnlyMajor extends GenericPageFilter {

	/**
	 * @param RevisionLookup $revisionLookup
	 */
	public function __construct(
	private readonly RevisionLookup $revisionLookup
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function pageFits( PageIdentity $page ): bool {
		$mustBeMajor = $this->getData()['mustBeMajor'] ?? false;
		if ( !$mustBeMajor ) {
			return true;
		}
		return !( $this->revisionLookup->getRevisionByTitle( $page )?->isMinor() ?? false );
	}

	/**
	 * @inheritDoc
	 */
	public function getLayout(): ?IFormSpecification {
		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
		[
	'type' => 'toggle',
	'name' => 'mustBeMajor',
	'label' => Message::newFromKey( 'wiki-automations-page-filter-only-major-label' )->text(),
		],
		] );
		return $formSpec;
	}
}
