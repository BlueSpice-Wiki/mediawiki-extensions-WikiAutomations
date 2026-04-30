<?php

namespace MediaWiki\Extension\WikiAutomations\PageFilter;

use MediaWiki\Language\Language;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Title\NamespaceInfo;
use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;

class NamespaceFilter extends GenericPageFilter {

	/**
	 * @param NamespaceInfo $namespaceInfo
	 * @param Language $language
	 */
	public function __construct(
		private readonly NamespaceInfo $namespaceInfo,
		private readonly Language $language
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function pageFits( PageIdentity $page ): bool {
		$namespaces = $this->getData()['namespaces'] ?? [];
		if ( empty( $namespaces ) ) {
			return true;
		}
		$namespaces = array_map( 'intval', $namespaces );
		return in_array( $page->getNamespace(), $namespaces, true );
	}

	public function getDisplayData(): array {
		$data = $this->getData();
		if ( !$data['namespaces'] ) {
			return [];
		}
		$labels = array_map( function ( $ns ) {
			return $this->getNamespaceLabel( $ns );
		}, $data['namespaces'] );

		return [ [
			'value' => implode( ', ', $labels )
		] ];
	}

	/**
	 * @inheritDoc
	 */
	public function getLayout(): ?IFormSpecification {
		$contentNamespaces = $this->namespaceInfo->getContentNamespaces();
		$options = [];
		sort( $contentNamespaces );
		foreach ( $contentNamespaces as $ns ) {
			$label = $this->getNamespaceLabel( $ns );
			if ( !$label ) {
				continue;
			}
			$options[] = [
				'label' => $label,
				'data' => (string) $ns
			];
		}
		if ( empty( $options ) ) {
			return null;
		}
		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
			[
				'type' => 'menutag_multiselect',
				'name' => 'namespaces',
				'required' => false,
				'label' => Message::newFromKey( 'wiki-automations-page-filter-ns-label' )->text(),
				'widget_$overlay' => true,
				'options' => $options
			],
		] );

		return $formSpec;
	}

	/**
	 * @param int $id
	 * @return string
	 */
	private function getNamespaceLabel( int $id ): string {
		$label = $id === NS_MAIN ?
			Message::newFromKey( 'blanknamespace' )->inLanguage( $this->language )->text() :
			$this->language->getNsText( $id );
		if ( !$label ) {
			$label = $this->namespaceInfo->getCanonicalName( $id );
		}
		return $label;
	}
}
