<?php

namespace MediaWiki\Extension\WikiAutomations\PageFilter;

use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Title\NamespaceInfo;
use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;

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
		$isContent = $this->getData()['isContent'] ?? false;
		if ( !$isContent ) {
			return true;
		}
		return $this->namespaceInfo->isContent( $page->getNamespace() );
	}

	/**
	 * @inheritDoc
	 */
	public function getLayout(): ?IFormSpecification {
		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
		[
	'type' => 'toggle',
	'name' => 'isContent',
	'label' => Message::newFromKey( 'wiki-automations-page-filter-content-pages-label' )->text(),
		],
		] );
		return $formSpec;
	}
}
