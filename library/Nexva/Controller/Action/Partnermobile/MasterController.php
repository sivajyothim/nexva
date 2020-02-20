<?php

/**
 * Parent controller for Partner Mobile Web 
 * @copyright   neXva.com
 * @author      Maheel
 */

class Nexva_Controller_Action_Partnermobile_MasterController extends Zend_Controller_Action {
       
    protected $_chapId;
    public $_deviceId;
    public $_deviceDetails;
    public $_isLowEndDevice;
    public $_chapLanguageId;
    public $_userId;
    protected $_grade;
    protected $_baseUrl;
    protected $_userCanDownloadFree;
    protected $_mobileNo;
    protected $_tempPassword;
    protected $_headerEnrichment;
    protected $_isHeaderEnrichmentActive;
    protected $_smsVerifyChap;
    
    public function init() 
    {
        

         if (!Zend_Auth::getInstance ()->hasIdentity ()) 
         {
            $skip_action_names = array ('login', 'register', 'forgotpassword', 'reset-password', 'authentication' ,'change-mobile', 
                                                        're-authentication','reset-password','forgot-password','forgot-verification','authentication','user-login-iran','remote-login','google-login', 'buy-app-by-redirection-pg');
            
            if (! in_array ( $this->getRequest ()->getActionName (), $skip_action_names )) 
            {
                $requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();

                /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                    print_r($requestUri);
                    die();
                }*/

                $session = new Zend_Session_Namespace ( 'lastRequest' );
                $session->lastRequestUri = $requestUri;
                $session->lock ();
                //$this->_redirect ( '/user/login' );
            }
        }
        
        //Getting grade from session for qelasy
        if($this->_chapId == 81604){
            $studentGrade = new Zend_Session_Namespace('studentGrade');
            if(isset($studentGrade->gradeId)){
                $this->_grade = $studentGrade->gradeId;
            }

        }

        
        $sessionChapDetails = new Zend_Session_Namespace('partnermobile');
        $this->_chapId = $sessionChapDetails->id;

        if($this->_chapId == 81604){
            //$this->_grade = 4; //This is for testing purposes, has to be dynamic and supposed to be provided through qelasy api
        }

        //Detect device
        $this->detectUserDevice();
        
        //get the device details
        $this->detectUserDeviceDetails();
      
        $this->_helper->CheckDevice->CheckDevice($this->_deviceId);     
        
         $brandName = $this->_deviceDetails['brand_name'];
         $deviceOs = $this->_deviceDetails['device_os'];
         $modelName = $this->_deviceDetails['model_name'];   

         //Set the user id if the user logged in
         $auth = Zend_Auth::getInstance();
		 
         if (isset($auth->getIdentity()->id)) {
            $this->view->loggedUser = $auth->getIdentity()->id;
            //Set logged in user id
            $this->_userId = $auth->getIdentity()->id;
         }
         else {
            $this->view->loggedUser = 'none';
            //$this->_userId = 22332;
            $this->_userId = NULL;
         }

       	/*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
     		echo $this->_userId; die();
    	 } */

        $themeMetaModel = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($this->_chapId);

        //assigning header enrichment details
        $this->_headerEnrichment = (($themeMetaModel->HEADER_ENRICHMENT == 'yes') ? TRUE : FALSE);

        //Get the default user for MTN chap
        if ($this->_headerEnrichment) {

            
            $commonUser = $this->_helper->IpHeader->checkUser($this->_chapId);
            
 
            
            //Temperorily assigning the default user id for $this->_userId
            $this->_userId = ($commonUser['user_id']) ? $commonUser['user_id'] : $this->_userId;
            $this->_mobileNo = $commonUser['mobile_no'];
            $this->_tempPassword = $commonUser['password'];
            $this->_isHeaderEnrichmentActive = $commonUser['header_enrichment_active'];
            
            //This is only for AirtelSL. Coz they don't want a login even if a premium download
            // , , , 
            if(($this->_chapId == 283006 || $this->_chapId == 163302 || $this->_chapId == 25022 || $this->_chapId == 81449 || $this->_chapId == 110721 || $this->_chapId == 114306 || $this->_chapId == 81449 || $this->_chapId == 274515 || $this->_chapId == 163302 || $this->_chapId == 280316 || $this->_chapId == 326728 || $this->_chapId == 320343) && $this->_isHeaderEnrichmentActive){
                $this->_helper->IpHeader->loginUserByHeaderEnrichment($this->_chapId, $this->_mobileNo);
            }
        }

