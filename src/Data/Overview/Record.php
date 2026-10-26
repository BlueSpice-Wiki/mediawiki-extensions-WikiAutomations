<?php

namespace MediaWiki\Extension\WikiAutomations\Data\Overview;

class Record extends \MWStake\MediaWiki\Component\DataStore\Record {
	public const NAME = 'name';
	public const PAGE_PREFIXED_TEXT = 'page_prefixed_text';
	public const NAMESPACE_TEXT = 'namespace_text';
	public const PAGE_URL = 'page_url';
	public const STATUS = 'status';

	public const STATUS_ENABLED = 'enabled';
	public const STATUS_DISABLED = 'disabled';
}
