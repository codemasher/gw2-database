<?php
/**
 * Class GW2DBOptions
 *
 * @filesource   GW2DBOptions.php
 * @created      06.01.2018
 * @package      chillerlan\GW2DB
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB;

use chillerlan\Database\DatabaseOptionsTrait;
use chillerlan\HTTP\HTTPOptionsTrait;
use chillerlan\Logger\LogOptionsTrait;
use chillerlan\Traits\ContainerAbstract;

/**
 * @property bool   $truncateTemp
 * @property string $tableDiff
 * @property string $tableItems
 * @property string $tableItemsTemp
 * @property string $tableAttributeCombo
 * @property string $tableAttributes
 * @property string $tableRecipes
 * @property string $tableSkins
 * @property string $tableColors
 * @property string $tableMapFloors
 * @property string $tableRegions
 * @property string $tableMaps
 */
class GW2DBOptions extends ContainerAbstract{
	use HTTPOptionsTrait, DatabaseOptionsTrait, LogOptionsTrait;

	protected $truncateTemp = false;

	protected $tableDiff           = 'gw2_diff';
	protected $tableItems          = 'items_gw2treasures';
	protected $tableItemsTemp      = 'gw2_items_temp';
	protected $tableAttributeCombo = 'gw2_attribute_combinations';
	protected $tableAttributes     = 'gw2_attributes';
	protected $tableRecipes        = 'gw2_recipes';
	protected $tableSkins          = 'gw2_skins';
	protected $tableColors         = 'gw2_colors';
	protected $tableMapFloors      = 'gw2_map_floors';
	protected $tableRegions        = 'gw2_regions';
	protected $tableMaps           = 'gw2_maps';

}
