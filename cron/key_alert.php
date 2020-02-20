<?php

if (php_sapi_name() != 'cli')
{
	exit('Command line use only');
}
require('enviroment.php');
define('APPLICATION_ENV', $enviroment);
define('_CRONJOB_',true);

//BCC email send to the Internal administrator 
define('_BCCEMAIL_', 'cj19820508@hotmail.com');
define('_BCCNAME_','Chathura Jayasekara');
$mailTemlateContentsStr = $str = file_get_contents('../application/views/layouts/mail-templates/generic_mail_template.phtml');

if(APPLICATION_ENV == 'development') {
	
    $_SERVER['HTTP_HOST']='localhost';
    define('_DEBUG_',true);
    define('_TESTEMAIL_', 'cj19820508@hotmail.com');
    define('_TESTNAME_','Chathura Jayasekara');
    
}
if(APPLICATION_ENV == 'staging') {
	
    define('_DEBUG_',true);
    define('_TESTEMAIL_', 'cj19820508@hotmail.com');
    define('_TESTNAME_','Chathura Jayasekara');
    
}


require('../public/index.php');

$productKeyModel_ProductKey = new Model_ProductKey();
$productKeyModel_ProductKey->productKeysLessthanTen($mailTemlateContentsStr);

