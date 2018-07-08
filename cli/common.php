<?php
/**
 * @filesource   common.php
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DBCLI;

use chillerlan\Database\{Database, Drivers\MySQLiDrv};
use chillerlan\GW2DB\GW2DBOptions;
use chillerlan\Logger\Log;
use chillerlan\Logger\Output\ConsoleLog;
use chillerlan\SimpleCache\{Cache, Drivers\MemoryCacheDriver};
use chillerlan\Traits\DotEnv;
use Psr\Log\LogLevel;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/functions.php';

if(!is_cli()){
	throw new \Exception('no way, buddy.');
}

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

$env = (new DotEnv(__DIR__.'/../config', '.env'))->load();

$options = new GW2DBOptions([
	'driver'      => MySQLiDrv::class,
	'host'        => $env->DB_HOST,
	'port'        => $env->DB_PORT,
	'database'    => $env->DB_DATABASE,
	'username'    => $env->DB_USERNAME,
	'password'    => $env->DB_PASSWORD,
	'ca_info'     => __DIR__.'/../config/cacert.pem',
	'userAgent'   => 'chillerlanPhpOAuth/2.0.1 +https://github.com/codemasher/gw2-database',
	'minLogLevel' => LogLevel::INFO,
]);

$log = (new Log)->addInstance(new ConsoleLog($options), 'console');
$db  = new Database($options, new Cache(new MemoryCacheDriver), $log);
