<?php
/**
 * Abstract class to manage Direct Mobile Billing payment gateways
 * 
 * Maheel
 */
abstract class Nexva_MobileBilling_Type_Abstract
{    
    public $_paymentId = null;
    
    /**
     * Manages payment process
     * @param $chapId Chap Id
     * @param $appId App Id 
     * @param $mobileNo Mobile No
     * @param $appName Application name
     * @param $price price
     */
    abstract public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price);
     
    /**
     * Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
     * @param $appId App Id
     * @param $chapId Chap Id
     * @param $buildId Build Id
     * @param $mobileNo Mobile No
     * @param $price App price(transaction amount)
     * @param $paymentGatewayId payment gateway id
     */
    public function addMobilePayment($chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId, $smsVerificationToken = null)
    {
    
        $interopPaymentsModel = new Api_Model_InteropPayments();

        $paymentId = $interopPaymentsModel->addInteropPayment($chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId, $smsVerificationToken);

        $this->_paymentId = $paymentId;
        
        //Added for Airtel Nigeria payment process
        return $this->_paymentId;
    }
    

    public function UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl, $request = null, $response = null)
    {

     
        $interopPaymentsModel = new Api_Model_InteropPayments();
        $interopPaymentsModel->updateInteropPayment($this->_paymentId, $paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl, $token = null, $request = null, $response = null);
    }
    
    
    
    /**
     * Update the relevant Transaction record in the DB
     * @param $paymentTimeStamp payment time stamp
     * @param $paymentTransId payment trans Id
     * @param $paymentResult payment result
     * @param $buildUrl build url
     */
    /*
    public function UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl)
    {
        $interopPaymentsModel = new Api_Model_InteropPayments();
        $interopPaymentsModel->updateInteropPayment($this->_paymentId, $paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
    }
    
    */
    
    /**
     * Select the relevant Transaction record from the DB
     * @param $paymentTimeStamp payment time stamp
     */
    public function selectInteropPayment($sessionId, $interopPaymentId)
    {
        $interopPaymentsModel = new Api_Model_InteropPayments();
        $purchaseRecord = $interopPaymentsModel->selectInteropPayment($sessionId, $interopPaymentId);
        return $purchaseRecord;
    }
    
    /**
     * Update the relevant Transaction record in the DB
     * @param $paymentTimeStamp payment time stamp
     * @param $paymentTransId payment trans Id
     * @param $paymentResult payment result
     * @param $buildUrl build url
     * @param $paymentId payment id
     */
    public function updateInteropPayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl, $paymentId)
    {
        $interopPaymentsModel = new Api_Model_InteropPayments();
        $interopPaymentsModel->updateInteropPayment($paymentId, $paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
    }
}