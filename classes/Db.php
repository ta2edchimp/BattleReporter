<?php

/*
**  Db - based upon the PHP MySQL PDO Database Slass by Vivek Wicky Aswal
**  https://github.com/indieteq/PHP-MySQL-PDO-Database-Class
*/

class Db {
    
    private $pdo;
    
    private $sQuery;
    
    private $settings;
    
    private $bConnected = false;
    
    private $log;
    
    private $parameters;
    
    public function __construct($dbname, $user, $password, $host) {
        $this->connect($dbname, $user, $password, $host);
    }
    
    private function connect($dbname, $user, $password, $host = "127.0.0.1") {
        $dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . '';
        try {
            $this->pdo = new PDO($dsn, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->bConnected = true;
        } catch (PDOException $e) {
            echo "Exception while connecting to the database\n" . $e->getMessage();
            die();
        }
    }
    
    public function closeConnection() {
        $this->pdo = null;
    }
    
    private function init($query, $parameters = "") {
        if (!$this->bConnected)
            $this->connect();
        
        $this->parameters = array();
        
        try {
            $this->sQuery = $this->pdo->prepare($query);
            $this->bindMore($parameters);
            if (!empty($this->parameters)) {
                foreach ($this->parameters as $param) {
                    $parameters = explode("\x7F", $param);
                    $this->sQuery->bindParam($parameters[0], $parameters[1]);
                }
            }
            $this->success = $this->sQuery->execute();
        } catch (PDOException $e) {
            echo "Exception while initializing database query\n" . $e->getMessage();
        }
    }
    
    private function bind($param, $value) {
        $this->parameters[sizeof($this->parameters)] = ":" . $param . "\x7F" . utf8_encode($value);
    }
    
    private function bindMore($parray) {
        if (empty($this->parameters) && is_array($parray)) {
            $columns = array_keys($parray);
            foreach ($columns as $i => &$column) {
                $this->bind($column, $parray[$column]);
            }
        }
    }
    
    public function query($query, $params = null, $fetchmode = PDO::FETCH_ASSOC) {
        $query = trim($query);
        
        $this->init($query, $params);
        
        $rawStatement = explode(" ", $query);
        
        $statement = strtolower($rawStatement[0]);
        
        if ($statement === 'select' || $statement === 'show') {
            return $this->sQuery->fetchAll($fetchmode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->sQuery->rowCount();
        } else {
            return NULL;
        }
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    public function column($query, $params = null) {
        $this->init($query, $params);
        $columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);
        
        $column = null;
        
        foreach ($columns as $cells) {
            $column[] = $cells[0];
        }
        
        return $column;
    }
    
    public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC) {
        $this->init($query, $params);
        return $this->sQuery->fetch($fetchmode);
    }
    
    public function single($query, $params = null) {
        $this->init($query, $params);
        return $this->sQuery->fetchColumn();
    }
    
}

?>