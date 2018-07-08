<?php
/**
 * Class GW2DB
 *
 * @filesource   GW2DB.php
 * @created      06.01.2018
 * @package      chillerlan\GW2DB
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB;

use chillerlan\Database\Database;
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait, LoggerInterface, NullLogger};

class GW2DB implements LoggerAwareInterface{
	use LoggerAwareTrait;

	/**
	 * @var \chillerlan\GW2DB\GW2API
	 */
	protected $gw2api;

	/**
	 * @var \chillerlan\Database\Database
	 */
	protected $db;

	/**
	 * @var \chillerlan\GW2DB\Updaters\UpdaterInterface
	 */
	protected $updater;

	/**
	 * GW2DB constructor.
	 *
	 * @param \chillerlan\GW2DB\GW2API      $gw2api
	 * @param \chillerlan\Database\Database $db
	 * @param \Psr\Log\LoggerInterface|null $logger
	 *
	 * @throws \chillerlan\Database\Drivers\DriverException
	 */
	public function __construct(GW2API $gw2api, Database $db, LoggerInterface $logger = null){
		$this->gw2api = $gw2api;
		$this->db     = $db;
		$this->logger = $logger ?? new NullLogger;

		$this->db->connect();
	}

	/**
	 * @param string[] $updaters
	 */
	public function update(array $updaters){

		/** @var \chillerlan\GW2DB\Updaters\UpdaterInterface $updater */
		foreach($updaters as $updater){
			$this->updater = new $updater($this->gw2api, $this->db, $this->logger);
			$this->updater->init();
		}

	}

}
