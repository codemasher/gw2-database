<?php
/**
 * Class UpdaterAbstract
 *
 * @filesource   UpdaterAbstract.php
 * @created      22.02.2016
 * @package      chillerlan\GW2DB\Updaters
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters;

use chillerlan\Database\DBQuery;
use chillerlan\Database\Drivers\DBDriverInterface;
use chillerlan\TinyCurl\{Request, URL};

abstract class UpdaterAbstract implements UpdaterInterface{

	/**
	 * @var \chillerlan\Database\Drivers\DBDriverInterface
	 */
	protected $DBDriverInterface;

	/**
	 * @var \chillerlan\TinyCurl\Request
	 */
	protected $request;

	/**
	 * @var \chillerlan\Database\DBQuery
	 */
	protected $query;

	/**
	 * @var float
	 */
	protected $starttime;

	/**
	 * UpdaterAbstract constructor.
	 *
	 * @link https://curl.haxx.se/ca/cacert.pem
	 *
	 * @param \chillerlan\Database\Drivers\DBDriverInterface $DBDriverInterface
	 * @param \chillerlan\TinyCurl\Request                   $request
	 */
	public function __construct(DBDriverInterface $DBDriverInterface, Request $request){
		$this->DBDriverInterface = $DBDriverInterface;
		$this->DBDriverInterface->connect();
		$this->query = new DBQuery($this->DBDriverInterface);

		$this->request = $request;
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

		$response = $this->request->fetch(new URL(self::API_BASE.'/'.$endpoint));
		$this->logToCLI(__METHOD__.': response');

		if($response->info->http_code !== 200){
			throw new UpdaterException('failed to get /v2/'.$endpoint);
		}

		$this->DBDriverInterface->multi_callback(
			'INSERT IGNORE INTO `'.$table.'` (`id`) VALUES (?)',
			$response->json,
			function($id){
				return [$id];
			}
		);

		$this->logToCLI(__METHOD__.': end');
	}


}
