<?php
/**
 * Common used functions
 *
 * @filesource func_common.inc.php
 * @version    0.1.0
 * @link       https://github.com/codemasher/gw2-database/blob/master/inc/func_common.inc.php
 * @created    08.03.14
 *
 * @author     Smiley <smiley@chillerlan.net>
 * @copyright  Copyright (c) 2013 {@link https://twitter.com/codemasher Smiley} <smiley@chillerlan.net>
 * @license    http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */


/**
 * gpc_in
 *
 * Receive a GPC var and return it's value with filter applied
 *
 * @param string $name name of the var
 * @param string $mode get, post, cookie - defaults to get
 * @param string $type type of the var: string, int, float, escape, raw - defaults to string
 *
 * @return bool|string true if variable exists but empty, filtered value or false if not exist
 */
function gpc_in($name, $mode = 'get', $type = 'string'){
	$gpc = [
		'get'    => isset($_GET[$name]) ? $_GET[$name] : false,
		'post'   => isset($_POST[$name]) ? $_POST[$name] : false,
		'cookie' => isset($_COOKIE[$name]) ? $_COOKIE[$name] : false
	];

	$mode = strtolower($mode);

	if($gpc[$mode]){
		switch(strtolower($type)){
			case 'string':
				$filter = FILTER_SANITIZE_STRING;
				break;
			case 'int':
				$filter = FILTER_SANITIZE_NUMBER_INT;
				break;
			case 'float':
				$filter = FILTER_SANITIZE_NUMBER_FLOAT;
				break;
			case 'escape':
				$filter = FILTER_SANITIZE_SPECIAL_CHARS;
				break;
			case 'raw':
				$filter = FILTER_UNSAFE_RAW;
				break;
			default:
				$filter = FILTER_SANITIZE_STRING;
				break;
		}

		$var = filter_var($gpc[$mode], $filter);
		return empty($var) ? false : $var;
	}
	return false;
}

/**
 * gzoutput
 *
 * gzip output compression
 *
 * @param string $content
 *
 * @return string
 */
function gzoutput($content){
	global $conf;
	if(headers_sent()){
		$encoding = false;
	}
	else if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip')){
		$encoding = 'x-gzip';
	}
	else if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
		$encoding = 'gzip';
	}
	else{
		$encoding = false;
	}

	if($conf->var['output_gzip'] && $encoding){
		header('Content-Encoding: '.$encoding);
		$content = gzencode($content, 9);
	}
	return $content;
}

/**
 * minify
 *
 * Minify HTML and strip out any HTML-comments except IE conditionals
 *
 * @param string $html
 * @param bool   $force_minify
 *
 * @return string
 */
function minify($html, $force_minify = false){
	global $conf;
	if($conf->var['minify_html'] || $force_minify === true){
		$html = preg_replace('#<!--((?!\[if).*)-->#isU', '', $html);
		$html = str_replace(array("\r", "\n", "\t"), '', $html);
	}
	return $html;
}

/**
 * set_bitflag
 *
 * set a bitflag value
 *
 * @param array $flags
 *
 * @return int
 */
function set_bitflag($flags){
	$val = 0;
	foreach($flags as $flag){
		$val = $val|constant($flag);
	}
	return $val;
}

/**
 * get_bitflag
 *
 * get a bitflag value
 *
 * @param int $flag
 * @param int $val
 *
 * @return bool
 */
function get_bitflag($flag, $val){
	return ($val&$flag) === $flag;
}

/**
 * Logger
 *
 * Pass a line of text to store it into a logfile and display it in the CLI.
 *
 * @param string $line text to log
 * @param        $logfile
 * @param string $dateformat
 * @param bool   $echo output to console y/n
 */
function write_log($line, $logfile, $dateformat = '[Y-m-d H:i:s]', $echo = false){
	$line = date($dateformat).' '.$line.PHP_EOL;
	$logfile = fopen(BASEDIR.'logs/'.date('Y-m-d').'-'.$logfile, 'a');
	fwrite($logfile, $line);
	fclose($logfile);
	if($echo){
		echo $line;
	}
}

/**
 * @param string $old
 * @param string $new
 *
 * @return bool
 */
function diff($old, $new){
	return sha1($old) !== sha1($new);
}

