<?php
/**
 * wikicode.inc.php
 * created: 17.09.13
 */

/*
 * TODO
 */

function wiki_infobox_item_de($item, $type = '', $params = array()){
	global $rarity, $attributes;
	$n = "\n";
	$infobox_item =
		'{{Infobox Gegenstand'.$n.
		'| id = '.$item['id'].$n.
		'| typ = '.$type.$n.
		'| seltenheit = '.str_replace($rarity['en'], $rarity['de'], $item['rarity']).$n.
		(intval($item['level']) > 0 ? '| stufe = '.$item['level'].$n : '').
		(intval($item['value']) > 0 && !in_array('NoSell', $item['data_de']['flags']) ? '| händlerwert = {{Münzen|'.$item['value'].'}}'.$n : '').
		(!empty($item['desc_de']) ? '| beschreibung = '.preg_replace('/('.implode('|',$attributes['en']).')/Uis','[[$1]]',strip_tags($item['desc_de'])).$n : '');

	switch(true){
		case in_array(array('AccountBound','SoulBindOnUse'), $item['data_de']['flags']): $infobox_item .= '| gebunden = accbenutzung'.$n; break;
		case in_array('SoulbindOnAcquire', $item['data_de']['flags']): $infobox_item .= '| gebunden = seele'.$n; break;
		case in_array('AccountBound', $item['data_de']['flags']) && !in_array('SoulBindOnUse', $item['data_de']['flags']): $infobox_item .= '| gebunden = account'.$n; break;
		case in_array('SoulBindOnUse', $item['data_de']['flags']) && !in_array('AccountBound', $item['data_de']['flags']): $infobox_item .= '| gebunden = benutzung'.$n; break;
	}


	foreach($params as $k => $v){
		$infobox_item .= '| '.$k.' = '.$v.$n;
	}

	$infobox_item .= '}}'.$n;



	return $infobox_item;
}




/**
 * @param array $recipe
 * @param array $item
 * @param array $params
 * @param bool  $rarity_suffix
 *
 * @return string
 */
