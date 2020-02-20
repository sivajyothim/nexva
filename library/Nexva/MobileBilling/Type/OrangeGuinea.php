<?php
/**
 * This class is used to return build URL since the payment is redirection
 * 
 * Rooban
 */
class Nexva_MobileBilling_Type_OrangeGuinea extends Nexva_MobileBilling_Type_Abstract 
{    
    public function __construct() 
    {
        
    }
    
    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
        
    retrun;

     

    }

    public function sendsms($mobileNo, $message, $chapId)
    {
        
        
     $msg = '{
	"outboundSMSMessageRequest": {
		"address": "tel:+'.$mobileNo.'",
		"outboundSMSTextMessage": {
			"message": "'.$message.'"
		},
		  "senderAddress":"tel:+224624245911",
          "senderName":"Orange"
	}
}';
     


         
          $url =  "https://iosw-rest.orange.com:443/PDK/SMS_Wrapper-1/smsmessaging/v1/outbound/tel:+224624245911/requests";
    
        //  $url =   "https://iosw3sn-rest.orange.com:8443/PDK/BE_API-1";
        $cert_file = APPLICATION_PATH . '/configs/orangeappstore.pem';
        $cert_password = "abc123";
    
        $ch = curl_init();
    
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
             
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
            CURLOPT_RETURNTRANSFER        => true,
            CURLOPT_HEADER        => true,
            CURLOPT_VERBOSE        => true,
            CURLOPT_URL => $url ,
            CURLOPT_SSLCERT => $cert_file ,
            CURLOPT_SSLCERTPASSWD => $cert_password ,
        );
    
    
        
        
     curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-OAPI-Application-Id: b2b-orangestore-72c67501t2',
            'X-OAPI-Contact-Id: b2b-orangestore-72c67501t2',
            'X-OAPI-Resource-Type: SMS_OSM',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($msg))
        );
        
    
        curl_setopt_array($ch , $options);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
    
    
        $output = curl_exec($ch);
    
        if(!$output)
        {
          //  echo "Curl Error : " . curl_error($ch);
        }
        else
        {
/*            // echo 'ddd';
            $info = curl_getinfo($ch);
      print_r($info['request_header']);
            Zend_Debug::dump($output);
             
             die();  */
            
          
        }
    
        return true;
    
    }
    
    
    
    public function testsms()
    {
        
    }
    
}