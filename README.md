# Slim-SessionMysql

[![License]]

## Features

- acts as a wrapper for PHP's default session handling functions, but instead of storing session data in flat files it stores them in a MySQL database, providing better security and better performance

- it is a drop-in and seamingless replacement for PHP's default session handler: PHP sessions will be used in the same way as prior to using the library; you don't need to change any existing code!

- implements *row locks*, ensuring that data is correctly handled in scenarios with multiple concurrent AJAX requests

- because session data is stored in a database, the library represents a solution for applications that are scaled across multiple web servers (using a load balancer or a round-robin DNS)

- has awesome documentation

- the code is heavily commented and generates no warnings/errors/notices when PHP's error reporting level is set to E_ALL

## Requirements

PHP 5.1.0+ with the `mysqli extension` activated, MySQL 4.1.22+

## Installation

Download the latest version, unpack it, and load it in your project

```php
require_once ('Zebra_Session.php');
```

## Installation with Composer

You can install Zebra_Session via [Composer](https://packagist.org/packages/stefangabos/zebra_session)

```
composer require stefangabos/zebra_session
```

## Install MySQL table

Notice a directory called *install* containing a file named *session_data.sql*. This file contains the SQL code that will create a table that is used by the class to store session data. Import or execute the SQL code using your preferred MySQL manager (like phpMyAdmin or the fantastic Adminer) into a database of your choice.

## How to use

> Note that this class assumes that there is an active connection to a MySQL database and it does not attempt to create one! If you really need the class to make a database connection, put the code in the "open" method of the class.*

```php
// first, connect to a database containing the sessions table
// like $link = mysqli_connect(host, username, password, database);

// include the Zebra_Session class
include 'path/to/Zebra_Session.php';

// instantiate the class
// this also calls session_start()
$session = new Zebra_Session($link, 'sEcUr1tY_c0dE');

// from now on, use sessions as you would normally
// this is why it is called a "drop-in replacement" :)
$_SESSION['foo'] = 'bar';

// data is in the database!
```

 :books: Checkout the [awesome documentation](https://stefangabos.github.io/Zebra_Session/Zebra_Session/Zebra_Session.html)!