<?php

namespace MediaWiki\Extension\WikiAutomations\Exception;

class EntityNotFoundException extends \Exception {

	public function __construct( string $type, string $key ) {
		parent::__construct( "Requested $type \"$key\" not found", 404 );
	}
}
