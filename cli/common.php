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
use chillerlan\SimpleCache\{Cache, Drivers\MemoryCacheDriver};
use chillerlan\Traits\DotEnv;

if(!is_cli()){
	throw new \Exception('no way, buddy.');
}

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

$env = (new DotEnv(__DIR__.'/../config', '.env'))->load();

$db = new Connection(new Options([
	'driver'       => PDOMySQLDriver::class,
	'querybuilder' => MySQLQueryBuilder::class,
	'host'     => $env->get('DB_HOST'),
	'port'     => $env->get('DB_PORT'),
	'database' => $env->get('DB_DATABASE'),
	'username' => $env->get('DB_USERNAME'),
	'password' => $env->get('DB_PASSWORD'),
]), new Cache(new MemoryCacheDriver));

