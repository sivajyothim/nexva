<?php 
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

include_once( 'vendors/Nusoap/lib/nusoap.php' );

// Create the server instance
$server = new soap_server();
// Initialize WSDL support
$server->configureWSDL('soapserveruganda', 'http://nexva-v2-dev.com/soapserveruganda.php');
// Register the method to expose
$server->register('hello',                // method name
    array('name' => ''),        // input parameters
    array('return' => ''),      // output parameters
    'urn:hellowsdl',                      // namespace
    'urn:hellowsdl#hello',                // soapaction
    'rpc',                                // style
    'encoded',                            // use
    'Says hello to the caller'            // documentation
);
// Define the method as a PHP function
function hello($name) {
        return 'Hello, ' . $name;
}
// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);