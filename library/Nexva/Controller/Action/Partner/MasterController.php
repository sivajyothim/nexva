<?php

class Nexva_Controller_Action_Partner_MasterController extends Zend_Controller_Action {

    protected $_productsPerPage;
    protected $_baseUrl;
    protected $_chapId;
    protected $_featuredApps;
    protected $_bannerCount;
    protected $_grade;
    protected $_userType;
    public $_chapLanguageId;
    public $_chapPlatform;
    public $_userId;

    public function preDispatch() {
               


        $session = new Zend_Session_Namespace("devices");
        $this->view->selectedDevice = $session->selectedDevices;
        


    }

    //Return Device Id, if choosen
    protected function getDeviceId() {
        $session = new Zend_Session_Namespace("devices");
        $devieArray = $session->selectedDevices;

        $deviceId = null;

        if ($devieArray !== null && !empty($devieArray)) {
            $deviceId = $devieArray['id'];
        }

        return $deviceId;
    }

    public function init() {
        
  
        
    
        $sessionChapDetails = new Zend_Session_Namespace('partner');
        $this->_productsPerPage = 10;
        $this->_chapId = $sessionChapDetails->id;
        $this->view->partnerId = $this->_chapId;
        
        

        
         if($_SERVER['REMOTE_ADDR'] == '113.59.222.81'){
        	$this->__profilerEnabled = true;
        
        }
        
        
        
        if ($this->__profilerEnabled) { //don't want this activating for all - JP
        	$db         = Zend_Registry::get('db');
        	$profiler   = new Nexva_Db_Profiler_Generic();
        	$profiler->setEnabled(true);
        	$db->setProfiler($profiler);
        	Zend_Registry::set('db', $db);
        }

        
        //Getting grade from session for qelasy
        if($this->_chapId == 81604){
            $studentGrade = new Zend_Session_Namespace('studentGrade');
            if(isset($studentGrade->gradeId)){
                $this->_grade = $studentGrade->gradeId;
            }
        }

        //$this->_grade = 4; //This is for testing purposes, has to be dynamic and supposed to be provided through qelasy api
        //$this->_userType = 2;

        //get the chapter site defaultlanguage
        $userModel = new Model_User();
        $this->_chapLanguageId = $userModel->getUserLanguage($this->_chapId);

        
        
        //Set chap default language to english if $_chapLanguageId is empty
        $this->_chapLanguageId = (empty($this->_chapLanguageId)) ? 1 : $this->_chapLanguageId;
        
        //If chap is multilingual
        $sessionLanguage = new Zend_Session_Namespace('languagesession');
        if($sessionLanguage->id){
            $this->_chapLanguageId = $sessionLanguage->id;
        }
        else{
            if($this->_chapId == 81604 || $this->_chapId == 274515 || $this->_chapId == 110721 || $this->_chapId == 280316 || $this->_chapId == 324405 || 
                    $this->_chapId == 326728 || $this->_chapId == 320343 || $this->_chapId == 585480 || $this->_chapId == 585474 || $this->_chapId == 282227  ){ //This is for temporary for default language french chaps
                $this->_chapLanguageId = 2;
            }
            if($this->_chapId == 276531 || $this->_chapId == 320345 ){ //This is for temporary for default language french chaps
            	$this->_chapLanguageId = 60;
            }
            if($this->_chapId == 115189){ //For YCoins
                $sessionCounty = new Zend_Session_Namespace('county');
                $this->_chapLanguageId = ($sessionCounty->code == 'JP') ? 24 : 1;  
            }
            if($this->_chapId == 136079){ //For YCoins
            	$this->_chapLanguageId = 4;
            }
            
            if($this->_chapId == 283006){ //For YCoins
            	$this->_chapLanguageId = 22;
            }
            
            
            if($this->_chapId == 726355){ 
            	$this->_chapLanguageId = 57;
            }
            
            
        }

        /*if($this->_chapId == 115189){
            print_r($sessionCounty->code);
            echo $sessionCounty->code['code'];
        }*/
        /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99' && $this->_chapId == 115189){
            
            $sessionCounty = new Zend_Session_Namespace('county');
              echo $sessionCounty->code; die();
        }*/
        
        //Current language
        $this->view->currentLanguageId = $this->_chapLanguageId;
        
        $themeMetaModel = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($this->_chapId);
        
        //Set chapter platform
        $this->_chapPlatform = $themeMetaModel->WHITELABLE_PLATEFORM;
        $this->view->appStoreAppid = $themeMetaModel->APPSTORE_APP_ID;

