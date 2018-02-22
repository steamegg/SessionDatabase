<?php
namespace steamegg\SessionDatabase;

class SessionConfig {
	private $security_code;
	private $fingerprint;
	private $session_lifetime;
	private $gc_probability;
	private $gc_divisor;
	private $table;
	
	function __construct(
		$security_code,
		$fingerprint,
		$session_lifetime,
		$gc_probability,
		$gc_divisor,
		$table = 'session_data'){
		
		$this->security_code = $security_code;
		$this->fingerprint = $fingerprint;
		
		if(!is_numeric($session_lifetime))
			trigger_error("session_lifetime is not numeric", E_USER_ERROR);
		$this->session_lifetime = $session_lifetime;
		
		if(!is_numeric($gc_probability))
			trigger_error("gc_probability is not numeric", E_USER_ERROR);
		$this->gc_probability = $gc_probability;
		
		if(!is_numeric($gc_divisor))
			trigger_error("gc_divisor is not numeric", E_USER_ERROR);
		$this->gc_divisor = $gc_divisor;
		
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
		$hash = sprintf("%s%s", $this->security_code, $this->fingerprint);
		return md5($hash);
	}
}
