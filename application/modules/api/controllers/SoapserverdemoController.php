<?php

class Api_SoapserverdemoController extends Zend_Controller_Action {
    
    
    
    public function init() {
        
        include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );
        
        /* Initialize actionNexpayer controller here */
        $this->_helper->layout ()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
    }
    
    
    function hello($username) {
    	return 'Howdy, '.$username.'!';
    }
    
    //second function implementation
    function login($username, $password) {
    	//should do some database query here
    	// .... ..... ..... .....
    	//just some dummy result
    	return array(
    			'id_user'=>1,
    			'fullname'=>'John Reese',
    			'email'=>'john@reese.com',
    			'level'=>99
    	);
    }
    
    
    
    public function bbAction() {
        
        $server = new nusoap_server;
        
        $server->configureWSDL('server', 'urn:server');
        
        $server->wsdl->schemaTargetNamespace = 'urn:server';
        
        //SOAP complex type return type (an array/struct)
        $server->wsdl->addComplexType(
        		'Person',
        		'complexType',
        		'struct',
        		'all',
        		'',
        		array(
        				'id_user' => array('name' => 'id_user', 'type' => 'xsd:int'),
        				'fullname' => array('name' => 'fullname', 'type' => 'xsd:string'),
        				'email' => array('name' => 'email', 'type' => 'xsd:string'),
        				'level' => array('name' => 'level', 'type' => 'xsd:int')
        		)
        );
        
        //first simple function
        $server->register('hello',
        		array('username' => 'xsd:string'),  //parameter
        		array('return' => 'xsd:string'),  //output
        		'urn:server',   //namespace
        		'urn:server#helloServer',  //soapaction
        		'rpc', // style
        		'encoded', // use
        		'Just say hello');  //description
        
        //this is the second webservice entry point/function
        $server->register('login',
        		array('username' => 'xsd:string', 'password'=>'xsd:string'),  //parameters
        		array('return' => 'tns:Person'),  //output
        		'urn:server',   //namespace
        		'urn:server#loginServer',  //soapaction
        		'rpc', // style
        		'encoded', // use
        		'Check user login');  //description
        
        
        $HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
        
        $server->service($HTTP_RAW_POST_DATA);
        
    }
    
    
    public function bbcAction() {

        
        
        $spEndPoint =  'http://api.nexva.com/soapserverdemo/bb';
        
         
        $client = new nusoap_client($spEndPoint);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        
         
        $this->_header = array();
        
        
        
  //      $result = $client->call('test', array('a' => 3, 'v' => 5), '', '', $this->_header);
        
        $result=$client->call('hello', array('username'=>'achmad'));
        
        //trun on this for debuging
        echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
        Zend_Debug::dump($result);
        
        die();
    }
    


    public function soapAction()
    {

    	// initialize server and set URI
    	$server = new Zend_Soap_Server(null, array('uri' => 'http://api.nexva.com/soapserverdemo/soap'));
    	

    	
    	//$server->configureWSDL('AddService', 'http://api.nexva.com');
    	//$server->wsdl->schemaTargetNamespace = 'http://api.nexva.com/xsd/';
    	


    	
    	// set SOAP service class
    	$server->setClass('Example_Manager');
    
    	// handle request
    	$server->handle();
    	
    }
    
    function add($a, $b) {
    	return $a + $b;
    }
    
    public function ssssAction()
    {
    
    
    $spEndPoint =  'http://api.nexva.com/soapserverdemo/soap';

     
    $client = new nusoap_client($spEndPoint);
    $client->soap_defencoding = 'UTF-8';
    $client->decode_utf8 = false;
    
     
    $this->_header = array();
    

    
    $result = $client->call('test', array('a' => 3, 'v' => 5), '', '', $this->_header);
    
    //trun on this for debuging
        echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
	    echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
	    echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
        Zend_Debug::dump($result);
    
    die();
    
   Zend_Debug::dump($client->getDebug());
   Zend_Debug::dump($error = $client->getError());

   
   //trun on this for debuging
   // echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
   //echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	
    
    }
    
    
    
   
    

}

     




class Example_Manager
{
    
    function test($ss, $sss)
    {
    	
        return $ss.' abccdefgh '.$sss ;
    }
    
    public function getProducts()
    {
    	$db = Zend_Registry::get ( 'db' );
    	$sql = "SELECT * FROM productss limit 10";

    	return $db->fetchAll($sql);
    }
    
    
}

	