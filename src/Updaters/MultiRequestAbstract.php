<?php
/**
 * Class MultiRequestAbstract
 *
 * @filesource   MultiRequestAbstract.php
 * @created      29.03.2017
 * @package      chillerlan\GW2DB\Updaters
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters;

use chillerlan\TinyCurl\{MultiRequest, MultiRequestOptions, MultiResponseHandlerInterface, ResponseInterface, URL};

/**
 *
 */
abstract class MultiRequestAbstract extends UpdaterAbstract implements MultiResponseHandlerInterface{

	/**
	 * @var array
	 */
	protected $urls = [];

	/**
	 * @var array
	 */
	protected $changes = [];

	/**
	 * @var string
	 */
	protected $lang;

	/**
	 * @param array $urls
	 *
	 * @return \chillerlan\TinyCurl\MultiRequestOptions
	 * @throws \chillerlan\TinyCurl\RequestException
	 */
	protected function fetchMulti(array $urls){
		$this->logToCLI('multirequest: start');

		$options = new MultiRequestOptions([
			'ca_info'     => $this->request->getOptions()->ca_info,
			'window_size' => self::CONCURRENT,
			'sleep'       => self::SLEEP_TIMER * 1000000,
		]);

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

	/**
	 * Schrödingers cat state handler.
	 *
	 * This method will be called within a loop in MultiRequest::processStack().
	 * You can either build your class around this MultiResponseHandlerInterface to process
	 * the response during runtime or return the response data to the running
	 * MultiRequest instance via addResponse() and receive the data by calling getResponseData().
	 *
	 * This method may return void or an URL object as a replacement for a failed request,
	 * which then will be re-added to the running queue.
	 *
	 * However, the return value will not be checked, so make sure you return valid URLs. ;)
	 *
	 * @param \chillerlan\TinyCurl\ResponseInterface $response
	 *
	 * @return bool|\chillerlan\TinyCurl\URL
	 * @internal
	 */
	public function handleResponse(ResponseInterface $response){
		$info = $response->info;

		// there be dragons.
		if(in_array($info->http_code, [200, 206], true)){
			return $this->processResponse($response);
		}

		// instant retry on a 502
		// https://gitter.im/arenanet/api-cdi?at=56c3ba6ba5bdce025f69bcc8
		elseif($info->http_code === 502){
			$this->logToCLI('URL readded due to a 502. ('.$info->url.')');

			return new URL($info->url);
		}

		// request limit hit
		// @see https://forum-en.guildwars2.com/forum/community/api/HEADS-UP-rate-limiting-is-coming
		elseif($info->http_code === 429){
			$this->logToCLI('request limit. ('.$info->url.')');

			return new URL($info->url);
		}

		// examine and add the failed response to retry later @todo
		$this->logToCLI('unknown error: '.print_r($response, true));

		return false;
	}

	/**
	 * @param \chillerlan\TinyCurl\ResponseInterface $response
	 *
	 * @return mixed
	 */
	abstract protected function processResponse(ResponseInterface $response);

 }
