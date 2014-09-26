<?php
/**
 * Global config
 *
 * @filesource config.class.php
 * @version    0.1.0
 * @link       https://github.com/codemasher/gw2-database/blob/master/classes/config.class.php
 * @created    26.12.13
 *
 * @author     {@link https://twitter.com/codemasher Smiley} <smiley@chillerlan.net>
 * @copyright  Copyright (c) 2014 Smiley <smiley@chillerlan.net>
 * @license    http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */


/**
 * Class Config
 */
class Config{
	/**
	 * special config vars which shall not be written into DB
	 * @var array
	 */
	public $var = [];

	/**
	 * Constructor
	 *
	 * @global object $db
	 */
	public function __construct(){
		global $db;

		// load database config
		$q = $db->simple_query('SELECT * FROM '.TBL_CONFIG);
		foreach($q as $v){
			// convert string true and false to actual boolean values
			if(strtolower($v['value']) === 'true'){
				$this->var[$v['variable']] = true;
			}
			else if(strtolower($v['value']) === 'false'){
				$this->var[$v['variable']] = false;
			}
			else{
				$this->var[$v['variable']] = $v['value'];
			}
		}

		// set some special config values

		// set the default language
		$this->var['lang'] = $this->var['default_lang'];

		// are we using ssl?
		$this->var['ssl'] = (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === 443);

		// get the absolute URL of the installation - sort of.
		$this->var['inst_url'] = '//'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').INST_ROOT;
		// make sure it has a trailing slash
		$this->var['inst_url'] .= substr($this->var['inst_url'], -1) !== '/' ? '/' : '';


		//for debug purposes
		ksort($this->var, SORT_STRING);
	}

}