        $auth = Zend_Auth::getInstance();
        if (isset($auth->getIdentity()->id)) {
            $this->view->loggedUser = $auth->getIdentity()->id;
            //Set logged in user id
            $this->_userId = $auth->getIdentity()->id;
        }
        else {
            $this->view->loggedUser = 'none';
            $this->_userId = NULL;
        }
        

        $metaDetails = $themeMetaModel->getThemeMetaForPartner($this->_chapId);

        $themeDetails = json_decode($metaDetails['WHITELABLE_SETTINGS']);

        $config = Zend_Registry::get("config");
        $server = $_SERVER['SERVER_NAME'];

        $this->view->headLink()->appendStylesheet('http://' . '178.63.83.24' . '/partner/default/assets/css/client_css.css');

        $this->view->headLink()->appendStylesheet('http://' . '178.63.83.24' . '/mobile/whitelables/' . $metaDetails['WHITELABLE_THEME_NAME'] . '/web/css/' . $themeDetails->custom_css);

        $this->view->headLink()->appendStylesheet('http://' . '178.63.83.24' . '/partner/default/assets/css/slicss/screen.css');
        $this->view->headLink()->appendStylesheet('http://' . '178.63.83.24' . '/partner/default/assets/css/slicss/quick.css');
        $this->view->headLink()->appendStylesheet('http://' . '178.63.83.24' . '/web/nexlinker/shadowbox/shadowbox.css');
        $this->view->headLink()->appendStylesheet('http://' . '178.63.83.24' . '/partner/default/assets/css/lightbox/jquery.lightbox-0.5.css');
        $this->view->headLink()->appendStylesheet('http://' . '178.63.83.24' . '/partner/default/assets/css/jquery.fancybox-1.3.4.css');

        $this->view->headLink()->appendStylesheet('http://' . '178.63.83.24' . '/partner/default/assets/css/jquery.ui.all.css');
        $this->view->headLink()->appendStylesheet('http://' . '178.63.83.24' . '/partner/default/assets/css/popbox.css');
        $this->view->headLink()->appendStylesheet('http://' . '178.63.83.24' . '/partner/default/assets/css/popup-login-style.css');
        
        if($this->_chapId == 283006){
        	$this->view->headLink()->appendStylesheet('http://' . '178.63.83.24' . '/partner/default/assets/css/colorbox.css');
        }
        
        $this->view->headScript()->appendFile('http://' . '178.63.83.24' . '/partner/default/assets/js/jquery-1.7.2.js');
        $this->view->headScript()->appendFile('http://' . '178.63.83.24' . '/partner/default/assets/js/jquery.lightbox-0.5.js');
        
        $this->view->headScript()->appendFile('http://' . '178.63.83.24' . '/partner/default/assets/js/jquery-1.7.1.js');
        $this->view->headScript()->appendFile('http://' . '178.63.83.24' . '/partner/default/assets/js/popbox.js');
        $this->view->headScript()->appendFile('http://' . '178.63.83.24' .'/web/nexlinker/shadowbox/shadowbox.js');
        //$this->view->headScript()->appendFile('http://' . '178.63.83.24' .'partner/default/assets/js/shadowbox.js');

        $this->view->headScript()->appendFile('http://' . '178.63.83.24' . '/partner/default/assets/js/jquery.ui.core.js');
        $this->view->headScript()->appendFile('http://' . '178.63.83.24' . '/partner/default/assets/js/jquery.ui.widget.js');
        $this->view->headScript()->appendFile('http://' . '178.63.83.24' . '/partner/default/assets/js/jquery.ui.position.js');
        $this->view->headScript()->appendFile('http://' . '178.63.83.24' . '/partner/default/assets/js/jquery.ui.autocomplete.js');
        $this->view->headScript()->appendFile('http://' . '178.63.83.24' . '/partner/default/assets/js/jquery.fancybox-1.3.4.js');

        if($this->_chapId == 283006){
        	$this->view->headScript()->appendFile('http://' . '178.63.83.24' . '/partner/default/assets/js/jquery.colorbox.js');
        }

        //toddish popup files
        /*$this->view->headLink()->appendStylesheet('http://' . $_SERVER['SERVER_NAME'] . '/partner/default/assets/css/popup.css');
        $this->view->headScript()->appendFile('http://' . $_SERVER['SERVER_NAME'] . '/partner/default/assets/js/jquery.popup.js');
        $this->view->headScript()->appendFile('http://' . $_SERVER['SERVER_NAME'] . '/partner/default/assets/js/toddish-popup.js');*/

