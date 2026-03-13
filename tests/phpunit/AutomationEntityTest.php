<?php

namespace MediaWiki\Extension\WikiAutomations\Tests;

use MediaWiki\Extension\WikiAutomations\AutomationEntity;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\WikiAutomations\AutomationEntity
 */
class AutomationEntityTest extends TestCase {

	private function getConcreteEntity(): AutomationEntity {
		return new class extends AutomationEntity {
			public function getLayout(): ?\MWStake\MediaWiki\Component\FormEngine\IFormSpecification {
				return null;
			}
		};
	}

	public function testDefaultEnabled() {
		$entity = $this->getConcreteEntity();
		$this->assertTrue( $entity->isEnabled() );
	}

	public function testSetAndGetData() {
		$entity = $this->getConcreteEntity();
		$data = [ 'key' => 'value', 'number' => 42 ];

		$entity->setData( $data );
		$this->assertEquals( $data, $entity->getData() );
	}

	public function testSetAndGetEnabled() {
		$entity = $this->getConcreteEntity();

		$entity->setEnabled( false );
		$this->assertFalse( $entity->isEnabled() );

		$entity->setEnabled( true );
		$this->assertTrue( $entity->isEnabled() );
	}

	public function testDataDefaultsToEmptyArray() {
		$entity = $this->getConcreteEntity();
		$this->assertEquals( [], $entity->getData() );
	}

	public function testSetDataOverwritesPreviousData() {
		$entity = $this->getConcreteEntity();

		$entity->setData( [ 'old' => 'data' ] );
		$entity->setData( [ 'new' => 'data' ] );

		$this->assertEquals( [ 'new' => 'data' ], $entity->getData() );
		$this->assertArrayNotHasKey( 'old', $entity->getData() );
	}

	public function testSetDataWithEmptyArray() {
		$entity = $this->getConcreteEntity();
		$entity->setData( [ 'initial' => 'data' ] );
		$entity->setData( [] );

		$this->assertEquals( [], $entity->getData() );
	}
}
