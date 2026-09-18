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
		if ( !( $this->getData()['isContent'] ?? false ) ) {
			return true;
		}
		return $this->namespaceInfo->isContent( $page->getNamespace() );
	}

	/**
	 * @return array
	 */
	public function getDisplayData(): array {
		$data = $this->getData();
		if ( $data['isContent'] === '1' ) {
			return [ [ 'value' => Message::newFromKey( 'wiki-automations-page-filter-option-yes' )->text() ] ];
		} else {
			return [ [ 'value' => Message::newFromKey( 'wiki-automations-page-filter-option-no' )->text() ] ];
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getLayout(): ?IFormSpecification {
		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
			[
				'type' => 'dropdown',
				'name' => 'isContent',
				'label' => Message::newFromKey( 'wiki-automations-page-filter-content-pages-label' )->text(),
				'required' => false,
				'widget_$overlay' => true,
				'options' => [
					[
						'data' => '0',
						'label' => Message::newFromKey( 'wiki-automations-page-filter-option-no' )->text()
					],
					[
						'data' => '1',
						'label' => Message::newFromKey( 'wiki-automations-page-filter-option-yes' )->text()
					]
				]
			]
		] );
		return $formSpec;
	}
}
