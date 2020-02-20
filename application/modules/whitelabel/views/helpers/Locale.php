<?php
class Whitelabel_View_Helper_Locale extends Zend_View_Helper_Abstract {

    private $currency   = null;
    private $country    = null;
    
    public function __construct() {
        $localeNs       = new Zend_Session_Namespace('locale');
        $this->currency = $localeNs->country;
        $this->country  = $localeNs->currency;
    }
    
    public function locale() {
        return $this;
    }
    
    public function getCurrencySymbol() {
        return '$';
    }
}