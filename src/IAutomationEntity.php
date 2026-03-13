<?php

namespace MediaWiki\Extension\WikiAutomations;

use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;

interface IAutomationEntity {

	/**
	 * @return array
	 */
	public function getDisplayData(): array;

	/**
	 * @return IFormSpecification|null
	 */
	public function getLayout(): ?IFormSpecification;

	/**
	 * @param array $data
	 */
	public function setData( array $data ): void;

	/**
	 * @return array
	 */
	public function getData(): array;

	/**
	 * @param bool $enabled
	 * @return void
	 */
	public function setEnabled( bool $enabled ): void;

	/**
	 * @return bool
	 */
	public function isEnabled(): bool;
}
