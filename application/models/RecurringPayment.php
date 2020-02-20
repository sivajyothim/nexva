<?php
/**
 * This is use by the cron 'paysubscription'  
 * this renew the subscription of the user subscription if the end_date is expired
 *
 * @author Chathura
 */

class Model_RecurringPayment {
	
	public function makePayment($mailTemlateContentsStr) {
		
		$paymentUserSubscription = new Model_UserSubscription ( );
		$inApp = new Model_Inapp ( );
		
		// get all expired subscriptions
		$expiredSubscriptions = $paymentUserSubscription->getAll ();
		
		$config = Zend_Registry::get ( 'config' );
		$key = $config->paythru->api->key;
		$password = $config->paythru->api->password;
		
		foreach ( $expiredSubscriptions as $subscriptions ) {
			
			// start -  part to be implemented in the interface as a adapator
			//$paymentGateway = new Nexva_PaymentGateway_PayThruRepeat ( $key, $password );
			
			//$paymentGateway = Nexva_PaymentGateway_Factory::factory('repeat');
			$paymentGateway = Nexva_PaymentGateway_Factory::factory('Paythru', 'PayThruRepeat');
						
			$vars = array (
			         'prev_trans_key' => $subscriptions->transaction_id, 
			         'version' => '2', 
			         'trans_type' => 'Auth', 
			         'trans_value' => $subscriptions->price, 
			         'trans_class' => 'Subscription' 
			);
			
			$paymentGateway->Prepare ( $vars );
			$paymentStatus = $paymentGateway->Execute ();
			// end -  part to be implemented in the interface as a adapator
			

			//if error is occurred continue with the next record and notify the user  
			if ($paymentStatus ['errorcode']) {
				
				$this->updateMetaFailedCount ( $subscriptions->product_id, $subscriptions->user_id, $subscriptions->id, $mailTemlateContentsStr );
				continue;
			
			}
			
			// Cheking for the Paythru server is up ...   [ server status - up, under maintains ]
			if ($paymentStatus ['trans_key'] && empty($paymentStatus ['errorcode'])) {
				// calculate the new expiry date
				$newSubscriptionExpiryDate = $this->dateAdd ( $subscriptions->end_date, $subscriptions->interval );
				
				// enter new order details with the royalties   get the new order_id         
				$newOrderId = $inApp->processPaymentInapp ( 

				$subscriptions->product_id, $subscriptions->user_id, $paymentStatus ['trans_key'], $paymentStatus ['amount'] );
				
				$data = array (

				'end_date' => $newSubscriptionExpiryDate, 'order_id' => $newOrderId )

				;
				
				// insert the new expiry date with new order details
				$paymentUserSubscription->update ( $data, ' id = ' . $subscriptions->id );
				
				//  send the notification 
				$userModel    =  new Model_User();
				$userMeta     =  new Model_UserMeta();
				$productModel =  new Model_Product();
	            $fisrtName    =  $userMeta->getAttributeValue($subscriptions->user_id,'FIRST_NAME');
	            $lastName     =  $userMeta->getAttributeValue($subscriptions->user_id,'LAST_NAME');
	            
	            $userInfo     =  $userModel->getUserDetailsById($subscriptions->user_id);
	            $productInfo  =  $productModel->getProductDetailsByIdInapp( $subscriptions->product_id );
	            
	            if(empty($fisrtName)) 
	            {
	                $name = $userInfo->email;
	            
	            }
	            else
	            {
	                $name =  $fisrtName . ' ' .$lastName;
	            }
	            
			
	            
	            $productMeta = new Model_ProductMeta ( );
	            $payment_failed_count = $productMeta->getAttributeValue ( $subscriptions->product_id, 'RECURRING_FAIL_COUNT_USER_' . $subscriptions->user_id);
	            if($payment_failed_count >= 1 )
	               $productMeta->setAttributeValue ( $subscriptions->product_id, 'RECURRING_FAIL_COUNT_USER_' . $subscriptions->user_id, '0' );
	               
	            
				
				$mailer = new Nexva_Util_Mailer_Mailer ( );
				$mailer ->addTo ( $userInfo->email, $userInfo->email)

				        ->setSubject ( 'Payment notification neXva.' )
				        ->setBodyHtml ( 
				                        $this->getMailBody ( 
				                                            $name, 
				                                            $productInfo['name'], 
				                                            $newOrderId, 
				                                            $paymentStatus ['amount'], 
				                                            $newSubscriptionExpiryDate,
				                                            $mailTemlateContentsStr 
				                        ) 
                        )
				        ->send ();
				        
				        //echo $userInfo->email;
			
			} else {
				
				continue;
			
			}
		
		}
	
	}
	
	
	/*
	 * add no of dates to the date 
	 * 
	 */
	public function dateAdd($date, $noOfDates) {
		$tmp = explode ( '-', $date );
		$newDate = mktime ( 0, 0, 0, $tmp [1], $tmp [2] + ($noOfDates), $tmp [0] );
		return date ( 'Y-m-d', $newDate );
	}
	
	
	/* 1. Update the product meta with the unsuccessful attempts 
	 * 2. Send failed notification mails 
	 * 3. upon 3rd unsuccessful attempt disabled the subscription
	 * 
	 * 
	 */
	public function updateMetaFailedCount($product_id, $user_id, $id,$mailTemlateContentsStr) {
		
		$productMeta = new Model_ProductMeta ( );
		$paymentUserSubscription = new Model_UserSubscription ( );
		
		$payment_failed_count = $productMeta->getAttributeValue ( $product_id, 'RECURRING_FAIL_COUNT_USER_' . $user_id );
		
		if (empty ( $payment_failed_count ))
			$payment_failed_count = 0;
			
		// First Attempt	
		if ($payment_failed_count == '0') {
			$productMeta->setAttributeValue ( $product_id, 'RECURRING_FAIL_COUNT_USER_' . $user_id, 1 );
			
			
			    $userModel    =  new Model_User();
                $userMeta     =  new Model_UserMeta();
                $productModel =  new Model_Product();
                $fisrtName    =  $userMeta->getAttributeValue($user_id,'FIRST_NAME');
                $lastName     =  $userMeta->getAttributeValue($user_id,'LAST_NAME');
                
                $userInfo     =  $userModel->getUserDetailsById($user_id);
                $productInfo  =  $productModel->getProductDetailsByIdInapp( $product_id );
                
                if(empty($fisrtName)) 
                {
                    $name = $userInfo->email;
                
                }
                else
                {
                    $name =  $fisrtName . ' ' .$lastName;
                }
                        
                $mailer = new Nexva_Util_Mailer_Mailer ( );
                $mailer ->addTo ( $userInfo->email, $userInfo->email)

                        ->setSubject ( 'Payment failure notification neXva.' )
                        ->setBodyHtml ( 
                                        $this->getMailBodyFail ( 
                                                            $name, 
                                                            $productInfo['name'], 
                                                            $mailTemlateContentsStr 
                                        ) 
                        )
                        ->send ();
			
			
			
		}
		
		// Second Attempt
		if ($payment_failed_count == '1') {
			$productMeta->setAttributeValue ( $product_id, 'RECURRING_FAIL_COUNT_USER_' . $user_id, 2 );
		}
		
		// Third Attempt
		if ($payment_failed_count == '2') {
			$productMeta->setAttributeValue ( $product_id, 'RECURRING_FAIL_COUNT_USER_' . $user_id, 3 );
		}
		
		// Disabled ruccuring for this subscription
		if ($payment_failed_count == '3') {
			$data = array ('status' => '0' );
			$paymentUserSubscription->update ( $data, ' id = ' . $id );
		}
	
	}
	