        //ketchup files
        $this->view->headScript()->appendFile('http://' . '178.63.83.24' . '/common/js/jquery/jquery.validate.js');

        $this->_helper->layout->setLayout('partner/web_inner_page');

        $this->_baseUrl = 'http://' . $server;
        //$nexApi = new Nexva_Api_NexApi();
        $nexApi = new Nexva_Api_QelasyApi();

        //additional $this->_langId added for filter categories according to the chapter default language
        $categories = $nexApi->categoryAction($this->_chapId, $this->_chapLanguageId,$this->_grade);

        $this->view->categories = $categories;
        $this->view->baseUri = $this->_baseUrl;
        $this->view->controllerName =ucfirst( Zend_Controller_Front::getInstance()->getRequest()->getControllerName());
        //$this->view->pageName =  Zend_Controller_Front::getInstance()->getRequest()->getActionName();

        $nexvaBaseUrl = Zend_Registry::get("config")->nexva->application->base->url;

        $this->view->nexLinkerUrl = $server;
     
        //Generate Footer Menus
        $menuItemModel = new Partner_Model_WlMenuItem();
        $menuItems = $menuItemModel->getAllMenuItems($this->_chapId, $this->_chapLanguageId);
        //Zend_Debug::dump($menuItems);die();
        $this->view->footerMenuItems = $menuItems;
        

        $this->view->siteTitle = ($themeMetaModel->WHITELABLE_SITE_TITLE) ? $themeMetaModel->WHITELABLE_SITE_TITLE : '';
        $this->view->metaKeys = ($themeMetaModel->WHITELABLE_SITE_META_KEYS) ? $themeMetaModel->WHITELABLE_SITE_META_KEYS : '';
        $this->view->metaDes = ($themeMetaModel->WHITELABLE_SITE_META_DES) ? $themeMetaModel->WHITELABLE_SITE_META_DES : '';
        $this->view->googleAnalytic = ($themeMetaModel->WHITELABLE_SITE_GOOGLE_ANALYTIC) ? $themeMetaModel->WHITELABLE_SITE_GOOGLE_ANALYTIC : '';
        $this->view->siteLogo = ($themeMetaModel->WHITELABLE_SITE_LOGO) ? $themeMetaModel->WHITELABLE_SITE_LOGO : '';
        $this->view->siteFavicon = ($themeMetaModel->WHITELABLE_SITE_FAVICON) ? $themeMetaModel->WHITELABLE_SITE_FAVICON : '';
        
        $this->_featuredApps = ($themeMetaModel->WHITELABLE_SITE_FETURED_APPS) ? $themeMetaModel->WHITELABLE_SITE_FETURED_APPS : '';
        $this->_bannerCount = ($themeMetaModel->WHITELABLE_SITE_BANNER_COUNT) ? $themeMetaModel->WHITELABLE_SITE_BANNER_COUNT : '';

        $this->view->showSlider = ($themeMetaModel->PARTNER_SITE_SLIDER) ? $themeMetaModel->PARTNER_SITE_SLIDER : 'yes';
        $this->view->showFeaturedApps = ($themeMetaModel->PARTNER_SITE_FEATURED_APPS) ? $themeMetaModel->PARTNER_SITE_FEATURED_APPS : 'yes';
        $this->view->showTopFreeApps = ($themeMetaModel->PARTNER_SITE_TOP_FREE_APPS) ? $themeMetaModel->PARTNER_SITE_TOP_FREE_APPS : 'yes';
        $this->view->showTopPaidApps = ($themeMetaModel->PARTNER_SITE_TOP_PAID_APPS) ? $themeMetaModel->PARTNER_SITE_TOP_PAID_APPS : 'yes';
        $this->view->showTopDownloadedApps = ($themeMetaModel->PARTNER_SITE_TOP_DOWNLOADED_APPS) ? $themeMetaModel->PARTNER_SITE_TOP_DOWNLOADED_APPS : 'yes';
        $this->view->showRegisterLink = ($themeMetaModel->PARTNER_SITE_REGISTER_LINK) ? $themeMetaModel->PARTNER_SITE_REGISTER_LINK : 'yes';
        $this->view->showForgotPasswordLink = ($themeMetaModel->PARTNER_SITE_FORGOT_PASSWORD_LINK) ? $themeMetaModel->PARTNER_SITE_FORGOT_PASSWORD_LINK : 'yes';
        $this->view->showLoginLink = ($themeMetaModel->PARTNER_SITE_LOGIN_LINK) ? $themeMetaModel->PARTNER_SITE_LOGIN_LINK : 'yes';
        $this->view->showSiteTitle = ($themeMetaModel->PARTNER_SITE_TITLE) ? $themeMetaModel->PARTNER_SITE_TITLE : 'no';
        $this->view->copyRightText = ($themeMetaModel->WHITELABLE_SITE_COPYRIGHT_TEXT) ? $themeMetaModel->WHITELABLE_SITE_COPYRIGHT_TEXT : 'Products, Logos and Companies mentioned herein are trademarks or trade names of their respective owners and therefore copyrighted.';

