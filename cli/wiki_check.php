<?php
/**
 * wiki_check.php
 * created: 14.09.13
 *
 * command line!
 * c:\amp\php\php.exe -c c:\amp\apache\conf\php.ini wiki_check.php
 */

error_reporting(E_ALL);
set_time_limit(0);
$limit = 20;

require_once '../inc/mysqli.inc.php';
require_once '../inc/utils.inc.php';


$query = sql_query('SELECT `id`, `name_de`, `name_en`, `name_es`, `name_fr` FROM `gw2_items` WHERE `updated` = 1 AND `wiki_checked` = 0 ORDER BY `gw2_items`.`id`');//
$count = count($query);
if(is_array($query) && $count > 0){
	if($stmt = mysqli_prepare($db, 'UPDATE `gw2_items` SET `wikipage_de` = ?, `wikipage_en` = ?, `wikipage_es` = ?, `wikipage_fr` = ?, `wiki_checked` = ? WHERE `id` = ?')){
		/** @noinspection PhpUndefinedVariableInspection */
		mysqli_stmt_bind_param($stmt, 'iiiiii', $wikipage_de, $wikipage_en, $wikipage_es, $wikipage_fr, $wiki_checked, $id);
		foreach($query as $key => $row){
			// TODO: strip pre- and suffixes of weapons and armor
			$items['id'][] = $row['id'];
			$items['de'][] = $row['name_de'];
			$items['en'][] = $row['name_en'];
			$items['es'][] = str_replace('  ', ' ', $row['name_es']);
			$items['fr'][] = str_replace('  ', ' ', $row['name_fr']);
			$items['de_url'][] = rawurlencode($row['name_de']);
			$items['en_url'][] = rawurlencode($row['name_en']);
			$items['es_url'][] = rawurlencode(str_replace('  ', ' ', $row['name_es']));
			$items['fr_url'][] = rawurlencode(str_replace('  ', ' ', $row['name_fr']));
			$items['de_check'][] = 0;
			$items['en_check'][] = 0;
			$items['es_check'][] = 0;
			$items['fr_check'][] = 0;

			if(count($items['id']) === $limit || ($key >= $count-1 && count($items['id']) === $count%$limit)){
				$items = wiki_check($items);
				foreach($items['id'] as $k => $i){
					$wikipage_de = $items['de_check'][$k];
					$wikipage_en = $items['en_check'][$k];
					$wikipage_es = $items['es_check'][$k];
					$wikipage_fr = $items['fr_check'][$k];
					$wiki_checked = $items['de_check'][$k] === 0 && $items['en_check'][$k] === 0 && $items['es_check'][$k] === 0 && $items['fr_check'][$k] === 0 ? 0 : 1; // just assume it's not checked if there's no page
					$id = $i;
					mysqli_stmt_execute($stmt);
					echo 'checked item: '.$i.' - de:'.str_pad($items['de_check'][$k], 7, ' ').' - en:'.str_pad($items['en_check'][$k], 7, ' ').' - es:'.str_pad($items['es_check'][$k], 7, ' ').' - fr:'.str_pad($items['fr_check'][$k], 7, ' ').' ('.$items['en'][$k].')'."\n";
				}
				$items = array();
			}
		}
	}
	mysqli_stmt_close($stmt);
}



?>