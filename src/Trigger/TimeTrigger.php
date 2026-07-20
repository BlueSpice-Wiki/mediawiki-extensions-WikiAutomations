<?php

namespace MediaWiki\Extension\WikiAutomations\Trigger;

use MediaWiki\Extension\WikiAutomations\Util\WikitextPagelistProvider;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\User\User;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;

class TimeTrigger extends GenericTrigger {

	private array $dayMapping = [
		'0' => 'wiki-automations-time-trigger-cron-days-sunday',
		'1' => 'wiki-automations-time-trigger-cron-days-monday',
		'2' => 'wiki-automations-time-trigger-cron-days-tuesday',
		'3' => 'wiki-automations-time-trigger-cron-days-wednesday',
		'4' => 'wiki-automations-time-trigger-cron-days-thursday',
		'5' => 'wiki-automations-time-trigger-cron-days-friday',
		'6' => 'wiki-automations-time-trigger-cron-days-saturday',
	];

	/**
	 * @param WikitextPagelistProvider $pagelistProvider
	 */
	public function __construct(
		private readonly WikitextPagelistProvider $pagelistProvider
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getLayout(): ?StandaloneFormSpecification {
		$formSpec = new StandaloneFormSpecification();

		$dayOptions = [];
		foreach ( $this->dayMapping as $day => $messageKey ) {
			$dayOptions[] = [
				'data' => $day,
				'label' => Message::newFromKey( $messageKey )->text(),
			];
		}

		$hourOptions = [];
		for ( $i = 0; $i < 24; $i++ ) {
			$hourOptions[] = [
				'data' => (string)$i,
				'label' => sprintf( '%02d:00', $i ),
			];
		}

		$formSpec->setItems( array_merge( [
			[
				'type' => 'dropdown',
				'name' => 'hourOfDay',
				'options' => $hourOptions,
				'label' => Message::newFromKey( 'wiki-automations-time-trigger-cron-time' )->text(),
				'labelAlign' => 'top',
			],
			[
				'type' => 'checkbox_multiselect',
				'name' => 'daysOfWeek',
				'options' => $dayOptions,
				'label' => Message::newFromKey( 'wiki-automations-time-trigger-cron-days' )->text(),
				'labelAlign' => 'top',
			],
		], [ $this->getPageProviderLayoutItem() ] ) );

		return $formSpec;
	}

	/**
	 * @return array
	 */
	protected function getPageProviderLayoutItem(): array {
		return [
			'type' => 'text',
			'name' => 'pages',
			'label' => Message::newFromKey( 'wiki-automations-time-trigger-pages-label' )->text(),
			'labelAlign' => 'top',
			'help' => Message::newFromKey( 'wiki-automations-time-trigger-pages-help' )->text(),
			'helpInline' => true,
		];
	}

	/**
	 * @return string|null
	 */
	public function getTimeExpression(): ?string {
		$data = $this->getData();
		if ( !empty( $data['time_expression'] ) ) {
			return $data['time_expression'];
		}
		$hour = $data['hourOfDay'] ?? '';
		if ( $hour === '' ) {
			return null;
		}
		if ( !empty( $data['daysOfWeek'] ) ) {
			$days = implode( ',', $data['daysOfWeek'] );
		} else {
			$days = '*';
		}
		return "0 {$hour} * * {$days}";
	}

	public function getDisplayData(): array {
		$data = $this->getData();
		$hour = sprintf( '%02d:00', $data['hourOfDay'] ?? 0 );

		$daysOfWeek = $data['daysOfWeek'] ?? [];
		if ( empty( $daysOfWeek ) ) {
			$daysText = Message::newFromKey( 'wiki-automations-time-trigger-display-every-day' )->text();
		} else {
			$dayLabels = array_map( function ( $day ) {
				return Message::newFromKey( $this->dayMapping[$day] )->text();
			}, $daysOfWeek );
			$daysText = implode( ', ', $dayLabels );
		}

		$displayData = [
			[
				'value' => Message::newFromKey( 'wiki-automations-time-trigger-display-time', [ $hour ] )->text(),
			],
			[
				'value' => $daysText
			],
		];

		if ( $data['pages'] ) {
			$displayData[] = [
				'key' => Message::newFromKey( 'wiki-automations-time-trigger-display-pages' )->text(),
				'value' => $data['pages'],
			];
		}
		return $displayData;
	}

	/**
	 * @return array|PageIdentity[]
	 */
	public function providePages( array $triggerData = [] ): array {
		$pagesExpression = $this->getData()['pages'] ?? null;
		if ( !$pagesExpression ) {
			return [];
		}
		$status = $this->pagelistProvider->processExpression(
			$pagesExpression,
			User::newSystemUser( User::MAINTENANCE_SCRIPT_USER, [ 'steal' => true ] )
		);
		if ( !$status->isOK() ) {
			return [];
		}
		return $status->getValue();
	}
}
