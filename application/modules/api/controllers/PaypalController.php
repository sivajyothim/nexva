<?php

class Api_PaypalController extends Zend_Controller_Action {
    
        public function init() 
    {
        parent::init(); 
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
        
        
            
    }

    public function sendPaymentRequestAction() {
        
        if (isset($this->_request->currency_code) && !empty($this->_request->currency_code)) {
            $currencyCode = 'AUD';//$this->_request->currency_code;
        } else {
            return json_encode(array(
                "message" => "The Currency code doesn't set."
            ));
        }

        if (isset($this->_request->product_price) && !empty($this->_request->product_price)) {
            if ((is_float($this->_request->product_price) || is_numeric($this->_request->product_price))) {
                $productPrice = $this->_request->product_price;
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

        if (isset($this->_request->product_id) && !empty($this->_request->product_id)) {
            if ((is_float($this->_request->product_id) || is_numeric($this->_request->product_id))) {
                $productId = $this->_request->product_id;
            } else {
                return json_encode(array(
                    "message" => "The Product id should be integer value"
                ));
            }
        } else {
            return json_encode(array(
                "message" => "The Product id doesn't set."
            ));
        }

        if (isset($this->_request->product_name) && !empty($this->_request->product_name)) {
            $productName = $this->_request->product_name;
        } else {
            return json_encode(array(
                "message" => "The Product name doesn't set."
            ));
        }


        $param = array(
            'currency_code' => $currencyCode,
            'product_price' => $productPrice,
            'product_name' => $productName,
            'product_id' => $productId
        );
        $paypal = new Nexva_Paypal_PaypalConnect();
        $status=$paypal->sendPaymentRequest($param);
        
        
        switch ($status['status']){
            case "Success":
                 $this->_redirect('https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . urlencode($status['token']));
            case "Failure":
                $this->_redirect(Zend_Registry::get('config')->paypal->mobile->sandbox->error);
            default :
                $this->_redirect(Zend_Registry::get('config')->paypal->mobile->sandbox->error);
        }
    }

    public function conformPaymentAction() {
        
        $paypal = new Nexva_Paypal_PaypalConnect();
        $status=$paypal->conformPayment();
        
        switch ($status[1]){
            case "Success":
                
                    $this->_redirect($status[2]);
                
            default :
                $this->_redirect(Zend_Registry::get('config')->paypal->mobile->sandbox->error);
                
        }
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
            
                $paypal->email = $this->_request->email;
           
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
        
     
        $paypal = new Nexva_PaypalRecurring_PaypalConnect();
        $status=$paypal->sendPaymentRequest($param);
    
        
        switch ($status['status']){
            case "Success":
                $url='https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . urlencode($status['token']); 
                $this->_redirect($url);
            case "Failure":
                $this->_redirect(Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->error);
            default :
                $this->_redirect(Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->error);
        }
    }

    public function conformRecurringPaymentAction() {
        $paypal = new Nexva_PaypalRecurring_PaypalConnect();
        $status=$paypal->conformPayment();
        switch ($status[1]){
            case "Success":                
                    $this->_redirect(Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->success);                
            default :
                $this->_redirect(Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->error);
                
        }
    }
    
    public function updatePaypalStatusAction(){
    
        $paypal = new Nexva_PaypalRecurring_PaypalConnect();
        /*get recurring profile details*/
        $paypalModel=new Api_Model_EcarrotRecurringPayment();
        $result = $paypalModel->getAllPaymentDate();
        
        foreach ($result as $row ){   
            $responce = $paypal->getProfileData($row->profile_id);            
            try{
                $date=date_create($responce['NEXTBILLINGDATE']);
                $finalDate = date_format($date,"Y-m-d");
                $paypalModel->updateProfileByProfileId($row->profile_id, $finalDate);                
            } catch (Exception $e){
                die(json_encode(array('error' =>$e->getMessage())));
            }
            die(json_encode(array('success' =>'profile updated successfully.')));
        }
        /*end*/
        
        
    }
}
