# SessionDatabase

## Features

- This is fork of [stefangabos/Zebra_Session](https://github.com/stefangabos/Zebra_Session).

## Changes from stefangabos/Zebra_Session

* Helper function is removed.(get_settings(), regenerate_id(), get_active_sessions(), stop(), ...)
* Not support flashdata.
* Use tab indentation.
* Not support composer yet.
* Support database driver. (Mysqli, PDO, PDO-Mysql) 

## Requirements

* PHP > 5.4.0 (namespace, SessionHandlerInterface)
* Mysql > 4.1.22

## Installation

Library is not registered in packagist yet.

> composer.json

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/steamegg/SessionDatabase.git"}
    ],
    "require": {
        "steamegg/SessionDatabase": "dev-develop"
    }
}
```

## Install MySQL table

> install/session_data.sql

```sql
CREATE TABLE `session_data` (
  `session_id` varchar(32) NOT NULL default '',
  `hash` varchar(32) NOT NULL default '',
  `session_data` blob NOT NULL,
  `session_expire` int(11) NOT NULL default '0',
  PRIMARY KEY  (`session_id`)
) DEFAULT CHARSET=utf8;
```

## How to use

> Create database connection using Mysqli
```php
use steamegg\SessionDatabase\Connection\MysqliConnection;
use steamegg\SessionDatabase\SessionConfig;
use steamegg\SessionDatabase\SessionDbHandler;

$connection = new MysqliConnection(mysqli_connect("localhost","dbuser","password","test"));
$config = new SessionConfig(
			"SECURITY", 
			$_SERVER["HTTP_USER_AGENT"],
			ini_get("session.gc_maxlifetime"),
			ini_get("session.gc_probability"),
			ini_get("session.gc_divisor")
			);
new SessionDbHandler($connection, $config);
```

> Create database connection using PDO-Mysql
```php
use steamegg\SessionDatabase\Connection\PdoMysqlConnection;
use steamegg\SessionDatabase\SessionConfig;
use steamegg\SessionDatabase\SessionDbHandler;

$connection = new PdoMysqlConnection(new \PDO("mysql:dbname=test;host=localhost", "dbuser", "password"));
$config = new SessionConfig(
			"SECURITY", 
			$_SERVER["HTTP_USER_AGENT"],
			ini_get("session.gc_maxlifetime"),
			ini_get("session.gc_probability"),
			ini_get("session.gc_divisor")
			);
new SessionDbHandler($connection, $config);
```

## Fingerprint
> If you want to use user-agent only
```php
$config = new SessionConfig(
			"SECURITY", 
			$_SERVER["HTTP_USER_AGENT"],
			ini_get("session.gc_maxlifetime"),
			ini_get("session.gc_probability"),
			ini_get("session.gc_divisor")
			);
```

> If you want to use user-agent with ip
```php
$config = new SessionConfig(
			"SECURITY", 
			$_SERVER["HTTP_USER_AGENT"].$_SERVER["REMOTE_ADDR"],
			ini_get("session.gc_maxlifetime"),
			ini_get("session.gc_probability"),
			ini_get("session.gc_divisor")
			);
```