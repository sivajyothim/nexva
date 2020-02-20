<?php


class Admin_Model_BuildSupportedVersions extends Nexva_Db_Model_MasterModel {
        
    protected  $_id     = 'id';
    protected  $_name   = 'build_supported_versions';
    
    function __construct() {
        parent::__construct();
    }
    
    
    public function addBuildSupportOsVersion($buildId, $minVersion, $orBetter) {
    	
    	$values = array (
    			'build_id' => $buildId, 
    			'min_version' => $minVersion,
    			'or_better' => $orBetter
    			); 
    			
        $this->insert($values);
        return Zend_Registry::get('db')->lastInsertId();
      
    }
    
    function getBuildSupportedVersion($buildId)    {  	
    	$row = $this->fetchRow($this->select()->where("build_id=?",$buildId));

    	if($row)
        	return $row;
        else 
        	return false;
    	
    }
    
    
    public function save($data) {
      	
        if (null == ($id = $data['id'])) {
            unset($data['id']);
            return $this->insert($data);
        }	else	{

        	if($data['min_version'] == 0)
        		$this->delete('id ='. $id);
        	else
            	$this->update($data, array('id = ?' => $id));
            
            return false;
        }
    }
    
}