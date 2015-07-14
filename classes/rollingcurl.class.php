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
	public $urls = [];

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
	 * concurrent request counter
	 * @var int
	 */
	private $request_count = 0;


	/**
	 * initializes the curl_multi and sets some needed variables
	 *
	 * @param array $urls
	 * @param callable $callback
	 */
	public function __construct(array $urls, callable $callback){
		$this->handle = curl_multi_init();
		$this->urls = $urls;
		$this->callback = $callback;
		$this->request_count = count($this->urls);
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
	 * creates a new handle for $request[$index]
	 *
	 * @param $index
	 */
	private function create_handle($index){
		$ch = curl_init($this->base_url.$this->urls[$index]);
		curl_setopt_array($ch, $this->curl_options);
		curl_multi_add_handle($this->handle, $ch);
#		echo $this->base_url.$this->urls[$index].PHP_EOL;
	}

	/**
	 * processes the requests
	 */
	public function process(){
		for($i = 0; $i < $this->window_size; $i++){
			$this->create_handle($i);
		}

		do{
			if(curl_multi_exec($this->handle, $active) !== CURLM_OK){
				break;
			}
			while($state = curl_multi_info_read($this->handle)){
				call_user_func($this->callback, curl_multi_getcontent($state['handle']), curl_getinfo($state['handle']));
				if($i < $this->request_count && isset($this->urls[$i])){
					$this->create_handle($i);
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
