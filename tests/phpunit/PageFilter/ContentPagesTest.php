<?php

namespace MediaWiki\Extension\WikiAutomations\Tests\PageFilter;

use MediaWiki\Extension\WikiAutomations\PageFilter\ContentPages;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Title\NamespaceInfo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\WikiAutomations\PageFilter\ContentPages
 */
class ContentPagesTest extends TestCase {

	private function createMockNamespaceInfo( array $contentNamespaces = [ NS_MAIN ] ): NamespaceInfo {
		$nsInfo = $this->createMock( NamespaceInfo::class );
		$nsInfo->method( 'isContent' )
			->willReturnCallback( static function ( $ns ) use ( $contentNamespaces ) {
				return in_array( $ns, $contentNamespaces, true );
			} );
		return $nsInfo;
	}

	private function createMockPage( int $namespace ): PageIdentity {
		$page = $this->createMock( PageIdentity::class );
		$page->method( 'getNamespace' )->willReturn( $namespace );
		return $page;
	}

	public function testPageFitsWhenIsContentNotSet() {
		$nsInfo = $this->createMockNamespaceInfo();
		$filter = new ContentPages( $nsInfo );

		$filter->setData( [] );

		$page = $this->createMockPage( NS_MAIN );
		$this->assertTrue( $filter->pageFits( $page ) );

		$page = $this->createMockPage( NS_TALK );
		$this->assertTrue( $filter->pageFits( $page ) );
	}

	public function testPageFitsWhenIsContentFalse() {
		$nsInfo = $this->createMockNamespaceInfo();
		$filter = new ContentPages( $nsInfo );

		$filter->setData( [ 'isContent' => false ] );

		$page = $this->createMockPage( NS_MAIN );
		$this->assertTrue( $filter->pageFits( $page ) );

		$page = $this->createMockPage( NS_TALK );
		$this->assertTrue( $filter->pageFits( $page ) );
	}

	public function testPageFitsOnlyContentNamespaces() {
		$nsInfo = $this->createMockNamespaceInfo( [ NS_MAIN, NS_PROJECT ] );
		$filter = new ContentPages( $nsInfo );

		$filter->setData( [ 'isContent' => true ] );

		$this->assertTrue( $filter->pageFits( $this->createMockPage( NS_MAIN ) ) );
		$this->assertTrue( $filter->pageFits( $this->createMockPage( NS_PROJECT ) ) );
		$this->assertFalse( $filter->pageFits( $this->createMockPage( NS_TALK ) ) );
		$this->assertFalse( $filter->pageFits( $this->createMockPage( NS_USER ) ) );
	}

	public function testPageFitsWithMultipleContentNamespaces() {
		$contentNs = [ NS_MAIN, NS_PROJECT, NS_FILE ];
		$nsInfo = $this->createMockNamespaceInfo( $contentNs );
		$filter = new ContentPages( $nsInfo );

		$filter->setData( [ 'isContent' => true ] );

		foreach ( $contentNs as $ns ) {
			$this->assertTrue(
				$filter->pageFits( $this->createMockPage( $ns ) ),
				"Page in namespace $ns should fit"
			);
		}

		$this->assertFalse( $filter->pageFits( $this->createMockPage( NS_CATEGORY ) ) );
	}

	public function testGetLayoutReturnsFormSpecification() {
		$nsInfo = $this->createMockNamespaceInfo();
		$filter = new ContentPages( $nsInfo );

		$layout = $filter->getLayout();

		$this->assertNotNull( $layout );
		$this->assertInstanceOf( \MWStake\MediaWiki\Component\FormEngine\IFormSpecification::class, $layout );
	}

	public function testInheritsFromGenericPageFilter() {
		$nsInfo = $this->createMockNamespaceInfo();
		$filter = new ContentPages( $nsInfo );

		$data = [ 'isContent' => true ];
		$filter->setData( $data );
		$this->assertEquals( $data, $filter->getData() );

		$filter->setEnabled( false );
		$this->assertFalse( $filter->isEnabled() );
	}
}
