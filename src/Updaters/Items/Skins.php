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

use chillerlan\Database\DBResultRow;
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

		$this->skins = $this->query->select
			->cols([
				'id', 'data_de', 'data_en', 'data_es', 'data_fr', 'data_zh',
				'update_time' => ['update_time', 'UNIX_TIMESTAMP'], 'date_added' => ['date_added', 'UNIX_TIMESTAMP']
			])
			->from([getenv('TABLE_GW2_SKINS')])
			->execute('id')
			->__toArray();

		if(count($this->skins) < 1){
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

		$result = $this->query->update
			->table(getenv('TABLE_GW2_SKINS'))
			->set(['name_'.$this->lang, 'data_'.$this->lang], false)
			->where('id', '?', '=', false)
			->execute(null, $response->json_array, [$this, 'callback']);

		if(!$result){
			$this->logToCLI('SQL insert failed, retrying URL. ('.$info->url.')');

			return new URL($info->url);
		}

		if(!empty($this->changes)){

			$result = $this->query->insert
				->into(getenv('TABLE_GW2_DIFF'))
				->values($this->changes)->execute();

			if($result){
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

		$old_data = $this->skins[$skin['id']]['data_'.$this->lang] ?? false;

		$old  = !$old_data ? [] : Helpers\array_sort_recursive(json_decode($old_data, true));
		$diff = Helpers\array_diff_assoc_recursive($old, $skin, true);

		if(!empty($old) && !empty($diff)){

			$this->changes[] = [
				'db_id' => $skin['id'],
				'type' => 'skin',
				'lang' => $this->lang,
				'date' => $this->skins[$skin['id']]['update_time'] ?? $this->skins[$skin['id']]['date_added'] ?? time(),
				'data' => json_encode($old),
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

		$this->skins = $this->query->select
			->cols(['data_en'])
			->from([getenv('TABLE_GW2_SKINS')])
			->execute();

		if(!$this->skins || $this->skins->length === 0){
			throw new UpdaterException('failed to fetch skin data from db');
		}

		$result = $this->query->update
			->table(getenv('TABLE_GW2_SKINS'))
			->set(['signature', 'file_id', 'type', 'subtype', 'properties', 'updated'], false)
			->where('id', '?', '=', false)
			->execute(null, $this->skins, [$this, 'statsCallback']);

		if(!$result){
			throw new UpdaterException('failed to update stats');
		}
	}

	/**
	 * @param \chillerlan\Database\DBResultRow $skin
	 *
	 * @return array
	 */
	public function statsCallback(DBResultRow $skin):array{
		$data = json_decode($skin->data_en);

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
