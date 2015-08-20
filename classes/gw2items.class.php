<?php

/**
 *
 * @package    gw2-database
 * @filesource gw2items.class.php
 * @version    0.1.0
 * @link       https://github.com/codemasher/
 * @created    24.09.2014
 *
 * @author     Smiley <smiley@chillerlan.net>
 * @copyright  Copyright (c) 2013 {@link https://twitter.com/codemasher Smiley} <smiley@chillerlan.net>
 * @license    http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */
class GW2Items extends GW2API{

	/**
	 * @var array
	 */
	public $attributes = [];

	/**
	 * @var array
	 */
	public $combinations = [];

	private $skins = [];
	private $items = [];
	private $recipes = [];

	/**
	 *
	 */
	public function __construct(){
		parent::__construct();

		$this->attributes = $this->db->simple_query('SELECT `shortname`, `primary`, `name_'.$this->conf->var['lang'].'` AS `name` FROM `gw2_attributes`', true, 'shortname');

		$this->combinations = $this->db->simple_query('SELECT `id`, `attribute1`, `attribute2`, `attribute3`, `prefix_'.$this->conf->var['lang'].'` AS `prefix`, `suffix_'.$this->conf->var['lang'].'` AS `suffix` FROM `gw2_attribute_combinations`', true, 'id');
		$this->combinations = array_map(function ($arr){
			$ret = [
				'id'         => $arr['id'],
				'attributes' => [$arr['attribute1']],
				'prefix'     => $arr['prefix'],
				'suffix'     => $arr['suffix'],
			];
			if(!empty($arr['attribute2'])){
				$ret['attributes'][] = $arr['attribute2'];
				if(!empty($arr['attribute3'])){
					$ret['attributes'][] = $arr['attribute3'];
				}
			}

			return $ret;
		}, $this->combinations);
	}

	/**
	 *
	 */
	public function item_refresh(){
		$this->request('v2/items');
		if(is_array($this->api_response) && count($this->api_response) > 0){
			$values = [];
			$time = time();
			foreach($this->api_response as $item){
				$values[] = [$item, $time];
			}
			$this->db->multi_insert('INSERT IGNORE INTO '.TABLE_ITEMS.' (`id`, `date_added`) VALUES (?, ?)', $values);
			$this->log('Item database refresh done. '.count($this->api_response).' items in items.json.');
		}
	}

	/**
	 * @param bool $full_update
	 */
	public function item_update($full_update = false){
		// going to blow up the memory here...
		$this->items = $this->db->simple_query(
			'SELECT `id`, `data_de`, `data_en`, `data_es`, `data_fr`, `update_time`
				FROM '.TABLE_ITEMS.(
			$full_update
				? ''
				: ' WHERE `updated` = 0'
			), true, 'id');

