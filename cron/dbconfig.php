<?php
/**
 * **********THIS FILE IS DEPCRECATED. DO NOT USE*************
 *
 * USE application/BootstrapCli.php TO BOOTSTRAP ZEND FRAMEWORK FROM CLI
 *
 *
 */
 
include_once("enviroment.php");
if( $enviroment == "development")
{
    define("HOST", "192.168.1.253");
    define("USERNAME", "root");
    define("PASSWORD", "");
    define("WURFL_DB", "tera_wurfl");
    define("NEXVA_DB", "nexva_v2_dev");
    define("NEXVA_V1", "nexva_dev");
}
if( $enviroment == "staging")
{
    define("HOST", "127.0.0.1");
    define("USERNAME", "stageuser");
    define("PASSWORD", "drY76oRariF");
    define("WURFL_DB", "tera_wurfl_v2");
    define("NEXVA_DB", "nexva_v2_staging");
    define("USERNAME_V1", "nexvadb");
    define("PASSWORD_V1", "aluthgama");
    define("NEXVA_V1", "nexvadb");
}

if( $enviroment == "production")
{
    //@TODO: fill this up
    define("HOST", "127.0.0.1");
    define("USERNAME", "stageuser");
    define("PASSWORD", "drY76oRariF");
    define("WURFL_DB", "tera_wurfl_v2");
    define("NEXVA_DB", "nexva_v2_production");
    define("USERNAME_V1", "nexvadb");
    define("PASSWORD_V1", "aluthgama");
    define("NEXVA_V1", "nexvadb");
}

?>