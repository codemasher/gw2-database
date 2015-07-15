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
	 * @var int
	 */
	public $chunksize = 50;

	/**
	 * @var array
	 */
	public $attributes = [];

	/**
	 * @var array
	 */
	public $combinations = [];

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

		// todo: WHERE update_time < ... ?
		$items = $this->db->prepared_query('SELECT `id` FROM '.TABLE_ITEMS.($full_update ? '' : ' WHERE `updated` = 0'));

		if(is_array($items)){
			// fetch all the IDs into a one dimensional array and put them into chunks...
			$ids = array_chunk(array_column($items, 'id'), $this->chunksize);

			$urls = [];
			// ...now loop through the chunks
			foreach($ids as $chunk){
				$chunk = implode(',', $chunk);
				// ...and create the request for each chunk and language
				foreach($this->api_languages as $lang){
					$urls[] = http_build_query(['lang' => $lang, 'ids' => $chunk]);
				}
			}

			$this->multi_request($urls, $this->api_base.'v2/items?', function ($response, $info){
				// get the current request params
				parse_str(parse_url($info['url'], PHP_URL_QUERY), $params);

				if(in_array($info['http_code'], [200, 206], true)){
					$response = json_decode($response, true);

					// push the data for each item to the temp array
					foreach($response as $item){
						$this->temp_data[$item['id']][$params['lang']] = $item;
					}

					$ids = explode(',', $params['ids']);

					// check if we got the data for all languages
					if(count($this->temp_data[$ids[0]]) === count($this->api_languages)){

						$values = [];
						$sql = 'UPDATE '.TABLE_ITEMS.' SET `signature` = ?, `file_id` = ?, `rarity` = ?, `weight` = ?, `type` = ?,
					`subtype` = ?, `unlock_type` = ?, `level` = ?, `value` = ?, `pvp` = ?, `attr_combination` = ?, `unlock_id` = ?,
					`name_de` = ?, `name_en` = ?, `name_es` = ?, `name_fr` = ?, `data_de` = ?, `data_en` = ?, `data_es` = ?,
					`data_fr` = ?, `updated` = ?, `update_time` = ?  WHERE `id` = ?';

						// loop through the chunk's item ids and process the data
						foreach($ids as $id){
							$values[] = $this->parse_itemdata($id);

							// remove the processed item from the temp array to not blow up the memory
							unset($this->temp_data[$id]);
						}
						$this->db->multi_insert($sql, $values);
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
		$recipes = $this->db->prepared_query('SELECT `recipe_id` FROM '.TABLE_RECIPES.($full_update ? '' : ' WHERE `updated` = 0'));

		if(is_array($recipes)){

			$ids = array_chunk(array_column($recipes, 'recipe_id'), $this->chunksize);
			$urls = [];
			foreach($ids as $chunk){
				$urls[] = http_build_query(['ids' => implode(',', $chunk)]);
			}

			unset($ids);

			$this->multi_request($urls, $this->api_base.'v2/recipes?', function ($response, $info){
				if(in_array($info['http_code'], [200, 206], true)){
					$response = json_decode($response, true);
					$values = [];
					foreach($response as $recipe){
						$values[] = [
							$recipe['output_item_id'],
							$recipe['output_item_count'],
							$this->set_bitflag($recipe['disciplines']),//disciplines
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
							$data = json_encode($recipe),
							1,
							time(),
							$recipe['id'],
						];
						$this->log('Updated recipe #'.$recipe['id']);
					}

					$sql = 'UPDATE '.TABLE_RECIPES.' SET `output_id` = ?, `output_count` = ?, `disciplines`= ?,
								`rating` = ?, `type` = ?, `from_item` = ?, `ing_id_1` = ?, `ing_count_1` = ?,
								`ing_id_2` = ?, `ing_count_2` = ?, `ing_id_3` = ?, `ing_count_3` = ?,
								`ing_id_4` = ?, `ing_count_4` = ?, `data` = ?, `updated` = ?, `update_time` = ?
								WHERE `recipe_id` = ?';

					$this->db->multi_insert($sql, $values);
					unset($response, $values);
				}
				else{
					print_r([$response, $info]);
					$this->temp_failed[] = $info['url'];
				}
			});
		}
	}

}
