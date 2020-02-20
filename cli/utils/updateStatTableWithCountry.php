<?php

include_once("../../application/BootstrapCli.php");
 
error_reporting(E_ALL);
set_time_limit(0);
ini_set('memory_limit', '1000M');
 
    $statisticDownloadModel  = new Model_StatisticDownload();
    $geoData = Nexva_GeoData_Ip2Country_Factory::getProvider();
            
    $records = $statisticDownloadModel->getAllStatRecords();
    

            
    foreach($records as $record){
    	
    
    	
        $ip = trim($record->ip);

        if(!empty($ip))	{
        	
           	 $country  =   $geoData->getCountry($ip);
           	 $statisticDownloadModel->updateCountyCode($record->id, $country['code']);
        }
        Zend_Debug::dump($ip. ' -  '.$country['code'].'\n');
        $ip = '';
           	
    }