<?php
/**
 * Class UpdateMaps
 *
 * @filesource   UpdateMaps.php
 * @created      13.04.2016
 * @package      chillerlan\GW2DB\Updaters\Maps
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters\Maps;

use chillerlan\GW2DB\Updaters\UpdaterAbstract;
use chillerlan\GW2DB\Updaters\UpdaterException;
use chillerlan\TinyCurl\MultiResponseHandlerInterface;
use chillerlan\TinyCurl\ResponseInterface;
use chillerlan\TinyCurl\URL;

class UpdateMaps extends UpdaterAbstract implements MultiResponseHandlerInterface{

	public function init(){
		$this->starttime = microtime(true);
		$this->logToCLI(__METHOD__.': start');
		$sql = 'SELECT `continent_id`, `region_id`, `floor_id`, `map_id` FROM '.self::MAPS_TABLE;

		if(!($maps = $this->DBDriverInterface->raw($sql)) || !is_array($maps)){
			throw new UpdaterException('failed to fetch maps from db, please run CreateRegions before');
		}

		$urls = [];

		foreach($maps as $map){
			foreach(self::API_LANGUAGES as $lang){
				$urls[] = new URL(self::API_BASE.'/continents/'.$map->continent_id.'/floors/'.$map->floor_id.'/regions/'.$map->region_id.'/maps/'.$map->map_id, ['lang' => $lang]);
			}
		}

		$this->fetchMulti($urls);
		$this->logToCLI(__METHOD__.': end');
	}

	public function handleResponse(ResponseInterface $response){
		$info = $response->info;

		if(in_array($info->http_code, [200, 206], true)){
			parse_str(parse_url($info->url, PHP_URL_QUERY), $params);

			$lang = $response->headers->{'content-language'} ?: $params['lang'];

			if(!$this->checkResponseLanguage($lang)){
				return false;
			}

			$data = $response->json;

			list($continent, $floor, $region, $map) = explode('/', str_replace(['/v2/continents/', 'floors/', '/regions', '/maps'], '', parse_url($info->url, PHP_URL_PATH)));

			$sql = 'UPDATE '.self::MAPS_TABLE.' SET `name_'.$lang.'` = ?, `data_'.$lang.'` = ? WHERE `continent_id` = ? AND `region_id` = ? AND `floor_id` = ? AND `map_id` = ?';

			$values = [
				'name_'.$lang  => $data->name,
				'data_'.$lang  => json_encode($data),
				'continent_id' => $continent,
				'region_id'    => $region,
				'floor_id'     => $floor,
				'map_id'       => $map,
			];

			$this->DBDriverInterface->prepared($sql, $values);

			$this->logToCLI('updated map #'.$map.' ('.$lang.'), continent: '.$continent.', floor: '.$floor.', region: '.$region);

			return true;
		}
		elseif($info->http_code === 502){
			$this->logToCLI('URL readded due to a 502. ('.$info->url.')');
			return new URL($info->url);
		}

		return false;
	}

}
