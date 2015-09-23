<?php

/*
**  Db - based upon the PHP MySQL PDO Database Slass by Vivek Wicky Aswal
**  https://github.com/indieteq/PHP-MySQL-PDO-Database-Class
*/

class Db {
	
	private $pdo;
	
	private $sQuery;
	
	private $bConnected = false;
	
	private $dbname;
	private $user;
	private $password;
	private $host;
	
	/**
	 * Construct a new instance of Db using the specified credentials
	 * @param string $dbname   database name to connect to
	 * @param string $user     name of user to connect with
	 * @param string $password password of user to connect with
	 * @param string $host     host (ip or host name) to connect to
	 * @return none
	 */
	public function __construct($dbname, $user, $password, $host = "127.0.0.1") {
		$this->dbname = $dbname;
		$this->user = $user;
		$this->password = $password;
		$this->host = $host;
	}
	
	/**
	 * Connect to the database
	 * @return none
	 */
	private function connect() {
		
		$dsn = 'mysql:dbname=' . $this->dbname . ';host=' . $this->host . '';
		
		try {
			$this->pdo = new PDO(
				$dsn,
				$this->user,
				$this->password,
				array(
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8, time_zone = '+00:00'",
					PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_EMULATE_PREPARES => false,
					PDO::ATTR_PERSISTENT => true
				)
			);
			$this->bConnected = true;
		} catch (PDOException $e) {
			throw new Exception("Exception while connection to the database: " . $e->getMessage());
		}
		
	}
	
	/**
	 * Close the connection to the database
	 * @return none
	 */
	public function closeConnection() {
		$this->bConnected = false;
		$this->sQuery = null;
		$this->pdo = null;
	}
	
	/**
	 * Initializes a query as prepared statement with its (optional) parameters and executes it
	 * @param  string $query      the sql statement to query
	 * @param  array  $parameters an array of parameters for the prepared statement
	 * @return none
	 */
	private function init($query, array $parameters = array()) {
		
		if (!$this->bConnected)
			$this->connect();
		
		try {
			$this->sQuery = $this->pdo->prepare($query);
		} catch (PDOException $e) {
			throw new Exception("Exception while preparing database query: " . $e->getMessage());
		}
		$queryParams = array();
		if (sizeof($parameters) > 0) {
			foreach ($parameters as $paramName => $paramValue)
				$queryParams[":" . $paramName] = $paramValue;
		}
		try {
			$this->success = $this->sQuery->execute($queryParams);
		} catch (PDOException $e) {
			throw new Exception("Exception while executing database query: " . $e->getMessage());
		}
		
	}
	
	/**
	 * Performs the specified query as a prepared statement, using the (optional) parameters and fetches all results according to the (optional) fetch mode
	 * @param  string $query     the sql statement to query
	 * @param  array  $params    an array of parameters to use in the query
	 * @param  int    $fetchmode PDO fetch_style to use (ref. to PDO::fetch)
	 * @return array             the query's result as an array
	 */
	private function performQuery($query, array $params = array(), $fetchmode = PDO::FETCH_ASSOC) {
		
		$this->init($query, $params);
		
		if ($this->sQuery->errorCode() != 0)
			throw new Exception("Exception while querying statement: " . $this->sQuery->errorInfo(), $this->sQuery->errorCode());
		
		$result = $this->sQuery->fetchAll($fetchmode);
		
		// Close the cursor, necessary because
		// of the use of BUFFERED QUERIES
		$this->sQuery->closeCursor();
		
		return $result;
		
	}
	
	/**
	 * Performs a query, using the (optional) parameters and performs a roll back on failure.
	 * @param  string  $query    the query to be executed
	 * @param  array   $params   the parameters (optional)
	 * @param  boolean $returnID whether to return the last inserted row's id (default = false)
	 * @return int               depending on $returnID either the transaction's rowcount or the last inserted row's id
	 */
	private function performExecution($query, array $params = array(), $returnID = false) {
		
		if (!$this->bConnected)
			$this->connect();
		
		$this->pdo->beginTransaction();
		
		$this->init($query, $params);
		
		// init to be called before ...
		if ($this->sQuery->errorCode() != 0) {
			// Rollback the query, important because of 
			// MYSQL_ATTR_USE_BUFFERED_QUERY being set to true
			$this->pdo->rollBack();
			
			throw new Exception("Exception while executing statement: " . $this->sQuery->errorInfo(), $this->sQuery->errorCode());
		}
		
		$lastInsertID = $returnID ? $this->pdo->lastInsertId() : 0;
		
		// Since there has been no error, commit
		$this->pdo->commit();
		
		$rowCount = $this->sQuery->rowCount();
		
		// Close the cursor, necessary because
		// of the use of BUFFERED QUERIES
		$this->sQuery->closeCursor();
		
		return $returnID ? $lastInsertID : $rowCount;
		
	}
	
