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
use chillerlan\GW2DB\Updaters\{MultiRequestAbstract, UpdaterException};
use chillerlan\TinyCurl\{ResponseInterface, URL};

/**
 */
class Colors extends MultiRequestAbstract{

	/**
	 * @var array
	 */
	protected $colors;

	/**
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 */
	public function init(){
		$this->refreshIDs('colors', getenv('TABLE_GW2_COLORS'));

		$this->colors = $this->query->select
			->cols([
				'id', 'data_de', 'data_en', 'data_es', 'data_fr', 'data_zh',
				'update_time' => ['update_time', 'UNIX_TIMESTAMP'], 'date_added' => ['date_added', 'UNIX_TIMESTAMP']
			])
			->from([getenv('TABLE_GW2_COLORS')])
			->execute('id')
			->__toArray();

		if(count($this->colors) < 1){
			throw new UpdaterException('failed to fetch color data from db');
		}

		$urls = [];

		foreach(array_chunk($this->colors, self::CHUNK_SIZE) as $chunk){
			foreach(self::API_LANGUAGES as $lang){
				$urls[] = new URL(self::API_BASE.'/colors', ['lang' => $lang, 'ids' => implode(',', array_column($chunk, 'id'))]);
			}
		}

		$this->fetchMulti($urls);
#		$this->updateStats();
		$this->logToCLI(__METHOD__.': end');
	}

	/**
	 * @param \chillerlan\TinyCurl\ResponseInterface $response
	 *
	 * @return mixed
	 */
	protected function processResponse(ResponseInterface $response){
		$info = $response->info;

		parse_str(parse_url($info->url, PHP_URL_QUERY), $params);

		$this->lang = $response->headers->{'content-language'} ?: $params['lang'];

		if(!$this->checkResponseLanguage($this->lang)){
			return false;
		}

		$result = $this->query->update
			->table(getenv('TABLE_GW2_COLORS'))
			->set(['name_'.$this->lang, 'data_'.$this->lang], false)
			->where('id', '?', '=', false)
			->execute(null, $response->json_array, [$this, 'callback']);

		if(!$result){
			$this->logToCLI('SQL insert failed, retrying URL. ('.$info->url.')');

			return new URL($info->url);
		}

		if(!empty($this->changes)){

			if($this->query->insert->into(getenv('TABLE_GW2_DIFF'))->values($this->changes)->execute()){
				$this->changes = [];
			}

		}

		$this->logToCLI('['.$this->lang.'] '.md5($info->url).' updated');
		return true;
	}

	/**
	 * @param array $color
	 *
	 * @return array
	 */
	public function callback(array $color){
		$old_data = $this->colors[$color['id']]['data_'.$this->lang] ?? false;

		$old   = !$old_data ? [] : json_decode($old_data, true);
		$diff  = Helpers\array_diff_assoc_recursive($old, $color, true);

		if(!empty($old) && !empty($diff)){
			$this->changes[] = [
				'db_id' => $color['id'],
				'type'  => 'color',
				'lang'  => $this->lang,
				'date'  => $this->colors[$color['id']]['update_time'] ?? $this->colors[$color['id']]['date_added'] ?? time(),
				'data'  => json_encode($old),
			];

			$this->logToCLI('['.$this->lang.'] color changed #'.$color['id'].' '.print_r($diff, true));
		}

		$this->logToCLI('['.$this->lang.'] updated color data #'.$color['id']);

		return [
			$color['name'],
			json_encode($color),
			$color['id'],
		];
	}

	/**
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 */
	protected function updateStats(){
		$this->colors = $this->query->select->cols(['data_en'])->from([getenv('TABLE_GW2_COLORS')])->execute();

		if(!$this->colors || $this->colors->length === 0){
			throw new UpdaterException('failed to fetch color data from db');
		}

		$result = $this->query->update
			->table(getenv('TABLE_GW2_COLORS'))
			->set(['hue', 'material', 'rarity', 'updated'], false)
			->where('id', '?', false, false)
			->execute(null, $this->colors, function(array $color):array{
				$data = json_decode($color['data_en']);

				list($hue, $material, $rarity) = !empty($data->categories) ? $data->categories : [null, null, null];

				$this->logToCLI('updated color stats #'.$data->id);

				return [$hue, $material, $rarity, 1, $data->id];
			});

		if(!$result){
			throw new UpdaterException('failed to update stats');
		}
	}

}


