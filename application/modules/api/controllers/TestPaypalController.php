<?php

class Api_TestPaypalController extends Zend_Controller_Action {
    
        public function init() 
    {
        parent::init(); 
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
        
        
            
    }

    public function sendRecurringPaymentRequestAction() {
        
    
       
        if (isset($this->_request->currency_code) && !empty($this->_request->currency_code)) {
            $currencyCode = $this->_request->currency_code;
        } else {
            return json_encode(array(
                "message" => "The Currency code doesn't set."
            ));
        }

        if (isset($this->_request->product_price) && !empty($this->_request->product_price)) {
            if ((is_float($this->_request->product_price) || is_numeric($this->_request->product_price))) {
                $paypal = new Zend_Session_Namespace('paypal');
                $paypal->amt = $this->_request->product_price;                
            } else {
                return json_encode(array(
                    "message" => "The Product price should be integer or float value"
                ));
            }
        } else {
            return json_encode(array(
                "message" => "The Product price doesn't set."
            ));
        }
     
        if (isset($this->_request->email) && !empty($this->_request->email)) {
            
                $paypal->email = 'sujith@nexva.com';
           
        } else {
            return json_encode(array(
                "message" => "The Email doesn't set."
            ));
        }

        if (isset($this->_request->user_id) && !empty($this->_request->user_id)) {
            
                $paypal->user_id = $this->_request->user_id;
           
        } else {
            return json_encode(array(
                "message" => "The Email doesn't set."
            ));
        }


        $param = array(
            'currency_code' => $currencyCode,
            'product_name' => 'ecorret',
            'product_id' => '12'
        );
        

        $paypal = new Nexva_TestPaypalRecurring_PaypalConnect();
        $status=$paypal->sendPaymentRequest($param);
    
        
        switch ($status['status']){
            case "Success":
                $url='https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . urlencode($status['token']); 
                $this->_redirect($url);
            case "Failure":
                $this->_redirect(Zend_Registry::get('config')->paypal->test->recurring->sandbox->error);
            default :
                $this->_redirect(Zend_Registry::get('config')->paypal->test->recurring->sandbox->error);
        }
    }

    public function conformRecurringPaymentAction() {
        $paypal = new Nexva_TestPaypalRecurring_PaypalConnect();
        $status=$paypal->conformPayment();
        switch ($status[1]){
            case "Success":                
                    $this->_redirect('http://ecarrot.nexva.com/');                
            default :
                $this->_redirect('http://ecarrot.nexva.com/');
                
        }
    }

}
