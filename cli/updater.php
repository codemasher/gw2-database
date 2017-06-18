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

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/functions.php';

use chillerlan\Database\{DBOptions, Drivers\MySQLiDriver};
use chillerlan\GW2DB\Updaters\Items\{Colors, ItemTempUpdater, Recipes, Skins, UpdateItemDB};
use chillerlan\GW2DB\Updaters\Maps\{CreateFloors, CreateRegions, UpdateMaps, UpdateRegions};
use chillerlan\TinyCurl\{Request, RequestOptions};
use Dotenv\Dotenv;

if(!is_cli()){
	throw new \Exception('no way, buddy.');
}

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

(new Dotenv(__DIR__.'/../config', '.env'))->load();

$DBDriverInterface = new MySQLiDriver(new DBOptions([
	'host'     => getenv('DB_HOST'),
	'port'     => getenv('DB_PORT'),
	'database' => getenv('DB_DATABASE'),
	'username' => getenv('DB_USERNAME'),
	'password' => getenv('DB_PASSWORD'),
]));

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
	$updater = new $updater($DBDriverInterface, $request);
	$updater->init();
}
