<?php
/**
 *
 * @filesource   functions.php
 * @created      18.02.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DBCLI;

/**
 * Checks wether the script is running in CLI mode.
 */
if(!function_exists('is_cli')){
	function is_cli(){
		return !isset($_SERVER['SERVER_SOFTWARE']) && (PHP_SAPI === 'cli' || (is_numeric($_SERVER['argc']) && $_SERVER['argc'] > 0));
	}
}
