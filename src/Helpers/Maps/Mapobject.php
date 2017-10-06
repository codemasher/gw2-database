<?php
/**
 * Class Mapobject
 *
 * @filesource   Mapobject.php
 * @created      12.04.2016
 * @package      chillerlan\GW2DB\Helpers\Maps
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Helpers\Maps;

use chillerlan\Database\Connection;
use chillerlan\GW2DB\Helpers\Chatlinks\Chatlink;
use chillerlan\GW2DB\Updaters\UpdaterInterface;

/**
 *
 */
class Mapobject{

	const API_TILES = 'https://tiles.guildwars2.com';
	const TILE_SIZE = 256;
	const CONTINENT_TYRIA = 1;
	const CONTINENT_MISTS = 2;
	const MAX_ZOOM = [
		self::CONTINENT_TYRIA => 7,
		self::CONTINENT_MISTS => 6, // @todo https://github.com/arenanet/api-cdi/issues/308
	];

	/**
	 * @var \chillerlan\Database\Result|array
	 */
	protected $map;

	/**
	 * @var int
	 */
	protected $map_id;

	/**
	 * @var int
	 */
	protected $floor_id;

	/**
	 * @var int
	 */
	protected $default_floor;

	/**
	 * @var array
	 */
	protected $floors = [];

	/**
	 * @var int
	 */
	protected $continent_id;

	/**
	 * @param array $view   a rectangle defining the northwest and southeast point of the desired view
	 *                      example: [[3840,14592],[5888,17152]] // Dry Top (continent_rect)
	 */
	protected $view;

	/**
	 * @var int
	 */
	protected $maxZoom;

	/**
	 * @var int
	 */
	protected $zoom;

	/**
	 * @var string
	 */
	protected $cachedir;

	/**
	 * Mapobject constructor.
	 *
	 * @param \chillerlan\Database\Connection $db
	 * @param string                          $cachedir
	 */
	public function __construct(Connection $db, string $cachedir){
		$this->db = $db;
		$this->db->connect();

		$this->cachedir = $cachedir;

		$this->Chatlink = new Chatlink;
	}

	/**
	 * @param int         $id
	 * @param string|null $lang
	 *
	 * @return \chillerlan\GW2DB\Helpers\Maps\Mapobject
	 */
	public function load(int $id, string $lang = null):Mapobject {
		$this->floors = [];

		$lang = in_array($lang, UpdaterInterface::API_LANGUAGES, true) ? $lang : 'en' ;

		$this->map = $this->db->select
			->cols([
				'continent_id', 'floor_id', 'region_id', 'map_id', 'default_floor',
				'map_rect', 'continent_rect', 'min_level', 'min_level',
				'name' => 'name_'.$lang, 'data' => 'data_'.$lang
			])
			->from(['gw2_maps'])
			->where('map_id', $id)
			->orderBy(['floor_id' => 'asc'])
			->cached()
			->execute('floor_id')
			->__map(function($floor, $floor_id){
				$this->continent_id  = $floor->continent_id;
				$this->map_id        = $floor->map_id;
				$this->default_floor = $floor->default_floor;
				$this->floors[]      = $floor_id;

				foreach(['data', 'continent_rect', 'map_rect'] as $field){
					$floor[$field] = json_decode($floor[$field]);
				}

				return $floor;
			});

		$this->maxZoom  = self::MAX_ZOOM[$this->continent_id];
		$this->setFloor($this->default_floor);

		return $this;
	}

	/**
	 * @param int $floor_id
	 *
	 * @return \chillerlan\GW2DB\Helpers\Maps\Mapobject
	 */
	public function setFloor(int $floor_id):Mapobject{
		$this->floor_id = $this->default_floor;
		$this->view     = $this->map[$this->default_floor]->continent_rect;
		$this->zoom     = $this->maxZoom;

		if(in_array($floor_id, $this->floors, true)){
			$this->floor_id = $floor_id;
			$this->view     = $this->map[$floor_id]->continent_rect;
		}

		return $this;
	}

	/**
	 * @param int   $zoom
	 * @param array $view   a rectangle defining the northwest and southeast point of the desired view
	 *                      example: [[3840,14592],[5888,17152]] // Dry Top (continent_rect)
	 *
	 * @return array
	 */
	public function getTiles(int $zoom = null){ // $size = 256, 512,... $center = [x, y]
		$this->zoom = $zoom ?? $this->maxZoom;

		if($this->zoom > $this->maxZoom){
			$this->zoom = $this->maxZoom;
		}

		$tiles = [];

		$clamp = array_map(function($point){
			return array_map(function($coord){
				return floor(($coord / (1 << ($this->maxZoom - $this->zoom))) / self::TILE_SIZE);
			}, $point);
		}, $this->view);

		$range = range(0, 1 << $this->zoom);

		foreach($range as $y){
			foreach($range as $x){

				if($x < $clamp[0][0] || $x > $clamp[1][0] || $y < $clamp[0][1] || $y > $clamp[1][1]){
					continue;
				}

				$dir = $this->continent_id.'/'.$this->floor_id.'/'.$this->zoom.'/'.$x;

				$cache = $this->cachedir.'/tiles/';

				$this->downloadTile($cache, $dir, $y);

				$tiles[$y][$x] = $cache.$dir.'/'.$y.'.jpg';
			}
		}

		return $tiles;
	}

