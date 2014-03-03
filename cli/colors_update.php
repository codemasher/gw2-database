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

$starttime = microtime(true);
$data = gw2_api_request('colors.json?lang=en');
if(is_array($data) && isset($data['colors'])){
	sql_query('TRUNCATE TABLE `gw2_colors`');
	$values = array();
	foreach($data['colors'] as $id => $color){
		$values[] = array(
			$id,
			$color['name'],
			json_encode($color['cloth']),
			json_encode($color['leather']),
			json_encode($color['metal'])
		);
		echo $id.' - '.$color['name'].$n;
	}
	sql_multi_row_insert('INSERT IGNORE INTO `gw2_colors` (`color_id`, `name_en`, `cloth`, `leather`, `metal`) VALUES (?, ?, ?, ?, ?)', $values, 'issss');
	echo 'refresh done. ('.round((microtime(true) - $starttime),3).'s)'.$n.count($data['colors']).' items in colors.json.'.$n;
}


foreach(array('de','es','fr') as $lang){//'en',
	$starttime = microtime(true);
	$data = gw2_api_request('colors.json?lang='.$lang);
	if(is_array($data) && isset($data['colors'])){
		$values = array();
		foreach($data['colors'] as $id => $color){
			$values[] = array($color['name'], $id);
			echo $id.' - '.$color['name'].$n;
		}
		sql_multi_row_insert('UPDATE `gw2_colors` SET `name_'.$lang.'` = ? WHERE `color_id` = ?', $values, 'si');
		echo $lang.' refresh done. ('.round((microtime(true) - $starttime),3).'s)'.$n;
	}
}


$q = sql_prepared_query('SELECT `id`, `unlock_id`, `file_id`, `rarity` FROM `gw2_items` WHERE `unlock_type` = ?', array('Dye'));
if(is_array($q)){
	$values = array();
	foreach($q as $item){
		// 'starter','common','uncommon','rare','special','none'
		switch($item['rarity']){
			case 'Fine': $set = 'common'; break;
			case 'Rare': $set = 'uncommon'; break;
			case 'Masterwork': $set = 'rare'; break;
			default: $set = 'none'; break;
		}

		//'fine-left','fine-right','masterwork-left','masterwork-right','rare-left','rare-right','special','none'
		switch((int)$item['file_id']){
			case 66649: $icon = 'rare-left'; break;
			case 66650: $icon = 'rare-right'; break;
			case 66651: $icon = 'masterwork-left'; break;
			case 66652: $icon = 'fine-left'; break;
			case 66653: $icon = 'masterwork-right'; break;
			case 66654: $icon = 'fine-right'; break;
			case 561734: $icon = 'special'; $set = 'special'; break;
			default: $icon = 'none'; break;
		}

		$values[] = array(
			$item['id'],
			$set,
			$icon,
			$item['unlock_id']
		);
	}
	sql_multi_row_insert('UPDATE `gw2_colors` SET `item_id` = ?, `set` = ?, `icon` = ? WHERE `color_id` = ?', $values, 'issi');
}

?>