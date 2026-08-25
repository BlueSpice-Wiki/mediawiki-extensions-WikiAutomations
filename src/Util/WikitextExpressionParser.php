<?php

namespace MediaWiki\Extension\WikiAutomations\Util;

use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Permissions\Authority;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserIdentity;

/**
 * Given the wikitext expression, provides list of titles resulting from that expression
 */
class WikitextExpressionParser {

	/**
	 * @param Parser $parser
	 * @param TitleFactory $titleFactory
	 * @param UserFactory $userFactory
	 */
	public function __construct(
		private readonly Parser $parser,
		private readonly TitleFactory $titleFactory,
		private readonly UserFactory $userFactory
	) {
	}

	/**
	 * @param string $expression
	 * @param Authority $actor
	 * @param string $separator
	 * @return Status
	 */
	public function processPagelist( string $expression, Authority $actor, string $separator = '|' ): Status {
		$processed = $this->processExpression( $expression, $actor->getUser() );

		$list = explode( $separator, $processed );
		$list = array_map( static function ( $item ) {
			return trim( $item );
		}, $list );

		$res = [];
		foreach ( $list as $pageName ) {
			$title = $this->titleFactory->newFromText( $pageName );
			if ( !( $title instanceof Title ) ) {
				continue;
			}
			$res[] = $title;
		}

		return Status::newGood( $res );
	}

	/**
	 * @param string $expression
	 * @param UserIdentity $actor
	 * @param PageIdentity|null $asPage
	 * @param string $separator
	 * @return array|UserIdentity[]
	 */
	public function processUsers(
		string $expression, UserIdentity $actor, ?PageIdentity $asPage = null, string $separator = ','
	): array {
		$processed = $this->processExpression( $expression, $actor, $asPage );
		$usersRaw = array_map( 'trim', explode( $separator, $processed ) );

		$seenUsers = [];
		$users = array_map( function ( $userName ) use ( &$seenUsers ) {
			if ( in_array( $userName, $seenUsers ) ) {
				return null;
			}
			$seenUsers[] = $userName;
			return $this->userFactory->newFromName( $userName );
		}, $usersRaw );
		return array_filter( $users, static function ( $user ) {
			return $user && $user->isRegistered();
		} );
	}

	/**
	 * @param string $expression
	 * @param UserIdentity $actor
	 * @param PageIdentity|null $asPage
	 * @return string
	 */
	private function processExpression( string $expression, UserIdentity $actor, ?PageIdentity $asPage = null ): string {
		if ( $asPage ) {
			$this->parser->setPage( $asPage );
		}
		$this->parser->setUser( $actor );

		$parserOptions = ParserOptions::newFromUser( $actor );
		$this->parser->setOptions( $parserOptions );
		$this->parser->clearState();

		$processed = $this->parser->preprocess( $expression, $asPage ?? null, $parserOptions );
		if ( !is_string( $processed ) ) {
			return Status::newFatal( Message::newFromKey( 'wikiautomations-pagelistprovider-invalidexpression' ) );
		}
		return $processed;
	}

}
