<?php


class Api_SyniversecwwController extends Zend_Controller_Action
{
    public function init()
    {
        include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );
        
	
    }
    
    public function paynowAction()
    {
        //Charging code, which is relevant to the contract of the charged party.  [ Static code ]   = code 
        $price = $this->_request->price;
        $mobileNo = $this->_request->mobile_no;
        $currency = $this->_request->currency;
        $appId = $this->_request->app_id;
        
        if(!empty($price)) {
        
        } else {
           $this->__echoError('1001', 'Price is empty');
        }
        
        $validator = new Zend_Validate_Alnum();
        if($validator->isValid($mobileNo)) {
        
        } else {
        	 $this->__echoError('1002', 'Mobile No is empty');
        }
        
        
        $validator = new Zend_Validate_Alnum();
        if($validator->isValid($appId)) {
      
        } else {
        	$this->__echoError('1005', 'App id is empty');
        }

        
    	$urlProduction = 'http://pmg-acg-billing.syniverse.com/pmgxml/services/premiumcharging';
    	
    	$this->_client = new nusoap_client($urlProduction);
    	$this->_client->soap_defencoding = 'UTF-8';
    	$this->_client->decode_utf8 = false;
    	 
    	$this->_user = array('aggregatorId' => 1423, 'pwd' => 'n3x41A99', 'version' => '1.0');
    	
    	
    	$amount = 100 * $price;
    	
    	$timestanp = $appId.date("Ymd").date("His");
        
        $amount = str_pad($amount, 3, '0', STR_PAD_LEFT);
        
        $param = array(
        		'user'     => $this->_user,
        		'min'     => '1'.$mobileNo,
        		'productId'  => 'nexva_nt_'.$amount,
        		'shortCode' => '6171',
        		'carrierId' => 404,
        		'chargeId' => $timestanp,
        		'msgtxid' => 'NOID'
        );
        
        $result = $this->_client->call('chargeSubscriber', $param);
        
        if ($this->_client->fault) {
        	//Zend_Debug::dump($result, 'Fault');
        } else {
        	$err = $this->_client->getError();
        	if ($err) {
        		//Zend_Debug::dump($err, 'Error');
        	} else {
        	//	Zend_Debug::dump($result, 'Result');
        	}
        }
        
        //echo '<pre>' . htmlspecialchars($this->_client->response, ENT_QUOTES) . '</pre>';
        
        // turn on for debug
        /*
        echo '<h2>Request</h2>';
        echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2>';
        echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2>';
        
        
        if ($err) {
        	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
        	echo '<h2>Debug</h2>';
        	echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
        	 
        }
        
        die();
        Zend_Debug::dump($client->getDebug());
        
        */
        
        //Zend_Debug::dump($result);
   

        
        if($result['errorCode'] == 0) {
            

           $message = 'Hello, your account was charged USD '. $price.' at '.$timestanp. ' for the successful transaction on the CWW App store. Thank you.';
           $fields_string = '';
       
            $fields = array(
       		    'to' => urlencode($mobileNo),
       		    'text' => urlencode($message)
            );
       
            //url-ify the data for the POST
            foreach($fields as $key=>$value) {
       	    $fields_string .= $key.'='.$value.'&';
            }
       
            rtrim($fields_string, '&');
       
            $url =  'http://208.109.189.54:13013/cgi-bin/sendsms?username=foo1&password=bar&from=1155&'.$fields_string;

            $ch = curl_init($url);
       
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
       
            $status = curl_exec($ch);
        
            curl_close($ch);
            
            $this->__echoJson(array('status' => 1), 1);
            

        }

          $this->__echoJson(array('status' => 0), 1);
        

    }
     
    
   public function sendsmsAction()
   {
       
       $message = $this->_request->message;
       $mobileNo = $this->_request->mobile_no;

       
       $validator = new Zend_Validate_Alnum();
       if($validator->isValid($mobileNo)) {
       
       } else {
       	$this->__echoError('1002', 'Mobile No is empty');
       }
    
       if(empty($message))
           $this->__echoError('1003', 'Message is empty');
       
       $fields_string = '';
       
       $fields = array(
       		'to' => urlencode($mobileNo),
       		'text' => urlencode($message)
       );
       
       //url-ify the data for the POST
       foreach($fields as $key=>$value) {
       	$fields_string .= $key.'='.$value.'&';
       }
       
       rtrim($fields_string, '&');
       
       //Center 1 
      // http://mobilereloaded.com:13013/cgi-bin/sendsms?&username=foo1&password=bar&to=5569570515&text=Helloworld&from=6171
       
       ////Center 2
       //http://mobilereloaded.com:13013/cgi-bin/sendsms?&username=foo2&password=bar&to=5569570515&text=Helloworld&from=6171

       
       $url =  'http://208.109.189.54:13013/cgi-bin/sendsms?username=foo1&password=bar&from=1155&'.$fields_string;

       $ch = curl_init($url);
       
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_HEADER, 0);
        

       //curl_setopt($ch,CURLOPT_POST, count($fields));
       //curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
       
       $status = curl_exec($ch);
        
       curl_close($ch);
      
        
       if($status == '0: Accepted for delivery') {

       	    $this->__echoJson(array('status' => 1), 1);
       	    
       }

       $this->__echoJson(array('status' => 0), 1);
        
       // trun on this for debuging
       //  Zend_Debug::dump( $result);
       //echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
       //echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
       //die();
   }
   

   private function __echoError($errNo, $errMsg)
   {
       $msg = json_encode(array("message" =>$errMsg,"error_code" => $errNo));
   	   $this->getResponse()->setHeader('Content-type', 'application/json');
   	   echo $msg; die();
   }
   
   
   
   private function __echoJson($array, $halt=1) 
   {
       $this->getResponse()->setHeader('Content-type', 'application/json');
       echo json_encode($array);
       if($halt) die();
   }
    
    
}

