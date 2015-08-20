<?php
/**
 * MySQLi Wrapper class
 *
 * @filesource sql.class.php
 * @version    0.3.0
 * @link       https://github.com/codemasher/gw2-database/blob/master/classes/sql.class.php
 * @created    27.12.13
 *
 * @author     Smiley <smiley@chillerlan.net>
 * @copyright  Copyright (c) 2014 Smiley <smiley@chillerlan.net>
 * @license    http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */

/**
 * Class SQL
 */
class SQL{

	/**
	 * @var string
	 */
	private $host = null;

	/**
	 * @var string
	 */
	private $user = null;

	/**
	 * @var string
	 */
	private $password = null;

	/**
	 * @var string
	 */
	private $database = null;

	/**
	 * @var int
	 */
	private $port = null;

	/**
	 * @var string
	 */
	private $socket = null;

	/**
	 * Connection timeout
	 * @var int
	 * @link http://php.net/manual/mysqli.options.php
	 */
	public $timeout = 5;

	/**
	 * Connection character set
	 * @var string
	 * @link https://mathiasbynens.be/notes/mysql-utf8mb4
	 */
	public $charset = 'utf8mb4';

	/**
	 * @var array
	 */
	public $affected_rows = [];

	/**
	 * List of errors that occured during operation
	 * @var array
	 * @link http://php.net/manual/mysqli-stmt.error-list.php
	 */
	public $errors = [];

	/**
	 * the insert id(s) of the last operation
	 * @var array
	 */
	public $insert_ids = [];

	/**
	 * Use a secure connection?
	 * @var bool
	 */
	private $use_ssl = false;

	/**
	 * The path name to the certificate authority file.
	 * @var string
	 * @link http://php.net/manual/mysqli.ssl-set.php
	 * @link http://curl.haxx.se/ca/cacert.pem
	 */
	private $ssl_ca = null;

	/**
	 * The pathname to a directory that contains trusted SSL CA certificates in PEM format.
	 * @var string
	 */
	private $ssl_capath = null;

	/**
	 * The path name to the certificate file.
	 * @var string
	 */
	private $ssl_cert = null;

	/**
	 * A list of allowable ciphers to use for SSL encryption.
	 * @var string
	 */
	private $ssl_cipher = null;

	/**
	 * The path name to the key file.
	 * @var string
	 */
	private $ssl_key = null;

	/**
	 * @var resource
	 */
	private $mysqli;

	/**
	 * Constructor
	 */
	public function __construct(){
		// init mysqli
		$this->mysqli = mysqli_init();
	}

	/**
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param string $database
	 * @param string $port
	 * @param string $socket
	 *
	 * @return $this
	 */
	public function set_credentials($host = null, $user = null, $password = null, $database = null, $port = null, $socket = null){
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->database = $database;
		$this->port = $port;
		$this->socket = $socket;

		return $this;
	}

	/**
	 * @param bool   $use_ssl
	 * @param string $ssl_key
	 * @param string $ssl_cert
	 * @param string $ssl_ca
	 * @param string $ssl_capath
	 * @param string $ssl_cipher
	 *
	 * @return $this
	 */
	public function set_ssl($use_ssl = false, $ssl_key = null, $ssl_cert = null, $ssl_ca = null, $ssl_capath = null, $ssl_cipher = null){
		$this->use_ssl = $use_ssl;
		$this->ssl_key = $ssl_key;
		$this->ssl_cert = $ssl_cert;
		$this->ssl_ca = $ssl_ca;
		$this->ssl_capath = $ssl_capath;
		$this->ssl_cipher = $ssl_cipher;

		return $this;
	}

	/**
	 * Connect
	 *
	 * Establishes a connection to a MySQL database and forces UTF-8 character set
	 *
	 * @return resource|bool
	 */
	public function connect(){
		if($this->mysqli->connect_errno){
			return $this->mysqli;
		}

		// set timeout
		if(!$this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, $this->timeout)){
			exit('Could not set database timeout.');
		}

		// using ssl?
		if($this->use_ssl){
			$this->mysqli->ssl_set($this->ssl_key, $this->ssl_cert, $this->ssl_ca, $this->ssl_capath, $this->ssl_cipher);
		}

