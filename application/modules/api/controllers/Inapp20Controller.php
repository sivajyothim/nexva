<?php

/**
 *
 * @copyright   neXva.com
 * @author      chathura
 * @package     inapp
 * 
 */


include_once APPLICATION_PATH."/../library/Nexva/PaymentGateway/Adapter/PaypalMobile/library/paypal.php";

class Api_Inapp20Controller extends Nexva_Controller_Action_Api_Inapp_MasterController {
    
    
    function init() {
        
        $this->_helper->layout ()->disableLayout ();
        $this->_helper->viewRenderer->setNoRender ( true );
        
    }
    
    
    /*  This is the first entry point from the inapp to API and  this will validate User params before payment process 
     * 
     */
    
    public function paymentAction() {
        
        $inapp = new Model_Inapp ( );
        
        // check for the session id 
        if (empty ( $this->_request->ssid ))
            $inapp->ErrorCodes ( 'ssid_empty' );
            
        // check for the product id
        if (empty ( $this->_request->id ))
            $inapp->ErrorCodes ( 'id_empty' );
            
        // check for the call backurl for the sdk 
        if (empty ( $this->_request->callback_url ))
            $inapp->ErrorCodes ( 'callback_url_empty' );
            
        // check for the product price
        if (empty ( $this->_request->price ))
            $inapp->ErrorCodes ( 'price_empty' );
            
            
        Zend_Session::setId ( $this->_request->ssid );
        $sessionUser = new Zend_Session_Namespace ( 'api_user' );    
            
        // check if this request is product subscription for the product 
        if ($this->_request->subscription && (empty ( $this->_request->subscription_interval ) || ('0' == (( int ) ($this->_request->subscription_interval)))))
            $inapp->ErrorCodes ( 'subscription_interval_not_valid' );
        else {
            
            $sessionUser->subscription = $this->_request->subscription;
            $sessionUser->subscriptionInterval = $this->_request->subscription_interval;
        
        }

        if (empty($this->_request->subscription))   {

	        $sessionUser->subscription = NULL;
	        $sessionUser->subscriptionInterval = NULL;
        
        }
        

        $product = new Model_Product();
        $encTypeId = strtolower(str_replace('-','', $this->_request->id));
        $productId = $product->getProductIdByEncryptedId($encTypeId);  

        $sessionUser->productId = $productId;
        $sessionUser->callbackUrl = $this->_request->callback_url;
        $sessionUser->price = $this->_request->price;
        $sessionUser->sandBox = $this->_request->sand_box;
        
        $sessionId = Zend_Session::getId ();
        
        // inputs are validated then forward to select payment gateway screen (selectPaymentGatewayAction)
        $this->_redirect ( '/inapp/2.0/select-payment-gateway/ssid/' . $sessionId );
    
    }
    
    
    
    /*  Display the list of pgs support for inapp
     * 
     */
       
    public function selectPaymentGatewayAction() {
        
        // This is required to enable rendering script...  as this will list the available pgs for user to select
        $this->_helper->viewRenderer->setNoRender ( false );
        
        Zend_Session::setId ( $this->_request->ssid );
        $sessionUser = new Zend_Session_Namespace ( 'api_user' );
        
        $paymentGatewaysModel = new Model_PaymentGateway ( );
        
        if ($sessionUser->subscription != 1)
            $paymentGateways = $paymentGatewaysModel->getPaymentGatewaysForInapp ();
        else
            $paymentGateways = $paymentGatewaysModel->getPaymentGatewaysForInapp ( 1 );
        
        $this->view->paymentGateways = $paymentGateways;
        $this->view->ssid = $this->_request->ssid;
    
    }
    
    
    
    /* paynowAction() this will call by the selectPaymentGatewayAction once payment gateway is selcted 
     * It will call this action to process payments with the relevant payment gateway
     * 
     * 
     */
     
