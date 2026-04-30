<?php

namespace MediaWiki\Extension\WikiAutomations\PageFilter;

use MediaWiki\Page\PageIdentity;
use MediaWiki\Revision\RevisionLookup;
use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;

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
		return !( $this->revisionLookup->getRevisionByTitle( $page )?->isMinor() ?? false );
	}

	/**
	 * @inheritDoc
	 */
	public function getLayout(): ?IFormSpecification {
		return null;
	}
}
