<?php
/**
 * Class ItemTempUpdater
 *
 * @filesource   ItemTempUpdater.php
 * @created      24.02.2016
 * @package      chillerlan\GW2DB\Updaters\Items
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters\Items;

use chillerlan\GW2DB\Updaters\{MultiRequestAbstract, UpdaterException};
use chillerlan\TinyCurl\{ResponseInterface, URL};

class ItemTempUpdater extends MultiRequestAbstract{

	public function init(){
		$this->starttime = microtime(true);
		$this->logToCLI(__METHOD__.': start');
		$this->refreshIDs('items', getenv('TABLE_GW2_ITEMS_TEMP'));

		$sql = 'SELECT `id` FROM '.getenv('TABLE_GW2_ITEMS_TEMP').' WHERE `blacklist` = 0';

		if(!($items = $this->DBDriverInterface->raw($sql, 'id', true, true)) || !is_array($items)){
			throw new UpdaterException('failed to fetch item IDs from db');
		}

		$urls = [];

		foreach(array_chunk($items, self::CHUNK_SIZE) as $chunk){
			foreach(self::API_LANGUAGES as $lang){
				$urls[] = new URL(self::API_BASE.'/items', ['lang' => $lang, 'ids' => implode(',', array_column($chunk, 'id'))]);
			}
		}

		$this->fetchMulti($urls);
		$this->logToCLI(__METHOD__.': end');
	}

	/**
	 * @param \chillerlan\TinyCurl\ResponseInterface $response
	 *
	 * @return bool|\chillerlan\TinyCurl\URL
	 */
	protected function processResponse(ResponseInterface $response){
		$info = $response->info;
		// get the current request params
		parse_str(parse_url($info->url, PHP_URL_QUERY), $params);

		$lang = $response->headers->{'content-language'} ?: $params['lang'];

		if(!$this->checkResponseLanguage($lang)){
			return false;
		}

		// insert the data as soon as we receive it
		// this will result in a couple more database writes but won't block the responses much
		$sql   = 'UPDATE '.getenv('TABLE_GW2_ITEMS_TEMP').' SET `name_'.$lang.'` = ?, `data_'.$lang.'` = ? WHERE `id` = ?';
		$query = $this->DBDriverInterface->multi_callback($sql, $response->json, function($item){
			return [$item->name, json_encode($item), $item->id];
		});

		// retry if the insert failed for whatever reason
		if(!$query){
			$this->logToCLI('SQL insert failed, retrying URL. ('.$info->url.')');
			return new URL($info->url);
		}

		$this->logToCLI('['.$lang.'] '.md5($info->url).' updated');

		return true;
	}
}
