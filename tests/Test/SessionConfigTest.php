<?php
namespace steamegg\Slim\SessionDatabase\Test;

use steamegg\Slim\SessionDatabase\SessionConfig;

class SessionConfigTest extends TestCase {
	
	function testSecurityCode(){
		$config = new SessionConfig("code1", "userAgent");
		$this->assertAttributeEquals("code1", "security_code", $config);
	}
	
	function testUserAgent(){
		$config = new SessionConfig("code1", "userAgent");
		$this->assertAttributeEquals("userAgent", "user_agent", $config);
		
		$config = new SessionConfig("code1", NULL);
		$this->assertAttributeEquals(NULL, "user_agent", $config);
	}
	
	function testSessionLifetime(){
		$config = new SessionConfig("code1", "userAgent");
		$this->assertEquals(ini_get('session.gc_maxlifetime'), $config->getSessionLifetime());
		
		$config = new SessionConfig("code1", "userAgent", "notNumeric");
		$this->assertEquals(ini_get('session.gc_maxlifetime'), $config->getSessionLifetime());
		
		$config = new SessionConfig("code1", "userAgent", 9999);
		$this->assertEquals(9999, $config->getSessionLifetime());
	}
	
	function testIp(){
		$config = new SessionConfig("code1", "userAgent");
		$this->assertAttributeEquals(NULL, "ip", $config);
		
		$config = new SessionConfig("code1", NULL, NULL, "1.2.3.4");
		$this->assertAttributeEquals("1.2.3.4", "ip", $config);
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
		$config = new SessionConfig("code1", "userAgent");
		$this->assertEquals("session_data", $config->getTable());
		
		$config = new SessionConfig("code1", NULL, NULL, NULL, NULL, NULL, "table1");
		$this->assertEquals("table1", $config->getTable());
	}
	
	function testHash(){
		$config = new SessionConfig("code1", "userAgent");
		$hash = md5(sprintf("%s%s", "code1", "userAgent"));
		$this->assertEquals($hash, $config->calculateHash());
		
		$config = new SessionConfig("code1", "userAgent", NULL, "ip");
		$hash = md5(sprintf("%s%s", "code1", "userAgent"));
		$this->assertNotEquals($hash, $config->calculateHash());
		$hash = md5(sprintf("%s%s%s", "code1", "userAgent", "ip"));
		$this->assertEquals($hash, $config->calculateHash());
	}
}
