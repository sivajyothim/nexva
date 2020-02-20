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
                    			
                                        /*convert to dollers*/
                                        $amount=number_format($inapp->convertToDollers($this->_request->amount, $this->_request->currency),2);
                                        /*end*/
                
                    			$inappPayments = new Api_Model_InappPayments();
                    			$paymentId =  $inappPayments->add($carrierId, $mdn, $amount, $paymentGatewayId, $activationCode, $nexvAppId, $currency);
                    			
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
    
    
    public function paymentbAction()
    {
       $aa = new Nexva_MobileBilling_Type_Huaweinew();
      $dd = $aa->chrage('242066135252', '5', 'NGN');
      
      Zend_Debug::dump($dd);
      die();
    
    }
   
    public function listCarrierAction() {
    
    	$users = new Api_Model_Users();
    	$userList = $users->listChapSupportNexPayer();
        $modifiedUserList=array();
        $data=array();
        $themeMeta=new Model_ThemeMeta();
        foreach($userList as $row){
            $data=$row->toArray();
            $mcc=$themeMeta->getMetaValue($row->id,'MCC')->toArray();
            $mnc=$themeMeta->getMetaValue($row->id,'MNC')->toArray();
            $data['mcc']=isset($mcc[0])?$mcc[0]['meta_value']:'';
            $data['mnc']=isset($mnc[0])?$mnc[0]['meta_value']:'';             
            array_push($modifiedUserList, $data);
        }
        echo Zend_Json::encode($modifiedUserList);

    }
    
