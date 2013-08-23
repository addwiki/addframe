<?php

use Addframe\Mediawiki\Api;
use Addframe\Mediawiki\ApiRequest;
use Addframe\TestHttp;

class ApiTest extends PHPUnit_Framework_TestCase{

	function testCanConstruct( ){
		$api = new Api( );
		$this->assertInstanceOf( 'Addframe\Mediawiki\Api', $api );
	}

	/**
	 * @dataProvider provideUrls
	 */
	function testCanGetNewFromUrl( $url ){
		$api = Api::newFromUrl( $url );
		$api->setUrl( $url );
		$this->assertEquals( $url, $api->getUrl() );
	}

	/**
	 * @dataProvider provideUrls
	 */
	function testCanSetUrl( $url ){
		$api = new Api();
		$api->setUrl( $url );
		$this->assertEquals( $url, $api->getUrl() );
	}

	function provideUrls(){
		return array(
			array( 'localhost/mediawiki/api.php' ),
			array( '127.0.0.1/api.php' ),
			array( 'en.wikipedia.org/wiki/api.php' ),
		);
	}

	/**
	 * @dataProvider provideApiRequests
	 */
	function testCanDoRequest( ApiRequest $request ){
		$expected = array( 'key' => 'value' );

		$api = new Api( new TestHttp( $this->encodeData( $expected, $request->getFormat() ) ) );

		$api->setUrl( 'hostname' );
		$result = $api->doRequest( $request );

		$this->assertEquals( $expected, $result );
	}

	function provideApiRequests(){
		return array(
			//data, //post
			array( new ApiRequest()  ),
			array( new ApiRequest( array( 'param' => 'value' ) ) ),
			array( new ApiRequest( array( 'param' => 'value' ) ) ),
			array( new ApiRequest( array( 'param' => 'value' ), 'php' ) ),
			array( new ApiRequest( array( 'param' => 'value', 'param2' => 'value2' ), 'php' ) ),
			array( new ApiRequest( array( 'param' => 'value', 'param2' => 'value2' ), 'json' ) ),
		);
	}

	protected function encodeData( $data, $format ){
		switch ( $format ) {
			case 'php':
				return serialize( $data );
				break;
			case 'json':
				return json_encode( $data );
				break;
		}
		$this->fail( 'Can not test given data format in api response, see ApiTest::encodeData()' );
		return '';
	}

}