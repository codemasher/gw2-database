<?php
/**
 * event_update.php
 * created: 20.09.13
 */

error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');
set_time_limit(0);
mb_internal_encoding('UTF-8');

require_once '../inc/mysqli.inc.php';
require_once '../inc/request.inc.php';

$n = "\n";

$data = gw2_api_request('event_details.json');
if(is_array($data) && isset($data['events'])){
	echo count($data['events']).' items in event_details.json.'.$n;
#	sql_query('TRUNCATE TABLE `gw2_events`');
	if($stmt = mysqli_prepare($db, 'INSERT IGNORE INTO `gw2_events` (`event_id`, `level`, `map_id`, `flags`, `location`, `name_en`) VALUES (?, ?, ?, ?, ?, ?)')){
		/** @noinspection PhpUndefinedVariableInspection */
		mysqli_stmt_bind_param($stmt, 'siisss', $event_id, $level, $map_id, $flags, $location, $name_en);
		foreach($data['events'] as $i => $e){
			$event_id = $i;
			$level = $e['level'];
			$map_id = $e['map_id'];
			$flags = json_encode($e['flags']); // temporary, will change to a bitflag when this info makes more sense
			$location = json_encode($e['location']);
			$name_en = $e['name'];
			mysqli_stmt_execute($stmt);
			echo $i.' - '.$e['name'].$n;
		}
		mysqli_stmt_close($stmt);
		echo 'refresh done.'.$n;
	}
}


foreach(array('de','en','es','fr') as $lang){//'en',
	$data = gw2_api_request('event_details.json?lang='.$lang);
	if(is_array($data) && isset($data['events'])){
		if($stmt = mysqli_prepare($db, 'UPDATE `gw2_events` SET `name_'.$lang.'` = ? WHERE `event_id` = ?')){
			/** @noinspection PhpUndefinedVariableInspection */
			mysqli_stmt_bind_param($stmt, 'ss', $name, $event_id);
			foreach($data['events'] as $i => $e){
				$name = $e['name'];
				$event_id = $i;
				mysqli_stmt_execute($stmt);
				echo $i.' - '.$e['name'].$n;
			}
		}
		mysqli_stmt_close($stmt);
		echo $lang.' refresh done.'.$n;
	}
}

// TODO: Wikicheck

?>