<?php
/**
 * This class is used to manage MTN
 */
class Nexva_MobileBilling_Type_Huawei extends Nexva_MobileBilling_Type_Abstract 
{
    
    public function __construct() {
        
      
    }
    
    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
        //Charging code, which is relevant to the contract of the charged party.  [ Static code ]   = code 
        $error = '';
        
        $currency = new Api_Model_Currencies();
        $currencyRate = $currency->getCurrencyRate('NGN');

        
        $rate = $currencyRate->rate;

        
        //$url = 'http://api.mobilereloaded.com/huaweingn/paynow';
        $url = 'http://api.nexva.com/huaweingn/paynow';
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
         
        $fields = array(
        		'price' => urlencode($price),
        		'mobile_no' => urlencode($mobileNo),
        		'rate' => urlencode($rate),
        		'currency' => urlencode('NGN'),
        		'app_id' => urlencode($appId)
        );
        

         
        $fields_string = '';
         
        //url-ify the data for the POST
        foreach($fields as $key=>$value) {
        	$fields_string .= $key.'='.$value.'&';
        }
         
        rtrim($fields_string, '&');
         
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        
        //echo curl_exec($ch);
        //die();
        
        
        $status = json_decode(curl_exec($ch));
        
        
        $buildUrl = '';
        
        
        if($status->status == 1) {
        
        //Get the S3 URL of the Relevant build
        $productDownloadCls = new Nexva_Api_ProductDownload();
        $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
        
        $timeStamp = date("Ymd").date("His");
        	    
        $paymentTimeStamp = date('YmdHis');
        $paymentTransId = 'MTN '. $appId.'-'.$timeStamp;
        $paymentResult = 'Success';
        	    //Update the relevant Transaction record in the DB
        parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
        
    
        
        }
        return $buildUrl;

    }
    
    function sendsms($mobileNo, $message, $chapId = null)
    {
    
    	// $URL =  'http://api.mobilereloaded.com/huaweingn/sendsms/mobile_no/2347060553995/message/test';
    	//$URL =  'http://api.mobilereloaded.com/huaweingn/sendsms';
    	$URL =  'http://api.nexva.com/huaweingn/sendsms';
    	 
    	$ch = curl_init($URL);
    	    
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_VERBOSE, 0);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    	 
    	$fields = array(
    			'mobile_no' => $mobileNo,
    			'message' => $message.' MTN NIGERIA APP STORE'
    	);
    	
 

    	curl_setopt($ch,CURLOPT_POST, count($fields));
    //	curl_setopt($ch,CURLOPT_POSTFIELDS,	http_build_query($fields));
    	curl_setopt($ch,CURLOPT_POSTFIELDS,	$fields);
    	
    	$status = json_decode(curl_exec($ch));
    	
	
    	return $status;
    	 
    	//Zend_Debug::dump($xml);
    	//Zend_Debug::dump($mobileNo);
    	//Zend_Debug::dump($message);
    	//curl_close($ch);
    	 
    	//die();
    	 
    }
    
    
    public function chrage($mobileNo, $price, $currency)
    {
    	//Charging code, which is relevant to the contract of the charged party.  [ Static code ]   = code
    	$error = '';
    

    
    	//$url = 'http://api.mobilereloaded.com/huaweingn/paynow';
    	$url = 'http://api.nexva.com/huaweingn/paynow';
    	$ch = curl_init($url);
    
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	 
    	$appId = '11111001';
    	$rate = 1;
    	
    	$fields = array(
    			'price' => urlencode($price),
    			'mobile_no' => urlencode($mobileNo),
    			'rate' => urlencode($rate),
    			'currency' => urlencode('NGN'),
    			'app_id' => urlencode($appId)
    	);
    
    	$fields_string = '';
    	 
    	//url-ify the data for the POST
    	foreach($fields as $key=>$value) {
    		$fields_string .= $key.'='.$value.'&';
    	}
    	 
    	rtrim($fields_string, '&');
    	 
    	curl_setopt($ch,CURLOPT_POST, count($fields));
    	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    
    	//Zend_Debug::dump( json_decode(curl_exec($ch)), 'dddd');
    	
    	//return  curl_exec($ch);
//     	//die('ddd');
    
  //  return 1;
    	$status = json_decode(curl_exec($ch));

    
    	if($status->status == 1) {
            return 1;
    	}
    	
    	return 0;
    
    }

    
}