		// connect
		if(!$this->mysqli->real_connect($this->host, $this->user, $this->password, $this->database, $this->port, $this->socket)){
			// don't ever expose SQL errors to the public.
			// this is enough info for the user
			exit('Could not connect to the database.');
		}

		// try to set the character set
		if(!$this->mysqli->set_charset($this->charset)){
			exit('Could not set database charset.');
		}

		// everything fine
		return $this->mysqli || false;
	}

	/**
	 * Sanitizer
	 *
	 * recursively escapes string values, optional <i>htmlspecialchars()</i>
	 *
	 * @param array|string $data         <i>array</i> or <i>string</i> to escape
	 * @param bool         $specialchars if <i>true</i>, it performs a <i>htmlspecialchars()</i> on each value given
	 *
	 * @return array|string <i>array</i> or <i>string</i>. escaped. obviously.
	 */
	public function escape($data, $specialchars = false){
		// recursive array walk
		if(is_array($data)){
			foreach($data as $key => $value){
				$data[$key] = $this->escape($value, $specialchars);
			}
		}
		else{
			if($specialchars === true){
				$data = htmlspecialchars($data, null, 'UTF-8', false);
			}
			$data = $this->mysqli->real_escape_string($data);
		}

		return $data;
	}

	/**
	 * Simple Query
	 *
	 * <b>Basic MySQLi query for non prepared statements</b>
	 *
	 * <p> There is no escaping in here, so make sure, your SQL is clean/escaped.
	 * Also, your SQL should <b>NEVER</b> contain user input, use prepared statements in this case.</p>
	 *
	 * <p> If the query was successful it returns either an <i>array</i> of results or <i>true</i>
	 * if it was a void query.  On errors, a <i>false</i> will be returned, obviously.</p>
	 *
	 * @param string $sql   The SQL statement
	 * @param bool   $assoc [optional] If <i>true</i>, the fields are named with the respective column names, otherwise numbered
	 * @param string $index [optional] an index column to assingn as the result's keys
	 *
	 * @return array|bool <i>array</i> with results, <i>true</i> on void query success, otherwise <i>false</i>.
	 */
	public function simple_query($sql, $assoc = true, $index = ''){
		if($result = $this->mysqli->query($sql)){
			if(!empty($this->mysqli->error_list)){
				$this->errors = [
					'error' => $this->mysqli->error_list,
					'sql' => $sql,
					'assoc' => $assoc,
					'index' => $index,
				];
			}
			$this->affected_rows = $this->mysqli->affected_rows;
			$this->insert_ids = !empty($this->mysqli->insert_id) ? $this->mysqli->insert_id : false;

			// ok, we have a result with one or more rows, loop out the rows and output as array
			if(!is_bool($result)){
				$out = [];
				if($result->num_rows > 0){
					while($row = $assoc === true ? $result->fetch_assoc() : $result->fetch_row()){
						if($assoc === true && !empty($index) && isset($row[$index])){
							$out[$row[$index]] = $row;
						}
						else{
							$out[] = $row;
						}
					}
				}
				$result->free();

				return $out;
			}

			// void result
			return true;
		}

		return false;
	}

	/**
	 * get_references
	 *
	 * <b>Helper function for the prepared statements wrappers</b>
	 *
	 * copies an array to an array of referenced values
	 *
	 * @param array $array source
	 *
	 * @return array $references destination
	 */
	private function get_references(array $array){
		$references = [];
		foreach($array as $key => &$value){
			$references[$key] = &$value;
		}

		return $references;
	}

	/**
	 * Prepared Query
	 *
	 * <b>MySQLi prepared statements wrapper</b>
	 *
	 * Does everything for you: prepares the statement and fetches the results as an <i>array</i>
	 * just pass a query along with values and you're done. Not meant for multi-inserts.
	 *
	 * @link https://www.owasp.org/index.php/PHP_Security_Cheat_Sheet#MySQLi_Prepared_Statements_Wrapper
	 *
	 * @param string $sql    The SQL statement to prepare
	 * @param array  $values [optional] the value for each "?" in the statement - in the respective order, of course
	 * @param string $types  [optional] the types for each column: <b>b</b>lob, <b>d</b>ouble (float), <b>i</b>nteger,
	 *                       <b>s</b>tring, see http://php.net/manual/mysqli-stmt.bind-param.php
	 * @param bool   $assoc  [optional] If <i>true</i>, the fields are named with the respective column names, otherwise numbered
	 * @param string $index  [optional] an index column to assingn as the result's keys
	 *
	 * @return array|bool Array with results, true on void query success, otherwise false
	 */
	public function prepared_query($sql, array $values = [], $types = '', $assoc = true, $index = ''){
		// create prepared statement
		$stmt = $this->mysqli->stmt_init();
		$reflection = new ReflectionClass('mysqli_stmt');
		$method = $reflection->getMethod('prepare');
		if($method->invokeArgs($stmt, [$sql])){
			$cols = count($values);
			if($cols > 0){
				// if no types given just assume that all params are strings, works well on MySQL and SQLite
				$types = preg_match('/^[bdis]{'.$cols.'}$/', $types) ? $types : str_repeat('s', $cols);

				// copy values to reference for bind_param's sake
				$refs = $this->get_references($values);

				// put the types on top of the references array
				array_unshift($refs, $types);

				$method = $reflection->getMethod('bind_param');
				$method->invokeArgs($stmt, $refs);
			}

			$stmt->execute();
			$metadata = $stmt->result_metadata();
			$this->affected_rows = $stmt->affected_rows;
			$this->insert_ids = !empty($stmt->insert_id) ? $stmt->insert_id : false;
			if(!empty($stmt->error_list)){
				$this->errors = [
					'error' => $stmt->error_list,
					'sql' => $sql,
					'values' => $values,
					'types' => $types,
					'assoc' => $assoc,
					'index' => $index,
				];
			}

			// void result
			if(!$metadata){
				return true;
			}

			// fetch all results as a 2D array
			$out = [];
			$fields = [];
			$count = 0;
			while($field = $metadata->fetch_field()){
				if($assoc === true){
					$fields[] = &$out[$field->name];
				}
				else{
					$fields[] = &$out[$count];
				}
				$count++;
			}

			$method = $reflection->getMethod('bind_result');
			$method->invokeArgs($stmt, $fields);

			$output = [];
			$count = 0;
			while($stmt->fetch()){
				foreach($out as $k => $v){
					// if $index is set, assign the given column as key
					if($assoc === true && !empty($index) && isset($out[$index])){
						$output[$out[$index]][$k] = $v;
					}
					else{
						$output[$count][$k] = $v;
					}
				}
				$count++;
			}

			// KTHXBYE!
			$stmt->free_result();
			$stmt->close();

			return ($count === 0) ? true : $output;
		}

		return false;
	}

	/**
	 * Prepared multi line insert
	 *
	 * <b>Prepared statement multi insert/update</b>
	 *
	 * @param string $sql    The SQL statement to prepare
	 * @param array  $values a multidimensional array with the values, each row represents one line to insert.
	 * @param string $types  [optional] the types of the values in their respective order,
	 *                       see http://php.net/manual/mysqli-stmt.bind-param.php
	 *
	 * @return bool true query success, otherwise false
	 */
	public function multi_insert($sql, $values, $types = ''){
		$affected_rows = [];
		$errors = [];
		$insert_ids = [];
		// check if the array is multidimensional
		if(is_array($values) && count($values) > 0 && is_array($values[0]) && count($values[0]) > 0){
			$stmt = $this->mysqli->stmt_init();
			$reflection = new ReflectionClass('mysqli_stmt');
			$method = $reflection->getMethod('prepare');
			if($method->invokeArgs($stmt, [$sql])){
				$method = $reflection->getMethod('bind_param');
				$cols = count($values[0]);
				$types = preg_match('/^[bdis]{'.$cols.'}$/', $types) ? $types : str_repeat('s', $cols);
				foreach($values as $i => $row){
					$refs = $this->get_references($row);
					array_unshift($refs, $types);
					$method->invokeArgs($stmt, $refs);
					$stmt->execute();
					if(!empty($stmt->error_list)){
						$errors[$i] = [
							'error' => $stmt->error_list,
							'sql' => $sql,
							'values' => $row,
							'types' => $types,
							'loop_id' => $i,
						];
					}
					$affected_rows[$i] = $stmt->affected_rows;
					$insert_ids[$i] = !empty($stmt->insert_id) ? $stmt->insert_id : false;
				}
			}
			$this->affected_rows = $affected_rows;
			$this->errors = $errors;
			$this->insert_ids = $insert_ids;
			$stmt->close();

			return true;
		}

		return false;
	}

}
