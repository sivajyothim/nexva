<?php

class Api_NexpayerController extends Zend_Controller_Action {
    
    
    
    public function init() {
        
        include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );
        
        /* Initialize actionNexpayer controller here */
        $this->_helper->layout ()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
    }
    
    public function authAction() 
    {
        
       $inapp = new Model_Inapp();
        
       if($this->getRequest ()->isPost ()) {

                $userName = $this->_request->username;
                $password = $this->_request->password;
                
                $amount = $this->_request->amount;
                $currency = $this->_request->currency;
                $carrierId = $this->_request->carrier_id;
                $mdn = $this->_request->mdn;
                $nexvAppId = $this->_request->nexva_app_id;

                // if the email format is invalid
                $validator = new Zend_Validate_EmailAddress();
                if(!($validator->isValid($userName)))
                    $inapp->ErrorCodes('invalid_request_email');
           
                // for blank salt even if the user name and password entered
                if(empty($password)) 
                    $inapp->ErrorCodes('invalid_pass');
                
                if(empty($amount))
                	$inapp->ErrorCodes('amount_empty');
                
                if(empty($currency))
                	$inapp->ErrorCodes('currency_empty');
                
                if(empty($carrierId))
                	$inapp->ErrorCodes('carrier_id_empty');
                
                if(empty($mdn))
                	$inapp->ErrorCodes('mdn_empty');
                
                if(empty($nexvAppId))
                	$inapp->ErrorCodes('product_id_not_valid');
                    

                // if user name, password, enterd
                if(!empty($userName) && !empty($password)) {
                    
                    //Check if email exists
                    $user = new Model_User();
                    $userRow = $user->fetchRow($user->select()->where('email = ?', $userName));
                    
                    if($userRow)
                    {
                    	$userMeta = new Model_UserMeta();
                    	$verified = $userMeta->getAttributeValue($userRow->id, 'VERIFIED_ACCOUNT');
                    
                    	//Check if account has verified
                    	if($verified != '0')
                    	{
                    		$db = Zend_Registry::get('db');
                    		$authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'email', 'password', "MD5(?) AND status=1 AND type='CP'");
                    		$authDbAdapter->setIdentity($userName);
                    		$authDbAdapter->setCredential($password);
                    
                    		$result = Zend_Auth::getInstance()->authenticate($authDbAdapter);
                    
                    		//If validated successfully
                    		if($result->isValid())
                    		{
                    			Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));
                    
                    			//Save last login details
                    			$auth = Zend_Auth::getInstance();
                    			
                    			// get payment gateway details create payment gateway record
                    			$pgUsersModel = new Api_Model_PaymentGatewayUsers();
                    			$pgDetails = $pgUsersModel->getGatewayDetailsByChap($carrierId);
                    			
                    			$pgType = $pgDetails->gateway_id;
                    			$paymentGatewayId = $pgDetails->payment_gateway_id;
                    			
                    			//Call Nexva_MobileBilling_Factory and create relevant instance
                    			$pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
                    			
                    			$activationCode = substr(md5(uniqid(rand(), true)), 5,8);
                    			
                    			$inappPayments = new Api_Model_InappPayments();
                    			$paymentId =  $inappPayments->add($carrierId, $mdn, $amount, $paymentGatewayId, $activationCode);
                    			
                    			//Call Nexva_MobileBilling_Factory and create relevant instance
                    			$pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
                    			
                    			$message = "Your verification code :-  ".$activationCode;
                    			
                    			$pgClass->sendsms($mdn, $message, $chapId = null);
                    			
                    			$sessionUser = new Zend_Session_Namespace('api_nexpayer');
                    			$sessionUser->id = $auth->getIdentity()->id;
                    			$sessionUser->amount = $amount;
                    			$sessionUser->currency = $currency;
                    			$sessionUser->carrier_id = $carrierId;
                    		    $sessionUser->transaction_id = $paymentId;
                    		    $sessionUser->nexva_app_id = $nexvAppId;
                    			$sessionUser->mdn = $mdn;
                    			$sessionId = Zend_Session::getId();
                    			$userInfo['ssid'] = $sessionId;
                    			$userInfo['transaction_id'] = $paymentId;
                    			$userInfo['status'] = 1;
                    			$userInfo['response'] = 'Authenticated';
                    			echo json_encode($userInfo);
                    			
                    		}    else
                    			     $inapp->ErrorCodes('username_or_passwor_is_incorrect');

                    	}    else
                    		    $inapp->ErrorCodes('this_account_has_not_been_verified');

                    }    else
                    	     $inapp->ErrorCodes('user_not_found');
                    
                
                } 
                
       }    else
           $inapp->ErrorCodes('only_post_request_is_allowed');

    }
    
    
    public function paymentAction()
    {


        $ssid = $this->_request->ssid;
       
        $mode = $this->_request->mode;
        $recurring = $this->_request->subscription;
        $interval = $this->_request->subscription_interval;
        $verificationCode = $this->_request->verification_code;
  
        $inapp = new Model_Inapp();

        if(empty($ssid))
        	$inapp->ErrorCodes('ssid_empty');
        
        /*
        if(empty($amount))
        	$inapp->ErrorCodes('amount_empty');

        if(empty($currency))
        	$inapp->ErrorCodes('currency_empty');

        if(empty($carrierId))
        	$inapp->ErrorCodes('carrier_id_empty');
        
        if(empty($mdn))
        	$inapp->ErrorCodes('mdn_empty');
        */
        if(empty($verificationCode))
        	$inapp->ErrorCodes('verification_code_empty');
        
        

        // check if this request is product subscription for the product
        if($recurring && (empty($interval) || ('0' == ((int) ($interval)))))
        	$inapp->ErrorCodes('subscription_interval_not_valid');
        
        Zend_Session::setId($ssid);
        $sessionUser = new Zend_Session_Namespace('api_nexpayer');
        
        $userId = $sessionUser->id;
        
        if(empty($userId))
        	$inapp->ErrorCodes('ssid_not_valid');
        
        $amount = $sessionUser->amount;
        $currency = $sessionUser->currency;
        $carrierId = $sessionUser->carrier_id;
        $mdn = $sessionUser->mdn;
        $paymentId =  $sessionUser->transaction_id;

        
        $inappPayments = new Api_Model_InappPayments();
        $payment =  $inappPayments->getPayment($paymentId);
        
        if($paymentId->activation_code != $verificationCode) 
            $inapp->ErrorCodes('verification_code_invalid');

        //echo $userId;
        
        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($carrierId);
        
        $pgType = $pgDetails->gateway_id;
        $paymentGatewayId = $pgDetails->payment_gateway_id;

        //Call Nexva_MobileBilling_Factory and create relevant instance
        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
        
        //$inappPayments = new Api_Model_InappPayments();
        //$paymentId =  $inappPayments->add($carrierId, $mdn, $amount, $paymentGatewayId);
        
        
        
        if($recurring == 1) {
            
            $paymentUserSubscription = new Model_UserSubscription();
            $recurringPayment = new Model_RecurringPayment();
            $today = date('Y-m-d');
            $newDate = $recurringPayment->dateAdd($today, $interval);
            
            $userSubscriptionData['end_date'] =  $newDate; 
            $userSubscriptionData['interval'] = $interval;
            $userSubscriptionData['payment_gateway_id'] = $paymentGatewayId; 
            $userSubscriptionData['mdn'] = $mdn;
            $userSubscription = new Model_UserSubscription();
            $userSubscription->createSubscription($userSubscriptionData);
        }
        
        
        
        if($mode == 'sand_box') {
            
            if($mdn == '081235356533')    {
                $tranId = $paymentId + 100000;
                $message =  'Hello, your account was charged '. $amount." $currency at ".$paymentId. ' for the successful transaction on the MTN Nextapps store. Thank you.';
                $pgClass->sendsms($mdn, $message, $chapId = null);
                $inappPayments->updatePayments($paymentId, $tranId, 'success', 'test');
                $userInfo['transaction_id'] = $tranId;
                $userInfo['status'] = 1;
                $userInfo['message'] = 'payment success';
                echo json_encode($userInfo);
            }    else    {
                
                $inappPayments->updatePayments($paymentId, '', 'error', 'test');
                $userInfo['transaction_id'] = '';
                $userInfo['status'] = 0;
                $userInfo['message'] = 'payment failed';
                echo json_encode($userInfo);
            }
             
           
          
        }    else    {
            

         
            $status = $pgClass->chrage($mdn, $amount, $currency); 
    
           
            
            if($status == 1)    {
                $tranId = $paymentId + 100000;
                $message =  'Hello, your account was charged '. $amount." $currency at ".$paymentId. ' for the successful transaction on the MTN Nextapps store. Thank you.';
                $pgClass->sendsms($mdn, $message, $chapId = null);
                $inappPayments->updatePayments($paymentId, $tranId, 'success', 'live');
                
                $userAccount = new Model_UserAccount();
                $userAccount->saveRoyalitiesForInapp($mdn, $amount, $paymentMethod='INAPP', $carrierId, $carrierId, $currency);
                
                $userInfo['transaction_id'] = $tranId;
                $userInfo['status'] = 1;
                $userInfo['message'] = 'payment success';
                echo json_encode($userInfo);
                    
            }    else {
                
                $userInfo['transaction_id'] = '';
                $userInfo['status'] = 0;
                $userInfo['message'] = 'payment failed';
                echo json_encode($userInfo);
                

            }
            
        }



        

    
    }
    
    
    public function checkSubscriptionAction()
    {
        $inapp = new Model_Inapp();
        
        if($this->getRequest()->isPost()) {
            
            $mdn = $this->_request->mdn;
            $nexvAppId = $this->_request->nexva_app_id;
            
            if(empty($mdn))
            	$inapp->ErrorCodes('mdn_empty');
            
            if(empty($nexvAppId))
            	$inapp->ErrorCodes('product_id_not_valid');
            

            
        } else {
        
            $inapp->ErrorCodes('only_post_request_is_allowed');
        }
        
    	
        
    }
    
    public function endSubscriptionAction()
    {   
        $inapp = new Model_Inapp();
        
        if($this->getRequest()->isPost()) {
            
            $mdn = $this->_request->mdn;
            $nexvAppId = $this->_request->nexva_app_id;
            
            if(empty($mdn))
            	$inapp->ErrorCodes('mdn_empty');
            
            if(empty($nexvAppId))
            	$inapp->ErrorCodes('product_id_not_valid');
        
        } else {
            $inapp = new Model_Inapp();
        	$inapp->ErrorCodes('only_post_request_is_allowed');
        }
    	 
    
    }
    
    
    
}

	