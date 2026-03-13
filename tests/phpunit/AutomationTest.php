<?php

namespace MediaWiki\Extension\WikiAutomations\Tests;

use MediaWiki\Extension\WikiAutomations\Automation;
use MediaWiki\Extension\WikiAutomations\IAutomationAction;
use MediaWiki\Extension\WikiAutomations\IAutomationTrigger;
use MediaWiki\Extension\WikiAutomations\IPageFilter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\WikiAutomations\Automation
 */
class AutomationTest extends TestCase {

	private function createMockTrigger( array $data = [], bool $enabled = true ): IAutomationTrigger {
		$trigger = $this->createMock( IAutomationTrigger::class );
		$trigger->method( 'getData' )->willReturn( $data );
		$trigger->method( 'isEnabled' )->willReturn( $enabled );
		return $trigger;
	}

	private function createMockPageFilter( array $data = [] ): IPageFilter {
		$filter = $this->createMock( IPageFilter::class );
		$filter->method( 'getData' )->willReturn( $data );
		return $filter;
	}

	private function createMockAction( array $data = [], bool $enabled = true ): IAutomationAction {
		$action = $this->createMock( IAutomationAction::class );
		$action->method( 'getData' )->willReturn( $data );
		$action->method( 'isEnabled' )->willReturn( $enabled );
		return $action;
	}

	public function testConstructorSetsDefaultEnabled() {
		$automation = new Automation( [], [], [] );
		$this->assertTrue( $automation->isEnabled() );
	}

	public function testConstructorSetsEnabledState() {
		$automation = new Automation( [], [], [], false );
		$this->assertFalse( $automation->isEnabled() );
	}

	public function testGetTriggers() {
		$trigger = $this->createMockTrigger();
		$automation = new Automation( [ $trigger ], [], [] );

		$this->assertCount( 1, $automation->getTriggers() );
		$this->assertSame( $trigger, $automation->getTriggers()[0] );
	}

	public function testGetPageFilters() {
		$filter = $this->createMockPageFilter();
		$automation = new Automation( [], [ $filter ], [] );

		$this->assertCount( 1, $automation->getPageFilters() );
		$this->assertSame( $filter, $automation->getPageFilters()[0] );
	}

	public function testGetActions() {
		$action = $this->createMockAction();
		$automation = new Automation( [], [], [ $action ] );

		$this->assertCount( 1, $automation->getActions() );
		$this->assertSame( $action, $automation->getActions()[0] );
	}

	public function testSetEnabled() {
		$automation = new Automation( [], [], [], true );
		$automation->setEnabled( false );
		$this->assertFalse( $automation->isEnabled() );

		$automation->setEnabled( true );
		$this->assertTrue( $automation->isEnabled() );
	}

	public function testJsonSerialize() {
		$triggerData = [ 'type' => 'edit' ];
		$trigger = $this->createMockTrigger( $triggerData, true );

		$filterData = [ 'namespaces' => [ 0, 1 ] ];
		$filter = $this->createMockPageFilter( $filterData );

		$actionData = [ 'key' => 'test-action', 'config' => 'value' ];
		$action = $this->createMockAction( $actionData, false );

		$automation = new Automation( [ $trigger ], [ $filter ], [ $action ], false );

		$serialized = $automation->jsonSerialize();

		$this->assertIsArray( $serialized );
		$this->assertArrayHasKey( 'triggers', $serialized );
		$this->assertArrayHasKey( 'pageFilters', $serialized );
		$this->assertArrayHasKey( 'actions', $serialized );
		$this->assertArrayHasKey( 'enabled', $serialized );

		$this->assertFalse( $serialized['enabled'] );
		$this->assertCount( 1, $serialized['triggers'] );
		$this->assertCount( 1, $serialized['pageFilters'] );
		$this->assertCount( 1, $serialized['actions'] );

		$this->assertEquals( $triggerData, $serialized['triggers'][0]['data'] );
		$this->assertTrue( $serialized['triggers'][0]['enabled'] );

		$this->assertEquals( $filterData, $serialized['pageFilters'][0]['data'] );

		$this->assertEquals( $actionData, $serialized['actions'][0]['data'] );
		$this->assertFalse( $serialized['actions'][0]['enabled'] );
	}

	public function testJsonSerializeWithEmptyArrays() {
		$automation = new Automation( [], [], [] );
		$serialized = $automation->jsonSerialize();

		$this->assertEquals( [], $serialized['triggers'] );
		$this->assertEquals( [], $serialized['pageFilters'] );
		$this->assertEquals( [], $serialized['actions'] );
		$this->assertTrue( $serialized['enabled'] );
	}

	public function testJsonSerializeMultipleItems() {
		$triggers = [
			$this->createMockTrigger( [ 'type' => 'edit' ] ),
			$this->createMockTrigger( [ 'type' => 'delete' ] ),
		];
		$filters = [
			$this->createMockPageFilter( [ 'namespaces' => [ 0 ] ] ),
			$this->createMockPageFilter( [ 'namespaces' => [ 1 ] ] ),
		];
		$actions = [
			$this->createMockAction( [ 'key' => 'action1' ] ),
			$this->createMockAction( [ 'key' => 'action2' ] ),
		];

		$automation = new Automation( $triggers, $filters, $actions );
		$serialized = $automation->jsonSerialize();

		$this->assertCount( 2, $serialized['triggers'] );
		$this->assertCount( 2, $serialized['pageFilters'] );
		$this->assertCount( 2, $serialized['actions'] );
	}
}
