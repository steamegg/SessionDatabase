<?php
namespace steamegg\Slim\SessionDatabase\Test;

use steamegg\Slim\SessionDatabase\SessionConfig;

class SessionConfigTest extends TestCase {
	
	function testSecurityCode(){
		$config = new SessionConfig("code1", "fingerprint");
		$this->assertAttributeEquals("code1", "security_code", $config);
	}
	
	function testFingerprint(){
		$config = new SessionConfig("code1", "fingerprint");
		$this->assertAttributeEquals("fingerprint", "fingerprint", $config);
		
		$config = new SessionConfig("code1", "fingerprint+ip");
		$this->assertAttributeEquals("fingerprint+ip", "fingerprint", $config);
	}
	
	function testSessionLifetime(){
		$config = new SessionConfig("code1", "fingerprint");
		$this->assertEquals(ini_get('session.gc_maxlifetime'), $config->getSessionLifetime());
		
		$config = new SessionConfig("code1", "fingerprint", "notNumeric");
		$this->assertEquals(ini_get('session.gc_maxlifetime'), $config->getSessionLifetime());
		
		$config = new SessionConfig("code1", "fingerprint", 9999);
		$this->assertEquals(9999, $config->getSessionLifetime());
	}
	
	function testGc(){
		$config = new SessionConfig("code1", "fingerprint", NULL);
		$this->assertEquals(ini_get('session.gc_probability'), $config->getGcProbability());
		$this->assertEquals(ini_get('session.gc_divisor'), $config->getGcDivisor());
		
		$config = new SessionConfig("code1", "fingerprint", NULL, "notNumeric1", "notNumeric2");
		$this->assertEquals(ini_get('session.gc_probability'), $config->getGcProbability());
		$this->assertEquals(ini_get('session.gc_divisor'), $config->getGcDivisor());
		
		$config = new SessionConfig("code1", "fingerprint", NULL, 1234, 9876);
		$this->assertEquals(1234, $config->getGcProbability());
		$this->assertEquals(9876, $config->getGcDivisor());
	}
	
	function testTable(){
		$config = new SessionConfig("code1", "fingerprint");
		$this->assertEquals("session_data", $config->getTable());
		
		$config = new SessionConfig("code1", "fingerprint", NULL, NULL, NULL, "table1");
		$this->assertEquals("table1", $config->getTable());
	}
	
	function testHash(){
		$config = new SessionConfig("code1", "fingerprint");
		$hash = md5(sprintf("%s%s", "code1", "fingerprint"));
		$this->assertEquals($hash, $config->calculateHash());
		$hash = md5(sprintf("%s%s", "code1", "fingerprint+ip"));
		$this->assertNotEquals($hash, $config->calculateHash());
	}
}
