<?php
/**
 *
 * @filesource   TempUpdater.php
 * @created      24.02.2016
 * @package      chillerlan\GW2DB\Updaters\Items
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters\Items;

use chillerlan\GW2DB\Updaters\UpdaterBase;
use chillerlan\GW2DB\Updaters\UpdaterException;
use chillerlan\GW2DB\Updaters\UpdaterInterface;
use chillerlan\TinyCurl\MultiRequest;
use chillerlan\TinyCurl\MultiRequestOptions;
use chillerlan\TinyCurl\Response\MultiResponseHandlerInterface;
use chillerlan\TinyCurl\Response\ResponseInterface;
use chillerlan\TinyCurl\URL;

/**
 * Class TempUpdater
 */
class TempUpdater extends UpdaterBase implements UpdaterInterface, MultiResponseHandlerInterface{

	const ITEM_TEMP_TABLE = 'gw2_items_temp';

	/**
	 * @var array
	 */
	protected $urls = [];

	/**
	 * @throws \chillerlan\TinyCurl\RequestException
	 */
	public function init(){
		$this->starttime = microtime(true);
		$this->logToCLI(__METHOD__.': start');
		$this->refreshIDs('items', self::ITEM_TEMP_TABLE);
		$this->getURLs();

		$options = new MultiRequestOptions;
		$options->ca_info     = self::CACERT;
		$options->window_size = self::CONCURRENT;

		$this->logToCLI(__METHOD__.': multirequest start');
		$request = new MultiRequest($options);
		// solving the hen-egg problem, feed the hen with the egg!
		$request->setHandler($this);

		$request->fetch($this->urls);
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

		// get the current request params
		parse_str(parse_url($info->url, PHP_URL_QUERY), $params);

		// there be dragons.
		if(in_array($info->http_code, [200, 206], true)){
			$lang = $response->headers->{'content-language'} ?: $params['lang'];

			// discard the response when it's impossible to determine the language
			if(!in_array($lang, self::API_LANGUAGES)){
				$this->logToCLI('URL discarded. ('.$info->url.')');
				return false;
			}

			// insert the data as soon as we receive it
			// this will result in a couple more database writes but won't block the responses much
			$sql   = 'UPDATE '.self::ITEM_TEMP_TABLE.' SET `name_'.$lang.'` = ?, `data_'.$lang.'` = ? WHERE `id` = ?';
			$query = $this->MySQLiDriver->multi_callback($sql, $response->json, function($item){
				return [
					$item->name,
					json_encode($item),
					$item->id
				];
			});

			if($query){
				$this->logToCLI('['.$lang.'] '.md5($response->info->url).' updated');
				return true;
			}
			else{
				// retry if the insert failed for whatever reason
				$this->logToCLI('SQL insert failed, retrying URL. ('.$info->url.')');
				return new URL($info->url);
			}

		}
		// instant retry on a 502
		// https://gitter.im/arenanet/api-cdi?at=56c3ba6ba5bdce025f69bcc8
		else if($info->http_code === 502){
			$this->logToCLI('URL readded due to a 502. ('.$info->url.')');
			return new URL($info->url);
		}
		// examine and add the failed response to retry later @todo
		else{
			var_dump($info);
#			$this->logToCLI('('.$info->url.')');
			return false;
		}

	}

	/**
	 * Loads all the item IDs currently stored in the DB and creates the request URLs
	 *
	 * going to blow up the memory here...
	 */
	protected function getURLs(){
		$this->starttime = microtime(true);
		$this->logToCLI(__METHOD__.': start');

		if($items = $this->MySQLiDriver->raw('SELECT `id` FROM '.self::ITEM_TEMP_TABLE.' WHERE `blacklist` = 0', 'id', true, true)){

			array_map(function($chunk){
				foreach(self::API_LANGUAGES as $lang){
					$this->urls[] = new URL(self::API_BASE.'items?lang='.$lang.'&ids='.implode(',', array_column($chunk, 'id')));
				}
			}, array_chunk($items, self::CHUNK_SIZE));

			$this->logToCLI(__METHOD__.': end');
		}
		else{
			throw new UpdaterException('failed to fetch item IDs from db');
		}
	}


}
