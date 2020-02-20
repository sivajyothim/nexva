<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan
 * @version     $Id$
 */
require_once('./vendors/GoogleCheckout/library/googlecart.php');
require_once('./vendors/GoogleCheckout/library/googleitem.php');
require_once('./vendors/GoogleCheckout/library/googleshipping.php');
require_once('./vendors/GoogleCheckout/library/googletax.php');
require_once('./vendors/GoogleCheckout/library/googleresponse.php');

class Nexva_PaymentGateway_Adapter_GoogleCheckout_GoogleCheckout implements Nexva_PaymentGateway_Interface_Interface {

  protected $cart;
  private static $__instance;
  protected $variables = array();

  public function __construct($sandBox) {
    $config = Zend_Registry::get('config');
    // @TODO : add keys to config

    if ($sandBox == 0) {
	    $merchant_id = $config->googlecheckout->api->merchant_id;  // Your Merchant ID
	    $merchant_key = $config->googlecheckout->api->merchant_key;  // Your Merchant Key
	    $server_type = ($config->googlecheckout->api->sandbox) ? "sandbox" : "production";
    }
    else    
    {
	    $merchant_id = $config->googlecheckout->sandbox->merchant_id;  // Your Merchant ID
	    $merchant_key = $config->googlecheckout->sandbox->merchant_key;  // Your Merchant Key
	    $server_type = ($config->googlecheckout->sandbox->sandbox) ? "sandbox" : "production";
    }
    
    $currency = "USD";
    $this->cart = new GoogleCart($merchant_id, $merchant_key, $server_type, $currency);
  }

  private function __clone() {

  }

  // Copying from paythru class
  // @TODO : should move this singleton to the factory
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
    return "GoogleCheckout";
  }

  public function Prepare($vars = array()) {
    $this->variables = $vars;
  }

  public function Execute() {
    $cart = $this->cart;
    $itemDetails = $this->variables;
    $total_count = 1;
    //  Key/URL delivery
    $item = new GoogleItem($itemDetails['item_name'], // Item name
            $itemDetails['item_desc'], // Item description
            $total_count, // Quantity
            $itemDetails['item_price']); // Unit price
    $item->SetURLDigitalContent($itemDetails['success_url'], '', '');
    $cart->AddItem($item);

    // Add tax rules
//    $tax_rule = new GoogleDefaultTaxRule(0.05);
//    $tax_rule->SetStateAreas(array("MA", "FL", "CA"));
//    $cart->AddDefaultTaxRules($tax_rule);

    // Request buyer's phone number
    $cart->SetRequestBuyerPhone(true);

    // This will do a server-2-server cart post and send an HTTP 302 redirect status
    // This is the best way to do it if implementing digital delivery
    // More info http://code.google.com/apis/checkout/developer/index.html#alternate_technique
    list($status, $error) = $cart->CheckoutServer2Server();
    // if i reach this point, something was wrong
    echo "An error had ocurred: <br />HTTP Status: " . $status . ":";
    echo "<br />Error message:<br />";
    echo $error;
  }

  public function validate() {
    return TRUE;
  }

}

?>
