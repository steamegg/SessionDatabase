<?php
namespace steamegg\Slim\SessionMysql;

use steamegg\Slim\SessionMysql\Connection\IConnection;

/**
 *  A PHP library acting as a drop-in replacement for PHP's default session handler, but instead of storing session data
 *  in flat files it stores them in a MySQL database, providing better performance as well as better security and
 *  protection against session fixation and session hijacking.
 *
 *  Read more {@link https://github.com/stefangabos/Zebra_Session/ here}
 *
 *  @author	 Stefan Gabos <contact@stefangabos.ro>
 *  @version	2.1.8 (last revision: May 20, 2017)
 *  @copyright  (c) 2006 - 2017 Stefan Gabos
 *  @license	http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE
 *  @package	Zebra_Session
 */

class Zebra_Session implements \SessionHandlerInterface{

	/**
	 * @var IConnection
	 */
	private $connection;
	
	/**
	 * @var SessionConfig
	 */
	private $config;
	
	/**
	 * the maximum amount of time (in seconds) for which a process can lock the session
	 * @var int
	 */
	private $lock_timeout;
	
	/**
	 * @var string
	 */
	private $lockName;
	
	public function __construct(IConnection &$connection, SessionConfig $config, $lock_timeout = 60) {
		
		if(!$connection->ping())
			trigger_error("Connection ping failed", E_USER_ERROR);

		$this->connection = $connection;
		$this->config = $config;
		$this->lock_timeout = $lock_timeout;
		
		$this->setIni($this->config);

		session_set_save_handler(
			array(&$this, 'open'),
			array(&$this, 'close'),
			array(&$this, 'read'),
			array(&$this, 'write'),
			array(&$this, 'destroy'),
			array(&$this, 'gc')
		);

		session_start();
	}
	
	protected function setIni(SessionConfig $config){
		ini_set('session.gc_maxlifetime', $config->getSessionLifetime());
		ini_set('session.gc_probability', $config->getGcProbability());
		ini_set('session.gc_divisor', $config->getGcDivisor());
		
		// make sure session cookies never expire so that session lifetime
		// will depend only on the value of $session_lifetime
		ini_set('session.cookie_lifetime', 0);
		
		// tell the browser not to expose the cookie to client side scripting
		// this makes it harder for an attacker to hijack the session ID
		ini_set('session.cookie_httponly', 1);
		
		// make sure that PHP only uses cookies for sessions and disallow session ID passing as a GET parameter
		ini_set('session.use_only_cookies', 1);
	}

	function close() {
		if($this->connection->releaseLock($this->lockName))
			return TRUE;
	}

	function destroy($session_id) {
		$sql = sprintf("DELETE FROM %s WHERE session_id = %s", 
			$this->config->getTable(), 
			$this->connection->quote($session_id));
		$this->connection->query($sql);

		return $this->connection->affectedRows() >= 0 ? TRUE : FALSE;
	}

	function gc($maxlifetime) {
		$sql = sprintf("DELETE FROM %s WHERE session_expire < %s", 
			$this->config->getTable(), 
			$this->connection->quote(time()));
		$this->connection->query($sql);
	}

	function open($save_path, $name){
		return TRUE;
	}

	function read($session_id) {
		$this->lockName = sprintf("session_%s", $session_id);

		if( !$this->connection->getLock($this->lockName, $this->lock_timeout) )
			die("Could not obtain session lock");

		$sql = sprintf("SELECT session_data FROM %s WHERE session_id = %s AND session_expire > %s AND hash = %s LIMIT 1", 
			$this->config->getTable(), 
			$this->connection->quote($session_id), 
			$this->connection->quote(time()), 
			$this->connection->quote($this->config->calculateHash())
			);
		$result = $this->connection->query($sql);
		
		$row = $this->connection->fetchRow($result);
		return isset($row["session_data"]) ? $row["session_data"] : "";
	}
	
	function write($session_id, $session_data) {
		// insert OR update, read more here http://dev.mysql.com/doc/refman/4.1/en/insert-on-duplicate.html
		$sql = sprintf("INSERT INTO %s (session_id,hash,session_data,session_expire) VALUES (%s,%s,%s,%s)
			ON DUPLICATE KEY UPDATE session_data = %s, session_expire = %s", 
			$this->config->getTable(),
			$this->connection->quote($session_id),
			$this->connection->quote($this->config->calculateHash()),
			$this->connection->quote($session_data),
			$this->connection->quote(time() + $this->config->getSessionLifetime()),
			$this->connection->quote($session_data),
			$this->connection->quote(time() + $this->config->getSessionLifetime())
			);
		
		$result = $this->connection->query($sql);
		
		return $result ? true : false;
	}
}
