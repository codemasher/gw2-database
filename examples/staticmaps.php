<?php
/**
 * @filesource   staticmaps.php
 * @created      10.04.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DBExamples;

require_once __DIR__.'/../vendor/autoload.php';

use chillerlan\GW2DB\Helpers\Maps\LatLngHelpers;

$LatLngHelpers = new LatLngHelpers;

header('Content-type: text/html;charset=utf-8;');

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>GW2 static maps example</title>
	<style>
		.tile-row, .tile{ margin: 0; padding: 0; }
		.tile-row{ height: 256px; white-space: nowrap; }
		.tile{ width: 256px; display: inline-block; }
	</style >
</head>
<body>
<?php

$zoom = intval($_GET['zoom']);
$width = (1 << $zoom)*256;

// show Stonemist castle
$tiles = $LatLngHelpers->getTiles([[10400, 14300],[10800, 14850]], $zoom, LatLngHelpers::CONTINENT_MISTS, 3);

foreach($tiles as $row){
	echo '<div class="tile-row" style="width: '.$width.'px;">';

	foreach($row as $tile){
		echo '<img class="tile" src="'.$tile.'" />';
	}

	echo '</div>';
}

?>
</body>
</html>
