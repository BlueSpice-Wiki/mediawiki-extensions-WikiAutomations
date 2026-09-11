<?php

namespace MediaWiki\Extension\WikiAutomations\Hook;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\WikiAutomations\Component\CreateAutomationButton;
use MediaWiki\Permissions\PermissionManager;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class CommonUserInterface implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( private readonly PermissionManager $permissionManager ) {
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$context = RequestContext::getMain();
		$title = $context->getTitle();
		$skin = $context->getSkin();

		if ( $title && $title->isSpecial( 'Automations' ) &&
			is_a( $skin, 'SkinBlueSpiceEclipseSkin', true ) ) {
			$registry->register(
				'TitleActions',
				[
					'create-automation-action' => [
						'factory' => function () {
							return new CreateAutomationButton( $this->permissionManager );
						}
					]
				]
			);
		}
	}
}
