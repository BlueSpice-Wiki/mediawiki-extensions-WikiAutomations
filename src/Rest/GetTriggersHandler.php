<?php

namespace MediaWiki\Extension\WikiAutomations\Rest;

use MediaWiki\Extension\WikiAutomations\EntityFactory;
use MediaWiki\Extension\WikiAutomations\Exception\EntityNotFoundException;
use MediaWiki\Message\Message;
use MediaWiki\Rest\SimpleHandler;

class GetTriggersHandler extends SimpleHandler {

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
		$types = $this->entityFactory->getTriggerTypes();

		$typedTriggers = [];
		foreach ( $this->entityFactory->listTriggers() as $triggerKey => $data ) {
			$type = $data['type'];
			if ( !isset( $typedTriggers[$type] ) ) {
				$typedTriggers[$type] = [];
			}
			$typedTriggers[$type][$triggerKey] = [
				'label' => Message::newFromKey( $data['message'] )->text(),
				'description' => isset( $data['description'] ) ?
					Message::newFromKey( $data['description'] )->text() : '',
				'layout' => $data['layout'] ?? null
			];
		}

		$result = [];
		foreach ( $types as $type => $triggerTypeData ) {
			$result[$type] = [
				'message' => Message::newFromKey( $triggerTypeData['message'] )->text(),
				'triggers' => $typedTriggers[$type] ?? []
			];
		}

		return $this->getResponseFactory()->createJson( $result );
	}
}
