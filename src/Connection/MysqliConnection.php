<?php
namespace steamegg\Slim\SessionMysql\Connection;

class MysqliConnection implements IConnection {
	protected $db;
	
	function __construct(\mysqli $db){
		$this->db = $db;
	}
	
	function affectedRows(){
		return $this->db->affected_rows;
	}
	
	function error(){
		return $this->db->error;
	}
	
	function query($query){
		return $this->db->query($query);
	}
	
	function escape($string){
		return $this->db->real_escape_string($string);
	}
	
	function getLock($name, $timeout){
		$sql = sprintf("SELECT GET_LOCK('%s', '%s')", 
			$this->escape($name),
			$this->escape($timeout));
		
		$result = $this->query($sql);
		
		if(!$result)
			return FALSE;
		
		if(mysqli_num_rows($result) != 1)
			return FALSE;
		
		$row = mysqli_fetch_array($result);
		if($row[0] !== "1")
			return FALSE;
		
		return TRUE;
	}
	
	function releaseLock($name){
		$sql = sprintf("SELECT RELEASE_LOCK('%s')", $this->escape($name));
		$this->query($sql);
	}
	
	function fetchRow($result){
		return mysqli_fetch_assoc($result);
	}
	
	function ping(){
		return $this->db->ping();
	}
}
