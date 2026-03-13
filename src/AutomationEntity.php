<?php

namespace MediaWiki\Extension\WikiAutomations;

use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;

abstract class AutomationEntity implements IAutomationEntity {

	/** @var array */
	private array $data = [];

	/** @var bool */
	private bool $isEnabled = true;

	/**
	 * @param array $data
	 * @return void
	 */
	public function setData( array $data ): void {
		$this->data = $data;
	}

	/**
	 * @return array
	 */
	public function getData(): array {
		return $this->data;
	}

	/**
	 * @param bool $enabled
	 * @return void
	 */
	public function setEnabled( bool $enabled ): void {
		$this->isEnabled = $enabled;
	}

	/**
	 * @return bool
	 */
	public function isEnabled(): bool {
		return $this->isEnabled;
	}

	/**
	 * @return array
	 */
	public function getDisplayData(): array {
		$data = $this->getData();
		$displayData = [];
		foreach ( $data as $key => $value ) {
			$displayData[$key] = [
				'label' => $key,
				'value' => is_array( $value ) ? json_encode( $value ) : (string)$value,
			];
		}
		return $displayData;
	}

	/**
	 * @return IFormSpecification|null
	 */
	public function getLayout(): ?IFormSpecification {
		return null;
	}
}
