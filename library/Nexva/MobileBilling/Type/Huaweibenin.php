<?php
/**
 * This class is used to manage MTN
 */
class Nexva_MobileBilling_Type_Huaweibenin extends Nexva_MobileBilling_Type_Abstract 
{
    
    public function __construct() {
        
      
    }
    
 public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
                
        ini_set('default_socket_timeout', 300);
        
        include_once( APPLICATION_PATH . '/../public/vendors/nusoap.php' );
        
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];

        //testbed
        //$spEndPoint =  'http://41.206.4.219:8310/AmountChargingService/services/AmountCharging';
        
        //live
        $spEndPoint =  'http://41.206.4.162:8310/AmountChargingService/services/AmountCharging';
        
        $client = new nusoap_client($spEndPoint);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        
        //testbed
        $spId =  2290110003718;
        $pass = 'Huawei123';
        
        $spId =  2290110003718;
        $pass = 'Huawei123';
        
        $spId =  2290110006039;
        $pass = 'Huawei123';
        
        //live
       // $spId =  2290110002440;
        //$pass = 'Huawei123';
        //$spId = 2290110001939;
        //$pass = 'Huawei123';
        
        $timeStamp = date("Ymd").date("His");
        $password = md5($spId.$pass.$timeStamp);
        
        $spServiceId = '';
        $oa = $mobileNo;
        $fa = $mobileNo;

        $header = array(
                            'RequestSOAPHeader' => array ( 
                                                            'spId' => $spId, 
                                                            'spPassword' => $password, 
                                                            'serviceId' => $spServiceId, 
                                                            'timeStamp' => $timeStamp, 
                                                            'OA' => $oa, 
                                                            'FA' => $fa
                                                         )
                          );
        
        $error = '';
        
        $amount = ceil($currencyRate * $price); 
        //$amount = 10; 
        $amountFormatted = $amount * 100;
        
        //$amountFormatted = 50;
        
        $charge = array(
        		'description' => $appId,
        		'currency' => 'XOF',
        		'amount' => $amountFormatted,
        		'code' => 100101
        );
       
        
        $paymentTransId =  $timeStamp;
  
        //     - Unique ID of the request.  can take as transaction id 

        $paymentInfo = array(
        		'endUserIdentifier'     => $mobileNo,
        		'charge'   => $charge,
        		'referenceCode'  => $paymentTransId
        );


        $result = $client->call('chargeAmount', $paymentInfo, '', '', $header);

        /*Zend_Debug::dump($result);die();

        $err = $client->getError();
        if ($err) {
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
        }

*/
        
        /*
        echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
       echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
*/
         //    die('aa');
        $amount = ceil($currencyRate * $price);
        $paymentTimeStamp = date('d-m-Y');
        
        $paymentTimeStamp = date('Y-m-d H:i:s');

        $buildUrl = null;

        if(!$client->fault)
        {
            $error = $client->getError();

            if(!$error)
            {
                
               
                //Get the S3 URL of the Relevant build
                $productDownloadCls = new Nexva_Api_ProductDownload();
                $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
            

                //todo, change message language
                $message = 'Y\'ello, vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour un telechargement depuis  MTN app-store. Merci.';
                $this->sendsms($mobileNo, $message, $chapId);

                $paymentResult = 'Success';
                $paymentTransId = strtotime($paymentTimeStamp);
                //Update the relevant Transaction record in the DB
                parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl, serialize($$client->request), serialize($$client->response));
                
                return $buildUrl;
            }
        }
    }
    
    function sendsms($mobileNo, $message, $chapId = null)
    {
    
       include_once( APPLICATION_PATH.'/../public/vendors/nusoap.php' );

       //testbed
    //   $client = new nusoap_client('http://41.206.4.219:8310/SendSmsService/services/SendSms');
       //live
       $client = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/SendSms');
       
       $client->soap_defencoding = 'UTF-8';
       $client->decode_utf8 = false;
        
       $timeStamp = date("Ymd").date("His");
       
       //testbed
       $spId =  2290110003718;
       $pass = 'Huawei123';
       
       //live
       //$spId =  2290110002440;
         
     //  $spId = 2290110001939;
      // $pass = 'Huawei123';
      
       //testbed
       $spId =  2290110006039;
       $pass = 'Huawei123';
       
       $password = md5($spId.$pass.$timeStamp);
        
       $header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'timeStamp' => $timeStamp, 'OA' => $mobileNo, 'FA' => $mobileNo));
        
       $phone = array(
       		'addresses'     =>  'tel:'.$mobileNo,
       		'senderName'   =>  'Nexvaapp',
       		'message'  => $message
       );
       

       $result = $client->call('sendSms', $phone, '', '', $header);
       
  //  echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
   //   echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES)  . '</pre>';
  //  echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
  //   Zend_Debug::dump($result);
      
  ///   die();
       
       if($client->fault) {
       	// there is a error. Payment is unsuccessful
           return false;
       } else {
       	$error = $client->getError();
       	if($error) {
       	    
       	    return false;;
       	} else {
       	    
       	    return true;
       	    
       	    }
       	}
       

        
       
       //die();
    	 
    }
    
    
    public function chrage($mobileNo, $price, $currency)
    {
    	
    }

    
}
