<?php
/**
 *
 * @copyright   neXva.com
 * @author      chathura 
 * @version     $Id$
 */

require_once('library/paypal.php');

class Nexva_PaymentGateway_Adapter_PaypalMobile_PaypalMobile implements Nexva_PaymentGateway_Interface_Interface {
	
	private static $__instance;
	protected $variables = array ();

  public function __construct($sandBox) {

  	$config = Zend_Registry::get('config');
  	$sandbox = 0;
        if ($sandbox == 0) {
			// live values
          
            define ( 'API_USERNAME', $config->paypal->mobile->merchant_id );
            define ( 'API_PASSWORD', $config->paypal->mobile->merchant_password );
            define ( 'API_SIGNATURE', $config->paypal->mobile->merchant_key );
            define ( 'API_ENDPOINT', $config->paypal->mobile->endpoint_url );
            define ( 'PAYPAL_URL', 'https://www.sandbox.paypal.com/wc?t=');
			
		} else {
            // sandbox / testing values
            define ( 'API_USERNAME', $config->paypalmobile->merchant_id );
            define ( 'API_PASSWORD', $config->paypalmobile->merchant_password );
            define ( 'API_SIGNATURE', $config->paypalmobile->merchant_key );
            define ( 'API_ENDPOINT', $config->paypalmobile->endpoint_url );
            define ( 'PAYPAL_URL', 'https://www.sandbox.paypal.com/wc?t=');
		}
       
    define('USE_PROXY',FALSE);
    define('VERSION', '3.0');
    
  }

  private function __clone() {

  }

public static function getInstance($sandBox = 0) {
    if (!isset(self::$__instance)) {
      $class = __CLASS__;
      self::$__instance = new $class($sandBox);
    }

    return self::$__instance;
  }

  public function setServiceEndPoint($url) {

  }

  public function addVar($name, $value) {
    
  }

  public function getAdapterName() {
    return "PaypalMobile";
  }

  public function Prepare($vars = array()) {
  
   $this->variables = '';
   $this->variables.= '&AMT='.$vars ['item_price'] ; 
   $this->variables.= '&CURRENCYCODE=USD'; 
   $this->variables.= '&DESC='.$vars['item_name'];
   $this->variables.= '&RETURNURL='. $vars['success_url'];
   $this->variables.= '&CANCELURL='. $vars['cancel_return'];

  // $submit_string .= '&NUMBER='; // OPTIONAL - pass through value, returned verbatin on DoMobileCheckoutPayment - for values like stock keeping units <127 single byte characters
  // $submit_string .= '&CUSTOM='; // OPTIONAL - pass through value, returned verbatin on DoMobileCheckoutPayment - store session IDs and other values here <256 characters
  // $submit_string .= '&INVNUM='.time(); // OPTIONAL - Your own invoice number of ID used to identify the transaction. <127 single byte characters - must be unique
    
  }
	
	public function Execute() {
		
    	$resArray = hash_call ( 'SetMobileCheckout', $this->variables );
     	// if we get an acknowledgement of SUCCESS there should also be a valid token
		if (strtoupper ( $resArray ['ACK'] ) == 'SUCCESS') {
			// redirect the customer to paypal to confirm the payment, pass the token value in the url - tokens expire after three hours (20 single byte characters)
			header ( 'Location: ' . PAYPAL_URL . urldecode ( $resArray ['TOKEN'] ) );
		} else {
			// SetMobileCheckout failed
			echo 'SetMobileCheckout failed: ' . $resArray ['L_SHORTMESSAGE0'] . ' ' . $resArray ['L_ERRORCODE0'] . ' ' . $resArray ['L_LONGMESSAGE0'];
		}
	}
	


}