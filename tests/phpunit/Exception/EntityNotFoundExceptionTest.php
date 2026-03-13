<?php

namespace MediaWiki\Extension\WikiAutomations\Tests\Exception;

use MediaWiki\Extension\WikiAutomations\Exception\EntityNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\WikiAutomations\Exception\EntityNotFoundException
 */
class EntityNotFoundExceptionTest extends TestCase {

	public function testConstructorSetsMessage() {
		$exception = new EntityNotFoundException( 'trigger', 'edit' );

		$this->assertEquals( 'Requested trigger "edit" not found', $exception->getMessage() );
	}

	public function testConstructorSetsCode() {
		$exception = new EntityNotFoundException( 'trigger', 'edit' );

		$this->assertEquals( 404, $exception->getCode() );
	}

	public function testWithDifferentEntityTypes() {
		$triggerException = new EntityNotFoundException( 'trigger', 'schedule' );
		$this->assertEquals( 'Requested trigger "schedule" not found', $triggerException->getMessage() );

		$filterException = new EntityNotFoundException( 'page_filter', 'namespace' );
		$this->assertEquals( 'Requested page_filter "namespace" not found', $filterException->getMessage() );

		$actionException = new EntityNotFoundException( 'action', 'test_action' );
		$this->assertEquals( 'Requested action "test_action" not found', $actionException->getMessage() );
	}

	public function testIsInstanceOfException() {
		$exception = new EntityNotFoundException( 'trigger', 'edit' );

		$this->assertInstanceOf( \Exception::class, $exception );
	}
}
