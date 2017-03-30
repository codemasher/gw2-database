<?php
/**
 * Class Skins
 *
 * @filesource   Skins.php
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
class Skins extends MultiRequestAbstract{

	/**
	 * @var array
	 */
	protected $skins;

	/**
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 */
	public function init(){
		$this->refreshIDs('skins', getenv('TABLE_GW2_SKINS'));

		$sql = 'SELECT `id`, `data_de`, `data_en`, `data_es`, `data_fr`, `data_zh`, UNIX_TIMESTAMP(`update_time`) AS `update_time`, UNIX_TIMESTAMP(`date_added`) AS `date_added` FROM `'.getenv('TABLE_GW2_SKINS').'`';

		$this->skins = $this->DBDriverInterface->raw($sql, 'id', true, true);

		if(!$this->skins || !is_array($this->skins)){
			throw new UpdaterException('failed to fetch skin data from db');
		}

		$urls = [];

		foreach(array_chunk($this->skins, self::CHUNK_SIZE) as $chunk){
			foreach(self::API_LANGUAGES as $lang){
				$urls[] = new URL(self::API_BASE.'/skins', ['lang' => $lang, 'ids' => implode(',', array_column($chunk, 'id'))]);
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

		$sql = 'UPDATE '.getenv('TABLE_GW2_SKINS').' SET `name_'.$this->lang.'`= ?, `data_'.$this->lang.'`= ? WHERE `id` = ?';

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

		return true;
	}

	/**
	 * @param array $skin
	 *
	 * @return array
	 */
	public function callback(array $skin):array{
		$skin = Helpers\array_sort_recursive($skin);
		$old  = Helpers\array_sort_recursive(json_decode(@$this->skins[$skin['id']]['data_'.$this->lang], true) ?? []);
		$diff = Helpers\array_diff_assoc_recursive($old, $skin, true);

		if(!empty($old) && !empty($diff)){

			$this->changes[] = [
				$skin['id'],
				'skin',
				$this->lang,
				$this->skins[$skin['id']]['update_time'] ?? $this->skins[$skin['id']]['date_added'] ?? time(),
				json_encode($old),
			];

			$this->logToCLI('['.$this->lang.'] skin changed #'.$skin['id'].' '.print_r($diff, true));
		}

		$this->logToCLI('['.$this->lang.'] updated skin data #'.$skin['id']);

		return [
			$skin['name'],
			json_encode($skin),
			$skin['id'],
		];
	}

	/**
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 */
	protected function updateStats(){
		$sql = 'SELECT `data_en` FROM `'.getenv('TABLE_GW2_SKINS').'`';

		$this->skins = $this->DBDriverInterface->raw($sql, null, true, true);

		if(!$this->skins || !is_array($this->skins)){
			throw new UpdaterException('failed to fetch skin data from db');
		}

		$sql = 'UPDATE '.getenv('TABLE_GW2_SKINS').' SET `signature`= ?, `file_id`= ?, `type`= ?, 
				`subtype`= ?, `properties` = ?, `updated`= ? WHERE `id` = ?';

		$query = $this->DBDriverInterface->multi_callback($sql, $this->skins, [$this, 'statsCallback']);

		if(!$query){
			throw new UpdaterException('failed to update stats');
		}
	}

	/**
	 * @param array $skin
	 *
	 * @return array
	 */
	public function statsCallback(array $skin):array{
		$data = json_decode($skin['data_en']);

		$file_id = explode('/', str_replace(['https://render.guildwars2.com/file/', '.png'], '', $data->icon ?? ''));

		$this->logToCLI('updated skin stats #'.$data->id);

		return [
			$file_id[0],
			$file_id[1] ?? 0,
			$data->type,
			$data->details->type ?? '',
			$data->details->weight_class ?? $data->details->damage_type ?? '',
			1,
			$data->id,
		];
	}
}
