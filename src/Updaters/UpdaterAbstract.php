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

use chillerlan\Database\Drivers\DBDriverInterface;
use chillerlan\TinyCurl\MultiRequest;
use chillerlan\TinyCurl\MultiRequestOptions;
use chillerlan\TinyCurl\RequestTrait;

abstract class UpdaterAbstract implements UpdaterInterface{
	use RequestTrait;

	/**
	 * @var \chillerlan\Database\Drivers\DBDriverInterface
	 */
	protected $DBDriverInterface;

	/**
	 * @var float
	 */
	protected $starttime;

	/**
	 * @var array
	 */
	protected $urls = [];

	/**
	 * CA Root Certificates for use with CURL/SSL
	 *
	 * @var string
	 * @link https://curl.haxx.se/ca/cacert.pem
	 */
	protected $cacert;

	/**
	 * UpdaterAbstract constructor.
	 *
	 *
	 * @param \chillerlan\Database\Drivers\DBDriverInterface $DBDriverInterface
	 *
	 * @param string                                         $cacert
	 *
	 * @throws \chillerlan\Database\DBException
	 */
	public function __construct(DBDriverInterface $DBDriverInterface, $cacert){
		$this->DBDriverInterface = $DBDriverInterface;
		$this->DBDriverInterface->connect();
		$this->cacert = $cacert;
		$this->setRequestCA($this->cacert);
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
		$response = $this->fetch(self::API_BASE.'/'.$endpoint);
		$this->logToCLI(__METHOD__.': response');

		if($response->info->http_code !== 200){
			throw new UpdaterException('failed to get /v2/'.$endpoint);
		}

		$this->DBDriverInterface->multi_callback(
			'INSERT IGNORE INTO '.$table.' (`id`) VALUES (?)',
			$response->json,
			function ($item){
				return [$item];
			}
		);

		$this->logToCLI(__METHOD__.': end');
	}

	/**
	 * @param array $urls
	 *
	 * @return \chillerlan\TinyCurl\MultiRequestOptions
	 * @throws \chillerlan\TinyCurl\RequestException
	 */
	protected function fetchMulti(array $urls){
		$this->logToCLI('multirequest: start');
		$options = new MultiRequestOptions;
		$options->ca_info     = $this->cacert;
		$options->window_size = self::CONCURRENT;

		$multiRequest = new MultiRequest($options);
		// solving the hen-egg problem, feed the hen with the egg!
		$multiRequest->setHandler($this);

		$multiRequest->fetch($urls);
		$this->logToCLI('multirequest: end');
	}

	/**
	 * discard the response when it's impossible to determine the language
	 *
	 * @param $lang
	 *
	 * @return bool
	 */
	protected function checkResponseLanguage($lang){

		if(!in_array($lang, self::API_LANGUAGES)){
			$this->logToCLI('invalid language, URL discarded.');
			return false;
		}

		return true;
	}

}
