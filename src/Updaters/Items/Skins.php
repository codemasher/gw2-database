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
use chillerlan\GW2DB\Updaters\{UpdaterAbstract, UpdaterException};
use chillerlan\HTTP\HTTPResponseInterface;

class Skins extends UpdaterAbstract{

	/**
	 * @var array
	 */
	protected $skins;

	/**
	 * @return void
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 */
	public function init():void{
		$this->logger->info(__METHOD__.': start');
		$this->refreshIDs('/skins', $this->options->tableSkins);

		$this->skins = $this->db->select
			->cols([
				'id', 'data_de', 'data_en', 'data_es', 'data_fr', 'data_zh',
				'update_time' => ['update_time', 'UNIX_TIMESTAMP'], 'date_added' => ['date_added', 'UNIX_TIMESTAMP']
			])
			->from([$this->options->tableSkins])
			->query('id')
			->__toArray();

		if(count($this->skins) < 1){
			throw new UpdaterException('failed to fetch skin data from db');
		}

		foreach(array_chunk($this->skins, self::CHUNK_SIZE) as $chunk){
			foreach(self::API_LANGUAGES as $lang){
				$this->urls[] = ['/skins', ['lang' => $lang, 'ids' => implode(',', array_column($chunk, 'id'))]];
			}
		}

		$this->processURLs();
		$this->updateStats();
		$this->db->raw('OPTIMIZE TABLE '.$this->options->tableSkins);

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

		parse_str(parse_url($response->url, PHP_URL_QUERY), $params);

		$result = $this->db->update
			->table($this->options->tableSkins)
			->set(['name_'.$this->lang, 'data_'.$this->lang], false)
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

		$this->logger->info('['.$this->lang.'] '.md5($response->url).' updated');
	}

	/**
	 * @param array $skin
	 *
	 * @return array
	 */
	public function insertCallback(array $skin):array{
		$skin = Helpers\array_sort_recursive($skin);

		$old_data = $this->skins[$skin['id']]['data_'.$this->lang] ?? false;

		$old  = !$old_data ? [] : Helpers\array_sort_recursive(json_decode($old_data, true));
		$diff = Helpers\array_diff_assoc_recursive($old, $skin, true);

		if(!empty($old) && !empty($diff)){

			$this->diff[] = [
				'db_id' => $skin['id'],
				'type' => 'skin',
				'lang' => $this->lang,
				'date' => $this->skins[$skin['id']]['update_time'] ?? $this->skins[$skin['id']]['date_added'] ?? time(),
				'data' => json_encode($old),
			];

			$this->logger->info('['.$this->lang.'] skin changed #'.$skin['id'].' '.json_encode($diff));
		}

		$this->logger->info('['.$this->lang.'] updated skin data #'.$skin['id']);

		return [
			$skin['name'],
			json_encode($skin),
			$skin['id'],
		];
	}

	/**
	 * @return void
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 */
	protected function updateStats():void{

		$this->skins = $this->db->select
			->cols(['data_en'])
			->from([$this->options->tableSkins])
			->query();

		if(!$this->skins || $this->skins->length === 0){
			throw new UpdaterException('failed to fetch skin data from db');
		}

		$result = $this->db->update
			->table($this->options->tableSkins)
			->set(['signature', 'file_id', 'type', 'subtype', 'properties', 'updated'], false)
			->where('id', '?', '=', false)
			->callback($this->skins->__toArray(), [$this, 'statsCallback']);

		if(!$result){
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

		$this->logger->info('updated skin stats #'.$data->id);

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
