<?php

class Partnermobile_Model_AppWall extends Zend_Db_Table_Abstract
{
    protected $_name = 'app_wall';
    protected $_id = 'id';
    
    public function saveStatics($sessionId,$ipAddress,$chapId,$userId,$languageId,$platformId,$deviceId){
        $geoData = Nexva_GeoData_Ip2Country_Factory::getProvider();
        $country  =   $geoData->getCountry($ipAddress);
         $data=array(
                'date'=>new Zend_Db_Expr('NOW()'),
                'session_id'=>$sessionId,
                'ip'=>$ipAddress,
                'chap_id'=> $chapId,
                'user_id'=>$userId,
                'language_id'=>$languageId,
                'platform_id'=>$platformId,
                'device_id'=>$deviceId,
                'iso'=>$country['code']
            );
            $this->insert($data);
    }
}