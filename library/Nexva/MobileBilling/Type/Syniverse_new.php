<?php
/**
 * This class is used to manage MTN
 */
class Nexva_MobileBilling_Type_Syniverse extends Nexva_MobileBilling_Type_Abstract 
{
    
    public function __construct() {
        
      
    }
    
    
    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
        
        
        
        
        
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
