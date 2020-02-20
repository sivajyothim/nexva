<?php

/**
 * 
 * This is a wrapper class that uses a API to get the country code by the visitor IP
 * @author Rooban
 *
 */
class Nexva_GeoData_Ip2Country_Api extends Nexva_GeoData_Ip2Country_Abstract {
        
    /**
     * Returns the Country information given a ip
     * @param string $ip
     */
    public function getCountry($ip) { 
     
        $visitorIpModel = new Model_VisitorIp();
        $existIp = $visitorIpModel->selectIp($ip);

        if(count($existIp)>0){
            return array('name'=>$existIp['country'], 'code'=>$existIp['country_code']);
        }
        else{
            $curl = curl_init("http://ipinfo.io/{$ip}"); 
            curl_setopt($curl, CURLOPT_FAILONERROR, true); 
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);   
            $result = curl_exec($curl); 

            $ipDetails = $this->formatCountryData($result);
            $ipDetails['ip'] = $ip;
 
            $insertData = array(
                'ip' => sprintf("%u", ip2long($ip)),
                'country_code' => $ipDetails['code'],
                'country' => $ipDetails['name']
            );
            $existIp = $visitorIpModel->insert($insertData);
            if($existIp){
                return $ipDetails;
            }
        }
    }
    
    /**
     * This method is used to format the data for return. 
     * Need to normalize the data before it
     * @param $data
     */
    protected  function formatCountryData($data) {
        $country    = false;
        $objCountry = json_decode($data);
        $country    = array(
                'name'  => $objCountry->city,
                'code'  => $objCountry->country 
            );

        return $country;
    }
}