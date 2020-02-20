<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_Device extends Zend_Db_Table_Abstract {

    protected $_name = 'devices';
    protected $_id = 'id';
    protected $_dependentTables = array('Model_DeviceAttributes', 'Model_ProductBuildDevices');

    function __construct() {
        parent::__construct();
    }

    /**
     * Get all the devices list
     * @return <type>
     */
    public function getAllDevies() {
        $resultSet = $this->fetchAll();
        $css = 'all';
        foreach ($resultSet as $row) {
            $name = $this->trimAndElipsis($row->brand . ' ' . $row->model, 100, '...');
            $phones[$row->id]['id'] = $row->id;
            $phones[$row->id]['img'] = '/vendors/device_pix/' . $row->wurfl_actual_root_device . '.gif';
            $phones[$row->id]['phone'] = $name;
            $phones[$row->id]['css'] = $css;
        }
        return $phones;
    }

    /**
     * This function proxies getAllDevies() - because it was not spelt right and no one wants to correct it.
     *
     * @todo: Person responsible for getAllDevies() - please find all occurences of where this is called then change it to call this function
     * and then copy the implementation of getAllDevies() over to this functiona remove getAllDevies();
     *  - jahufar
     *
     * @return array
     */
    public function getAllDevices() {
        return $this->getAllDevies();
    }

    public function getAllDevicesWithoutDeviceImages() {

        $rows = $this->fetchAll();

        $devices = array();

        $deviceImagePath = Zend_Registry::get('config')->nexva->application->deviceImagePath;

        foreach ($rows as $row) {
            //Zend_Debug::dump($row);
            //Zend_Debug::dump(APPLICATION_PATH);die();

            if (!file_exists($deviceImagePath . "/" . $row->wurfl_device_id . ".gif"))
                array_push($devices, $row);
        }

        return $devices;
    }

    /**
     * Search devices by Where and Join
     * @param <type> $where
     * @param <type> $join
     * @return <type>
     */
    public function searchDevicsByWhereAndJoin($where, $join) {
        $select = $this->select('devices.id, devices.brand, devices.model');
        $select->setIntegrityCheck(false);
        foreach ($join as $table => $joinString) {
            $select->joinInner('device_attributes as ' . $table, $joinString);
        }
        $select->where(implode(' and ', $where));
        $select->order('brand ASC');
        $select->order('model ASC');
//    Zend_Debug::dump($select->__toString());
//    echo $select->__toString();
//    exit;
        $resultSet = $this->fetchAll($select);
        $devicesAttributes = array();
        foreach ($resultSet as $device) {
            $deviceAttributes = array();
            $deviceAttributes = $this->getDeviceAttributesById($device->device_id);
            $devicesAttributes = array_merge($devicesAttributes, $deviceAttributes);
        }
        return $devicesAttributes;
    }

    /**
     * Search Devices by a Where
     * @param <type> $where
     * @return <type>
     */
    public function searchDevicesByWhere($where) {
        $table = new Model_Device();
        $resultSet = $this->fetchAll(
                    $table->select()
                    ->where($where)
                    ->limit(27)
                    ->group('wurfl_actual_root_device')
        );

        $devicesAttributes = array();
        foreach ($resultSet as $device) {
            $deviceAttributes = array();
            $deviceAttributes = $this->getDeviceAttributesById($device->id);
            $devicesAttributes = array_merge($devicesAttributes, $deviceAttributes);
        }
        return $devicesAttributes;
    }

    public function findDevicesByKeyword($keyword, $limit = 12) {
        /* this is the right wayto do it see this 
        $deviceSearch = new Zend_Db_Table('device_search');
        $resultSet = $deviceSearch->fetchAll(
                    $deviceSearch->select()
                    ->distinct()
                    ->where("keywords LIKE '%$keyword%'")
                    ->group('wurfl_actual_root_device')
                    ->limit($limit)
        );
        */
        
       // >from('devices',array("total" => "count(*)"))

        $deviceSearch = new Zend_Db_Table('devices');
        $resultSet = $this->fetchAll($this->select()->from(array('p' =>'devices'), array('p.id as device_id', 'p.brand', 'p.model', 'p.wurfl_actual_root_device'))
                    ->distinct()
                    ->where("model LIKE '%$keyword%'")
                    ->orWhere("brand LIKE '%$keyword%'")
                    ->group('wurfl_actual_root_device')
                    ->order('p.id DESC')
                    ->limit($limit)
        );
        //->order(array('p.id DESC', 'date DESC'));
        
        return $resultSet;
    }
    
    
    public function nexvaDeviceInfo() {

    
        $deviceSearch = new Zend_Db_Table('devices');
        $resultSet = $this->fetchRow($this->select()->from(array('p' =>'devices'), array('*'))
                    ->distinct()
                    ->where("useragent LIKE '%".$_SERVER['HTTP_USER_AGENT']."%'")
                    ->Where("detection_type = 'handset'")

    	);
        
    
    	return $resultSet;
    }
    
    /**
     * Get Device Attributes by Deviced ids 
     * @param array  $ids deviced ids 
     * @return Obj Device info
     */
    
    public function findDevicesByIds($ids, $limit = 5) {
        if (empty($ids)) return array();
        
        $deviceSearch = new Zend_Db_Table('device_search');
        $resultSet = $deviceSearch->fetchAll(
                    $deviceSearch->select()
                    ->distinct()
                    //->setIntegrityCheck(false)
                    //-join('devices','devices.id = device_search.device_id')
                    ->where('id IN(?)', $ids)
                    ->order('device_id DESC')
                    ->limit($limit)
        );
        /*->setIntegrityCheck(false)
            ->columns(array('products.name AS product_name','products.id AS pro_id'))
            ->join('product_categories', 'product_categories.product_id = products.id')*/
       
        return $resultSet;
    }

    /**
     * Get Device Attributes by device Id
     * @param <type> $deviceId
     * @return <type>
     */
    public function getDeviceAttributesById($deviceId, $show_attributes = false) {
        $phones = array();
        $deviceRowset = $this->find($deviceId);
        $device = $deviceRowset->current();
        $name = $device->brand . ' ' . $device->model;
        // construct device array
        $phones[$device->id]['id'] = $deviceId;
        $phones[$device->id]['phone'] = $name;
        $img = '/vendors/device_pix/' . $device->wurfl_actual_root_device . '.gif';
        if (!file_exists(APPLICATION_PATH . '/../public' . $img)) {
            $device->wurfl_actual_root_device . '.gif';
            $img = '/mobile/images/mobile_phone_img.gif';
        }
        $phones[$device->id]['img'] = $img;
        $phones[$device->id]['css'] = 'all';
        if ($show_attributes) {
            $attributesByProduct = $device->findDependentRowset('Model_DeviceAttributes');
            $attributes = array();
            $resolution = array();
            foreach ($attributesByProduct as $attributed) {
//                print_r($attributed->toArray());
                // TODO : do a database synchronization with this array
                $attributeDefinitions = array('Device OS', 'Pointing Method', 'Device OS Version', 'Support MP3',
                  'Support MIDP 1.0', 'Support MIDP 2.0');
                $attributeDeff = array(1, 2, 3, 4, 5, 6);
                $arrtibDefId = $attributed->device_attribute_definition_id;
                if ($arrtibDefId == 3 || empty($attributed->value))// skip all deivce os version arttributes or no value
                    continue;
                if (($attributed->value != 1) && $arrtibDefId != 7 && $arrtibDefId != 8)
                    $attributes[] = str_replace($attributeDeff, $attributeDefinitions,
                            $attributed->device_attribute_definition_id) . ($attributed->value != 1) ? $attributed->value : '';
                if ($attributed->value == 1)
                    $attributes[] = str_replace($attributeDeff, $attributeDefinitions,
                            $attributed->device_attribute_definition_id);

                if ($arrtibDefId == 7 || $arrtibDefId == 8) { // device width and height
                    $resolution[] = $attributed->value;
                }
            }
            // show only resolution is there
            if (is_array($resolution) && !empty($resolution))
                $attributes[] = 'Resolution : ' . implode('X', $resolution);
            $phones[$device->id]['info'] = $attributes;
        }
        return $phones;
    }

    public function getDeviceNameById($deviceId) {
        $row = $this->find($deviceId);
        $device = $row->current();
        return $device->brand . ' ' . $device->model;
    }

    /**
     *
     * @param <type> $string
     * @param <type> $count
     * @param <type> $ellipsis
     * @return <type>
     */
    private function trimAndElipsis($string, $count, $ellipsis = FALSE) {
        $length = '';
        if (strlen($string) > $count) {
            $length -= strlen($ellipsis);  // $length =  $length – strlen($end);
            $string = substr($string, 0, $count);
            $string .= $ellipsis;  //  $string =  $string . $end;
        }
        return $string;
    }

    public function attribFilterDevices($search) {
        $db = Zend_Registry::get('db');
        $keywords = explode('&', $search);
        $attributeDefinitions = array(
          'mp3_playback' => 4,
          'java_midp_1' => 5,
          'java_midp_2' => 6,
          'width' => 7,
          'height' => 8,
          'navigation_method' => 2,
          'device_os' => 1
        );
        if (is_array($keywords)) { // explode by space and construct the where of the query
            foreach ($keywords as $key => $attributeTerm) {
                $attribVal = null;
                $attribTerms = explode('=', $attributeTerm);
                $attribKey = $attribTerms[0];
                if (!empty($attribTerms[1]))
                    $attribVal = $attribTerms[1];
                // construct joins and where
                $join[$attribKey] = $attribKey . '.device_id = devices.id';
                if ($attribKey == 'navigation_method' || $attribKey == 'device_os')
                    $attribVal = ' = "' . $attribVal . '"';
                else
                    $attribVal = (!empty($attribVal)) ? '>= ' . $attribVal : '= 1';
                $definitionId = $attributeDefinitions[$attribKey];
                $where[] = '(' . $attribKey . '.device_attribute_definition_id = ' . $definitionId . ' and ' . $attribKey . '.value ' . $attribVal . ')';
//        $wheres[] = '(device_attribute_definition_id = ' . $definitionId . ' and value ' . $attribVal . ')';
            }
        }
        $whereQuery = implode(' AND ', $where);
        $sql = '';
        $count = count($join);
        $i = 0;
        $joinQuery = '';
        foreach ($join as $tmpTable => $argument) {
            $joinQuery .= ' INNER JOIN device_attributes AS ' . $tmpTable . ' ON ' . $argument;
        }
        $sql = "SELECT
            devices.brand,
             GROUP_CONCAT(DISTINCT devices.model
                    ORDER BY devices.model DESC SEPARATOR ', ') AS 'models'
            FROM
            devices
            $joinQuery
            WHERE
            $whereQuery
            GROUP BY
            devices.brand";
//    echo $sql;
//    exit;
        $stmt = $db->query($sql); //probably not a good idea this - but I could not fig out how to use GROUP_CONCAT in a select() object
        $resultRows = $stmt->fetchAll();
//    print_r($resultRows);
//    exit;
        return $resultRows;
//    return $this->searchDevicsByWhereAndJoin($where, $join);
    }

    /**
     * Returns an array of device IDs of the currently selected devices in the session
     * Only applicable for web FO.
     *
     * @deprecated
     *
     * @return array An array of devices IDs that are currently selected.
     */
    public function getSelectedDevicesFromSession() {

        $session = new Zend_Session_Namespace("devices");
        $devices = $session->selectedDevices;

        $result = array();

        if (0 == count($devices))
            return array(); //no devices selected

            foreach ($devices as $device) {
            $result[] = $device['id'];
        }


        return $result;
    }

    public function getAppCountById($deviceId) {
//    $buildDevices = new B
    }

    public function deviceExists($switcher, $values) {
        switch ($switcher) {
            case 'useragent':
                $row = $this->fetchRow(
                        "useragent LIKE '" . mysql_escape_string($values['useragent']) . "'"
                );
                break;
            case 'wurfl_device_id':
                $row = $this->fetchRow(
                        "wurfl_device_id LIKE '" . mysql_escape_string($values['wurfl_device_id']) . "'"
                );
                break;
            case 'model':
                $row = $this->fetchRow(
                        "model LIKE '" . mysql_escape_string($values['model']) . "' and brand LIKE '" . mysql_escape_string($values['brand']) . "'"
                );
                break;
        }
        if (empty($row['id']))
            return FALSE;
        return $row;
    }

    // add new device attributes or update existing
    public function updateDeviceAttributes($attributes, $deviceId) {
        $modelAttrib = new Model_DeviceAttributes();

        foreach ($attributes as $attribId => $value) {
            $data = array(
              'device_attribute_definition_id' => $attribId,
              'device_id' => $deviceId,
              'value' => $value
            );
            // cehck the values are exists
            $row = $modelAttrib->fetchRow(
                        $modelAttrib->select()
                        ->where('device_attribute_definition_id = ?', $attribId)
                        ->where('device_id = ?', $deviceId)
            );
            if (!empty($row->id)) {
                // if the values are different
                if ($value != $row->value) {
                    // do update
                    $update = $modelAttrib->update($data, 'id = ' . $row->id);
                }
            } else {
                // insert new ids
                $insert = $modelAttrib->insert($data);
            }
        }
    }

    public function getDeviceAttributes($deviceId) {
        if (empty($deviceId))
            return;

        $cache  = Zend_Registry::get('cache');
        //sorted so that we don't get multiple entries just because the order is different - JP
        if (is_array($deviceId)) {
            sort($deviceId);    
            $key    = 'DEVICEATTR_KEY_' . implode(',', $deviceId);    
        } else {
            $key    = 'DEVICEATTR_KEY_' . $deviceId;
        }





        if (($attributes = $cache->get($key)) === false) {
            $deviceRowset = $this->find($deviceId);
            $device = $deviceRowset->current();
            $attributes = $device->findDependentRowset('Model_DeviceAttributes');
            //we're wasting a bit of space here putting the whole rowset instead of only the data array - JP
            //Needed because the rowset object can't be remade
            $cache->set($attributes, $key); 
        }
        return $attributes;
    }

    public function deviceUpdateNotify($deviceId) {
        $model = new Model_DeviceUpdate();
        $data = array('device_id' => $deviceId);
        $model->insert($data);
    } 

    /*
     * Returns the device platform
     */
    public function getDevicePlatformById($deviceId) {
        $row = $this->find($deviceId);
        $device = $row->current();
        if($device){
            return $device->platform;
        }
        else{
            return null;
        }
    }
}

?>
