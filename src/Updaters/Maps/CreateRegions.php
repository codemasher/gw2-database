<?php
/**
 * Class CreateRegions
 *
 * @filesource   CreateRegions.php
 * @created      12.04.2016
 * @package      chillerlan\GW2DB\Updaters\Maps
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters\Maps;

use chillerlan\GW2DB\Updaters\{UpdaterAbstract, UpdaterException};
use chillerlan\HTTP\HTTPResponseInterface;

class CreateRegions extends UpdaterAbstract{

	/**
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 *
	 * @return void
	 */
	public function init():void{
		$this->logger->info(__METHOD__.': start');

		$floors = $this->db->select
			->from([$this->options->tableMapFloors])
			->query();

		if(!$floors || $floors->length === 0){
			throw new UpdaterException('failed to fetch floors from db, please run CreateFloors before');
		}

		foreach($floors as $floor){
			$regions = json_decode($floor->regions, true);
			if(is_array($regions) && !empty($regions)){
				foreach($regions as $regionID){
					$this->urls[] = ['/continents/'.$floor->continent_id.'/floors/'.$floor->floor_id.'/regions/'.$regionID];
				}
			}
		}

		$this->db->truncate->table($this->options->tableRegions)->query();
		$this->db->truncate->table($this->options->tableMaps)->query();

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
		$data = $response->json;
		$maps = [];

		[$continent, $floor, $region] = explode('/', str_replace(['/v2/continents/', 'floors/', '/regions'], '', parse_url($response->url, PHP_URL_PATH)));

		foreach($data->maps as $map){
			$maps[] = $map->id;

			$this->db->insert
				->into($this->options->tableMaps)
				->values([
					'map_id'         => $map->id,
					'continent_id'   => $continent,
					'floor_id'       => $floor,
					'region_id'      => $region,
					'default_floor'  => $map->default_floor,
					'map_rect'       => json_encode($map->map_rect),
					'continent_rect' => json_encode($map->continent_rect),
					'min_level'      => $map->min_level,
					'max_level'      => $map->max_level,
				])
				->query();

			$this->logger->info('added map #'.$map->id.', continent: '.$continent.', floor: '.$floor.', region: '.$region);
		}

		$this->db->insert
			->into($this->options->tableRegions)
			->values([
				'continent_id' => $continent,
				'floor_id'     => $floor,
				'region_id'    => $region,
				'label_coord'  => json_encode($data->label_coord),
				'maps'         => json_encode($maps),
				'name_en'      => $data->name,
			])
			->query();

		$this->logger->info('added region #'.$region.', continent: '.$continent);
	}

}
