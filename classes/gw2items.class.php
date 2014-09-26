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

	public $chunksize = 50;
	private $temp_data = [];
	private $temp_failed = [];

	private $attribute_map = [
		'single' => [
			'winter_1'   => 'BoonDuration',
			'festering'  => 'ConditionDamage',
			'givers_1'   => 'ConditionDuration',
			'compassion' => 'Healing',
			'might'      => 'Power',
			'precision'  => 'Precision',
			'resilience' => 'Toughness',
			'vitality'   => 'Vitality',
		],
		'double' => [
			'ravaging'     => ['ConditionDamage', 'Precision'],
			'lingering'    => ['ConditionDamage', 'Vitality'],
			'givers_2w'    => ['ConditionDuration', 'Vitality'], //weapon
			'rejuvenation' => ['Healing', 'Power'],
			'mending'      => ['Healing', 'Vitality'],
			'potency'      => ['Power', 'ConditionDamage'],
			'honing'       => ['Power', 'CritDamage'],
			'strength'     => ['Power', 'Precision'],
			'vigor'        => ['Power', 'Vitality'],
			'penetration'  => ['Precision', 'CritDamage'],
			'hunter'       => ['Precision', 'Power'],
			'enduring'     => ['Toughness', 'ConditionDamage'],
			'givers_2a '   => ['Toughness', 'Healing'], //armor
			'hearty'       => ['Vitality', 'Toughness'],
			'stout'        => ['Toughness', 'Precision'],
		],
		'triple' => [
			'carrion'    => ['ConditionDamage', 'Power', 'Vitality'],
			'rabid'      => ['ConditionDamage', 'Precision', 'Toughness'],
			'dire'       => ['ConditionDamage', 'Toughness', 'Vitality'],
			'givers_3w'  => ['ConditionDuration', 'Precision', 'Vitality'], //weapon
			'apothecary' => ['Healing', 'ConditionDamage', 'Toughness'],
			'cleric'     => ['Healing', 'Power', 'Toughness'],
			'magi'       => ['Healing', 'Precision', 'Vitality'],
			'zealot'     => ['Power', 'Healing', 'Precision'], //same stats as Keeper's
			'berserker'  => ['Power', 'Precision', 'CritDamage'],
			'soldier'    => ['Power', 'Toughness', 'Vitality'],
			'valkyrie'   => ['Power', 'Vitality', 'CritDamage'],
			'rampager'   => ['Precision', 'ConditionDamage', 'Power'],
			'assassin'   => ['Precision', 'Power', 'CritDamage'],
			'knight_3s'  => ['Precision', 'Power', 'Toughness'], //suffix
			'settler'    => ['Toughness', 'ConditionDamage', 'Healing'],
			'givers_3a'  => ['Toughness', 'Healing', 'BoonDuration'], //armor
			'cavalier'   => ['Toughness', 'Power', 'CritDamage'],
			'knight_3p'  => ['Toughness', 'Power', 'Precision'], //prefix
			'shaman_3p'  => ['Vitality', 'ConditionDamage', 'Healing'], //prefix
			'sentinel'   => ['Vitality', 'Power', 'Toughness'],
			'shaman_3s'  => ['Vitality', 'Healing', 'Power'], //suffix
		],
	];

	public function __destruct(){
		print_r($this->temp_failed);
	}

	private function attribute_combination($infix_upgrade){
		$att = [];
		$mod = [];

		foreach($infix_upgrade['attributes'] as $a){
			$att[] = $a['attribute'];
			$mod[] = $a['modifier'];
		}

		array_multisort($mod, SORT_DESC, SORT_NUMERIC, $att, SORT_STRING);

		// Condition Duration is only available as major attribute, so put it on top
		if(isset($infix_upgrade['buff']['skill_id']) && (int)$infix_upgrade['buff']['skill_id'] === 16631){
			array_unshift($att, 'ConditionDuration');
		}

		// Boon duration is only a minor attribute, so add it to the end
		if(isset($infix_upgrade['buff']['skill_id']) && (int)$infix_upgrade['buff']['skill_id'] === 16517){
			$att[] = 'BoonDuration';
		}

		$count = count($att);

		if($count === 1){
			// SELECT COUNT(*) AS `count`, `attr1`, `attr_name` FROM `gw2_items` WHERE `attr1` != '' AND `attr2` = '' AND `attr3` = '' GROUP BY `attr1`
			return [array_search($att[0], $this->attribute_map['single']), $att];

		}

		if($count === 2){
			// SELECT COUNT(*) AS `count`, `attr1`, `attr2`, `attr_name` FROM `gw2_items` WHERE `attr1` != '' AND `attr2` != '' AND `attr3` = '' GROUP BY `attr1`, `attr2`
			foreach($this->attribute_map['double'] as $name => $map){
				$arr = array_diff_assoc($att, $map);
				if(empty($arr)){
					return [$name, $att];
				}
			}
		}

		if($count === 3){
			// SELECT COUNT(*) AS `count`, `attr1`, `attr2`, `attr3`, `attr_name` FROM `gw2_items` WHERE `attr1` != '' AND `attr2` != '' AND `attr3` != '' GROUP BY `attr1`, `attr2`, `attr3`
			foreach($this->attribute_map['triple'] as $name => $map){
				$arr = array_diff_assoc($att, $map);
				if(empty($arr)){
					return [$name, $att];
				}
			}
		}

		if($count === 7){
			// celestial is currently the only combination of 7 attributes
			return ['celestial', []];
		}

		return ['', []];
	}

	/**
	 *
	 */
	public function refresh_db(){
		global $db;
		$this->request('items.json');
		if(is_array($this->api_response) && isset($this->api_response['items'])){
			print_r($this->api_response);
			$values = [];
			$time = time();
			foreach($this->api_response['items'] as $item){
				$values[] = [$item, $time];
			}
			$db->multi_insert('INSERT IGNORE INTO '.TABLE_ITEMS.' (`id`, `date_added`) VALUES (?, ?)', $values);
			$this->log('Item database refresh done. '.count($this->api_response['items']).' items in items.json.');
		}
	}

	/**
	 * @param bool $full_update
	 */
	public function update_db($full_update = false){
		global $db;

		// reset temp arrays
		$this->temp_data = [];
		$this->temp_failed = [];

		// todo: WHERE update_time < ... ?
		$items = $db->prepared_query('SELECT `id` FROM '.TABLE_ITEMS.($full_update ? '' : ' WHERE `updated` = 0'));

		if(is_array($items)){
			$ids = [];
			foreach($items as $id){
				$ids[] = $id['id'];
			}

			$ids = array_chunk($ids, $this->chunksize);

			$urls = [];
			foreach($ids as $chunk){
				$chunk = implode(',', $chunk);
				foreach($this->api_languages as $lang){
					$urls[] = http_build_query(['lang' => $lang, 'ids' => $chunk]);
				}
			}

			$rolling_curl = new RollingCurl($urls, function($data, $info){
				global $db;
				// get the current request params
				$url = parse_url($info['url']);
				parse_str($url['query'], $params);

				if($info['http_code'] === 200){
					$data = json_decode($data, true);

					// push the data for each item to the temp array
					foreach($data as $item){
						$this->temp_data[$item['id']][$params['lang']] = $item;
					}

					$ids = explode(',', $params['ids']);

					// check if we got the data for all languages
					if(count($this->temp_data[$ids[0]]) === count($this->api_languages)){
						$values = [];
						$sql = 'UPDATE '.TABLE_ITEMS.' SET `signature` = ?, `file_id` = ?, `rarity` = ?, `weight` = ?, `type` = ?,
					`subtype` = ?, `unlock_type` = ?, `level` = ?, `value` = ?, `pvp` = ?, `attr_name` = ?, `unlock_id` = ?,
					`name_de` = ?, `name_en` = ?, `name_es` = ?, `name_fr` = ?, `data_de` = ?, `data_en` = ?, `data_es` = ?,
					`data_fr` = ?, `updated` = ?, `update_time` = ?  WHERE `id` = ?';
						foreach($ids as $id){
							$values[] = $this->parse_itemdata($id);
							unset($this->temp_data[$id]);
						}
						$db->multi_insert($sql, $values);
					}
				}
				else{
					$this->temp_failed[$params['lang']][] = $params['ids'];
				}
			});

			$rolling_curl->base_url = $this->api_base.'v2/items?';
			$rolling_curl->curl_options = [
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_SSL_VERIFYHOST => 2,
				CURLOPT_CAINFO         => BASEDIR.$this->ca_info,
				CURLOPT_RETURNTRANSFER => true,
			];

			$rolling_curl->process();
		}
	}

	/**
	 * @param $id
	 *
	 * @return array
	 */
	private function parse_itemdata($id){
		$data = $this->temp_data[$id];
		$this->log('#'.$id.': '.$data['en']['name']);
		$s = [chr(194).chr(160), '  '];

		switch(true){
			case isset($data['en']['details']['recipe_id']) : $unlock_id = $data['en']['details']['recipe_id']; break;
			case isset($data['en']['details']['color_id'])  : $unlock_id = $data['en']['details']['color_id']; break;
			default: $unlock_id = 0; break;
		}

		$file_id = explode('/', str_replace(['https://render.guildwars2.com/file/', '.png'], '', $data['en']['icon']));

		return [
			$file_id[0],
			$file_id[1],
			$data['en']['rarity'],
			isset($data['en']['details']['weight_class']) ? ($data['en']['details']['weight_class']) : 'None',
			$data['en']['type'],
			isset($data['en']['details']['type']) ? $data['en']['details']['type'] : '',
			isset($data['en']['details']['unlock_type']) ? $data['en']['details']['unlock_type'] : '',
			(int)$data['en']['level'],
			(int)$data['en']['vendor_value'],
			in_array('Pvp',$data['en']['game_types']) && in_array('PvpLobby',$data['en']['game_types']) ? 1 : 0,
			'', // todo: fix attribute_map
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
			$data['en']['id']
		];
	}


}

