<?php

namespace MediaWiki\Extension\WikiAutomations\Util;

use MediaWiki\Message\Message;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Permissions\Authority;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;

/**
 * Given the wikitext expression, provides list of titles resulting from that expression
 */
class WikitextPagelistProvider {

	/**
	 * @param Parser $parser
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		private readonly Parser $parser,
		private readonly TitleFactory $titleFactory
	) {
	}

	/**
	 * @param string $expression
	 * @param Authority $actor
	 * @param string $separator
	 * @return Status
	 */
	public function processExpression( string $expression, Authority $actor, string $separator = '|' ): Status {
		$processed = $this->parser->preprocess( $expression, null, ParserOptions::newFromUser( $actor->getUser() ) );
		if ( !is_string( $processed ) ) {
			return Status::newFatal( Message::newFromKey( 'wikiautomations-pagelistprovider-invalidexpression' ) );
		}

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
}
