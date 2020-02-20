<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 10/30/13
 * Time: 6:39 PM
 * To change this template use File | Settings | File Templates.
 */

class Mobile_TestController extends Nexva_Controller_Action_Mobile_MasterController {

    public function init() {

       $db       = Zend_Registry::get('db');
            $profiler   = new Nexva_Db_Profiler_Generic();
            $profiler->setEnabled(true);
            $db->setProfiler($profiler);
            Zend_Registry::set('db', $db);
    }

    public function indexAction()
    {
        //die('seuytgeirt');
        //echo 'awbjwhi';
        //$deviceDetector = Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
        //$deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();
        //Zend_Debug::dump($deviceDetector);die('edhyyhrty');

/*
        require_once('../library/Nexva/DeviceDetection/HandsetDetection/hd3.php');
        $hd3 = new HD3();

        echo "<pre>";
        $hd3->setDetectVar('ipaddress','64.34.165.180');
        $hd3->setDetectVar('user-agent','Mozilla/5.0 (Linux; Android 4.1.1; TECNO F7 Build/JRO03C) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.82 Mobile Safari/537.36');
       // $hd3->setDetectVar('user-agent','Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_0 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7A341 Safari/528.16');
        //$hd3->setDetectVar('x-wap-profile','http://nds1.nds.nokia.com/uaprof/NN95-1r100.xml');
        if ($hd3->siteDetect(array('options' => 'hd_specs,geoip,legacy,product_info,display'))) {
            $response = $hd3->getReply();

            print_r($response);
            echo $response['class'],'<br/>';
            echo $response['hd_specs']['general_vendor'],'<br/>';
            echo $response['hd_specs']['general_model'],'<br/>';
            if(!empty($response['hd_specs']['general_aliases']))
            {
                echo $response['hd_specs']['general_aliases'][0],'<br/>';
            }
            //echo $response['hd_specs']['general_image'];
            echo '<img src="https://hdimages-raw.s3.amazonaws.com/'.$response['hd_specs']['general_image'].'" />';//$response['hd_specs']['general_image'];


        } else {
            print $hd3->getError();
        }
        echo "</pre>";
        
        
        die();
        
        */
    }
    
    public function ipTestAction() {
    
    	//	$_SERVER['HTTP_USER_AGENT'];
    
    	$to = 'chathura@nexva.com';
    	$subject = "neXva test";
    	$txt = "neXva test";
    	$headers = "From: chathura@nexva.com" . "\r\n" .
    			"CC: ";
    
    	Zend_Debug::dump(apache_request_headers(),'dd');
    	Zend_Debug::dump($_SERVER);
    
    	//	$a = var_export($_SERVER);
    	
    	die('<b>Thank you</b>');
    
    	$dump = var_export($_SERVER, true);
    
    	mail($to,'User-Agent',$dump,$headers);
 
    
    
    
    }
    
    public function testhAction()
    {
    	//	die('seuytgeirt');
    	//echo 'awbjwhi';
    	//$deviceDetector = Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
    	//$deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();
    	//Zend_Debug::dump($deviceDetector);die('edhyyhrty');
    
    	$deviceDetection =  Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
    	// $tt = $deviceDetection->detectDeviceByUserAgent('Mozilla/5.0 (Linux; U; Android 4.2.2; zh-cn; GT-I9500 Build/JDQ39) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30; 360browser(securitypay,securityinstalled); 360(android,uppayplugin); 360 Aphone Browser (4.8.0)');
    
    	// $tt = $deviceDetection->detectDeviceByUserAgent('UCWEB/2.0(Java; U; MIDP-2.0; en-US; U2/1.0.0 UCBrowser/8.8.1.252 U2/1.0.0 Mobile UNTRUSTED/1.0 SonyEricssonP990i/R100 Profile/MIDP-2.0 Configuration/CLDC-1.1');
    	//Zend_Debug::dump($tt);
    	 
    
    	Zend_Debug::dump($_SERVER['HTTP_USER_AGENT']);
    
    	 
    	//$dd = $deviceDetection->getNexvaDeviceIdd('Mozilla/5.0 (Linux; Android 4.1.1; TECNO F7 Build/JRO03C) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.82 Mobile Safari/537.36');
    	$dd = $deviceDetection->getNexvaDeviceIdd('iPhone; U; CPU iPhone OS 3_0 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7A341 Safari/528.16');
    	
    	//Zend_Debug::dump('fgdfgdfgfdgdgdgfddd');
    	Zend_Debug::dump($dd);
    	die();
    	// $ss = $deviceDetection->getDeviceImage($userAgent=null);
    	//   Zend_Debug::dump($ss);
    	 
    
    
    }
    
    
    public function testbAction(){
        
    $deviceModel = new Model_Device();
            // Check whether this is a device previosly detected by the WURFL if the use WURFL
            // check for neXva device table for user agnet
            $row = $deviceModel->fetchRow("useragent = '".$_SERVER['HTTP_USER_AGENT']."' and detection_type = 'wurfl'");
            
            if(!is_null($row)) {
            
            	$deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();
            	$exactMatch = $deviceDetector->detectDeviceByUserAgent();
            	//If this is not a wireless device redirect to the main site
            	$isWireless = $deviceDetector->getDeviceAttribute('is_wireless_device', 'product_info');
            
            	// get properties from the Wurfl
            	$brandName = $deviceDetector->getDeviceAttribute('brand_name', 'product_info');
            	$modelName = $deviceDetector->getDeviceAttribute('model_name', 'product_info');
            	$marketing_name = $deviceDetector->getDeviceAttribute('marketing_name', 'product_info');
            	$inputMethod = $deviceDetector->getDeviceAttribute('pointing_method', 'product_info');
            	$osVersion = $deviceDetector->getDeviceAttribute('device_os_version', 'product_info');
            	$isWireless = $deviceDetector->getDeviceAttribute('is_wireless_device', 'product_info');
            	//get nexva device Id
            	$deviceId = $deviceDetector->getNexvaDeviceId();
            
            
            } else {
            
            	$deviceDetection =  Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
            	$deviceInfo = $deviceDetection->getNexvaDeviceId($_SERVER['HTTP_USER_AGENT']);
            	//If this is not a wireless device redirect to the main site
            	$isWireless = @$deviceInfo->is_mobile_device;
            
            
            	// get properties from the Wurfl
            	$brandName =  @$deviceInfo->brand;
            	$modelName =  @$deviceInfo->model;
            	$marketing_name =  @$deviceInfo->marketing_name;
            	$inputMethod =  @$deviceInfo->pointing_method;
            	$osVersion =  @$deviceInfo->device_os_version;
            	$exactMatch = $deviceInfo;
            	//get nexva device Id
            	$deviceId =  @$deviceInfo->id;
            	$isWireless =  @$deviceInfo->is_mobile_device;
            
            }
            
            Zend_Debug::dump($deviceInfo);
            
            echo $brandName;
            echo $deviceId;
            
            die();
  
    }
}