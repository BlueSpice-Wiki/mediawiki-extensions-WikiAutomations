<?php

namespace MediaWiki\Extension\WikiAutomations\Tests\PageFilter;

use MediaWiki\Extension\WikiAutomations\PageFilter\OnlyMajor;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\WikiAutomations\PageFilter\OnlyMajor
 */
class OnlyMajorTest extends TestCase {

	private function createMockRevisionLookup( ?bool $isMinor = null ): RevisionLookup {
		$revisionLookup = $this->createMock( RevisionLookup::class );

		if ( $isMinor !== null ) {
			$revision = $this->createMock( RevisionRecord::class );
			$revision->method( 'isMinor' )->willReturn( $isMinor );
			$revisionLookup->method( 'getRevisionByTitle' )->willReturn( $revision );
		} else {
			$revisionLookup->method( 'getRevisionByTitle' )->willReturn( null );
		}

		return $revisionLookup;
	}

	private function createMockPage(): PageIdentity {
		return $this->createMock( PageIdentity::class );
	}

	public function testPageFitsWhenOnlyMajorNotSet() {
		$revisionLookup = $this->createMockRevisionLookup( true );
		$filter = new OnlyMajor( $revisionLookup );

		$filter->setData( [] );

		$page = $this->createMockPage();
		$this->assertTrue( $filter->pageFits( $page ) );
	}

	public function testPageFitsWhenOnlyMajorFalse() {
		$revisionLookup = $this->createMockRevisionLookup( true );
		$filter = new OnlyMajor( $revisionLookup );

		$filter->setData( [ 'mustBeMajor' => false ] );

		$page = $this->createMockPage();
		$this->assertTrue( $filter->pageFits( $page ) );
	}

	public function testPageFitsForMajorEdit() {
		$revisionLookup = $this->createMockRevisionLookup( false );
		$filter = new OnlyMajor( $revisionLookup );

		$filter->setData( [ 'mustBeMajor' => true ] );

		$page = $this->createMockPage();
		$this->assertTrue( $filter->pageFits( $page ) );
	}

	public function testPageDoesNotFitForMinorEdit() {
		$revisionLookup = $this->createMockRevisionLookup( true );
		$filter = new OnlyMajor( $revisionLookup );

		$filter->setData( [ 'mustBeMajor' => true ] );

		$page = $this->createMockPage();
		$this->assertFalse( $filter->pageFits( $page ) );
	}

	public function testPageFitsWhenNoRevisionFound() {
		$revisionLookup = $this->createMockRevisionLookup( null );
		$filter = new OnlyMajor( $revisionLookup );

		$filter->setData( [ 'mustBeMajor' => true ] );

		$page = $this->createMockPage();
		$this->assertTrue( $filter->pageFits( $page ) );
	}

	public function testGetLayoutReturnsFormSpecification() {
		$revisionLookup = $this->createMockRevisionLookup();
		$filter = new OnlyMajor( $revisionLookup );

		$layout = $filter->getLayout();

		$this->assertNotNull( $layout );
		$this->assertInstanceOf( \MWStake\MediaWiki\Component\FormEngine\IFormSpecification::class, $layout );
	}

	public function testInheritsFromGenericPageFilter() {
		$revisionLookup = $this->createMockRevisionLookup();
		$filter = new OnlyMajor( $revisionLookup );

		$data = [ 'mustBeMajor' => true ];
		$filter->setData( $data );
		$this->assertEquals( $data, $filter->getData() );

		$filter->setEnabled( false );
		$this->assertFalse( $filter->isEnabled() );
	}
}
