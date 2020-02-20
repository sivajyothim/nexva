<?php

require_once( './vendors/Tera-WURFL/TeraWurflLoader.php');

class Nexva_DeviceDetection_TeraWurflLoader extends TeraWurflLoader {
	
	public function load(){
		$this->wurfl->toLog("Loading WURFL",LOG_INFO);
		if(!is_readable($this->file)){
			$this->wurfl->toLog("The main WURFL file could not be opened: ".$this->file,LOG_ERR);
			$this->errors[]="The main WURFL file could not be opened: ".$this->file;
			return false;
		}
		
		$this->timestart = microtime(true);
		// Parse XML data into $this->devices array
		$this->parser->open($this->file, TeraWurflXMLParser::$TYPE_WURFL);
		$this->parser->process($this->devices);
		$this->mainDevices = count($this->devices);
		$this->version = $this->parser->wurflVersion;
		$this->last_updated = $this->parser->wurflLastUpdated;
		if(!$this->loadPatches()) return false;
		if(!$this->validate()) return false;
		if(!$this->sort()) return false;
		$msg = $this->loadIntoNexvaTempTable();

		//echo $msg;
		return true;
	}
	
	
		
	/**
	 * Loads the WURFL devices into the nexva temp table {temp_wrfl_devices} database.
	 * @return Bool Completed without error
	 */
	
	
	public function loadIntoNexvaTempTable(){
		
		$this->timedatanexvatempttable = microtime(true);

		$devicesMsg = '';
		$added = 0;

		$tempWrflDevice = new Admin_Model_WurflDevices();
		
         $devicesMsg.= '<pre>';    	
            	foreach ($this->tables as $key => $value)	{
            			$devicesMsg.=  $key. ' <br>';
            			
  
            			 	foreach ($value as $keyy => $valuee)
            			 	{

	            	       			
	            	       			if($valuee['id'])	{
	            	       			
	            	       		
									$deviceresult = $tempWrflDevice->getDevice($valuee['id']);
	     
									$deviceExist = count($deviceresult);
										       			
							
									if($deviceExist == 1)	{
	
									$devicesMsg.=  '<br> '.  $valuee['id']. ' exsist<br> ';
										continue;
										
									} else {
											
									$userAgentInsertedId = $tempWrflDevice->addUserAgent($valuee['id'],$valuee['user_agent']);

									$devicesMsg .= 'added UA '. $valuee['id'] . ' - ' . $valuee['user_agent']. ' '. $userAgentInsertedId. '<br>';
									++$added;
									}
            	       			
            	       			
            	       			}
            	       				 
            	       				 	$devicesMsg.=    '<br><br>';
            			 	}
            	       			$devicesMsg.=    '<br><br>';
            			}	
            			
        	$devicesMsg.=  '</pre>';   

        	$this->timedatanexvatempttableEnd = microtime(true);
        	//echo $devicesMsg;
        	
        	if($added)
        		return $added;
        	else
        		return 'No any devices Added.';
        	

	}
	
	public function loadIntoNexvaTempTableTime(){
		return ($this->timedatanexvatempttableEnd - $this->timedatanexvatempttable);
	}

	
	
}