	/**
	 * @todo
	 *
	 * @param $cache
	 * @param $dir
	 * @param $y
	 */
	public function downloadTile($cache, $dir, $y){

		if(!is_file($cache.$dir.'/'.$y.'.jpg')){

			if(!is_dir($cache.$dir)){
				mkdir($cache.$dir, 0777 , true);
			}

			file_put_contents($cache.$dir.'/'.$y.'.jpg', file_get_contents(self::API_TILES.'/'.$dir.'/'.$y.'.jpg'));
		}

	}

	/**
	 * @todo
	 *
	 * @param int|null $zoom
	 *
	 * @return string
	 */
	public function staticMap(int $zoom = null){
		$this->zoom = $zoom ?? $this->maxZoom;

		// draw the base map
		$tiles = $this->getTiles($this->zoom);
		$image = imagecreatetruecolor(count(array_values($tiles)[0]) * self::TILE_SIZE, count($tiles) * self::TILE_SIZE);

		$delta = [0, 0]; // world map 0 -> image 0, [x, y] of first map tile
		$r = 0;

		foreach($tiles as $y => $row){
			$t = 0;

			if($r === 0){
				$delta[1] = $y;
			}

			foreach($row as $x => $tile){

				if($t === 0){
					$delta[0] = $x;
				}

				$newtile = imagecreatefromjpeg($tile);
				imagecopy($image, $newtile, $t, $r,0,0,self::TILE_SIZE,self::TILE_SIZE);
				imagedestroy($newtile);

				$t += self::TILE_SIZE;
			}

			$r += self::TILE_SIZE;
		}

		/**
		 * @param $coords
		 * @param $iconsize
		 *
		 * @return mixed
		 */
		$fooCoords = function($coords, $iconsize) use ($delta){

			foreach($coords as $k => $c){
				$coords[$k] = round($c - $delta[$k] * self::TILE_SIZE - $iconsize / 2);
			}

			return $coords;
		};


		// heropoints
		$heropoint = imagecreatefrompng($this->cachedir.'/icons/heropoint.png');

		foreach($this->map[$this->floor_id]->data->skill_challenges as $sc){
			$coords = $fooCoords($sc->coord, 32);

			imagecopy($image, $heropoint, $coords[0], $coords[1],0,0,32,32);
		}

		// poi
		$unlock = imagecreatefrompng($this->cachedir.'/icons/unlock.png');
		$landmark = imagecreatefrompng($this->cachedir.'/icons/landmark.png');
		$vista = imagecreatefrompng($this->cachedir.'/icons/vista.png');
		$waypoint = imagecreatefrompng($this->cachedir.'/icons/waypoint.png');

		foreach($this->map[$this->floor_id]->data->points_of_interest as $poi){
			$coords = $fooCoords($poi->coord, 32);

			switch($poi->type){
				case 'unlock':
					$im = $unlock;
					break;
				case 'landmark':
					$im = $landmark;
					break;
				case 'vista':
					$im = $vista;
					break;
				case 'waypoint':
					imagecopyresized ($image, $waypoint, $coords[0], $coords[1], 0,0, 32,32,64,64);
					$im = null;
					break;
				default:
					$im = null;
			}

			if($im){
				imagecopy($image, $im, $coords[0], $coords[1],0,0,32,32);
			}

		}

		// tasks
		$task = imagecreatefrompng($this->cachedir.'/icons/task_hover.png');

		foreach($this->map[$this->floor_id]->data->tasks as $t){
			$coords = $fooCoords($t->coord, 32);
			imagecopy($image, $task, $coords[0], $coords[1],0,0,32,32);
		}

		ob_start();
		imagepng($image, $this->cachedir.'/maps/'.md5($this->continent_id.$this->floor_id.$this->map_id.$this->zoom).'.png', 9);

		$imageData = ob_get_contents();

		imagedestroy($image);
		imagedestroy($heropoint);
		imagedestroy($unlock);
		imagedestroy($landmark);
		imagedestroy($vista);
		imagedestroy($waypoint);
		imagedestroy($task);
		ob_end_clean();

		return $imageData;
	}

}
