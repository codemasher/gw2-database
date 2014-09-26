<?php
/**
 * Common settings
 *
 * This file loads all common includes, settings and initializes some basic global objects
 *
 * @filesource common.php
 * @version    0.1.0
 * @link       https://github.com/codemasher/gw2-database/blob/master/common.php
 * @created    08.03.14
 *
 * @author     Smiley <smiley@chillerlan.net>
 * @copyright  Copyright (c) 2013 {@link https://twitter.com/codemasher Smiley} <smiley@chillerlan.net>
 * @license    http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */


// global .ini settings
error_reporting(E_ALL);
date_default_timezone_set('UTC'); //nasty
mb_internal_encoding('UTF-8');

/**
 * Unset unnessecary variables and throw an error if something attempts to set illegal/unwanted ones
 */

// Save some memory.. (since we don't use these anyway.)
unset($GLOBALS['HTTP_POST_VARS'], $GLOBALS['HTTP_POST_VARS']);
unset($GLOBALS['HTTP_POST_FILES'], $GLOBALS['HTTP_POST_FILES']);

// These keys shouldn't be set...ever.
if(isset($_REQUEST['GLOBALS']) || isset($_COOKIE['GLOBALS'])){
	exit('THE HIVE CLUSTER IS UNDER ATTACK');
}

// Same goes for numeric keys.
foreach(array_merge(array_keys($_POST), array_keys($_GET), array_keys($_FILES)) as $key){
	if(is_numeric($key)){
		exit('THE HIVE CLUSTER IS UNDER ATTACK');
	}
}

// Numeric keys in cookies are less of a problem. Just unset those.
foreach($_COOKIE as $key => $value){
	if(is_numeric($key)){
		unset($_COOKIE[$key]);
	}
}

// Get the correct query string.  It may be in an environment variable...
if(!isset($_SERVER['QUERY_STRING'])){
	$_SERVER['QUERY_STRING'] = getenv('QUERY_STRING');
}


// set base paths
define('BASEDIR', dirname(__FILE__).'/');
define('INST_ROOT', parse_url(getenv('REQUEST_URI'), PHP_URL_PATH));
define('CLASSDIR', BASEDIR.'classes/');
define('INCLUDEDIR', BASEDIR.'inc/');

set_include_path(get_include_path().PATH_SEPARATOR.CLASSDIR.PATH_SEPARATOR.INCLUDEDIR);

// autoload classes
spl_autoload_extensions('.class.php');
spl_autoload_register();

require_once(INCLUDEDIR.'config.inc.php');
require_once(INCLUDEDIR.'func_common.inc.php');

// init stuff
$db = new SQL();
$db->connect($mysql['server'], $mysql['user'], $mysql['password'], $mysql['dbname']);

$conf = new Config();
