<?php
/**
 * do_stuff.php
 * created: 16.09.13
 *
 * command line!
 *
 * This is a multi purpose file for generating all kinds of wiki code - this is and will always stay messy just because...
 */


error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');
set_time_limit(0);
mb_internal_encoding('UTF-8');

require_once '../inc/mysqli.inc.php';
require_once '../inc/variables.inc.php';
require_once '../inc/utils.inc.php';
require_once '../inc/wikicode.inc.php';


foreach($weapon_types['api'] as $w){

$q = sql_query('SELECT * FROM `gw2_items` WHERE `type` = ? AND `subtype` = ? AND `rarity` = ? AND LOWER(`name_de`) LIKE ? ORDER BY `gw2_items`.`level`', array('Weapon', $w, 'Exotic', '%stammes%'));// AND `pvp` = 1
$count = count($q);
if(is_array($q) && $count > 0){

/*
	// add stuff from the api-data-json
	if($stmt = mysqli_prepare($db, 'UPDATE `gw2_items` SET `pvp` = ? WHERE `id` = ?')){
		mysqli_stmt_bind_param($stmt, 'ii', $pvp, $id);
		foreach($q as $k => $i){
			$i['data_de'] = json_decode($i['data_de'],1);

			$pvp = is_array($i['data_de']['game_types']) && in_array('Pvp',$i['data_de']['game_types']) && in_array('PvpLobby',$i['data_de']['game_types']) ? 1 : 0;
			$id = $i['id'];
			mysqli_stmt_execute($stmt);
		}
		mysqli_stmt_close($stmt);
	}
*/


/*
	// replace all &nbsp; characters with spaces
	// 'SELECT `id`, `name_de`, `name_en`, `name_es`, `name_fr` FROM `gw2_items` ORDER BY `gw2_items`.`id`'
	if($stmt = mysqli_prepare($db, 'UPDATE `gw2_items` SET `name_de` = ?, `name_en` = ?, `name_es` = ?, `name_fr` = ? WHERE `id` = ?')){
		mysqli_stmt_bind_param($stmt, 'ssssi', $name_de, $name_en, $name_es, $name_fr, $id);
		foreach($q as $i){
			$name_de = str_replace(array(chr(194).chr(160), '  '), ' ', $i['name_de']);
			$name_en = str_replace(array(chr(194).chr(160), '  '), ' ', $i['name_en']);
			$name_es = str_replace(array(chr(194).chr(160), '  '), ' ', $i['name_es']);
			$name_fr = str_replace(array(chr(194).chr(160), '  '), ' ', $i['name_fr']);
			$id = $i['id'];
			mysqli_stmt_execute($stmt);
		}
		mysqli_stmt_close($stmt);
	}
*/



	// generate weapon recipe tables for crafted weapons
	$rcp = '';
	$rcp = '{{Infobox Waffe
| typ = '.str_replace($weapon_types['api'], $weapon_types['de'], $w).'
| set = Stammes-Waffe
}}';
	foreach($q as $k => $i){
#		$recipe = sql_query('SELECT * FROM `gw2_recipes` WHERE `output_id` = ?', array($i['id']));

		$rcp .= $k===0 ?'

Der/Die/Das \'\'\''.str_replace($fixes['de'], '', $i['name_de']).'\'\'\' ist der/die/das [['.str_replace($weapon_types['api'], $weapon_types['de'], $w).']] aus dem Set der [[Stammes-Waffe]]n. Er/Sie/Es kann als seltene Beute im [[Maguuma-Dschungel]] gefunden werden.

==Ausrüstungswerte==
' : '';


		$i['data_de'] = json_decode($i['data_de'],1);

		switch(true){
			case $k === 0: $tbl='tbl-start'; break;
			case $k === $count-1: $tbl='tbl-ende'; break;
			default: $tbl='tbl'; break;
		}

		$rcp .=  wiki_equip_de($i, array('layout' => $tbl)).PHP_EOL;
#		$rcp .=  wiki_recipe_de($recipe[0], $i, array('layout' => $tbl), true).PHP_EOL;

	}

#	$rcp .= '==Zutat für=={{Rezeptliste|zutat={{PAGENAME}}}}';
	$rcp .= '{{Navigationsleiste Stammes-Waffe}}

[[en:]]
[[fr:]]';


	$fh = fopen($w.'-tribe.txt', 'w');
	fwrite($fh,$rcp);
	fclose($fh);

	echo $rcp;

}


}
?>