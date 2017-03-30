<?php
/**
 * Class Recipes
 *
 * @filesource   Recipes.php
 * @created      30.03.2017
 * @package      chillerlan\GW2DB\Updaters\Items
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters\Items;

use chillerlan\GW2DB\Helpers;
use chillerlan\GW2DB\Updaters\{MultiRequestAbstract, UpdaterException};
use chillerlan\TinyCurl\{ResponseInterface, URL};

/**
 *
 */
class Recipes extends MultiRequestAbstract{

	const CRAFT_ARMORSMITH    = 0x1;
	const CRAFT_ARTIFICER     = 0x2;
	const CRAFT_CHEF          = 0x4;
	const CRAFT_HUNTSMAN      = 0x8;
	const CRAFT_JEWELER       = 0x10;
	const CRAFT_LEATHERWORKER = 0x20;
	const CRAFT_TAILOR        = 0x40;
	const CRAFT_WEAPONSMITH   = 0x80;
	const CRAFT_SCRIBE        = 0x100;

	/**
	 * @var array
	 */
	protected $recipes;

	/**
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 */
	public function init(){
		$this->refreshIDs('recipes', getenv('TABLE_GW2_RECIPES'));

		$sql = 'SELECT `id`, `data`, UNIX_TIMESTAMP(`update_time`) AS `update_time`, UNIX_TIMESTAMP(`date_added`) AS `date_added` FROM `'.getenv('TABLE_GW2_RECIPES').'`';

		$this->recipes = $this->DBDriverInterface->raw($sql, 'id', true, true);

		if(!$this->recipes || !is_array($this->recipes)){
			throw new UpdaterException('failed to fetch recipe IDs from db');
		}

		$urls = [];

		foreach(array_chunk($this->recipes, self::CHUNK_SIZE) as $chunk){
			$urls[] = new URL(self::API_BASE.'/recipes', ['ids' => implode(',', array_column($chunk, 'id'))]);
		}

		$this->fetchMulti($urls);
		$this->logToCLI(__METHOD__.': end');
	}

	/**
	 * @param \chillerlan\TinyCurl\ResponseInterface $response
	 *
	 * @return mixed
	 */
	protected function processResponse(ResponseInterface $response){
		$info = $response->info;
		$json = $response->json_array;

		if(!is_array($json) || empty($json)){
			return false;
		}

		$sql = 'UPDATE `'.getenv('TABLE_GW2_RECIPES').'` SET `output_id` = ?, `output_count` = ?, `disciplines`= ?,
				`rating` = ?, `type` = ?, `from_item` = ?, `ing_id_1` = ?, `ing_count_1` = ?,
				`ing_id_2` = ?, `ing_count_2` = ?, `ing_id_3` = ?, `ing_count_3` = ?,
				`ing_id_4` = ?, `ing_count_4` = ?, `data` = ?, `updated` = ?
				WHERE `id` = ?';

		$query = $this->DBDriverInterface->multi_callback($sql, $json, [$this, 'callback']);

		if(!$query){
			$this->logToCLI('SQL insert failed, retrying URL. ('.$info->url.')');

			return new URL($info->url);
		}

		if(!empty($this->changes)){
			$sql = 'INSERT INTO `'.getenv('TABLE_GW2_DIFF').'` (`db_id`, `type`, `date`, `data`) VALUES (?,?,?,?)';

			if($this->DBDriverInterface->multi($sql, $this->changes)){
				$this->changes = [];
			}
		}

		return true;
	}

	/**
	 * @param array $recipe
	 *
	 * @return array
	 */
	public function callback(array $recipe):array{
		$recipe = $this->sortRecipe($recipe);
		$old    = $this->sortRecipe(json_decode(@$this->recipes[$recipe['id']]['data'], true) ?? []);
		$diff   = Helpers\array_diff_assoc_recursive($old, $recipe, true);

		if(!empty($old) && !empty($diff)){

			$this->changes[] = [
				$recipe['id'],
				'recipe',
				$this->recipes[$recipe['id']]['update_time'] ?? $this->recipes[$recipe['id']]['date_added'] ?? time(),
				json_encode($old),
			];

			$this->logToCLI('recipe changed #'.$recipe['id'].' '.print_r($diff, true));
		}

		$disciplines = array_map(function($value){
			return constant('self::CRAFT_'.strtoupper($value));
		}, $recipe['disciplines']);

		$this->logToCLI('updated recipe #'.$recipe['id']);

		return [
			$recipe['output_item_id'],
			$recipe['output_item_count'],
			Helpers\set_bitflag($disciplines),
			$recipe['min_rating'],
			$recipe['type'],
			isset($recipe['flags']) && is_array($recipe['flags']) && in_array('LearnedFromItem', $recipe['flags']),
			$recipe['ingredients'][0]['item_id'] ?? 0,
			$recipe['ingredients'][0]['count'] ?? 0,
			$recipe['ingredients'][1]['item_id'] ?? 0,
			$recipe['ingredients'][1]['count'] ?? 0,
			$recipe['ingredients'][2]['item_id'] ?? 0,
			$recipe['ingredients'][2]['count'] ?? 0,
			$recipe['ingredients'][3]['item_id'] ?? 0,
			$recipe['ingredients'][3]['count'] ?? 0,
			json_encode($recipe),
			1,
			$recipe['id'],
		];
	}

	/**
	 * preserve order of the ingrediends
	 *
	 * @param array $recipe
	 *
	 * @return array
	 */
	protected function sortRecipe(array $recipe):array{
		$ingredients       = $recipe['ingredients'] ?? null;
		$guild_ingredients = $recipe['guild_ingredients'] ?? null;

		$recipe = Helpers\array_sort_recursive($recipe);

		if($ingredients){
			$recipe['ingredients'] = $ingredients;
		}

		if($guild_ingredients){
			$recipe['guild_ingredients'] = $guild_ingredients;
		}

		return $recipe;
	}
}