    public function paynowAction() {
        
        $inapp = new Model_Inapp ( );
        
        Zend_Session::setId ( $this->_request->ssid );
        $sessionUser = new Zend_Session_Namespace ( 'api_user' );
        
        // if not empty process the payment further      
        if (! empty ( $this->_request->ssid )) {
            
            $userId = $sessionUser->id;
            $price = $sessionUser->price;
            $itemId = $sessionUser->productId;
            $callbackUrl = $sessionUser->callbackUrl;
            $sandBox = $sessionUser->sandBox;
            
            $sessionUser->pgid = $this->_request->pgid;
            
            $config = Zend_Registry::get ( 'config' );
            
            if ($userId) {
                
                $sessionId = Zend_Session::getId ();
                
                $payementGatewaysModel = new Model_PaymentGateway ( );
                $pgRow = $payementGatewaysModel->find ( $this->_request->pgid );
                $pgRow = $pgRow->current ();
                $pgAdapter = $pgRow->gateway_id;
                
                $userMeta = new Model_UserMeta ( );
                $userMeta->setEntityId ( $userId );
                
                $product = new Model_Product ( );
                $productVal = $product->getProductValById ( $itemId );
                
                
              
                
                switch ($pgAdapter) {
                    
                    case 'PayThru':
                          $pgAdapterClass = 'Paythru';
                          // payment is cancelled     
                          $cancelUrl = "http://" . $_SERVER ['SERVER_NAME'] . "/inapp/2.0/callback-paythru/?ssid=" . $sessionId . "&status=failed";
                
                          $successUrl = "http://" . $_SERVER ['SERVER_NAME'] . "/inapp/2.0/callback-paythru/?ssid=" . $sessionId;
                
                          // the postback url for the user information update
                          $callbackUrlPay = "http://" . $_SERVER ['SERVER_NAME'] . "/inapp/2.0/update-user-info/?ssid=" . $sessionId;
                    
                          $vars = array (
                          'currency' => 'USD', 
                          'salutation' => $userMeta->TITLE, 
                          'first_name' => $userMeta->FIRST_NAME, 
                          'surname' => $userMeta->LAST_NAME, 
                          'mobile_phone' => $userMeta->MOBILE, 
                          'address_1' => $userMeta->ADDRESS, 
                          'town' => $userMeta->CITY, 
                          'postcode' => $userMeta->ZIPCODE, 
                          'version' => 2, 'token_uses' => 1, 
                          'success_url' => $successUrl, 
                          'cancel_url' => $cancelUrl, 
                          'callback_url' => $callbackUrlPay, 
                          'item_name' => $productVal->name, 
                          'item_price' => $price, 
                          'item_reference' => $itemId );
                          break;
                          
                    case 'GoogleCheckout':
                          $pgAdapterClass = $pgAdapter;
                          $successUrl = "http://" . $_SERVER ['SERVER_NAME'] . "/inapp/2.0/callback-google-checkout/?ssid=" . $sessionId;
                          $vars = array (
                          'currency' => 'USD', 
                          'success_url' => $successUrl, 
                          'item_name' => $productVal->name, 
                          'item_price' => $price, 
                          'item_reference' => $itemId ,
                          'item_desc' => $productVal->name);
                          break;  

                          
                    case 'PaypalMobile':
                          $pgAdapterClass = $pgAdapter;
                          $successUrl = "http://" . $_SERVER ['SERVER_NAME'] . "/inapp/2.0/callback-paypal-mobile/?ssid=" . $sessionId;
                          $vars = array (
                          'currency' => 'USD', 
                          'success_url' => $successUrl, 
                          'item_name' => $productVal->name, 
                          'item_price' => $price, 
                          'item_reference' => $itemId ,
                          'cancel_return' => $successUrl, 
                          'item_desc' => $productVal->name);
                          break;  
                    
                }
                
                
			if ($sandBox == '1') 
				    $paymentGateway = Nexva_PaymentGateway_Factory::factory ( $pgAdapterClass, $pgAdapter, 1 );
		        else {
					$paymentGateway = Nexva_PaymentGateway_Factory::factory ( $pgAdapterClass, $pgAdapter);
					$sessionUser->sand_box = 0;
				}
                
                
                
                if (! empty ( $productVal->name )) {
                    

                    $paymentGateway->Prepare ( $vars );
                    $paymentGateway->Execute ();
                } else {
                    $inapp->ErrorCodes ( 'product_id_not_valid' );
                }
            
            } else {
                $inapp->ErrorCodes ( 'ssid_not_valid' );
            }
        
        }
    
    }
    
