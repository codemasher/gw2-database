<?php
/**
 * Class Items
 *
 * @filesource   Items.php
 * @created      24.02.2016
 * @package      chillerlan\GW2DB\Updaters\Items
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters\Items;

use chillerlan\Database\Result;
use chillerlan\GW2DB\Helpers;
use chillerlan\GW2DB\Updaters\{UpdaterAbstract, UpdaterException};
use chillerlan\HTTP\HTTPResponseInterface;

class Items extends UpdaterAbstract{

	protected $temp_items = [];
	protected $old_items = [];
	protected $attribute_combinations = [];

	/**
	 * @return void
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 * @throws \chillerlan\Database\Drivers\DriverException
	 */
	public function init():void{
		$this->logger->info($this->processTimer().__METHOD__.': start');

		$this->fetchTempItems();
		$this->db->raw('OPTIMIZE TABLE '.$this->options->tableItemsTemp);

		$this->refreshItems();
		$this->db->raw('OPTIMIZE TABLE '.$this->options->tableItems);

		$this->logger->info($this->processTimer().__METHOD__.': end');
	}

	/**
	 * @return void
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 */
	protected function fetchTempItems():void{

		if($this->options->truncateTemp){
			$this->db->truncate
				->table($this->options->tableItemsTemp)
				->query();
		}

		$this->refreshIDs('/items', $this->options->tableItemsTemp);

		$result = $this->db->select
			->cols(['id'])
			->from([$this->options->tableItemsTemp])
			->where('blacklist', 0)
			->query();

		if(!$result instanceof Result || $result->length === 0){
			$msg = __METHOD__.': failed to fetch item IDs from db';
			$this->logger->error($msg);

			throw new UpdaterException($msg);
		}

		foreach($result->__chunk(self::CHUNK_SIZE) as $chunk){
			foreach(self::API_LANGUAGES as $lang){
				$this->urls[] = ['/items', ['lang' => $lang, 'ids' => implode(',', array_column($chunk, 'id'))]];
			}
		}

		$this->processURLs();
	}

	/**
	 * @param \chillerlan\HTTP\HTTPResponseInterface $response
	 * @param array|null                             $params
	 *
	 * @return void
	 */
	protected function processResponse(HTTPResponseInterface $response, array $params = null):void{
		$lang = $params[1]['lang'];

		$q = $this->db->update
			->table($this->options->tableItemsTemp)
			->set(['type', 'subtype', 'data_'.$lang], false)
			->where('id', '?', '=', false)
			->callback($response->json, function($item) use ($lang){
				$this->logger->info('updated temp item #'.$item->id.' '.$lang.' ('.htmlspecialchars($item->name).')');
				return [$item->type, $item->details->type ?? null, json_encode($item), $item->id];
			});

		// retry if the insert failed for whatever reason
		if(!$q){
			$this->addRetry('SQL insert failed, retrying URL.', $params);
		}

	}



	/**
	 * @return void
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 */
	protected function refreshItems():void{
		$this->logger->info($this->processTimer().__METHOD__.': start');

		$this->refreshIDs('/items', $this->options->tableItems);

		$this->initRefesh();

		$this->db->update
			->table($this->options->tableItems)
			->set([
				'signature', 'file_id', 'rarity', 'weight', 'type', 'subtype', 'unlock_type', 'level',
				'value', 'pvp', 'attr_combination', 'unlock_id', 'name_de', 'name_en', 'name_es', 'name_fr',
				'name_zh', 'data_de', 'data_en', 'data_es', 'data_fr', 'data_zh', 'metadata', 'updated'
			], false)
			->where('id', '?', '=', false)
			->callback($this->temp_items, [$this, 'refreshCallback']);


		$this->logger->info($this->processTimer().__METHOD__.': end');
	}

	/**
	 * @param array $item
	 *
	 * @return array|null
	 */
	public function refreshCallback(array $item):?array{

		// slow down things...
		foreach(self::API_LANGUAGES as $lang){

			// discard empty responses (if any...)
			if(empty($item['data_'.$lang])){
				return null;
			}

			// decode the json to array
			$item['data_'.$lang] = json_decode($item['data_'.$lang], true);

			// deep sort the array
			// https://gitter.im/chillerlan/gw2hero.es?at=56c3dcfbfdaaf5f17c0b331d
			$item['data_'.$lang] = Helpers\array_sort_recursive($item['data_'.$lang]);

			// strip out weird double spaces from item names
			// https://gitter.im/arenanet/api-cdi?at=56dc3e56126367383571545d
			$old_name = $item['data_'.$lang]['name'];
			$new_name = str_replace([chr(194).chr(160), '  '], ' ', $old_name);
			$item['@metadata']['name_replacement'][$lang] = $old_name !== $new_name;
			$item['data_'.$lang]['name'] = $new_name;
		}

		// ... -> diff

		$file_id = explode('/', str_replace(['https://render.guildwars2.com/file/', '.png'], '', $item['data_en']['icon']));

		$this->logger->info($this->processTimer().'item #'.$item['data_en']['id'].' ('.$item['data_en']['name'].') updated');

		return [
			'signature'        => $file_id[0],
			'file_id'          => $file_id[1],
			'rarity'           => $item['data_en']['rarity'],
			'weight'           => $item['data_en']['details']['weight_class'] ?? null,
			'type'             => $item['data_en']['type'],
			'subtype'          => $item['data_en']['details']['type'] ?? null,
			'unlock_type'      => $item['data_en']['details']['unlock_type'] ?? null,
			'level'            => $item['data_en']['level'],
			'value'            => $item['data_en']['vendor_value'],
			'pvp'              => in_array('Pvp', $item['data_en']['game_types']) && in_array('PvpLobby', $item['data_en']['game_types']),
			'attr_combination' => isset($item['data_en']['details']['infix_upgrade']) ? $this->attribute_combination($item['data_en']['details']['infix_upgrade']) : 0,
			'unlock_id'        => $item['data_en']['details']['recipe_id'] ?? $item['data_en']['details']['color_id'] ?? 0,
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
			'metadata'         => json_encode($item['@metadata']),
			'updated'          => 1,
			'id'               => $item['data_en']['id'],
		];

	}

	/**
	 * @param array $infix_upgrade
	 *
	 * @return int
	 *
	 * @link http://wiki.guildwars2.com/wiki/Item_nomenclature
	 * @link http://xkcd.com/221/
	 */
	protected function attribute_combination(array $infix_upgrade):int{
		$attributes = array_column($infix_upgrade['attributes'], 'attribute');

		if(isset($infix_upgrade['buff']['skill_id'])){
			switch((int)$infix_upgrade['buff']['skill_id']){
				// Condition Duration is only available as major attribute, so put it on top
				case 16631:
				case 25542:
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

	/**
	 * @todo
	 * @return void
	 */
	protected function initRefesh():void{
		// fetch both, old and new items
/*
		$this->old_items = $this->db->select
			->cols(['id', 'data_de', 'data_en', 'data_es', 'data_fr', 'data_zh'])
			->from([$this->options->tableItems])
			->execute('id')
			->__toArray();

		$this->logger->debug($this->processTimer().'old items fetched');
*/
		$this->temp_items = $this->db->select
			->cols([
				'id', 'blacklist', 'data_de', 'data_en', 'data_es', 'data_fr', 'data_zh',
				'response_time' => ['response_time', 'UNIX_TIMESTAMP']
			])
			->from([$this->options->tableItemsTemp])
			->query('id')
			->__toArray();

		$this->logger->debug($this->processTimer().'temp items fetched');

		// get the attribute combinations
		$combos = $this->db->select
			->cols(['id', 'attribute1', 'attribute2', 'attribute3', 'attribute4'])
			->from([$this->options->tableAttributeCombo])
			->query('id');

		foreach($combos as $combo){
			$combination = [
				'id'         => $combo->id,
				'attributes' => [$combo->attribute1],
			];

			if(!empty($combo->attribute2)){
				$combination['attributes'][] = $combo->attribute2;

				if(!empty($combo->attribute3)){
					$combination['attributes'][] = $combo->attribute3;

					if(!empty($combo->attribute4)){
						$combination['attributes'][] = $combo->attribute4;
					}
				}
			}

			$this->attribute_combinations[$combo->id] = $combination;
		}

		$this->logger->debug($this->processTimer().'attribute combos fetched');
	}

}
