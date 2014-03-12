<?php
/**
 * gw2itemsearch.php
 * created: 06.09.13
 */

@error_reporting(E_ALL); // change to 0 for production environments

require_once 'inc/config.inc.php';
require_once 'inc/mysqli.inc.php';
require_once 'inc/variables.inc.php';
require_once 'inc/utils.inc.php';
require_once 'inc/request.inc.php';
require_once 'inc/wikicode.inc.php';

mb_internal_encoding('UTF-8');


// search
if(isset($_POST['search']) && !empty($_POST['search'])){
	// decode the json
	if(!$data = json_decode($_POST['search'],1)){
		exit('json error');
	}

	// determine the current page number
	$page = (isset($data['p']) && !empty($data['p']) && intval($data['p']) > 0) ? intval($data['p']) : 1;

	// search text
	$str = utf8_encode(base64_decode($data['str']));

	// language column names (whitelist)
	$cols = array(
		'de' => 'name_de',
		'en' => 'name_en',
		'es' => 'name_es',
		'fr' => 'name_fr',
	);

	// determine the correct language column / else set a default
	$col = array_key_exists($data['form']['lang'], $cols) ? $cols[$data['form']['lang']] : 'name_de';

	// build the WHERE clause for the SQL statement and add the corresponding values to an array
	$values = array();

	// determine search mode: id, id-range, string (is_int() doesn't work here!)
	$range = explode('-',$str);
	if(check_int($str)){
		$where = '`id` LIKE ?';
		$values[] = '%'.intval($str).'%';
	}
	else if(preg_match("/\d+-\d+/", $str) && is_array($range) && count($range) === 2 && check_int(trim($range[0])) && check_int(trim($range[1]))){
		$where = '`id` >= ? AND `id` <= ?';
		$values[] = min(intval(trim($range[0])),intval(trim($range[1])));
		$values[] = max(intval(trim($range[0])),intval(trim($range[1])));
	}
	else{
		$where = 'LOWER(`'.$col.'`) LIKE ?';
		$values[] = '%'. mb_strtolower($str).'%';
	}


//TODO: if mode = items...

	if(isset($data['form']['type']) && !empty($data['form']['type'])){
		$where .= ' AND `type` = ?';
		$values[] = $data['form']['type'];
	}

	if(isset($data['form']['subtype']) && !empty($data['form']['subtype'])){
		$where .= ' AND `subtype` = ?';
		$values[] = $data['form']['subtype'];
	}

	if(isset($data['form']['attributes']) && !empty($data['form']['attributes'])){
		$where .= ' AND `attr_name` = ?';
		$values[] = $data['form']['attributes'];
	}

	if(isset($data['form']['weight']) && !empty($data['form']['weight'])){
		$where .= ' AND `weight` = ?';
		$values[] = $data['form']['weight'];
	}

	if(isset($data['form']['rarity']) && !empty($data['form']['rarity'])){
		$where .= ' AND `rarity` = ?';
		$values[] = $data['form']['rarity'];
	}

	if(isset($data['form']['min-level']) && $data['form']['min-level'] !== ''){ // empty won't work because 0 counts as empty too
		$where .= ' AND `level` >= ?';
		$values[] = isset($data['form']['max-level']) && intval($data['form']['max-level']) < intval($data['form']['min-level']) ? intval($data['form']['max-level']) : intval($data['form']['min-level']);
	}

	if(isset($data['form']['max-level']) && $data['form']['max-level'] !== ''){
		$where .= ' AND `level` <= ?';
		$values[] = isset($data['form']['min-level']) && intval($data['form']['min-level']) > intval($data['form']['max-level']) ? intval($data['form']['min-level']) : intval($data['form']['max-level']);
	}

	if(isset($data['form']['redlinks']) && $data['form']['redlinks'] === 'true'){
		// language column names (whitelist)
		$redlink_cols = array(
			'de' => 'wikipage_de',
			'en' => 'wikipage_en',
			'es' => 'wikipage_es',
			'fr' => 'wikipage_fr',
		);
		$where .= ' AND `'.(array_key_exists($data['form']['lang'], $redlink_cols) ? $redlink_cols[$data['form']['lang']] : 'wikipage_de').'` = 0';// AND `wiki_checked` = 1
	}

	if(isset($data['form']['gametype']) && $data['form']['gametype'] !== ''){
		$where .= ' AND `pvp` = ?';
		$values[] = $data['form']['gametype'] === 'pvp' ? 1 : 0;
	}

	// first count the results to create the pagination
	$count = sql_prepared_query('SELECT COUNT(*) FROM '.TABLE_ITEMS.' WHERE '.$where, $values, null, false);

	// items per page limit
	$limit = isset($data['form']['limit']) && !empty($data['form']['limit']) ? max(1, min(250, intval($data['form']['limit']))) : 50;

	// create the pagination
	$pagination = pagination($count[0][0], $page, $limit);

	// ORDER BY clause
	$orderby_arr = array(
		'id' => 'id',
		'name' => $col,
		'level' => 'level',
		'rarity' => 'rarity',
		'weight' => 'weight',
		'attr' => 'attr_name',
	);

	$orderby = isset($data['form']['orderby']) && array_key_exists($data['form']['orderby'], $orderby_arr) ? $orderby_arr[$data['form']['orderby']] : (check_int($str) || preg_match("/\d+-\d+/", $str) ? 'id' : $col);
	$orderdir = isset($data['form']['orderdir']) && $data['form']['orderdir'] === 'desc' ? 'DESC' : 'ASC';

	// values for the LIMIT clause
	$values[] = empty($pagination['pages']) || !isset($pagination['pages'][$page]) ? 0 : $pagination['pages'][$page];
	$values[] = $limit;

	// get the item result
	$result = sql_prepared_query('SELECT `'.$col.'`, `id`, `level`, `rarity` FROM '.TABLE_ITEMS.' WHERE '.$where.' ORDER BY `'.TABLE_ITEMS.'`.`'.$orderby.'` '.$orderdir.' LIMIT ? , ?', $values);

	// process the result
	$list = '';
	if(is_array($result) && count($result) > 0){
		foreach($result as $row){
			// TODO: improve text highlighting
			$list .= '<div data-id="'.$row['id'].'" class="'.strtolower($row['rarity']).' '.(isset($data['id']) && intval($data['id']) === $row['id'] ? 'selected' : '').'">';
			if(mb_strlen($str) > 0){
				if(check_int($str) || preg_match("/\d+-\d+/", $str)){
					$list .= preg_replace('/('.$str.')/U', '<span class="highlight">$1</span>', $row['id']).': ';
				}
				$list .= mb_eregi_replace('('.$str.')', '<span class="highlight">\\1</span>', $row[$col]);
			}
			else{
				$list .= $row[$col];
			}
			$list .= ' ('.$row['level'].')</div>';
		}
	}
	else{
		$list .= 'no results';
	}


//TODO: else if mode = events...


	header('Content-type: text/html;charset=utf-8;');
	echo $pagination['pagination'].'
	<div class="table-row">
		<div class="table-cell" id="resultlist">'.$list.'</div>
		<div class="table-cell" id="details">'.(isset($data['id']) && check_int($data['id']) ? get_item_details($data['id'], $data['form']['lang']) : '').'</div>
	</div>';
	exit;
}

