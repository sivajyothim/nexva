<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 10/30/13
 * Time: 6:39 PM
 * To change this template use File | Settings | File Templates.
 */

class Partnermobile_TestController extends  Nexva_Controller_Action_Partnermobile_MasterController {


    public function init() {

        //die('soiegpiehg');
        //parent::init();

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
        //$hd3->setDetectVar('user-agent','Mozilla/5.0 (SymbianOS/9.2; U; Series60/3.1 NokiaN95-3/20.2.011 Profile/MIDP-2.0 Configuration/CLDC-1.1 ) AppleWebKit/413');
        $hd3->setDetectVar('user-agent','Mozilla/5.0 (Linux; U; Android 4.2.2; zh-cn; GT-I9500 Build/JDQ39) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30; 360browser(securitypay,securityinstalled); 360(android,uppayplugin); 360 Aphone Browser (4.8.0)');
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
        
        */
        die();
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
    
    	 
    	$dd = $deviceDetection->getNexvaDeviceIdd($_SERVER['HTTP_USER_AGENT']);
    
    	//Zend_Debug::dump('fgdfgdfgfdgdgdgf');
    	Zend_Debug::dump($dd);
    	die();
    	// $ss = $deviceDetection->getDeviceImage($userAgent=null);
    	//   Zend_Debug::dump($ss);
    	 
    	$deviceDetection->insertDevice();
    	 
    	 
    	// Zend_Debug::dump($tt, 'aaa');
    	die();
    	 
    	$ss = $deviceDetection->getDeviceAttribute('brand_name', 'product_info');
    	$ss = $deviceDetection->getDeviceAttribute('is_wireless_device', 'product_info');
    	$ss = $deviceDetection->getDeviceAttribute('marketing_name', 'product_info');
    	$ss = $deviceDetection->getDeviceAttribute('model_name', 'product_info');
    	$ss = $deviceDetection->getDeviceAttribute('hd_image1', 'product_info');
    	$ss = $deviceDetection->getDeviceAttribute('model_name', 'product_info');
    
    	Zend_Debug::dump($ss, 'ddd');
    	die();
    
    }
    
    public function ipTestAction() {
    
    	//	$_SERVER['HTTP_USER_AGENT'];
    
    	$to = 'chathura@nexva.com';
    	$subject = "neXva test";
    	$txt = "neXva test";
    	$headers = "From: chathura@nexva.com" . "\r\n" .
    			"CC: ";
    	 
    	//Zend_Debug::dump(apache_request_headers(),'dd');
    	//Zend_Debug::dump($_SERVER);
    
    	//	$a = var_export($_SERVER);
    	
  	Zend_Debug::dump(apache_request_headers(),'dd');
  	
  	$themeMeta   = new Model_ThemeMeta();
  	$themeMeta->setEntityId(283006);
  	$headers = apache_request_headers();
  	$headerIdentifierCode = $themeMeta->WHITELABLE_IP_HDR_IDENTIFIER;

  		Zend_Debug::dump($headers['X-WAP-MSISDN'], 'aa');
  		Zend_Debug::dump($headers['x-wap-msisdn'], 'bb');
  		


    
    	die('<b>Thank you</b>');
    
    
    
    }
    
    
}