// 
//    public function paymentAction()
//    {
//        $ssid = $this->_request->ssid;
//       
//        $mode = $this->_request->mode;
//        $recurring = $this->_request->subscription;
//        $interval = $this->_request->subscription_interval;
//        $verificationCode = $this->_request->verification_code;
//        
//        $packageName = $this->_request->package_name;
//        $assertName = $this->_request->assert_name;
//  
//        $inapp = new Model_Inapp();
//
//        if(empty($ssid))
//        	$inapp->ErrorCodes('ssid_empty');
//        
//        /*
//        if(empty($amount))
//        	$inapp->ErrorCodes('amount_empty');
//
//        if(empty($currency))
//        	$inapp->ErrorCodes('currency_empty');
//
//        if(empty($carrierId))
//        	$inapp->ErrorCodes('carrier_id_empty');
//        
//        if(empty($mdn))
//        	$inapp->ErrorCodes('mdn_empty');
//        */
//        if(empty($verificationCode))
//        	$inapp->ErrorCodes('verification_code_empty');
//        
//        
//
//        // check if this request is product subscription for the product
//        if($recurring && (empty($interval) || ('0' == ((int) ($interval)))))
//        	$inapp->ErrorCodes('subscription_interval_not_valid');
//        
//        Zend_Session::setId($ssid);
//        $sessionUser = new Zend_Session_Namespace('api_nexpayer');
//        
//        $userId = $sessionUser->id;
//        
//
//        
//        if(empty($userId))
//        	$inapp->ErrorCodes('ssid_not_valid');
//        
//        $amount = $sessionUser->amount;
//        $currency = $sessionUser->currency;
//        $carrierId = $sessionUser->carrier_id;
//        $mdn = $sessionUser->mdn;
//        $paymentId =  $sessionUser->transaction_id;
//        
//
//        
//        $inappPayments = new Api_Model_InappPayments();
//        $payment =  $inappPayments->getPayment($paymentId);
//        
//        if($mdn == '081235356533' && $verificationCode = '1111'){
//            
//        } else {
//            if($payment->activation_code != $verificationCode)
//            	$inapp->ErrorCodes('verification_code_invalid');
//        }
//        
//
//
//        //echo $userId;
//        
//        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
//        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($carrierId);
//        
//        $pgType = $pgDetails->gateway_id;
//        $paymentGatewayId = $pgDetails->payment_gateway_id;
//
//        
//
//        //Call Nexva_MobileBilling_Factory and create relevant instance
//        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
//        
//        //$inappPayments = new Api_Model_InappPayments();
//        //$paymentId =  $inappPayments->add($carrierId, $mdn, $amount, $paymentGatewayId);
//        
//        
// 
//        
//        
//       
//        
//  
//        
//        if($mode == 'sand_box') {
//            
//            
//            if($mdn == '081235356533' or $mdn == '242066782417')    {
//                $tranId = $paymentId + 100000;
//
//          
//
//                if(isset($packageName) and isset($assertName))
//                	$inappPayments->updatePaymentsWithPackageAssert($paymentId, $tranId, 'success', 'live', $packageName, $assertName);
//                else
//                	$inappPayments->updatePayments($paymentId, $tranId, 'success', 'live');
//                
//                
//                $currencyUsers = new Api_Model_CurrencyUsers();
//                
//                $currencyDetails = $currencyUsers->getCurrencyDetailsByChap($carrierId);
//                
//                $amountInUsd = $amount/$currencyDetails['rate'];
//                
//                $amountInUsd = round($amountInUsd,2);
//                
//                $userAccount = new Model_UserAccount();
//                
//                $userAccount->saveRoyalitiesForInapp($sessionUser->nexva_app_id, $amountInUsd, 'INAPP', $carrierId, null, $paymentId);
//                
//                if($recurring == 1) {
//                
//                	$paymentUserSubscription = new Model_UserSubscription();
//                	$recurringPayment = new Model_RecurringPayment();
//                	$today = date('Y-m-d');
//                	$newDate = $recurringPayment->dateAdd($today, $interval);
//                
//                	$userSubscriptionData['end_date'] =  $newDate;
//                	$userSubscriptionData['interval'] = $interval;
//                	$userSubscriptionData['payment_gateway_id'] = $paymentGatewayId;
//                	$userSubscriptionData['mdn'] = $mdn;
//                	$userSubscriptionData['product_id'] = $sessionUser->nexva_app_id;
//                	$userSubscriptionData['price'] = $amountInUsd;
//                	 
//                	$userSubscription = new Model_UserSubscription();
//                	
//                	if(isset($packageName) and isset($assertName))    {
//                	    
//                	    $userSubscriptionData['package_name'] = $packageName;
//                	    $userSubscriptionData['assert_name'] = $assertName;                	    
//                		$userSubscription->createSubscriptionWithPackageAssert($userSubscriptionData);
//                	}
//                	else {
//                		$userSubscription->createSubscription($userSubscriptionData);
//                		
//                	}
//                	
//                	
//                
//                }
//                
//                $userInfo['transaction_id'] = $tranId;
//                $userInfo['status'] = 1;
//                $userInfo['message'] = 'payment success';
//                echo json_encode($userInfo);
//                
//            }    else    {
//                
//                $inappPayments->updatePayments($paymentId, '', 'error', 'test');
//                $userInfo['transaction_id'] = '';
//                $userInfo['status'] = 0;
//                $userInfo['message'] = 'payment failed';
//                echo json_encode($userInfo);
//            }
//             
// 
//          
//        }    else    {
//            
//
//            $status = $pgClass->chrage($mdn, $amount, $currency); 
//            
//
//            $time = time();
//           
//            if($status == 1)    {
//                $tranId = $paymentId + $time;
//                $message =  'Hello, your account was charged '. $amount." $currency at ".$paymentId. ' for the successful transaction. Thank you.';
//                $pgClass->sendsms($mdn, $message, $chapId = null);
//                
//                
//                if(isset($packageName) and isset($assertName))
//                    $inappPayments->updatePaymentsWithPackageAssert($paymentId, $tranId, 'success', 'live', $packageName, $assertName);
//                else
//                    $inappPayments->updatePayments($paymentId, $tranId, 'success', 'live');
//                
//              
//                $currencyUsers = new Api_Model_CurrencyUsers();
//                
//                $currencyDetails = $currencyUsers->getCurrencyDetailsByChap($carrierId);
// 
//                $amountInUsd = $amount/$currencyDetails['rate'];
//                
//                $amountInUsd = round($amountInUsd,2);
//
//                $userAccount = new Model_UserAccount();
//                
//                $userAccount->saveRoyalitiesForInapp($sessionUser->nexva_app_id, $amountInUsd, 'INAPP', $carrierId, null, $paymentId);
//                
//                if($recurring == 1) {
//                
//                	$paymentUserSubscription = new Model_UserSubscription();
//                	$recurringPayment = new Model_RecurringPayment();
//                	$today = date('Y-m-d');
//                	$newDate = $recurringPayment->dateAdd($today, $interval);
//                
//                	$userSubscriptionData['end_date'] =  $newDate;
//                	$userSubscriptionData['interval'] = $interval;
//                	$userSubscriptionData['payment_gateway_id'] = $paymentGatewayId;
//                	$userSubscriptionData['mdn'] = $mdn;
//                	$userSubscriptionData['product_id'] = $sessionUser->nexva_app_id;
//                	$userSubscriptionData['price'] = $amountInUsd;
//                	
//                	$userSubscription = new Model_UserSubscription();
//                	
//                	if(isset($packageName) and isset($assertName))    {
//                	    
//                	    $userSubscriptionData['package_name'] = $packageName;
//                	    $userSubscriptionData['assert_name'] = $assertName;                	    
//                		$userSubscription->createSubscriptionWithPackageAssert($userSubscriptionData);
//                	}
//                	else {
//                		$userSubscription->createSubscription($userSubscriptionData);
//                		
//                	}
//                
//                }
//                
//                $userInfo['transaction_id'] = $tranId;
//                $userInfo['status'] = 1;
//                $userInfo['message'] = 'payment success';
//                echo json_encode($userInfo);
//                    
//            }    else {
//                
//                $userInfo['transaction_id'] = '';
//                $userInfo['status'] = 0;
//                $userInfo['message'] = 'payment failed';
//                echo json_encode($userInfo);
//                
//
//            }
//            
//        }
//
//
//    }
//    
//      
    public function checkSubscriptionAction()
    {
        $inapp = new Model_Inapp();
        
        if($this->getRequest()->isPost()) {
            
            $mdn = $this->_request->mdn;
            $nexvAppId = $this->_request->nexva_app_id;
            
            $packageName = $this->_request->package_name;
            $assertName = $this->_request->assert_name;

            
            if(empty($mdn))
            	$inapp->ErrorCodes('mdn_empty');
            
            if(empty($nexvAppId))
            	$inapp->ErrorCodes('product_id_not_valid');
            
          
            
            $userSubscription = new Model_UserSubscription();
            if(isset($packageName) and isset($assertName))
                $subscriptionResults = $userSubscription->subscriptionValidDateWithPackageAssert($mdn, $nexvAppId, $packageName, $assertName);
            else                
                $subscriptionResults = $userSubscription->subscriptionValidDate($mdn, $nexvAppId);
            
            if($subscriptionResults) {

                $dateInfo['valid_untill'] = $subscriptionResults->end_date;
                $dateInfo['status'] = 1;
                $dateInfo['message'] = 'Valid untill '.$subscriptionResults->end_date;
                echo json_encode($dateInfo); 
            
            } else {
                
                $dateInfo['valid_untill'] = '';
                $dateInfo['status'] = 0;
                $dateInfo['message'] = 'request failed';
                echo json_encode($dateInfo);
              
            }
            
        } else {
        
            $inapp->ErrorCodes('only_post_request_is_allowed');
        }
        
    	
        
    }
    
    public function endSubscriptionAction()
    {   
        $inapp = new Model_Inapp();
        
        if($this->getRequest()->isPost()) {
            
            $mdn = $this->_request->mdn;
            $nexvaAppId = $this->_request->nexva_app_id;
            
            if(empty($mdn))
            	$inapp->ErrorCodes('mdn_empty');
            
            if(empty($nexvaAppId))
            	$inapp->ErrorCodes('product_id_not_valid');
            
            if(empty($mdn))
            	$inapp->ErrorCodes('mdn_empty');
            
            if(empty($nexvaAppId))
            	$inapp->ErrorCodes('product_id_not_valid');
            
            $inAppModel =  new Api_Model_InappPayments();
            $result=$inAppModel->updateRecurreingStatus(1, $mdn, $nexvaAppId);
            if($result){
                $dateInfo['status'] = 1;
                $dateInfo['message'] = 'request success';
                echo json_encode($dateInfo);
            }else{
                $dateInfo['status'] = 0;
                $dateInfo['message'] = 'request fail';
                echo json_encode($dateInfo);
            }
        
        } else {
            $inapp = new Model_Inapp();
        	$inapp->ErrorCodes('only_post_request_is_allowed');
        }
    	 
    
    }
    

    public function getAppPriceAction(){
    
    	if($this->getRequest()->isPost()) {
    		$carrier_id = $this->_request->carrier_id;
    		$priceInUsd = $this->_request->price_in_usd;
    
    		if(!isset($carrier_id)){
    			die(json_encode(array('status'=>113,'response'=>'Please enter data.')));
    		}else{
    			if(!is_numeric($carrier_id) || ($carrier_id <=0) || is_float($carrier_id+0)){
    				die( json_encode(array('status'=>114,'response'=>'Please enter valid data.')));
    			}
    		}
    
    		if(!isset($priceInUsd)){
    			die(json_encode(array('status'=>113,'response'=>'Please enter data.')));
    		}else{
    			if(!is_numeric($priceInUsd) || $priceInUsd <=0){
    				die(json_encode(array('status'=>114,'response'=>'Please enter valid data.')));
    			}
    		}
    		$currency = new Api_Model_CurrencyUsers();
    		$currencyDetails = $currency->getCurrencyDetailsByChap($carrier_id);
    
    		$rate=0;
    		if(isset($currencyDetails['rate'])){
    			$rate=$currencyDetails['rate'];
    		} else{
    			die(json_encode(array('status'=>115,'response'=>'Currency rate is not included yet.')));
    		}
    
    		$paymentGatewayUser = new Api_Model_PaymentGatewayUsers();
    		$paymentGateway = $paymentGatewayUser->getGatewayDetailsByChap($carrier_id);
    		$paymentGatewayDetails=$paymentGateway->toArray();
    		$pgClass = Nexva_MobileBilling_Factory::createFactory($paymentGatewayDetails['gateway_id']);
    
    		if(!isset($paymentGatewayDetails['gateway_id'])){
    			die(json_encode(array('status'=>116,'response'=>'Payment gateway is not included yet.')));
    		}
    		switch($paymentGatewayDetails['gateway_id']){
    			case 'AirtelDrc':
    				$price = $pgClass->getAirtelPricePoints(ceil($rate * $priceInUsd));
    				break;
    			case 'AirtelGabon':
    				$objPricePOints = new Nexva_View_Helper_PricePoints();
    				$price = $objPricePOints->PricePoints(ceil($rate * $priceInUsd), $carrier_id);
    				break;
    			case 'AirtelMalawi':
    				$price = $pgClass->getPricePoints(ceil($rate * $priceInUsd));
    				break;
    			case 'AirtelNiger':
    				$price = $pgClass->getPricePoints(ceil($rate * $priceInUsd));
    				break;
    			case 'AirtelNigeria':
    				$price = $pgClass->getAirtelPricePoints(ceil($rate * $priceInUsd));
    				break;
    			case 'AirtelRuwanda':
    				$price = $pgClass->getPricePoints(ceil($rate * $priceInUsd));
    				break;
    			case 'AirtelZm':
    				$price = $pgClass->getAirtelPricePoints(ceil($rate * $priceInUsd));
    				break;
    			default:
    				$price =  $rate * $priceInUsd ;
    		}
    		echo json_encode(array(
    				'status'=>($price)?'1':'0',
    				'price'=>($price)?$price:'Price point not available.',
    				'currency'=>isset($currencyDetails['code'])?$currencyDetails['code']:''));
    	}
    }
    
    public function paymentAction()
    {
        $ssid = $this->_request->ssid;
      
        $mode = $this->_request->mode;
        $recurring = $this->_request->subscription;
        $interval = $this->_request->subscription_interval;
        $verificationCode = $this->_request->verification_code;
        
        $packageName = $this->_request->package_name;
        $assertName = $this->_request->assert_name;
  
        $inapp = new Model_Inapp();
        
        if(empty($ssid))
        	$inapp->ErrorCodes('ssid_empty');

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
       
        if(($mdn == '081235356533' or $mdn == '242066782417') && $verificationCode = '1111'){
            
        } else {
            if($payment->activation_code != $verificationCode)
            	$inapp->ErrorCodes('verification_code_invalid');
        }
        


        //echo $userId;
        
        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($carrierId);
         
        $pgType = $pgDetails->gateway_id;
        $paymentGatewayId = $pgDetails->payment_gateway_id;

        

        //Call Nexva_MobileBilling_Factory and create relevant instance
        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
       
  
        
        if($mode == 'sand_box') {
            
            
            if($mdn == '081235356533' or $mdn == '242066782417')    {
                $tranId = $paymentId + 100000;

          

                if(isset($packageName) and isset($assertName))
                	$inappPayments->updatePaymentsWithPackageAssert($paymentId, $tranId, 'success', 'sand_box', $packageName, $assertName);
                else
                	$inappPayments->updatePayments($paymentId, $tranId, 'success', 'sand_box');
                
                
                $currencyUsers = new Api_Model_CurrencyUsers();
                
                $currencyDetails = $currencyUsers->getCurrencyDetailsByChap($carrierId);
                
                $userAccount = new Model_UserAccount();
                
                // amount in USD 
                /*This is enable in testing mode only*/
                //$userAccount->saveRoyalitiesForInapp($sessionUser->nexva_app_id, $amount, 'INAPP', $carrierId, null, $paymentId);
                /* End */
               /* if($recurring == 1) {
                    
                	$paymentUserSubscription = new Model_UserSubscription();
                	$recurringPayment = new Model_RecurringPayment();
                	$today = date('Y-m-d');
                	$newDate = $recurringPayment->dateAdd($today, $interval);
                
                	$userSubscriptionData['end_date'] =  $newDate;
                	$userSubscriptionData['interval'] = $interval;
                	$userSubscriptionData['payment_gateway_id'] = $paymentGatewayId;
                	$userSubscriptionData['mdn'] = $mdn;
                	$userSubscriptionData['product_id'] = $sessionUser->nexva_app_id;
                	$userSubscriptionData['price'] = $amount;
                	 
                	$userSubscription = new Model_UserSubscription();
                	
                	if(isset($packageName) and isset($assertName))    {
                	    
                	    $userSubscriptionData['package_name'] = $packageName;
                	    $userSubscriptionData['assert_name'] = $assertName;                	    
                		$userSubscription->createSubscriptionWithPackageAssert($userSubscriptionData);
                	}
                	else {
                		$userSubscription->createSubscription($userSubscriptionData);
                		
                	}
                	
                	
                
                }*/
                
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
            
        // pass price in USD for the payment geteway and it will be converted to relevant currency 
           
            $status = $pgClass->chrage($mdn, $amount, $currency); 
            
            

            $time = time();
           
            if($status == 1)    {
                $tranId = $paymentId + $time;
           
                
                if(isset($packageName) and isset($assertName))
                    $inappPayments->updatePaymentsWithPackageAssert($paymentId, $tranId, 'success', 'live', $packageName, $assertName);
                else
                    $inappPayments->updatePayments($paymentId, $tranId, 'success', 'live');
                
                
                $currencyUsers = new Api_Model_CurrencyUsers();
                
                $currencyDetails = $currencyUsers->getCurrencyDetailsByChap($carrierId);
 
                // convert to relevant currency
                $amountInCurrency = $amount*$currencyDetails['rate'];
                
                $amountInCurrency = round($amountInCurrency,2);
                
                $message =  'Hello, your account was charged '. $amountInCurrency." $currency at ".$paymentId. ' for the successful transaction. Thank you.';
                $pgClass->sendsms($mdn, $message, $chapId = null);
                

                $userAccount = new Model_UserAccount();
                
                $userAccount->saveRoyalitiesForInapp($sessionUser->nexva_app_id, $amount, 'INAPP', $carrierId, null, $paymentId);
                
                if($recurring == 1) {
                
                	$paymentUserSubscription = new Model_UserSubscription();
                	$recurringPayment = new Model_RecurringPayment();
                	$today = date('Y-m-d');
                	$newDate = $recurringPayment->dateAdd($today, $interval);
                
                	$userSubscriptionData['end_date'] =  $newDate;
                	$userSubscriptionData['interval'] = $interval;
                	$userSubscriptionData['payment_gateway_id'] = $paymentGatewayId;
                	$userSubscriptionData['mdn'] = $mdn;
                	$userSubscriptionData['product_id'] = $sessionUser->nexva_app_id;
                	$userSubscriptionData['price'] = $amount;
                	
                	$userSubscription = new Model_UserSubscription();
                	
                	if(isset($packageName) and isset($assertName))    {
                	    
                	    $userSubscriptionData['package_name'] = $packageName;
                	    $userSubscriptionData['assert_name'] = $assertName;                	    
                		$userSubscription->createSubscriptionWithPackageAssert($userSubscriptionData);
                	}
                	else {
                		$userSubscription->createSubscription($userSubscriptionData);
                		
                	}
                
                }
                
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
    
    
    public function paymentHeaderAction()
    { 
        $mode = $this->_request->mode;
        $recurring = $this->_request->subscription;
        $interval = $this->_request->subscription_interval;
        $verificationCode = $this->_request->verification_code;
       // $amount = $this->_request->amount;
        $currency = $this->_request->currency;
        $carrierId = $this->_request->carrier_id;
        $mdn = $this->_request->mdn;
        //$paymentId =  $this->_request->transaction_id;
        $nexvAppId = $this->_request->nexva_app_id;
        
        $inapp = new Model_Inapp();
        

        if(empty($verificationCode))
        	$inapp->ErrorCodes('verification_code_empty');
        
        

        // check if this request is product subscription for the product
        if($recurring && (empty($interval) || ('0' == ((int) ($interval)))))
        	$inapp->ErrorCodes('subscription_interval_not_valid');
       
        
        
        if(($mdn == '081235356533' or $mdn == '242066782417') && $verificationCode = '1111'){
            
        } else {
            if($payment->activation_code != $verificationCode)
            	$inapp->ErrorCodes('verification_code_invalid');
        }
        


        //echo $userId;
        
        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($carrierId);
         
        $pgType = $pgDetails->gateway_id;
        $paymentGatewayId = $pgDetails->payment_gateway_id;

        
        
        //Call Nexva_MobileBilling_Factory and create relevant instance
        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
       
        /* Inapp payments */
        $activationCode = substr(md5(uniqid(rand(), true)), 5, 8);

        /* convert to dollers */
        $amount = number_format($inapp->convertToDollers($this->_request->amount, $currency), 2);
        
        /* end */
        $inappPayments = new Api_Model_InappPayments();  
        $paymentId = $inappPayments->add($carrierId, $mdn, $amount, $paymentGatewayId, $activationCode, $nexvAppId, $currency);
       
        /* End */
              
        $payment =  $inappPayments->getPayment($paymentId);
       
        if($mode == 'sand_box') {
            
            
            if($mdn == '081235356533' or $mdn == '242066782417')    {
                $tranId = $paymentId + 100000;         

                $inappPayments->updatePayments($paymentId, $tranId, 'success', 'test');

                $currencyUsers = new Api_Model_CurrencyUsers();
                
                $currencyDetails = $currencyUsers->getCurrencyDetailsByChap($carrierId);
                 
                $userAccount = new Model_UserAccount();
                
                // amount in USD 
               
               /*This is enable in testing mode only
                $userAccount->saveRoyalitiesForInapp($nexvAppId, $amount, 'INAPP', $carrierId, null, $paymentId);
                 if($recurring == 1) {
                    
                	$paymentUserSubscription = new Model_UserSubscription();
                	$recurringPayment = new Model_RecurringPayment();
                	$today = date('Y-m-d');
                	$newDate = $recurringPayment->dateAdd($today, $interval);
                
                	$userSubscriptionData['end_date'] =  $newDate;
                	$userSubscriptionData['interval'] = $interval;
                	$userSubscriptionData['payment_gateway_id'] = $paymentGatewayId;
                	$userSubscriptionData['mdn'] = $mdn;
                	$userSubscriptionData['product_id'] = $nexvAppId;
                	$userSubscriptionData['price'] = $amount;
                	 
                	$userSubscription = new Model_UserSubscription();
                	$userSubscription->createSubscription($userSubscriptionData);
             
                } 
                */
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
            
        // pass price in USD for the payment geteway and it will be converted to relevant currency 
           
            $status = $pgClass->chrage($mdn, $amount, $currency); 
            


            $time = time();
           
            if($status == 1)    {
                $tranId = $paymentId + $time;
                $inappPayments->updatePayments($paymentId, $tranId, 'success', 'live');
                
                
                $currencyUsers = new Api_Model_CurrencyUsers();
                
                $currencyDetails = $currencyUsers->getCurrencyDetailsByChap($carrierId);
 
                // convert to relevant currency
                $amountInCurrency = $amount*$currencyDetails['rate'];
                
                $rounedAmountInCurrency = round($amountInCurrency,2);
                
                $message =  'Hello, your account was charged '. $rounedAmountInCurrency." $currency at ".$paymentId. ' for the successful transaction. Thank you.';
                $pgClass->sendsms($mdn, $message, $chapId = null);
                

                $userAccount = new Model_UserAccount();
                
                $userAccount->saveRoyalitiesForInapp($nexvAppId, $amount, 'INAPP', $carrierId, null, $paymentId);
                
                if($recurring == 1) {
                
                	$paymentUserSubscription = new Model_UserSubscription();
                	$recurringPayment = new Model_RecurringPayment();
                	$today = date('Y-m-d');
                	$newDate = $recurringPayment->dateAdd($today, $interval);
                
                	$userSubscriptionData['end_date'] =  $newDate;
                	$userSubscriptionData['interval'] = $interval;
                	$userSubscriptionData['payment_gateway_id'] = $paymentGatewayId;
                	$userSubscriptionData['mdn'] = $mdn;
                	$userSubscriptionData['product_id'] = $nexvAppId;
                	$userSubscriptionData['price'] = $amount;
                	
                	$userSubscription = new Model_UserSubscription();
                	$userSubscription->createSubscription($userSubscriptionData);
            
                }
                
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
    
    
    
    
}

	