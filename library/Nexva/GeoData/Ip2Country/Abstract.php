<?php
abstract class Nexva_GeoData_Ip2Country_Abstract {
    
    
    /**
     * Returns the Country information given a ip
     * @param string $ip
     */
    public abstract function getCountry($ip);
    
    
    /**
     * This method is used to format the data for return. 
     * Need to normalize the data before it
     * @param $data
     */
    protected abstract function formatCountryData($data);
}