        /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            echo 123; print_r($commonUser); die();
        }*/
        
        $this->view->isHeaderEnrichmentActive = ($this->_isHeaderEnrichmentActive) ? true : false ; 

        //Set the user can downloadd free apps if the user is common user. User id will be hard coded
        /*$this->_userCanDownloadFree = FALSE;
        if($this->_userId == 101543){
            $this->_userCanDownloadFree = TRUE;
        }*/
        
        // echo $deviceOs;

        //Low end Devices array
        $lowEndDevicesOs = array('Android', 'iPhone OS', 'iOS', 'Windows Mobile OS', 'RIM OS', 'BlackBerry');
        $lowEndDeviceBrand = array('Nokia');
        
        //check the device and change the layout if the device old-end
        if(in_array($brandName,$lowEndDeviceBrand) || !in_array($deviceOs,$lowEndDevicesOs))
        {
            $this->_isLowEndDevice = TRUE; // set true to low end devices
            $this->_helper->layout->setLayout('partnermobile/home_low_end');
        }
        else
        {
            $this->_isLowEndDevice = FALSE; // set false to modern devices
            $this->_helper->layout->setLayout('partnermobile/home');            
        }
    

        $metaDetails = $themeMetaModel->getThemeMetaForPartner($this->_chapId);
        
        $themeDetails = unserialize($metaDetails['WHITELABEL_THEME']);
        
        $this->view->css = $themeDetails['basic']['css'];
        
        //Get the low end mobile web url
        if(isset($themeDetails['basic']['lowendcss'])){
            $this->view->lowendcss = $themeDetails['basic']['lowendcss'];
        }
        
        //get multi languages for chap
        $languageUsersModel = new Model_LanguageUsers();
        $multiLanguages = $languageUsersModel->getMultiLanguagesByChap($this->_chapId);
        $this->view->multiLanguages = $multiLanguages;
        
        $this->view->folderPath = $metaDetails['WHITELABLE_THEME_NAME'];

        $this->view->partnerId = $this->_chapId;


		
	$this->view->rtl = ($this->_chapId == 23045) ? TRUE : FALSE;
		 
        $this->view->siteTitle = ($themeMetaModel->WHITELABLE_SITE_TITLE) ? $themeMetaModel->WHITELABLE_SITE_TITLE : '';
        $this->view->metaKeys = ($themeMetaModel->WHITELABLE_SITE_META_KEYS) ? $themeMetaModel->WHITELABLE_SITE_META_KEYS : '';
        $this->view->metaDes = ($themeMetaModel->WHITELABLE_SITE_META_DES) ? $themeMetaModel->WHITELABLE_SITE_META_DES : '';
        $this->view->googleAnalytic = ($themeMetaModel->WHITELABLE_SITE_GOOGLE_ANALYTIC) ? $themeMetaModel->WHITELABLE_SITE_GOOGLE_ANALYTIC : '';
        $this->view->siteLogo = ($themeMetaModel->WHITELABLE_SITE_LOGO) ? $themeMetaModel->WHITELABLE_SITE_LOGO : '';
        $this->view->siteFavicon = ($themeMetaModel->WHITELABLE_SITE_FAVICON) ? $themeMetaModel->WHITELABLE_SITE_FAVICON : '';

        //assigning CHAP details for views
        $this->view->headerEnrichment = (($themeMetaModel->HEADER_ENRICHMENT == 'yes') ? TRUE : FALSE);
        $this->view->showRegisterLink = (($themeMetaModel->PARTNER_SITE_REGISTER_LINK == 'yes') ? TRUE : FALSE);
        $this->view->showForgotPasswordLink = (($themeMetaModel->PARTNER_SITE_FORGOT_PASSWORD_LINK == 'yes') ? TRUE : FALSE);
        $this->view->showLoginLink = (($themeMetaModel->PARTNER_SITE_LOGIN_LINK == 'yes') ? TRUE : FALSE);
        
        $server = $_SERVER['SERVER_NAME'];
        $this->_baseUrl = 'http://' . $server;        
        
	    $this->view->mtnIran = ($this->_chapId == 23045  or $this->_chapId == 726355) ? TRUE : FALSE; //Check if the site is MTN Iran
        
        $this->view->airtelSriLanka = ($this->_chapId == 25022) ? TRUE : FALSE; //Check if the site is Airtel Sri Lanka
        
        $this->view->ycoins = ($this->_chapId == 115189) ? TRUE : FALSE; //Check if the site is YCoins
        
        $this->view->airtelNigeria = ($this->_chapId == 81449) ? TRUE : FALSE; //Check if the site is Airtel Nigeria
        
        $this->view->qelasy = ($this->_chapId == 81604) ? TRUE : FALSE; //Check if the site is Qelasy

        //$this->view->caboapps = ($this->_chapId == 136079) ? TRUE : FALSE; //Check if the site is caboapps
        $this->view->caboapps = ($this->_chapId == 107760) ? TRUE : FALSE; //Check if the site is caboapps

        //If user can download free apps with out logging
        $this->view->allowFreeDownload = ($this->_isHeaderEnrichmentActive || $this->_headerEnrichment) ? TRUE : FALSE;

        //if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){ echo $this->_isHeaderEnrichmentActive; } 

        $this->view->showDownloadCount = FALSE;
        $showDownloadsChapIds = array(25022, 276531, 320345);

        if (in_array($this->_chapId, $showDownloadsChapIds)) {
            $this->view->showDownloadCount = TRUE;
        }

        //Check if the user has temp password
        $this->view->tempPassword = ($this->_tempPassword == md5('mtnpassword') || $this->_tempPassword == md5('airtelpassword')) ? TRUE : FALSE;
        
        //Assign chapIds for sms verification when buy apps
        $arrSmsVerifyId = array(81449, 110721, 114306, 163302, 274515, 280316 );
        $this->_smsVerifyChap = false;
        if(in_array($this->_chapId, $arrSmsVerifyId) && $this->_isHeaderEnrichmentActive){
            $this->view->smsVerification = true;
            $this->_smsVerifyChap = true;
        }

 
        //Alert a popup for AritelSL appstore for the first time
        if(($this->_chapId == 25022 or $this->_chapId == 163302 or $this->_chapId == 280316 or $this->_chapId == 320342
              or $this->_chapId ==  320343 or $this->_chapId == 280316 or $this->_chapId == 282227 or $this->_chapId == 320344  or $this->_chapId == 320345
              or $this->_chapId == 324405 or $this->_chapId == 326728 or $this->_chapId == 136079 or $this->_chapId == 163302  or $this->_chapId  == 274515 
              or $this->_chapId == 276531 or $this->_chapId == 81449  or $this->_chapId == 100791 or $this->_chapId == 110721 or $this->_chapId == 114306 or $this->_chapId == 397395) 
                && ($deviceOs == 'Android')){
            
            $sessionAppstoreApp = new Zend_Session_Namespace('appstoreapp');
            if(!$sessionAppstoreApp->id){
                $themeMeta  = new Model_ThemeMeta();
                $themeMeta->setEntityId($this->_chapId);
                $chapInfo  = $themeMeta->getAll();

                //Get the Appstore app details
                $nexApi = new Nexva_Api_NexApi();
                
                switch($deviceOs){
                    case 'Android':
                        $appstoreAppId = $chapInfo->APPSTORE_APP_ID;
                        break;
                }
                
                $appStoreAppDetails = $nexApi->detailsAppAction($appstoreAppId);
        
                $this->view->appStoreAppDetailsChap = $appStoreAppDetails;
                $this->view->showAppstoreApp = TRUE;
                $sessionAppstoreApp->id = $appstoreAppId; 

                    
            }
            else{
                
              
                $this->view->showAppstoreApp = FALSE;
            }
        }

        //functionality to determine unique visits
        /*$visits = new Zend_Session_Namespace("visits");

        $ip = $_SERVER['REMOTE_ADDR'];
        $sessionId = Zend_Session::getId();
        $now = date("Y-m-d H:i:s");
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $uniqueVisitsModel = new Model_UniqueVisits();
        $isUnique = $uniqueVisitsModel->isUnique($ip, $sessionId);      //check whether this is a Unique visit

        $isExpired = FALSE;

        if($visits->visitTime){     //if already visited, we calculate visited time duration

            //$visitDetails = $uniqueVisitsModel->lastUpdatedTime($ip, $sessionId, $visits->visitTime);

            $visitTime = new DateTime($visits->visitTime);
            $updatedTime = new DateTime($now);
            $interval = $visitTime->diff($updatedTime);

            if($interval->h){       //if visited time duration is more than one hour, we insert a new record
                $isExpired = TRUE;
            }

        }

        if($isUnique || $isExpired){        //if this is a unique visit or current visit expired, we insert a new record
        //if($isUnique){        //if this is a unique visit or current visit expired, we insert a new record

            $visits->visitTime = $now;

            $data = array(
                'ip' => $ip,
                'visit_time' => $now,
                'updated_time' => $now,
                'chap_id' => $this->_chapId,
                'session_id' => $sessionId,
                'source' => 'PARTNERMOBILE',
                'user_agent'=> $userAgent
            );

            $uniqueVisitsModel->insert($data);

        }*/

        // removed this part, we don't need to run a SQL query for every single click

        /*else {    //if this is not a unique visit we update the current record

            $data = array(
                'updated_time' => $now
            );

            $uniqueVisitsModel->update($data,array('session_id = ?' => $sessionId, 'ip = ?' => $ip, 'visit_time =?' => $visits->visitTime));
        }*/

    }

    /**
     * 
     * Returns the deviced id from the DB based on the User Agent.
     * @param $userAgent User Agent
     * returns $deviceId
     */
    protected function detectUserDevice()
    {
        //get the language id for chapter site
        $userModel = new Model_User();
        
        //get the chapter site defaultlanguage
        $this->_chapLanguageId = $userModel->getUserLanguage($this->_chapId);
        
        //Set chap default language to emglish if $_chapLanguageId is empty
        $this->_chapLanguageId = (empty($this->_chapLanguageId)) ? 1 : $this->_chapLanguageId;
        
        //If chap is multilingual
        $sessionLanguage = new Zend_Session_Namespace('languagesession');
        if($sessionLanguage->id){
            $this->_chapLanguageId = $sessionLanguage->id;
        }
        else{
            if($this->_chapId == 81604 || $this->_chapId == 274515 || $this->_chapId == 110721 ||  $this->_chapId == 276531 || $this->_chapId == 280316
                     || $this->_chapId == 320345 || $this->_chapId == 324405 || $this->_chapId == 326728 || $this->_chapId == 320345 
                    || $this->_chapId == 585480 || $this->_chapId == 585474 || $this->_chapId == 282227 ){ //This is for temporary for qelasy
                $this->_chapLanguageId = 2;
            }
            if($this->_chapId == 115189){ //For YCoins
                
                $sessionCounty = new Zend_Session_Namespace('county');
                $this->_chapLanguageId = ($sessionCounty->code == 'JP') ? 24 : 1; 
                //$this->_chapLanguageId = ($sessionCounty->code == 'JP') ? 24 : 1;  

            }
            
            if($this->_chapId == 136079){ //For YCoins
            	$this->_chapLanguageId = 4;
            }
            
            
            if($this->_chapId == 283006){ //For YCoins
            	$this->_chapLanguageId = 22;
            }

            
            if($this->_chapId == 726355){ //For YCoins
            	$this->_chapLanguageId = 57;
            }
            
            
        }
        
        //$nexApi = new Nexva_Api_NexApi();
        $nexApi = new Nexva_Api_QelasyApi();
        $categories = $nexApi->categoryAction($this->_chapId,$this->_chapLanguageId,$this->_grade);
        
        $this->view->categories = $categories;
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'];        
        
        if($userAgent) 
        {            
            /*
            * I have uncommented this to add new detection engine this is old code.. so In case any error comment the new detection code and uncomment the old one. 
            * start
            * 
            */
            
            //$deviceSession = new Zend_Session_Namespace('Device');    
            
            //Iniate device detection using Device detection adapter
            //$deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();
           
            //Detect the device
            //$exactMatch = $deviceDetector->detectDeviceByUserAgent($userAgent);

            //Device barand name
            //$brandName = $deviceDetector->getDeviceAttribute('brand_name', 'product_info');
            //Zend_Debug::dump($deviceDetector->getDeviceAttribute('product_info'));die();
            //Zend_Debug::dump($brandName);die();
            
            //Get the Device ID of nexva db
            //$deviceId = $deviceDetector->getNexvaDeviceId();
            
            
            
           /*
            * I have uncommented this to add new detection engine this is old code.. so In case any error comment the new detection code and uncomment the old one.
            * end
            *
            */
            
            
            
            
            /*
             * Detection Script Start we need to find a better way to implemnt this as i am doing this in hurry bcos shaun need to put this live up
            *
            *
            */
            
            /******* comment start **************************************************************
            
            
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
            	$isWireless = $deviceInfo->is_mobile_device;
            
            
            	// get properties from the Wurfl
            	$brandName = $deviceInfo->brand;
            	$modelName = $deviceInfo->model;
            	$marketing_name = $deviceInfo->marketing_name;
            	$inputMethod = $deviceInfo->pointing_method;
            	$osVersion = $deviceInfo->device_os_version;
            	$exactMatch = $deviceInfo;
            	//get nexva device Id
            	$deviceId = $deviceInfo->id;
            	$isWireless = $deviceInfo->is_mobile_device;
            	
            	
            	
            	
            
            }
            

            
            */
            
            $session = new Zend_Session_Namespace("devices_partner_web");
            $deviceId = $session->deviceId;
           
            
            if(!isset($deviceId) and  $session->is_check == false) {
                
                $deviceDetection =  Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
                $deviceInfo = $deviceDetection->getNexvaDeviceId($_SERVER['HTTP_USER_AGENT']);
                //If this is not a wireless device redirect to the main site
                $deviceId = $deviceInfo->id;
                $session->deviceId = $deviceId;
                $session->is_check = true;
                $session->platfrom = $deviceInfo->platform;
                
                
            } 
            

            $this->_deviceId = $deviceId;
              
        }
        else
        {
            $this->_deviceId = null;
        }
       
    }
      
   /**
     * 
     * Returns the deviced name, brand and OS from the DB based on the User Agent.
     * @param $userAgent User Agent
     * returns $device_details Array
     */
    protected function detectUserDeviceDetails()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];        
        
        if($userAgent) 
        {                        

            
            /*
             * I have uncommented this to add new detection engine this is old code.. so In case any error comment the new detection code and uncomment the old one.
            * start
            *
            */
            
            //Iniate device detection using Device detection adapter
            //$deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();
             
            //Detect the device
           //$exactMatch = $deviceDetector->detectDeviceByUserAgent($userAgent);
            
            //Device barand name
            //$this->_deviceDetails = $deviceDetector->getDeviceAttribute('product_info');
            
            
            /*
             * I have uncommented this to add new detection engine this is old code.. so In case any error comment the new detection code and uncomment the old one.
            * end
            *
            */
            
            
            
            
            /*
             * Detection Script Start we need to find a better way to implemnt this as i am doing this in hurry bcos shaun need to put this live up
            *
            *
            */
            
            $session = new Zend_Session_Namespace("devices_partner_web");
            $deviceId = $session->deviceId;

            
            
            if(!isset($deviceId) and  $session->is_check == false) {
                
            	$deviceDetection =  Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
            	$deviceInfo = $deviceDetection->getNexvaDeviceId($_SERVER['HTTP_USER_AGENT']);
            	//If this is not a wireless device redirect to the main site
            
            
            	// get properties from the Wurfl
            	$brandName = $deviceInfo->brand;
            	$modelName = $deviceInfo->model;
            	$marketing_name = $deviceInfo->marketing_name;
            	$inputMethod = $deviceInfo->pointing_method;
            	$osVersion = $deviceInfo->device_os_version;
            	$exactMatch = $deviceInfo;
            	$deviceOs = $deviceInfo->platform;
            	//get nexva device Id
            	$deviceId = $deviceInfo->id;
            	$isWireless = $deviceInfo->is_mobile_device;
            	
            	$session->deviceId = $deviceId;
            	$session->is_mobile_device = $isWireless;
            	$session->device_os_version =
            	$session->platform  = $deviceOs;
            	$session->device_os_version  = $osVersion;
            	$session->pointing_method = $inputMethod;
            	$session->marketing_name = $marketing_name;
            	$session->model = $modelName;
            	$session->brand = $brandName;
            	$session->is_check = true;
            	
            } else {
                // get properties from the Wurfl

                $deviceOs = $session->platform;
                $deviceId = $session->deviceId;
                $isWireless = $session->is_mobile_device;
                $deviceOs =  $session->platform;
                $osVersion = $session->device_os_version;
                $inputMethod =  $session->pointing_method;
                $marketing_name =   $session->marketing_name;
                $brandName = $session->brand;
                $modelName = $session->model;
            }
          
            	

            	
            
            $this->_deviceDetails['brand_name'] = $brandName;
            $this->_deviceDetails['device_os_version'] = $osVersion;
            $this->_deviceDetails['device_os'] = $deviceOs;//($deviceOs == 'BlackBerry' ? 'RIM OS' : $deviceOs);
            $this->_deviceDetails['model_name'] = $modelName;
            $this->_deviceDetails['brand_name'] = $brandName;
            $this->_deviceDetails['pointing_method'] = $inputMethod;
            $this->_deviceDetails['is_wireless_device'] = $isWireless;
            
 
            
            //   $this->_deviceDetails =
            
            
            /*
             * Detection Script End
            *
            *
            */
            

           /*
           if( $deviceOs == 'BlackBerry' && $osVersion == '10.0'){
               
 
               $skip_action_names = array('index');
               
               if (in_array( $this->getRequest ()->getActionName (), $skip_action_names ))
               {
                $this->_redirect('http://'. $_SERVER ['HTTP_HOST'].'/index/device-not-found');   
               }
               
             

           
           }
            
           
           */
            
           
            
            
            
            
            

        }
        else
        {
            $this->_deviceDetails = null;
        }
        
    }
    
}