        $this->view->headerEnrichment = $themeMetaModel->HEADER_ENRICHMENT;
        
       // ($this->_chapId == 23045 or $this->_chapId = 726355) ? TRUE : enable this 
        $this->view->mtnIran =  FALSE; //Check if the site is MTN Iran
        
        $this->view->airtelSriLanka = ($this->_chapId == 25022) ? TRUE : FALSE; //Check if the site is Airtel Sri Lanka
        
        $this->view->airtelNiger = ($this->_chapId == 274515) ? TRUE : FALSE; //Check if the site is Airtel Niger
        
        $this->view->ycoins = ($this->_chapId == 115189) ? TRUE : FALSE; //Check if the site is YCoins
        
        $this->view->qelasy = ($this->_chapId == 81604) ? TRUE : FALSE; //Check if the site is Qelasy
        
        $this->view->caboapps = ($this->_chapId == 136079) ? TRUE : FALSE; //Check if the site is Caboapps
        
        $this->view->showPlatformIcons = ($this->_chapId == 81604) ? FALSE : TRUE; //Set show platform icons false for qelasy

        $this->view->showDownloadCount = FALSE;
        $showDownloadsChapIds = array(25022,276531, 320345);

        if (in_array($this->_chapId, $showDownloadsChapIds)) {
            $this->view->showDownloadCount = TRUE;
        }

        //get multi languages for chap
        $languageUsersModel = new Model_LanguageUsers();
        $multiLanguages = $languageUsersModel->getMultiLanguagesByChap($this->_chapId);
        $this->view->multiLanguages = $multiLanguages;
        
        //Overlay loading for multi language switched
        if($this->_chapId == 81604 || $this->_chapId == 115189 || $this->_chapId == 136079 || $this->_chapId == 274515){
            $this->view->headLink()->appendStylesheet('http://' . '178.63.83.24' . '/partner/default/assets/css/overlay.css');
            $this->view->headScript()->appendFile('http://' . '178.63.83.24' . '/partner/default/assets/js/loading-overlay.js');

        }

        //functionality to determine unique visits
        /*$visits = new Zend_Session_Namespace("visits");

        $ip = $_SERVER['REMOTE_ADDR'];
        $sessionId = Zend_Session::getId();
        $now = date("Y-m-d H:i:s");

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

            $visits->visitTime = $now;

            $data = array(
                'ip' => $ip,
                'visit_time' => $now,
                'updated_time' => $now,
                'chap_id' => $this->_chapId,
                'session_id' => $sessionId,
                'source' => 'PARTNER'
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
    

    
    public function postDispatch() {
        

    	//get the query profiler data
    //	$this->__dumpLog();


    }
    
    
    private function __dumpLog() {

    	if ($this->__profilerEnabled) {//don't want this activating for all - JP

    		$db         = Zend_Registry::get('db');
    		$profiler   = $db->getProfiler();
    		$config     = Zend_Registry::get('config');
    		$total      = 0;
    		$params     = $this->_request->getParams();
    		$traces     = $profiler->getCallers();
    
    		$filename   = $config->nexva->application->logDirectory . '/queries-' . $params['controller'] . '-' . $params['action'] . '.log';
    		$fd         = fopen($filename, 'w');
    		fwrite($fd, "DURATION\t\t\t\tQUERY\n\n");
    
    		foreach ($profiler->getQueries() as $id=>$query) {
    			$duration   = $query->getElapsedSecs();
    			fwrite($fd, $duration . "\t\t");
    			fwrite($fd, isset($traces[$id]) ? $traces[$id] : 'BLANK' . "\t\t");
    			fwrite($fd, (str_ireplace(array("\n", "\r"), ' ', $query->getQuery())) . "\n");
    			$total      += $duration;
    		}
    		$totalQueries   = count($profiler->getQueries());
    		$footer         = "Total Queries : {$totalQueries}\nTotal Time Spent : {$total} seconds";
    		fwrite($fd, "\n\n\n" . $footer);
    		fclose($fd);
    
    	}
    	
    }

}

