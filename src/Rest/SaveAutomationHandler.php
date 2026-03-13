<?php

namespace MediaWiki\Extension\WikiAutomations\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\WikiAutomations\AutomationStore;
use MediaWiki\Extension\WikiAutomations\EntityFactory;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Title\TitleFactory;
use Wikimedia\ParamValidator\ParamValidator;

class SaveAutomationHandler extends SimpleHandler {

	/**
	 * @param TitleFactory $titleFactory
	 * @param AutomationStore $automationStore
	 * @param EntityFactory $entityFactory
	 */
	public function __construct(
		private readonly TitleFactory $titleFactory,
		private readonly AutomationStore $automationStore,
		private readonly EntityFactory $entityFactory
	) {
	}

	/**
	 * @return mixed|void
	 */
	public function execute() {
		$body = $this->getValidatedBody();

		$title = $this->titleFactory->newFromText( $body['title'] );
		if ( !$title ) {
			throw new \InvalidArgumentException( 'Invalid title' );
		}
		$data = json_decode( $body['data'], true );
		if ( !is_array( $data ) ) {
			throw new \InvalidArgumentException( 'Data must be a JSON object' );
		}
		try {
			// This will throw if automation data not valid
			$automation = $this->entityFactory->automationFromData( $data );
			$revision = $this->automationStore->saveAutomation(
				$title, $automation, RequestContext::getMain()->getUser()
			);
			return $this->getResponseFactory()->createJson( [
				'success' => true,
				'revision_id' => $revision->getId(),
				'title' => $title->getPrefixedText()
			] );
		} catch ( \Throwable $ex ) {
			throw new \RuntimeException( 'Failed to save automation: ' . $ex->getMessage() );
		}
	}

	public function getBodyParamSettings(): array {
		return [
			'data' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
			'title' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			]
		];
	}

	public function needsWriteAccess() {
		return true;
	}
}
