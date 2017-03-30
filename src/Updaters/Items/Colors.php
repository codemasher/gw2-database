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

		$sql = 'SELECT `id`, `data_de`, `data_en`, `data_es`, `data_fr`, `data_zh`, UNIX_TIMESTAMP(`update_time`) AS `update_time`, UNIX_TIMESTAMP(`date_added`) AS `date_added` FROM `'.getenv('TABLE_GW2_COLORS').'`';

		$this->colors = $this->DBDriverInterface->raw($sql, 'id', true, true);

		if(!$this->colors || !is_array($this->colors)){
			throw new UpdaterException('failed to fetch color data from db');
		}

		$urls = [];

		foreach(array_chunk($this->colors, self::CHUNK_SIZE) as $chunk){
			foreach(self::API_LANGUAGES as $lang){
				$urls[] = new URL(self::API_BASE.'/colors', ['lang' => $lang, 'ids' => implode(',', array_column($chunk, 'id'))]);
			}
		}

		$this->fetchMulti($urls);
		$this->updateStats();
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

		$sql = 'UPDATE '.getenv('TABLE_GW2_COLORS').' SET `name_'.$this->lang.'`= ?, `data_'.$this->lang.'`= ? WHERE `id` = ?';

		$query = $this->DBDriverInterface->multi_callback($sql, $response->json_array, [$this, 'callback']);

		if(!$query){
			$this->logToCLI('SQL insert failed, retrying URL. ('.$info->url.')');

			return new URL($info->url);
		}

		if(!empty($this->changes)){
			$sql = 'INSERT INTO `'.getenv('TABLE_GW2_DIFF').'` (`db_id`, `type`, `lang`, `date`, `data`) VALUES (?,?,?,?,?)';

			if($this->DBDriverInterface->multi($sql, $this->changes)){
				$this->changes = [];
			}
		}

		$this->logToCLI('['.$this->lang.'] '.md5($info->url).' updated');
	}

	/**
	 * @param array $color
	 *
	 * @return array
	 */
	public function callback(array $color){
		$old   = json_decode(@$this->colors[$color['id']]['data_'.$this->lang], true) ?? [];
		$diff  = Helpers\array_diff_assoc_recursive($old, $color, true);

		if(!empty($old) && !empty($diff)){
			$this->changes[] = [
				$color['id'],
				'color',
				$this->lang,
				$this->colors[$color['id']]['update_time'] ?? $this->colors[$color['id']]['date_added'] ?? time(),
				json_encode($old),
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
		$sql = 'SELECT `data_en` FROM `'.getenv('TABLE_GW2_COLORS').'`';

		$this->colors = $this->DBDriverInterface->raw($sql, null, true, true);

		if(!$this->colors || !is_array($this->colors)){
			throw new UpdaterException('failed to fetch color data from db');
		}

		$sql = 'UPDATE '.getenv('TABLE_GW2_COLORS').' SET `hue`= ?, `material`= ?, `rarity`= ?, `updated`= ? WHERE `id` = ?';

		$query = $this->DBDriverInterface->multi_callback($sql, $this->colors, [$this, 'statsCallback']);

		if(!$query){
			throw new UpdaterException('failed to update stats');
		}
	}

	/**
	 * @param array $color
	 *
	 * @return array
	 */
	public function statsCallback(array $color):array{
		$data = json_decode($color['data_en']);

		list($hue, $material, $rarity) = !empty($data->categories) ? $data->categories : [null, null, null];

		$this->logToCLI('updated color stats #'.$data->id);

		return [
			$hue,
			$material,
			$rarity,
			1,
			$data->id,
		];
	}

}


