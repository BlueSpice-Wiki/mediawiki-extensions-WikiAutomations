<?php

namespace MediaWiki\Extension\WikiAutomations\Tests\PageFilter;

use MediaWiki\Extension\WikiAutomations\PageFilter\NamespaceFilter;
use MediaWiki\Language\Language;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Title\NamespaceInfo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\WikiAutomations\PageFilter\NamespaceFilter
 */
class NamespaceFilterTest extends TestCase {

	private function createMockNamespaceInfo( array $contentNamespaces = [ NS_MAIN, NS_PROJECT ] ): NamespaceInfo {
		$nsInfo = $this->createMock( NamespaceInfo::class );
		$nsInfo->method( 'getContentNamespaces' )->willReturn( $contentNamespaces );
		$nsInfo->method( 'getCanonicalName' )
			->willReturnCallback( static function ( $ns ) {
				$names = [ NS_MAIN => '', NS_PROJECT => 'Project' ];
				return $names[$ns] ?? "NS_$ns";
			} );
		return $nsInfo;
	}

	private function createMockLanguage(): Language {
		$lang = $this->createMock( Language::class );
		$lang->method( 'getNsText' )
			->willReturnCallback( static function ( $ns ) {
				$names = [ NS_MAIN => '', NS_PROJECT => 'Project' ];
				return $names[$ns] ?? '';
			} );
		$lang->method( 'getCode' )->willReturn( 'en' );
		return $lang;
	}

	private function createMockPage( int $namespace ): PageIdentity {
		$page = $this->createMock( PageIdentity::class );
		$page->method( 'getNamespace' )->willReturn( $namespace );
		return $page;
	}

	public function testPageFitsWithEmptyNamespaces() {
		$nsInfo = $this->createMockNamespaceInfo();
		$lang = $this->createMockLanguage();
		$filter = new NamespaceFilter( $nsInfo, $lang );

		$filter->setData( [] );

		$page = $this->createMockPage( NS_MAIN );
		$this->assertTrue( $filter->pageFits( $page ) );
	}

	public function testPageFitsWithMatchingNamespace() {
		$nsInfo = $this->createMockNamespaceInfo();
		$lang = $this->createMockLanguage();
		$filter = new NamespaceFilter( $nsInfo, $lang );

		$filter->setData( [ 'namespaces' => [ NS_MAIN, NS_PROJECT ] ] );

		$page = $this->createMockPage( NS_MAIN );
		$this->assertTrue( $filter->pageFits( $page ) );
	}

	public function testPageDoesNotFitWithNonMatchingNamespace() {
		$nsInfo = $this->createMockNamespaceInfo();
		$lang = $this->createMockLanguage();
		$filter = new NamespaceFilter( $nsInfo, $lang );

		$filter->setData( [ 'namespaces' => [ NS_PROJECT ] ] );

		$page = $this->createMockPage( NS_MAIN );
		$this->assertFalse( $filter->pageFits( $page ) );
	}

	public function testPageFitsWithMultipleNamespaces() {
		$nsInfo = $this->createMockNamespaceInfo();
		$lang = $this->createMockLanguage();
		$filter = new NamespaceFilter( $nsInfo, $lang );

		$filter->setData( [ 'namespaces' => [ 0, 1, 2, 3 ] ] );

		$this->assertTrue( $filter->pageFits( $this->createMockPage( 0 ) ) );
		$this->assertTrue( $filter->pageFits( $this->createMockPage( 1 ) ) );
		$this->assertTrue( $filter->pageFits( $this->createMockPage( 2 ) ) );
		$this->assertFalse( $filter->pageFits( $this->createMockPage( 4 ) ) );
	}

	public function testGetLayoutReturnsFormSpecification() {
		$nsInfo = $this->createMockNamespaceInfo( [ NS_MAIN, NS_PROJECT ] );
		$lang = $this->createMockLanguage();
		$filter = new NamespaceFilter( $nsInfo, $lang );

		$layout = $filter->getLayout();
		$this->assertNotNull( $layout );
	}

	public function testGetLayoutReturnsNullForEmptyNamespaces() {
		$nsInfo = $this->createMockNamespaceInfo( [] );
		$lang = $this->createMockLanguage();
		$filter = new NamespaceFilter( $nsInfo, $lang );

		$layout = $filter->getLayout();
		$this->assertNull( $layout );
	}
}
