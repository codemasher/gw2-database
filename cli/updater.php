<?php
/**
 *
 * @filesource   updater.php
 * @created      17.02.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DBCLI;

use chillerlan\GW2DB\{GW2API, GW2DB};
use chillerlan\GW2DB\Updaters\Items\{Colors, Items, Recipes, Skins};
use chillerlan\GW2DB\Updaters\Maps\{CreateFloors, CreateRegions, UpdateRegions, UpdateMaps};
use chillerlan\HTTP\CurlClient;
use chillerlan\OAuth\Storage\MemoryStorage;

/** @var \chillerlan\Database\Database $db */
$db = null;

/** @var \chillerlan\Traits\DotEnv $env */
$env = null;

/** @var \Psr\Log\LoggerInterface $env */
$log = null;

/** @var \chillerlan\GW2DB\GW2DBOptions $options */
$options = null;

require_once __DIR__.'/common.php';

$gw2api = new GW2API(new CurlClient($options), new MemoryStorage, $options);
$gw2api->storeGW2Token($env->GW2_APIKEY);

$gw2db = new GW2DB($gw2api, $db, $log);

$gw2db->update([
#	Items::class,
#	Colors::class,
#	Recipes::class,
#	Skins::class,
#	CreateFloors::class,
#	CreateRegions::class,
#	UpdateRegions::class,
	UpdateMaps::class,
]);

