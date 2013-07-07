<?php

/**
 * Class SiteFactory
 * Creates sites and adds them to the site registry
 */
class SiteFactory extends Registry{

	/**
	 * @param $handel string of site
	 * @param null $hostname
	 * @param null $api
	 */
	function newSite( $handel, $hostname = null, $api = null){
		if(isset($hostname) && isset($api)){
			$this->$handel = new mediawiki($hostname,$api);
		}
		else{
			//@todo lookup site from a sitematrix kind of thing
		}
	}
}