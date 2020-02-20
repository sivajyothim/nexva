<?php
/**
 * This class is used to manage MTN
 */
class Nexva_MobileBilling_Type_Syniverse extends Nexva_MobileBilling_Type_Abstract 
{
    
    public function __construct() {
        
        include_once( APPLICATION_PATH.'/../public/vendors/nusoap.php' );
        
    	$urlProduction = 'http://pmg-acg-billing.syniverse.com/pmgxml/services/premiumcharging';
    	
    	$this->_client = new nusoap_client($urlProduction);
    	$this->_client->soap_defencoding = 'UTF-8';
    	$this->_client->decode_utf8 = false;
    	 
    	$this->_user = array('aggregatorId' => 1423, 'pwd' => 'n3x41A99', 'version' => '1.0');
    
    }
    
    
    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
        
        $amount = 100 * $price;
        
        $amount = str_pad($amount, 3, '0', STR_PAD_LEFT);
        
        $param = array(
        		'user'     => $this->_user,
        		'min'     => $mobileNo,
        		'productId'  => 'nexva_nt_'.$amount,
        		'shortCode' => '6171',
        		'carrierId' => 404,
        		'chargeId' => $appId.date("Ymd").date("His"),
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

        $buildUrl = null;
        
        if($result['errorCode'] == 0) {
            
            //Get the S3 URL of the Relevant build
            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
             
            $paymentTimeStamp = date('YmdHis');
            $paymentTransId = $appId.'-'.$result['transactionId'];
            $paymentResult = 'Success';

            //Update the relevant Transaction record in the DB
            parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
         

            
        }
        

        return $buildUrl;
        
   
      
    }
     
    
    
    
}