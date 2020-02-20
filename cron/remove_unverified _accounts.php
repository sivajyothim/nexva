<?php
if (php_sapi_name() != 'cli')
{
    exit('Command line use only');
}

require_once 'init.php';

$userModel= new Model_User();
$userModel->deleteUnverifiedUsers();