		if(is_array($this->items)){
			// fetch all the IDs into a one dimensional array and put them into chunks...
			$ids = array_chunk(array_keys($this->items), $this->chunksize);

			$urls = [];
			// ...now loop through the chunks
			foreach($ids as $chunk){
				// ...and create the request for each chunk and language
				foreach($this->api_languages as $lang){
					$urls[] = http_build_query(['lang' => $lang, 'ids' => implode(',', $chunk)]);
				}
			}

			$this->multi_request($urls, $this->api_base.'v2/items?', function ($response, $info){
				// get the current request params
				parse_str(parse_url($info['url'], PHP_URL_QUERY), $params);

				if(in_array($info['http_code'], [200, 206], true)){
					$response = json_decode($response, true);
					$changes = [];

					// push the data for each item to the temp array and create a diff
					foreach($response as $item){
						$item = array_sort_recursive($item);
						$this->temp_data[$item['id']][$params['lang']] = $item;
						$old = json_decode(@$this->items[$item['id']]['data_'.$params['lang']], true);
						$old = is_array($old) ? array_sort_recursive($old) : [];
						if(!empty($old) && !empty(array_diff_assoc_recursive($old, $item, true))){
							$changes[] = [
								$item['id'],
								'item',
								$params['lang'],
								$this->items[$item['id']]['update_time'],
								json_encode($old),
							];
						}
						unset($this->items[$item['id']]);
					}

					$sql = 'INSERT INTO '.TABLE_DIFF.' (`db_id`, `type`, `lang`, `date`, `data`) VALUES (?,?,?,?,?)';
					$this->db->multi_insert($sql, $changes);

					// check if we got the data for all languages
					$ids = explode(',', $params['ids']);
					if(count($this->temp_data[$ids[0]]) === count($this->api_languages)){
						// loop through the chunk's item ids and process the data
						$values = [];
						foreach($ids as $id){
							$values[] = $this->parse_itemdata($id);
							// remove the processed item from the temp array to not blow up the memory
							unset($this->temp_data[$id]);
						}

						$sql = 'UPDATE '.TABLE_ITEMS.' SET `signature` = ?, `file_id` = ?, `rarity` = ?, `weight` = ?, `type` = ?,
					`subtype` = ?, `unlock_type` = ?, `level` = ?, `value` = ?, `pvp` = ?, `attr_combination` = ?, `unlock_id` = ?,
					`name_de` = ?, `name_en` = ?, `name_es` = ?, `name_fr` = ?, `data_de` = ?, `data_en` = ?, `data_es` = ?,
					`data_fr` = ?, `updated` = ?, `update_time` = ?  WHERE `id` = ?';

						$this->db->multi_insert($sql, $values);
						unset($response, $values, $changes);
					}
				}
				else{
					$this->temp_failed[$params['lang']][] = $params['ids'];
				}
			});
		}
	}

	/**
	 * @param int $id
	 *
	 * @return array
	 */
	private function parse_itemdata($id){
		$data = $this->temp_data[$id];
		$this->log('#'.$id.': '.$data['en']['name']);
		$s = [chr(194).chr(160), '  '];

		switch(true){
			case isset($data['en']['details']['recipe_id']) :
				$unlock_id = $data['en']['details']['recipe_id'];
				break;
			case isset($data['en']['details']['color_id'])  :
				$unlock_id = $data['en']['details']['color_id'];
				break;
			default:
				$unlock_id = 0;
				break;
		}

		$file_id = explode('/', str_replace(['https://render.guildwars2.com/file/', '.png'], '', $data['en']['icon']));

		return [
			$file_id[0],
			$file_id[1],
			$data['en']['rarity'],
			@$data['en']['details']['weight_class'],
			$data['en']['type'],
			@$data['en']['details']['type'],
			@$data['en']['details']['unlock_type'],
			$data['en']['level'],
			$data['en']['vendor_value'],
			in_array('Pvp', $data['en']['game_types']) && in_array('PvpLobby', $data['en']['game_types']),
			isset($data['en']['details']['infix_upgrade']) ? $this->attribute_combination($data['en']['details']['infix_upgrade']) : 0,
			$unlock_id,
			str_replace($s, ' ', $data['de']['name']),
			str_replace($s, ' ', $data['en']['name']),
			str_replace($s, ' ', $data['es']['name']),
			str_replace($s, ' ', $data['fr']['name']),
			json_encode($data['de']),
			json_encode($data['en']),
			json_encode($data['es']),
			json_encode($data['fr']),
			1,
			time(),
			$data['en']['id'],
		];
	}

	/**
	 * @param array $infix_upgrade
	 *
	 * @return bool|int
	 *
	 * @link http://wiki.guildwars2.com/wiki/Item_nomenclature
	 */
	private function attribute_combination(array $infix_upgrade){
		$att = array_column($infix_upgrade['attributes'], 'attribute');
		if(isset($infix_upgrade['buff']['skill_id'])){
			switch((int)$infix_upgrade['buff']['skill_id']){
				// Condition Duration is only available as major attribute, so put it on top
				case 16631:
					array_unshift($att, 'ConditionDuration');
					break;
				// Boon duration is only a minor attribute, so add it to the end
				case 16517:
					$att[] = 'BoonDuration';
					break;
			}
		}

		$key = array_search($att, array_column($this->combinations, 'attributes', 'id'));
		if(count($att) === 7){
			return 52; // celestial todo: HARDCODE ALL THE THINGS!
		}
		else if(isset($this->combinations[$key])){
			return $this->combinations[$key]['id'];
		}

		return false;
	}

	/**
	 *
	 */
	public function recipe_refresh(){
		$this->request('v2/recipes');
		$data = $this->api_response;
		$values = [];
		$time = time();
		foreach($data as $id){
			$values[] = [$id, $time];
		}
		$this->db->multi_insert('INSERT IGNORE INTO '.TABLE_RECIPES.' (`recipe_id`, `date_added`) VALUES (?,?)', $values);
		$this->log('Recipe database refresh done. '.count($this->api_response).' items in recipes.json.');
	}

	/**
	 * @param bool $full_update
	 */
	public function recipe_update($full_update = false){
		$this->recipes = $this->db->simple_query(
			'SELECT `recipe_id`, `data`, `update_time`
				FROM '.TABLE_RECIPES.(
			$full_update
				? ''
				: ' WHERE `updated` = 0'
			), true, 'recipe_id');

		if(is_array($this->recipes)){

			$ids = array_chunk(array_keys($this->recipes), $this->chunksize);
			$urls = [];
			foreach($ids as $chunk){
				$urls[] = http_build_query(['ids' => implode(',', $chunk)]);
			}

			unset($ids);

			$this->multi_request($urls, $this->api_base.'v2/recipes?', function ($response, $info){
				if(in_array($info['http_code'], [200, 206], true)){
					$response = json_decode($response, true);
					$values = [];
					$changes = [];
					foreach($response as $recipe){
						$recipe = array_sort_recursive($recipe);

						$disciplines = array_map(function($value){
							return 'CRAFT_'.strtoupper($value);
						}, $recipe['disciplines']);

						$values[] = [
							$recipe['output_item_id'],
							$recipe['output_item_count'],
							$this->set_bitflag($disciplines),
							$recipe['min_rating'],
							$recipe['type'],
							is_array($recipe['flags']) && in_array('LearnedFromItem', $recipe['flags']),
							@$recipe['ingredients'][0]['item_id'],
							@$recipe['ingredients'][0]['count'],
							@$recipe['ingredients'][1]['item_id'],
							@$recipe['ingredients'][1]['count'],
							@$recipe['ingredients'][2]['item_id'],
							@$recipe['ingredients'][2]['count'],
							@$recipe['ingredients'][3]['item_id'],
							@$recipe['ingredients'][3]['count'],
							json_encode($recipe),
							1,
							time(),
							$recipe['id'],
						];

						$old = json_decode(@$this->recipes[$recipe['id']]['data'], true);
						$old = is_array($old) ? array_sort_recursive($old) : [];
						if(!empty($old) && !empty(array_diff_assoc_recursive($old, $recipe, true))){
							$changes[] = [
								$recipe['id'],
								'recipe',
								$this->recipes[$recipe['id']]['update_time'],
								json_encode($old),
							];
						}
						unset($this->recipes[$recipe['id']]);
						$this->log('Updated recipe #'.$recipe['id']);
					}

					$sql = 'INSERT INTO '.TABLE_DIFF.' (`db_id`, `type`, `date`, `data`) VALUES (?,?,?,?)';
					$this->db->multi_insert($sql, $changes);

					$sql = 'UPDATE '.TABLE_RECIPES.' SET `output_id` = ?, `output_count` = ?, `disciplines`= ?,
								`rating` = ?, `type` = ?, `from_item` = ?, `ing_id_1` = ?, `ing_count_1` = ?,
								`ing_id_2` = ?, `ing_count_2` = ?, `ing_id_3` = ?, `ing_count_3` = ?,
								`ing_id_4` = ?, `ing_count_4` = ?, `data` = ?, `updated` = ?, `update_time` = ?
								WHERE `recipe_id` = ?';

					$this->db->multi_insert($sql, $values);
					unset($response, $values, $changes);
				}
				else{
					print_r([$response, $info]);
					$this->temp_failed[] = $info['url'];
				}
			});
		}
	}

	/**
	 *
	 */
	public function skin_refresh(){
		$this->request('v2/skins');
		$data = $this->api_response;
		$values = [];
		$time = time();
		foreach($data as $id){
			$values[] = [$id, $time];
		}
		$this->db->multi_insert('INSERT IGNORE INTO '.TABLE_SKINS.' (`skin_id`, `added`) VALUES (?,?)', $values);
		$this->log('Skin database refresh done. '.count($this->api_response).' items in skins.json.');
	}

	/**
	 * @param bool $full_update
	 */
	public function skin_update($full_update = false){
		$this->skins = $this->db->simple_query(
			'SELECT `skin_id`, `data_de`, `data_en`, `data_es`, `data_fr`, `update_time`
				FROM '.TABLE_SKINS.(
			$full_update
				? ''
				: ' WHERE `updated` = 0'
			), true, 'skin_id');

		if(is_array($this->skins)){
			$ids = array_chunk(array_keys($this->skins), $this->chunksize);

			$urls = [];
			foreach($ids as $chunk){
				foreach($this->api_languages as $lang){
					$urls[] = http_build_query(['lang' => $lang, 'ids' => implode(',', $chunk)]);
				}
			}

			$this->multi_request($urls, $this->api_base.'v2/skins?', function ($response, $info){
				if(in_array($info['http_code'], [200, 206], true)){
					$response = json_decode($response, true);
					parse_str(parse_url($info['url'], PHP_URL_QUERY), $params);
					$changes = [];

					foreach($response as $skin){
						$skin = array_sort_recursive($skin);
						$this->temp_data[$skin['id']][$params['lang']] = $skin;
						$old = json_decode(@$this->skins[$skin['id']]['data_'.$params['lang']], true);
						$old = is_array($old) ? array_sort_recursive($old) : [];
						if(!empty($old) && !empty(array_diff_assoc_recursive($old, $skin, true))){
							$changes[] = [
								$skin['id'],
								'skin',
								$params['lang'],
								$this->skins[$skin['id']]['update_time'],
								json_encode($old),
							];
						}
						unset($this->skins[$skin['id']]);
					}

					$sql = 'INSERT INTO '.TABLE_DIFF.' (`db_id`, `type`, `lang`, `date`, `data`) VALUES (?,?,?,?,?)';
					$this->db->multi_insert($sql, $changes);
					unset($changes);

					$ids = explode(',', $params['ids']);
					if(count($this->temp_data[$ids[0]]) === count($this->api_languages)){

						$values = [];
						foreach($ids as $id){
							$file_id = explode('/', str_replace(['https://render.guildwars2.com/file/', '.png'], '', $this->temp_data[$id]['en']['icon']));

							$sub = '';
							if(isset($this->temp_data[$id]['en']['details']['weight_class'])){
								$sub = $this->temp_data[$id]['en']['details']['weight_class'];
							}
							else if(isset($this->temp_data[$id]['en']['details']['damage_type'])){
								$sub = $this->temp_data[$id]['en']['details']['damage_type'];
							}

							$values[] = [
								$file_id[0],
								$file_id[1],
								$this->temp_data[$id]['en']['type'],
								@$this->temp_data[$id]['en']['details']['type'],
								$sub,
								$this->temp_data[$id]['de']['name'],
								$this->temp_data[$id]['en']['name'],
								$this->temp_data[$id]['es']['name'],
								$this->temp_data[$id]['fr']['name'],
								json_encode($this->temp_data[$id]['de']),
								json_encode($this->temp_data[$id]['en']),
								json_encode($this->temp_data[$id]['es']),
								json_encode($this->temp_data[$id]['fr']),
								1,
								time(),
								$id,
							];

							$this->log('Updated skin #'.$id);
							unset($this->temp_data[$id]);
						}
						$sql = 'UPDATE '.TABLE_SKINS.' SET `signature`= ?, `file_id`= ?, `type`= ?, `subtype`= ?,
									`properties` = ?, `name_de`= ?, `name_en`= ?, `name_es`= ?, `name_fr`= ?,
									`data_de`= ?, `data_en`= ?, `data_es`= ?, `data_fr`= ?, `updated`= ?, `update_time`= ?
									WHERE `skin_id` = ?';

						$this->db->multi_insert($sql, $values);
						unset($response, $values);
					}
				}
				else{
					print_r([$response, $info]);
					$this->temp_failed[] = $info['url'];
				}
			});
		}
	}

}
