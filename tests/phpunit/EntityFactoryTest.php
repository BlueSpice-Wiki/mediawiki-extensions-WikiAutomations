<?php

namespace MediaWiki\Extension\WikiAutomations\Tests;

use MediaWiki\Extension\WikiAutomations\Automation;
use MediaWiki\Extension\WikiAutomations\EntityFactory;
use MediaWiki\Extension\WikiAutomations\IAutomationAction;
use MediaWiki\Extension\WikiAutomations\IAutomationTrigger;
use MediaWiki\Extension\WikiAutomations\IPageFilter;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Wikimedia\ObjectFactory\ObjectFactory;

/**
 * @covers \MediaWiki\Extension\WikiAutomations\EntityFactory
 */
class EntityFactoryTest extends TestCase {

	private function createMockObjectFactory(): ObjectFactory {
		$objectFactory = $this->createMock( ObjectFactory::class );
		return $objectFactory;
	}

	private function createMockTrigger(): IAutomationTrigger {
		$trigger = $this->createMock( IAutomationTrigger::class );
		$mockLayout = $this->createMock( \MWStake\MediaWiki\Component\FormEngine\IFormSpecification::class );
		$trigger->method( 'getLayout' )->willReturn( $mockLayout );
		return $trigger;
	}

	private function createMockPageFilter(): IPageFilter {
		$filter = $this->createMock( IPageFilter::class );
		return $filter;
	}

	private function createMockAction(): IAutomationAction {
		$action = $this->createMock( IAutomationAction::class );
		$mockLayout = $this->createMock( \MWStake\MediaWiki\Component\FormEngine\IFormSpecification::class );
		$action->method( 'getLayout' )->willReturn( $mockLayout );
		return $action;
	}

	public function testGetTriggerTypes() {
		$triggerTypes = [
			'page' => [ 'message' => 'wiki-automations-trigger-type-page' ],
			'time' => [ 'message' => 'wiki-automations-trigger-type-time' ],
		];

		$factory = new EntityFactory(
			$triggerTypes,
			[],
			[],
			[],
			$this->createMockObjectFactory()
		);

		$this->assertEquals( $triggerTypes, $factory->getTriggerTypes() );
	}

	public function testListTriggersThrowsExceptionForInvalidType() {
		$triggerRegistry = [
			'edit' => [
				'type' => 'invalid_type',
				'message' => 'wiki-automations-trigger-edit',
				'spec' => [ 'class' => 'MockClass' ]
			]
		];

		$factory = new EntityFactory(
			[],
			$triggerRegistry,
			[],
			[],
			$this->createMockObjectFactory()
		);

		$this->expectException( \UnexpectedValueException::class );
		$factory->listTriggers();
	}

	public function testListTriggers() {
		$triggerTypes = [ 'page' => [ 'message' => 'trigger-type-page' ] ];
		$triggerRegistry = [
			'edit' => [
				'type' => 'page',
				'message' => 'wiki-automations-trigger-edit',
				'spec' => [ 'class' => 'MockTriggerClass' ]
			]
		];

		$objectFactory = $this->createMockObjectFactory();
		$objectFactory->method( 'createObject' )
			->willReturn( $this->createMockTrigger() );

		$factory = new EntityFactory(
			$triggerTypes,
			$triggerRegistry,
			[],
			[],
			$objectFactory
		);

		$triggers = $factory->listTriggers();

		$this->assertArrayHasKey( 'edit', $triggers );
		$this->assertEquals( 'wiki-automations-trigger-edit', $triggers['edit']['message'] );
		$this->assertEquals( 'page', $triggers['edit']['type'] );
	}

	public function testListPageFilters() {
		$filterRegistry = [
			'namespace' => [
				'message' => 'wiki-automations-filter-namespace',
				'spec' => [ 'class' => 'MockFilterClass' ]
			]
		];

		$objectFactory = $this->createMockObjectFactory();
		$objectFactory->method( 'createObject' )
			->willReturn( $this->createMockPageFilter() );

		$factory = new EntityFactory(
			[],
			[],
			$filterRegistry,
			[],
			$objectFactory
		);

		$filters = $factory->listPageFilters();

		$this->assertArrayHasKey( 'namespace', $filters );
	}

	public function testListActions() {
		$actionRegistry = [
			'test_action' => [
				'message' => 'wiki-automations-action-test',
				'type' => 'content',
				'spec' => [ 'class' => 'MockActionClass' ]
			]
		];

		$objectFactory = $this->createMockObjectFactory();
		$objectFactory->method( 'createObject' )
			->willReturn( $this->createMockAction() );

		$factory = new EntityFactory(
			[],
			[],
			[],
			$actionRegistry,
			$objectFactory
		);

		$actions = $factory->listActions();

		$this->assertArrayHasKey( 'test_action', $actions );
		$this->assertEquals( 'wiki-automations-action-test', $actions['test_action']['message'] );
		$this->assertEquals( 'content', $actions['test_action']['type'] );
	}

