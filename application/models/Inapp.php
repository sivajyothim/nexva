<?php

class Model_Inapp {

    
    
    public function authenticateUser($userName, $password, $salt) {
        $userObj = new Model_User ( );
        $tmpUser = $userObj->getUserByEmail ( $userName );
        
        if (is_null ( $tmpUser )) {
            $this->ErrorCodes('user_not_found');
            
        }
        
            if (empty( $salt )) {
            $this->ErrorCodes('invalid_salt');
            
        }
        
        
        if (md5 ( $tmpUser->password . $salt ) != $password) {
            $this->ErrorCodes('invalid_pass');
           
        }
        
        $sessionUser = new Zend_Session_Namespace ( 'api_user' );
        $sessionUser->id = $tmpUser->id;
        $sessionId = Zend_Session::getId ();
        $userInfo ['ssid'] = $sessionId;
        $userInfo ['status'] = 1;
        echo json_encode ( $userInfo );
    
    }
       
    public function authenticateUnclaimedUser($userId) {
                  
        $sessionUser = new Zend_Session_Namespace ( 'api_user' );
        $sessionUser->id = $userId;
        $sessionId = Zend_Session::getId ();
        $userInfo ['ssid'] = $sessionId;
        $userInfo ['status'] = 1;
        echo json_encode ( $userInfo );
    
    }
    
    
     public function registerUser($userName, $unclaimedAccountMail=NULL, $chapId = null) {
     	
     	$password = $this->createRandomPassword ();
        
        $user = new Model_User ( );
        $userData = array (
                      'username' => $userName, 
                      'email' => $userName, 
                      'password' => $password, 
                      'type' => "USER", 
                      'login_type' => "NEXVA" ,
                      'status' => "1",
                      'chap_id'   => $chapId
        );
        
        $user_id = $user->createUser ( $userData );
                
        // set the user meta UNCLAIMED_ACCOUNT value 
        $userMeta = new Model_UserMeta();
        $userMeta->setAttributeValue($user_id, 'UNCLAIMED_ACCOUNT', '1');
        $cdkeyWarninCountStr  =  $userMeta->getAttributeValue($user_id,'UNCLAIMED_ACCOUNT');
        if($unclaimedAccountMail == 'web' or $unclaimedAccountMail == 'mobile')
          $user->sendUnclaimedAccountMail($userName,'web');
        else
	      $user->sendUnclaimedAccountMail($userName);     
        
	      
	    return $password;
    
       
    
    }
    
    public function createRandomPassword() {
        
        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
        srand ( ( double ) microtime () * 1000000 );
        $i = 0;
        $pass = '';
        
        while ( $i <= 7 ) {
            $num = rand () % 33;
            $tmp = substr ( $chars, $num, 1 );
            $pass = $pass . $tmp;
            $i ++;
        }
        
        return $pass;
    
    }
    
