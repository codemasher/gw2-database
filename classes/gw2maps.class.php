<?php

/**
 * Fancy Header
 *
 * ...some fancy description
 *
 *
 * @package    gw2-database
 * @filesource gw2maps.class.php
 * @version    0.1.0
 * @link       https://github.com/codemasher/
 * @created    13.07.2015
 *
 * @author     Smiley <smiley@chillerlan.net>
 * @copyright  Copyright (c) 2013 {@link https://twitter.com/codemasher Smiley} <smiley@chillerlan.net>
 * @license    http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */
class GW2Maps extends GW2API{

	/**
	 *
	 */
	public function refresh_floors(){
		global $db;
#		$this->request('v2/continents');
#		$continents = $this->api_response;
		$continents = [1, 2];
		$urls = [];
		foreach($continents as $c){
			$this->request('v2/continents/'.$c);
			$continent = $this->api_response;
			if(is_array($continent['floors'])){
				foreach($continent['floors'] as $f){
					$urls[] = $c.'/floors/'.$f.'/regions';
				}
			}
		}

		$this->multi_request($urls, $this->api_base.'v2/continents/', function ($response, $info){
			if($info['http_code'] === 200){
				$location = explode('/', str_replace(['/v2/continents/', 'floors/', '/regions'], '', parse_url($info['url'], PHP_URL_PATH)));
				$this->temp_data[] = array_merge($location, [$response]);
				$this->log('updating floor #'.$location[1].', continent: '.$location[0].', data: '.$response);
			}
			else{
				$this->temp_failed[] = $info['url'];
			}
		});

		$db->simple_query('TRUNCATE TABLE `gw2_floors`');
		$db->multi_insert('INSERT INTO `gw2_floors` (`continent_id`, `floor_id`, `regions`) VALUES (?,?,?)', $this->temp_data);
	}

	/**
	 *
	 */
	public function refresh_regions_maps(){
		global $db;
		$floors = $db->simple_query('SELECT * FROM `gw2_floors`');
		$urls_m = [];
		$urls_r = [];
		foreach($floors as $floor){
			$region = json_decode($floor['regions'], true);
			if(is_array($region) && !empty($region)){
				foreach($region as $rid){
					$base = $floor['continent_id'].'/floors/'.$floor['floor_id'].'/regions/'.$rid;
					$urls_m[] = $base.'/maps';
					foreach(['de', 'en', 'es', 'fr'] as $lang){
						$urls_r[] = $base.'?lang='.$lang;
					}
				}
			}
		}

		$db->simple_query('TRUNCATE TABLE `gw2_regions`');
		$db->simple_query('TRUNCATE TABLE `gw2_maps`');

		$this->multi_request($urls_r, $this->api_base.'v2/continents/', function ($response, $info){
			global $db;
			if($info['http_code'] === 200){
				$response = json_decode($response, true);
				$path = parse_url($info['url'], PHP_URL_PATH);
				$path_hash = sha1($path);
				parse_str(parse_url($info['url'], PHP_URL_QUERY), $params);
				$this->temp_data[$path_hash][$params['lang']] = $response;
				if(count($this->temp_data[$path_hash]) === count($this->api_languages)){
					$map_arr = array_column($this->temp_data[$path_hash]['en']['maps'], 'id');
					$location = explode('/', str_replace(['/v2/continents/', 'floors/', '/regions'], '', $path));

					$maps = [];
					foreach($map_arr as $map){
						$maps[] = array_merge([$map], $location);
						$this->log('Adding map #'.$map.', continent: '.$location[0].', floor: '.$location[1].', region: '.$location[2]);
					}

					$sql = 'INSERT INTO `gw2_maps` (`map_id`, `continent_id`, `floor_id`, `region_id`) VALUES (?,?,?,?)';
					$db->multi_insert($sql, $maps);


					$sql = 'INSERT INTO `gw2_regions` (`continent_id`, `floor_id`, `region_id`,
								`label_coord`, `maps`, `name_de`, `name_en`, `name_es`, `name_fr`)
								VALUES(?,?,?,?,?,?,?,?,?)';

					$regions = array_merge($location, [
						json_encode($this->temp_data[$path_hash]['en']['label_coord']),
						json_encode($map_arr),
						$this->temp_data[$path_hash]['de']['name'],
						$this->temp_data[$path_hash]['en']['name'],
						$this->temp_data[$path_hash]['es']['name'],
						$this->temp_data[$path_hash]['fr']['name'],
					]);

					$db->prepared_query($sql, $regions);
					$this->log('Region '.$this->temp_data[$path_hash]['en']['name'].' added, data:'.json_encode(array_column($this->temp_data[$path_hash]['en']['maps'], 'id')).'.');
					unset($this->temp_data[$path_hash]);
				}
			}
			else{
				$this->temp_failed[] = $info['url'];
			}
		});
	}

	/**
	 *
	 */
	public function update_maps(){
		global $db;
		$maps = $db->prepared_query('SELECT `continent_id`, `floor_id`, `region_id`, `map_id` FROM `gw2_maps`');

		$urls = [];
		foreach($maps as $map){
			foreach(['de', 'en', 'es', 'fr'] as $lang){
				$urls[] = $map['continent_id'].'/floors/'.$map['floor_id'].'/regions/'.$map['region_id'].'/maps/'.$map['map_id'].'?lang='.$lang;
			}
		}

		$this->multi_request($urls, $this->api_base.'v2/continents/', function ($data, $info){
			global $db;
			$path = parse_url($info['url'], PHP_URL_PATH);
			$path_hash = sha1($path);

			if($info['http_code'] === 200){
				parse_str(parse_url($info['url'], PHP_URL_QUERY), $params);
				$this->temp_data[$path_hash][$params['lang']] = json_decode($data, true);
				if(count($this->temp_data[$path_hash]) === count($this->api_languages)){
					$sql = 'UPDATE `gw2_maps` SET `default_floor` = ?, `map_rect` = ?, `continent_rect` = ?,
									`min_level` = ?, `max_level` = ?, `name_de` = ?, `data_de` = ?,  `name_en` = ?,
									`data_en` = ?,  `name_es` = ?, `data_es` = ?,  `name_fr` = ?, `data_fr` = ?
								WHERE `continent_id` = ?
									AND `floor_id` = ?
									AND `region_id` = ?
									AND `map_id` = ?';

					$values = array_merge([
						$this->temp_data[$path_hash]['en']['default_floor'],
						json_encode($this->temp_data[$path_hash]['en']['map_rect']),
						json_encode($this->temp_data[$path_hash]['en']['continent_rect']),
						json_encode($this->temp_data[$path_hash]['en']['min_level']),
						$this->temp_data[$path_hash]['en']['max_level'],
						$this->temp_data[$path_hash]['de']['name'],
						json_encode($this->temp_data[$path_hash]['de']),
						$this->temp_data[$path_hash]['en']['name'],
						json_encode($this->temp_data[$path_hash]['en']),
						$this->temp_data[$path_hash]['es']['name'],
						json_encode($this->temp_data[$path_hash]['es']),
						$this->temp_data[$path_hash]['fr']['name'],
						json_encode($this->temp_data[$path_hash]['fr']),
					], explode('/', str_replace(['/v2/continents/', 'floors/', 'regions/', 'maps/'], '', $path)));

					$db->prepared_query($sql, $values);
					$this->log('Map #'.$this->temp_data[$path_hash]['en']['id'].' updated: '.$this->temp_data[$path_hash]['en']['name']);
					unset($this->temp_data[$path_hash]);
				}
			}
			else{
				$temp_failed[$path_hash][] = $info['url'];
			}
		});
	}
}

