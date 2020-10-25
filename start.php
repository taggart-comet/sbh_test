<?php

// ----
// Инклудим, устанавливаем настройки
// ----

//
declare(strict_types = 1);

// locale
date_default_timezone_set('Europe/Moscow');
setlocale(LC_ALL, 'en_US.utf8');
setlocale(LC_NUMERIC, 'en_US.utf8');

// errors for this script
ini_set('log_errors', '1');
ini_set('error_log', dirname(__FILE__) . "/logs/_error_start_php.log");
ini_set('display_errors', '0');
ini_set('error_reporting', (string)E_ALL);

//
define('PATH_ROOT', dirname(__FILE__) . '/');
define('PATH_LOGS', dirname(__FILE__) . '/logs/');

// db/rabbit/etc access constants
require_once PATH_ROOT . "private/access.php";

// feature switches/etc
require_once PATH_ROOT . "private/control.php";

// -------------------------------------------
// PHP SYSTEM OPTION
// -------------------------------------------

// errors
ini_set('log_errors', '1');
ini_set('error_log', PATH_ROOT . "/logs/__php_error.log");
ini_set('display_errors', '0');
ini_set('error_reporting', (string)(E_ALL ^ E_DEPRECATED ^ E_STRICT));

// options
ini_set('max_execution_time', '10');
ini_set('max_input_time', '10');
ini_set('include_path', (string)PATH_ROOT);
ini_set('memory_limit', (string)'256M');

//
set_time_limit(10);

if (!headers_sent()) {
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: no-cache");
    //	header("Content-type: text/html;charset=" . CONFIG_WEB_CHARSET );
}

// mysql orm
require_once PATH_ROOT . "php_myorm/init.php";

// composer's autoload
require PATH_ROOT . "modules/vendor/autoload.php";

// auto-loading API classes
spl_autoload_register(function ($class) {

    $prefix   = 'Api\\';
    $base_dir = __DIR__ . '/Api/';

    # character length of our prefix
    $len = strlen($prefix);

    # if the first {$len} characters don't match
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $class_name = str_replace($prefix, '', $class);
    $file       = $base_dir . str_replace('\\', '/', $class_name) . '.php';

    # require the file if it exists
    if (file_exists($file)) {

        include_once $file;
    }
});

