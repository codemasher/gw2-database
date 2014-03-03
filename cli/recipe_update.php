<?php
/**
 * recipe_update.php
 * created: 15.09.13
 *
 * command line!
 * c:\amp\php\php.exe -c c:\amp\apache\conf\php.ini recipe_update.php
 */

error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');
set_time_limit(0);

require_once '../inc/mysqli.inc.php';
require_once '../inc/variables.inc.php';
require_once '../inc/request.inc.php';
require_once '../inc/utils.inc.php';

$n = "\n";

// first of all refresh the recipe IDs
$data = gw2_api_request('recipes.json');
if(is_array($data) && isset($data['recipes'])){
	$values = array();
	$time = time();
	foreach($data['recipes'] as $id){
		$values[] = array($id, $time);
	}
	sql_multi_row_insert('INSERT IGNORE INTO `gw2_recipes` (`recipe_id`, `date_added`) VALUES (?, ?)', $values, 'ii');
	echo 'refresh done.'.$n.count($data['recipes']).' items in recipes.json.'.$n;
}

$q = sql_query('SELECT `recipe_id` FROM `gw2_recipes` WHERE `updated` = 0 ORDER BY `gw2_recipes`.`recipe_id`');
if(is_array($q) && count($q) > 0){
	$err = array();
	$sql = 'UPDATE `gw2_recipes` SET `output_id` = ?, `output_count` = ?, `disciplines`= ?, `rating` = ?, `type` = ?, `from_item` = ?, `ing_id_1` = ?, `ing_count_1` = ?, `ing_id_2` = ?, `ing_count_2` = ?, `ing_id_3` = ?, `ing_count_3` = ?, `ing_id_4` = ?, `ing_count_4` = ?, `data` = ?, `updated` = ?, `update_time` = ? WHERE `recipe_id` = ?';

	// i don't use the multiline insert over here as it would blow up the system memory
	// also this way we insert each line as soon as we get the data from the API
	if($stmt = mysqli_prepare($db, $sql)){
		/** @noinspection PhpUndefinedVariableInspection */
		mysqli_stmt_bind_param($stmt, 'iiiisiiiiiiiiisiii', $output_id, $output_count, $disciplines, $rating, $type, $from_item, $ing_id_1, $ing_count_1, $ing_id_2, $ing_count_2, $ing_id_3, $ing_count_3, $ing_id_4, $ing_count_4, $data, $updated, $update_time, $recipe_id);
		foreach($q as $r){
			$starttime = microtime(true);
			$apidata = gw2_api_request('recipe_details.json?recipe_id='.$r['recipe_id']);
			$output_id = intval($apidata['output_item_id']);
			$output_count = intval($apidata['output_item_count']);
			$disciplines = is_array($apidata['disciplines']) && count($apidata['disciplines']) > 0 ? set_bitflag($apidata['disciplines']) : 0;
			$rating = intval($apidata['min_rating']);
			$type = $apidata['type'];
			$from_item = is_array($apidata['flags']) && in_array('LearnedFromItem',$apidata['flags']) ? 1 : 0;
			$ing_id_1 = isset($apidata['ingredients'][0]) ? intval($apidata['ingredients'][0]['item_id']) : 0;
			$ing_count_1 = isset($apidata['ingredients'][0]) ? intval($apidata['ingredients'][0]['count']) : 0;
			$ing_id_2 = isset($apidata['ingredients'][1]) ? intval($apidata['ingredients'][1]['item_id']) : 0;
			$ing_count_2 = isset($apidata['ingredients'][1]) ? intval($apidata['ingredients'][1]['count']) : 0;
			$ing_id_3 = isset($apidata['ingredients'][2]) ? intval($apidata['ingredients'][2]['item_id']) : 0;
			$ing_count_3 = isset($apidata['ingredients'][2]) ? intval($apidata['ingredients'][2]['count']) : 0;
			$ing_id_4 = isset($apidata['ingredients'][3]) ? intval($apidata['ingredients'][3]['item_id']) : 0;
			$ing_count_4 = isset($apidata['ingredients'][3]) ? intval($apidata['ingredients'][3]['count']) : 0;
			$data = json_encode($apidata);
			$updated = 1;
			$update_time = time();

			$recipe_id = $r['recipe_id'];
			mysqli_stmt_execute($stmt);
			echo 'Updated recipe #'.$r['recipe_id'].' ('.round((microtime(true) - $starttime),3).'s)'.$n;
		}
	}

}
?>