    /* This is the callback action for paythru 
     * Once payment is success from pg paythru.  will call this action
     * 
     * 
     */

    
    public function callbackPaythruAction() {
    
        $inapp = new Model_Inapp ( );
        
        Zend_Session::setId ( $this->_request->ssid );
        
        $sessionUser = new Zend_Session_Namespace ( 'api_user' );
        
        $callbackUrl = $sessionUser->callbackUrl;
        $userId = $sessionUser->id;
        $productId = $sessionUser->productId;
        $subscription = $sessionUser->subscription;
        $subscriptionInterval = $sessionUser->subscriptionInterval;
        
        if (empty ( $this->_request->transkey ) || empty ( $this->_request->ssid )) {
            header ( 'Location: ' . $callbackUrl . '?status=false' );
            die ();
        }
        
        if (empty ( $sessionUser->id )) {
            header ( 'Location: ' . $callbackUrl . '?status=false' );
            die ();
        }
        
        $result = file_get_contents ( "http://api.paythru.com/transactions/" . $this->_request->transkey );
        
        if ($result == false) {
            header ( 'Location: ' . $callbackUrl . '?status=false' );
            die ();
        }
        
        // retunrning format status=success&token=s&value=10.00&currency=USD
        parse_str ( $result );
        
        $paymentStatus = $status; // "success" or "cancel" 
        $paymentAmount = $value;
        
        if ($paymentStatus == 'cancel') {
            header ( 'Location: ' . $callbackUrl . '?status=false' );
            die ();
        }
        
        if ($sessionUser->sand_box != '1') {
            
            $orders = new Model_Order ( );
            
            // if it is the fisrt time..   process the payment
            if ($orders->chkTransAction ( $this->_request->transkey )) {
                $orderId = $inapp->processPaymentInapp ( $productId, $userId, $this->_request->transkey, $paymentAmount );
                
                if ($subscription) {
                    
                    $paymentUserSubscription = new Model_UserSubscription ( );
                    $recurringPayment = new Model_RecurringPayment ( );
                    $today = date ( 'Y-m-d' );
                    $newDate = $recurringPayment->dateAdd ( $today, $subscriptionInterval );
                    
                    $data ['order_id'] = $orderId;
                    $data ['end_date'] = $newDate;
                    $data ['interval'] = $subscriptionInterval;
                    
                    // create new subscription request
                    $paymentUserSubscription->createSubscription ( $data );
                    
                    
                    // make the RECURRING_FAIL_COUNT_USER_ 0 
                    $productMeta = new Model_ProductMeta ( );
                    $payment_failed_count = $productMeta->getAttributeValue ( $productId, 'RECURRING_FAIL_COUNT_USER_' . $userId );
                    if ($payment_failed_count >= 1)
                        $productMeta->setAttributeValue ( $productId, 'RECURRING_FAIL_COUNT_USER_' . $userId, '0' );
                
                }
                
               // echo $paymentStatus, ' ', $paymentAmount, ' ', $userId, ' ', $productId, ' ', $callbackUrl;
                header ( "location: " . $callbackUrl . "?status=true" );
            }
            
            //  echo $paymentStatus, ' ', $paymentAmount, ' ', $userId, ' ', $productId, ' ', $callbackUrl;
        
        } else {
            
           // echo $paymentStatus, ' ', $paymentAmount, ' ', $userId, ' ', $productId, ' ', $callbackUrl;
            
           // this is sandbox call don't insert any data 
            if ($paymentStatus == 'success') {
                header ( "location: " . $callbackUrl . "?status=true&sand_box=1" );
            } else {
                header ( "location: " . $callbackUrl . "?status=false&sand_box=1" );
            }
        }
        
  

    }
    
    /* This is the callback action for GoogleCheckout
     * Once payment is success pg GoogleChekout will call this action
     * 
     * 
     */
    
