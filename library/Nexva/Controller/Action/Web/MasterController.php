<?php

class Nexva_Controller_Action_Web_MasterController extends Zend_Controller_Action {

    private $__profilerEnabled = false;
    private $__productImagesUrl = false;
    private $__siteAssetsUrl = false;

    public function init() {
        if (APPLICATION_ENV == 'development') {
            $this->__profilerEnabled = false;
        }


        
        if ($this->__profilerEnabled) { //don't want this activating for all - JP
            $db         = Zend_Registry::get('db');
            $profiler   = new Nexva_Db_Profiler_Generic();
            $profiler->setEnabled(true);
            $db->setProfiler($profiler);
            Zend_Registry::set('db', $db);
        }
		
		$this->__productImagesUrl = $config = Zend_Registry::get ( "config" )->nexva->application->product_images_domain;
		$this->__siteAssetsUrl = $config = Zend_Registry::get ( "config" )->nexva->application->site_assets_domain;
		
		$this->view->productImagesUrl = 'http://'.$this->__productImagesUrl;
		$this->view->siteAssetsUrl = 'http://'.$this->__siteAssetsUrl;

        $this->checkWhiteLabel();
    }

    public function preDispatch() {

        
        $this->view->selectedDevices = $this->getSelectedDevices();

        $this->view->categories     = $this->getCategories();
        $this->view->flashMessages  = $this->_helper->FlashMessenger->getMessages();

        // check for mobile devices
        /**
        * See if the user has selected a device
        * and if not pop up the device selection screen.
        */
        $this->view->showDevicePage = false;
        $session    = new Zend_Session_Namespace("devices");
        $devices    = $session->selectedDevices;
        $reminded   = $session->deviceScreenShown;


        
        
        if (is_array($devices)) {
            $reminded   = true;
        }
        $exlcudeReminder = array('indexmobile');
        $controllerActionPair   = $this->_getParam('controller', '') . $this->_getParam('action', ''); 
        if ($this->_getParam('error_handler', false) || in_array($controllerActionPair, $exlcudeReminder)) {
            $reminded   = true;
        }

        if (!$reminded && (!$devices || empty($devices))) {
            //show popup
            $session->deviceScreenShown = true;
            $this->view->showDevicePage = true;
        }
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            
            
            
          
            
            $session = new Zend_Session_Namespace("devices_partner_mobile");
            $isWireless = $session->is_mobile_device;
            
            if(!$isWireless and $session->is_check == false) {
            	$deviceDetection =  Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
            	$deviceInfo = $deviceDetection->getNexvaDeviceId($_SERVER['HTTP_USER_AGENT']);
            	//If this is not a wireless device redirect to the main site
            	$isWireless = $deviceInfo->is_mobile_device;
            	$session->is_mobile_device = $isWireless;
            	$session->is_check = true;
            }
             

            if ($isWireless) {
                $config = Zend_Registry::get("config");
                // check for app contoller and index action with app id redirect to the relevant mobile location
                if ($this->_request->getControllerName() == 'app' && $this->_request->getActionName() == 'index' && $this->_request->id) {
                    $this->_redirect('http://' . $config->nexva->application->mobile->url . '/app/show/id/' . $this->_request->id);
                } else {
                    $this->_redirect("http://" . $config->nexva->application->mobile->url. $_SERVER['REQUEST_URI']);
                }
            }
        }
        $this->__setVersion();
    }


    public function postDispatch() {
        //get the query profiler data
        $this->__dumpLog();

                //message handling
        $this->view->flashMessages  = $this->_helper->FlashMessenger->getMessages();
        $this->_helper->FlashMessenger->setNamespace('ERROR');
        $this->view->flashErrors    = $this->_helper->FlashMessenger->getMessages();
        $this->_helper->FlashMessenger->setNamespace('NOTICE');
        $this->view->flashNotices    = $this->_helper->FlashMessenger->getMessages();
    }

    /**
     * Retrieves the phones the user has selected
     *
     * @return array
     */
    protected function getSelectedDevices() {

        $session = new Zend_Session_Namespace("devices");

        return $session->selectedDevices;
    }


    protected function getCategories($id=null) {

        $cache  = Zend_Registry::get('cache');
        $key    = 'SITE_CATEGORIES';
        if (($rows = $cache->get($key)) === false) {
            $categoryModel  = new Model_Category();
            $rows = $categoryModel->getCategorylist();
            $cache->set($rows, $key);
        }
        return $rows;

    }
    
    

    protected function getSelectedDeviceIds(){

        $session = new Zend_Session_Namespace("devices");
        if (count($session->selectedDevices)== 0 )
        return null;
        else
        return array_keys($session->selectedDevices);

    }

    /**
     * Checks whether this is a whitelabel request using .com instead of .mobi.
     */
    protected function checkWhiteLabel() {
        //airtel.nexva-v2-dev.com
         
        $config = Zend_Registry::get("config");
        $server = $_SERVER['SERVER_NAME'];
        $base   = $config->nexva->application->base->url;
        if ($server == trim($base)) {
            return; //it's our own url, just continue
        }
        
        $themeMeta  = new Model_ThemeMeta();
        $themeData  = null;
        $row        = $themeMeta->fetchRow('meta_name = "WHITELABLE_URL" AND meta_value = "' .  $server . '"');
        if (!is_null($row) || !empty($row)) {
            $userId     = $row['user_id'];
            $themeMeta->setEntityId($userId);
            $themeData  = $themeMeta->getAll();
            $themeData->USER_ID = $userId;
        } else {
            $server     = str_ireplace('www.', '', $server);
            $whiteLabel = substr($server, 0, strpos($server, '.'));
            if ($whiteLabel == 'nexva') {
                return;//don't want www.nexva.com to get the warning
            }
            $row        = $themeMeta->fetchRow('meta_name = "WHITELABLE_THEME_NAME" AND meta_value = "' .  $whiteLabel . '"');
            if (!is_null($row) || !empty($row)) {
                $userId     = $row['user_id'];
                $themeMeta->setEntityId($userId);
                $themeData  = $themeMeta->getAll();
                $themeData->USER_ID = $userId;
            }    
        }
        
        if (empty($themeData) || empty($themeData->WHITELABLE_URL)) {
            return; //not enough data for us to redirect anywhere. serve as usual
        }
        $url    = 'http://' . $base . '/index/mobile/u/' . $themeData->USER_ID ;
        $this->_redirect($url);
    }

    private function __setVersion() {
                //get the cache object and see what the site version is
        $cache  = Zend_Registry::get('cache');
        if (($version = $cache->get('SITE_VERSION')) === false) {
            //no version, load it from file
            if (file_exists(APPLICATION_PATH . '/.version')) {
                $version    = trim(file_get_contents(APPLICATION_PATH . '/.version'));
            } else {
                $version    = '2.0';
            }
            $cache->set($version, 'SITE_VERSION');
        }

        $this->view->siteVersion    = $version;
        $this->view->VER            = '?' . $version; // for brevity
    }
    
    private function __log($data) {
        return; 
        if (is_array($data)) {
            $data   = print_r($data, true);
        }
        $config = Zend_Registry::get("config");
        $filename   = $config->nexva->application->logDirectory . '/data-logs.txt'; 
        @file_put_contents($filename, $data, FILE_APPEND);
        @mail('chathura@nexva.com', 'log .' . time(), $data);
    }
    
    /**
     * update the lastRequested URL on every HTTP request.
     */
    
    public function setLastRequestedUrl()   {      
        $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
        $sessionlastRequest = new Zend_Session_Namespace('lastRequest');
        $sessionlastRequest->lastRequestUri = $requestUri;
        //echo $sessionlastRequest->lastRequestUri;
        //die();
    }
    
    
    protected function __addMessage($message = '') {
        $this->_helper->FlashMessenger->resetNamespace();
        $this->_helper->FlashMessenger->addMessage($message);
    }
    
    protected function __addNoticeMessage($message = '') {
        $this->_helper->FlashMessenger->setNamespace('NOTICE');
        $this->_helper->FlashMessenger->addMessage($message);
        $this->_helper->FlashMessenger->resetNamespace();
    }

    protected function __addErrorMessage($message = '') {
        $this->_helper->FlashMessenger->setNamespace('ERROR');
        $this->_helper->FlashMessenger->addMessage($message);
        $this->_helper->FlashMessenger->resetNamespace();
        
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
?>
