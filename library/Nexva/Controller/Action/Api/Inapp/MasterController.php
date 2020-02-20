<?php

class Nexva_Controller_Action_Api_Inapp_MasterController extends Zend_Controller_Action {
    
    public function init() {
        /* Initialize action controller here */
        $this->_helper->layout ()->disableLayout ();
        $this->_helper->viewRenderer->setNoRender ( true );
    }
    
    public function authAction() {
    	
    	$this->_helper->layout ()->disableLayout ();
        $this->_helper->viewRenderer->setNoRender ( true );
        
        if ($this->getRequest ()->isPost ()) {
            

            
            if($this->_request->output == 'json')    {
            
                $inapp = new Model_Inapp ( );
                
                $userName = $this->_request->username;
                $password = $this->_request->password;
                $salt = $this->_request->salt;
                
                // if the email format is invalid
                $validator = new Zend_Validate_EmailAddress();
                if (!($validator->isValid($userName)))
                    $inapp->ErrorCodes('invalid_request_email');
                
                // for blank user name 
                if (empty ( $userName ))
                    $inapp->ErrorCodes('invalid_request_email');
                    
                // for blank salt even if the user name and password entered
                if (! empty ( $userName ) && !empty ( $password ) && empty ( $salt )) 
                    $inapp->ErrorCodes('invalid_salt');
                    

                // if user name, password, salt is enterd
                if (! empty ( $userName ) && ! empty ( $password ) && ! empty ( $salt )) {
                    
                    $userObj = new Model_User ( );
                    $user = $userObj->getUserByEmail ( $userName );
                    $userMeta = new Model_UserMeta ( );
                    
                 
                    if ($user == NULL) {
                        
                        // if the user name not exist even if password and salt is entred
                        $inapp->ErrorCodes('user_not_found');
                    
                    } else {
                    
                        $user = $userObj->getUserByEmail ( $userName );
                        $userMeta = new Model_UserMeta ( );
                        $unClaimAc = $userMeta->getAttributeValue ( $user->id, 'UNCLAIMED_ACCOUNT' );
                    
                        if ($unClaimAc == 1)
                              
                            //if the entered username is unclaimed... authenticate as unclaimed user, even if entered password is wrong or right
                            $inapp->authenticateUnclaimedUser ( $user->id );
                        else
                        
                            //if the entered username is claimed ... authenticate as normal user, with right password and salt 
                            $inapp->authenticateUser ( $userName, $password, $salt );
                    }
                
                } 
    
                else 
    
                {
                    
                    if (! empty ( $userName ) && empty ( $password )) {
                        
                        $userObj = new Model_User ( );
                        $username_check = $userObj->validateUserEmail ( $userName );
                        
                        if (true === $username_check) {
                            
                            //if the user name is entered but with blank password and user name is not exist, create new user set as unclaimed user
                            $inapp->registerUser ( $userName );
                            $userObj = new Model_User ( );
                            $user = $userObj->getUserByEmail ( $userName );
                            $userMeta = new Model_UserMeta ( );
                            $unClaimAc = $userMeta->getAttributeValue ( $user->id, 'UNCLAIMED_ACCOUNT' );
                            
                            if ($unClaimAc == 1)
                                // authenticate above created user as unclaimed user 
                                $inapp->authenticateUnclaimedUser ( $user->id );
                        
                        } 
    
                        else 
    
                        {
                            
                            $userObj = new Model_User ( );
                            $user = $userObj->getUserByEmail ( $userName );
                            $userMeta = new Model_UserMeta ( );
                            $unClaimAc = $userMeta->getAttributeValue ( $user->id, 'UNCLAIMED_ACCOUNT' );
                            if ($unClaimAc == 1) {
                                
                                //if the user name is entered but with blank password and user name exist and if he is unclaimed user then autheticate
                                // as unclaimed user 
                                $inapp->authenticateUnclaimedUser ( $user->id );
                            
                            } else {
                                //if the user name is entered but with blank password and user name exist and if he is claimed user then autheticate
                                // as normal user
                                $inapp->authenticateUser ( $userName, $password, $salt );
                            }
                        
                        }
                    
                    }
                }
            
            }
        
        }
    
    }
    
    
public function paymentAction()
    {
        $this->_helper->layout ()->disableLayout ();
        $this->_helper->viewRenderer->setNoRender ( true );
          
        $inapp = new Model_Inapp ( );
        
        if(empty($this->_request->ssid)) 
           $inapp->ErrorCodes('ssid_empty');
            
        if(empty($this->_request->id))
           $inapp->ErrorCodes('id_empty');
           
        if(empty($this->_request->callback_url))
           $inapp->ErrorCodes('callback_url_empty');
        
        if(empty($this->_request->price))
           $inapp->ErrorCodes('price_empty');
           
        if($this->_request->subscription && (empty($this->_request->subscription_interval) || ('0' ==((int)($this->_request->subscription_interval))))  )
              $inapp->ErrorCodes('subscription_interval_not_valid');
        
        
        if(!empty($this->_request->ssid))   {
        
        Zend_Session::setId($this->_request->ssid);
        $sessionUser = new Zend_Session_Namespace('api_user');
   
        $userId = $sessionUser->id;
        
        $sessionUser->productId = $this->_request->id;
        $sessionUser->callbackUrl = $this->_request->callback_url;
        $sessionUser->subscription = $this->_request->subscription;
        $sessionUser->subscription_interval = $this->_request->subscription_interval;
 
        
        $item_price = $this->_request->price;
        $item_id = $this->_request->id;
        $callback_url = $this->_request->callback_url;
        
        $config = Zend_Registry::get( 'config' );
          
        if($userId)
        {
  
            $config = Zend_Registry::get('config');
            
            if($this->_request->sand_box == '1')   {

                $paymentGateway = Nexva_PaymentGateway_Factory::factory('Paythru', 'PayThru', 1);
                $sessionUser->sand_box = $this->_request->sand_box;
            
            }
            else
            {
                
                $paymentGateway = Nexva_PaymentGateway_Factory::factory('Paythru', 'PayThru');
                $sessionUser->sand_box = 0;

               
            }
            
            $sessionId =  Zend_Session::getId();
                
            $cancel_url  ="http://" . $_SERVER['SERVER_NAME'] . "/inapp/1.0/callback/?ssid=".$sessionId."&status=failed";
       
            $success_url = "http://" . $_SERVER['SERVER_NAME'] . "/inapp/1.0/callback/?ssid=".$sessionId;
            
            $callback_url_pay = "http://" . $_SERVER['SERVER_NAME'] . "/inapp/1.0/update-user-info/?ssid=".$sessionId;
           
            //retrive user info 
    
            $userMeta = new Model_UserMeta();
            $userMeta->setEntityId($userId);
            
            $product = new Model_Product();
            $productVal = $product->getProductValById($this->_request->id);
    
                 if(!empty($productVal->name))   {
                     
                     $vars = array(
                          'currency' => 'USD',
                          'salutation' => $userMeta->TITLE,
                          'first_name' => $userMeta->FIRST_NAME,
                          'surname' => $userMeta->LAST_NAME,
                          'mobile_phone' => $userMeta->MOBILE,
                          'address_1' => $userMeta->ADDRESS,
                          'town' => $userMeta->CITY,
                          'postcode' => $userMeta->ZIPCODE,
                          'version' => 2,
                          'token_uses' => 1,
                          'success_url' => $success_url,
                          'cancel_url' => $cancel_url,
                          'callback_url' => $callback_url_pay,
                          'item_name' => $productVal->name,
                          'item_price' =>   $item_price,
                          'item_reference' => $this->_request->id 
                      );
                   $paymentGateway->Prepare($vars);
                   $paymentGateway->Execute();
                 }
                 else
                 {
                    $inapp->ErrorCodes('product_id_not_valid'); 
                 }
              
        
        }
        else
        {
             $inapp->ErrorCodes('ssid_not_valid');
        }
        
      }
    
    }
    



    
public function callbackAction() {
        
	    $this->_helper->layout ()->disableLayout ();
        $this->_helper->viewRenderer->setNoRender ( true );

           
        $inapp = new Model_Inapp ( );
        
        Zend_Session::setId($this->_request->ssid);
        
        $sessionUser = new Zend_Session_Namespace ( 'api_user' );
        $callbackUrl = $sessionUser->callbackUrl;
        $userId = $sessionUser->id;
        $productId = $sessionUser->productId;
        $subscription = $sessionUser->subscription;
        $subscriptionInterval = $sessionUser->subscription_interval;

      
     
        if (empty ( $this->_request->transkey ) || empty ( $this->_request->ssid )) {
            header ( 'Location: ' . $callbackUrl . '?status=false' );
            die();
        }

        if (empty ( $sessionUser->id )) {
            header ( 'Location: ' . $callbackUrl . '?status=false' );
            die();
        }
        
        $result = file_get_contents ( "http://api.paythru.com/transactions/" . $this->_request->transkey );
        
        if($result == false)    {
            header ( 'Location: ' . $callbackUrl . '?status=false' );
            die();
        }

        // retunrning format status=success&token=s&value=10.00&currency=USD
        parse_str ( $result );
              
        $paymentStatus = $status; // "success" or "cancel" 
        $paymentAmount = $value; 

        if ($paymentStatus == 'cancel') {
            header ( 'Location: ' . $callbackUrl . '?status=false' );
            die();
        }
        
        if($sessionUser->sand_box != '1')   { 
            
             $orders = new Model_Order ( );
            
             // if it is the fisrt time.. it clicks the retruns to nexva link process the payment
            if($orders->chkTransAction($this->_request->transkey)) {
               $orderId = $inapp->processPaymentInapp( $productId, $userId, $this->_request->transkey, $paymentAmount);
               
               if($subscription)    {
                
                $paymentUserSubscription = new Model_UserSubscription ( );
                $recurringPayment = new Model_RecurringPayment ( );
                $today = date('Y-m-d');
                $newDate = $recurringPayment->dateAdd($today,$subscriptionInterval);
                
                $data['order_id'] = $orderId;
                $data['end_date'] = $newDate;
                $data['interval'] = $subscriptionInterval;
                
                $paymentUserSubscription->createSubscription($data);
                
                
                $productMeta = new Model_ProductMeta ( );
                $payment_failed_count = $productMeta->getAttributeValue ( $productId, 'RECURRING_FAIL_COUNT_USER_' . $userId);
                if($payment_failed_count >= 1 )
                   $productMeta->setAttributeValue ( $productId, 'RECURRING_FAIL_COUNT_USER_' . $userId, '0' );
                
               }

                //echo $paymentStatus, ' ', $paymentAmount, ' ', $userId,  ' ',  $productId , ' ', $callbackUrl;
            header ( "location: " . $callbackUrl . "?status=true" );
            }
           

        }
        else
        {   
            //echo $paymentStatus, ' ', $paymentAmount, ' ', $userId,  ' ',  $productId , ' ', $callbackUrl;
           // echo 'this is sandbox call';
             if( $paymentStatus == 'success') {
                header ( "location: " . $callbackUrl . "?status=true&sand_box=1" );
             }
             else
             {
                header ( "location: " . $callbackUrl . "?status=false&sand_box=1" );
             }
        }


        //echo "<script>window.close();</script>";
        
      
      
    }
    
    
   
    
    public function updateUserInfoAction(){
        
    	$this->_helper->layout ()->disableLayout ();
        $this->_helper->viewRenderer->setNoRender ( true );
            
        if(empty($this->_request->ssid))
           die();
        
        Zend_Session::setId($this->_request->ssid);
        $sessionUser = new Zend_Session_Namespace ( 'api_user' );
        $userId = $sessionUser->id;
        
        if(empty($userId))
            die();
        
        $userMeta = new Model_UserMeta ( );
        $unClaimAc = $userMeta->getAttributeValue ( $userId, 'UNCLAIMED_ACCOUNT' );
        if ($unClaimAc == 1)
        
        {
            
            if(!empty($this->_request->user_title))
               $userMeta->setAttributeValue($userId, 'TITLE', $this->_request->user_title);
               
            if(!empty($this->_request->user_first_name))
                $userMeta->setAttributeValue($userId, 'FIRST_NAME', $this->_request->user_first_name);
                
            if(!empty($this->_request->user_surname))
               $userMeta->setAttributeValue($userId, 'LAST_NAME', $this->_request->user_surname);
               
            if(!empty($this->_request->address_address_1))  
                $userMeta->setAttributeValue($userId, 'ADDRESS', $this->_request->address_address_1);
                
            if(!empty($this->_request->user_mobile_number))      
                $userMeta->setAttributeValue($userId, 'MOBILE', $this->_request->user_mobile_number);
            
            if(!empty($this->_request->address_county))     
                $userMeta->setAttributeValue($userId, 'COUNTRY', $this->_request->address_county);
                
            if(!empty($this->_request->address_town))   
                $userMeta->setAttributeValue($userId, 'CITY', $this->_request->address_town);
                
            if(!empty($this->_request->address_postcode))  
                $userMeta->setAttributeValue($userId, 'ZIPCODE', $this->_request->address_postcode);
                
        }
        
 
    }
    
    
    public function checkSubscriptionAction(){
    	
    	$this->_helper->layout ()->disableLayout ();
        $this->_helper->viewRenderer->setNoRender ( true );
        
        $inapp = new Model_Inapp ( );
        
        
        if(empty($this->_request->email)) 
            $inapp->ErrorCodes('invalid_request_email');
        
        if(empty($this->_request->id)) 
            $inapp->ErrorCodes('id_empty');


        if($this->_request->output == 'json')
        {
            
            if($this->_request->email && $this->_request->id)
            {
            
            $paymentUserSubscription = new Model_UserSubscription ( );
            $paymentUserSubscriptionInfo =  $paymentUserSubscription->getUserSubscription($this->_request->email, $this->_request->id);
            
      
               if(count($paymentUserSubscriptionInfo) >= 1) {
                    $userInfo ['status'] = 1;
                    $userInfo ['response'] = "subscription is valid untill ".$paymentUserSubscriptionInfo[0]->end_date;
                    echo json_encode ( $userInfo );
               } 
               else 
               {
                    $userInfo ['status'] = 0;
                    $userInfo ['response'] = "subscription is not valid";
                    echo json_encode ( $userInfo );
                
               }
            
            }
            
        }
  
                
             
 
    }
 
    
    
   
    

}

	