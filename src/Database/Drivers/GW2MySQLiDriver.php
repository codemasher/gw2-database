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
	 * @var \ReflectionMethod
	 */
	protected $reflectionMethod;

	/**
	 * @var \mysqli_stmt
	 */
	protected $mysqli_stmt;

	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * @var string
	 */
	protected $sql;

	/**
	 * Prepared multi line insert/update with callback
	 *
	 * @param string         $sql      The SQL statement to prepare
	 * @param array          $data     an array with the (raw) data to insert, each row represents one line to insert.
	 * @param callable|array $callback a callback that processes the values for each row.
	 *
	 * @return bool true query success, otherwise false
	 * @throws \chillerlan\Database\DBException
	 */
	public function multi_callback($sql, array $data, $callback){
		$this->sql = $sql;

		if(!is_callable($callback) || (is_array($callback) && (!isset($callback[0]) || !is_object($callback[0])))){
			throw new DBException('invalid callback');
		}

		$this->callback = $callback;

		if(count($data) < 1){
			throw new DBException('invalid data');
		}

		$this->mysqli_stmt = $this->db->stmt_init();

		if(!$this->mysqli_stmt->prepare($this->sql)){
			throw new DBException('could not prepare statement ('.$this->sql.')');
		}

		$this->reflectionMethod = (new ReflectionClass('mysqli_stmt'))->getMethod('bind_param');
		array_map([$this, 'insertPreparedRow'], $data);
		$this->mysqli_stmt->close();

		return true;
	}

	/**
	 * @param mixed $row
	 */
	protected function insertPreparedRow($row){
		$references = [];

		if($row = call_user_func_array($this->callback, [$row])){

			foreach($row as &$field){
				$references[] = &$field;
			}

			array_unshift($references, $this->getTypes($references));
			$this->reflectionMethod->invokeArgs($this->mysqli_stmt, $references);
			$this->mysqli_stmt->execute();
/*
			$this->addStats([
				'affected_rows' => $this->mysqli_stmt->affected_rows,
				'error'         => $this->mysqli_stmt->error_list,
				'insert_id'     => $this->mysqli_stmt->insert_id,
				'sql'           => $this->sql,
				'values'        => $row,
				'types'         => $types,
			]);
*/

		}


	}
}
