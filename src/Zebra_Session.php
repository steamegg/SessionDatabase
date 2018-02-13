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
class Zebra_Session {

	private $session_lifetime;
	private $connection;
	private $lock_timeout;
	private $lock_to_ip;
	private $lock_to_user_agent;
	private $table_name;
	private $lockName;

	/**
	 *  Constructor of class. Initializes the class and automatically calls
	 *  {@link http://php.net/manual/en/function.session-start.php start_session()}.
	 *
	 *  <code>
	 *  // first, connect to a database containing the sessions table
	 *
	 *  //  include the class
	 *  require 'path/to/Zebra_Session.php';
	 *
	 *  //  start the session
	 *  //  where $link is a connection link returned by mysqli_connect
	 *  $session = new Zebra_Session($link, 'sEcUr1tY_c0dE');
	 *  </code>
	 *
	 *  By default, the cookie used by PHP to propagate session data across multiple pages ('PHPSESSID') uses the
	 *  current top-level domain and subdomain in the cookie declaration.
	 *
	 *  Example: www.domain.com
	 *
	 *  This means that the session data is not available to other subdomains. Therefore, a session started on
	 *  www.domain.com will not be available on blog.domain.com. The solution is to change the domain PHP uses when it
	 *  sets the 'PHPSESSID' cookie by calling the line below *before* instantiating the Zebra_Session library.
	 *
	 *  <code>
	 *  // takes the domain and removes the subdomain
	 *  // blog.domain.com becoming .domain.com
	 *  ini_set(
	 *	  'session.cookie_domain',
	 *	  substr($_SERVER['SERVER_NAME'], strpos($_SERVER['SERVER_NAME'], '.'))
	 *  );
	 *  </code>
	 *
	 *  From now on whenever PHP sets the 'PHPSESSID' cookie, the cookie will be available to all subdomains!
	 *
	 *  @param  resource	$connection			   An object representing the connection to a MySQL Server, as returned
	 *										  by calling {@link http://www.php.net/manual/en/mysqli.construct.php mysqli_connect}.
	 *
	 *										  If you use {@link http://stefangabos.ro/php-libraries/zebra-database/ Zebra_Database}
	 *										  to connect to the database, you can get the connection to the MySQL server
	 *										  via Zebra_Database's {@link http://stefangabos.ro/wp-content/docs/Zebra_Database/Zebra_Database/Zebra_Database.html#methodget_link get_link}
	 *										  method.
	 *
	 *  @param  string	  $security_code	  The value of this argument is appended to the string created by
	 *										  concatenating the user's User Agent (browser) string (or an empty string
	 *										  if "lock_to_user_agent" is FALSE) and to the user's IP address (or an
	 *										  empty string if "lock_to_ip" is FALSE), before creating an MD5 hash out
	 *										  of it and storing it in the database.
	 *
	 *										  On each call this value will be generated again and compared to the
	 *										  value stored in the database ensuring that the session is correctly linked
	 *										  with the user who initiated the session thus preventing session hijacking.
	 *
	 *										  <samp>To prevent session hijacking, make sure you choose a string around
	 *										  12 characters long containing upper- and lowercase letters, as well as
	 *										  digits. To simplify the process, use {@link https://www.random.org/passwords/?num=1&len=12&format=html&rnd=new this}
	 *										  link to generate such a random string.</samp>
	 *
	 *  @param  integer	 $session_lifetime   (Optional) The number of seconds after which a session will be considered
	 *										  as <i>expired</i>.
	 *
	 *										  Expired sessions are cleaned up from the database whenever the <i>garbage
	 *										  collection routine</i> is run. The probability of the <i>garbage collection
	 *										  routine</i> to be executed is given by the values of <i>$gc_probability</i>
	 *										  and <i>$gc_divisor</i>. See below.
	 *
	 *										  Default is the value of <i>session.gc_maxlifetime</i> as set in in php.ini.
	 *										  Read more at {@link http://www.php.net/manual/en/session.configuration.php}
	 *
	 *										  To clear any confusions that may arise: in reality, <i>session.gc_maxlifetime</i>
	 *										  does not represent a session's lifetime but the number of seconds after
	 *										  which a session is seen as <i>garbage</i> and is deleted by the <i>garbage
	 *										  collection routine</i>. The PHP setting that sets a session's lifetime is
	 *										  <i>session.cookie_lifetime</i> and is usually set to "0" - indicating that
	 *										  a session is active until the browser/browser tab is closed. When this class
	 *										  is used, a session is active until the browser/browser tab is closed and/or
	 *										  a session has been inactive for more than the number of seconds specified
	 *										  by <i>session.gc_maxlifetime</i>.
	 *
	 *										  To see the actual value of <i>session.gc_maxlifetime</i> for your
	 *										  environment, use the {@link get_settings()} method.
	 *
	 *										  Pass an empty string to keep default value.
	 *
	 *  @param  boolean	 $lock_to_user_agent (Optional) Whether to restrict the session to the same User Agent (or
	 *										  browser) as when the session was first opened.
	 *
	 *										  <i>The user agent check only adds minor security, since an attacker that
	 *										  hijacks the session cookie will most likely have the same user agent.</i>
	 *
	 *										  In certain scenarios involving Internet Explorer, the browser will randomly
	 *										  change the user agent string from one page to the next by automatically
	 *										  switching into compatibility mode. So, on the first load you would have
	 *										  something like:
	 *
	 *										  <code>Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; etc...</code>
	 *
	 *										  and reloading the page you would have
	 *
	 *										  <code> Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; etc...</code>
	 *
	 *										  So, if the situation asks for this, change this value to FALSE.
	 *
	 *										  Default is TRUE.
	 *
	 *  @param  boolean	 $lock_to_ip		 (Optional) Whether to restrict the session to the same IP as when the
	 *										  session was first opened.
	 *
	 *										  Use this with caution as many users have dynamic IP addresses which may
	 *										  change over time, or may come through proxies.
	 *
	 *										  This is mostly useful if your know that all your users come from static IPs.
	 *
	 *										  Default is FALSE.
	 *
	 *  @param  integer	 $gc_probability	 (Optional) Used in conjunction with <i>$gc_divisor</i>. It defines the
	 *										  probability that the <i>garbage collection routine</i> is started.
	 *
	 *										  The probability is expressed by the formula:
	 *
	 *										  <code>
	 *										  $probability = $gc_probability / $gc_divisor;
	 *										  </code>
	 *
	 *										  So, if <i>$gc_probability</i> is 1 and <i>$gc_divisor</i> is 100, it means
	 *										  that there is a 1% chance the the <i>garbage collection routine</i> will
	 *										  be called on each request.
	 *
	 *										  Default is the value of <i>session.gc_probability</i> as set in php.ini.
	 *										  Read more at {@link http://www.php.net/manual/en/session.configuration.php}
	 *
	 *										  To see the actual value of <i>session.gc_probability</i> for your
	 *										  environment, and the computed <i>probability</i>, use the
	 *										  {@link get_settings()} method.
	 *
	 *										  Pass an empty string to keep default value.
	 *
	 *  @param  integer	 $gc_divisor		 (Optional) Used in conjunction with <i>$gc_probability</i>. It defines the
	 *										  probability that the <i>garbage collection routine</i> is started.
	 *
	 *										  The probability is expressed by the formula:
	 *
	 *										  <code>
	 *										  $probability = $gc_probability / $gc_divisor;
	 *										  </code>
	 *
	 *										  So, if <i>$gc_probability</i> is 1 and <i>$gc_divisor</i> is 100, it means
	 *										  that there is a 1% chance the the <i>garbage collection routine</i> will
	 *										  be called on each request.
	 *
	 *										  Default is the value of <i>session.gc_divisor</i> as set in php.ini.
	 *										  Read more at {@link http://www.php.net/manual/en/session.configuration.php}
	 *
	 *										  To see the actual value of <i>session.gc_divisor</i> for your
	 *										  environment, and the computed <i>probability</i>, use the
	 *										  {@link get_settings()} method.
	 *
	 *										  Pass an empty string to keep default value.
	 *
	 *  @param  string	  $table_name		 (Optional) Name of the MySQL table used by the class.
	 *
	 *										  Default is <i>session_data</i>.
	 *
	 *  @param  string	  $lock_timeout	   (Optional) The maximum amount of time (in seconds) for which a lock on
	 *										  the session data can be kept.
	 *
	 *										  <i>This must be lower than the maximum execution time of the script!</i>
	 *
	 *										  Session locking is a way to ensure that data is correctly handled in a
	 *										  scenario with multiple concurrent AJAX requests.
	 *
	 *										  Read more about it at
	 *										  {@link http://thwartedefforts.org/2006/11/11/race-conditions-with-ajax-and-php-sessions/}
	 *
	 *										  Default is <i>60</i>
	 *
	 *  @return void
	 */
	public function __construct(
		IConnection &$connection, 
		$security_code, 
		$session_lifetime = NULL, 
		$lock_to_user_agent = true, 
		$lock_to_ip = false, 
		$gc_probability = NULL, 
		$gc_divisor = NULL, 
		$table_name = 'session_data', 
		$lock_timeout = 60) {
		
		// continue if the provided link is valid
		if(!$connection->ping())
			trigger_error("Connection ping failed", E_USER_ERROR);

		// store the connection link
		$this->connection = $connection;
		
		// we'll use this later on in order to try to prevent HTTP_USER_AGENT spoofing
		$this->security_code = $security_code;
		
		// get session lifetime
		$this->session_lifetime = $this->detectLifetime($session_lifetime);
		
		// some other defaults
		$this->lock_to_user_agent = $lock_to_user_agent;
		$this->lock_to_ip = $lock_to_ip;
		
		// the table to be used by the class
		$this->table_name = $table_name;
		
		// the maximum amount of time (in seconds) for which a process can lock the session
		$this->lock_timeout = $lock_timeout;
		
		$this->setIni($gc_probability, $gc_divisor);

		// register the new handler
		session_set_save_handler(
			array(&$this, 'open'),
			array(&$this, 'close'),
			array(&$this, 'read'),
			array(&$this, 'write'),
			array(&$this, 'destroy'),
			array(&$this, 'gc')
		);

		// start the session
		session_start();
	}
	
