<?php

namespace Addframe;

if( function_exists( 'mb_internal_encoding' ) ) {
	mb_internal_encoding( "UTF-8" );
}

spl_autoload_register( function ( $className ) {
	static $classes = false;

	if ( $classes === false ) {
		$classes = include( __DIR__ . '/' . 'Addframe.classes.php' );
	}

	if ( array_key_exists( $className, $classes ) ) {
		include_once __DIR__ . '/includes/' . $classes[$className];
	}
} );

//todo recursively load entry files for modules rather than defining them here
include_once __DIR__ . '/modules/WikibaseDataModel/WikibaseDataModel.php';
include_once __DIR__ . '/modules/DataValues/DataValues.php';
include_once __DIR__ . '/modules/Diff/Diff.php';

//todo it would be good to also echo the date of the current revision
echo "Running Addframe, branch '" . GitInfo::currentBranch() . "', commit '" . GitInfo::headSHA1() . "'\n";
GitInfo::destruct();