	/*
     * format the email body for succesfull payment
     * 
     */
	private function getMailBody ($name, $productName, $orderId, $amount, $subscriptionExpiryDate, $mailTemlateContentsStr)  {
		
		$key [] = "<?=Zend_Registry::get('config')->nexva->application->base->url?>";
		$key [] = '<?=$this->layout()->content ?>';
		$imageUrl = Zend_Registry::get ( 'config' )->nexva->application->base->url;
		
		$mailContents = <<<EOD
    <br />
    Dear $name,  <br /><br />
                            
                            This is an automatically generated notification, <br /><br />
                            
                            You have been charge for the product subscription chargers for the "$productName".  
                            <br /> <br />
                                
                            Charge amount :-  $ $amount. <br />
                            New expiry date  :-  $subscriptionExpiryDate. <br />
                            Order id  :-  $orderId. <br /> <br />
                            
                           
                            You can unsubscribe your subscription by login to your account.  <br /><br />
                            
                             <a href="http://www.nexva.com" target="_blank">Please click here to Login to neXva </a><br/><br/>
                             
                             We hope to see you soon!<br/><br/>

                             Team neXva <br/><br/>
                             
EOD;
		
		$bodyTag = str_replace ( $key, array ($imageUrl, $mailContents ), $mailTemlateContentsStr );
		
		return $bodyTag;
	}
	
	
	/*
     * format the email body for unsuccesfull payment
     * 
     */
	private function getMailBodyFail ($name, $productName, $mailTemlateContentsStr)  {
        
        $key [] = "<?=Zend_Registry::get('config')->nexva->application->base->url?>";
        $key [] = '<?=$this->layout()->content ?>';
        $imageUrl = Zend_Registry::get ( 'config' )->nexva->application->base->url;
        
        $mailContents = <<<EOD
    <br />
    Dear $name,  <br /><br />
                            
                            This is an automatically generated notification, <br /><br />
                            
                            We have been failed to charge you for the subscription chargers for the product  "$productName".<br /> <br />  
                            
                                        
                            Your subscription will be disabled shortly, upon 3 unsuccessful attempts.  <br /> <br />
                            
                             <a href="http://www.nexva.com" target="_blank">Please click here to Login to neXva </a><br/><br/>
                             
                                                We hope to see you soon!<br/><br/>

                             Team neXva <br/><br/>
EOD;
        
        $bodyTag = str_replace ( $key, array ($imageUrl, $mailContents ), $mailTemlateContentsStr );
        
        return $bodyTag;
    }
	

}