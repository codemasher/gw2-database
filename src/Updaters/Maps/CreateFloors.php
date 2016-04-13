<?php
/**
 * Class CreateFloors
 *
 * @filesource   CreateFloors.php
 * @created      12.04.2016
 * @package      chillerlan\GW2DB\Updaters\Maps
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters\Maps;

use chillerlan\GW2DB\Updaters\UpdaterAbstract;
use chillerlan\TinyCurl\Response\MultiResponseHandlerInterface;
use chillerlan\TinyCurl\Response\ResponseInterface;
use chillerlan\TinyCurl\URL;

class CreateFloors extends UpdaterAbstract implements MultiResponseHandlerInterface{

	public function init(){
		$this->starttime = microtime(true);
		$this->logToCLI(__METHOD__.': start');

		// get floor URLs
		$urls = [];
		foreach(self::CONTINENTS as $continent){
			$response = $this->fetch(self::API_BASE.'/continents/'.$continent);
			if($response->info->http_code === 200){
				$floordata = $response->json;
				if(is_array($floordata->floors)){
					foreach($floordata->floors as $floor){
						$urls[] = new URL(self::API_BASE.'/continents/'.$continent.'/floors/'.$floor.'/regions');
					}
				}
			}
		}

		$this->DBDriverInterface->raw('TRUNCATE TABLE '.self::MAPS_FLOOR_TABLE);
		$this->fetchMulti($urls);
		$this->logToCLI(__METHOD__.': end');
	}

	public function handleResponse(ResponseInterface $response){
		$info = $response->info;

		if(in_array($info->http_code, [200, 206], true)){
			list($continent, $floor) = explode('/', str_replace(['/v2/continents/', 'floors/', '/regions'], '', parse_url($info->url, PHP_URL_PATH)));
			
			$sql = 'INSERT INTO '.self::MAPS_FLOOR_TABLE.' (`continent_id`, `floor_id`, `regions`) VALUES (?,?,?)';
			$this->DBDriverInterface->prepared($sql, [
				'continent_id' => $continent,
				'floor_id'     => $floor,
				'regions'      => json_encode($response->json),
			]);

			$this->logToCLI('updating continent #'.$continent.', floor '.$floor.', data: '.$response->body->content);
			return true;
		}
		elseif($info->http_code === 502){
			$this->logToCLI('URL readded due to a 502. ('.$info->url.')');
			return new URL($info->url);
		}

		return false;
	}

}
