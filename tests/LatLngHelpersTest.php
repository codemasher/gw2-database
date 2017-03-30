<?php
/**
 * Class LatLngHelpersTest
 *
 * @filesource   LatLngHelpersTest.php
 * @created      10.04.2016
 * @package      chillerlan\GW2DB\Helpers\Tests\Maps
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DBTests;

use chillerlan\GW2DB\Helpers\Maps\LatLngHelpers;
use PHPUnit\Framework\TestCase;

class LatLngHelpersTest extends TestCase{

	/**
	 * @var \chillerlan\GW2DB\Helpers\Maps\LatLngHelpers
	 */
	private $LatLngHelpers;

	protected function setUp(){
		$this->LatLngHelpers = new LatLngHelpers;
	}

	public function tileTestDataProvider(){
		return [
			[
				[[3840,14592], [5888,17152]],
				5,
				LatLngHelpers::CONTINENT_TYRIA,
				1,
				[
					14 => [
						3 => 'https://tiles.guildwars2.com/1/1/5/3/14.jpg',
						4 => 'https://tiles.guildwars2.com/1/1/5/4/14.jpg',
						5 => 'https://tiles.guildwars2.com/1/1/5/5/14.jpg'
					],
					15 => [
						3 => 'https://tiles.guildwars2.com/1/1/5/3/15.jpg',
						4 => 'https://tiles.guildwars2.com/1/1/5/4/15.jpg',
						5 => 'https://tiles.guildwars2.com/1/1/5/5/15.jpg'
					],
					16 => [
						3 => 'https://tiles.guildwars2.com/1/1/5/3/16.jpg',
						4 => 'https://tiles.guildwars2.com/1/1/5/4/16.jpg',
						5 => 'https://tiles.guildwars2.com/1/1/5/5/16.jpg'
					]
				]
			],
			[
				[[10400, 14300],[10800, 14850]],
				7, // max zoom adjust coverage
				LatLngHelpers::CONTINENT_MISTS,
				3,
				[
					55 => [
						40 => 'https://tiles.guildwars2.com/2/3/6/40/55.jpg',
						41 => 'https://tiles.guildwars2.com/2/3/6/41/55.jpg',
						42 => 'https://tiles.guildwars2.com/2/3/6/42/55.jpg'
					],
					56 => [
						40 => 'https://tiles.guildwars2.com/2/3/6/40/56.jpg',
						41 => 'https://tiles.guildwars2.com/2/3/6/41/56.jpg',
						42 => 'https://tiles.guildwars2.com/2/3/6/42/56.jpg'
					],
					57 => [
						40 => 'https://tiles.guildwars2.com/2/3/6/40/57.jpg',
						41 => 'https://tiles.guildwars2.com/2/3/6/41/57.jpg',
						42 => 'https://tiles.guildwars2.com/2/3/6/42/57.jpg'
					],
					58 => [
						40 => 'https://tiles.guildwars2.com/2/3/6/40/58.jpg',
						41 => 'https://tiles.guildwars2.com/2/3/6/41/58.jpg',
						42 => 'https://tiles.guildwars2.com/2/3/6/42/58.jpg'
					]
				]
			],
		];
	}

	/**
	 * @dataProvider tileTestDataProvider
	 */
	public function testGetTiles($view, $zoom, $continent, $floor, $expected){
		$this->assertEquals($expected, $this->LatLngHelpers->getTiles($view, $zoom, $continent, $floor));
	}

	/**
	 * @expectedException \chillerlan\GW2DB\Helpers\Maps\MapsException
	 * @expectedExceptionMessage continent does not exist
	 */
	public function testGetTilesInvalidContinentException(){
		$this->LatLngHelpers->getTiles([], 1, 0, 42);
	}

	public function testProjectPointMapToContinent(){
		// Queensdale:
		// https://api.guildwars2.com/v2/maps/15
		// https://api.guildwars2.com/v1/event_details.json?event_id=D77349CE-E422-469E-905B-827AA138D88F
		$coords = $this->LatLngHelpers->projectPointMapToContinent(
			[-28975, 27321.2],
			[[-43008, -27648], [43008, 30720]],
			[[9856, 11648], [13440, 14080]]
		);

		$this->assertEquals([10441, 11790], $coords);
	}

}
