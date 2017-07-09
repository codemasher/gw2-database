<?php
/**
 * Class UpdateRegions
 *
 * @filesource   UpdateRegions.php
 * @created      13.04.2016
 * @package      chillerlan\GW2DB\Updaters\Maps
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters\Maps;

use chillerlan\GW2DB\Updaters\{MultiRequestAbstract, UpdaterException};
use chillerlan\TinyCurl\{ResponseInterface, URL};

class UpdateRegions extends MultiRequestAbstract{

	public function init(){
		$this->starttime = microtime(true);
		$this->logToCLI(__METHOD__.': start');

		$regions = $this->db->select
			->cols(['continent_id', 'region_id', 'floor_id'])
			->from([getenv('TABLE_GW2_REGIONS')])
			->execute();

		if(!$regions || !$regions->length === 0){
			throw new UpdaterException('failed to fetch regions from db, please run CreateRegions before');
		}

		$urls = [];

		foreach($regions as $region){
			foreach(self::API_LANGUAGES as $lang){
				$urls[] = new URL(self::API_BASE.'/continents/'.$region->continent_id.'/floors/'.$region->floor_id.'/regions/'.$region->region_id, ['lang' => $lang]);
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

		list($continent, $floor, $region) = explode('/', str_replace(['/v2/continents/', 'floors/', '/regions'], '', parse_url($info->url, PHP_URL_PATH)));

		$this->db->update
			->table(getenv('TABLE_GW2_REGIONS'))
			->set([
				'name_'.$lang  => $response->json->name,
			])
			->where('continent_id', $continent)
			->where('region_id', $region)
			->where('floor_id', $floor)
			->execute();

		$this->logToCLI('updated region #'.$region.' ('.$lang.'), continent: '.$continent.', floor: '.$floor);

		return true;
	}
}
