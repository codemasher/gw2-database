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

use chillerlan\Database\Connection;
use chillerlan\TinyCurl\{Request, URL};

abstract class UpdaterAbstract implements UpdaterInterface{

	/**
	 * @var \chillerlan\Database\Connection
	 */
	protected $db;

	/**
	 * @var \chillerlan\TinyCurl\Request
	 */
	protected $request;

	/**
	 * @var float
	 */
	protected $starttime;

	/**
	 * UpdaterAbstract constructor.
	 *
	 * @link https://curl.haxx.se/ca/cacert.pem
	 *
	 * @param \chillerlan\Database\Connection $DBDriverInterface
	 * @param \chillerlan\TinyCurl\Request                   $request
	 */
	public function __construct(Connection $DBDriverInterface, Request $request){
		$this->db = $DBDriverInterface;
		$this->db->connect();

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

		$this->db->multiCallback(
			'INSERT IGNORE INTO `'.$table.'` (`id`) VALUES (?)',
			$response->json,
			function($id){
				return [$id];
			}
		);

		$this->logToCLI(__METHOD__.': end');
	}


}
