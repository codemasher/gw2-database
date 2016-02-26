<?php
/**
 *
 * @filesource   UpdaterBase.php
 * @created      22.02.2016
 * @package      chillerlan\GW2DB\Updaters
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters;

use chillerlan\Database\DBOptions;
use chillerlan\Database\Traits\DatabaseTrait;
use chillerlan\GW2DB\Database\Drivers\GW2MySQLiDriver;
use chillerlan\TinyCurl\Traits\RequestTrait;
use Dotenv\Dotenv;

/**
 * Class UpdaterBase
 */
class UpdaterBase{
	use DatabaseTrait, RequestTrait;

	const CONCURRENT    = 7;
	const CHUNK_SIZE    = 100;
	const CONFIGDIR     = __DIR__.'/../../config';
	const STORAGEDIR    = __DIR__.'/../../storage';
	const CACERT        = __DIR__.'/../../config/update-me-cacert.pem';
	const API_LANGUAGES = ['de', 'en', 'es', 'fr', 'zh'];
	const API_BASE      = 'https://api.guildwars2.com/v2/';

	/**
	 * @var \chillerlan\GW2DB\Database\Drivers\GW2MySQLiDriver
	 */
	protected $GW2MySQLiDriver;

	/**
	 * @var float
	 */
	protected $starttime;

	/**
	 * UpdaterBase constructor.
	 */
	public function __construct(){
		(new Dotenv(self::CONFIGDIR))->load();

		$dbOptions = new DBOptions([
			'host'     => getenv('DB_MYSQLI_HOST'),
			'port'     => getenv('DB_MYSQLI_PORT'),
			'database' => getenv('DB_MYSQLI_DATABASE'),
			'username' => getenv('DB_MYSQLI_USERNAME'),
			'password' => getenv('DB_MYSQLI_PASSWORD'),
		]);

		$this->GW2MySQLiDriver = $this->dbconnect(GW2MySQLiDriver::class, $dbOptions);
	}

	/**
	 * Write some info to the CLI
	 *
	 * @param $str
	 */
	protected function logToCLI($str){
		echo '['.date('c', time()).']'.sprintf('[%10ss] ', sprintf('%01.4f', microtime(true) - $this->starttime)).$str.PHP_EOL;
	}

	/**
	 * @param string $endpoint
	 * @param string $table
	 *
	 * @throws \chillerlan\Database\DBException
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 */
	protected function refreshIDs($endpoint, $table){
		$this->starttime = microtime(true);
		$this->logToCLI(__METHOD__.': start ('.$endpoint.', '.$table.')');
		$response = $this->fetch(self::API_BASE.$endpoint);
		$this->logToCLI(__METHOD__.': response');

		if($response->info->http_code !== 200){
			throw new UpdaterException('failed to get /v2/'.$endpoint);
		}

		/**
		 * @param int $item
		 *
		 * @return array
		 */
		$callback = function($item){
			return [
				$item,
			];
		};

		$this->GW2MySQLiDriver->multi_callback('INSERT IGNORE INTO '.$table.' (`id`) VALUES (?)', $response->json, $callback);
		$this->logToCLI(__METHOD__.': end');
	}
	
}