	/**
	 * Performs the given query, using the (optional) parameters
	 * @param  string $query    the query to run
	 * @param  array  $params   the optional parameters
	 * @param  mixed  $varParam specifies the fetch_style on `select` or `show` statements or whether to return the last inserted row's id
	 * @return mixed            the fetched results on `select` or `show` statements, the last inserted row's id or the affected row count on other transactions
	 */
	public function query($query, array $params = array(), $varParam = null) {
		
		$query = trim($query);
		
		$rawStatement = explode(" ", $query);
		$statement = strtolower($rawStatement[0]);
		
		$fetchmode = PDO::FETCH_ASSOC;
		$returnID = false;
		
		if ($statement === 'select' || $statement === 'show') {
			if ($varParam !== null)
				$fetchmode = $varParam;
			return $this->performQuery($query, $params, $fetchmode);
		} else {
			if ($varParam !== null)
				$returnID = $varParam;
			return $this->performExecution($query, $params, $returnID);
		}
		
		return NULL;
		
	}
	
	/**
	 * Performs a query using the (optional) parameters
	 * @param  string $query  the query to run
	 * @param  array  $params the (optional) parameters to use
	 * @return array          an array of all fetched values of the result's first column
	 */
	public function column($query, array $params = array()) {
		
		$this->init($query, $params);
		$columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);
		
		// Close the cursor, necessary because
		// of the use of BUFFERED QUERIES
		$this->sQuery->closeCursor();
		
		$column = null;
		
		foreach ($columns as $cells) {
			$column[] = $cells[0];
		}
		
		return $column;
		
	}
	
	/**
	 * Performs a query using the (optional) parameters and the (optional) fetch_style
	 * @param  string $query     the query to run
	 * @param  array  $params    the (optional) parameters
	 * @param  int    $fetchmode the fetch_style to apply (ref. to PDO::fetch)
	 * @return array             an array of the fetched values' first row
	 */
	public function row($query, array $params = array(), $fetchmode = PDO::FETCH_ASSOC) {
		
		$result = $this->query($query, $params, $fetchmode);
		
		if ($result === FALSE || sizeof($result) == 0)
			return FALSE;
		
		return $result[0];
		
	}
	
	/**
	 * Performs a query using the (optional) parameters
	 * @param  string $query  the query to run
	 * @param  array  $params the (optional) parameters
	 * @return mixed          the first row's first value out of the fetched results
	 */
	public function single($query, array $params = array()) {
		
		$result = $this->query($query, $params);
		
		if ($result === FALSE || sizeof($result) == 0)
			return FALSE;
		
		$row = $result[0];
		
		foreach ($row as $field)
			return $field;
		
		return FALSE;
		
	}
	
	/**
	 * Imports the specified sql file
	 * @param  string $sqlFile sql file to import, including its compelte, absolute path
	 * @return boolean         import success
	 */
	public function import($sqlFile = "") {
		
		if (empty($sqlFile))
			return false;
		
		$handle = fopen($sqlFile, "r");
		$query = "";
		
		while ($buffer = fgets($handle)) {
			
			// Skip comments and empty lines ...
			if (substr($buffer, 0, 2) == '--' || substr($buffer, 0, 1) == '#' || $buffer == '')
				continue;
			
			// ... anything else gets concatenated
			// to the resulting statement
			$query .= trim($buffer);
			
			if (substr($query, -1, 1) == ';') {
				
				$query = trim(substr($query, 0, -1));
				$rawStatement = explode(" ", $query);
				$statement = strtolower($rawStatement[0]);
				
				// Omit lock and unlock statements as
				// they interfere heavily with out db handler
				if ($statement != "lock" && $statement != "unlock")
					$this->query($query);
				
				$query = "";
				
			}
			
		}
		
		fclose($handle);
		
		return true;
		
	}
	
	private static $dbName;
	private static $dbHost;
	private static $dbUserName;
	private static $dbUserPwd;
	
	/**
	 * Stores the given credentials for repeated database access layer instantiation
	 * @param string $dbname   the database name
	 * @param string $user     an approved user's name
	 * @param string $password an approved user's password
	 * @param string $host     the database's host (either ip or hostname)
	 */
	public static function setCredentials($dbname = "", $user = "", $password = "", $host = "") {
		
		self::$dbName = $dbname;
		self::$dbHost = $host;
		self::$dbUserName = $user;
		self::$dbUserPwd = $password;
		
	}
	
	/**
	 * Instantiates a new database access layer, either using the given credentials or by using those, set by Db::setCredentials
	 * @param  string $dbname   the database name (optional)
	 * @param  string $user     an approved user's name (optional)
	 * @param  string $password an approved user's password (optional)
	 * @param  string $host     the database's host (optional)
	 * @return Db               a new database access layer instance
	 */
	public static function getInstance($dbname = "", $user = "", $password = "", $host = "") {
		
		$dbname = empty($dbname) ? self::$dbName : $dbname;
		$user = empty($user) ? self::$dbUserName : $dbuser;
		$password = empty($password) ? self::$dbUserPwd : $password;
		$host = empty($host) ? self::$dbHost : $host;
		
		return new self($dbname, $user, $password, $host);
		
	}
	
}
