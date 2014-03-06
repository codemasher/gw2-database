<?php
/**
 * maps_update.php
 * created: 20.09.13
 *
 * command line!
 */

error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');
set_time_limit(0);
mb_internal_encoding('UTF-8');

require_once '../inc/config.inc.php';
require_once '../inc/mysqli.inc.php';
require_once '../inc/request.inc.php';

$n = "\n";

$starttime = microtime(true);
$data = gw2_api_request('maps.json');
if(is_array($data) && isset($data['maps'])){
#	sql_query('TRUNCATE TABLE '.TABLE_MAPS);
	$values = array();
	foreach($data['maps'] as $id => $map){
		$values[] = array(
			$id,
			$map['continent_id'],
			$map['region_id'],
			$map['default_floor'],
			json_encode($map['floors']),
			json_encode($map['map_rect']),
			json_encode($map['continent_rect']),
			$map['min_level'],
			$map['max_level'],
			$map['map_name'],
			$map['region_name']
		);
		echo $id.' - '.$map['map_name'].$n;
	}
	$sql = 'INSERT IGNORE INTO '.TABLE_MAPS.' (`map_id`, `continent_id`, `region_id`, `default_floor`, `floors`, `map_rect`, `continent_rect`, `min_level`, `max_level`, `name_en`, `region_en`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
	sql_multi_row_insert($sql, $values, 'iiiisssiiss');
	echo 'refresh done. ('.round((microtime(true) - $starttime),3).'s)'.$n.count($data['maps']).' items in maps.json.'.$n;
}


foreach(array('de','es','fr') as $lang){//'en',
	$starttime = microtime(true);
	$data = gw2_api_request('maps.json?lang='.$lang);
	if(is_array($data) && isset($data['maps'])){
		$values = array();
		foreach($data['maps'] as $id => $map){
			$values[] = array($map['map_name'], $map['region_name'], $id);
			echo $id.' - '.$map['map_name'].$n;
		}
	}
	sql_multi_row_insert('UPDATE '.TABLE_MAPS.' SET `name_'.$lang.'` = ?, `region_'.$lang.'` = ? WHERE `map_id` = ?', $values, 'ssi');
	echo $lang.' refresh done. ('.round((microtime(true) - $starttime),3).'s)'.$n;
}

?>