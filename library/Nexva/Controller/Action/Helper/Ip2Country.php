<?php
/**
 * 
 * Detects the region and loads the appropriate currency
 * @author John
 *
 */
class Nexva_Controller_Action_Helper_Locale extends Zend_Controller_Action_Helper_Abstract {
    
    public function preDispatch() {
        $ip2Country     = Nexva_GeoData_Ip2Country_Factory::getProvider('local');
        $request        = new Zend_Controller_Request_Http();
        $country        = $ip2Country->getCountry($request->getClientIp(true));
        $country        = $ip2Country->getCountry('208.109.189.54');
        
        
    }
}
