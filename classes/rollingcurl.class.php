<?php
/**
 * @filesource rollingcurl.class.php
 * @version    0.1.0
 * @link       https://gist.github.com/Xeoncross/2362936
 * @link       http://www.onlineaspect.com/2009/01/26/how-to-use-curl_multi-without-blocking/
 * @link       https://code.google.com/p/rolling-curl/
 * @created    24.09.2014
 */

/**
 * Class RollingCurl
 */
class RollingCurl{

	/**
	 * the base URL for each request, useful if you're hammering the same host all the time
	 * @var string
	 */
	public $base_url = '';

	/**
	 * the requests - make sure to specify the full URL if you don't use $base_url
	 * @var array
	 */
	public $requests = [];

	/**
	 * maximum of concurrent requests
	 * @var int
	 */
	public $window_size = 5;

	/**
	 * options for each curl instance - make sure to append CURLOPT_RETURNTRANSFER = true if you specify your own
	 * @var array
	 */
	public $curl_options = [
		CURLOPT_RETURNTRANSFER => true,
	];

	/**
	 * wtb timeout
	 * @var int
	 */
	public $timeout = 10;

	/**
	 * callback function to process the incoming data
	 * @var callable
	 */
	private $callback;

	/**
	 * the curl_multi master handle
	 * @var resource
	 */
	private $handle;

	/**
	 * the map of active recuests
	 * @var array
	 */
	private $request_map = [];

	/**
	 * concurrent request counter
	 * @var int
	 */
	private $request_count = 0;


	/**
	 * initializes the curl_multi and sets some needed variables
	 *
	 * @param array $requests
	 * @param callable $callback
	 */
	public function __construct(array $requests, callable $callback){
		$this->handle = curl_multi_init();
		$this->requests = $requests;
		$this->callback = $callback;

		$this->request_count = count($this->requests);
		if($this->request_count < $this->window_size){
			$this->window_size = $this->request_count;
		}
	}

	/**
	 * closes the curl instance
	 */
	public function __destruct(){
		curl_multi_close($this->handle);
	}

	/**
	 * processes the requests
	 */
	public function process(){

		for($i = 0; $i < $this->window_size; $i++){
			$ch = curl_init($this->base_url.$this->requests[$i]);
			curl_setopt_array($ch, $this->curl_options);
			curl_multi_add_handle($this->handle, $ch);
			$this->request_map[(string)$ch] = $i;
		}

		do{
			// https://bugs.php.net/bug.php?id=64443
#			while(($exec = curl_multi_exec($this->handle, $active)) == CURLM_CALL_MULTI_PERFORM){}
			$exec = curl_multi_exec($this->handle, $active);

			if($exec != CURLM_OK){
				break;
			}

			while($state = curl_multi_info_read($this->handle)){

				$key = (string)$state['handle'];
				unset($this->request_map[$key]);
				// no need to check for is_callable because type hint
				call_user_func($this->callback, curl_multi_getcontent($state['handle']), curl_getinfo($state['handle']));

				if($i < $this->request_count && isset($this->requests[$i])){
					$ch = curl_init($this->base_url.$this->requests[$i]);
					curl_setopt_array($ch, $this->curl_options);
					curl_multi_add_handle($this->handle, $ch);
					$this->request_map[(string)$ch] = $i;
					$i++;
				}

				curl_multi_remove_handle($this->handle, $state['handle']);
			}

			if($active){
				curl_multi_select($this->handle, $this->timeout);
			}

		}
		while($active);
	}

}
