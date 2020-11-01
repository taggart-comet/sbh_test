<?php

// ----
// private/access.php
// authentication data for MySQL/RabbitMQ/mCache/etc
// ----

// MYSQL
define('MYSQL_MAIN_HOST', 'mysql:3306');
define('MYSQL_MAIN_PORT', 3306);
define('MYSQL_MAIN_USER', 'root');
define('MYSQL_MAIN_PASS', 'secret');
define("MYSQL_MAIN_SSL", false);

// RABBIT BUS
define('RABBIT_BUS_HOST'                  , 'rabbitmq');
define('RABBIT_BUS_PORT'                  , '5672');
define('RABBIT_BUS_USER'                  , 'dev_user');
define('RABBIT_BUS_PASS'                  , 'dev_secret');
define('RABBIT_BUS_QUEUE_NAME'                  , 'rabbit_bus_queue');

