<?php
/**
 * db_update.php
 * created: 14.09.13
 *
 * command line!
 * c:\amp\php\php.exe -c c:\amp\apache\conf\php.ini db_update.php
 */

error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');
set_time_limit(0);
mb_internal_encoding('UTF-8');

require_once '../inc/mysqli.inc.php'; // https://github.com/codemasher/gw2api-tools/blob/master/inc/mysqli.inc.php
require_once '../inc/request.inc.php'; // https://github.com/codemasher/gw2api-tools/blob/master/inc/request.inc.php

$n = "\n";

// first of all refresh the item IDs
$data = gw2_api_request('items.json');
if(is_array($data) && isset($data['items'])){
	echo count($data['items']).' items in items.json.'.$n;
	if($stmt = mysqli_prepare($db, 'INSERT IGNORE INTO `gw2_items` (`id`) VALUES (?)')){
		mysqli_stmt_bind_param($stmt, 'i', $id);
		foreach($data['items'] as $item){
			$id = $item;
			mysqli_stmt_execute($stmt);
		}
		mysqli_stmt_close($stmt);
		echo 'refresh done.'.$n;
	}
}

// ok, now the nasty part - you may want to set `updated` to 0 before a full update
$q = sql_query('SELECT `id` FROM `gw2_items` WHERE `updated` = 0 ORDER BY `gw2_items`.`id`');
if(is_array($q) && count($q) > 0){
	$err = array();
	$sql = 'UPDATE `gw2_items` SET `signature` = ?, `file_id` = ?, `rarity` = ?, `weight` = ?, `type` = ?, `subtype` = ?, `unlock_type` = ?, `level` = ?, `value` = ?, `pvp` = ?, `attr1` = ?, `attr2` = ?, `attr3` = ?, `unlock_id` = ?, `name_de` = ?, `name_en` = ?, `name_es` = ?, `name_fr` = ?, `desc_de` = ?, `desc_en` = ?, `desc_es` = ?, `desc_fr` = ?,	`data_de` = ?, `data_en` = ?, `data_es` = ?, `data_fr` = ?, `updated` = ?, `update_time` = ?  WHERE `id` = ?';
	if($stmt = mysqli_prepare($db, $sql)){
		/** @noinspection PhpUndefinedVariableInspection */
		mysqli_stmt_bind_param($stmt, 'sisssssiiisssissssssssssssiii', $signature, $file_id, $rarity, $weight, $type, $subtype, $unlock_type, $level, $value, $pvp, $attr1, $attr2, $attr3, $unlock, $name_de, $name_en, $name_es, $name_fr, $desc_de, $desc_en, $desc_es, $desc_fr, $json_de, $json_en, $json_es, $json_fr, $updated, $update_time, $item_id);
		foreach($q as $i){
			$starttime = microtime(true);
			$data_de = gw2_api_request('item_details.json?item_id='.$i['id'].'&lang=de');
			$data_en = gw2_api_request('item_details.json?item_id='.$i['id']);
			$data_es = gw2_api_request('item_details.json?item_id='.$i['id'].'&lang=es');
			$data_fr = gw2_api_request('item_details.json?item_id='.$i['id'].'&lang=fr');

			switch($data_en['type']){
				case 'CraftingMaterial' : $t = 'crafting_material'; break;
				case 'MiniPet' 			: $t = 'mini_pet'; break;
				case 'UpgradeComponent' : $t = 'upgrade_component'; break;
				default					: $t = strtolower($data_en['type']); break;
			}

			switch(true){
				case isset($data_en[$t]['recipe_id'])	: $unlock_id = $data_en[$t]['recipe_id']; break;
				case isset($data_en[$t]['color_id'])	: $unlock_id = $data_en[$t]['color_id']; break;
				default: $unlock_id = 0; break;
			}

			if(isset($data_de['item_id']) && isset($data_en['item_id']) && isset($data_es['item_id']) && isset($data_fr['item_id'])){
				$signature = $data_en['icon_file_signature'];
				$file_id = $data_en['icon_file_id'];
				$rarity = $data_en['rarity'];
				$weight = isset($data_en[$t]['weight_class']) ? ($data_en[$t]['weight_class']) : 'None';
				$type = $data_en['type'];
				$subtype = isset($data_en[$t]['type']) ? $data_en[$t]['type'] : '';
				$unlock_type = isset($data_en['consumable']['unlock_type']) ? $data_en['consumable']['unlock_type'] : '';
				$level = intval($data_en['level']);
				$value = intval($data_en['vendor_value']);
				$pvp = in_array('Pvp',$data_en['game_types']) && in_array('PvpLobby',$data_en['game_types']) ? 1 : 0;
				$attr1 = isset($data_en[$t]['infix_upgrade']['attributes'][0]['attribute']) ? ($data_en[$t]['infix_upgrade']['attributes'][0]['attribute']) : '';
				$attr2 = isset($data_en[$t]['infix_upgrade']['attributes'][1]['attribute']) ? ($data_en[$t]['infix_upgrade']['attributes'][1]['attribute']) : '';
				$attr3 = isset($data_en[$t]['infix_upgrade']['attributes'][2]['attribute']) ? ($data_en[$t]['infix_upgrade']['attributes'][2]['attribute']) : '';
				$unlock = $unlock_id;
				$name_de = str_replace(array(chr(194).chr(160), '  '), ' ', $data_de['name']);
				$name_en = str_replace(array(chr(194).chr(160), '  '), ' ', $data_en['name']);
				$name_es = str_replace(array(chr(194).chr(160), '  '), ' ', $data_es['name']);
				$name_fr = str_replace(array(chr(194).chr(160), '  '), ' ', $data_fr['name']);
				$desc_de = strip_tags($data_de['description']);
				$desc_en = strip_tags($data_en['description']);
				$desc_es = strip_tags($data_es['description']);
				$desc_fr = strip_tags($data_fr['description']);
				$json_de = json_encode($data_de);
				$json_en = json_encode($data_en);
				$json_es = json_encode($data_es);
				$json_fr = json_encode($data_fr);
				$updated = 1;
				$update_time = time();

				$item_id = $i['id'];
				mysqli_stmt_execute($stmt);
				echo 'updated id: '.$i['id'].' - '.$data_en['name'].' ('.round((microtime(true) - $starttime),3).'s)'.$n; // character encoding sucks -.-
			}
			else{
				$err[] = $i['id'];
			}
		}
		mysqli_stmt_close($stmt);
		echo 'update done.'.$n;
		if(count($err) > 0){
			echo 'errors:'.$n.implode($n,$err).$n.$n;
		}
	}
}

?>