function wiki_recipe_de($recipe, $item, $params = array(), $rarity_suffix = false){
	global $rarity, $attributes, $infusions, $disciplines;
	$n = "\n";

	// get the name of the recipe item if learned from item
	if((bool)$recipe['from_item'] === true){
		$unlock = sql_query('SELECT `name_de` FROM `gw2_items` WHERE `unlock_type` = \'CraftingRecipe\' AND `unlock_id` = ?', array($recipe['recipe_id']));
		$unlock = $unlock[0]['name_de'];
	}
	else{
		$unlock = 'Automatisch';
		$unlock = 'Erforschung';
	}

	$recipe_box =
		'{{Rezept'.$n.
		'| name = '.$item['name_de'].($rarity_suffix === true ? ' ('.str_replace($rarity['en'], $rarity['de'], $item['rarity']).')' : '').$n.
		'| id = '.(!empty($recipe['output_id']) ? $recipe['output_id'] : $item['id']).$n.
		'| rezept-id = '.$recipe['recipe_id'].$n.
		'| freischaltung = '.$unlock.$n;

	// parse disciplines
	foreach($disciplines['en'] as $dis){
		if(get_bitflag(constant($dis), $recipe['disciplines'])){
			$recipe_box .=
				'| '.mb_strtolower(str_replace($disciplines['en'], $disciplines['de'], $dis)).' = '.$recipe['rating'].$n;
		}
	}

	// equipment specific params
	if(in_array($item['type'], array('Armor', 'Back', 'Trinket', 'Weapon'))){
		$t = strtolower($item['type']);

		$recipe_box .=
			'| ausrüstungsstufe = '.$item['level'].$n.
			'| seltenheit = '.str_replace($rarity['en'], $rarity['de'], $item['rarity']).$n.
			(intval($item['value']) > 0 && !in_array('NoSell', $item['data_de']['flags']) ? '| händlerwert = '.$item['value'].$n : '').
			($item['type'] === 'Weapon' ? '| waffenstärke = '.$item['data_de']['weapon']['min_power'].' - '.$item['data_de']['weapon']['max_power'].$n : '').
			(isset($item['data_de'][$t]['defense']) && $item['data_de'][$t]['defense'] > 0 ? '| verteidigung = '.$item['data_de'][$t]['defense'].$n : '');


		// parse attributes
		if(isset($item['data_de'][$t]['infix_upgrade'])){
			$i = 1;
			if(isset($item['data_de'][$t]['infix_upgrade']['attributes'])){
				foreach($item['data_de'][$t]['infix_upgrade']['attributes'] as $attr){
					$recipe_box .=
						'| attribut'.$i.' = '.str_replace($attributes['api'], $attributes['de'], $attr['attribute']).$n.
						'| attribut'.$i.'-wert = '.$attr['modifier'].$n;
					$i++;
				}
			}

			//check for a buff value (still unsure wether this can be array or not)
			if(isset($item['data_de'][$t]['infix_upgrade']['buff'])){
				$recipe_box .=
					'| attribut'.$i.' = '.trim(preg_replace("#[\d\+%]#U",'',$item['data_de'][$t]['infix_upgrade']['buff']['description'])).$n.
					'| attribut'.$i.'-wert = '.preg_replace("#[^\d]#U",'',$item['data_de'][$t]['infix_upgrade']['buff']['description']).$n;
			}
		}

		// check for an upgrade
		if(isset($item['data_de'][$t]['suffix_item_id'])){
			$recipe_box .=
				'| aufwertung = ';
			if(!empty($item['data_de'][$t]['suffix_item_id'])){
				$suffix_name = sql_query('SELECT `name_de` FROM `gw2_items` WHERE `id` = ?', array($item['data_de'][$t]['suffix_item_id']));
				$recipe_box .= (is_array($suffix_name) && isset($suffix_name[0]['name_de']) ? $suffix_name[0]['name_de'] : '');
			}
			$recipe_box .= $n;
		}

		// check for infusion slots
		if(isset($item['data_de'][$t]['infusion_slots'][0])){
			$recipe_box .=
				'| infusion = '.str_replace($infusions['api'], $infusions['de'], $item['data_de'][$t]['infusion_slots'][0]['flags'][0]).$n;
			// is there a second slot? (subject to change if more infusion slots are added in futute)
			if(isset($item['data_de'][$t]['infusion_slots'][1])){
				$recipe_box .=
					'| infusion2 = '.str_replace($infusions['api'], $infusions['de'], $item['data_de'][$t]['infusion_slots'][1]['flags'][0]).$n;
			}
		}

		// binding
		switch(true){
			case in_array('AccountBound', $item['data_de']['flags']) && in_array('SoulBindOnUse', $item['data_de']['flags']): $recipe_box .= '| gebunden = accbenutzung'.$n; break;
			case in_array('AccountBound', $item['data_de']['flags']) && !in_array('SoulBindOnUse', $item['data_de']['flags']): $recipe_box .= '| gebunden = account'.$n; break;
			case in_array('SoulBindOnUse', $item['data_de']['flags']) && !in_array('AccountBound', $item['data_de']['flags']): $recipe_box .= '| gebunden = benutzung'.$n; break;
			case in_array('SoulbindOnAcquire', $item['data_de']['flags']): $recipe_box .= '| gebunden = seele'.$n; break;
		}
	}

	// parse ingredients
	for($i=1; $i<5; $i++){
		if(intval($recipe['ing_count_'.$i]) > 0){
			$ing = sql_query('SELECT `name_de` FROM `gw2_items` WHERE `id` = ?', array($recipe['ing_id_'.$i]));
			$recipe_box .=
				'| material'.$i.' = '.$ing[0]['name_de'].$n.
				($recipe['ing_count_'.$i] > 1 ? '| menge'.$i.' = '.$recipe['ing_count_'.$i].$n : '');
		}
	}

	// add a flag if an item was removed - currently explorer type
	if(strpos($item['name_de'], 'des Explorators') !== false || strpos($item['name_de'], ' des Wanderers') !== false || strpos($item['name_de'], 'Plündernd') !== false){
		$recipe_box .=
			'| entfernt = ja'.$n;
	}

	// parse extra params
	foreach($params as $k => $v){
		$recipe_box .=
			'| '.$k.' = '.$v.$n;
	}

	$recipe_box .=
		'}}'.$n;

	return $recipe_box;
}

