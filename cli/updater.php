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

require_once '../vendor/autoload.php';
require_once 'functions.php';

use chillerlan\GW2DB\Updaters\Items\ItemRefresh;
use chillerlan\GW2DB\Updaters\Items\ItemUpdater;

if(!is_cli()){
	throw new \Exception('no way, buddy.');
}

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

$updaters = [
	ItemRefresh::class,
#	ItemUpdater::class,
];


foreach($updaters as $u){
	/** @var \chillerlan\GW2DB\Updaters\UpdaterInterface $updater */
	$updater = new $u;
	$updater->init();
}

