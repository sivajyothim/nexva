<?php
/**
 * This class is used to return build URL since the payment is redirection
 * 
 * Rooban
 */
class Nexva_MobileBilling_Type_PaymentwidgetYcoins extends Nexva_MobileBilling_Type_Abstract 
{    
    public function __construct() 
    {
        
    }
    
    public function doPayment($chapId, $buildId, $appId, $mobileNo = null, $appName, $price)
    {             
        //Get currency rate and code relevant to the CHAP
        $currencyUserModel = new Api_Model_CurrencyUsers();        
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];
    
        //For Iran, they need the amount without decimal
        $amount = ceil($currencyRate * $price);

        $timeStamp = date("Ymd").date("His");
        
        $paymentTransId =  $timeStamp;
       
        $paymentTimeStamp = date('d-m-Y');

        $buildUrl = null;
        
         //Get the S3 URL of the Relevant build
        $productDownloadCls = new Nexva_Api_ProductDownload();
        $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

        //$paymentResult = 'Success';
        //$paymentTransId = strtotime($paymentTimeStamp);
        //Update the relevant Transaction record in the DB
        //parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
                
        return $buildUrl;
    }
    
}