<?php
/**
 * Class LatLngHelpers
 *
 * @filesource   LatLngHelpers.php
 * @package      GW2Treasures\GW2Tools\Maps
 * @created      10.04.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Helpers\Maps;

class LatLngHelpers{

	const API_TILES = 'https://tiles.guildwars2.com';
	const CONTINENT_TYRIA = 1;
	const CONTINENT_MISTS = 2;
	const MAX_ZOOM = [
		self::CONTINENT_TYRIA => 7,
		self::CONTINENT_MISTS => 6,
	];

	/**
	 * @param array $point
	 * @param int   $zoom
	 * @param int   $maxZoom
	 *
	 * @return array
	 */
	public function project(array $point, $zoom, $maxZoom){
		$div = 1 << ($maxZoom - $zoom);

		return [$point[0] / $div, $point[1] / $div];
	}

	/**
	 * @param array $view   a rectangle defining the northwest and southeast point of the desired view
	 *                      example: [[3840,14592],[5888,17152]] // Dry Top (continent_rect)
	 * @param int   $zoom
	 * @param int   $continentID  from API
	 * @param int   $floorID      from API
	 *
	 * @return array
	 * @throws \GW2Treasures\GW2Tools\Maps\MapsException
	 */
	public function getTiles(array $view, $zoom, $continentID, $floorID){
		$tiles = [];

		if(!array_key_exists($continentID, self::MAX_ZOOM)){
			throw new MapsException('continent does not exist');
		}

		$maxZoom = self::MAX_ZOOM[$continentID];

		if($zoom > $maxZoom){
			$zoom = $maxZoom;
		}

		$northWest = $this->project($view[0], $zoom, $maxZoom);
		$southEast = $this->project($view[1], $zoom, $maxZoom);

		$range = range(0, 1 << $zoom);

		foreach($range as $y){
			foreach($range as $x){
				if(
					   $x >= floor($northWest[0] / 256)
					&& $x  <  ceil($southEast[0] / 256)
					&& $y >= floor($northWest[1] / 256)
					&& $y  <  ceil($southEast[1] / 256)
				){
					$tiles[$y][$x] = self::API_TILES.'/'.$continentID.'/'.$floorID.'/'.$zoom.'/'.$x.'/'.$y.'.jpg';
				}
			}
		}

		return $tiles;
	}

	/**
	 * re-calculation for points in map_rect to continent_rect, e.g. to project Mumble Link data on a map
	 * note: don't look at it. really! it will melt your brain and make your eyes bleed!
	 *
	 * @link https://forum-en.guildwars2.com/forum/community/api/Event-Details-API-location-coordinates/2262702
	 *
	 * @param array $pointInMapRect
	 * @param array $mapRect        from API
	 * @param array $continentRect  from API
	 *
	 * @return array point in continent_rect
	 */
	public function projectPointMapToContinent(array $pointInMapRect, array $mapRect, array $continentRect){
		//
		$pointInContinentRectX = $continentRect[0][0] + ($continentRect[1][0] - $continentRect[0][0])
		                          * ($pointInMapRect[0] - $mapRect[0][0]) / ($mapRect[1][0] - $mapRect[0][0]);

		$pointInContinentRectY = $continentRect[0][1] + ($continentRect[1][1] - $continentRect[0][1])
		                          * (1 - ($pointInMapRect[1] - $mapRect[0][1]) / ($mapRect[1][1] - $mapRect[0][1]));

		return [(int)round($pointInContinentRectX), (int)round($pointInContinentRectY)];
	}

}
