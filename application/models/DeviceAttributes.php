<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_DeviceAttributes extends Zend_Db_Table_Abstract {

  protected $_name = 'device_attributes';
  protected $_id = 'id';

  function __construct() {
    parent::__construct();
  }

  protected $_referenceMap = array(
    'Model_Device' => array(
      'columns' => array('device_id'),
      'refTableClass' => 'Model_Device',
      'refColumns' => array('id')
    ),
  );
  
   function getDeviceOsVersion($deviceId)    {  	
    	$row = $this->fetchRow($this->select()->where("device_id=?",$deviceId)->where("device_attribute_definition_id=3"));
    	
    	
    	if($row)
        	return $row;
        else 
        	return false;
    	
    }

}
?>