<?php


class Api_HuaweingnController extends Zend_Controller_Action
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
        $rate = $this->_request->rate;
        $currency = $this->_request->currency;
        $appId = $this->_request->app_id;
        
        $mobileNo = str_pad($mobileNo, 1, '0', STR_PAD_LEFT);
        

        if(!empty($price)) {
        
        } else {
           $this->__echoError('1001', 'Price is empty');
        }
        

        if($mobileNo) {
        
        } else {
        	 $this->__echoError('1002', 'Mobile No is empty');
        }
        
        if(empty($rate))
        	$this->__echoError('1003', 'Rate is empty');
        
        $validator = new Zend_Validate_Alnum();
        if($validator->isValid($currency)) {
        
        } else {
        	$this->__echoError('1004', 'Currency is empty');
        }
        
        $validator = new Zend_Validate_Alnum();
        if($validator->isValid($appId)) {
      
        } else {
        	$this->__echoError('1005', 'App id is empty');
        }
        
        $spEndPoint =  'http://41.206.4.162:8310/AmountChargingService/services/AmountCharging';
        $spId = 2340110000426;
        $spPass = 'nexT321';
        //$spServiceId = '234012000001923';
        $spServiceId = '234012000001927';
        $oa = $mobileNo;
        $fa = $mobileNo;
         
        $client = new nusoap_client($spEndPoint);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        
        $timeStamp = date("Ymd").date("His");
        $password = md5($spId.$spPass.$timeStamp);
         
        $this->_header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => $spServiceId, 'timeStamp' => $timeStamp, 'OA' => $oa, 'FA' => $fa));
        
        $error = '';
        
        $amount = ceil($rate * $price);
        
        
        $charge = array(
        		'description' => $appId,
        		'currency' => 'NGN',
        		'amount' => $amount * 100,
        		'code' => 10090
        );
       
        $timeStamp = date("Ymd").date("His");
        
        $paymentTransId =  $timeStamp;
         
        // referenceCode - Unique ID of the request.  can take as transaction id 

        $paymentInfo = array(
        		'endUserIdentifier'     => $mobileNo,
        		'charge'   => $charge,
        		'referenceCode'  => $paymentTransId
        );
        
        $result = $client->call('chargeAmount', $paymentInfo, '', '', $this->_header);
        
    //    Zend_Debug::dump( $result);
  
        
        //trun on this for debuging
    //    echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
   //     echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        


        $buildUrl = '';
        
        if($client->fault) {
            // there is a error. Payment is unsuccessful
            //echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';

        } else {
        	$error = $client->getError();
        	if($error) {
        	    
        	    // there is a error. Payment is unsuccessful
        	    //$this->_client->getDebug();
        	} else {
        	    //Get the S3 URL of the Relevant build

        	    
        	  //  $paymentTimeStamp = date('YmdHis');
        	    
        	    $paymentTimeStamp = date("d-M-Y H:i");

        	    $paymentResult = 'Success';
        	    
        	    $client1 = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/SendSms');
        	    $client1->soap_defencoding = 'UTF-8';
        	    $client1->decode_utf8 = false;
        	     
        	    $timeStamp = date("Ymd").date("His");
        	    
        	    $spId =  2340110000426;
        	    $pass = 'nexT321';
        	    
        	    $password = md5($spId.$pass.$timeStamp);
        	    
        	    $header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001929',  'timeStamp' => $timeStamp, 'OA' => $mobileNo, 'FA' => $mobileNo));
        	    
        	    $phone = array(
        	    		'addresses'     =>  'tel:'.$mobileNo,
        	    		'senderName'   =>  99999998,
        	    		'message'  => 'Hello, your account was charged '. $amount.' NGN at '.$paymentTimeStamp. ' for the successful transaction on the MTN Nextapps store. Thank you.'
        	    );
        	    
        	    $result = $client1->call('sendSms', $phone, '', '', $header);
        	    
        	    
        	    $this->__echoJson(array('status' => 1), 1);
        	    
        	    
        	}
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

       $client = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/SendSms');
       $client->soap_defencoding = 'UTF-8';
       $client->decode_utf8 = false;

       $timeStamp = date("Ymd").date("His");
        
       $spId =  2340110000426;
       $pass = 'nexT321';
        
       $password = md5($spId.$pass.$timeStamp);
        
       $header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001929',  'timeStamp' => $timeStamp, 'OA' => $mobileNo, 'FA' => $mobileNo));
        
       $phone = array(
       		'addresses'     =>  'tel:'.$mobileNo,
       		'senderName'   =>  99999998,
       		'message'  => $message
       );
       

       $result = $client->call('sendSms', $phone, '', '', $header);
       
       
       
       // trun on this for debuging
       //Zend_Debug::dump( $result);
    //   echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
   //   echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
       
       if($client->fault) {
       	// there is a error. Payment is unsuccessful
       
       } else {
       	$error = $client->getError();
       	if($error) {
       		 
       		// there is a error. Payment is unsuccessful
       		//$this->_client->getDebug();
       	} else {
       	    
       	    $this->__echoJson(array('status' => 1), 1);
       	    
       	    }
       	}
       
       $this->__echoJson(array('status' => 0), 1);
        
       
       //die();
   }
   
   
   public function curlAction()
   {
       
      // $URL =  'http://api.mobilereloaded.com/huaweingn/sendsms/mobile_no/2347060553995/message/test';
       $URL =  'http://api.mobilereloaded.com/huaweingn/sendsms';
       
       $ch = curl_init($URL);
        
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_HEADER, 0);
       
       $fields = array(
       		'mobile_no' => urlencode(2347060553995),
       		'message' => urlencode('test')
       );
       
       $fields_string = '';
       
       //url-ify the data for the POST
       foreach($fields as $key=>$value) {
       	$fields_string .= $key.'='.$value.'&';
       }
       
       rtrim($fields_string, '&');
       
       curl_setopt($ch,CURLOPT_POST, count($fields));
       curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        
        
       $xml = json_decode(curl_exec($ch));
       
       Zend_Debug::dump($xml->status);
       curl_close($ch);
       
       die();
       
       
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

