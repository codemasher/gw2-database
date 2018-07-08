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
use chillerlan\HTTP\HTTPResponseInterface;

class CreateFloors extends UpdaterAbstract{

	/**
	 * @return void
	 */
	public function init():void{
		$this->logger->info(__METHOD__.': start');

		// get floor URLs
		foreach(self::CONTINENTS as $continent){
			/** @var HTTPResponseInterface $response */
			$response = $this->gw2->request('/continents/'.$continent);
			if($response->headers->statuscode === 200){
				$floordata = $response->json;
				if(is_array($floordata->floors)){
					foreach($floordata->floors as $floor){
						$this->urls[] = ['/continents/'.$continent.'/floors/'.$floor.'/regions'];
					}
				}
			}
		}

		$this->db->truncate->table($this->options->tableMapFloors)->query();

		$this->processURLs();
		$this->db->raw('OPTIMIZE TABLE '.$this->options->tableMapFloors);

		$this->logger->info(__METHOD__.': end');
	}

	/**
	 * @param \chillerlan\HTTP\HTTPResponseInterface $response
	 * @param array|null                             $params
	 *
	 * @return void
	 */
	protected function processResponse(HTTPResponseInterface $response, array $params = null):void{
		[$continent, $floor] = explode('/', str_replace(['/v2/continents/', 'floors/', '/regions'], '', parse_url($response->url, PHP_URL_PATH)));

		$this->db->insert
			->into($this->options->tableMapFloors)
			->values([
				'continent_id' => $continent,
				'floor_id'     => $floor,
				'regions'      => json_encode($response->json),
			])
			->query();

		$this->logger->info('updating continent #'.$continent.', floor '.$floor);
	}
}
