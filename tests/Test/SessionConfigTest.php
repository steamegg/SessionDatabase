<?php
namespace steamegg\Slim\SessionDatabase\Test;

use steamegg\Slim\SessionDatabase\SessionConfig;

class SessionConfigTest extends TestCase {
	
	function testSecurityCode(){
		$config = new SessionConfig("code1");
		$this->assertAttributeEquals("code1", "security_code", $config);
	}
	
	function testSessionLifetime(){
		$config = new SessionConfig("code1");
		$this->assertEquals(ini_get('session.gc_maxlifetime'), $config->getSessionLifetime());
		
		$config = new SessionConfig("code1", "notNumeric");
		$this->assertEquals(ini_get('session.gc_maxlifetime'), $config->getSessionLifetime());
		
		$config = new SessionConfig("code1", 9999);
		$this->assertEquals(9999, $config->getSessionLifetime());
	}
	
	function testLockToIp(){
		$config = new SessionConfig("code1");
		$this->assertAttributeEquals(FALSE, "lock_to_ip", $config);
		
		$config = new SessionConfig("code1", NULL, TRUE);
		$this->assertAttributeEquals(TRUE, "lock_to_ip", $config);
	}
	
	function testLockToUserAgent(){
		$config = new SessionConfig("code1");
		$this->assertAttributeEquals(TRUE, "lock_to_user_agent", $config);
		
		$config = new SessionConfig("code1", NULL, NULL, FALSE);
		$this->assertAttributeEquals(FALSE, "lock_to_user_agent", $config);
	}
	
	function testGc(){
		$config = new SessionConfig("code1", NULL, NULL, NULL);
		$this->assertEquals(ini_get('session.gc_probability'), $config->getGcProbability());
		$this->assertEquals(ini_get('session.gc_divisor'), $config->getGcDivisor());
		
		$config = new SessionConfig("code1", NULL, NULL, NULL, "notNumeric1", "notNumeric2");
		$this->assertEquals(ini_get('session.gc_probability'), $config->getGcProbability());
		$this->assertEquals(ini_get('session.gc_divisor'), $config->getGcDivisor());
		
		$config = new SessionConfig("code1", NULL, NULL, NULL, 1234, 9876);
		$this->assertEquals(1234, $config->getGcProbability());
		$this->assertEquals(9876, $config->getGcDivisor());
	}
	
	function testTable(){
		$config = new SessionConfig("code1");
		$this->assertEquals("session_data", $config->getTable());
		
		$config = new SessionConfig("code1", NULL, NULL, NULL, NULL, NULL, "table1");
		$this->assertEquals("table1", $config->getTable());
	}
	
	function testHash(){
		$config = new SessionConfig("code1");
		$hash = md5(sprintf("%s", "code1"));
		$this->assertEquals($hash, $config->calculateHash());
		
		$hash = md5(sprintf("%s", "invalideCode"));
		$this->assertNotEquals($hash, $config->calculateHash());
	}
}
