<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 4/24/14
 * Time: 5:58 PM
 * To change this template use File | Settings | File Templates.
 */

class Api_Mobilemoney2Controller extends Zend_Controller_Action {

    public function init() {

       // include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );

        /* Initialize actionNexpayer controller here */
        $this->_helper->layout ()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        global $HTTP_RAW_POST_DATA;
    }

    public function confirmThirdPartyPaymentRequestAction()
    {
    	// initialize server and set URI
        // $server = new Zend_Soap_Server(null, array('uri' => 'http://api.nexva.com/test/confirm-third-party-payment-request'));
       
        $server = new TestSoapServer(null, array('uri' => 'http://api.nexva.com/mobilemoney2/confirm-third-party-payment-request'));
    	
    	// set SOAP service class
    	$server->setClass('Uganda_Soap_Functions');
        
        $server->setEncoding('UTF-8');
        //$server->setReturnResponse(false);

    	// handle request
    	$server->handle();

    }
    
}
    class Uganda_Soap_Functions
    {
    
    	function processRequest($p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9, $p10, $p11, $p12)
    	{
    
    		$objMain = new stdClass();
    
    		$obj1 = new stdClass();
    		$obj2 = new stdClass();
    		$obj3 = new stdClass();
    		$obj4 = new stdClass();
    		$obj5 = new stdClass();
    
    		$obj1->return->name = $p2->name;
    		$obj1->return->value = $p2->value;
    
    		$obj2->return->name = $p10->name;
    		$obj2->return->value = $p10->value;
    
    		$obj3->return->name = '';
    		$obj3->return->value = '';
    
    		$obj4->return->name = 'ThirdPartyAcctRef';
    		$obj4->return->value = $p4->value;
    
    		$obj5->return->name = '';
    		$obj5->return->value = '';
    
    		return array($obj1,$obj2,$obj3,$obj4,$obj5);
    
    	}
    
    }
    
    
    class TestSoapServer extends Zend_Soap_Server
    {
    
    	public function __construct($wsdl, $options = null)
    	{
    		return parent::__construct($wsdl, $options);
    	}
    
    	public function handleUganda($request = null)
    	{
    
    		if (null === $request) {
    			$request = file_get_contents('php://input');
    		}
    
    		// Set Zend_Soap_Server error handler
    		$displayErrorsOriginalState = $this->_initializeSoapErrorContext();
    
    		$setRequestException = null;
    		/**
    		 * @see Zend_Soap_Server_Exception
    		 */
    
    		try {
    			$this->_setRequest($request);
    		} catch (Zend_Soap_Server_Exception $e) {
    			$setRequestException = $e;
    		}
    
    		$soap = $this->_getSoap();
    
    		$fault = false;
    		ob_start();
    		if ($setRequestException instanceof Exception) {
    			// Create SOAP fault message if we've caught a request exception
    			$fault = $this->fault($setRequestException->getMessage(), 'Sender');
    		} else {
    			try {
    				$soap->handle($this->_request);
    			} catch (Exception $e) {
    				$fault = $this->fault($e);
    			}
    		}
    		$this->_response = ob_get_clean();
    
    
    		// Custom response
    		$doc = new DOMDocument();
    		libxml_use_internal_errors(true);
    		$doc->loadHTML($request);
    		libxml_clear_errors();
    		$xml = $doc->saveXML($doc->documentElement);
    		$xml = simplexml_load_string($xml);
    
    		$parameters = $xml->body->envelope->body->processrequest->parameter;
    
    		// $arrResponse = simplexml_load_string($request);
    
    		//print_r($p1); die();
    
    		$this->_response =  '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:b2b="http://b2b.mobilemoney.mtn.zm_v1.0/">
    		<soapenv:Header/>
    		<soapenv:Body>
    		<b2b:processRequestResponse>
    		<return>
    		<name>ProcessingNumber</name>
    		<value>2117285</value>
    		</return>
    		<return>
    		<name>StatusCode</name>
    		<value>01</value>
    		</return>
    		<return>
    		<name>StatusDesc</name>
    		<value></value>
    		</return>
    		<return>
    		<name>ThirdPartyAcctRef</name>
    		<value>20140502123653</value>
    		</return>
    		<return>
    		<name>Token</name>
    		<value></value>
    		</return>
    		</b2b:processRequestResponse>
    		</soapenv:Body>
    		</soapenv:Envelope>';
    		// Restore original error handler
    		restore_error_handler();
    		ini_set('display_errors', $displayErrorsOriginalState);
    
    		// Send a fault, if we have one
    		if ($fault) {
    			$soap->fault($fault->faultcode, $fault->faultstring);
    		}
    
    		if (!$this->_returnResponse) {
    			echo $this->_response;
    			return;
    		}
    
    		return $this->_response;
    	}
    
    	public function handle($request = null)
    	{
    
    		if (null === $request) {
    			$request = file_get_contents('php://input');
    		}
    
    		// Set Zend_Soap_Server error handler
    		$displayErrorsOriginalState = $this->_initializeSoapErrorContext();
    
    		$setRequestException = null;
    		/**
    		 * @see Zend_Soap_Server_Exception
    		 */
    
    		try {
    			$this->_setRequest($request);
    		} catch (Zend_Soap_Server_Exception $e) {
    			$setRequestException = $e;
    		}
    
    		$soap = $this->_getSoap();
    
    		$fault = false;
    		ob_start();
    		if ($setRequestException instanceof Exception) {
    			// Create SOAP fault message if we've caught a request exception
    			$fault = $this->fault($setRequestException->getMessage(), 'Sender');
    		} else {
    			try {
    				$soap->handle($this->_request);
    			} catch (Exception $e) {
    				$fault = $this->fault($e);
    			}
    		}
    		$this->_response = ob_get_clean();
    
    
    		// Custom response
    		$doc = new DOMDocument();
    		libxml_use_internal_errors(true);
    		$doc->loadHTML($request);
    		libxml_clear_errors();
    		$xml = $doc->saveXML($doc->documentElement);
    		$xml = simplexml_load_string($xml);
    
    		$parameters = $xml->body->envelope->body->processrequest->parameter;
    
    		// $arrResponse = simplexml_load_string($request);
    
    		//print_r($p1); die();
    
    		$this->_response =  '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:b2b="http://b2b.mobilemoney.mtn.zm_v1.0/">
    		<soapenv:Header/>
    		<soapenv:Body>
    		<b2b:processRequestResponse>
    		<return>
    		<name>'.$parameters[0]->name.'</name>
    		<value>'.$parameters[0]->value.'</value>
    		</return>
    		<return>
    		<name>'.$parameters[8]->name.'</name>
    		<value>'.$parameters[8]->value.'</value>
    		</return>
    		<return>
    		<name>StatusDesc</name>
    		<value></value>
    		</return>
    		<return>
    		<name>ThirdPartyAcctRef</name>
    		<value>'.$parameters[2]->value.'</value>
    		</return>
    		<return>
    		<name>Token</name>
    		<value></value>
    		</return>
    		</b2b:processRequestResponse>
    		</soapenv:Body>
    		</soapenv:Envelope>';
    		// Restore original error handler
    		restore_error_handler();
    		ini_set('display_errors', $displayErrorsOriginalState);
    
    		// Send a fault, if we have one
    		if ($fault) {
    			$soap->fault($fault->faultcode, $fault->faultstring);
    		}
    
    		if (!$this->_returnResponse) {
    			echo $this->_response;
    			return;
    		}
    
    		return $this->_response;
    	}
    
    }