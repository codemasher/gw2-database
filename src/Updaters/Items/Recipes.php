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
use chillerlan\GW2DB\Updaters\{UpdaterAbstract, UpdaterException};
use chillerlan\HTTP\HTTPResponseInterface;

class Recipes extends UpdaterAbstract{

	protected const CRAFT_ARMORSMITH    = 0x1;
	protected const CRAFT_ARTIFICER     = 0x2;
	protected const CRAFT_CHEF          = 0x4;
	protected const CRAFT_HUNTSMAN      = 0x8;
	protected const CRAFT_JEWELER       = 0x10;
	protected const CRAFT_LEATHERWORKER = 0x20;
	protected const CRAFT_TAILOR        = 0x40;
	protected const CRAFT_WEAPONSMITH   = 0x80;
	protected const CRAFT_SCRIBE        = 0x100;

	/**
	 * @var array
	 */
	protected $recipes;

	/**
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 *
	 * @return void
	 */
	public function init():void{
		$this->logger->info(__METHOD__.': start');
		$this->refreshIDs('/recipes', $this->options->tableRecipes);

		$this->recipes = $this->db->select
			->cols(['id', 'data', 'update_time' => ['update_time', 'UNIX_TIMESTAMP'], 'date_added' => ['date_added', 'UNIX_TIMESTAMP']])
			->from([$this->options->tableRecipes])
			->query('id')
			->__toArray();

		if(count($this->recipes) < 1){
			throw new UpdaterException('failed to fetch recipe IDs from db');
		}

		foreach(array_chunk($this->recipes, self::CHUNK_SIZE) as $chunk){
			$this->urls[] = ['/recipes', ['ids' => implode(',', array_column($chunk, 'id'))]];
		}

		$this->processURLs();
		$this->db->raw('OPTIMIZE TABLE '.$this->options->tableRecipes);

		$this->logger->info(__METHOD__.': end');
	}

	/**
	 * @param \chillerlan\HTTP\HTTPResponseInterface $response
	 * @param array|null                             $params
	 *
	 * @return void
	 */
	protected function processResponse(HTTPResponseInterface $response, array $params = null):void{
		$json = $response->json_array;

		if(!is_array($json) || empty($json)){
			$this->addRetry('invalid response, retrying URL.', $params);
			return;
		}

		$result = $this->db->update
			->table($this->options->tableRecipes)
			->set([
				'output_id', 'output_count', 'disciplines', 'rating', 'type', 'from_item',
				'ing_id_1', 'ing_count_1', 'ing_id_2', 'ing_count_2', 'ing_id_3', 'ing_count_3',
				'ing_id_4', 'ing_count_4', 'data', 'updated'
			], false)
			->where('id', '?', '=', false)
			->callback($json, [$this, 'insertCallback']);

		if(!$result){
			$this->addRetry('SQL insert failed, retrying URL. ('.$response->url.')', $params);
			return;
		}

		if(!empty($this->diff)){

			$result = $this->db->insert
				->into($this->options->tableDiff)
				->values($this->diff)
				->multi();

			if($result){
				$this->diff = [];
			}
		}

		$this->logger->info(md5($response->url).' updated');
	}

	/**
	 * @param array $recipe
	 *
	 * @return array
	 */
	public function insertCallback(array $recipe):array{
		$recipe = $this->sortRecipe($recipe);

		$old_data = $this->recipes[$recipe['id']]['data'] ?? false;

		$old    = !$old_data ? [] : $this->sortRecipe(json_decode($old_data, true));
		$diff   = Helpers\array_diff_assoc_recursive($old, $recipe, true);

		if(!empty($old) && !empty($diff)){

			$this->diff[] = [
				'db_id' => $recipe['id'],
				'type' => 'recipe',
				'date' => $this->recipes[$recipe['id']]['update_time'] ?? $this->recipes[$recipe['id']]['date_added'] ?? time(),
				'data' => json_encode($old),
			];

			$this->logger->info('recipe changed #'.$recipe['id'].' '.json_encode($diff));
		}

		$disciplines = array_map(function($value){
			return constant('self::CRAFT_'.strtoupper($value));
		}, $recipe['disciplines']);

		$this->logger->info('updated recipe #'.$recipe['id']);

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
