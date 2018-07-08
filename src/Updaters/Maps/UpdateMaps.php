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

use chillerlan\GW2DB\Updaters\{UpdaterAbstract, UpdaterException};
use chillerlan\HTTP\HTTPResponseInterface;

class UpdateMaps extends UpdaterAbstract{

	/**
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 *
	 * @return void
	 */
	public function init():void{
		$this->logger->info(__METHOD__.': start');

		$regions = $this->db->select
			->cols(['continent_id', 'region_id', 'floor_id'])
			->from([$this->options->tableRegions])
			->query();

		if(!$regions || !$regions->length === 0){
			throw new UpdaterException('failed to fetch maps from db, please run CreateRegions before');
		}

		foreach($regions as $region){
			foreach(self::API_LANGUAGES as $lang){
				$this->urls[] = ['/continents/'.$region->continent_id.'/floors/'.$region->floor_id.'/regions/'.$region->region_id.'/maps', ['ids' => 'all', 'lang' => $lang]];
			}
		}

		$this->processURLs();
		$this->logger->info(__METHOD__.': end');
	}

	/**
	 * @param \chillerlan\HTTP\HTTPResponseInterface $response
	 * @param array|null                             $params
	 *
	 * @return void
	 */
	protected function processResponse(HTTPResponseInterface $response, array $params = null):void{
		$lang = $params[1]['lang'];
		$data = $response->json;

		if(!is_array($data) || empty($data)){
			return;
		}

		[$continent, $floor, $region] = explode('/', str_replace(['/v2/continents/', 'floors/', '/regions', '/maps'], '', parse_url($response->url, PHP_URL_PATH)));

		foreach($data as $map){

			$this->db->update
				->table($this->options->tableMaps)
				->set([
					'name_'.$lang  => $map->name,
					'data_'.$lang  => json_encode($map),
				])
				->where('continent_id', $continent)
				->where('region_id', $region)
				->where('floor_id', $floor)
				->where('map_id', $map->id)
				->query();

			$this->logger->info('updated map #'.$map->id.' ('.$lang.'), continent: '.$continent.', floor: '.$floor.', region: '.$region);
		}

	}

}
