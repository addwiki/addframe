<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Family;
use Addframe\Mediawiki\TestApi;

/**
 * Class FamilyTest
 * @covers Addframe\Mediawiki\Family
 */
class FamilyTest extends MediawikiTestCase{

	public function testConstruct(){
		$family = new Family();
		$this->assertInstanceOf( 'Addframe\Mediawiki\Family', $family );
	}

	public function provideSitematrixData(){
		return array( array( 'sitematrix/wikimedia.json' ), array( 'sitematrix/empty.json' ) ) ;
	}

	/**
	 * @dataProvider provideSitematrixData
	 */
	public function testGetSiteMatrix( $dataLocation ){
		$expectedJson = $this->getTestApiData( $dataLocation );
		$expectedArray = json_decode( $expectedJson, true );
		$family = new Family();
		$family->setApi( new TestApi( $expectedJson ) );
		$sitematrix = $family->getSiteMatrix();
		$this->assertEquals( $expectedArray['sitematrix'], $sitematrix );
	}

	/**
	 * @dataProvider provideSitematrixData
	 */
	public function testGetSiteListReturnsSiteList( $dataLocation ){
		$expectedJson = $this->getTestApiData( $dataLocation );
		$expectedArray = json_decode( $expectedJson, true );
		$family = new Family();
		$family->setApi( new TestApi( $expectedJson ) );
		$sitelist = $family->getSiteList();
		$this->assertInstanceOf( 'Addframe\Mediawiki\SiteList', $sitelist );
		$this->assertEquals( $expectedArray['sitematrix']['count'], $sitelist->count() ); //as defined in the json
	}

	public function testFamilyHasSite(){
		$expectedJson = $this->getTestApiData( 'sitematrix/wikimedia.json' );
		$family = new Family();
		$family->setApi( new TestApi( $expectedJson ) );
		$family->getSiteList();

		$this->assertFalse( $family->hasSite( 'foo' ) );
		$this->assertTrue( $family->hasSite( 'en.wikipedia.org' ) );
	}

	public function provideSiteListFilter(){
		/*
		 * In the site matrix we are testing we can use the following sites to test
		 * active = en.wikipedia.org
		 * closed = kr.wikipedia.org
		 * private = office.wikimedia.org
		 * fishbowl = rs.wikimedia.org
		 */
		return array(
			//filter, //shouldhave, //shouldnt have
			array( null,
				array( 'en.wikipedia.org', 'kr.wikipedia.org', 'office.wikimedia.org', 'rs.wikimedia.org' ),
				array( 'foo' ) ),
			array( 'active',
				array( 'en.wikipedia.org' ),
				array( 'foo', 'kr.wikipedia.org', 'office.wikimedia.org', 'rs.wikimedia.org' ) ),
			array( 'closed',
				array( 'kr.wikipedia.org' ),
				array( 'foo', 'en.wikipedia.org', 'office.wikimedia.org', 'rs.wikimedia.org' ) ),
			array( 'private',
				array( 'office.wikimedia.org' ),
				array( 'foo', 'kr.wikipedia.org', 'en.wikipedia.org', 'rs.wikimedia.org' ) ),
			array( 'fishbowl',
				array( 'rs.wikimedia.org' ),
				array( 'foo', 'kr.wikipedia.org', 'office.wikimedia.org', 'en.wikipedia.org' ) ),
			array( array( 'active', 'closed' ),
				array( 'en.wikipedia.org', 'kr.wikipedia.org' ),
				array( 'foo', 'office.wikimedia.org', 'rs.wikimedia.org' ) ),
			array( array( 'active', 'closed', 'private', 'fishbowl' ),
				array( 'en.wikipedia.org', 'kr.wikipedia.org', 'office.wikimedia.org', 'rs.wikimedia.org' ),
				array( 'foo' ) ),
		) ;
	}

	/**
	 * @dataProvider provideSiteListFilter
	 */
	public function testGetSiteListFilters( $filter, $has, $hasnt ){
		$expectedJson = $this->getTestApiData( 'sitematrix/wikimedia.json' );
		$family = new Family();
		$family->setApi( new TestApi( $expectedJson ) );
		$partialSiteList = $family->getSiteList( $filter );

		foreach( $has as $url ){
			$this->assertTrue( $partialSiteList->hasSite( $url ) );
		}
		foreach( $hasnt as $url ){
			$this->assertFalse( $partialSiteList->hasSite( $url ) );
		}
	}

	public function provideFilterTypes(){
		return array(
			array( 'closed' ),
			array( 'private' ),
			array( 'fishbowl' ),
			array( 'active' ),
			array( 'ACTIVE' ),
		);
	}

	/**
	 * @dataProvider provideFilterTypes
	 */
	public function testGetSiteListFiltersWhenEmpty( $type ){
		$expectedJson = $this->getTestApiData( 'sitematrix/empty.json' );
		$family = new Family();
		$family->setApi( new TestApi( $expectedJson ) );

		$partialSiteList = $family->getSiteList( $type );
		$this->assertInstanceOf( 'Addframe\Mediawiki\SiteList', $partialSiteList );
		$this->assertEquals( 0, $partialSiteList->count() );
	}

	public function testGetSiteListWithBadFilter( ){
		$this->setExpectedException( '\UnexpectedValueException' );
		$expectedJson = $this->getTestApiData( 'sitematrix/empty.json' );
		$family = new Family();
		$family->setApi( new TestApi( $expectedJson ) );
		$family->getSiteList( 'Foo' );
	}

}