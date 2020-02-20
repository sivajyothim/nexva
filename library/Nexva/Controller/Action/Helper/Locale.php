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
        //$country        = $ip2Country->getCountry('208.109.189.54');
        $localeNs       = new Zend_Session_Namespace('locale');
        
        $country['code']    = isset($country['code']) ? $country['code'] : 'US';//in case it returns false
        
        $countryModel   = new Model_Country();
        $countryObject  = $countryModel->getCountryByCode($country['code']);   
        if ($countryObject) {
            $localeNs->country  = $countryObject;
            $localeNs->currency = $countryObject->currency;
        } else { //if the country code returned is reserved or something like that
            $countryObject      = $countryModel->getCountryByCode('US');
            $localeNs->country  = $countryObject;
            $localeNs->currency = $countryObject->currency;
        }
    }
}
