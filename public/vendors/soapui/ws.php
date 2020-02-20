<?php
/*
 * Webservice example. PHP with SoapUI
 * http://www.romanenco.com/phpsoapui
 * 
 *
 * Andrew Romanenco
 * andrew@romanenco.com
 *
 */


require('lib/nusoap.php');
$server = new soap_server('ws.wsdl');

function reverse($in){
    return strrev($in);
}

function ping() {
    return time();
}

function sum($a, $b) {
    return ($a + $b);
}

$server->service($HTTP_RAW_POST_DATA); 

?>