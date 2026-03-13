<?php

namespace MediaWiki\Extension\WikiAutomations\Trigger;

use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;

class NightlyTrigger extends TimeTrigger {

	/**
	 * @inheritDoc
	 */
	public function getLayout(): ?StandaloneFormSpecification {
		$spec = new StandaloneFormSpecification();
		$spec->setItems( [ $this->getPageProviderLayoutItem() ] );
		return $spec;
	}

	/**
	 * @inheritDoc
	 */
	public function getData(): array {
		return array_merge( parent::getData(), [
			'time_expression' => '0 1 * * *'
		] );
	}
}
