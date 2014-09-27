<?php
/**
 * GW2API
 *
 * @filesource gw2api.class.php
 * @version    0.1.0
 * @link       https://github.com/codemasher/gw2-database/blob/master/classes/gw2api.class.php
 * @created    08.03.14
 *
 * @author     Smiley <smiley@chillerlan.net>
 * @copyright  Copyright (c) 2013 {@link https://twitter.com/codemasher Smiley} <smiley@chillerlan.net>
 * @license    http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */

/**
 * Class GW2API
 */
class GW2API{

	/**
	 * The API base URL (including trailing slash)
	 * @var string
	 */
	public $api_base = 'https://api.guildwars2.com/';

	/**
	 * GW2 API version to use
	 *
	 * todo: questionable, changes are too marginal to keep the unused code, just use the API base
	 * @var int
	 */
	public $api_version = 1;

	/**
	 * Language used for translations. This does not affect request()
	 * Possible values: de, en, es, fr, also ko and zh with API v2 (maybe).
	 * @var string
	 */
	public $lang = 'en';

	/**
	 * languages supported by the API
	 * @var array
	 */
	public $api_languages = ['de', 'en', 'es', 'fr']; //, 'ko', 'zh'

	/**
	 * Holds the API response after a successful call to request()
	 * @var mixed
	 */
	public $api_response = null;

	/**
	 * Will be set to true when request() fails
	 * @var bool
	 */
	public $api_error = false;

	/**
	 * Holds an error message when request() fails
	 * @var string
	 */
	public $api_error_message = '';

	/**
	 * CA Root Certificates for use with CURL/SSL
	 * @var string
	 * @link http://curl.haxx.se/ca/cacert.pem
	 */
	public $ca_info = 'cert/cacert.pem';

	/**
	 * Write a logfile?
	 * @var bool
	 */
	public $log_enabled = true;

	/**
	 * Logfile name
	 * @var string
	 */
	public $log_file = 'gw2api.log';

	/**
	 * Time format for console output
	 * @see date()
	 * @var string
	 */
	public $log_date_format = '[Y-m-d H:i:s \U\T\C]';

	/**
	 * Log to console?
	 * @var bool
	 */
	public $log_to_cli = true;


	/**
	 * log wrapper
	 *
	 * @param $line
	 */
	public function log($line){
		if($this->log_enabled){
			write_log($line, $this->log_file, $this->log_date_format, $this->log_to_cli);
		}
	}

	/**
	 * GW2 API request
	 *
	 * sends a request to the given API endpoint and fills $api_response on success
	 *
	 * @param string $endpoint
	 * @param array  $params
	 *
	 * @return bool
	 */
	public function request($endpoint, array $params = []){

		// reset fields
		$this->api_response = null;
		$this->api_error = false;
		$this->api_error_message = '';

		$url = $this->api_base.'/'.$endpoint;
		$query = count($params) > 0 ? '?'.http_build_query($params) : '';

		$ch = curl_init();

		curl_setopt_array($ch, [
			CURLOPT_URL            => $url.$query,
#			CURLOPT_VERBOSE        => true,
#			CURLOPT_HEADER         => true,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_CAINFO         => BASEDIR.$this->ca_info,
			CURLOPT_RETURNTRANSFER => true,
		]);

		$data = curl_exec($ch);
		$info = curl_getinfo($ch);
		$errno = curl_errno($ch);
		$errstr = curl_error($ch);

		curl_close($ch);

		if($info['http_code'] === 200){
			$this->api_response = json_decode($data, true);
			return true;
		}
		else{
			$this->api_error = true;
			$this->api_error_message = 'connection error: '.$errno.', '.$errstr."\n".print_r($info, true);
			return false;
		}

	}

	/**
	 * The ugly coordinate recalculation
	 *
	 * @param array $continent_rect
	 * @param array $map_rect
	 * @param array $point
	 *
	 * @return array point
	 */
	public function recalc_coords($continent_rect, $map_rect, $point){
		// don't look at it. really! it will melt your brain and make your eyes bleed!
		return [
			round($continent_rect[0][0]+($continent_rect[1][0]-$continent_rect[0][0])*($point[0]-$map_rect[0][0])/($map_rect[1][0]-$map_rect[0][0])),
			round($continent_rect[0][1]+($continent_rect[1][1]-$continent_rect[0][1])*(1-($point[1]-$map_rect[0][1])/($map_rect[1][1]-$map_rect[0][1])))
		];
	}

}
