<?php
/**
 *
 * @filesource   CreateDB.php
 * @created      25.02.2016
 * @package      chillerlan\GW2DB\Updaters\Items
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters\Items;

use chillerlan\GW2DB\Updaters\UpdaterBase;
use chillerlan\GW2DB\Updaters\UpdaterInterface;

/**
 * Class CreateDB
 */
class CreateDB extends UpdaterBase implements UpdaterInterface{
	const ITEM_TEMP_TABLE = 'gw2_items_temp';
	const ITEM_TABLE      = 'items_gw2treasures';

	protected $temp_items = [];
	protected $old_items  = [];
	protected $attribute_combinations = [];

	public function init(){
		$this->starttime = microtime(true);
		$this->logToCLI(__METHOD__.': start');

		// refresh IDs in the item table
		$this->refreshIDs('items', self::ITEM_TABLE);

		// fetch both, old and new items
		$this->old_items  = $this->GW2MySQLiDriver->raw('SELECT `id`, `data_de`, `data_en`, `data_es`, `data_fr`, `data_zh` FROM '.self::ITEM_TABLE, 'id', true, true);
		$this->temp_items = $this->GW2MySQLiDriver->raw('SELECT `id`, `blacklist`, `data_de`, `data_en`, `data_es`, `data_fr`, `data_zh`, UNIX_TIMESTAMP(`response_time`) AS `response_time` FROM '.self::ITEM_TEMP_TABLE, 'id', true, true);

		// get the attribute combinations

		/**
		 * @param \stdClass $combo
		 *
		 * @return array
		 */
		$callback = function($combo){
			$combination = [
				'id'         => $combo->id,
				'attributes' => [$combo->attribute1],
			];

			if(!empty($combo->attribute2)){
				$combination['attributes'][] = $combo->attribute2;

				if(!empty($combo->attribute3)){
					$combination['attributes'][] = $combo->attribute3;
				}
			}

			return $combination;
		};

		$sql = 'SELECT `id`, `attribute1`, `attribute2`, `attribute3` FROM `gw2_attribute_combinations`';
		$this->attribute_combinations = array_map($callback, $this->GW2MySQLiDriver->raw($sql, 'id'));

		// update
		$sql = 'UPDATE '.self::ITEM_TABLE.' SET `signature` = ?, `file_id` = ?, `rarity` = ?, `weight` = ?, `type` = ?,
					`subtype` = ?, `unlock_type` = ?, `level` = ?, `value` = ?, `pvp` = ?, `attr_combination` = ?, `unlock_id` = ?,
					`name_de` = ?, `name_en` = ?, `name_es` = ?, `name_fr` = ?, `name_zh` = ?, 
					`data_de` = ?, `data_en` = ?, `data_es` = ?, `data_fr` = ?, `data_zh` = ?, 
					`updated` = ?  WHERE `id` = ?';

		$this->GW2MySQLiDriver->multi_callback($sql, $this->temp_items, [$this, 'callback']);

		$this->logToCLI(__METHOD__.': end');
	}

	/**
	 * @param \stdClass $item
	 *
	 * @return array
	 */
	public function callback($item){

		// slow down things...
		foreach(self::API_LANGUAGES as $lang){

			if(empty($item['data_'.$lang])){
				return false;
			}

			// decode the json to array
			$item['data_'.$lang] = json_decode($item['data_'.$lang], true);
			// deep sort the array https://gitter.im/chillerlan/gw2hero.es?at=56c3dcfbfdaaf5f17c0b331d
			$item['data_'.$lang] = array_sort_recursive($item['data_'.$lang]);
			// strip out weird double spaces from item names
			$item['data_'.$lang]['name'] = str_replace([chr(194).chr(160), '  '], ' ', $item['data_'.$lang]['name']);
		}

		// ... -> diff

		$file_id = explode('/', str_replace(['https://render.guildwars2.com/file/', '.png'], '', $item['data_en']['icon']));

		switch(true){
			case isset($item['data_en']['details']['recipe_id']) :
				$unlock_id = $item['data_en']['details']['recipe_id'];
				break;
			case isset($item['data_en']['details']['color_id'])  :
				$unlock_id = $item['data_en']['details']['color_id'];
				break;
			default:
				$unlock_id = 0;
				break;
		}


		$insert = [
			'signature'        => $file_id[0],
			'file_id'          => $file_id[1],
			'rarity'           => $item['data_en']['rarity'],
			'weight'           => isset($item['data_en']['details']['weight_class']) ? $item['data_en']['details']['weight_class'] : null,
			'type'             => $item['data_en']['type'],
			'subtype'          => isset($item['data_en']['details']['type']) ? $item['data_en']['details']['type'] : null,
			'unlock_type'      => isset($item['data_en']['details']['unlock_type']) ? $item['data_en']['details']['unlock_type'] : null,
			'level'            => $item['data_en']['level'],
			'value'            => $item['data_en']['vendor_value'],
			'pvp'              => in_array('Pvp', $item['data_en']['game_types']) && in_array('PvpLobby', $item['data_en']['game_types']),
			'attr_combination' => isset($item['data_en']['details']['infix_upgrade']) ? $this->attribute_combination($item['data_en']['details']['infix_upgrade']) : 0,
			'unlock_id'        => $unlock_id,
			'name_de'          => $item['data_de']['name'],
			'name_en'          => $item['data_en']['name'],
			'name_es'          => $item['data_es']['name'],
			'name_fr'          => $item['data_fr']['name'],
			'name_zh'          => $item['data_zh']['name'],
			'data_de'          => json_encode($item['data_de']),
			'data_en'          => json_encode($item['data_en']),
			'data_es'          => json_encode($item['data_es']),
			'data_fr'          => json_encode($item['data_fr']),
			'data_zh'          => json_encode($item['data_zh']),
			'updated'          => 1,
			'id'               => $item['data_en']['id'],
		];

		return $insert;
	}

	/**
	 * @param array $infix_upgrade
	 *
	 * @return int
	 *
	 * @link http://wiki.guildwars2.com/wiki/Item_nomenclature
	 * @todo: fix!
	 */
	protected function attribute_combination(array $infix_upgrade){
		$attributes = array_column($infix_upgrade['attributes'], 'attribute');

		if(isset($infix_upgrade['buff']['skill_id'])){
			switch((int)$infix_upgrade['buff']['skill_id']){
				// Condition Duration is only available as major attribute, so put it on top
				case 16631:
					array_unshift($attributes, 'ConditionDuration');
					break;
				// Boon duration is only a minor attribute, so add it to the end
				case 16517:
					$attributes[] = 'BoonDuration';
					break;
			}
		}

		$key = array_search($attributes, array_column($this->attribute_combinations, 'attributes', 'id'));

		switch(true){
			case count($attributes) === 7:
				return 52; // celestial todo: HARDCODE ALL THE THINGS!
			case isset($this->attribute_combinations[$key]):
				return $this->attribute_combinations[$key]['id'];
			default:
				return 0;
		}

	}


}
