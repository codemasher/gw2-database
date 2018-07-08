<?php
/**
 *
 * @filesource   endpointmap.php
 * @created      08.07.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

use chillerlan\GW2DB\GW2APIEndpoints;

require_once __DIR__.'/../vendor/autoload.php';

$apiMethods = [];

#$api = explode("\r\n", explode("\r\n\r\n", explode("API:\r\n",  file_get_contents('https://api.guildwars2.com/v2'), 2)[1])[0]);
$api = json_decode(file_get_contents('https://api.guildwars2.com/v2.json'), true)['routes'];

// add missing endpoints
$api[] = ['path' => '/v2/achievements/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/achievements/categories/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/achievements/groups/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/backstory/answers/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/backstory/questions/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/cats/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/characters/:id', 'lang' => false, 'auth' => true, 'active' => true];
$api[] = ['path' => '/v2/colors/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/commerce/exchange/gems?quantity', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/commerce/exchange/coins?quantity', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/commerce/listings/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/commerce/prices/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/commerce/transactions/current', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/commerce/transactions/current/buys', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/commerce/transactions/current/sells', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/commerce/transactions/history', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/commerce/transactions/history/buys', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/commerce/transactions/history/sells', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/continents/:continent_id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/continents/:continent_id/floors', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/continents/:continent_id/floors/:floor_id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/continents/:continent_id/floors/:floor_id/regions', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/continents/:continent_id/floors/:floor_id/regions/:region_id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/continents/:continent_id/floors/:floor_id/regions/:region_id/maps', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/continents/:continent_id/floors/:floor_id/regions/:region_id/maps/:map_id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/currencies/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/dungeons/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/emblem/backgrounds', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/emblem/backgrounds/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/emblem/foregrounds', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/emblem/foregrounds/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/files/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/finishers/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/gliders/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/guild/permissions/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/guild/upgrades/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/items/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/itemstats/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/legends/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/mailcarriers/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/maps/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/masteries/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/materials/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/minis/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/nodes/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/outfits/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/pets/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/professions/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/pvp/amulets/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/pvp/heroes/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/pvp/ranks/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/pvp/seasons/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/pvp/races/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/quaggans/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/raids/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/recipes/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/skills/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/skins/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/specializations/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/stories/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/stories/seasons/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/titles/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/traits/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/worlds/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/wvw/abilities/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/wvw/matches/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/wvw/matches/overview/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/wvw/matches/scores/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/wvw/matches/stats/:id', 'lang' => false, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/wvw/objectives/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/wvw/ranks/:id', 'lang' => true, 'auth' => false, 'active' => true];
$api[] = ['path' => '/v2/wvw/upgrades/:id', 'lang' => true, 'auth' => false, 'active' => true];

// prepare and normalize the endpoints
foreach($api as $endpoint){
	$query = [];
	$path_elements = [];
	$path = explode('/', trim($endpoint['path'], '/v2 '));
	$name = $path;


	$i = 1;
	foreach($path as $k => $el){
		$x = explode('?', $el);
		$name[$k] = ucfirst($x[0]);


		if(count($x) === 2){
			$query = array_merge($query, explode('&', $x[1]));
		}

		if(strpos($el, ':') === 0){
			$pe = substr($el, 1);
			$path_elements[] = substr($el, 1);
			$path[$k] = '%'.$i.'$s';
			$name[$k] = ($pe !== 'id' ? ucfirst(explode('_', $pe)[0]) : '').'Id';
			$i++;
		}
	}

	if((bool)$endpoint['lang']){
		$query[] = 'lang';
	}

	if((bool)$endpoint['auth']){
		$query[] = 'access_token';
	}

	if(!(bool)$endpoint['active']){
		continue;
	}

	$apiMethods[lcfirst(implode('', $name))] = [
		'path'          => '/'.explode('?', implode('/', $path))[0], // get rid of ?quantity for exchange coins/gems
#		'method'        => 'GET',
		'query'         => $query,
		'path_elements' => $path_elements,
#		'body'          => null,
#		'headers'       => [],
	];
}


uksort($apiMethods, 'strcmp');

#var_dump($apiMethods);

// get the current endpoint map
$reflection = new \ReflectionClass(GW2APIEndpoints::class);
$classfile  = $reflection->getFileName();

// now walk through the array and dump the method info
$str = [];
foreach($apiMethods as $methodname => $methodInfo){

	$str[] = '
	protected $'.$methodname.' = [
		\'path\'          => \''.$methodInfo['path'].'\',
		\'query\'         => ['.(!empty($methodInfo['query']) ? '\''.implode('\', \'', $methodInfo['query']).'\'' : '').'],
		\'path_elements\' => ['.(!empty($methodInfo['path_elements']) ? '\''.implode('\', \'', $methodInfo['path_elements']).'\'' : '').'],
	];';

}

// and replace the class
$content = '<?php
/**
 * Class GW2APIEndpoints (auto created)
 *
 * @link https://api.guildwars2.com/v2
 *
 * @filesource   GW2APIEndpoints.php
 * @created      '.date('d.m.Y').'
 * @package      chillerlan\\GW2DB
 * @license      MIT
 */

namespace chillerlan\\GW2DB;

use chillerlan\MagicAPI\EndpointMap;

class GW2APIEndpoints extends EndpointMap{
'.implode(PHP_EOL, $str).PHP_EOL.'
}'.PHP_EOL;

file_put_contents($classfile, $content);


// dump the docblock to the console
$doc = [];

foreach($apiMethods as $k => $v){
	$endpoint      = $v['path'];
	$params_in_url = count($v['path_elements']);
	$query_params  = $v['query'];

	$p = [];

	if($params_in_url > 0){

		foreach($v['path_elements'] as $i){
			$p[] = '$'.$i;
		}

	}

	if(!empty($query_params)){
		$p[] = 'array $params = [\''.implode('\', \'', $query_params).'\']';
	}

	$doc[] = ' * @method \\chillerlan\\HTTP\\HTTPResponseInterface '.$k.'('.implode(', ', $p).')';
}


echo '/**'.PHP_EOL.implode(PHP_EOL, $doc).PHP_EOL.' *'.'/';
