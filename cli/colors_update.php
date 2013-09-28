<?php
/**
 * colors_update.php
 * created: 28.09.13
 */

error_reporting(E_ALL);
set_time_limit(0);

require_once '../inc/mysqli.inc.php';
require_once '../inc/request.inc.php';

$n = "\n";

$data = gw2_api_request('colors.json');
if(is_array($data) && isset($data['colors'])){
	echo count($data['colors']).' items in colors.json.'.$n;
	sql_query('TRUNCATE TABLE `gw2_colors`');
	if($stmt = mysqli_prepare($db, 'INSERT IGNORE INTO `gw2_colors` (`color_id`, `name_en`, `cloth`, `leather`, `metal`) VALUES (?, ?, ?, ?, ?)')){
		/** @noinspection PhpUndefinedVariableInspection */
		mysqli_stmt_bind_param($stmt, 'issss', $color_id, $name_en, $cloth, $leather, $metal);
		foreach($data['colors'] as $i => $c){
			$color_id = $i;
			$name_en = $c['name'];
			$cloth = json_encode($c['cloth']);
			$leather = json_encode($c['leather']);
			$metal = json_encode($c['metal']);
			mysqli_stmt_execute($stmt);
			echo $i.' - '.$c['name'].$n;
		}
		mysqli_stmt_close($stmt);
		echo 'refresh done.'.$n;
	}
}


foreach(array('de','es','fr') as $lang){//'en',
	$data = gw2_api_request('colors.json?lang='.$lang);
	if(is_array($data) && isset($data['colors'])){
		if($stmt = mysqli_prepare($db, 'UPDATE `gw2_colors` SET `name_'.$lang.'` = ? WHERE `color_id` = ?')){
			/** @noinspection PhpUndefinedVariableInspection */
			mysqli_stmt_bind_param($stmt, 'si', $name, $color_id);
			foreach($data['colors'] as $i => $c){
				$name = $c['name'];
				$color_id = $i;
				mysqli_stmt_execute($stmt);
				echo $i.' - '.$c['name'].$n;
			}
		}
		mysqli_stmt_close($stmt);
		echo $lang.' refresh done.'.$n;
	}
}


$q = sql_query('SELECT `id`, `unlock_id`, `file_id`, `rarity` FROM `gw2_items` WHERE `unlock_type` = \'Dye\'');
if(is_array($q)){
	if($stmt = mysqli_prepare($db, 'UPDATE `gw2_colors` SET `item_id` = ?, `set` = ?, `icon` = ? WHERE `color_id` = ?')){
		/** @noinspection PhpUndefinedVariableInspection */
		mysqli_stmt_bind_param($stmt, 'issi', $item_id, $set, $icon, $color_id);
		foreach($q as $r){

			// 'starter','common','uncommon','rare','special','none'
			switch($r['rarity']){
				case 'Fine': $s = 'common'; break;
				case 'Rare': $s = 'uncommon'; break;
				case 'Masterwork': $s = 'rare'; break;
				default: $s = 'none'; break;
			}

			//'fine-left','fine-right','masterwork-left','masterwork-right','rare-left','rare-right','special','none'
			switch(intval($r['file_id'])){
				case 66649: $i = 'rare-left'; break;
				case 66650: $i = 'rare-right'; break;
				case 66651: $i = 'masterwork-left'; break;
				case 66652: $i = 'fine-left'; break;
				case 66653: $i = 'masterwork-right'; break;
				case 66654: $i = 'fine-right'; break;
				case 561734: $i = 'special'; $s = 'special'; break;
				default: $i = 'none'; break;
			}

			$item_id = $r['id'];
			$set = $s;
			$icon = $i;
			$color_id = $r['unlock_id'];
			mysqli_stmt_execute($stmt);
		}
		mysqli_stmt_close($stmt);
	}
}

?> 