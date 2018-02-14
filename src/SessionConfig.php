<?php
namespace steamegg\Slim\SessionMysql;

class SessionConfig {
	private $security_code;
	private $session_lifetime;
	private $lock_to_ip;
	private $lock_to_user_agent;
	private $gc_probability;
	private $gc_divisor;
	private $table;
	
	function __construct(
		$security_code,
		$session_lifetime = NULL,
		$lock_to_ip = FALSE,
		$lock_to_user_agent = TRUE,
		$gc_probability = NULL,
		$gc_divisor = NULL,
		$table = 'session_data'){
		
		$this->security_code = $security_code;
		
		$this->session_lifetime = is_numeric($session_lifetime) ? (int) $session_lifetime : ini_get('session.gc_maxlifetime');
		
		$this->lock_to_ip = $lock_to_ip;
		$this->lock_to_user_agent = $lock_to_user_agent;
		
		$this->gc_probability = is_numeric($gc_probability) ? (int) $gc_probability : ini_get('session.gc_probability');
		$this->gc_divisor = is_numeric($gc_divisor) ? (int) $gc_divisor : ini_get('session.gc_divisor');
		
		$this->table = $table;
	}
	
	function getSecurityCode(){
		return $this->security_code;
	}
	
	function getSessionLifetime(){
		return $this->session_lifetime;
	}
	
	function isLockToIp(){
		return $this->lock_to_ip;
	}
	
	function isLockToUseragent(){
		return $this->lock_to_user_agent;
	}
	
	function getGcProbability(){
		return $this->gc_probability;
	}
	
	function getGcDivisor(){
		return $this->gc_divisor;
	}
	
	function getTable(){
		return $this->table;
	}
}
