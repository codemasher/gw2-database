<?php
/**
 * Class GW2MySQLiDriver
 *
 * @filesource   GW2MySQLiDriver.php
 * @created      22.02.2016
 * @package      chillerlan\GW2DB\Database\Drivers
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Database\Drivers;

use chillerlan\Database\DBException;
use chillerlan\Database\Drivers\MySQLi\MySQLiDriver;
use ReflectionClass;

/**
 *
 */
class GW2MySQLiDriver extends MySQLiDriver{

	/**
	 * Prepared multi line insert with callback
	 *
	 * Prepared statement multi insert/update
	 *
	 * @param string   $sql      The SQL statement to prepare
	 * @param array    $data     an array with the (raw) data to insert, each row represents one line to insert.
	 * @param callable $callback a callback that processes the values for each row.
	 *
	 * @return bool true query success, otherwise false
	 * @throws \chillerlan\Database\DBException
	 */
	public function multi_callback($sql, array $data, callable $callback){

		if(!is_array($data) || count($data) < 1){
			throw new DBException('invalid data');
		}

		$stmt = $this->db->stmt_init();

		if(!$stmt->prepare($sql)){
			throw new DBException('could not prepare statement ('.$sql.')');
		}

		$bind_param = (new ReflectionClass('mysqli_stmt'))->getMethod('bind_param');

		foreach($data as $row){
			$references = [];

			foreach(call_user_func($callback, $row) as &$field){
				$references[] = &$field;
			}

			$types = $this->getTypes($references);
			array_unshift($references, $types);
			$bind_param->invokeArgs($stmt, $references);
			$stmt->execute();

			$this->addStats([
				'affected_rows' => $stmt->affected_rows,
				'error'         => $stmt->error_list,
				'insert_id'     => $stmt->insert_id,
				'sql'           => $sql,
				'values'        => $data,
				'types'         => $types,
			]);
		}

		$stmt->close();

		return true;
	}

}
