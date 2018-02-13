<?php
namespace steamegg\Slim\SessionMysql\Connection;

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
	 * @param string $string
	 * @return string
	 */
	function escape($string);
	
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