	protected function detectLifetime($lifetime){
		return is_numeric($lifetime) ? (int) $lifetime : ini_get('session.gc_maxlifetime');
	}
	
	protected function setIni($gc_probability, $gc_divisor){
		ini_set('session.gc_maxlifetime', $this->session_lifetime);
		
		// if $gc_probability is specified and is an integer number
		if(is_numeric($gc_probability))
			ini_set('session.gc_probability', (int)$gc_probability);
		
		// if $gc_divisor is specified and is an integer number
		if (is_numeric($gc_divisor))
			ini_set('session.gc_divisor', (int)$gc_divisor);
		
		// make sure session cookies never expire so that session lifetime
		// will depend only on the value of $session_lifetime
		ini_set('session.cookie_lifetime', 0);
		
		// tell the browser not to expose the cookie to client side scripting
		// this makes it harder for an attacker to hijack the session ID
		ini_set('session.cookie_httponly', 1);
		
		// make sure that PHP only uses cookies for sessions and disallow session ID passing as a GET parameter
		ini_set('session.use_only_cookies', 1);
	}

	/**
	 *  Custom close() function
	 *
	 *  @access private
	 */
	function close() {
		// release the lock associated with the current session
		if($this->connection->releaseLock($this->lockName))
			return TRUE;
	}

