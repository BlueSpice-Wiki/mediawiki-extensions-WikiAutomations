<?php

namespace MediaWiki\Extension\WikiAutomations\Special;

use MediaWiki\Html\Html;
use OOJSPlus\Special\OOJSGridSpecialPage;

class AutomationsOverview extends OOJSGridSpecialPage {

	public function __construct() {
		parent::__construct( 'Automations', 'edit-wiki-automations' );
	}

	/**
	 * @param string $subPage
	 * @return void
	 */
	protected function doExecute( $subPage ) {
		$this->getOutput()->addModules( [ 'ext.wikiAutomations.automationsOverview' ] );
		$this->getOutput()->addHTML(
			Html::element( 'div', [
				'id' => 'automations-overview-app-container',
				'class' => 'automations-overview-app-container'
			] )
		);
	}
}