/**
 * basically a copy of the recipe box because of lazyness
 *
 * @param array $item
 * @param array $params
 *
 * @return string
 */
function wiki_equip_de($item, $params = array()){
	global $rarity, $attributes, $infusions;

	$t = strtolower($item['type']);

	$n = "\n";
	$equip_box =
		'{{Ausrüstungswerte'.$n.
		'| id = '.$item['id'].$n.
		'| stufe = '.$item['level'].$n.
		'| seltenheit = '.str_replace($rarity['en'], $rarity['de'], $item['rarity']).$n.
		(intval($item['value']) > 0 && !in_array('NoSell', $item['data_de']['flags']) ? '| händlerwert = '.$item['value'].$n : '').
		($item['type'] === 'Weapon' ? '| waffenstärke = '.$item['data_de']['weapon']['min_power'].' - '.$item['data_de']['weapon']['max_power'].$n : '').
		(isset($item['data_de'][$t]['defense']) && $item['data_de'][$t]['defense'] > 0 ? '| verteidigung = '.$item['data_de'][$t]['defense'].$n : '');


	// parse attributes
	if(isset($item['data_de'][$t]['infix_upgrade'])){
		$i = 1;
		if(isset($item['data_de'][$t]['infix_upgrade']['attributes'])){
			foreach($item['data_de'][$t]['infix_upgrade']['attributes'] as $attr){
				$equip_box .=
					'| attribut'.$i.' = '.str_replace($attributes['api'], $attributes['de'], $attr['attribute']).$n.
					'| attribut'.$i.'-wert = '.$attr['modifier'].$n;
				$i++;
			}
		}

		//check for a buff value (still unsure wether this can be array or not)
		if(isset($item['data_de'][$t]['infix_upgrade']['buff'])){
			$equip_box .=
				'| attribut'.$i.' = '.trim(preg_replace("#[\d\+%]#U",'',$item['data_de'][$t]['infix_upgrade']['buff']['description'])).$n.
				'| attribut'.$i.'-wert = '.preg_replace("#[^\d]#U",'',$item['data_de'][$t]['infix_upgrade']['buff']['description']).$n;
		}
	}

	// check for an upgrade
	if(isset($item['data_de'][$t]['suffix_item_id'])){
		$equip_box .=
			'| aufwertung = ';
		if(!empty($item['data_de'][$t]['suffix_item_id'])){
			$suffix_name = sql_query('SELECT `name_de` FROM `gw2_items` WHERE `id` = ?', array($item['data_de'][$t]['suffix_item_id']));
			$equip_box .= (is_array($suffix_name) && isset($suffix_name[0]['name_de']) ? $suffix_name[0]['name_de'] : '');
		}
		$equip_box .= $n;
	}

	// check for infusion slots
	if(isset($item['data_de'][$t]['infusion_slots'][0])){
		$equip_box .=
			'| infusion = '.str_replace($infusions['api'], $infusions['de'], $item['data_de'][$t]['infusion_slots'][0]['flags'][0]).$n;
		// is there a second slot? (subject to change if more infusion slots are added in futute)
		if(isset($item['data_de'][$t]['infusion_slots'][1])){
			$equip_box .=
				'| infusion2 = '.str_replace($infusions['api'], $infusions['de'], $item['data_de'][$t]['infusion_slots'][1]['flags'][0]).$n;
		}
	}

	// binding
	switch(true){
		case in_array('AccountBound', $item['data_de']['flags']) && in_array('SoulBindOnUse', $item['data_de']['flags']): $equip_box .= '| gebunden = accbenutzung'.$n; break;
		case in_array('AccountBound', $item['data_de']['flags']) && !in_array('SoulBindOnUse', $item['data_de']['flags']): $equip_box .= '| gebunden = account'.$n; break;
		case in_array('SoulBindOnUse', $item['data_de']['flags']) && !in_array('AccountBound', $item['data_de']['flags']): $equip_box .= '| gebunden = benutzung'.$n; break;
		case in_array('SoulbindOnAcquire', $item['data_de']['flags']): $equip_box .= '| gebunden = seele'.$n; break;
	}

	// add a flag if an item was removed - currently explorer type
	if(strpos($item['name_de'], 'des Explorators') !== false || strpos($item['name_de'], ' des Wanderers') !== false || strpos($item['name_de'], 'Plündernd') !== false){
		$equip_box .=
			'| entfernt = ja'.$n;
	}

	// parse extra params
	foreach($params as $k => $v){
		$equip_box .=
			'| '.$k.' = '.$v.$n;
	}

	$equip_box .=
		'}}'.$n;

	return $equip_box;
}



