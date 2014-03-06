<?php
/**
 * attributes.inc.php
 * created 04.03.14
 */

/**
 * @param $infix_upgrade
 *
 * @return array -> array(COMBINATION_NAME, array(ATTRIBUTES))
 */
function attribute_combination($infix_upgrade){
	global $attribute_map;
	$att = array();
	$mod = array();

	foreach($infix_upgrade['attributes'] as $a){
		$att[] = $a['attribute'];
		$mod[] = $a['modifier'];
	}

	array_multisort($mod, SORT_DESC, SORT_NUMERIC, $att, SORT_STRING);

	// Condition Duration is only available as major attribute, so put it on top
	if(isset($infix_upgrade['buff']['skill_id']) && (int)$infix_upgrade['buff']['skill_id'] === 16631){
		array_unshift($att, 'ConditionDuration');
	}

	// Boon duration is only a minor attribute, so add it to the end
	if(isset($infix_upgrade['buff']['skill_id']) && (int)$infix_upgrade['buff']['skill_id'] === 16517){
		$att[] = 'BoonDuration';
	}

	$count = count($att);

	if($count === 1){
		// SELECT COUNT(*) AS `count`, `attr1`, `attr_name` FROM `gw2_items` WHERE `attr1` != '' AND `attr2` = '' AND `attr3` = '' GROUP BY `attr1`
		return array(array_search($att[0], $attribute_map['single']), $att);

	}

	if($count === 2){
		// SELECT COUNT(*) AS `count`, `attr1`, `attr2`, `attr_name` FROM `gw2_items` WHERE `attr1` != '' AND `attr2` != '' AND `attr3` = '' GROUP BY `attr1`, `attr2`
		foreach($attribute_map['double'] as $name => $map){
			$arr = array_diff_assoc($att, $map);
			if(empty($arr)){
				return array($name, $att);
			}
		}
	}

	if($count === 3){
		// SELECT COUNT(*) AS `count`, `attr1`, `attr2`, `attr3`, `attr_name` FROM `gw2_items` WHERE `attr1` != '' AND `attr2` != '' AND `attr3` != '' GROUP BY `attr1`, `attr2`, `attr3`
		foreach($attribute_map['triple'] as $name => $map){
			$arr = array_diff_assoc($att, $map);
			if(empty($arr)){
				return array($name, $att);
			}
		}
	}

	if($count === 7){
		// celestial is currently the only combination of 7 attributes
		return array('celestial', array());
	}

	return array('', array());
}

?>