# Slim-SessionDatabase

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

Library is not registered in packagist yet, you should create your own class autoloader.
```php
spl_autoload_register(function($class){
	$file = strtr($class, "\\", DIRECTORY_SEPARATOR);
	$fullpath = __DIR__."/".$file.".php";
	if(file_exists($fullpath))
		require_once($fullpath);
});
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
use steamegg\Slim\SessionDatabase\Connection\MysqliConnection;
use steamegg\Slim\SessionDatabase\SessionConfig;
use steamegg\Slim\SessionDatabase\SessionDbHandler;

$connection = new MysqliConnection(mysqli_connect("localhost","dbuser","password","test"));
$config = new SessionConfig("SECURITY_CODE");
new SessionDbHandler($connection, $config);
```

> Create database connection using PDO-Mysql
```php
use steamegg\Slim\SessionDatabase\Connection\PdoMysqlConnection;
use steamegg\Slim\SessionDatabase\SessionConfig;
use steamegg\Slim\SessionDatabase\SessionDbHandler;

$connection = new PdoMysqlConnection(new \PDO("mysql:dbname=test;host=localhost", "dbuser", "password"));
$config = new SessionConfig("SECURITY_CODE");
new SessionDbHandler($connection, $config);
```