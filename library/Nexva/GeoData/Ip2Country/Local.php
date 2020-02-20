<?php

/**
 * 
 * This is a wrapper class that uses a local database to provide IP2C suppport
 * @author John
 *
 */
class Nexva_GeoData_Ip2Country_Local extends Nexva_GeoData_Ip2Country_Abstract {
        
    /**
     * Returns the Country information given a ip
     * @param string $ip
     */
    public function getCountry($ip) { 
        include_once(APPLICATION_PATH . '/../cli/analytics/geo/Ip2Country.php');
              
        $i  = new Ip2Country();
        $i->dir = APPLICATION_PATH . '/../cli/analytics/geo/php_db/';
        $i->load($ip);
        return $this->formatCountryData($i);
    }
    
    
    /**
     * This method is used to format the data for return. 
     * Need to normalize the data before it
     * @param $data
     */
    protected  function formatCountryData($data) {
        $country    = false;
        if (substr($data->country, 0, 1) != '?') { //script returns ? as the country sometimes
            $country    = array(
                'name'  => $data->country,
                'code'  => $data->countryCode 
            );
        }
        return $country;
    }
}