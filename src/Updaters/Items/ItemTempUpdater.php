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

use chillerlan\GW2DB\Updaters\UpdaterAbstract;
use chillerlan\GW2DB\Updaters\UpdaterException;
use chillerlan\TinyCurl\Response\MultiResponseHandlerInterface;
use chillerlan\TinyCurl\Response\ResponseInterface;
use chillerlan\TinyCurl\URL;

class ItemTempUpdater extends UpdaterAbstract implements MultiResponseHandlerInterface{

	public function init(){
		$this->starttime = microtime(true);
		$this->logToCLI(__METHOD__.': start');
		$this->refreshIDs('items', self::ITEM_TEMP_TABLE);

		$sql = 'SELECT `id` FROM '.self::ITEM_TEMP_TABLE.' WHERE `blacklist` = 0';

		if(!($items = $this->DBDriverInterface->raw($sql, 'id', true, true)) || !is_array($items)){
			throw new UpdaterException('failed to fetch item IDs from db');
		}

		$urls = [];

		foreach(array_chunk($items, self::CHUNK_SIZE) as $chunk){
			foreach(self::API_LANGUAGES as $lang){
				$urls[] = new URL(self::API_BASE.'/items?lang='.$lang.'&ids='.implode(',', array_column($chunk, 'id')));
			}
		}

		$this->fetchMulti($urls);
		$this->logToCLI(__METHOD__.': end');
	}

	/**
	 * Schrödingers cat state handler.
	 *
	 * This method will be called within a loop in MultiRequest::processStack().
	 * You can either build your class around this MultiResponseHandlerInterface to process
	 * the response during runtime or return the response data to the running
	 * MultiRequest instance via addResponse() and receive the data by calling getResponseData().
	 *
	 * This method may return void or an URL object as a replacement for a failed request,
	 * which then will be re-added to the running queue.
	 *
	 * However, the return value will not be checked, so make sure you return valid URLs. ;)
	 *
	 * @param \chillerlan\TinyCurl\Response\ResponseInterface $response
	 *
	 * @return bool|\chillerlan\TinyCurl\URL
	 * @internal
	 */
	public function handleResponse(ResponseInterface $response){
		$info = $response->info;

		// there be dragons.
		if(in_array($info->http_code, [200, 206], true)){
			// get the current request params
			parse_str(parse_url($info->url, PHP_URL_QUERY), $params);

			$lang = $response->headers->{'content-language'} ?: $params['lang'];

			if(!$this->checkResponseLanguage($lang)){
				return false;
			}

			// insert the data as soon as we receive it
			// this will result in a couple more database writes but won't block the responses much
			$sql   = 'UPDATE '.self::ITEM_TEMP_TABLE.' SET `name_'.$lang.'` = ?, `data_'.$lang.'` = ? WHERE `id` = ?';
			$query = $this->DBDriverInterface->multi_callback($sql, $response->json, function($item){
				return [$item->name, json_encode($item), $item->id];
			});

			// retry if the insert failed for whatever reason
			if(!$query){
				$this->logToCLI('SQL insert failed, retrying URL. ('.$info->url.')');
				return new URL($info->url);
			}
			
			$this->logToCLI('['.$lang.'] '.md5($response->info->url).' updated');
			return true;
		}
		// instant retry on a 502
		// https://gitter.im/arenanet/api-cdi?at=56c3ba6ba5bdce025f69bcc8
		elseif($info->http_code === 502){
			$this->logToCLI('URL readded due to a 502. ('.$info->url.')');
			return new URL($info->url);
		}

		// examine and add the failed response to retry later @todo
		var_dump($info);
		$this->logToCLI('unknown error: ('.$info->url.')');
		return false;
	}

}
