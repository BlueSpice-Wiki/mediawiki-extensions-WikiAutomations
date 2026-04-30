<?php

namespace MediaWiki\Extension\WikiAutomations\PageFilter;

use MediaWiki\Page\PageIdentity;
use MediaWiki\Title\NamespaceInfo;
use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;

class ContentPages extends GenericPageFilter {

	/**
	 * @param NamespaceInfo $namespaceInfo
	 */
	public function __construct(
	private readonly NamespaceInfo $namespaceInfo
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function pageFits( PageIdentity $page ): bool {
		return $this->namespaceInfo->isContent( $page->getNamespace() );
	}

	/**
	 * @inheritDoc
	 */
	public function getLayout(): ?IFormSpecification {
		return null;
	}
}
