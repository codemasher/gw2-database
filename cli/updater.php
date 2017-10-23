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

/** @var \chillerlan\Database\Connection $db */
$db = null;

require_once __DIR__.'/common.php';

use chillerlan\GW2DB\Updaters\Items\{Colors, ItemTempUpdater, Recipes, Skins, UpdateItemDB};
use chillerlan\GW2DB\Updaters\Maps\{CreateFloors, CreateRegions, UpdateMaps, UpdateRegions};
use chillerlan\TinyCurl\{Request, RequestOptions};

$request = new Request(new RequestOptions([
	'ca_info' => __DIR__.'/../config/update-me-cacert.pem',
]));

foreach([
		ItemTempUpdater::class,
		UpdateItemDB::class,
		CreateFloors::class,
		CreateRegions::class,
		UpdateRegions::class,
		UpdateMaps::class,
		Recipes::class,
		Skins::class,
		Colors::class,
	] as $updater){
	/** @var \chillerlan\GW2DB\Updaters\UpdaterInterface $updater */
	$updater = new $updater($db, $request);
	$updater->init();
}
