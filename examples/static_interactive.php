<?php
/**
 *
 * @filesource   static_interactive.php
 * @created      13.04.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DBExamples;

use chillerlan\GW2DB\Helpers\Maps\Mapobject;
use chillerlan\GW2DB\Helpers\Maps\MapOptions;

require_once __DIR__.'/../vendor/autoload.php';

$mapDataInterface = new GW2DBDriver;
$mapOptions       = new MapOptions;

$mapobject = new Mapobject($mapDataInterface, $mapOptions);

header('Content-type: text/html;charset=utf-8;');

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>GW2 interactive static maps example</title>
	<style>
	</style >
</head>
<body>
<?php

$mapobject->setMap(15);

var_dump($mapobject);

?>
</body>
</html>



