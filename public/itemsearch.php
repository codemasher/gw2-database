<?php
/**
 * @filesource   itemsearch.php
 * @created      17.07.2015
 * @package      chillerlan\GW2DB
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

require_once __DIR__.'/../vendor/autoload.php' ;

use chillerlan\Database\{Connection, Options, Drivers\Native\MySQLiDriver, Query\Dialects\MySQLQueryBuilder};
use chillerlan\SimpleCache\{Cache, Drivers\MemoryCacheDriver};
use chillerlan\Traits\DotEnv;

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

$env = (new DotEnv(__DIR__.'/../config', '.env'))->load();

$cache = new Cache(new MemoryCacheDriver); // dummy, use redis or memcached instead

$db = new Connection(new Options([
	'driver'       => MySQLiDriver::class,
	'querybuilder' => MySQLQueryBuilder::class,
	'host'     => $env->get('DB_HOST'),
	'port'     => $env->get('DB_PORT'),
	'database' => $env->get('DB_DATABASE'),
	'username' => $env->get('DB_USERNAME'),
	'password' => $env->get('DB_PASSWORD'),
]), $cache);


$itemSearch = new \chillerlan\GW2DB\ItemSearch($db);

$response = [];

switch(true){
	case isset($_POST['load']) && $_POST['load'] === 'form':
		$response = $itemSearch->loadForm();
		break;
	case isset($_POST['search']) && !empty($_POST['search']):
		$response = $itemSearch->search($_POST['search']);
		break;
	case isset($_POST['details']) && !empty($_POST['details']):
		$response = $itemSearch->showDetails($_POST['details']);
		break;
	case isset($_POST['chatlinks']) && !empty($_POST['chatlinks']):
		$response = $itemSearch->chatlinkSearch($_POST['chatlinks']);
		break;
	// anything else is invalid
	default:
		$response['error'] = 'invalid request';
}

header('Content-type: application/json;charset=utf-8;');

echo json_encode($response);

exit;
