<?php

namespace MediaWiki\Extension\WikiAutomations\ContentAction;

class EditAutomationSource extends \EditAction {

	/**
	 * @return string
	 */
	public function getName() {
		return 'edit-automation-source';
	}

	/**
	 * @return string
	 */
	public function getRestriction() {
		return 'edit-wiki-automations';
	}
}
