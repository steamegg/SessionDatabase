<?php
namespace steamegg\Slim\SessionDatabase;

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
	
	function getSessionLifetime(){
		return $this->session_lifetime;
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
	
	function calculateHash(){
		$hash = sprintf("%s%s%s",
			$this->lock_to_user_agent && isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "",
			$this->lock_to_ip && isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "",
			$this->security_code);
		
		return md5($hash);
	}
}
