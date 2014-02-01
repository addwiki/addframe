<?php

namespace Addframe;

/**
 * A class to help return information about a git repo we may be inside
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @source mediawiki/includes/GitInfo.php
 */
class GitInfo {

	/**
	 * Singleton for the repo at $IP
	 */
	protected static $repo = null;

	/**
	 * Location of the .git directory
	 */
	protected $basedir;

	/**
	 * @param string $dir The root directory of the repo where the .git dir can be found
	 */
	public function __construct( $dir ) {
		$this->basedir = "{$dir}/.git";
		if ( is_readable( $this->basedir ) && !is_dir( $this->basedir ) ) {
			$GITfile = file_get_contents( $this->basedir );
			if ( strlen( $GITfile ) > 8 && substr( $GITfile, 0, 8 ) === 'gitdir: ' ) {
				$path = rtrim( substr( $GITfile, 8 ), "\r\n" );
				$isAbsolute = $path[0] === '/' || substr( $path, 1, 1 ) === ':';
				$this->basedir = $isAbsolute ? $path : "{$dir}/{$path}";
			}
		}
	}

	/**
	 * Return a singleton for the repo at $IP
	 * @return GitInfo
	 */
	public static function repo() {
		if ( is_null( self::$repo ) ) {
			//todo this directory should be defined using a global so if the file moves it doesnt break
			self::$repo = new self( __DIR__.'/..' );
		}
		return self::$repo;
	}

	/**
	 * Check if a string looks like a hex encoded SHA1 hash
	 *
	 * @param string $str The string to check
	 * @return bool Whether or not the string looks like a SHA1
	 */
	public static function isSHA1( $str ) {
		return !!preg_match( '/^[0-9A-F]{40}$/i', $str );
	}

	/**
	 * Return the HEAD of the repo (without any opening "ref: ")
	 * @return string The HEAD
	 */
	public function getHead() {
		$HEADfile = "{$this->basedir}/HEAD";

		if ( !is_readable( $HEADfile ) ) {
			return false;
		}

		$HEAD = file_get_contents( $HEADfile );

		if ( preg_match( "/ref: (.*)/", $HEAD, $m ) ) {
			return rtrim( $m[1] );
		} else {
			return rtrim( $HEAD );
		}
	}

	/**
	 * Return the SHA1 for the current HEAD of the repo
	 * @return string A SHA1 or false
	 */
	public function getHeadSHA1() {
		$HEAD = $this->getHead();

		// If detached HEAD may be a SHA1
		if ( self::isSHA1( $HEAD ) ) {
			return $HEAD;
		}

		// If not a SHA1 it may be a ref:
		$REFfile = "{$this->basedir}/{$HEAD}";
		if ( !is_readable( $REFfile ) ) {
			return false;
		}

		$sha1 = rtrim( file_get_contents( $REFfile ) );

		return $sha1;
	}

	/**
	 * Return the name of the current branch, or HEAD if not found
	 * @return string The branch name, HEAD, or false
	 */
	public function getCurrentBranch() {
		$HEAD = $this->getHead();
		if ( $HEAD && preg_match( "#^refs/heads/(.*)$#", $HEAD, $m ) ) {
			return $m[1];
		} else {
			return $HEAD;
		}
	}

	/**
	 * @see self::getHeadSHA1
	 * @return string
	 */
	public static function headSHA1() {
		return self::repo()->getHeadSHA1();
	}

	/**
	 * @see self::getCurrentBranch
	 * @return string
	 */
	public static function currentBranch() {
		return self::repo()->getCurrentBranch();
	}

	public static function destruct() {
		return self::$repo = null;
	}
}
