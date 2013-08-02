<?php

namespace Addframe;

/**
 * @since 0.0.2
 * @author Addshore
 */

class WikiText {

	protected $text;
	protected $isSet;

	function __construct( $text = null) {
		$this->text = $text;
	}

	public function setText( $text ) {
		$this->text = $text;
	}

	public function getText() {
		return $this->text;
	}

	public function appendText( $text ) {
		$this->text = $this->text.$text;
	}

	public function prependText( $text ) {
		$this->text = $text.$this->text;
	}

	public function emptyText() {
		$this->text = "";
	}

	/**
	 * Find a string
	 * @param $string string The string that you want to find.
	 * @return bool (true or false)
	 **/
	public function findString( $string ) {
		if ( strstr( $this->text, $string ) )
			return true; else
			return false;
	}

	/**
	 * Replace a string
	 * @param $string string The string that you want to replace.
	 * @param $newstring string The string that will replace the present string.
	 */
	public function replaceString( $string, $newstring ) {
		$this->text = str_replace( $string, $newstring, $this->text );
	}

	public function pregReplace( $patern, $replacment ) {
		$this->text = preg_replace( $patern, $replacment, $this->text );
	}

	public function removeRegexMatched( $patern ) {
		$this->pregReplace( $patern, '' );
	}

	public function getLength(){
		return strlen( $this->text );
	}

	//todo: this will currently match things like "ssh://123.456.789.com", We probably want to avoid that...
	public function getUrls() {
		preg_match_all( Regex::getUrlRegex(), $this->text, $matches );
		return $matches[0];
	}

	public function trimWhitespace(){
		$this->pregReplace( '/(\n\n)\n+$/', "$1" );
		$this->pregReplace( '/^(\n|\r){0,5}$/', "" );
	}
}