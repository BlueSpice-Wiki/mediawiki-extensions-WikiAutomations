<?php

namespace MediaWiki\Extension\WikiAutomations\Hook;

use MediaWiki\Permissions\Hook\GetUserPermissionsErrorsHook;

class ManageEditPermissions implements GetUserPermissionsErrorsHook {

	/**
	 * @inheritDoc
	 */
	public function onGetUserPermissionsErrors( $title, $user, $action, &$result ) {
		if ( $action !== 'create' && $action !== 'edit' ) {
			return;
		}
		if ( $title->getContentModel() === 'automation' ) {
			if ( !$user->isAllowed( 'wikiadmin' ) ) {
				$result = [ 'wiki-automations-edit-permission-required' ];
				return false;
			}
		}
	}
}
