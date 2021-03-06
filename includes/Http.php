<?php

namespace Addframe;

/**
 * This class is designed to provide a simplified interface to cURL which maintains cookies.
 * @author Cobi
 * @author Addshore
 **/
class Http {

	protected $ch;
	protected $uid;
	public $cookie_jar;
	public $postfollowredirs;
	public $getfollowredirs;

	/**
	 * @param $data array of data to be encoded
	 * @return null|string
	 */
	public function encodeData( $data ) {
		assert( is_array( $data ) );
		$vars = null;
		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) )
				$vars .= $this->encodeData( $value, $key . urlencode( "[" ), urlencode( "]" ) ); else
				$vars .= $key . "=" . urlencode( $value ) . "&";
		}
		return trim( $vars , '&' );
	}


	/**
	 * @return string the Uid for this http instance
	 */
	public function getUid(){
		return $this->uid;
	}

	/**
	 * This should generally not be used, get the default instance
	 */
	 /* protected */ function __construct() {
		$this->ch = curl_init();
		$this->uid = dechex( rand( 0, 99999999 ) );
		curl_setopt( $this->ch, CURLOPT_COOKIEJAR, '/tmp/addframe.cookies.' . $this->uid . '.dat' );
		curl_setopt( $this->ch, CURLOPT_COOKIEFILE, '/tmp/addframe.cookies.' . $this->uid . '.dat' );
		curl_setopt( $this->ch, CURLOPT_MAXCONNECTS, 100 );
		curl_setopt( $this->ch, CURLOPT_CLOSEPOLICY, CURLCLOSEPOLICY_LEAST_RECENTLY_USED );
		$this->postfollowredirs = 0;
		$this->getfollowredirs = 1;
		$this->cookie_jar = array();
	}

	/**
	 * @param $url string url of request
	 * @param $data array of data to post key => value
	 * @param int $attempts
	 * @throws HttpException
	 * @return string result of request
	 */
	public function post( $url, $data, $attempts = 3 ) {
		curl_setopt( $this->ch, CURLOPT_URL, $url );
		curl_setopt( $this->ch, CURLOPT_USERAGENT, 'Addframe' );
		curl_setopt( $this->ch, CURLOPT_ENCODING, "UTF-8" );
		/* Crappy hack to add extra cookies, should be cleaned up */
		$cookies = null;
		foreach ( $this->cookie_jar as $name => $value ) {
			if ( empty( $cookies ) )
				$cookies = "$name=$value"; else
				$cookies .= "; $name=$value";
		}
		if ( $cookies != null )
			curl_setopt( $this->ch, CURLOPT_COOKIE, $cookies );
		curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, $this->postfollowredirs );
		curl_setopt( $this->ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $this->ch, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
		curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $this->ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt( $this->ch, CURLOPT_CONNECTTIMEOUT, 10 );
		curl_setopt( $this->ch, CURLOPT_POST, 1 );
		curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $data );

		Logger::logDebug( "HTTP post {$url}?".self::encodeData($data) );
		$result = curl_exec( $this->ch );

		if( curl_errno ( $this->ch ) === 0 ){
			return $result;
		// todo the below repeated attempts are not tested
		} else if( curl_errno ( $this->ch ) === 28 && $attempts > 0 ) { //timeout
			return $this->post( $url, $data, $attempts - 1 );
		} else {
			throw new HttpException( 'Http post curl unexpected error code '.curl_errno ( $this->ch )." for {$url}?".self::encodeData($data) );
		}
	}

	/**
	 * @param $url string url to get
	 * @param int $attempts maximum number of attempts to make
	 * @throws HttpException
	 * @return string result of request
	 */
	public function get( $url, $attempts = 3 ) {
		curl_setopt( $this->ch, CURLOPT_URL, $url );
		curl_setopt( $this->ch, CURLOPT_USERAGENT, 'Addframe' );
		curl_setopt( $this->ch, CURLOPT_ENCODING, "UTF-8" );
		/* Crappy hack to add extra cookies, should be cleaned up */
		$cookies = null;
		foreach ( $this->cookie_jar as $name => $value ) {
			if ( empty( $cookies ) )
				$cookies = "$name=$value"; else
				$cookies .= "; $name=$value";
		}
		if ( $cookies != null )
			curl_setopt( $this->ch, CURLOPT_COOKIE, $cookies );
		curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, $this->getfollowredirs );
		curl_setopt( $this->ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $this->ch, CURLOPT_HEADER, 0 );
		curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $this->ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt( $this->ch, CURLOPT_CONNECTTIMEOUT, 10 );
		curl_setopt( $this->ch, CURLOPT_HTTPGET, 1 );

		Logger::logDebug( "HTTP get {$url}" );
		$result = curl_exec( $this->ch );

		if( curl_errno ( $this->ch ) === 0 ){
			return $result;
		// todo the below repeated attempts are not tested
		} else if( curl_errno ( $this->ch ) === 28 && $attempts > 0 ) { // timeout
			return $this->get( $url, $attempts - 1 );
		} else {
			throw new HttpException( 'Http get curl unexpected error code '.curl_errno ( $this->ch )." for {$url}" );
		}
	}

	/**
	 * @param $uname string
	 * @param $pwd string
	 */
	function setHTTPcreds( $uname, $pwd ) {
		curl_setopt( $this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		curl_setopt( $this->ch, CURLOPT_USERPWD, $uname . ":" . $pwd );
	}


	/**
	 * Get rid of any tokens when we destruct
	 */
	function __destruct() {
		curl_close( $this->ch );
		@unlink( '/tmp/addframe.cookies.' . $this->uid . '.dat' );
	}

	/*
 	* The below allows us to only have one instance of this class
 	*/
	static $defaultInstance = null;
	static function getDefaultInstance(){
		if( is_null( self::$defaultInstance ) ){
			self::$defaultInstance = new Http();
		}
		return self::$defaultInstance;
	}

}

/**
 * Class HttpException
 */
class HttpException extends \Exception {

}

/**
 * Class TestHttp, Overrides used methods in HTTP so we can return some default data
 */
class TestHttp extends Http{

	protected $returnData;

	/**
	 * @param array|string $returnData as a string
	 */
	public function __construct( $returnData = '' ) {
			$this->returnData = $returnData;
	}

	/**
	 * Returns the data defined in the constructor
	 */
	public function post( $url, $data, $attempts = 3 ) {
		if( is_array( $this->returnData ) ){
			return array_shift( $this->returnData );
		}
		return $this->returnData;
	}

	/**
	 * Returns the data defined in the constructor
	 */
	public function get( $url, $attempts = 3 ) {
		if( is_array( $this->returnData ) ){
			return array_shift( $this->returnData );
		}
		return $this->returnData;
	}

	/**
	 * Override the main class __destruct method
	 */
	function __destruct() {}

}