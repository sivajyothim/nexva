<?php

$time = microtime(true);
$memory = memory_get_usage();

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || include(APPLICATION_PATH.'/.enviroment'); //@todo: find a better way to define env variable

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);



$application->bootstrap();

register_shutdown_function('__shutdown');

function __shutdown() {
    global $time, $memory;
    $endTime = microtime(true);
    $endMemory = memory_get_usage();

    echo "\n\nTime taken: " . ($endTime - $time) .  " Seconds \nMemory used: " . number_format(( $endMemory - $memory) / 1024) . " KB\n";
}