// refresh item data from the API
else if(isset($_POST['refresh']) && !empty($_POST['refresh'])){
	$response = array();
	if(check_int($_POST['refresh'])){
		$data_de = gw2_api_request('item_details.json?item_id='.$_POST['refresh'].'&lang=de');
		$data_en = gw2_api_request('item_details.json?item_id='.$_POST['refresh'].'&lang=en');
		$data_es = gw2_api_request('item_details.json?item_id='.$_POST['refresh'].'&lang=es');
		$data_fr = gw2_api_request('item_details.json?item_id='.$_POST['refresh'].'&lang=fr');

		if(isset($data_de['item_id']) && isset($data_en['item_id']) && isset($data_es['item_id']) && isset($data_fr['item_id'])){
			switch($data_en['type']){
				case 'CraftingMaterial' : $t = 'crafting_material'; break;
				case 'MiniPet' 			: $t = 'mini_pet'; break;
				case 'UpgradeComponent' : $t = 'upgrade_component'; break;
				default					: $t = strtolower($data_en['type']); break;
			}

			switch(true){
				case isset($data_en[$t]['recipe_id'])	: $unlock_id = intval($data_en[$t]['recipe_id']); break;
				case isset($data_en[$t]['color_id'])	: $unlock_id = intval($data_en[$t]['color_id']); break;
				default: $unlock_id = 0; break;
			}

			$de = str_replace(array(chr(194).chr(160), '  '), ' ', $data_de['name']);
			$en = str_replace(array(chr(194).chr(160), '  '), ' ', $data_en['name']);
			$es = str_replace(array(chr(194).chr(160), '  '), ' ', $data_es['name']);
			$fr = str_replace(array(chr(194).chr(160), '  '), ' ', $data_fr['name']);

			$items['id'][0] = $data_en['item_id'];
			$items['de'][0] = $de;
			$items['en'][0] = $en;
			$items['es'][0] = $es;
			$items['fr'][0] = $fr;
			$items['de_url'][0] = rawurlencode($de);
			$items['en_url'][0] = rawurlencode($en);
			$items['es_url'][0] = rawurlencode($es);
			$items['fr_url'][0] = rawurlencode($fr);
			$items['de_check'][0] = 0;
			$items['en_check'][0] = 0;
			$items['es_check'][0] = 0;
			$items['fr_check'][0] = 0;

			$items = wiki_check($items);

			$sql = 'UPDATE '.TABLE_ITEMS.' SET `signature` = ?, `file_id` = ?, `rarity` = ?, `weight` = ?, `type` = ?, `subtype` = ?, `unlock_type` = ?, `level` = ?, `value` = ?, `pvp` = ?, `attr1` = ?, `attr2` = ?, `attr3` = ?, `unlock_id` = ?, `name_de` = ?, `name_en` = ?, `name_es` = ?, `name_fr` = ?, `desc_de` = ?, `desc_en` = ?, `desc_es` = ?, `desc_fr` = ?,	`data_de` = ?, `data_en` = ?, `data_es` = ?, `data_fr` = ?,`wikipage_de` = ?, `wikipage_en` = ?, `wikipage_es` = ?, `wikipage_fr` = ?, `wiki_checked` = ?, `updated` = 1, `update_time` = ?  WHERE `id` = ?';

			$values = array(
				$data_en['icon_file_signature'],
				intval($data_en['icon_file_id']),
				$data_en['rarity'],
				isset($data_en[$t]['weight_class']) ? ($data_en[$t]['weight_class']) : 'None',
				$data_en['type'],
				isset($data_en[$t]['type']) ? $data_en[$t]['type'] : '',
				isset($data_en['consumable']['unlock_type']) ? $data_en['consumable']['unlock_type'] : '',
				intval($data_en['level']),
				intval($data_en['vendor_value']),
				in_array('Pvp',$data_en['game_types']) && in_array('PvpLobby',$data_en['game_types']) ? 1 : 0,
				isset($data_en[$t]['infix_upgrade']['attributes'][0]['attribute']) ? ($data_en[$t]['infix_upgrade']['attributes'][0]['attribute']) : '',
				isset($data_en[$t]['infix_upgrade']['attributes'][1]['attribute']) ? ($data_en[$t]['infix_upgrade']['attributes'][1]['attribute']) : '',
				isset($data_en[$t]['infix_upgrade']['attributes'][2]['attribute']) ? ($data_en[$t]['infix_upgrade']['attributes'][2]['attribute']) : '',
				$unlock_id,
				$de,
				$en,
				$es,
				$fr,
				strip_tags($data_de['description']),
				strip_tags($data_en['description']),
				strip_tags($data_es['description']),
				strip_tags($data_fr['description']),
				json_encode($data_de),
				json_encode($data_en),
				json_encode($data_es),
				json_encode($data_fr),
				$items['de_check'][0],
				$items['en_check'][0],
				$items['es_check'][0],
				$items['fr_check'][0],
				$items['de_check'][0] === 0 && $items['en_check'][0] === 0 && $items['es_check'][0] === 0 && $items['fr_check'][0] === 0 ? 0 : 1,
				time(),
				intval($data_en['item_id'])
			);

			if(sql_prepared_query($sql, $values)){
				$response['success'] = true;
				$response['message'] = 'everything fine.';
			}
			else{
				$response['error'] = true;
				$response['message'] = 'Error while executing the SQL.';
			}
		}
		else{
			$response['error'] = true;
			$response['message'] = 'Error while requesting the API.';
		}
	}
	else{
		$response['error'] = true;
		$response['message'] = 'id seems not to be a number. ('.$_POST['refresh'].')';
	}
	// set a json header end output the result
	header('Content-type: application/json;charset=utf-8;');
	echo json_encode($response);
	exit;
}

