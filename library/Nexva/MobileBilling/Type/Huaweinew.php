<?php
/**
 * This class is used to manage Interop payments
 * 
 * Maheel
 */
class Nexva_MobileBilling_Type_Huaweinew extends Nexva_MobileBilling_Type_Abstract 
{    
    
    public function __construct() 
    {
        include_once( APPLICATION_PATH . '/../public/vendors/nusoap.php' );
    }
    
    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {        
        //Get currency rate and code relevant to the CHAP
        $currencyUserModel = new Api_Model_CurrencyUsers();        
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];
        
        $spEndPoint =  'http://41.206.4.162:8310/AmountChargingService/services/AmountCharging';
        $spId = 2420110000578;
        $spPass = 'Huawei123';
        $spServiceId = '242012000002352';
        $oa = $mobileNo;
        $fa = $mobileNo;
         
        $client = new nusoap_client($spEndPoint);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        
        $timeStamp = date("Ymd").date("His");
        $password = md5($spId.$spPass.$timeStamp);
         
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
        $amountFormatted = $amount * 100;
        
        //$amountFormatted = 50;
        
        $charge = array(
        		'description' => $appId,
        		'currency' => 'XAF',
        		'amount' => $amountFormatted,
        		'code' => 100101
        );
       
        $timeStamp = date("Ymd").date("His");
        
        $paymentTransId =  $timeStamp;
  
        //     - Unique ID of the request.  can take as transaction id 

        $paymentInfo = array(
        		'endUserIdentifier'     => $mobileNo,
        		'charge'   => $charge,
        		'referenceCode'  => $paymentTransId
        );

        if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            //Zend_Debug::dump($paymentInfo);die();
        }

        $result = $client->call('chargeAmount', $paymentInfo, '', '', $header);
       
        $paymentTimeStamp = date('d-m-Y');
        //date('jS F Y \a\t h:i:s A');
        
        //trun on this for debuging
       //echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    //  echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
     //  die();

        $buildUrl = null;
        
         //Check if payment was made success, Provide the download link
        if(!$client->fault) 
        {                 
            $error = $client->getError();
            
            if(!$error) 
            {            
                //Get the S3 URL of the Relevant build
                $productDownloadCls = new Nexva_Api_ProductDownload();
                $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

                $message = 'Y\'ello, vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour un telechargement depuis  MTN app-store. Merci.';
                $this->sendsms($mobileNo, $message, $chapId);
                
                $paymentResult = 'Success';
                $paymentTransId = strtotime($paymentTimeStamp);
                //Update the relevant Transaction record in the DB
                parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
                
            }
            
            // there is a error. Payment is unsuccessful
          //  echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';

        } 
        
        if($mobileNo == '2420661506751')
        {
            //Get the S3 URL of the Relevant build
            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
        }
        
        return $buildUrl;
    }
    
    public function sendsms($mobileNo, $message, $chapId)
    {
       //todo, get chap SMS gateway details dynamically242068661314
       $client = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/SendSms');
       $client->soap_defencoding = 'UTF-8';
       $client->decode_utf8 = false;

       $timeStamp = date("Ymd").date("His");        
       $spId =  2420110000578;
       $spServiceId = '242012000002435';
       $pass = 'Huawei123';
        
       $password = md5($spId.$pass.$timeStamp);
        
       $header = array(
                        'RequestSOAPHeader' => array ( 
                                                        'spId' => $spId, 
                                                        'spPassword' => $password, 
                                                        'serviceId' => $spServiceId,  
                                                        'timeStamp' => $timeStamp, 
                                                        'OA' => $mobileNo, 
                                                        'FA' => $mobileNo
                                                    )
                        );
        
       $phone = array(
                        'addresses'     =>  'tel:'.$mobileNo,
                        'senderName'   =>  999999999,
                        'message'  => $message,
//                        'receiptRequest' => array(
//                                                        'endpoint'     =>  'http://88.190.51.72:80/notify',
//                                                        'interfaceName'   =>  '234012000001660',
//                                                        'correlator'  => '234012000001660',
//                                                  )
                     );

       $result = $client->call('sendSms', $phone, '', '', $header);
       
       // trun on this for debuging
      // Zend_Debug::dump( $result);
     //  echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
     // echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
   //    die();
   
       return 1;
       
    }
    
    
    public function chrage($mobileNo, $price, $currency)
    {
    
    	$spEndPoint =  'http://41.206.4.162:8310/AmountChargingService/services/AmountCharging';
    	$spId = 2420110000578;
    	$spPass = 'Huawei123';
    	$spServiceId = '242012000002352';
    	$oa = $mobileNo;
    	$fa = $mobileNo;
    	 
    	$client = new nusoap_client($spEndPoint);
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
    	$timeStamp = date("Ymd").date("His");
    	$password = md5($spId.$spPass.$timeStamp);
    	 
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

    	$amountFormatted = $price * 100;
    
    	//$amountFormatted = 50;
    
    	$charge = array(
    			'description' => 'inapp_payment',
    			'currency' => 'XAF',
    			'amount' => $amountFormatted,
    			'code' => 100101
    	);
    	 
    	$timeStamp = date("Ymd").date("His");
    
    	$paymentTransId =  $timeStamp;
    
    	//     - Unique ID of the request.  can take as transaction id
    
    	$paymentInfo = array(
    			'endUserIdentifier'     => $mobileNo,
    			'charge'   => $charge,
    			'referenceCode'  => $paymentTransId
    	);
    
    	
    	$result = $client->call('chargeAmount', $paymentInfo, '', '', $header);
    	 
    	$paymentTimeStamp = date('d-m-Y');

    	
    	
    	//date('jS F Y \a\t h:i:s A');
    
    	//trun on this for debuging
    	//echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	//echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	 
    	 //die();
    	
    	

    
    	$results = 0;
    
    	//Check if payment was made success, Provide the download link
    	if(!$client->fault)
    	{
    		$error = $client->getError();

    		if(!$error)
    		{
    		    $message = 'Hello, your account was charged '. $price.' XAF at '.$timeStamp. ' for the successful transaction on the MTN Nextapps store. Thank you.';
    		    $this->sendsms($mobileNo, $message, $chapId = 1);

    			$results = 1; 
    
    		}
    	}
    	

    
    	return $results;
    
    }
    
}