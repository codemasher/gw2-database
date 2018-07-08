<?php
/**
 * Class GW2APITest
 *
 * @filesource   GW2APITest.php
 * @created      05.07.2018
 * @package      chillerlan\GW2DBTests
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\GW2DBTests;

use chillerlan\GW2DB\GW2API;
use chillerlan\OAuthTest\Core\OAuth2Test;

class GW2APITest extends OAuth2Test{

	protected $FQCN = GW2API::class;

	public function testGetAuthURL(){
		$this->markTestSkipped('N/A');
	}

	public function testGetAccessToken(){
		$this->markTestSkipped('N/A');
	}

	/**
	 * @expectedException \chillerlan\OAuth\Core\ProviderException
	 * @expectedExceptionMessage The Guild Wars 2 API doesn't support OAuth authentication anymore
	 */
	public function testRequestGetAuthURLNotSupportedException(){
		$this->provider->getAuthURL();
	}

	/**
	 * @expectedException \chillerlan\OAuth\Core\ProviderException
	 * @expectedExceptionMessage The Guild Wars 2 API doesn't support OAuth authentication anymore
	 */
	public function testRequestGetAccessTokenNotSupportedException(){
		$this->provider->getAccessToken('foo');
	}

}
