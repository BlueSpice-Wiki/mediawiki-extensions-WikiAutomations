<?php

namespace MediaWiki\Extension\WikiAutomations\Rest;

use MediaWiki\Extension\WikiAutomations\EntityFactory;
use MediaWiki\Extension\WikiAutomations\Exception\EntityNotFoundException;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use Wikimedia\ParamValidator\ParamValidator;

class GetEntityDisplayData extends SimpleHandler {

	/**
	 * @param EntityFactory $entityFactory
	 */
	public function __construct(
		private readonly EntityFactory $entityFactory
	) {
	}

	/**
	 * @return Response
	 * @throws EntityNotFoundException
	 */
	public function execute() {
		$params = $this->getValidatedBody();

		$entityType = $params['entityType'];
		$entity = match ( $entityType ) {
			'trigger' => $this->entityFactory->createTrigger( $params['entityKey'] ),
			'pageFilter' => $this->entityFactory->createPageFilter( $params['entityKey'] ),
			'action' => $this->entityFactory->createAction( $params['entityKey'] ),
			default => throw new \UnexpectedValueException( "Invalid entity type: $entityType" ),
		};
		$data = $params['data'] ?? [];
		$entity->setData( $data );
		return $this->getResponseFactory()->createJson( $entity->getDisplayData() );
	}

	/**
	 * @return array[]
	 */
	public function getBodyParamSettings(): array {
		return [
			'entityType' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'entityKey' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'data' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'array',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => [],
			]
		];
	}
}
