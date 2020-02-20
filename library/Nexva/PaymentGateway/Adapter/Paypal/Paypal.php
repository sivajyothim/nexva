<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
// incluede the PHP class
include_once 'paypal.class.php';

class Nexva_PaymentGateway_Adapter_Paypal_Paypal implements Nexva_PaymentGateway_Interface_Interface {

  protected $_paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
  protected $_paypal;
  private static $__instance;

  public function __construct() {
    $paypal = new paypal_class();
    $this->_paypal = $paypal;
    $config = Zend_Registry::get('config');
    $email = $config->paypal->business_email;
    $endPoint = $config->paypal->endpoint_url;
    $this->SetServiceEndPoint($endPoint);
    $this->addVar('business', $email);
  }

  public static function getInstance($sandBox = 0) {
    if (!isset(self::$__instance)) {
      $class = __CLASS__;
      self::$__instance = new $class($sandBox);
    }
    return self::$__instance;
  }

  public function SetServiceEndPoint($url) {
    $this->_paypal_url = $url;
  }

  public function AddVar($name, $value) {
    $this->_paypal->add_field($name, $value);
  }

  public function Prepare($vars = array()) {
    foreach ($vars as $key => $value) {
      $this->AddVar($key, $value);
    }
  }

  public function IpnValidate() {
    $this->_paypal->paypal_url = $this->_paypal_url;
    return $this->_paypal->validate_ipn();
  }

  public function getIpnData() {
    return $this->_paypal->ipn_data;
  }

  public function Execute() {
    $paypal = $this->_paypal;
    $paypal->paypal_url = $this->_paypal_url;
    $paypal->submit_paypal_post();
  }

  public function getAdapterName() {
    return 'Paypal';
  }

  public function validate() {
    $ipnPostData = $paymentGateway->getIpnData();
    
    return $this->IpnValidate();
  }

}

?>
