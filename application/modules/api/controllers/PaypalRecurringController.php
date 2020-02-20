<?php

class Api_PaypalRecurringController extends Zend_Controller_Action {

    private $paypalRecurringObj=null;
    
    public function init() {
        $this->paypalRecurringObj = new Nexva_PaypalRecurring_PaypalRecurring();
    }
    public function requestPaypemtAction(){
       
        $this->paypalRecurringObj->requestPayment();
    }
    
    public function doInitialPaymentAction(){ 
        $this->paypalRecurringObj->doInitialPayment();
    }
    
    public function doSubcriptionPaymentAction(){
        $this->paypalRecurringObj->doSubcriptionPayment();
    }
}
