<?php

// ----
// private/access.php
// authentication data for MySQL/RabbitMQ/mCache/etc
// ----

// MYSQL
define('MYSQL_MAIN_HOST', '127.0.0.1:3306');
define('MYSQL_MAIN_PORT', 3306);
define('MYSQL_MAIN_USER', '');
define('MYSQL_MAIN_PASS', '');
define("MYSQL_MAIN_SSL", false);

// REDIS
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PASSWORD', '');

// RABBIT BUS
define('RABBIT_BUS_HOST'                  , '127.0.0.1');
define('RABBIT_BUS_PORT'                  , '5672');
define('RABBIT_BUS_USER'                  , 'guest');
define('RABBIT_BUS_PASS'                  , 'guest');
define('RABBIT_BUS_QUEUE_NAME'                  , 'rabbit_bus_queue');