    public function callbackGoogleCheckoutAction() {
        
        
        $inapp = new Model_Inapp ( );
        
        Zend_Session::setId ( $this->_request->ssid );
        $sessionUser = new Zend_Session_Namespace ( 'api_user' );
        
        $callbackUrl = $sessionUser->callbackUrl;
        $userId = $sessionUser->id;
        $productId = $sessionUser->productId;
        $price = $sessionUser->price;
    
        $orders = new Model_Order ( );
        
        //echo  $price, ' ', $userId, ' ', $productId, ' ', $callbackUrl;
        
        if ($sessionUser->sand_box != '1') {
            $orderId = $inapp->processPaymentInapp ( $productId, $userId, 'GoogleCheckout', $price, $pg='nexva_googlecheckout_inapp');
            header ( "location: " . $callbackUrl . "?status=true" );
        }
        else
        {
            header ( "location: " . $callbackUrl . "?status=true&sand_box=1" );
            
        }
    
    }
    
    
    
    /* This is the callback action for PaypalMobile
     * Once payment is success pg PaypalMobile will call this action
     * 
     * 
     */
    
    public function callbackPaypalMobileAction() {
        
        
        $inapp = new Model_Inapp ( );
        
        $config = Zend_Registry::get ( 'config' );

        Zend_Session::setId ( $this->_request->ssid );
        $sessionUser = new Zend_Session_Namespace ( 'api_user' );
        
        $callbackUrl = $sessionUser->callbackUrl;
        $userId = $sessionUser->id;
        $productId = $sessionUser->productId;
        $price = $sessionUser->price;
		
		if (! empty ( $this->_request->token )) {
			
			// values should pass to paypal to validate the token 
			define ( 'API_USERNAME', $config->paypal->mobile->merchant_id );
			define ( 'API_PASSWORD', $config->paypal->mobile->merchant_password );
			define ( 'API_SIGNATURE', $config->paypal->mobile->merchant_key );
			define ( 'API_ENDPOINT', $config->paypal->mobile->endpoint_url );
			define ( 'PAYPAL_URL', 'https://www.sandbox.paypal.com/wc?t=' );
			define ( 'USE_PROXY', FALSE );
			define ( 'VERSION', '3.0' );
			
			// validate the token with the paypal and fetch the payment info
			$resArray = hash_call ( 'DoMobileCheckoutPayment', '&token=' . $this->_request->token );
			
			// run an if against the ACK value - it'll return either SUCCESS or FAILURE
			if (strtoupper ( $resArray ['ACK'] ) == 'SUCCESS') {
				//echo 'DoMobileCheckoutPayment success: ' . $resArray ['CURRENCYCODE'] . ' ' . $resArray ['AMT'] . ' from ' . $resArray ['EMAIL'] . ' 
				//  (' . $resArray ['FIRSTNAME'] . ' ' . $resArray ['LASTNAME'] . ')';
				$transId = $resArray ['TRANSACTIONID'];
				
				$orders = new Model_Order ( );
				
				echo  $price, ' ', $userId, ' ', $productId, ' ', $callbackUrl, $transId;
				

				if ($sessionUser->sand_box != '1') {
					$orderId = $inapp->processPaymentInapp ( $productId, $userId, $transId, $price, $pg = 'nexva_paypalmobile_inapp' );
					header ( "location: " . $callbackUrl . "?status=true" );
				} else {
					header ( "location: " . $callbackUrl . "?status=true&sand_box=1" );
				
				}
				
			// ends SUCCESS 
			} else {
				// DoMobileCheckoutPayment failed
				echo 'DoMobileCheckoutPayment failed ' . $resArray ['L_SHORTMESSAGE0'] . ' ' . $resArray ['L_ERRORCODE0'] . ' ' . $resArray ['L_LONGMESSAGE0'];
				echo 'efsdfsdf';
				header ( 'Location: ' . $callbackUrl . '?status=false' );
				die ();
			}
		
		}
    
       
    
    }
    
    
    
    
    
    
    

    

}
