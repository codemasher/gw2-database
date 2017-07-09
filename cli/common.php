<?php
/**
 * @filesource   common.php
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DBCLI;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/functions.php';

use chillerlan\Database\{Connection, Options, Drivers\PDO\PDOMySQLDriver, Query\Dialects\MySQLQueryBuilder};
use Dotenv\Dotenv;
use chillerlan\SimpleCache\{Cache, Drivers\MemoryCacheDriver};

if(!is_cli()){
	throw new \Exception('no way, buddy.');
}

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

(new Dotenv(__DIR__.'/../config', '.env'))->load();

$db = new Connection(new Options([
	'driver'       => PDOMySQLDriver::class,
	'querybuilder' => MySQLQueryBuilder::class,
	'host'     => getenv('DB_HOST'),
	'port'     => getenv('DB_PORT'),
	'database' => getenv('DB_DATABASE'),
	'username' => getenv('DB_USERNAME'),
	'password' => getenv('DB_PASSWORD'),
]), new Cache(new MemoryCacheDriver));

