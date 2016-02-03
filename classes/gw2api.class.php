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
	 * Language used for translations. This does not affect request()
	 * Possible values: de, en, es, fr, also ko and zh with API v2 (maybe).
	 * @var string
	 */
	public $lang = 'en';

	/**
	 * languages supported by the API
	 * @var array
	 */
	public $api_languages = ['de', 'en', 'es', 'fr', 'zh'];

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
	 * @var int
	 */
	public $chunksize = 50;

	const CRAFT_ARMORSMITH = 0x1;
	const CRAFT_ARTIFICER = 0x2;
	const CRAFT_CHEF = 0x4;
	const CRAFT_HUNTSMAN = 0x8;
	const CRAFT_JEWELER = 0x10;
	const CRAFT_LEATHERWORKER = 0x20;
	const CRAFT_TAILOR = 0x40;
	const CRAFT_WEAPONSMITH = 0x80;
	const CRAFT_SCRIBE = 0x100;

	const CHATLINK_COIN = 0x01;
	const CHATLINK_ITEM = 0x02;
	const CHATLINK_TEXT = 0x03;
	const CHATLINK_MAP = 0x04;
	const CHATLINK_SKILL = 0x07;
	const CHATLINK_TRAIT = 0x08;
	const CHATLINK_RECIPE = 0x0A;
	const CHATLINK_SKIN = 0x0B;
	const CHATLINK_OUTFIT = 0x0C;

	const UPGRADE_NONE = 0x00;
	const UPGRADE_1 = 0x40;
	const UPGRADE_2 = 0x60;
	const UPGRADE_SKIN = 0x80;
	const UPGRADE_SKIN_1 = 0xC0;
	const UPGRADE_SKIN_2 = 0xE0;

	protected $db;
	protected $conf;
	public function __construct(){
		global $db, $conf;
		$this->db = $db;
		$this->conf = $conf;
	}

	// used in $this->multi_request()
	protected $temp_data = [];
	protected $temp_failed = [];
	public function __destruct(){
#		print_r($this->temp_failed);
#		var_dump($this);
	}

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
	 * @param string $apikey
	 *
	 * @return bool
	 */
	public function request($endpoint, array $params = [], $apikey = ''){

		// reset fields
		$this->api_response = null;
		$this->api_error = false;
		$this->api_error_message = '';

		$url = $this->api_base.$endpoint;
		$query = count($params) > 0 ? '?'.http_build_query($params) : '';

		$ch = curl_init();

		$options = [
			CURLOPT_URL            => $url.$query,
#			CURLOPT_VERBOSE        => true,
#			CURLOPT_HEADER         => true,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_CAINFO         => BASEDIR.$this->ca_info,
			CURLOPT_RETURNTRANSFER => true,
		];

		// since the format of an API key may change, we just check if it's present
		if(!empty($apikey)){
			$options += [
				CURLOPT_HTTPHEADER => [
					'Authorization: Bearer '.$apikey,
				]
			];
		}

		curl_setopt_array($ch, $options);

		$data = curl_exec($ch);
		$info = curl_getinfo($ch);
		$errno = curl_errno($ch);
		$errstr = curl_error($ch);

		curl_close($ch);

		if(in_array($info['http_code'], [200, 206], true)){
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
	 * @param array    $urls
	 * @param callable $callback
	 * @param string   $base_url
	 * @param array    $curl_options
	 */
	public function multi_request(array $urls, $base_url, callable $callback, array $curl_options = []){
		// reset temp arrays
		$this->temp_data = [];
		$this->temp_failed = [];

		$rolling_curl = new RollingCurl($urls, $callback);
		$rolling_curl->base_url = $base_url;
		$rolling_curl->window_size = 10;

		$rolling_curl->curl_options = [
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_CAINFO         => BASEDIR.$this->ca_info,
			CURLOPT_RETURNTRANSFER => true,
		];

		if(!empty($curl_options)){
			$rolling_curl->curl_options = array_merge($rolling_curl->curl_options, $curl_options);
		}

		$rolling_curl->process();
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
	public function recalc_coords(array $continent_rect, array $map_rect, array $point){
		// don't look at it. really! it will melt your brain and make your eyes bleed!
		return [
			round($continent_rect[0][0]+($continent_rect[1][0]-$continent_rect[0][0])*($point[0]-$map_rect[0][0])/($map_rect[1][0]-$map_rect[0][0])),
			round($continent_rect[0][1]+($continent_rect[1][1]-$continent_rect[0][1])*(1-($point[1]-$map_rect[0][1])/($map_rect[1][1]-$map_rect[0][1])))
		];
	}

	/**
	 *
	 * @param array $chatlink
	 *
	 * @return string
	 * @author {@link https://twitter.com/poke poke}
	 * @link   http://wiki.guildwars2.com/wiki/Widget:Game_link
	 */
	public function chatlink_encode(array $chatlink){
		$data = [$chatlink['type']];
		$ids = [$chatlink['id']];
		$upgrade = self::UPGRADE_NONE;

		if($chatlink['type'] === self::CHATLINK_ITEM){
			$data[] = isset($chatlink['count']) && !empty($chatlink['count']) ? $chatlink['count'] : 1;

			if(isset($chatlink['skin']) && !empty($chatlink['skin'])){
				$ids[] = $chatlink['skin'];
				$upgrade = $this->set_bitflag(['UPGRADE_SKIN'], $upgrade);
			}

			if(isset($chatlink['upgrades']) && is_array($chatlink['upgrades']) && !empty($chatlink['upgrades'])){
				$ids = array_merge($ids, $chatlink['upgrades']);
				$upgrade = $this->set_bitflag(['UPGRADE_'.count($chatlink['upgrades'])], $upgrade);
			}
		}

		foreach($ids as $k => $id){
			$octets = [];
			while($id > 0){
				$octets[] = $id&255;
				$id = $id >> 8;
			}
			while(count($octets) < 3){
				$octets[] = 0;
			}
			$octets[] = $k === 0 ? $upgrade : 0;
			$data = array_merge($data, $octets);
		}

		$data = array_map(function ($ascii){
			return chr($ascii);
		}, $data);

		return '[&'.base64_encode(implode($data)).']';
	}

	/**
	 * @param string $chatlink
	 *
	 * @return array|bool
	 *
	 * @author {@link https://twitter.com/poke poke}
	 * @link   http://ideone.com/0RSpAA
	 */
	public function chatlink_decode($chatlink){
		if(preg_match('/\[&([a-z\d+\/]+=*)\]/i', $chatlink)){
			$out = [];
			// decode base64 and read octets
			$octets = [];
			foreach(str_split(base64_decode($chatlink)) as $char){
				$octets[] = ord($char);
			}

			if(count($octets) < 5){
				// invalid code
				return false;
			}

			$out['type'] = array_shift($octets);
			$skinned = false;
			if($out['type'] === self::CHATLINK_ITEM){
				$out['count'] = array_shift($octets);
				$skinned = $this->get_bitflag(self::UPGRADE_SKIN, $octets[3]);
			}

			foreach(array_chunk($octets, 4) as $k => $chunk){
				if(count($chunk) === 4){
					$id = $chunk[2] << 16|$chunk[1] << 8|$chunk[0];
					if($k === 0){
						$out['id'] = $id;
					}
					if($out['type'] === self::CHATLINK_ITEM){
						if($k === 1 && $skinned){
							$out['skin'] = $id;
						}
						if(($k > 0 && $k < 3 && !$skinned) || ($k > 1 && $k < 4 && $skinned)){
							$out['upgrades'][] = $id;
						}
					}
				}
			}

			return $out;
		}

		// invalid chatlink
		return false;
	}

	/**
	 * @param array $flags
	 *
	 * @param int   $val
	 *
	 * @return int
	 */
	public function set_bitflag(array $flags, $val = 0){
		foreach($flags as $flag){
			$val = $val|constant('self::'.$flag);
		}

		return $val;
	}

	/**
	 * @param int $flag
	 * @param int $val
	 *
	 * @return bool
	 */
	public function get_bitflag($flag, $val){
		return ($val&$flag) === $flag;
	}

}
