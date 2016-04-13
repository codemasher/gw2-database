<?php
/**
 *
 * @filesource   gw2api.php
 * @created      17.02.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DBCLI;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/functions.php';

use chillerlan\Database\DBOptions;
use chillerlan\Database\Drivers\MySQLi\MySQLiDriver;
use chillerlan\GW2DB\Updaters\Items\UpdateItemDB;
use chillerlan\GW2DB\Updaters\Items\ItemTempUpdater;
use chillerlan\GW2DB\Updaters\Maps\CreateFloors;
use chillerlan\GW2DB\Updaters\Maps\CreateRegions;
use chillerlan\GW2DB\Updaters\Maps\UpdateMaps;
use chillerlan\GW2DB\Updaters\Maps\UpdateRegions;
use Dotenv\Dotenv;

if(!is_cli()){
	throw new \Exception('no way, buddy.');
}

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

(new Dotenv(__DIR__.'/../config', '.env'))->load();

$updaters = [
	ItemTempUpdater::class,
	UpdateItemDB::class,
	CreateFloors::class,
	CreateRegions::class,
	UpdateRegions::class,
	UpdateMaps::class,
];

$dbOptions = new DBOptions([
	'host'     => getenv('DB_HOST'),
	'port'     => getenv('DB_PORT'),
	'database' => getenv('DB_DATABASE'),
	'username' => getenv('DB_USERNAME'),
	'password' => getenv('DB_PASSWORD'),
]);

$DBDriverInterface = new MySQLiDriver($dbOptions);

foreach($updaters as $updater){
	/** @var \chillerlan\GW2DB\Updaters\UpdaterInterface $updater */
	$updater = new $updater($DBDriverInterface, __DIR__.'/../config/update-me-cacert.pem');
	$updater->init();
}
