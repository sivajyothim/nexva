<?php

class Admin_DeviceController extends Zend_Controller_Action {
	
    public function preDispatch ()
    {
        if (! Zend_Auth::getInstance()->hasIdentity()) {
            if ($this->_request->getActionName() != "login") {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();
            }
            if ($this->_request->getActionName() != "login")
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/login');
        }
    }
    
    
    public function indexAction()
    {
    	
       	    	
    	
	   	 $query_handsets    =   Zend_Registry::get('db')->select()
                                                  ->from('devices',array("total" => "count(*)"))
                                                  ->query();
         $handsets   =   $query_handsets->fetchAll();
         $this->view->handsets_count=$handsets[0]->total;
         
        $deviceModel = new Model_Device();
        $devices = $deviceModel->getAllDevicesWithoutDeviceImages();
        $this->view->devicesMissingImages = count($devices);
    	
    }
    
    
    public function imagesAction ()
    {
        $deviceModel = new Model_Device();
        $devices = $deviceModel->getAllDevicesWithoutDeviceImages();
        $this->view->devices = $devices;
    }
    
    //
    
    public function loadnewdevicesAction() {
    	
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $tempWrflDevice = new Admin_Model_WurflDevices();
        $new = $tempWrflDevice->selectNewDevices();
        $this->view->newDevices = count($new);

        $devicesAdded = '';
		
        
        $deviceModel = new Model_Device();
        $deviceCountStart  = count($deviceModel->fetchAll());

			
		set_time_limit(0);
		ini_set('memory_limit', '300M');
			
		$i = 1;
		
		//$db = Zend_Registry::get('db');
		
		//Zend_Registry::set('dbcon', $db->getConnection());
		
		$deviceDetector = Nexva_DeviceDetection_TeraWurflDeviceDetector::getInstance();

			// select all the device from TempWrflDevice which is not flag as is_added_to_nexva_devices = 1
		foreach ($new as $device)	{
							
			++$i; 


			// if the user agent string is empty or NULL, then continue with next device and update it is processed 
				if(empty($device->useragent) or $device->useragent == NULL )	{
					$tempWrflDevice->update(array('is_added_to_nexva_devices' => 1 ), array('id = ?' => $device->id));
					//echo ' - user agent string is empty <br >';
					continue;
				}
					
				// if the user agent string is contaning "DO_NOT_MATCH" string, then continue with next device and update it is processed 
				if(substr($device->useragent, 0, 12) == 'DO_NOT_MATCH')	{
					$tempWrflDevice->update(array('is_added_to_nexva_devices' => 1), array('id = ?' => $device->id));
					//echo ' - user agent DO_NOT_MATCH <br >';
					continue;
				}		
	
	    			
				if($device->useragent)	{
		           	$deviceDetector->detectDeviceByUserAgent($device->useragent);
		           	//echo $device->useragent, ' - added <br >';
				}	else	{
		           		$tempWrflDevice->update(array('is_added_to_nexva_devices' => 1 ), array('id = ?' => $device->id));
		           		//echo ' - not added <br >';
		           		continue;
		        }
	            	
	 
		        if($deviceDetector->getNexvaDeviceId())		{
		        	        
		            	$devicesAdded .=  $i. ' - '. $deviceDetector->getNexvaDeviceId() . ' <br>';
		            	$tempWrflDevice->update(array('is_added_to_nexva_devices' => 1, 'nexva_device_id' =>  $deviceDetector->getNexvaDeviceId() ), array('id = ?' => $device->id));
		        } else {
		            	$devicesAdded .=  $i. ' - not Added UA -'.$device->useragent.' <br>';
		            	$tempWrflDevice->update(array('is_added_to_nexva_devices' => 1 ), array('id = ?' => $device->id));	
		        }
		     
		      
				//echo $device->useragent ,' - ', $i, ' - ', $device->id,  ' <br>';
				//if($i > 700)
		        	//return;
		        

			}

			$deviceCountEnd = count($deviceModel->fetchAll());
			$addedDevicesCount = $deviceCountEnd-$deviceCountStart ;
	
			if($addedDevicesCount)	
				echo 'Total '.  	$addedDevicesCount .' new devices added. <a href="<?php echo ADMIN_PROJECT_BASEPATH;?>device/index"> Device maintenance manu </a>';
			else 
				echo 'Total 0 new devices added. <a href="<?php echo ADMIN_PROJECT_BASEPATH;?>device/index"> Device maintenance manu </a>';
			//$this->view->updatedDevices = $devicesAdded;

			
				
				
			//$db->query('DELETE FROM wurfl_devices');
	


    }
    
        public function updatewurfltableAction() {

  		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		require_once( './vendors/Tera-WURFL/TeraWurfl.php' );
		require_once( './vendors/Tera-WURFL/TeraWurflLoader.php');
		require_once( './vendors/Tera-WURFL/TeraWurflXMLParsers/TeraWurflXMLParser.php');
		require_once( './vendors/Tera-WURFL/TeraWurflXMLParsers/TeraWurflXMLParser_XMLReader.php');
		require_once( './vendors/Tera-WURFL/TeraWurflXMLParsers/TeraWurflXMLParser_SimpleXML.php');

		$base = new TeraWurfl();
      
		set_time_limit(0);
		ini_set('memory_limit', '200M');

        $newfile = TeraWurfl::absoluteDataDir().TeraWurflConfig::$WURFL_FILE.".zip";
		$wurflfile = TeraWurfl::absoluteDataDir().TeraWurflConfig::$WURFL_FILE;

		$gzsize = WurflSupport::formatBytes(filesize($newfile));
		// Try to use ZipArchive, included from 5.2.0
		if(class_exists("ZipArchive")){
			$zip = new ZipArchive();
			if ($zip->open(str_replace('\\','/',$newfile)) === TRUE) {
			    $zip->extractTo(str_replace('\\','/',dirname($wurflfile)),array('wurfl.xml'));
			    $zip->close();
			} else {
			    Throw New Exception("Error: Unable to extract wurfl.xml from downloaded archive: $newfile");
				exit(1);
			}
		}else{
			system("gunzip $newfile");
		}

		$ok = true;

		usleep(50000);
//		flush();
		
		$loader =  new Nexva_DeviceDetection_TeraWurflLoader($base);
	
		$loader = $loader->load();
		
		if(	$loader)
			echo 'WURFL Devices table Updated.. go back to <a href="<?php echo ADMIN_PROJECT_BASEPATH;?>device/index"> Device maintenance manu </a>';
		else
			echo 'Error occured try again.. go back to <a href="<?php echo ADMIN_PROJECT_BASEPATH;?>device/index">Device maintenance manu</a>';

		

	
        }
        
        
        public function emptytableAction() {
        	
        	  	$this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
				$db = Zend_Registry::get('db');
				$db->query('DELETE FROM wurfl_devices');
				echo 'WURFL Devices table is now empty  <a href="<?php echo ADMIN_PROJECT_BASEPATH;?>device/index">Device maintenance manu</a>';
        	
        	
        }
        

}
?>
