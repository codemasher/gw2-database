<?php
/**
 *
 * @filesource   ChatlinkTest.php
 * @created      05.10.2016
 * @package      chillerlan\GW2DBTests
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DBTests;

use chillerlan\GW2DB\Helpers\Chatlinks\Chatlink;

/**
 * Class ChatlinkTest
 */
class ChatlinkTest extends \PHPUnit_Framework_TestCase{

	/**
	 * @var \chillerlan\GW2DB\Helpers\Chatlinks\Chatlink
	 */
	private $chatlink;

	protected function setUp(){
		$this->chatlink = new Chatlink();
	}

	public function testDecode(){
		var_dump($this->chatlink->decode('[&AgG/twDgthIAAAZgAADnXwAA]'));
	}

	// @todo...
}
