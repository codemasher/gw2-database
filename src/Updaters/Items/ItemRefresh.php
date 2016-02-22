<?php
/**
 * Class ItemRefresh
 *
 * @filesource   ItemRefresh.php
 * @created      22.02.2016
 * @package      chillerlan\GW2DB\Updaters\Items
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters\Items;

use chillerlan\GW2DB\Updaters\UpdaterBase;
use chillerlan\GW2DB\Updaters\UpdaterException;
use chillerlan\GW2DB\Updaters\UpdaterInterface;

/**
 *
 */
class ItemRefresh extends UpdaterBase implements UpdaterInterface{

	const ITEM_TABLE = 'gw2_items_temp';

	public function init(){
		$this->starttime = microtime(true);
		$this->logToCLI(__METHOD__.': start');

		$response = $this->fetch(self::API_BASE.'items');
		$this->logToCLI(__METHOD__.': response');

		if($response->info->http_code !== 200){
			throw new UpdaterException('failed to get /v2/items');
		}

		$this->GW2MySQLiDriver->multi_callback('INSERT IGNORE INTO '.self::ITEM_TABLE.' (`id`) VALUES (?)', $response->json, function($item){
			return [$item[0]];
		});

		$this->logToCLI(__METHOD__.': end');
	}
}