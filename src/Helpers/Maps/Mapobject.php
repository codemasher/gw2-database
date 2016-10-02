<?php
/**
 *
 * @filesource   Mapobject.php
 * @created      12.04.2016
 * @package      GW2Treasures\GW2Tools\Maps
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Helpers\Maps;

/**
 * Class Mapobject
 */
class Mapobject{

	/**
	 * @var \GW2Treasures\GW2Tools\Maps\MapdataInterface
	 */
	protected $mapdataInterface;

	/**
	 * @var \GW2Treasures\GW2Tools\Maps\MapOptions
	 */
	protected $mapOptions;

	/**
	 * @var array[\stdClass]
	 */
	protected $map;

	/**
	 * Mapobject constructor.
	 *
	 * @param \GW2Treasures\GW2Tools\Maps\MapdataInterface $mapdataInterface
	 * @param \GW2Treasures\GW2Tools\Maps\MapOptions       $mapOptions
	 */
	public function __construct(MapdataInterface $mapdataInterface, MapOptions $mapOptions){
		$this->mapdataInterface = $mapdataInterface;
		$this->mapOptions = $mapOptions;
	}


	public function setMap($id){
		$this->map = $this->mapdataInterface->getMapData($id);
	}

}
