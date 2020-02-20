<?php
    include_once("../../application/BootstrapCli.php");
    
       // log the missing images of the devices file is located at ../logs/missing_device_images.csv
       
    	$deviceModel = new Model_Device();

        $rows = $deviceModel->fetchAll();

        $devices = array();

        $deviceImagePath = Zend_Registry::get('config')->nexva->application->deviceImagePath;
        $missingImageLogFilePath = Zend_Registry::get('config')->nexva->application->logDirectory;

        $fp = fopen($missingImageLogFilePath. "/" .'missing_device_images.csv', 'w');
        fputcsv($fp, array('Device ID', 'Device manufacturer','Device model', 'wurfl_device_id', 'Device user agent'));
        
        foreach ($rows as $row) {

            if (!file_exists($deviceImagePath . "/" . $row->wurfl_device_id . ".gif"))
                fputcsv($fp, array($row->id, $row->brand, $row->model, $row->wurfl_device_id, $row->useragent));
                
        }
        
		fclose($fp);
        
