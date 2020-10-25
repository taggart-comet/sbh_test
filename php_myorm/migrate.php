<?php

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/main/migrate/init.php';

/**
 * !!! WARNING !!!
 * !!! MAKE SURE YOUR CLASSES ARE INCLUDED FOR THIS SCRIPT !!!
 * !!! WARNING !!!
 */
require_once __DIR__ . '/test/models.php';

// my project
require_once __DIR__ . '/../start.php';
ini_set('display_errors', 1);

$run = new \PhpMyOrm\migrate\MigrateManager();

// removing the script name
$arguments = $argv;
unset($arguments[0]);

//
$run->handle($arguments);

