<?php
namespace steamegg\SessionDatabase\Test;

use steamegg\SessionDatabase\SessionConfig;

class SessionConfigTest extends TestCase {
	
	function testSecurityCode(){
		$config = new SessionConfig("code1", "fingerprint", 111, 222, 333);
		$this->assertAttributeEquals("code1", "security_code", $config);
	}
	
	function testFingerprint(){
		$config = new SessionConfig("code1", "fingerprint", 111, 222, 333);
		$this->assertAttributeEquals("fingerprint", "fingerprint", $config);
		
		$config = new SessionConfig("code1", "fingerprint+ip", 111, 222, 333);
		$this->assertAttributeEquals("fingerprint+ip", "fingerprint", $config);
	}
	
	function testSessionLifetimeShouldNumeric(){
		$this->setExpectedException("PHPUnit_Framework_Error", "session_lifetime is not numeric");
		$config = new SessionConfig("code1", "fingerprint", "notNumeric", 222, 333);
	}
	
	function testSessionLifetime(){
		$config = new SessionConfig("code1", "fingerprint", 111, 222, 333);
		$this->assertEquals(111, $config->getSessionLifetime());
	}
	
	function testGcProbabilityShouldNumeric(){
		$this->setExpectedException("PHPUnit_Framework_Error", "gc_probability is not numeric");
		$config = new SessionConfig("code1", "fingerprint", 111, "notNumeric", 333);
	}
	
	function testGcDivisorShouldNumeric(){
		$this->setExpectedException("PHPUnit_Framework_Error", "gc_divisor is not numeric");
		$config = new SessionConfig("code1", "fingerprint", 111, 222, "notNumeric");
	}
	
	function testGc(){
		$config = new SessionConfig("code1", "fingerprint", 111, 222, 333);
		$this->assertEquals(222, $config->getGcProbability());
		$this->assertEquals(333, $config->getGcDivisor());
	}
	
	function testTable(){
		$config = new SessionConfig("code1", "fingerprint", 111, 222, 333);
		$this->assertEquals("session_data", $config->getTable());
		
		$config = new SessionConfig("code1", "fingerprint", 111, 222, 333, "table1");
		$this->assertEquals("table1", $config->getTable());
	}
	
	function testHash(){
		$config = new SessionConfig("code1", "fingerprint", 111, 222, 333);
		$hash = md5(sprintf("%s%s", "code1", "fingerprint"));
		$this->assertEquals($hash, $config->calculateHash());
		
		$hash = md5(sprintf("%s%s", "code1", "fingerprint+ip"));
		$this->assertNotEquals($hash, $config->calculateHash());
	}
}
