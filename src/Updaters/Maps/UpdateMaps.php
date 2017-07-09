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

use chillerlan\GW2DB\Updaters\{MultiRequestAbstract, UpdaterException};
use chillerlan\TinyCurl\{ResponseInterface, URL};

class UpdateMaps extends MultiRequestAbstract{

	public function init(){
		$this->starttime = microtime(true);
		$this->logToCLI(__METHOD__.': start');

		$regions = $this->db->select
			->cols(['continent_id', 'region_id', 'floor_id'])
			->from([getenv('TABLE_GW2_REGIONS')])
			->execute();

		if(!$regions || !$regions->length === 0){
			throw new UpdaterException('failed to fetch maps from db, please run CreateRegions before');
		}

		$urls = [];

		foreach($regions as $region){
			foreach(self::API_LANGUAGES as $lang){
				$urls[] = new URL(self::API_BASE.'/continents/'.$region->continent_id.'/floors/'.$region->floor_id.'/regions/'.$region->region_id.'/maps', ['ids' => 'all', 'lang' => $lang]);
			}
		}

		$this->fetchMulti($urls);
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

		$lang = $response->headers->{'content-language'} ?: $params['lang'];

		if(!$this->checkResponseLanguage($lang)){
			return false;
		}

		$data = $response->json;

		if(is_array($data) && !empty($data)){

			list($continent, $floor, $region) = explode('/', str_replace(['/v2/continents/', 'floors/', '/regions', '/maps'], '', parse_url($info->url, PHP_URL_PATH)));

			foreach($data as $map){

				$this->db->update
					->table(getenv('TABLE_GW2_MAPS'))
					->set([
						'name_'.$lang  => $map->name,
						'data_'.$lang  => json_encode($map),
					])
					->where('continent_id', $continent)
					->where('region_id', $region)
					->where('floor_id', $floor)
					->where('map_id', $map->id)
					->execute();

				$this->logToCLI('updated map #'.$map->id.' ('.$lang.'), continent: '.$continent.', floor: '.$floor.', region: '.$region);
			}

		}

		return true;
	}

}
