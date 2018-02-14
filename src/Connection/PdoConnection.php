<?php
namespace steamegg\Slim\SessionDatabase\Connection;

abstract class PdoConnection implements IConnection {
	protected $pdo;
	
	abstract function getLock($name, $timeout);
	abstract function releaseLock($name);
	
	function __construct(\PDO $pdo){
		$this->pdo = $pdo;
	}
	
	function affectedRows(){
		return $this->pdo->affected_rows;
	}
	
	function error(){
		return $this->pdo->error;
	}
	
	/**
	 * @return \PDOStatement
	 */
	function query($query){
		return $this->pdo->query($query);
	}
	
	function quote($string){
		return $this->pdo->quote($string);
	}
	
	/**
	 * @param \PDOStatement
	 */
	function fetchRow($statement){
		return $statement->fetch(\PDO::FETCH_ASSOC);
	}
	
	function ping(){
		//PDO has no method like mysqli_ping()
		$statement = $this->pdo->query("SELECT 1");
		return $statement ? TRUE : FALSE;
	}
}
