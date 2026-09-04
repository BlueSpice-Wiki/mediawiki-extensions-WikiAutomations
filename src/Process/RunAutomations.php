<?php

namespace MediaWiki\Extension\WikiAutomations\Process;

use MediaWiki\Extension\WikiAutomations\AutomationRunner;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\ProcessManager\IProcessStep;

class RunAutomations implements IProcessStep {

	/**
	 * @param AutomationRunner $runner
	 * @param UserFactory $userFactory
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		private readonly AutomationRunner $runner,
		private readonly UserFactory $userFactory,
		private readonly TitleFactory $titleFactory
	) {
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function execute( $data = [] ): array {
		$triggeredBy = null;
		if ( $data['triggeredBy'] ) {
			$triggeredBy = $this->userFactory->newFromName( $data['triggeredBy'] );
		}
		$pages = array_map( function ( $pageData ) {
			return $this->titleFactory->makeTitle( $pageData['namespace'], $pageData['title'] );
		}, $data['forPages'] );
		$pages = array_filter( $pages );

		return $this->runner->trigger(
			triggerKey: $data['triggerKey'],
			forPages: $pages,
			triggeredBy: $triggeredBy,
			triggerData: $data['triggerData'] ?? []
		);
	}
}
