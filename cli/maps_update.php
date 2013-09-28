<?php
/**
 * maps_update.php
 * created: 20.09.13
 */

error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');
set_time_limit(0);
mb_internal_encoding('UTF-8');

require_once '../inc/mysqli.inc.php'; // https://github.com/codemasher/gw2api-tools/blob/master/inc/mysqli.inc.php
require_once '../inc/request.inc.php'; // https://github.com/codemasher/gw2api-tools/blob/master/inc/request.inc.php

$n = "\n";

$data = gw2_api_request('maps.json');
if(is_array($data) && isset($data['maps'])){
	echo count($data['maps']).' items in maps.json.'.$n;
	sql_query('TRUNCATE TABLE `gw2_maps`');
	if($stmt = mysqli_prepare($db, 'INSERT IGNORE INTO `gw2_maps` (`map_id`, `continent_id`, `region_id`, `default_floor`, `floors`, `map_rect`, `continent_rect`, `min_level`, `max_level`, `name_en`, `region_en`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')){
		/** @noinspection PhpUndefinedVariableInspection */
		mysqli_stmt_bind_param($stmt, 'iiiisssiiss', $map_id, $continent_id, $region_id, $default_floor, $floors, $map_rect, $continent_rect, $min_level, $max_level, $name_en, $region_en);
		foreach($data['maps'] as $i => $m){
			$map_id = $i;
			$continent_id = $m['continent_id'];
			$region_id = $m['region_id'];
			$default_floor = $m['default_floor'];
			$floors = json_encode($m['floors']);
			$map_rect = json_encode($m['map_rect']);
			$continent_rect = json_encode($m['continent_rect']);
			$min_level = $m['min_level'];
			$max_level = $m['max_level'];
			$name_en = $m['map_name'];
			$region_en = $m['region_name'];

			mysqli_stmt_execute($stmt);
			echo $i.' - '.$m['map_name'].$n;
		}
		mysqli_stmt_close($stmt);
		echo 'refresh done.'.$n;
	}
}


foreach(array('de','es','fr') as $lang){//'en',
	$data = gw2_api_request('maps.json?lang='.$lang);
	if(is_array($data) && isset($data['maps'])){
		if($stmt = mysqli_prepare($db, 'UPDATE `gw2_maps` SET `name_'.$lang.'` = ?, `region_'.$lang.'` = ? WHERE `map_id` = ?')){
			/** @noinspection PhpUndefinedVariableInspection */
			mysqli_stmt_bind_param($stmt, 'ssi', $name, $region, $map_id);
			foreach($data['maps'] as $i => $m){
				$name = $m['map_name'];
				$region = $m['region_name'];
				$map_id = $i;
				mysqli_stmt_execute($stmt);
				echo $i.' - '.$m['map_name'].$n;
			}
		}
		mysqli_stmt_close($stmt);
		echo $lang.' refresh done.'.$n;
	}
}

?>