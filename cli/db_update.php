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

require_once '../inc/config.inc.php';
require_once '../inc/mysqli.inc.php';
require_once '../inc/request.inc.php';
require_once '../inc/variables.inc.php';
require_once '../inc/attributes.inc.php';

$n = "\n";
$s = array(chr(194).chr(160), '  ');

// first of all refresh the item IDs
$data = gw2_api_request('items.json');
if(is_array($data) && isset($data['items'])){
	$values = array();
	$time = time();
	foreach($data['items'] as $item){
		$values[] = array($item, $time);
	}
	sql_multi_row_insert('INSERT IGNORE INTO '.TABLE_ITEMS.' (`id`, `date_added`) VALUES (?, ?)', $values, 'ii');
	echo 'refresh done.'.$n.count($data['items']).' items in items.json.'.$n;
}

// ok, now the nasty part - you may want to set `updated` to 0 before a full update
#sql_query('UPDATE '.TABLE_ITEMS.' SET `updated` = 0');

$q = sql_query('SELECT `id` FROM '.TABLE_ITEMS.' WHERE `updated` = 0 ORDER BY '.TABLE_ITEMS.'.`id`');
if(is_array($q) && count($q) > 0){
	$err = array();
	$sql = 'UPDATE '.TABLE_ITEMS.' SET `signature` = ?, `file_id` = ?, `rarity` = ?, `rarity_id` = ?, `weight` = ?, `type` = ?, `subtype` = ?, `unlock_type` = ?, `level` = ?, `value` = ?, `pvp` = ?, `attr1` = ?, `attr2` = ?, `attr3` = ?, `attr_name` = ?, `unlock_id` = ?, `name_de` = ?, `name_en` = ?, `name_es` = ?, `name_fr` = ?, `desc_de` = ?, `desc_en` = ?, `desc_es` = ?, `desc_fr` = ?, `data_de` = ?, `data_en` = ?, `data_es` = ?, `data_fr` = ?, `updated` = ?, `update_time` = ?  WHERE `id` = ?';

	// i don't use the multiline insert over here as it would blow up the system memory
	// also this way we insert each line as soon as we get the data from the API
	if($stmt = mysqli_prepare($db, $sql)){
		/** @noinspection PhpUndefinedVariableInspection */
		mysqli_stmt_bind_param($stmt, 'sissssssiiissssissssssssssssiii', $signature, $file_id, $rarity_str, $rarity_id, $weight, $type, $subtype, $unlock_type, $level, $value, $pvp, $attr1, $attr2, $attr3, $attr_name, $unlock, $name_de, $name_en, $name_es, $name_fr, $desc_de, $desc_en, $desc_es, $desc_fr, $json_de, $json_en, $json_es, $json_fr, $updated, $update_time, $item_id);
		foreach($q as $i){
			$starttime = microtime(true);
			$data_de = gw2_api_request('item_details.json?item_id='.$i['id'].'&lang=de');
			$data_en = gw2_api_request('item_details.json?item_id='.$i['id'].'&lang=en');
			$data_es = gw2_api_request('item_details.json?item_id='.$i['id'].'&lang=es');
			$data_fr = gw2_api_request('item_details.json?item_id='.$i['id'].'&lang=fr');

			if(isset($data_de['item_id']) && isset($data_en['item_id']) && isset($data_es['item_id']) && isset($data_fr['item_id'])){
				switch($data_en['type']){
					case 'CraftingMaterial' : $t = 'crafting_material'; break;
					case 'MiniPet'          : $t = 'mini_pet'; break;
					case 'UpgradeComponent' : $t = 'upgrade_component'; break;
					default                 : $t = strtolower($data_en['type']); break;
				}

				switch(true){
					case isset($data_en[$t]['recipe_id']) : $unlock_id = $data_en[$t]['recipe_id']; break;
					case isset($data_en[$t]['color_id'])  : $unlock_id = $data_en[$t]['color_id']; break;
					default                               : $unlock_id = 0; break;
				}

				$com = array('', array());
				if(isset($data_en[$t]['infix_upgrade']['attributes'])){
					$com = attribute_combination($data_en[$t]['infix_upgrade']);
				}

				$signature = $data_en['icon_file_signature'];
				$file_id = $data_en['icon_file_id'];
				$rarity_str = $data_en['rarity'];
				$rarity_id = @(int)$rarity_ids[$data_en['rarity']]; // surpress notices because of some broken items... (looking at you, 43948 and 43949)
				$weight = isset($data_en[$t]['weight_class']) ? ($data_en[$t]['weight_class']) : 'None';
				$type = $data_en['type'];
				$subtype = isset($data_en[$t]['type']) ? $data_en[$t]['type'] : '';
				$unlock_type = isset($data_en['consumable']['unlock_type']) ? $data_en['consumable']['unlock_type'] : '';
				$level = (int)$data_en['level'];
				$value = (int)$data_en['vendor_value'];
				$pvp = in_array('Pvp',$data_en['game_types']) && in_array('PvpLobby',$data_en['game_types']) ? 1 : 0;
				$attr1 = isset($com[1][0]) ? $com[1][0] : '';
				$attr2 = isset($com[1][1]) ? $com[1][1] : '';
				$attr3 = isset($com[1][2]) ? $com[1][2] : '';
				$attr_name = $com[0];
				$unlock = $unlock_id;
				$name_de = str_replace($s, ' ', $data_de['name']);
				$name_en = str_replace($s, ' ', $data_en['name']);
				$name_es = str_replace($s, ' ', $data_es['name']);
				$name_fr = str_replace($s, ' ', $data_fr['name']);
				$desc_de = $data_de['description'];
				$desc_en = $data_en['description'];
				$desc_es = $data_es['description'];
				$desc_fr = $data_fr['description'];
				$json_de = json_encode($data_de);
				$json_en = json_encode($data_en);
				$json_es = json_encode($data_es);
				$json_fr = json_encode($data_fr);
				$updated = 1;
				$update_time = time();

				$item_id = $i['id'];
				mysqli_stmt_execute($stmt);
				echo 'updated id: '.$i['id'].' - '.$data_en['name'].' ('.round((microtime(true) - $starttime),3).'s)'.$n;
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