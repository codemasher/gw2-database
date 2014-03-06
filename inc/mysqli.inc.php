<?php
/**
 * mysqli.inc.php
 * created: 07.07.13
 */

$db = mysqli_init();

// connect to the db
if(!mysqli_real_connect($db, $mysql['server'], $mysql['user'], $mysql['password'], $mysql['dbname'])){
	// note: you sould not expose sql errors to the public on a production system. never. ever.
	exit('Could not connect to the database. ');// .mysqli_connect_errno().' - '.mysqli_connect_error()
}

// set the connection dataset
if(!mysqli_set_charset($db, 'utf8')){
	exit('Error loading character set utf8. ');// .mysqli_error($db)
}

/**
 * <b>Basic MySQLi query for non prepared statements</b>
 *
 * <p> There is no escaping in here, so make sure, your SQL is clean/escaped.
 * Also, your SQL should <b>NEVER</b> contain user input, use prepared statements in this case.</p>
 *
 * <p> If the query was successful it returns either an <i>array</i> of results or <i>true</i>
 * if it was a void query.  On errors, a <i>false</i> will be returned, obviously.</p>
 *
 * <p> Note about the function name:
 * Since i didn't want to override <i>mysqli::query</i>, i chose just Q instead of some_random_function_name.
 * Q is short and comes in handy because it'll be used quite excessive anyway, so just do <i>$db->Q($sql)</i>.</p>
 *
 * @param string $sql   The SQL statement
 * @param bool   $assoc [optional]<p>
 *                      If <i>true</i>, the fields are named with the respective column names, otherwise numbered</p>
 *
 * @return array|bool <i>array</i> with results, <i>true</i> on void query success, otherwise <i>false</i>.
 */
function sql_query($sql, $assoc = true){
	global $db;
	if($result = mysqli_query($db, $sql)){
		// ok, we have a result with one or more rows, loop out the rows and output as array
		if(!is_bool($result)){
			$out = array();
			if(mysqli_num_rows($result) > 0){
				while($r = ($assoc === true) ? mysqli_fetch_assoc($result) : mysqli_fetch_row($result)){
					$out[] = $r;
				}

			}
			mysqli_free_result($result);
			return $out;
		}
		// void result
		return true;

	}
	// catch possible errors over here
	return false;
}

/**
 * <b>MySQLi prepared statements wrapper</b>
 *
 * <p> Does everything for you: prepares the statement and fetches the results as an <i>array</i> -
 * just pass a query along with values and you're done. Not meant for multi-inserts.</p>
 *
 * @link https://www.owasp.org/index.php/PHP_Security_Cheat_Sheet#MySQLi_Prepared_Statements_Wrapper
 *
 * @param string $sql    The SQL statement to prepare
 * @param array  $values [optional]<p>
 *                       the value for each "?" in the statement - in the respective order, of course</p>
 * @param string $types  [optional]<p>
 *                       the types for each column: <b>b</b>lob, <b>d</b>ouble (float), <b>i</b>nteger, <b>s</b>tring,
 *                       see {@link http://php.net/manual/mysqli-stmt.bind-param.php mysqli_stmt::bind_param}</p>
 * @param bool   $assoc  [optional]<p>
 *                       If <i>true</i>, the fields are named with the respective column names, otherwise numbered</p>
 *
 * @return array|bool Array with results, true on void query success, otherwise false
 */
function sql_prepared_query($sql, $values = array(), $types = '', $assoc = true){
	global $db;
	// catch possible errors
	if($stmt = mysqli_prepare($db, $sql)){
		$cols = count($values);
		if($cols > 0){
			// if no types given just assume that all params are strings, works well on MySQL and SQLite
			$types = preg_match('/^[bdis]{'.$cols.'}$/', $types) ? $types : str_repeat('s', $cols);

			// copy values to reference for bind_param's sake
			$references = array();
			foreach($values as $k => &$v){
				$references[$k] = &$v;
			}

			// put the types on top of the references array
			array_unshift($references, $types);

			// calling mysqli_stmt_bind_param() the objective way
			call_user_func_array(array($stmt, 'bind_param'), $references);
		}

		mysqli_stmt_execute($stmt);
		$metadata = mysqli_stmt_result_metadata($stmt);

		// void result
		if(!$metadata){
			return true;
		}

		// fetch all results as a 2D array
		$out = array();
		$fields = array();
		$count = 0;
		while($field = mysqli_fetch_field($metadata)){
			if($assoc === true){
				$fields[] = & $out[$field->name];
			}
			else{
				$fields[] = & $out[$count];
			}
			$count++;
		}

		call_user_func_array(array($stmt, 'bind_result'), $fields);

		$output = array();
		$count = 0;
		while(mysqli_stmt_fetch($stmt)){
			foreach($out as $k => $v){
				$output[$count][$k] = $v;
			}
			$count++;
		}

		mysqli_stmt_free_result($stmt);
		mysqli_stmt_close($stmt);
		return ($count === 0) ? true : $output;
	}
	return false;
}

/**
 * <b>Prepared statement multi insert/update</b>
 *
 * @param        $sql
 * @param        $values
 * @param string $types
 *
 * @return bool
 */
function sql_multi_row_insert($sql, $values, $types = ''){
	global $db;
	// check if the array is multidimensional
	if(is_array($values) && count($values) > 0 && is_array($values[0]) && count($values[0]) > 0){
		if($stmt = mysqli_prepare($db, $sql)){
			$cols = count($values[0]);
			$types = preg_match('/^[bdis]{'.$cols.'}$/', $types) ? $types : str_repeat('s', $cols);
			foreach($values as $row){
				$references = array();
				foreach($row as &$val){
					$references[] = &$val;
				}
				array_unshift($references, $types);
				call_user_func_array(Array($stmt, 'bind_param'), $references);
				mysqli_stmt_execute($stmt);
			}
		}
		mysqli_stmt_close($stmt);
		return true;
	}
	return false;
}


?>
