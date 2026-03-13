<?php

namespace MediaWiki\Extension\WikiAutomations\Util;

use ManualLogEntry;
use MediaWiki\Message\Message;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;

class AutomationLogger {

	public function __construct(
		private readonly TitleFactory $titleFactory
	) {
	}

	/**
	 * @param string $automationName
	 * @param Status $status
	 * @param string $triggeredBy
	 * @param array $onPages
	 * @return void
	 */
	public function logRun( string $automationName, Status $status, string $triggeredBy, array $onPages ) {
		$title = $this->titleFactory->newFromText( $automationName );
		if ( !$title || $title->getContentModel() !== 'automation' ) {
			// Something very wrong
			return;
		}
		$actor = User::newSystemUser( 'MediaWiki default', [ 'steal' => true ] );
		if ( $status->isOK() ) {
			$this->addEntry(
				'automation-run-success',
				$title,
				$actor,
				[
					'4::triggeredby' => $triggeredBy,
					'5::pages' => $this->formatPages( $onPages )
				]
			);
		} else {
			$warnings = $status->getMessages( 'warning' );
			$warningText = $this->composeErrorMessage( $warnings );
			$errors = $status->getMessages( 'error' );
			if ( !empty( $errors ) ) {
				$errorText = $this->composeErrorMessage( $errors );
				$this->addEntry(
					'automation-run-failure',
					$title,
					$actor,
					[
						'4::triggeredby' => $triggeredBy,
						'5::warnings' => $warningText,
						'6::errors' => $errorText
					]
				);
			} else {
				$this->addEntry(
					'automation-run-with-warnings',
					$title,
					$actor,
					[
						'4::triggeredby' => $triggeredBy,
						'5::warnings' => $warningText,
					]
				);
			}
		}
	}

	/**
	 * @param string $action
	 * @param Title $target
	 * @param User $actor
	 * @param array $params
	 * @return void
	 */
	private function addEntry( string $action, Title $target, User $actor, array $params = [] ) {
		$logEntry = new ManualLogEntry( 'ext-wiki-automations', $action );
		$logEntry->setPerformer( $actor );
		$logEntry->setTarget( $target );

		$logEntry->setParameters( $params );

		$logId = $logEntry->insert();

		$logEntry->publish( $logId );
	}

	/**
	 * @param array $errors
	 * @return string
	 */
	private function composeErrorMessage( array $errors ): string {
		$text = [];
		foreach ( $errors as $error ) {
			$text[] = Message::newFromSpecifier( $error )->text();
		}
		return implode( '; ', $text );
	}

	/**
	 * @param array $pages
	 * @return string
	 */
	private function formatPages( array $pages ): string {
		if ( empty( $pages ) ) {
			return '(none)';
		}
		$text = [];
		foreach ( $pages as $page ) {
			$title = $this->titleFactory->newFromPageReference( $page );
			$text[] = $title->getPrefixedText();
			if ( count( $text ) >= 10 ) {
				$text[] = Message::newFromKey( 'wiki-automations-log-more-pages', count( $pages ) - 10 )->text();
				break;
			}
		}

		return implode( ', ', $text );
	}

}
