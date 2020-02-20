<?php

/**
 * Device detection adapter that uses Tera-WURFL to detect devices.
 *
 * @author 
 * @version $id$
 *
 */
class Nexva_DeviceDetection_TeraWurflDeviceDetector implements Nexva_DeviceDetection_Interface_DetectionInterface {

  private static $__instance;
  protected static $_wurfl = null;

  private function __construct() {

  }

  private function __clone() {

  }

  public static function getInstance() {
    if (!isset(self::$__instance)) {
      $class = __CLASS__;
      self::$__instance = new $class;
    }

    return self::$__instance;
  }

  public function getAdapterName() {
    return "TeraWurfl";
  }

  /**
   * Finds and returns neXva's internal device Id representation. For this adapter, we use 'devices' table.
   *
   * @return int || null
   */
  public function getNexvaDeviceId() {
    $deviceId = NULL;

    if (is_null(self::$_wurfl))
      throw new Zend_Exception('Device not detected. Please call detectDeviceByUserAgent() first');

     if($this->getDeviceAttribute('is_wireless_device', 'product_info')	){
      
    $deviceModel = new Model_Device();
    // product model and brand
    // $db = Zend_Registry::get('dbcon');
    
    // $marketing_name = mysqli_real_escape_string( $db, $this->getDeviceAttribute('marketing_name', 'product_info'));

    $marketing_name = $this->getDeviceAttribute('marketing_name', 'product_info');
  
    $marketing_name = !empty ($marketing_name) ? ' (' . $marketing_name . ')' : '';
 

    /*
    $row = $deviceModel->fetchRow("brand LIKE '%?" .mysqli_real_escape_string($db , $this->getDeviceAttribute('brand_name', 'product_info')) .
       "%' AND model LIKE '%" . mysqli_real_escape_string( $db, $this->getDeviceAttribute('model_name', 'product_info')) . $marketing_name . "%'");
	*/
    
    $row =  $deviceModel->fetchRow(
    						$deviceModel->select()
    									->where("brand LIKE ?", '%'.$this->getDeviceAttribute('brand_name', 'product_info').'%')
    									->where("model LIKE ?", '%'.$this->getDeviceAttribute('model_name', 'product_info') . $marketing_name.'%') 
    
    		);

   
    if (is_null($row)) { // check for wurfl device id
      $row = $deviceModel->fetchRow("wurfl_device_id = '" . $this->getDeviceAttribute('id') . "'");
      if (is_null($row)) { // check for actual root device id
        $row = $deviceModel->fetchRow("wurfl_actual_root_device = '" . $this->getDeviceAttribute('actual_root_device', 'tera_wurfl') . "'");
        if (is_null($row)) { // check for first fallback
          $row = $deviceModel->fetchRow("wurfl_actual_root_device = '" . $this->getDeviceAttribute('fall_back') . "'");
          if (is_null($row)) {// check for all fallbacks
            $fallbackDevices = split(',', $this->getDeviceAttribute('fall_back_tree', 'tera_wurfl'));
            $fallbackDevices = array_reverse($fallbackDevices);
            // @TODO: we shoud limit this to 3,4 levels
            $i = 0;
            foreach ($fallbackDevices as $device) { // we are trying to fallback to any other device root, now we should insert new Device
              if ($i == 2)
                break;
              $row = $deviceModel->fetchRow("wurfl_actual_root_device = '" . $device . "'");
              if (!is_null($row)) {
                $deviceId = $row->id;
                break;
              }
              $i++;
            }
          }
          else
            $deviceId = $row->id;
        }
        else
          return $row->id;
      }
      else
        return $row->id;
    }
    else
      return $row->id;

     $db = '';
    // if found the id on wurfl but not in our database then add it to our DB
    $inserId = $this->insertDevice();
    if ($deviceId)
      return $deviceId;
    else if ($inserId)
      return $inserId;
    else {

      return null;
      
    }
    }
  }

  /**
   * Detects a device based on the user agent (UA) thats passed in
   *
   * @param string $userAgent
   * @return int
   */
  public function detectDeviceByUserAgent($userAgent = null) {
    
    $wurfl = new Nexva_DeviceDetection_TeraWurfl();
    
    if($userAgent)
    	$result = $wurfl->getDeviceCapabilitiesFromAgent($userAgent);


    self::$_wurfl = $wurfl;

    return $result;
  }

  /**
   * Returns the raw results of the last device detection call. In this adapter, it's the WURFL object.
   * It's recommended that you use getDeviceAttribute() to access individual device attributes
   *
   * @return mixed
   */
  public function getResult() {
    return self::$_wurfl;
  }

  /**
   * Returns the (full path) image for the detect device. If detectDeviceByUserAgent() is not previously called, pass in $userAgent
   *
   * @param string $userAgent
   * @return strting
   */

  /**
   * Gets a attribute from the detected device.
   *
   * @param string $attributeName
   * @param string $groupName
   * @return string     
   */
  public function getDeviceAttribute($attributeName, $groupName=null) {

    if (is_null(self::$_wurfl)

      )throw new Zend_Exception('Device not detected. Please call detectDeviceByUserAgent() first');

    if (!is_null($groupName)) {
      return self::$_wurfl->capabilities[$groupName][$attributeName];
    } else {
      return self::$_wurfl->capabilities[$attributeName];
    }
    

  }

  /**
   * private
   * Insert device data if not found in nexva DB
   * @return <int> devuceId
   */
  private function insertDevice() {
    // device Model
    $deviceModel = new Model_Device();
    $deviceAttribModel = new Model_DeviceAttributes();
    //get device attrivutes
    $id = $this->getDeviceAttribute('id');
    $ua = $this->getDeviceAttribute('user_agent');
    $brand = $this->getDeviceAttribute('brand_name', 'product_info');
    $model = $this->getDeviceAttribute('model_name', 'product_info');
    $actualRootDeviceId = $this->getDeviceAttribute('actual_root_device', 'tera_wurfl');

    $dataDevice = array('useragent' => $ua, 'brand' => $brand, 'model' => $model, 'wurfl_device_id' => $id, 'wurfl_actual_root_device' => $actualRootDeviceId);

    $insertId = $deviceModel->insert($dataDevice);

    // update the CP nofificaiton database
    $deviceModel->deviceUpdateNotify($insertId);

    // insert all metaData
    $attributes = array();
    $attributes[1] = $this->getDeviceAttribute('device_os', 'product_info');
    $attributes[2] = $this->getDeviceAttribute('pointing_method', 'product_info');
    $attributes[3] = $this->getDeviceAttribute('device_os_version', 'product_info');
    $attributes[4] = $this->getDeviceAttribute('mp3', 'sound_format');
    $attributes[5] = $this->getDeviceAttribute('j2me_midp_1_0', 'j2me');
    $attributes[6] = $this->getDeviceAttribute('j2me_midp_2_0', 'j2me');
    $attributes[7] = $this->getDeviceAttribute('resolution_width', 'display');
    $attributes[8] = $this->getDeviceAttribute('resolution_height', 'display');

    foreach ($attributes as $attribId => $value) {
      $data = array(
        'device_attribute_definition_id' => $attribId,
        'device_id' => $insertId,
        'value' => $value
      );

      $deviceAttribModel->insert($data);
    }

    return $insertId;
  }
  
  
  public function getDeviceImage($userAgent=null)	{
  	
  	
  	
  }
  

}

