<?php
namespace steamegg\Slim\SessionDatabase\Connection;

interface IConnection {
	/**
	 * @return int
	 */
	function affectedRows();
	
	function error();
	
	/**
	 * @param string $query
	 */
	function query($query);
	
	/**
	 * Compatibility for PDO::quote, mysqli::real_escape_string
	 * @param string $string
	 * @return string
	 */
	function quote($string);
	
	/**
	 * @param string $name
	 * @param int timeout
	 * @return bool
	 */
	function getLock($name, $timeout);
	
	/**
	 * @param string $name
	 */
	function releaseLock($name);
	
	
	function fetchRow($result);
	
	/**
	 * @return bool
	 */
	function ping();
}
