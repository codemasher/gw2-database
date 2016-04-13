<?php
/**
 * Interface UpdaterInterface
 *
 * @filesource   UpdaterInterface.php
 * @created      22.02.2016
 * @package      chillerlan\GW2DB\Updaters
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters;

interface UpdaterInterface{

	const API_BASE      = 'https://api.guildwars2.com/v2';
	const API_LANGUAGES = ['de', 'en', 'es', 'fr', 'zh'];

	const CONCURRENT    = 7;
	const CHUNK_SIZE    = 50;

	const CONTINENTS = [1, 2];

	const DIFF_TABLE = 'gw2_diff';

	const ITEM_TABLE           = 'items_gw2treasures';
	const ITEM_TEMP_TABLE      = 'gw2_items_temp';
	const ITEM_ATTRIBUTE_COMBO = 'gw2_attribute_combinations';
	const ITEM_ATTRIBUTES      = 'gw2_attributes';

	const MAPS_FLOOR_TABLE  = 'gw2_map_floors';
	const MAPS_REGION_TABLE = 'gw2_regions';
	const MAPS_TABLE        = 'gw2_maps';

	public function init();

}
