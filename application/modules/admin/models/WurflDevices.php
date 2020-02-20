<?php
class Admin_Model_WurflDevices extends Nexva_Db_Model_MasterModel {
        
    protected  $_id     = 'id';
    protected  $_name   = 'wurfl_devices';
    
    function __construct() {
        parent::__construct();
    }
    
    public function selectNewDevices() {
        $select = $this->select()->where('is_added_to_nexva_devices <> 1');
        $row    = $this->fetchAll($select);
        
        if(!empty($row))
        	return  $row;
        else 
        	false;
      
    }
    
    public function getDevice($wurflDeviceId) {
        $select = $this->select()->where('wurfl_device_id = ?', $wurflDeviceId);
        $row    = $this->fetchAll($select);
        
        if(!empty($row))
        	return  $row;
        else 
        	false;
      
    }
    
    public function addUserAgent($wurflDeviceId, $userAgent) {
    	
    	$values = array (
    			'wurfl_device_id' => $wurflDeviceId, 
    			'useragent' => $userAgent
    			); 
    			
        $this->insert($values);
        return Zend_Registry::get('db')->lastInsertId();
      
    }
    
}