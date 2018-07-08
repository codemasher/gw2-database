<?php
/**
 * Interface UpdaterInterface
 *
 * @filesource   UpdaterInterface.php
 * @created      22.02.2016
 * @package      chillerlan\GW2DB\Updaters
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters;

/**
 * @method setLogger(\Psr\Log\LoggerInterface $logger)
 */
interface UpdaterInterface{

	const API_BASE      = 'https://api.guildwars2.com/v2';
	const API_LANGUAGES = ['de', 'en', 'es', 'fr', 'zh'];

	const CONCURRENT  = 50;
	const CHUNK_SIZE  = 200;
	const SLEEP_TIMER = 60 / 300;

	const CONTINENTS = [1, 2];

	const DB_CHUNK_SIZE = 500;
	const MAX_RETRIES = 3;

	public function init():void;

}