// detail display
else if(isset($_POST['details']) && !empty($_POST['details'])){
	if(!$data = json_decode($_POST['details'],1)){
		exit('json error');
	}
	header('Content-type: text/html;charset=utf-8;');
	echo get_item_details($data['id'], $data['lang']);
	exit;
}

// anything else is invalid
else{
	exit('invalid request');
}


/**
 * @param $id
 * @param $lng
 *
 * @return string
 */
function get_item_details($id, $lng){
	global $weapon_types, $fixes, $disciplines;
	$lng = in_array($lng, array('de','en','es','fr'), true) ? $lng : 'de';
	$n = "\n";
	$details = sql_prepared_query('SELECT * FROM '.TABLE_ITEMS.' WHERE `id` = ?', array($id));

	if(is_array($details) && count($details) > 0){
		// SELECT COUNT(*) AS `count`, `type`, `subtype` FROM '.TABLE_ITEMS.' GROUP BY `type`, `subtype` ORDER BY `type` LIMIT 0, 100
		$d = $details[0];

		// ingredient check
		$ingredient = sql_prepared_query('SELECT * FROM '.TABLE_RECIPES.' WHERE `ing_id_1` = ? OR `ing_id_2` = ? OR `ing_id_3` = ? OR `ing_id_4` = ?', array($d['id'], $d['id'], $d['id'], $d['id']));

		// recipe lookup
		$recipes = sql_prepared_query('SELECT * FROM '.TABLE_RECIPES.' WHERE `output_id` = ?', array($id));

		// overall fixes

		// API response JSON
		$d['data_de'] = json_decode($d['data_de'],1);
		$d['data_en'] = json_decode($d['data_en'],1);
		$d['data_es'] = json_decode($d['data_es'],1);
		$d['data_fr'] = json_decode($d['data_fr'],1);

		// wiki prefixes
		$wikis = array(
			'de' => 'wiki-de',
			'en' => 'wiki',
			'es' => 'wiki-es',
			'fr' => 'wiki-fr'
		);

		// interwiki links
		$interwiki = array(
			'de' => $n.'[[en:'.$d['name_en'].']]'.$n.'[[es:'.$d['name_es'].']]'.$n.'[[fr:'.$d['name_fr'].']]',
			'en' => $n.'[[de:'.$d['name_de'].']]'.$n.'[[es:'.$d['name_es'].']]'.$n.'[[fr:'.$d['name_fr'].']]',
			'es' => $n.'[[de:'.$d['name_de'].']]'.$n.'[[en:'.$d['name_en'].']]'.$n.'[[fr:'.$d['name_fr'].']]',
			'fr' => $n.'[[de:'.$d['name_de'].']]'.$n.'[[en:'.$d['name_en'].']]'.$n.'[[es:'.$d['name_es'].']]'
		);

		// wikicode
		$wikicode = array(
			'de' => '',
			'en' => '',
			'es' => '',
			'fr' => ''
		);

		if($d['type'] === 'Armor'){
#			$parts = array('Maske','Epauletten','Doublet','Handgelenkschutz','Stiefelhose','Schuhwerk','Antlitz','Schulterschützer','Deckmantel','Greifer','Beinkleid','Schreiter','Visier','Schulterschutz','Brustplatte','Kriegsfäuste','Beintaschen','Beinschienen');
#			$a_name = str_replace($parts, '', $d['name_de']);
#			$icon = 'Aufgestiegene Leichte Stiefel';
			$wikicode['de'] =
				wiki_infobox_armor_de($d).$n. //, array('icon' => $icon.' Icon.png', 'aussehen' => $a_name.'Rüstung (Leicht)')
				'==Beschaffung=='.
#				$n.'* {{Gegenstand Icon|'.$a_name.'Rüstungskiste}}'.
				$n.$n.
				(is_array($recipes) && count($recipes) > 0 ? '== Herstellung =='.$n.wiki_recipe_de($recipes[0], $d) : '').
#				$n.'{{Navigationsleiste '.$a_name.'Rüstung}}'.
				$n;
		}
#		else
#		if($d['type'] === 'Back'){
#			$wikicode['de'] =
#				'{{Infobox Rücken}}'.$n.
#				((!empty($d['desc_de'])) ? $n.'{{Zitat|'.strip_tags($d['desc_de']).'}}' : '');
#				((is_array($recipes) && count($recipes) > 0) ? $n.print_r($recipes,1) : ''); // not really working yet - MF recipes not in the API
#		}
		else if($d['type'] === 'Bag'){
			$wikicode['de'] =
				wiki_infobox_item_de($d, 'Tasche', array('stapelbar' => 'nein', 'plätze' => preg_replace("/[^\d]+/s", '', $d['desc_de']))).$n.
				(is_array($recipes) && count($recipes) > 0 ? '== Herstellung =='.$n.wiki_recipe_de($recipes[0], $d) : '==Beschaffung=='.$n);

			$wikicode['fr'] =
				wiki_infobox_item_fr($d, 'sac', array('icône' => '{{PAGENAME}}.png')).$n.
				(is_array($recipes) && count($recipes) > 0 ? wiki_recipe_fr($recipes[0], $d) : $n);


		}
		else if($d['type'] === 'Consumable'){
			$wikicode['de'] = '';
			if(isset($d['data_de']['consumable']['unlock_type']) && $d['data_de']['consumable']['unlock_type'] === 'CraftingRecipe'){
				$params = array(
					'icon' => 'Rezept '.($d['rarity'] === 'Ascended' ? 'Aufgestiegen ' : '').'Icon.png',
#					'preis' => '',
				);
				$wikicode['de'] .=
					wiki_infobox_item_de($d, 'Rezept', $params).$n.
					'==Beschaffung=='.$n.
					'* '.$n.$n.
					'== Verwendung =='.$n.
					'* Schaltet das Rezept für [['.str_replace('Rezept: ', '', $d['name_de']).']] frei.'.$n;
			}

			else{
				$wikicode['de'] .=
					wiki_infobox_item_de($d,'',array('preis' => '')).$n.
					'==Beschaffung=='.$n.
					'* '.$n.$n.
					'== Verwendung =='.$n.
					'* '.$n;
			}
		}
		else if($d['type'] === 'Container'){
			$wikicode['de'] =
				wiki_infobox_item_de($d, 'Behälter', array()).$n.'==Inhalt=='.$n.'* '.$n.$n.
				(is_array($recipes) && count($recipes) > 0 ? '== Herstellung =='.$n.wiki_recipe_de($recipes[0], $d) : '==Beschaffung=='.$n);

		}

		else if($d['type'] === 'CraftingMaterial'){

			$prof = '';
			$rate = '';

			foreach($disciplines['en'] as $dis){
				if(get_bitflag(constant($dis), $recipes[0]['disciplines'])){
					$prof = str_replace($disciplines['en'], $disciplines['de'], $dis);
					$rate = $recipes[0]['rating'];
				}
			}


			$wikicode['de'] =
				wiki_infobox_item_de($d, 'Komponente', array(mb_strtolower($prof) => $rate)).$n.
				'==Beschaffung=='.$n.'Ein \'\'\''.$d['name_de'].'\'\'\' kann von einem [['.$prof.']] hergestellt werden.'.$n.
				(is_array($recipes) && count($recipes) > 0 ? $n.'== Herstellung =='.$n.wiki_recipe_de($recipes[0], $d, array()) : '');

			$wikicode['de'].= $n.'== Zutat für =='.$n.'{{Rezeptliste|zutat={{PAGENAME}}}}'.$n;

		}

		else if($d['type'] === 'Gizmo'){
			$wikicode['de'] = '';
			if(!is_array($recipes) || count($recipes) === 0){
				$wikicode['de'] .= '{{Fehlende Informationen|Beschaffung}}'.$n;
			}
			$wikicode['de'] .=
				wiki_infobox_item_de($d, '', array('bild' => $d['name_de'].'.jpg')).$n.
				(is_array($recipes) && count($recipes) > 0 ? '== Herstellung =='.$n.wiki_recipe_de($recipes[0], $d) : '==Beschaffung=='.$n);
		}

		else if($d['type'] === 'MiniPet'){
			$wikicode['de'] =
				'{{Fehlende Informationen|Beschaffung}}'.$n.
				wiki_infobox_item_de($d, 'Miniatur', array('bild' => $d['name_de'].'.jpg')).$n.
				'==Beschaffung=='.$n;
		}

		else if($d['type'] === 'Weapon'){
			$wikicode['de'] =
				'{{Infobox Waffe'.$n.
				'| typ = '.str_replace($weapon_types['api'],$weapon_types['de'],$d['subtype']).$n.
				'| set = '.$n.
				'}}'.$n.

				(is_array($recipes) && count($recipes) > 0
					? '{{Zitat|}}'.$n.$n.'== Beschaffung =='.$n.'* {{Gegenstand Icon|}}'.$n.$n.'== Herstellung =='.$n.wiki_recipe_de($recipes[0], $d)
					: $n.wiki_equip_de($d).$n.'== Beschaffung =='.$n ).$n.
				'{{Navigationsleiste }}'.$n;
		}

		else{
			$wikicode['de'] = '';

			if(is_array($recipes) && count($recipes) > 0){
				$wikicode['de'] .= $n.print_r($recipes,1);
			}
			if(is_array($ingredient) && count($ingredient) > 0){
				$wikicode['de'] .= $n.print_r($ingredient,1);
			}
		}



		// TODO: display item details, list of ingredients
		$icon_url = 'http://gw2wbot.darthmaim.de/icon/'.$d['signature'].'/'.$d['file_id'].'.png';
		$icon_api = 'https://render.guildwars2.com/file/'.$d['signature'].'/'.$d['file_id'].'.png';
		$redirect = array(
			'de' => 'WEITERLEITUNG',
			'en' => 'REDIRECT',
			'es' => 'REDIRECT',
			'fr' => 'REDIRECTION'
		);

		$response = '';
		foreach($wikis as $lang => $wiki){
			$response .= '
		<img src="icons/'.$lang.'.png"> <a class="'.($d['wikipage_'.$lang] > 0 ? 'blue' : 'red').'link" href="http://'.$wiki.'.guildwars2.com/wiki/'.str_replace(' ', '_', $d['name_'.$lang]).'" target="wiki-'.$lang.'">'.$d['name_'.$lang].'</a> -
		<a href="http://'.$wiki.'.guildwars2.com/index.php?title='.str_replace(' ', '_', $d['name_'.$lang]).'&amp;action=edit" target="wiki-'.$lang.'">edit wiki</a>
		(<a target="apidata" href="https://api.guildwars2.com/v1/item_details.json?item_id='.$d['id'].'&amp;lang='.$lang.'">API</a> - <a target="gw2treasures" href="http://'.$lang.'.gw2treasures.de/item/'.$d['id'].'">gw2treasures</a>)<br />
		<input type="text" readonly="readonly" value="'.$d['name_'.$lang].'" class="selectable" /><br />
		<textarea cols="20" readonly="readonly" class="selectable" rows="3">'.$interwiki[$lang].'</textarea><br />';
		}

		$response .= '
		item id/chat code<br />
		<input type="text" readonly="readonly" value="'.$d['id'].'" class="selectable" style="width:17em;" />
		<input type="text" readonly="readonly" value="'.item_code($d['id']).'" class="selectable" style="width:17em;" /><br />
		icon<br />
		<img src="'.$icon_url.'"> <img src="'.$icon_api.'"><br />
		<span style="font-size: 70%;">(first icon has metadata stripped, second icon is the original from the API)</span><br />
		<input type="text" readonly="readonly" value="'.$icon_url.'" class="selectable" /><br />
		<input type="text" readonly="readonly" value="'.$icon_api.'" class="selectable" /><br />
		<a id="refresh" data-id="'.$d['id'].'" href="#">refresh data from the API</a> <br /><span id="msg"></span><br />

		<img src="icons/'.$lng.'.png"> wikicode (experimental)'.(is_array($recipes[0]) && count($recipes[0]) > 0 ? ' (<a href="https://api.guildwars2.com/v1/recipe_details.json?recipe_id='.$recipes[0]['recipe_id'].'" target="_blank">API/recipe</a>)' : '').'<br />
		<input type="text" readonly="readonly" value="#'.$redirect[$lng].' [['.str_replace($fixes[$lng], '', $d['name_'.$lng]).']]" class="selectable" /><br />
		<textarea cols="20" readonly="readonly" class="" style="width:35em;" rows="10">'.$wikicode[$lng].$interwiki[$lng].'</textarea><br />
';
	}
	else {
		$response = 'Item '.$id.' not found';
	}
	return $response;
}

?>