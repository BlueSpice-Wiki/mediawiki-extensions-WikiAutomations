<?php

namespace MediaWiki\Extension\WikiAutomations\Hook;

use MediaWiki\Config\Config;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;

class RegisterContentAction implements SkinTemplateNavigation__UniversalHook {

	/**
	 * @param Config $config
	 */
	public function __construct( private readonly Config $config ) {
	}

	/**
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		if ( !$this->config->get( 'WikiAutomationsEnableEditUI' ) ) {
			return;
		}
		if ( $sktemplate->getTitle()->getContentModel() !== 'automation' ) {
			return;
		}
		if ( !isset( $links['views']['edit'] ) ) {
			return;
		}

		$links['views']['edit-automation-source'] = $links['views']['edit'];
		$links['views']['edit-automation-source']['text'] =
			$sktemplate->msg( 'wiki-automations-ui-action-edit-source' )->text();
		$links['views']['edit-automation-source']['title'] =
			$sktemplate->msg( 'wiki-automations-ui-action-edit-source' )->text();
		$links['views']['edit-automation-source']['href'] =
			$sktemplate->getTitle()->getLinkURL( [ 'action' => 'edit-automation-source' ] );

		$links['views']['edit']['text'] = $sktemplate->msg( 'wiki-automations-ui-action-edit' )->text();
		$links['views']['edit']['title'] = $sktemplate->msg( 'wiki-automations-ui-action-edit' )->text();
	}
}
