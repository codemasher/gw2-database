<?php
/**
 * event_update.php
 * created: 20.09.13
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
$data = gw2_api_request('event_details.json?lang=en');
if(is_array($data) && isset($data['events'])){
#	sql_query('TRUNCATE TABLE '.TABLE_EVENTS);
	$values = array();
	foreach($data['events'] as $id => $event){
		$values[] = array(
			$id,
			$event['level'],
			$event['map_id'],
			json_encode($event['flags']), // temporary, will change to a bitflag when this info makes more sense
			json_encode($event['location']),
			$event['name']
		);
		echo $id.' - '.$event['name'].$n;
	}
	sql_multi_row_insert('INSERT IGNORE INTO '.TABLE_EVENTS.' (`event_id`, `level`, `map_id`, `flags`, `location`, `name_en`) VALUES (?, ?, ?, ?, ?, ?)', $values, 'siisss');
	echo 'refresh done. ('.round((microtime(true) - $starttime),3).'s)'.$n.count($data['events']).' items in event_details.json.'.$n;
}


foreach(array('de','es','fr') as $lang){//'en',
	$starttime = microtime(true);
	$data = gw2_api_request('event_details.json?lang='.$lang);
	if(is_array($data) && isset($data['events'])){
		$values = array();
		foreach($data['events'] as $id => $event){
			$values = array($event['name'], $id);
			echo $id.' - '.$event['name'].$n;
		}
		sql_multi_row_insert('UPDATE '.TABLE_EVENTS.' SET `name_'.$lang.'` = ? WHERE `event_id` = ?', $values, 'ss');
		echo $lang.' refresh done. ('.round((microtime(true) - $starttime),3).'s)'.$n;
	}
}

?>