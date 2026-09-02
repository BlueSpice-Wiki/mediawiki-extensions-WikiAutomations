<?php

namespace MediaWiki\Extension\WikiAutomations\Component;

use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;
use MediaWiki\Permissions\PermissionManager;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLink;

class CreateAutomationButton extends SimpleLink {

	/**
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( private readonly PermissionManager $permissionManager ) {
		return parent::__construct( [] );
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'wiki-automations-create-automation';
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getClasses(): array {
		return [ 'wiki-automations-create-automation', 'ico-btn', 'bi-bs-create-page' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getRole(): string {
		return 'button';
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): Message {
		return Message::newFromKey( 'wiki-automations-overview-action-create' );
	}

	/**
	 * @inheritDoc
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'wiki-automations-overview-action-create' );
	}

	/**
	 * @inheritDoc
	 */
	public function getHref(): string {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRender( IContextSource $context ): bool {
		return $this->permissionManager->userHasRight(
			$context->getUser(), 'edit-wiki-automations'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getRequiredRLModules(): array {
		return [ 'ext.wikiAutomations.automationsOverview' ];
	}
}
