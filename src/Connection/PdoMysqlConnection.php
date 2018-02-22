<?php
namespace steamegg\SessionDatabase\Connection;

class PdoMysqlConnection extends PdoConnection {
	function getLock($name, $timeout){
		$sql = sprintf("SELECT GET_LOCK(%s,%s)",
			$this->quote($name),
			$this->quote($timeout));
		
		if( ! $statement = $this->query($sql) )
			return FALSE;
		
		if($statement->rowCount() != 1)
			return FALSE;
		
		$row = $statement->fetch(\PDO::FETCH_NUM);
		if($row[0] !== "1")
			return FALSE;
			
		return TRUE;
	}
	
	function releaseLock($name){
		$sql = sprintf("SELECT RELEASE_LOCK(%s)", $this->quote($name));
		$this->query($sql);
	}
}