	public function testGetTriggerKeysOfType() {
		$triggerRegistry = [
			'edit' => [ 'type' => 'page', 'spec' => [] ],
			'delete' => [ 'type' => 'page', 'spec' => [] ],
			'schedule' => [ 'type' => 'time', 'spec' => [] ],
		];

		$factory = new EntityFactory(
			[],
			$triggerRegistry,
			[],
			[],
			$this->createMockObjectFactory()
		);

		$pageKeys = $factory->getTriggerKeysOfType( 'page' );
		$this->assertCount( 2, $pageKeys );
		$this->assertContains( 'edit', $pageKeys );
		$this->assertContains( 'delete', $pageKeys );

		$timeKeys = $factory->getTriggerKeysOfType( 'time' );
		$this->assertCount( 1, $timeKeys );
		$this->assertContains( 'schedule', $timeKeys );
	}

	public function testAutomationFromDataCreatesAutomation() {
		$triggerRegistry = [
			'edit' => [
				'type' => 'page',
				'spec' => [ 'class' => 'MockTriggerClass' ]
			]
		];
		$filterRegistry = [
			'namespace' => [ 'spec' => [ 'class' => 'MockFilterClass' ] ]
		];
		$actionRegistry = [
			'test_action' => [
				'spec' => [ 'class' => 'MockActionClass' ]
			]
		];

		$mockTrigger = $this->createMockTrigger();
		$mockTrigger->expects( $this->once() )
			->method( 'setData' )
			->with( [ 'config' => 'value' ] );
		$mockTrigger->expects( $this->once() )
			->method( 'setEnabled' )
			->with( true );

		$mockFilter = $this->createMockPageFilter();
		$mockFilter->expects( $this->once() )
			->method( 'setData' )
			->with( [ 'namespaces' => [ 0 ] ] );

		$mockAction = $this->createMockAction();
		$mockAction->expects( $this->once() )
			->method( 'setData' )
			->with( [ 'key' => 'test_action', 'param' => 'value' ] );

		$objectFactory = $this->createMockObjectFactory();
		$objectFactory->method( 'createObject' )
			->willReturnOnConsecutiveCalls( $mockTrigger, $mockFilter, $mockAction );

		$factory = new EntityFactory(
			[],
			$triggerRegistry,
			$filterRegistry,
			$actionRegistry,
			$objectFactory
		);
		$factory->setLogger( new NullLogger() );

		$data = [
			'triggers' => [
				'edit' => [
					'data' => [ 'config' => 'value' ],
					'enabled' => true
				]
			],
			'pageFilters' => [
				'namespace' => [ 'data' => [ 'namespaces' => [ 0 ] ], 'enabled' => true ]
			],
			'actions' => [
				[
					'key' => 'test_action',
					'data' => [ 'param' => 'value' ],
					'enabled' => true
				]
			],
			'enabled' => false
		];

		$automation = $factory->automationFromData( $data );

		$this->assertInstanceOf( Automation::class, $automation );
		$this->assertFalse( $automation->isEnabled() );
		$this->assertCount( 1, $automation->getTriggers() );
		$this->assertCount( 1, $automation->getPageFilters() );
		$this->assertCount( 1, $automation->getActions() );
	}

	public function testAutomationFromDataSkipsInvalidEntities() {
		$triggerRegistry = [
			'edit' => [ 'type' => 'page', 'spec' => [ 'class' => 'MockTriggerClass' ] ]
		];

		$mockTrigger = $this->createMockTrigger();
		$objectFactory = $this->createMockObjectFactory();
		$objectFactory->method( 'createObject' )
			->willReturn( $mockTrigger );

		$factory = new EntityFactory(
			[],
			$triggerRegistry,
			[],
			[],
			$objectFactory
		);
		$factory->setLogger( new NullLogger() );

		$data = [
			'triggers' => [
				'edit' => [ 'data' => [], 'enabled' => true ],
				'invalid_trigger' => [ 'data' => [], 'enabled' => true ]
			]
		];

		$automation = $factory->automationFromData( $data );

		$this->assertCount( 1, $automation->getTriggers() );
	}

	public function testAutomationFromDataWithEmptyData() {
		$factory = new EntityFactory(
			[],
			[],
			[],
			[],
			$this->createMockObjectFactory()
		);
		$factory->setLogger( new NullLogger() );

		$automation = $factory->automationFromData( [] );

		$this->assertInstanceOf( Automation::class, $automation );
		$this->assertTrue( $automation->isEnabled() );
		$this->assertEquals( [], $automation->getTriggers() );
		$this->assertEquals( [], $automation->getPageFilters() );
		$this->assertEquals( [], $automation->getActions() );
	}
}
