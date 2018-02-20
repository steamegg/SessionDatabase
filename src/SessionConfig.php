<?php
namespace steamegg\Slim\SessionDatabase;

class SessionConfig {
	private $security_code;
	private $user_agent;
	private $session_lifetime;
	private $ip;
	private $gc_probability;
	private $gc_divisor;
	private $table;
	
	function __construct(
		$security_code,
		$user_agent,
		$session_lifetime = NULL,
		$ip = NULL,
		$gc_probability = NULL,
		$gc_divisor = NULL,
		$table = 'session_data'){
		
		$this->security_code = $security_code;
		$this->user_agent = $user_agent;
		
		$this->session_lifetime = is_numeric($session_lifetime) ? (int) $session_lifetime : ini_get('session.gc_maxlifetime');
		
		$this->ip = $ip;
		
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
			$this->security_code,
			$this->user_agent ? $this->user_agent : "",
			$this->ip ? $this->ip : ""
			);
		
		return md5($hash);
	}
}