    public function ErrorCodes($error) {
        
        include_once 'Errorcodes.php';  
       

        
        switch ($error) {
        case 'invalid_request_email':
            $userInfo['status'] = ERROR_INVALID_EMAIL;
            $userInfo['response'] = "invalid email";
            break;
        case 'user_not_found':
            $userInfo['status'] = ERROR_USER_NOT_FOUND;
            $userInfo['response'] = "e-mail address not found in the DB";
            break;
        case 'invalid_pass':
            $userInfo['status'] = ERROR_INVALID_PASS;
            $userInfo['response'] = "invalid password";
            break;
        case 'invalid_salt':
            $userInfo['status'] = ERROR_INVALID_SALT;
            $userInfo['response'] = "invalid salt";
            break;            
       case 'ssid_empty':
            $userInfo['status'] = ERROR_EMPTY_SSID;
            $userInfo['response'] = "ssid is not provided";
            break;
       case 'id_empty':
            $userInfo['status'] = ERROR_EMPTY_ID;
            $userInfo['response'] = "id is not provided";
            break;
       case 'callback_url_empty':
            $userInfo['status'] = ERROR_EMPTY_CALLBACK;
            $userInfo['response'] = "callback_url is not provided";
            break;
       case 'price_empty':
            $userInfo['status'] = ERROR_EMPTY_PRICE;
            $userInfo ['response'] = "price is not provided";
            break;
       case 'ssid_not_valid':
            $userInfo['status'] = ERROR_INVALID_SSID;
            $userInfo['response'] = "ssid is not valid";
            break;
       case 'product_id_not_valid':
            $userInfo['status'] = ERROR_INVALID_PRODUCT;
            $userInfo['response'] = "product id is not valid";
            break;
       case 'subscription_interval_not_valid':
            $userInfo['status'] = ERROR_INVALID_SUBSCRIPTION_INTERVAL;
            $userInfo['response'] = "subscription interval is not valid";
            break;
       case 'username_or_passwor_is_incorrect':
            $userInfo['status'] = USERNAME_OR_PASSWOR_IS_INCORRECT;
            $userInfo['response'] = "user name or password invalid";
            break;
       case 'this_account_has_not_been_verified':
            $userInfo['status'] = THIS_ACCOUNT_HAS_NOT_BEEN_VERIFIED;
            $userInfo['response'] = "this account has not been verified";
            break;
       case 'only_post_request_is_allowed':
            $userInfo['status'] = ONLY_POST_REQUEST_IS_ALLOWED;
            $userInfo['response'] = "send only post request";
            break;
       case 'carrier_id_empty':
            $userInfo['status'] = ERROR_EMPTY_CARRIER_ID;
            $userInfo['response'] = "carrier id is not provided";
            break;
      case 'amount_empty':
           	$userInfo['status'] = ERROR_EMPTY_AMOUNT;
           	$userInfo['response'] = "amount is not provided";
           	break;
      case 'currency_empty':
            $userInfo['status'] = ERROR_EMPTY_CURRENCY;
           	$userInfo['response'] = "currency is not provided";
           	break;
      case 'mdn_empty':
            $userInfo['status'] = ERROR_MDN;
            $userInfo['response'] = "MDN is not provided";
            break;
      case 'verification_code_empty':
            $userInfo['status'] = ERROR_VERIFICATION_CODE;
            $userInfo['response'] = "verification code is not provided";
            break;
      case 'payment_id_empty':
            $userInfo['status'] = ERROR_PAYMENT_ID;
            $userInfo['response'] = "payment id is not provided";
            break;
      case 'verification_code_invalid':
            $userInfo['status'] = ERROR_INVALID_VERRIFICATION_CODE;
            $userInfo['response'] = "verification code is invalid";
            break;
        }
        
        echo json_encode($userInfo);
        die();
    
    }
    
    
    
    
    public function processPaymentInapp($productId, $userId, $transkey, $price, $pg='nexva_paythru_mobi') {
        
        $product = new Model_Product ( );

        //$productDetail = $product->getProductDetailsByIdInapp( $productId );
        $productDetail = $product->getProductDetailsById ( $productId );
        
        // Orders
        $orders = new Model_Order ( );
        $orderData = array (
                        'user_id' => $userId, 
                        'order_date' => new Zend_Db_Expr ( 'NOW()' ), 
                        'merchant_id' => $pg, 
                        'transaction_id' => $transkey, 
                        'payment_method' => 'INAPP' 
                    );
        
        $orderId = $orders->insert ( $orderData );
        //Orders details
        $orderDetails = new Model_OrderDetail ( );
        $orderDetailsData = array ('order_id' => $orderId, 'product_id' => $productDetail ['id'], 'price' => $price );
        
        $orderDetails->insert ( $orderDetailsData );
        

        // save royalties
        $userAccount = new Model_UserAccount();
        $userAccount->saveRoyalities($productDetail ['id'], 1, 'INAPP');
        
       
        
        return $orderId;
    
    }
    
    public function convertToDollers($amount,$currency){
        $currencyObj=new Api_Model_Currencies();
        $currencyDetails=$currencyObj->getCurrencyRate($currency);
        $rate=$currencyDetails->rate;
        if(isset($rate) && !empty($rate)){
                return ($amount/$rate);
        } else {
            return NULL;
        }
    }
    

}



?>