function wiki_infobox_item_fr($item, $type = '', $params = array()){
	global $rarity, $attributes;
	$n = "\n";
	$infobox_item =
		'{{Infobox objet'.$n.
		'| id = '.$item['id'].$n.
		'| type = '.$type.$n.
		'| rareté = '.str_replace($rarity['en'], $rarity['fr'], $item['rarity']).$n.
		(intval($item['level']) > 0 ? '| niveau = '.$item['level'].$n : '').
		(intval($item['value']) > 0 && !in_array('NoSell', $item['data_fr']['flags']) ? '| valeur = '.$item['value'].$n : '').
		(!empty($item['desc_fr']) ? '| description = '.preg_replace('/('.implode('|',$attributes['fr']).')/Uis','[[$1]]',strip_tags($item['desc_fr'])).$n : '');



	foreach($params as $k => $v){
		$infobox_item .= '| '.$k.' = '.$v.$n;
	}

	$infobox_item .= '}}'.$n;

	return $infobox_item;
}


function wiki_recipe_fr($recipe, $name){
	$n = "\n";

	// get the name of the recipe item if learned from item
	if((bool)$recipe['from_item'] === true){
		$unlock = sql_query('SELECT `name_fr` FROM `gw2_items` WHERE `type` = \'Consumable\' AND `subtype` = \'Unlock\' AND `unlock_id` = ?', array($recipe['recipe_id']));
		$unlock = $unlock[0]['name_fr'];
	}
	else{
		$unlock = 'Automatique';
	}

	$recipe_box = '==Recette=='.$n.
		'{{Recette'.$n.
		'| acquisition = '.$unlock.$n.
		'| difficulté = '.$recipe['rating'].$n.
		'| discipline = '.$n;

	;


	for($i=1; $i<5; $i++){
		if(intval($recipe['ing_count_'.$i]) > 0){
			$ing = sql_query('SELECT `name_fr` FROM `gw2_items` WHERE `id` = ?', array($recipe['ing_id_'.$i]));
			$recipe_box .=
				'| mat'.$i.' = '.$ing[0]['name_fr'].$n.
				'| qté'.$i.' = '.$recipe['ing_count_'.$i].$n;
		}
	}

#	foreach($disciplines['fr'] as $dis){
#		$recipe_box .= '| '.mb_strtolower($dis).' = '.$recipe['rating'].$n;
#	}

	$recipe_box .= '}}'.$n;

	return $recipe_box;
}


?>