	/**
	 *  Custom destroy() function
	 *
	 *  @access private
	 */
	function destroy($session_id) {
		// delete the current session id from the database
		$sql = sprintf('DELETE FROM %s WHERE session_id = "%s"', 
			$this->table_name, 
			$this->connection->escape($session_id));
		$this->connection->query($sql);

		// return true if everything went well
		return $this->connection->affectedRows() >= 0 ? TRUE : FALSE;
	}

	/**
	 *  Custom gc() function (garbage collector)
	 *
	 *  @access private
	 */
	function gc() {
		// delete expired sessions from database
		$sql = sprintf('DELETE FROM %s WHERE session_expire < "%s"', 
			$this->table_name, 
			$this->connection->escape(time()));
		$this->connection->query($sql);
	}

	/**
	 *  Custom open() function
	 *
	 *  @access private
	 */
	function open() {
		return true;
	}

	/**
	 *  Custom read() function
	 *
	 *  @access private
	 */
	function read($session_id) {
		// get the lock name, associated with the current session
		$this->lockName = sprintf("session_%s", $session_id);

		// try to obtain a lock with the given name and timeout
		// stop if there was an error
		if( !$this->connection->getLock($this->lockName, $this->lock_timeout) )
			die("Could not obtain session lock");

		//  reads session data associated with a session id, but only if
		//  -   the session ID exists;
		//  -   the session has not expired;
		//  -   if lock_to_user_agent is TRUE and the HTTP_USER_AGENT is the same as the one who had previously been associated with this particular session;
		//  -   if lock_to_ip is TRUE and the host is the same as the one who had previously been associated with this particular session;
		$sql = sprintf('SELECT session_data FROM %s WHERE session_id = "%s" AND session_expire > "%s" AND hash = "%s" LIMIT 1', 
			$this->table_name, 
			$this->connection->escape($session_id), 
			time(), 
			$this->connection->escape($this->calculateHash())
			);
		$result = $this->connection->query($sql);
		
		$row = $this->connection->fetchRow($result);
		return isset($row["session_data"]) ? $row["session_data"] : "";
	}
	
	protected function calculateHash(){
		$hash = '';
		
		// if we need to identify sessions by also checking the user agent
		if ($this->lock_to_user_agent && isset($_SERVER['HTTP_USER_AGENT']))
			$hash .= $_SERVER['HTTP_USER_AGENT'];
			
		// if we need to identify sessions by also checking the host
		if ($this->lock_to_ip && isset($_SERVER['REMOTE_ADDR']))
			$hash .= $_SERVER['REMOTE_ADDR'];
			
		// append this to the end
		$hash .= $this->security_code;
		return md5($hash);
	}

	/**
	 *  Custom write() function
	 *
	 *  @access private
	 */
	function write($session_id, $session_data) {
		// insert OR update session's data - this is how it works:
		// first it tries to insert a new row in the database BUT if session_id is already in the database then just
		// update session_data and session_expire for that specific session_id
		// read more here http://dev.mysql.com/doc/refman/4.1/en/insert-on-duplicate.html
		$sql = sprintf('INSERT INTO %s (session_id,hash,session_data,session_expire ) VALUES ("%s","%s","%s","%s")
			ON DUPLICATE KEY UPDATE session_data = "%s", session_expire = "%s"', 
			$this->table_name,
			$this->connection->escape($session_id),
			$this->connection->escape($this->calculateHash()),
			$this->connection->escape($session_data),
			$this->connection->escape(time() + $this->session_lifetime),
			$this->connection->escape($session_data),
			$this->connection->escape(time() + $this->session_lifetime)
			);
		$result = $this->connection->query($sql);

		// if anything happened, return TRUE
		// if something went wrong, return false
		return $result ? true : false;
	}
}