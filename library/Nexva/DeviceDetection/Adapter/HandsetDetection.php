<?php

/**
 * Device detection adapter that uses Tera-WURFL to detect devices.
 *
 * @author jahufar
 * @version $id$
 *
 */
#class Nexva_DeviceDetection_Adapter_HandsetDetection implements Nexva_DeviceDetection_Interface_DetectionInterface {
class Nexva_DeviceDetection_Adapter_HandsetDetection  {

  private static $__instance;
  protected static $_handsetDetection = null;
  protected static $_responce = null;
  protected static $_userAgent = null;

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
    return "HandsetDetection";
  }

  /**
   * Finds and returns neXva's internal device Id representation. For this adapter, we use 'devices' table.
   * 
   * @return int || null
   */
  public function getNexvaDeviceId($useragent) {
      
    $deviceId = NULL;

    $deviceModel = new Model_Device();
   
    // check for neXva device table for user agnet
    $row = $deviceModel->fetchRow("useragent = '".$useragent."' and detection_type = 'handset'");
    
    if(count($row) > 0) 
        return $row;
    else {
        //$deviceId = $this->detectDeviceByUserAgent($useragent);
        //$row =  $deviceModel->nexvaDeviceInfo();
        
        
        // this is a temporary fix need to find elegant solution 
        //if('113.59.222.103' == $_SERVER['REMOTE_ADDR']) {
        	//Zend_Debug::dump($useragent);
        	//Zend_Debug::dump($row);
        
        	include_once( APPLICATION_PATH .'/../public/vendors/Mobile-Detect-2.8.17/Mobile_Detect.php');
        	$detect = new Mobile_Detect;
        
        	if($detect->isMobile() || $detect->isTablet()) {
        	    
        	   // Zend_Debug::dump($detect->isAndroidOS());
        
        		if($detect->isAndroidOS())
        			return $deviceModel->fetchRow("id = 792421");
        
        		if($detect->isiOS() || $detect->isiphone() || $detect->isIOS() || $detect->isIphone() ||  $detect->isiPad())
        			return $deviceModel->fetchRow("id = 633311");
        
        		if($detect->isBlackBerry())
        			return $deviceModel->fetchRow("id = 1104467");
        
        	}
        
        	return $deviceModel->fetchRow("id = 9087");
        
        
        
       // }   // this is a temporary fix need to find elegant solution 
        
        
        /*
        
        if(is_null($row))
            return $deviceModel->fetchRow("id = 792421");
        else */
            return $deviceModel->fetchRow("useragent = '".$useragent."' and detection_type = 'handset'");
    }
      


  }

  /**
   * Detects a device based on the user agent (UA) thats passed in
   *
   * @param string $userAgent
   * @return  boolean Returns whether the match was exact or not 
   */
  public function detectDeviceByUserAgent($userAgent = null) {

        require_once('../library/Nexva/DeviceDetection/HandsetDetection/hd3.php');
    	$hnadsetDetection3 = new HD3();
    	$hnadsetDetection3->setDetectVar('ipaddress', $_SERVER['REMOTE_ADDR']);
    	$hnadsetDetection3->setDetectVar('user-agent', $userAgent);
        
        if($hnadsetDetection3->siteDetect(array('options' => 'hd_specs,geoip,legacy,product_info,display'))) {
            $response = (!is_null($hnadsetDetection3->getReply()) ?  $hnadsetDetection3->getReply() : '' );
            
            self::$_handsetDetection = $hnadsetDetection3;
            self::$_responce = $response;
            self::$_userAgent = $userAgent;
            
            $deviceId = $this->insertDevice($userAgent, 'mobile');
            $this->getDeviceImage($userAgent);
            return $deviceId;
            
        } else {
            
         
            if($hnadsetDetection3->getError()){
                
            /*    
            $deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();
            $exactMatch = $deviceDetector->detectDeviceByUserAgent();
            $isWireless = $deviceDetector->getDeviceAttribute('is_wireless_device', 'product_info');
            $browserType = $deviceDetector->getDeviceAttribute('brand_name', 'product_info');
            if($browserType == 'generic web browser') {
         
               $deviceId = $this->insertDevice($userAgent, 'desktop');
               return $deviceId;
                
            }    else    {
                
                // Could not find the device... email is send to nexva admin
                $config = Zend_Registry::get("config");
                $emails = $config->nexva->application->content_admin->contact;
                
                
                $newMails = explode(',', $emails);
                
                $mailer = new Nexva_Util_Mailer_Mailer();
                $mailer->setSubject('neXva - Problem Detecting User-Agent');
                
                foreach ($newMails as $mail) {
                	$mailer->addTo($mail, $mail);
                }
                
            
                
                $mailer->setLayout("generic_mail_template")
                       ->setMailVar("ip", $_SERVER['REMOTE_ADDR'])
                       ->setMailVar("userAgent", $_SERVER['HTTP_USER_AGENT'])
                       ->sendHTMLMail('new_mobile_device_detected.phtml');
                       
              
              
            }  */
            }

        }


  }

  /**
   * Returns the raw results of the last device detection call. In this adapter, it's the WURFL object.
   * It's recommended that you use getDeviceAttribute() to access individual device attributes
   *
   * @return mixed
   */
  public function getResult() {
    return self::$_handsetDetection;
  }

  /**
   * Returns the (full path) image for the detect device. If detectDeviceByUserAgent() is not previously called, pass in $userAgent
   *
   * @param string $userAgent
   * @return strting
   */
  public function getDeviceImage($userAgent=null) {
     
      return;
    
    $response =  self::$_responce;
    
    $imageName = $response['hd_specs']['general_image'];

    if($imageName) {
        
        /*
        any of the follwing methods will work 
        
        //method 1
        $content = file_get_contents('https://hdimages-raw.s3.amazonaws.com/samsunggalaxys4-1366946263-0.jpg');
        //Store in the filesystem.
        $fp = fopen(APPLICATION_PATH.'/../public/vendors/device_pix/samsunggalaxys4-1366946263-0.jpg', "w");
        fwrite($fp, $content);
        fclose($fp);
        
        //method 2
        copy('https://hdimages-raw.s3.amazonaws.com/samsunggalaxys4-1366946263-0.jpg', APPLICATION_PATH.'/../public/vendors/device_pix/samsunggalaxys4-1366946263-0.jpg');
        
        */
       
      //  $client = new Zend_Http_Client('https://hdimages-raw.s3.amazonaws.com/'.$imageName, array(
       // 		'keepalive' => true
      ///  ));

        
      //  $client->setStream();
       // $response = $client->request('GET');
        
        // copy file 
        copy('https://hdimages-raw.s3.amazonaws.com/'.$imageName, APPLICATION_PATH.'/../public/vendors/device_pix/'.$imageName);
        
        //copy('https://hdimages-raw.s3.amazonaws.com/samsunggalaxys4-1366946263-0.jpg', APPLICATION_PATH.'/../public/vendors/device_pix/samsunggalaxys4-1366946263-0.jpg');
        
        if(file_exists(APPLICATION_PATH.'/../public/vendors/device_pix/'.$imageName)) {
        	return "/vendors/device_pix/" . $imageName;
        } else {
        	return null;
        }

    return null;
    
    }

    return null;
  }

  /**
   * Gets a attribute from the detected device.
   *
   * @param string $attributeName
   * @param string $groupName
   * @return string     
   */
  public function getDeviceAttribute($attributeName, $groupName=null) {

    if (is_null(self::$_handsetDetection)) {
        throw new Zend_Exception('Device not detected. Please call detectDeviceByUserAgent() first');
    }


    
        return self::$_responce[$groupName][$attributeName];
  }

  /**
   * private
   * Insert device data if not found in nexva DB
   * @return <int> devuceId
   */
  public function insertDevice($userAgent, $type = 'mobile') {
    
    if($type == 'mobile')    {

        

        // device Model
        $deviceModel = new Model_Device();
        $deviceAttribModel = new Model_DeviceAttributes();
        //get device attrivutes

        $marketingName = ' ';
        $ua = $userAgent;
        $brand = @$this->getDeviceAttribute('brand_name', 'product_info');
        $model = @$this->getDeviceAttribute('model_name', 'product_info');
        $actualRootDeviceId = '';
        $deviceImageUrl = @$this->getDeviceAttribute('hd_image1', 'product_info'); 
        $platform = @$this->getDeviceAttribute('device_os', 'product_info');
        $pointingMethod = @$this->getDeviceAttribute('pointing_method', 'product_info');
        $deviceOsVersion = @$this->getDeviceAttribute('device_os_version', 'product_info');
        $marketingName = @$this->getDeviceAttribute('marketing_name', 'product_info');
        $resolutionWidth = @$this->getDeviceAttribute('max_image_width', 'display');
        $resolutionHeight = @$this->getDeviceAttribute('max_image_height', 'display');
        //$fullDetailJson = Zend_Json::encode(self::$_responce);
        $fullDetailJson = $_SERVER['REMOTE_ADDR'];
        $mp3Support = @$this->getDeviceAttribute('media_audio', 'hd_specs');
       
        $mp3 = 0; 
    
        if(!empty($mp3Support))
        {
            if(in_array("MP3", $mp3Support)) {
            $mp3 = 1;
            }
        }

        // hack for nokia asha
        if(strpos($marketingName,'Asha') !== false) {
            $platform = 'Java' ;
            $j1MeMid = 2;
        } else 
            $j1MeMid = 0;
        
        if(strpos($marketingName,'asha') !== false) {
        	$platform = 'Java' ;
        	$j2MeMid = 2;
        } else 
            $j2MeMid = 0;
        

        
        //	
            
            $dataDevice = array('useragent' => $ua, 'brand' => $brand, 
                    'model' => $model, 
                    'wurfl_actual_root_device' => $actualRootDeviceId, 
                    'device_image_url' => $deviceImageUrl, 
                    'platform' => $platform, 
                    'pointing_method' => $pointingMethod, 
                    'device_os_version' => $deviceOsVersion, 
                    'mp3' => $mp3, 
                    'j2me_midp_1_0' => $j1MeMid, 
                    'j2me_midp_2_0' => $j2MeMid, 
                    'marketing_name' => $marketingName, 
                    'resolution_width' => $resolutionWidth, 
                    'resolution_height' => $resolutionHeight, 
                    'full_detail_json' => $fullDetailJson, 
                    'is_mobile_device' => '1',
                    'detection_type' => 'handset', 
                    'called_url' =>  $_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI]);
            

    $insertId = $deviceModel->insert($dataDevice);


    /* this is for debugging purposes .. 
     
    $db         = Zend_Registry::get('db');
    $profiler   = $db->getProfiler();
    $config     = Zend_Registry::get('config');
    $total      = 0;

    foreach ($profiler->getQueries() as $id=>$query) {
    	$duration   = $query->getElapsedSecs();
    	echo  "<br>".$duration . "<br>";
    	echo  isset($traces[$id]) ? $traces[$id] : 'BLANK'  . "<br>";
    	echo   $query->getQuery(). "<br>";
    	$total      += $duration;
    }
    $totalQueries   = count($profiler->getQueries());
    $footer         = "Total Queries : {$totalQueries}\nTotal Time Spent : {$total} seconds";
    echo $footer;

    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    $db->getProfiler()->setEnabled(true);
    $profiler = $db->getProfiler();
    
    // Execute your any of database query here like select, update, insert
    //The code below must be after query execution
    $query  = $profiler->getLastQueryProfile();
    $params = $query->getQueryParams();
    $querystr  = $query->getQuery();
    
    foreach ($params as $par) {
    	$querystr = preg_replace('/\\?/', "'" . $par . "'", $querystr, 1);
    }
    echo $querystr;
    */

    // update the CP nofificaiton table
    $deviceModel->deviceUpdateNotify($insertId);
   
    // to compatible with product selection method
    if($platform == 'BlackBerry')
    	$platform = 'RIM OS';

    // insert all metaData
    $attributes = array();
    $attributes[1] = $platform;
    $attributes[2] = $pointingMethod;
    $attributes[3] = $deviceOsVersion;
    $attributes[4] = $mp3;
    $attributes[5] = $j1MeMid;
    $attributes[6] = $j2MeMid;
    $attributes[7] = $resolutionWidth;
    $attributes[8] = $resolutionHeight;
    
    foreach ($attributes as $attribId => $value) {
      $data = array(
        'device_attribute_definition_id' => $attribId,
        'device_id' => $insertId,
        'value' => $value
      );
      
      $deviceAttribModel->insert($data);
    }
    

    // send an email to admins
    // @TODO : add observer pattern to this
  //  if (APPLICATION_ENV == 'production') {
      $mailer = new Nexva_Util_Mailer_Mailer();
      $mailer->setSubject("New device($brand $model.'-'.$marketingName) added to the neXva");
//    $mailer->addTo(Zend_Auth::getInstance()->getIdentity()->email, Zend_Auth::getInstance()->getIdentity()->username)
/*       $mailer->addTo('chathura@nexva.com', 'Chathura')
          ->addCc('shaun@nexva.com', 'Shaun')
          ->addCc('developers@nexva.com', 'Developer')
          ->setLayout("generic_mail_template")         //change if needed. remember, stylesheets cannot be linked in HTML email, so it has to be embedded. see the layout for more.
          ->setMailVar("device", $dataDevice)
          ->setMailVar("deviceId", $insertId)
          ->setMailVar("deviceAttributes", $attributes)
          ->setMailVar("fullAttributes", self::$_responce)
          ->sendHTMLMail('new_device.phtml'); */ //change this. mail templates are in /views/scripts/mail-templates
   // }
    return $insertId;
  }
  
  
  if($type == 'desktop') {
      
      $dataDevice = array( 'useragent' => $userAgent,
      		'brand' => 'generic web browser',
      		'model' => '',
      		'wurfl_actual_root_device' => '',
      		'device_image_url' => '',
      		'platform' => '',
      		'pointing_method' => '',
      		'mp3' => '',
      		'j2me_midp_1_0' => '',
      		'j2me_midp_2_0' => '',
      		'marketing_name' => '',
      		'resolution_width' => '',
      		'resolution_height' => '',
      		'full_detail_json' => '',
      		'is_mobile_device' => '0',
            'detection_type' => 'handset'
      );
      
      $deviceModel = new Model_Device();
      $insertId = $deviceModel->insert($dataDevice);

      return $insertId;
        
  }
  
  }

}

?>
