<?php

/*
**  Db - based upon the PHP MySQL PDO Database Slass by Vivek Wicky Aswal
**  https://github.com/indieteq/PHP-MySQL-PDO-Database-Class
*/

class Db {
    
    private $pdo;
    
    private $sQuery;
    
    private $bConnected = false;
    
    private $parameters;
    
	private $dbname;
	private $user;
	private $password;
	private $host;
	
	public function __construct($dbname, $user, $password, $host = "127.0.0.1") {
		$this->dbname = $dbname;
		$this->user = $user;
		$this->password = $password;
		$this->host = $host;
	}
	
	private function connect() {
		
		$dsn = 'mysql:dbname=' . $this->dbname . ';host=' . $this->host . '';
		
		try {
			$this->pdo = new PDO(
				$dsn,
				$this->user,
				$this->password,
				array(
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8; SET time_zone = '+00:00",
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
	
	public function closeConnection() {
		$this->bConnected = false;
		$this->sQuery = null;
		$this->pdo = null;
	}
	
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
	
	private function performQuery($query, array $params = array(), $fetchmode = PDO::FETCH_ASSOC) {
		
		$this->init($query, $params);
		
		if ($this->sQuery->errorCode() != 0)
			throw new Exception("Exception while querying statement: " . $this->sQuery->errorInfo(), $this->sQuery->errorCode());
		
		$result = $this->sQuery->fetchAll($fetchmode);
		
		// Close the cursor, necessary because
		// of the use of BUFFERED QUERIES
		$this->sQuery->closeCursor();
		
		$this->closeConnection();
		
		return $result;
		
	}
	
	private function performExecution($query, array $params = array(), $returnID = false) {
		
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
		
		$this->closeConnection();
		
		return $returnID ? $lastInsertID : $rowCount;
		
	}
	
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
		} elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
			if ($varParam !== null)
				$returnID = $varParam;
			return $this->performExecution($query, $params, $returnID);
		}
		
		return NULL;
		
	}
	
	public function lastInsertId() {
		throw new Exception("The use of Db::lastInsertId() is obsolete! Use Db::query() with its third parameter \$varParam as \"returnID\" set to true, instead.");
	}
	
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
	
	public function row($query, array $params = array(), $fetchmode = PDO::FETCH_ASSOC) {
		
		$result = $this->query($query, $params, $fetchmode);
		
		if ($result === FALSE || sizeof($result) == 0)
			return FALSE;
		
		return $result[0];
		
	}
	
	public function single($query, array $params = array()) {
		
		$result = $this->query($query, $params);
		
		if ($result === FALSE || sizeof($result) == 0)
			return FALSE;
		
		$row = $result[0];
		
		foreach ($row as $field)
			return $field;
		
		return FALSE;
		
	}
	
	
	//private static $instance = null;
	
	private static $dbName;
	private static $dbHost;
	private static $dbUserName;
	private static $dbUserPwd;
	
	public static function setCredentials($dbname = "", $user = "", $password = "", $host = "") {
		
		self::$dbName = $dbname;
		self::$dbHost = $host;
		self::$dbUserName = $user;
		self::$dbUserPwd = $password;
		
	}
	
	public static function getInstance($dbname = "", $user = "", $password = "", $host = "") {
		
		$dbname = empty($dbname) ? self::$dbName : $dbname;
		$user = empty($user) ? self::$dbUserName : $dbuser;
		$password = empty($password) ? self::$dbUserPwd : $password;
		$host = empty($host) ? self::$dbHost : $host;
		
		return new self($dbname, $user, $password, $host);
		
	}
	
}
