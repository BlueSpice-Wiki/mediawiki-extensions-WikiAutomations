<?php

namespace MediaWiki\Extension\WikiAutomations\Rest;

use MediaWiki\Extension\WikiAutomations\EntityFactory;
use MediaWiki\Extension\WikiAutomations\Exception\EntityNotFoundException;
use MediaWiki\Message\Message;
use MediaWiki\Rest\SimpleHandler;

class GetPageFiltersHandler extends SimpleHandler {

	/**
	 * @param EntityFactory $entityFactory
	 */
	public function __construct(
		private readonly EntityFactory $entityFactory
	) {
	}

	/**
	 * @return mixed|void
	 * @throws EntityNotFoundException
	 */
	public function execute() {
		$pageFilters = $this->entityFactory->listPageFilters();

		$result = [];
		foreach ( $pageFilters as $key => $data ) {
			$result[$key] = [
				'label' => Message::newFromKey( $data['message'] )->text(),
				'description' => isset( $data['description'] ) ?
					Message::newFromKey( $data['description'] )->text() : '',
				'layout' => $data['layout'] ?? null
			];
		}

		return $this->getResponseFactory()->createJson( $result );
	}
}
