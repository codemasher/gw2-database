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

use chillerlan\Database\Database;
use chillerlan\GW2DB\{GW2API, GW2DBOptions};
use chillerlan\HTTP\HTTPResponseInterface;
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait, LoggerInterface, NullLogger};

abstract class UpdaterAbstract implements UpdaterInterface, LoggerAwareInterface{
	use LoggerAwareTrait;

	/**
	 * @var \chillerlan\GW2DB\GW2API
	 */
	protected $gw2;

	/**
	 * @var \chillerlan\Database\Database
	 */
	protected $db;

	/**
	 * @var \chillerlan\GW2DB\GW2DBOptions
	 */
	protected $options;

	/**
	 * @var float
	 */
	protected $starttime;

	/**
	 * @var array
	 */
	protected $urls = [];

	/**
	 * @var [][]
	 */
	protected $retry = [];

	/**
	 * @var int[]
	 */
	protected $retryCount = [];

	protected $lang;
	protected $diff;

	/**
	 * UpdaterAbstract constructor.
	 *
	 * @param \chillerlan\GW2DB\GW2API               $gw2
	 * @param \chillerlan\Database\Database          $db
	 * @param \Psr\Log\LoggerInterface               $log
	 * @param \chillerlan\GW2DB\GW2DBOptions|null    $options
	 */
	public function __construct(GW2API $gw2, Database $db, LoggerInterface $log, GW2DBOptions $options = null){
		$this->starttime = microtime(true);
		$this->gw2       = $gw2;
		$this->db        = $db;
		$this->logger    = $log ?? new NullLogger;
		$this->options   = $options ?? new GW2DBOptions;
	}

	/**
	 * @param \chillerlan\HTTP\HTTPResponseInterface $response
	 * @param array|null                             $params
	 *
	 * @return void
	 */
	abstract protected function processResponse(HTTPResponseInterface $response, array $params = null):void;

	/**
	 * @param $endpoint
	 * @param $table
	 *
	 * @return void
	 * @throws \chillerlan\GW2DB\Updaters\UpdaterException
	 */
	public function refreshIDs(string $endpoint, string $table):void{
		$this->logger->info($this->processTimer().__METHOD__.': '.$endpoint.' -> '.$table.' start');

		$response = $this->gw2->request($endpoint);

		if($response->headers->statuscode !== 200){
			$msg = $this->processTimer().__METHOD__.': failed to get '.$endpoint.' [HTTP/'.$response->headers->statuscode.' '.$response->headers->statustext.']';
			$this->logger->error($msg);

			throw new UpdaterException($msg);
		}

		/** @var array $r */
		$r = $response->json;

		$chunks = array_map(function($chunk){
			return array_map(function($id){
				return ['id' => $id];
			}, $chunk);

		}, array_chunk($r, $this::DB_CHUNK_SIZE));

		$count = count($chunks);

		$this->logger->info($this->processTimer().__METHOD__.': '.$endpoint.' response contains '.count($r).' items');

		foreach($chunks as $i => $chunk){
			$this->logger->debug($this->processTimer().'refresh '.$endpoint.' #'.($i+1).'/'.$count.', '.round((100 / $count) * $i, 2).'% done');

			$this->db->insert
				->into($table, 'IGNORE')
				->values($chunk)
				->multi()
			;
		}

		$this->logger->debug($this->processTimer().$endpoint.' -> '.$table.' refresh 100% done');
	}

	/**
	 * @return void
	 */
	protected function processURLs():void{
		$this->logger->info($this->processTimer().__METHOD__.': start');

		if(empty($this->urls)){
			$this->logger->info('no URLs to process');

			return;
		}

		$params = [];

		// process the items
		while(!empty($this->urls)){
			$params = array_shift($this->urls);

			$this->handleResponse($this->gw2->request(...$params), $params);
			// lazy request limiter
			usleep($this::SLEEP_TIMER * 1000000);
		}

		// process failed requests
		while(!empty($this->retry)){
			$retry = array_shift($this->retry);

			if($this->retryCount[md5(serialize($retry))] < $this::MAX_RETRIES){
				$this->handleResponse($this->gw2->request(...$params), $retry);
				usleep($this::SLEEP_TIMER * 1000000);
			}
		}

	}

	/**
	 * @param \chillerlan\HTTP\HTTPResponseInterface $response
	 * @param array|null                             $params
	 *
	 * @return void
	 */
	protected function handleResponse(HTTPResponseInterface $response, array $params = null):void{
		$this->logger->debug('response:', [$response, $params]);

		// there be dragons.
		if(in_array($response->headers->statuscode, [200, 206], true)){
			$this->processResponse($response, $params);
			return;
		}

		// instant retry on a 502
		// https://gitter.im/arenanet/api-cdi?at=56c3ba6ba5bdce025f69bcc8
		if($response->headers->statuscode === 502){
			$this->addRetry('URL readded due to a 502.', $params);
			return;
		}

		// request limit hit
		// @see https://forum-en.guildwars2.com/forum/community/api/HEADS-UP-rate-limiting-is-coming
		if($response->headers->statuscode === 429){
			$this->addRetry('request limit - URL readded.', $params);
			return;
		}

		// examine and add the failed response to retry later @todo
		$this->logger->error('unknown error: '.print_r($response, true));
	}

	/**
	 * @param string $msg
	 * @param array  $params
	 *
	 * @return void
	 */
	protected function addRetry(string $msg, array $params):void{
		$this->retry[]        = $params;
		$h                    = md5(serialize($params));
		$this->retryCount[$h] = ($this->retryCount[$h] ?? 0)+1;

		$this->logger->notice($msg);
	}


	/**
	 * @return string [     3.603s]
	 */
	protected function processTimer():string{
		return sprintf('[%10ss] ', sprintf('%01.3f', microtime(true) - $this->starttime));
	}

}
