<?php

namespace MediaWiki\Extension\WikiAutomations\Tests\Trigger;

use MediaWiki\Extension\WikiAutomations\Trigger\PageEventTrigger;
use MediaWiki\Page\PageIdentity;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\WikiAutomations\Trigger\PageEventTrigger
 * @covers \MediaWiki\Extension\WikiAutomations\Trigger\GenericTrigger
 */
class PageEventTriggerTest extends TestCase {

	private function createMockPage(): PageIdentity {
		return $this->createMock( PageIdentity::class );
	}

	public function testProvidePagesReturnsEmptyArrayByDefault() {
		$trigger = new PageEventTrigger();
		$this->assertEquals( [], $trigger->providePages() );
	}

	public function testSetAndProvidePages() {
		$trigger = new PageEventTrigger();
		$pages = [ $this->createMockPage(), $this->createMockPage() ];

		$trigger->setPages( $pages );

		$this->assertEquals( $pages, $trigger->providePages() );
		$this->assertCount( 2, $trigger->providePages() );
	}

	public function testSetPagesOverwritesPreviousPages() {
		$trigger = new PageEventTrigger();

		$firstPages = [ $this->createMockPage() ];
		$trigger->setPages( $firstPages );

		$secondPages = [ $this->createMockPage(), $this->createMockPage() ];
		$trigger->setPages( $secondPages );

		$this->assertEquals( $secondPages, $trigger->providePages() );
		$this->assertCount( 2, $trigger->providePages() );
	}

	public function testGetLayoutReturnsNull() {
		$trigger = new PageEventTrigger();
		$this->assertNull( $trigger->getLayout() );
	}

	public function testInheritsFromAutomationEntity() {
		$trigger = new PageEventTrigger();

		$trigger->setEnabled( false );
		$this->assertFalse( $trigger->isEnabled() );

		$data = [ 'config' => 'value' ];
		$trigger->setData( $data );
		$this->assertEquals( $data, $trigger->getData() );
	}
}
