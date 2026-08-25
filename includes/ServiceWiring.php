<?php

use MediaWiki\Extension\WikiAutomations\AutomationRunner;
use MediaWiki\Extension\WikiAutomations\AutomationStore;
use MediaWiki\Extension\WikiAutomations\EntityFactory;
use MediaWiki\Extension\WikiAutomations\Util\AutomationLogger;
use MediaWiki\Extension\WikiAutomations\Util\TriggerCronListManager;
use MediaWiki\Extension\WikiAutomations\Util\WikitextExpressionParser;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

return [
	'WikiAutomations.EntityFactory' => static function ( MediaWikiServices $services ) {
		$instance = new EntityFactory(
			ExtensionRegistry::getInstance()->getAttribute( 'WikiAutomationsTriggerTypes' ),
			ExtensionRegistry::getInstance()->getAttribute( 'WikiAutomationsTriggers' ),
			ExtensionRegistry::getInstance()->getAttribute( 'WikiAutomationsPageFilters' ),
			ExtensionRegistry::getInstance()->getAttribute( 'WikiAutomationsActions' ),
			$services->getObjectFactory()
		);
		$instance->setLogger( $services->getService( 'WikiAutomations._Logger' ) );
		return $instance;
	},
	'WikiAutomations.AutomationStore' => static function ( MediaWikiServices $services ) {
		return new AutomationStore(
			$services->getService( 'WikiAutomations.EntityFactory' ),
			$services->getRevisionLookup(),
			$services->getDBLoadBalancer(),
			$services->getWikiPageFactory(),
			$services->getTitleFactory()
		);
	},
	'WikiAutomations.Runner' => static function ( MediaWikiServices $services ) {
		$runner = new AutomationRunner(
			$services->getService( 'WikiAutomations.AutomationStore' ),
			$services->getService( 'WikiAutomations.Util.AutomationLogger' ),
			$services->getService( 'ProcessManager' )
		);
		$runner->setLogger( $services->getService( 'WikiAutomations._Logger' ) );
		return $runner;
	},
	'WikiAutomations.Util.WikitextExpressionParser' => static function ( MediaWikiServices $services ) {
		return new WikitextExpressionParser(
			$services->getParser(),
			$services->getTitleFactory(),
			$services->getUserFactory()
		);
	},
	'WikiAutomations.Util.AutomationLogger' => static function ( MediaWikiServices $services ) {
		return new AutomationLogger(
			$services->getTitleFactory()
		);
	},
	'WikiAutomations.Util.CronListManager' => static function ( MediaWikiServices $services ) {
		$instance = new TriggerCronListManager( $services->getDBLoadBalancer() );
		$instance->setLogger( $services->getService( 'WikiAutomations._Logger' ) );
		return $instance;
	},
	'WikiAutomations._Logger' => static function () {
		return LoggerFactory::getInstance( 'WikiAutomations' );
	},
	'WikiAutomations._EditorDataProvider' => static function ( MediaWikiServices $services ) {
		return new \MediaWiki\Extension\WikiAutomations\Util\AutomationEditorDataProvider(
			$services->getService( 'WikiAutomations.EntityFactory' )
		);
	}
];
