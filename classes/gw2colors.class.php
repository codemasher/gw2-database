<?php

/**
 * Fancy Header
 *
 * ...some fancy description
 *
 *
 * @package    gw2-database
 * @filesource gw2colors.class.php
 * @version    0.1.0
 * @link       https://github.com/codemasher/
 * @created    16.07.2015
 *
 * @author     Smiley <smiley@chillerlan.net>
 * @copyright  Copyright (c) 2013 {@link https://twitter.com/codemasher Smiley} <smiley@chillerlan.net>
 * @license    http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */
class GW2Colors extends GW2API{

	public function __construct(){
		parent::__construct();
	}

	/**
	 *
	 */
	public function color_refresh(){
		$this->request('v2/colors');
		$data = $this->api_response;
		$values = [];
		$time = time();
		foreach($data as $id){
			$values[] = [$id, $time];
		}
		$this->db->multi_insert('INSERT IGNORE INTO '.TABLE_COLORS.' (`color_id`, `added`) VALUES (?,?)', $values);
		$this->log('Color database refresh done. '.count($this->api_response).' items in /v2/colors.');
	}

	/**
	 * @param bool $full_update
	 */
	public function color_update($full_update = false){
		$colors = $this->db->prepared_query('SELECT `color_id` FROM '.TABLE_COLORS.' '.($full_update ? '' : ' WHERE `updated` = 0'));

		if(is_array($colors)){
			$ids = array_chunk(array_column($colors, 'color_id'), $this->chunksize);

			$urls = [];
			foreach($ids as $chunk){
				foreach($this->api_languages as $lang){
					$urls[] = http_build_query(['lang' => $lang, 'ids' => implode(',', $chunk)]);
				}
			}

			$this->multi_request($urls, $this->api_base.'v2/colors?', function ($response, $info){
				parse_str(parse_url($info['url'], PHP_URL_QUERY), $params);

				if(in_array($info['http_code'], [200, 206], true)){
					$response = json_decode($response, true);
					foreach($response as $color){
						$this->temp_data[$color['id']][$params['lang']] = $color;
					}

					$ids = explode(',', $params['ids']);
					if(count($this->temp_data[$ids[0]]) === count($this->api_languages)){
						$values = [];
						foreach($ids as $id){
							// todo: wait for https://github.com/arenanet/api-cdi/pull/51 (done, now implement...)
							$values[] = [
								$this->temp_data[$id]['de']['name'],
								$this->temp_data[$id]['en']['name'],
								$this->temp_data[$id]['es']['name'],
								$this->temp_data[$id]['fr']['name'],
								$this->temp_data[$id]['zh']['name'],
								json_encode($this->temp_data[$id]['en']['base_rgb']),
								json_encode($this->temp_data[$id]['en']['cloth']),
								json_encode($this->temp_data[$id]['en']['leather']),
								json_encode($this->temp_data[$id]['en']['metal']),
								1,
								time(),
								$id,
							];
							$this->log('Updated color #'.$id.', '.$this->temp_data[$id]['en']['name']);
							unset($this->temp_data[$id]);
						}

						$sql = 'UPDATE '.TABLE_COLORS.' SET `name_de` = ?, `name_en` = ?, `name_es` = ?, `name_fr` = ?, `name_zh` = ?,
									`base_rgb` = ?, `cloth` = ?, `leather` = ?, `metal` = ?, `updated` = ?, `update_time` = ?
									WHERE `color_id` = ?';

						$this->db->multi_insert($sql, $values);
						unset($response, $values);
					}
				}
				else{
					$this->temp_failed[$params['lang']][] = $params['ids'];
				}
			});
		}
	}

}

