<?php
/**
 * Class Colors
 *
 * @filesource   Colors.php
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

class Colors extends UpdaterAbstract{

	/**
	 * @var array
	 */
	protected $colors;

	/**
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 *
	 * @return void
	 */
	public function init():void{
		$this->logger->info(__METHOD__.': start');
		$this->refreshIDs('/colors', $this->options->tableColors);

		$this->colors = $this->db->select
			->cols([
				'id', 'data_de', 'data_en', 'data_es', 'data_fr', 'data_zh',
				'update_time' => ['update_time', 'UNIX_TIMESTAMP'], 'date_added' => ['date_added', 'UNIX_TIMESTAMP']
			])
			->from([$this->options->tableColors])
			->query('id')
			->__toArray();

		if(count($this->colors) < 1){
			throw new UpdaterException('failed to fetch color data from db');
		}

		foreach(array_chunk($this->colors, self::CHUNK_SIZE) as $chunk){
			foreach(self::API_LANGUAGES as $lang){
				$this->urls[] = ['/colors', ['lang' => $lang, 'ids' => implode(',', array_column($chunk, 'id'))]];
			}
		}

		$this->processURLs();
		$this->updateStats();
		$this->db->raw('OPTIMIZE TABLE '.$this->options->tableColors);

		$this->logger->info(__METHOD__.': end');
	}

	/**
	 * @param \chillerlan\HTTP\HTTPResponseInterface $response
	 * @param array|null                             $params
	 *
	 * @return void
	 */
	protected function processResponse(HTTPResponseInterface $response, array $params = null):void{
		$this->lang = $params[1]['lang'];
		$json       = $response->json_array;

		if(!is_array($json) || empty($json)){
			$this->addRetry('invalid response, retrying URL.', $params);
			return;
		}

		parse_str(parse_url($response->url, PHP_URL_QUERY), $query);

		$result = $this->db->update
			->table($this->options->tableColors)
			->set(['name_'.$this->lang, 'data_'.$this->lang], false)
			->where('id', '?', '=', false)
			->callback($response->json_array, [$this, 'insertCallback']);

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

		$this->logger->info('['.$this->lang.'] '.md5($response->url).' updated');
	}

	/**
	 * @param array $color
	 *
	 * @return array
	 */
	public function insertCallback(array $color):array{
#		$color = Helpers\array_sort_recursive($color);

		$old_data = $this->colors[$color['id']]['data_'.$this->lang] ?? false;

		$old   = !$old_data ? [] : json_decode($old_data, true);
		$diff  = Helpers\array_diff_assoc_recursive($old, $color, true);

		if(!empty($old) && !empty($diff)){
			$this->diff[] = [
				'db_id' => $color['id'],
				'type'  => 'color',
				'lang'  => $this->lang,
				'date'  => $this->colors[$color['id']]['update_time'] ?? $this->colors[$color['id']]['date_added'] ?? time(),
				'data'  => json_encode($old),
			];

			$this->logger->info('['.$this->lang.'] color changed #'.$color['id'].' '.json_encode($diff));
		}

		$this->logger->info('['.$this->lang.'] updated color data #'.$color['id']);

		return [
			$color['name'],
			json_encode($color),
			$color['id'],
		];
	}

	/**
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 *
	 * @return void
	 */
	protected function updateStats():void{
		$this->colors = $this->db->select
			->cols(['data_en'])
			->from([$this->options->tableColors])
			->query();

		if(!$this->colors || $this->colors->length === 0){
			throw new UpdaterException('failed to fetch color data from db');
		}

		$result = $this->db->update
			->table($this->options->tableColors)
			->set(['hue', 'material', 'rarity', 'updated'], false)
			->where('id', '?', '=', false)
			->callback($this->colors->__toArray(), function(array $color):array{
				$data = json_decode($color['data_en']);

				$this->logger->info('updated color stats #'.$data->id);

				return [
					$data->categories[0] ?? null,
					$data->categories[1] ?? null,
					$data->categories[2] ?? null,
					1,
					$data->id
				];
			});

		if(!$result){
			throw new UpdaterException('failed to update stats');
		}
	}

}


