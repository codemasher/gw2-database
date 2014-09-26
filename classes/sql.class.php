<?php
/**
 * MySQLi Wrapper class
 *
 * @filesource sql.class.php
 * @version    0.1.0
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
class SQL extends mysqli{

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
	 * Use a secure connection?
	 * @var bool
	 */
	public $use_ssl = false;

	/**
	 * The path name to the certificate authority file.
	 * @var string
	 * @link http://php.net/manual/mysqli.ssl-set.php
	 */
	public $ssl_ca = null;

	/**
	 * The pathname to a directory that contains trusted SSL CA certificates in PEM format.
	 * @var string
	 */
	public $ssl_capath = null;

	/**
	 * The path name to the certificate file.
	 * @var string
	 */
	public $ssl_cert = null;

	/**
	 * A list of allowable ciphers to use for SSL encryption.
	 * @var string
	 */
	public $ssl_cipher = null;

	/**
	 * The path name to the key file.
	 * @var string
	 */
	public $ssl_key = null;


	/**
	 * Constructor
	 *
	 * Initializes MySQLi and sets some base options
	 */
	public function __construct(){
		// init mysqli
		parent::init();

		// set timeout
		if(!parent::options(MYSQLI_OPT_CONNECT_TIMEOUT, $this->timeout)){
			exit('Could not set database timeout.');
		}

		// using ssl?
		if($this->use_ssl){
			parent::ssl_set($this->ssl_key , $this->ssl_cert, $this->ssl_ca, $this->ssl_capath, $this->ssl_cipher);
		}
	}

	/**
	 * Connect
	 *
	 * Establishes a connection to a MySQL database and forces UTF-8 character set
	 *
	 * @link http://php.net/manual/mysqli.construct.php
	 *
	 * @param string $host     Can be either a host name or an IP address.
	 *                         Passing the NULL value or the string "localhost" to this parameter, the local host is assumed.
	 * @param string $user     The MySQL user name.
	 * @param string $password If not provided or NULL, the MySQL server will attempt to authenticate the user
	 *                         against those user records which have no password only.
	 * @param string $database If provided will specify the default database to be used when performing queries.
	 * @param string $port     Specifies the port number to attempt to connect to the MySQL server.
	 * @param string $socket   Specifies the socket or named pipe that should be used.
	 *
	 * @return bool|void
	 */
	public function connect($host = null, $user = null, $password = null, $database = null, $port = null, $socket = null){
		// connect
		if(!parent::real_connect($host, $user, $password, $database, $port, $socket)){
			// don't ever expose SQL errors to the public.
			// this is enough info for the user
			exit('Could not connect to the database.');
		}

		// try to set the character set
		if(!parent::set_charset($this->charset)){
			exit('Could not set database charset.');
		}

		// everything fine
		return true;
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
			if($specialchars == true){
				$data = htmlspecialchars($data, null, 'UTF-8', false);
			}
			$data = parent::real_escape_string($data);
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
	 *
	 * @return array|bool <i>array</i> with results, <i>true</i> on void query success, otherwise <i>false</i>.
	 */
	public function simple_query($sql, $assoc = true){
		if($result = parent::query($sql)){
			// ok, we have a result with one or more rows, loop out the rows and output as array
			if(!is_bool($result)){
				$out = [];
				if($result->num_rows > 0){
					while($r = $assoc === true ? $result->fetch_assoc() : $result->fetch_row()){
						$out[] = $r;
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
	public function get_references(array $array){
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
	 *
	 * @return array|bool Array with results, true on void query success, otherwise false
	 */
	public function prepared_query($sql, array $values = [], $types = '', $assoc = true){
		// create prepared statement
		$stmt = parent::stmt_init();
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
					$output[$count][$k] = $v;
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
		// check if the array is multidimensional
		if(is_array($values) && count($values) > 0 && is_array($values[0]) && count($values[0]) > 0){
			$stmt = parent::stmt_init();
			$reflection = new ReflectionClass('mysqli_stmt');
			$method = $reflection->getMethod('prepare');
			if($method->invokeArgs($stmt, [$sql])){
				$method = $reflection->getMethod('bind_param');
				$cols = count($values[0]);
				$types = preg_match('/^[bdis]{'.$cols.'}$/', $types) ? $types : str_repeat('s', $cols);
				foreach($values as $row){
					$refs = $this->get_references($row);
					array_unshift($refs, $types);
					$method->invokeArgs($stmt, $refs);
					$stmt->execute();
				}
			}
			$stmt->close();
			return true;
		}
		return false;
	}

}
