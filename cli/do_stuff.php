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

require_once '../inc/config.inc.php';
require_once '../inc/mysqli.inc.php';
require_once '../inc/variables.inc.php';
require_once '../inc/utils.inc.php';
require_once '../inc/wikicode.inc.php';
require_once '../inc/request.inc.php';
require_once '../inc/attributes.inc.php';

$n = "\n";


#foreach($weapon_types['api'] as $w){// AND `rarity` = ?

#$q = sql_query('SELECT * FROM '.TABLE_ITEMS.' WHERE `type` = ? AND `subtype` = ? AND `pvp` = 0 AND LOWER(`name_de`) LIKE ? ORDER BY '.TABLE_ITEMS.'.`level`', array('Weapon', $w, 'axt des%'));// 'Rare',
#$q = sql_query('SELECT * FROM `gw2_worlds` ORDER BY `gw2_worlds`.`world_id`');

$q = sql_query('SELECT `id`, `data_en` FROM '.TABLE_ITEMS.'');

$count = count($q);
if(is_array($q) && $count > 0){

	// add stuff from the api-data-json
	// refresh attributes and add attribute combination names
	if($stmt = mysqli_prepare($db, 'UPDATE '.TABLE_ITEMS.' SET `attr1` = ?, `attr2` = ?, `attr3` = ?, `attr_name` = ? WHERE `id` = ?')){
		/** @noinspection PhpUndefinedVariableInspection */
		mysqli_stmt_bind_param($stmt, 'ssssi', $attr1, $attr2, $attr3, $attr_name, $id);
		foreach($q as $k => $i){
			$i['data_en'] = json_decode($i['data_en'],1);

			switch($i['data_en']['type']){
				case 'CraftingMaterial' : $t = 'crafting_material'; break;
				case 'MiniPet'          : $t = 'mini_pet'; break;
				case 'UpgradeComponent' : $t = 'upgrade_component'; break;
				default                 : $t = strtolower($i['data_en']['type']); break;
			}

			$com = array('', array());
			if(isset($i['data_en'][$t]['infix_upgrade']['attributes'])){
				$com = attribute_combination($i['data_en'][$t]['infix_upgrade']);
			}

			$attr1 = isset($com[1][0]) ? $com[1][0] : '';
			$attr2 = isset($com[1][1]) ? $com[1][1] : '';
			$attr3 = isset($com[1][2]) ? $com[1][2] : '';
			$attr_name = $com[0];
			$id = $i['id'];
			mysqli_stmt_execute($stmt);
		}
		mysqli_stmt_close($stmt);
	}


/*
	// replace all &nbsp; characters with spaces
	// 'SELECT `id`, `name_de`, `name_en`, `name_es`, `name_fr` FROM '.TABLE_ITEMS.' ORDER BY '.TABLE_ITEMS.'.`id`'
	if($stmt = mysqli_prepare($db, 'UPDATE '.TABLE_ITEMS.' SET `name_de` = ?, `name_en` = ?, `name_es` = ?, `name_fr` = ? WHERE `id` = ?')){
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
	//$q = sql_query('SELECT * FROM '.TABLE_ITEMS.' WHERE `type` = ? AND `subtype` = ? AND `rarity` = ? AND LOWER(`name_de`) LIKE ? ORDER BY '.TABLE_ITEMS.'.`level`', array('Weapon', $w, 'Exotic', '%stammes%'));// AND `pvp` = 1
#	$rcp = '';
#	$rcp = '{{Infobox Waffe| typ = '.str_replace($weapon_types['api'], $weapon_types['de'], $w).'| set = Stammes-Waffe}}';
#	foreach($q as $k => $i){
#		$recipe = sql_query('SELECT * FROM '.TABLE_RECIPES.' WHERE `output_id` = ?', array($i['id']));
#		$rcp .= $k===0 ?'Der/Die/Das \'\'\''.str_replace($fixes['de'], '', $i['name_de']).'\'\'\' ist der/die/das [['.str_replace($weapon_types['api'], $weapon_types['de'], $w).']] aus dem Set der [[Stammes-Waffe]]n. Er/Sie/Es kann als seltene Beute im [[Maguuma-Dschungel]] gefunden werden.==Ausrüstungswerte==' : '';
#		$rcp .= $i['id'].'|'.$i['name_de'].'|#WEITERLEITUNG [['.ucfirst(str_replace($fixes['de'], '', $i['name_de'])).']]'.PHP_EOL;// (Gegenstand)

/*
		$i['data_de'] = json_decode($i['data_de'],1);
		switch(true){
			case $k === 0: $tbl='tbl-start'; break;
			case $k === $count-1: $tbl='tbl-ende'; break;
			default: $tbl='tbl'; break;
		}
		$rcp .=  wiki_equip_de($i, array('layout' => $tbl)).PHP_EOL;
*/

#		$rcp .=  wiki_recipe_de($recipe[0], $i, array('layout' => $tbl), true).PHP_EOL;
#	}
#	$rcp .= '==Zutat für=={{Rezeptliste|zutat={{PAGENAME}}}}';
#	$rcp .= '{{Navigationsleiste Stammes-Waffe}}[[en:]][[fr:]]';


/*
	//generate a dye list for the french wiki
	$tpl = '';
	// SELECT t1.`color_id`, t1.`item_id`, t1.`tone`, t1.`set`, t1.`material`, t1.`name_fr`, t1.`icon`, t1.`cloth`, t1.`leather`, t1.`metal` FROM '.TABLE_COLORS.' AS t1 WHERE t1.`set` = 'none'
	//$q = sql_query('SELECT t1.`color_id`, t1.`item_id`, t1.`tone`, t1.`set`, t1.`material`, t1.`name_fr`, t1.`icon`, t1.`cloth`, t1.`leather`, t1.`metal`, t2.`rarity` FROM '.TABLE_COLORS.' AS t1, '.TABLE_ITEMS.' AS t2 WHERE t1.`color_id` = t2.`unlock_id` AND t2.`unlock_type` = \'Dye\'');
	foreach($q as $c){
		$c['cloth'] = json_decode($c['cloth'],1);
		$c['leather'] = json_decode($c['leather'],1);
		$c['metal'] = json_decode($c['metal'],1);
		$tpl .= '{{TEMPLATE_NAME|'.$c['color_id'].'|'.$c['item_id'].'|'.$c['name_fr'].'|'.rgb2hex($c['cloth']['rgb']).'|'.rgb2hex($c['leather']['rgb']).'|'.rgb2hex($c['metal']['rgb']).'|'.$c['set'].'|'.str_replace($rarity['en'],$rarity['fr'],$c['rarity']).'|'.$c['icon'].'|tone|material}}'.PHP_EOL;
#		print_r($c);
	}
*/


/*
	//compare items API with local items
	$data = gw2_api_request('items.json');
	$rcp.= '
	<table>';
	if(is_array($data) && isset($data['items'])){
		foreach($q as $item){
			if(!in_array($item['id'], $data['items'])){
				$rcp.= '
		<tr>
			<td>'.$item['id'].'</td>
			<td>'.item_code($item['id']).'</td>
			<td>'.$item['name_en'].'</td>
			<td>'.$item['name_de'].'</td>
			<td>'.$item['name_es'].'</td>
			<td>'.$item['name_fr'].'</td>
		</tr>';
			}
		}
		$rcp.= '
	</table>';

		echo $count.'<br />'.$rcp;
	}
*/

}


#}

function rgb2hex($rgb) {
	return sprintf('%02X%02X%02X', $rgb[0], $rgb[1], $rgb[2]);
}

?>