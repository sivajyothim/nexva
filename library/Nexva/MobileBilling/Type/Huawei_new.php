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
        
        $price = $this->_request->price;
        $mobileNo = $this->_request->mobile_no;
        $rate = $currencyRate->rate;
        $appId = $this->_request->app_id;
        
        $url = 'http://api.mobilereloaded.com/huaweingn/paynow';
        
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
        
        
        $status = json_decode(curl_exec($ch));
         
        //Zend_Debug::dump($xml);
        
        curl_close($ch);
        
        
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
        
        return $buildUrl;
        
        }
        

    }
    
    function sendSms($mobileNo, $message, $chapId)
    {
    
       $url =  'http://api.mobilereloaded.com/huaweingn/sendsms';
       
       $ch = curl_init($url);
        
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_HEADER, 0);
       
       $fields = array(
       		'mobile_no' => urlencode($mobileNo),
       		'message' => urlencode($message." MTN NIGERIA APP STORE")
       );
       
       $fields_string = '';
       
       //url-ify the data for the POST
       foreach($fields as $key=>$value) {
       	$fields_string .= $key.'='.$value.'&';
       }
       
       rtrim($fields_string, '&');
       
       curl_setopt($ch,CURLOPT_POST, count($fields));
       curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        
       $status = json_decode(curl_exec($ch));
   
       curl_close($ch);
       
       return $status->status;
       
    }
       

    
}
