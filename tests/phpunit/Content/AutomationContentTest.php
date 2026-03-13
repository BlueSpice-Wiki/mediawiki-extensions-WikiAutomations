<?php

namespace MediaWiki\Extension\WikiAutomations\Tests\Content;

use MediaWiki\Extension\WikiAutomations\Content\AutomationContent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\WikiAutomations\Content\AutomationContent
 */
class AutomationContentTest extends TestCase {

	public function testConstructorSetsContentModel() {
		$data = '{"triggers":[],"filters":[],"actions":[]}';
		$content = new AutomationContent( $data );

		$this->assertEquals( 'automation', $content->getModel() );
	}

	public function testConstructorSetsData() {
		$data = '{"triggers":[],"filters":[],"actions":[]}';
		$content = new AutomationContent( $data );

		$this->assertEquals( $data, $content->getText() );
	}

	public function testWithEmptyString() {
		$content = new AutomationContent( '' );

		$this->assertSame( '', $content->getText() );
		$this->assertEquals( 'automation', $content->getModel() );
	}

	public function testWithComplexJson() {
		$data = json_encode( [
			'triggers' => [
				'edit' => [
					'data' => [ 'type' => 'edit' ],
					'enabled' => true
				]
			],
			'filters' => [
				'namespace' => [ 'namespaces' => [ 0, 1, 2 ] ]
			],
			'actions' => [
				[
					'key' => 'test_action',
					'data' => [ 'param' => 'value' ],
					'enabled' => true
				]
			],
			'enabled' => true
		] );

		$content = new AutomationContent( $data );

		$this->assertEquals( $data, $content->getText() );
		$this->assertTrue( $content->isValid() );
	}

	public function testInvalidJsonIsStillStored() {
		$invalidJson = '{"invalid": json}';
		$content = new AutomationContent( $invalidJson );

		$this->assertEquals( $invalidJson, $content->getText() );
		$this->assertFalse( $content->isValid() );
	}
}
