<?php

namespace MediaWiki\Extension\WikiAutomations\Rest;

use MediaWiki\Extension\WikiAutomations\EntityFactory;
use MediaWiki\Extension\WikiAutomations\Exception\EntityNotFoundException;
use MediaWiki\Message\Message;
use MediaWiki\Rest\SimpleHandler;

class GetActionsHandler extends SimpleHandler {

	/**
	 * @param EntityFactory $entityFactory
	 */
	public function __construct(
		private readonly EntityFactory $entityFactory
	) {
	}

	/**
	 * @return \MediaWiki\Rest\Response
	 * @throws EntityNotFoundException
	 */
	public function execute() {
		$actions = [];
		foreach ( $this->entityFactory->listActions() as $actionKey => $data ) {

			$actions[$actionKey] = [
				'label' => Message::newFromKey( $data['message'] )->text(),
				'description' => isset( $data['description'] ) ?
					Message::newFromKey( $data['description'] )->text() : '',
				'layout' => $data['layout'] ?? null
			];
		}

		return $this->getResponseFactory()->createJson( $actions );
	}
}
