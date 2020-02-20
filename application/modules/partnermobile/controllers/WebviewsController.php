<?php

class Partnermobile_WebviewsController extends Nexva_Controller_Action_Partnermobile_MasterController {
	
    public function indexAction()
    {
		$this->_helper->layout()->disableLayout(); 
		
        if($this->_getParam('token')){  
            $token = $this->_getParam('token', null);
            $paymentId = $this->_getParam('payment_id', null);
            $chapId = $this->_getParam('chap_id', null);
            $sessionId = $this->_getParam('session_id', null);
	
            //echo $token.'###'.$paymentId.'###'.$chapId.'###'.$sessionId.'###';
            //Get payment gateway Id of the CHAP            
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
            $pgType = $pgDetails->gateway_id; 
            $paymentGatewayId = $pgDetails->payment_gateway_id;
            $pgClassName = $pgType;

            //Call Nexva_MobileBilling_Factory and create relevant instance. Since this is a redirection payment, this factory doesn't contain the payment codes
            $pgClass = Nexva_PaymentGateway_Factory::factory($pgType,$pgClassName);
        
            //Select the relevent payment records
            $purchasedAppDetails = $pgClass->selectInteropPayment($sessionId, $paymentId);

            $price = $purchasedAppDetails->price;
            
            
            //Convert the price to the local price
            $currencyUserModel = new Api_Model_CurrencyUsers();
            $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
            $currencyRate = $currencyDetails['rate'];
            $currencyCode = $currencyDetails['code'];
            $price = ceil($currencyRate * $price);
        
            //echo $token.'###'.$paymentId.'###'.$chapId.'###'.$sessionId.'###'.$price;
            //die();
            
            $purchaseref = $pgClass->getEnc($paymentId.'#APP#'.$sessionId);
			
			
			$this->view->appId = $purchasedAppDetails->app_id;
			$this->view->buildId = $purchasedAppDetails->build_id;	
					
			$this->view->token = $token;
			$this->view->sessionId = $sessionId;
			$this->view->purchaseref = $purchaseref;
			$this->view->price = $price;
            
		}
		
		//echo 123;
		//die();
	}
	
	function getEnc($string) {

        $key = 'neXva.inc.2014';
        // initialization vector 
        $iv = md5(md5($key));
        $output = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, $iv);
        $output = base64_encode($output);
        return $output;
    }

    function getDec($string) {

        $key = 'neXva.inc.2014';
        // initialization vector 
        $iv = md5(md5($key));
        $output = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, $iv);
        $output = rtrim($output, "");
        return $output;
    }
}