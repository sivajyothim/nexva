<?php

/**
 * Mobile Web Master controller
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
define('MAJOR', 1); // Major Version
define('MINOR', 2); // Minor Version
define('REVISION', 3); // Revision

class Nexva_Controller_Action_Mobile_MasterController extends Zend_Controller_Action {

    private $_conf;
    protected $_categories;
    protected $themeMeta;
    private $__logEnabled = false;

    public function init() {
        


        // check the session and load depends on that session
        $this->__setVersion();
        $this->loadThemeFile();

        //check access
        if (!$this->isAuthenticatedClient()) {
            $logger = new Nexva_Util_Log_Mongo('mobile_site_deny_log');
            $data = array(
                'whitelabel' => print_r($this->themeMeta, true),
                'chap_id' => $this->themeMeta->USER_ID
            ); //just want usage so need to log specific data
            $logger->log($data);

            $this->_redirect('/page/static/id/deny');
        }

        $conf = $this->_conf;
        // set the title
        $this->view->title = isset($conf->basic->sitename) ? $conf->basic->sitename : 'neXva';
        // add the overiride CSS depends on the theme
        if (!empty($conf->basic->css)) {
            $verQs = '?' . $this->view->siteVersion; //siteVersion set in __setVersion();
            $this->view->headLink()->appendStylesheet($this->view->cdn('site_assets') . $conf->basic->css . $verQs);
        }
        // site Name
        $this->view->footerSitename = isset($conf->footer->sitename) ? $conf->footer->sitename : 'neXva Inc';
        // menu
        $this->view->menuLinks = isset($conf->content->menu) ? $conf->content->menu->toArray() : array();
        // footer links
        $this->view->legalLinks = isset($conf->footer->menu) ? $conf->footer->menu->toArray() : array('Legal' => '#', 'Privacy' => '#');

        // Logo Image
        $this->view->logo = isset($conf->header->logo) ? $conf->header->logo->toArray() : array('/mobile/images/nexva_logo.gif', 'neXva Logo');
        // Buttons
        $this->view->buyButton = isset($conf->buttons->buy) ? $conf->buttons->buy : 'nexva_buy.gif';
        $this->view->downloadButton = isset($conf->buttons->buy) ? $conf->buttons->download : 'nexva_download.gif';
        // show title of the page on contents
        $this->view->showPageTitle = true;
        // show utility options
        $this->view->showUtility = true;
        // enable search for all pages
        $this->view->enableSearch = true;


        $this->initLog();
    
        //Detect device
        $this->checkIsNotWireless();
        

    }

    private function checkIsNotWireless() {

        if ($_SERVER['HTTP_USER_AGENT'] != '') {

            $deviceSession = new Zend_Session_Namespace('Device');

/*       
 * 
 *          $deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();
            $exactMatch = $deviceDetector->detectDeviceByUserAgent();
            $isWireless = $deviceDetector->getDeviceAttribute('is_wireless_device', 'product_info'); 
 */
            

           /*
            * Detection Script Start
            *
            *
            */
            
            $session = new Zend_Session_Namespace("devices_nexva_mobile");
            $isWireless = $session->is_mobile_device;
            
            if(!$isWireless and $session->is_check == false) {
            	$deviceDetection =  Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
            	if('/np/443039' ==  $_SERVER['REQUEST_URI'])
            	$deviceInfo = $deviceDetection->getNexvaDeviceId('Mozilla/5.0 (Linux; U; Android 1.5; fr-fr; Galaxy Build/CUPCAKE) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2 -H');
            	else 
            	$deviceInfo = $deviceDetection->getNexvaDeviceId($_SERVER['HTTP_USER_AGENT']);
            	//If this is not a wireless device redirect to the main site
            	$isWireless = $deviceInfo->is_mobile_device;
            	$session->is_mobile_device = $isWireless;
            	$session->is_check = true;
            }
            
            
            /*
             * Detection Script End
            *
            *
            */

            if ($exactMatch || $deviceSession->deviceManuallySelected) {
                $this->view->DEVICE_EXACT_MATCH = true;
            } else {
                //sometimes the match isn't perfect so we let the user know that there is a manual option as well
                $this->view->DEVICE_EXACT_MATCH = false;
            }

            if ($this->__disableMobileCheck()) {
                return;
            }
            if (!$isWireless) {
                //see if it's a whitelable request first
                $config = Zend_Registry::get("config");
                $server = $_SERVER['SERVER_NAME'];
                $base = $config->nexva->application->base->url;


                if ($server == 'mtngroup.nexva.mobi')
                    $this->_redirect('http://nextapps.mtnonline.com/');

                if ($server == trim($base)) {
                    $this->_redirect("http://" . $config->nexva->application->base->url . $_SERVER['REQUEST_URI']);
                    //it's our own url, just continue
                }

                $themeMeta = new Model_ThemeMeta();
                $themeData = null;
                $row = $themeMeta->fetchRow('meta_name = "WHITELABLE_URL" AND meta_value = "' . mysql_escape_string($server) . '"');
                if (!is_null($row) || !empty($row)) {
                    $userId = $row['user_id'];
                    $themeMeta->setEntityId($userId);
                    $themeData = $themeMeta->getAll();
                    $themeData->USER_ID = $userId;
                }
                if ($themeData != null && strtolower($themeData->WHITELABLE_SITE_NAME) != 'nexva') {
                    $url = 'http://' . $base . '/index/mobile/u/' . $themeData->USER_ID;
                    $this->_redirect($url);
                }



                //point them to nexva.com
                if ($this->_request->getControllerName() == 'app' && $this->_request->getActionName() == 'index' && $this->_request->id) {
                    $this->_redirect('http://' . $config->nexva->application->base->url . '/' . $this->_request->id);
                } else {
                    $this->_redirect("http://" . $config->nexva->application->base->url . $_SERVER['REQUEST_URI']);
                }
                $this->_redirect($url, $options);
            }
        }
    }

    // predispatch to get device detection work
    public function preDispatch() {
        $config = Zend_Registry::get("config");
        $brandName = '';
        $modelName = '';
        $deviceId = '';
        // Check session is exists or not
        $deviceSession = new Zend_Session_Namespace('Device');
        //Zend_Debug::dump($_SESSION);die();
        // check for cookie
        if (!isset($deviceSession->deviceId) && isset($_COOKIE['device_id'])) 
        {
            $deviceSession->deviceId = isset($_COOKIE['device_id']) ? $_COOKIE['device_id'] : FALSE;
            $deviceSession->deviceName = isset($_COOKIE['device_name']) ? $_COOKIE['device_name'] : FALSE;
            $deviceSession->osVersion = isset($_COOKIE['os_version']) ? $_COOKIE['os_version'] : FALSE;
        } 
        elseif (!isset($deviceSession->deviceId)) 
        {
            
           /*
            * Detection Script Start 
            * 
            * 
            */
            
            
            $session = new Zend_Session_Namespace("devices_partner_mobile_details");
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
            
            
           /*
            * Detection Script End
            *
            *
            */
            
            
            /*    old methods and uncomment
             *
            *
            $deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();
            $exactMatch = $deviceDetector->detectDeviceByUserAgent();
            //If this is not a wireless device redirect to the main site
            $isWireless = $deviceDetector->getDeviceAttribute('is_wireless_device', 'product_info');
            if (!$this->__disableMobileCheck()) {
            if (!$isWireless) {
            $this->_redirect("http://" . $config->nexva->application->base->url . $_SERVER['REQUEST_URI']);
            }
            }
            
            // get properties from the Wurfl
            $brandName = $deviceDetector->getDeviceAttribute('brand_name', 'product_info');
            $modelName = $deviceDetector->getDeviceAttribute('model_name', 'product_info');
            $marketing_name = $deviceDetector->getDeviceAttribute('marketing_name', 'product_info');
            $inputMethod = $deviceDetector->getDeviceAttribute('pointing_method', 'product_info');
            $osVersion = $deviceDetector->getDeviceAttribute('device_os_version', 'product_info');
            //get nexva device Id
            $deviceId = $deviceDetector->getNexvaDeviceId();
            if (empty($deviceId)) {
            // get the page where user comming from
            $requestUri = new Zend_Session_Namespace('RequestUri');
            $requestUri->uri = $_SERVER['REQUEST_URI'];
            $this->_redirect('/device');
            }
            // Set device id and name to session
            $deviceSession->deviceId = $deviceId;
            $model = !empty($marketing_name) ? $marketing_name : $modelName;
            $deviceSession->deviceName = $brandName . ' ' . $model;
            $deviceSession->inputMethod = $inputMethod;
            $deviceSession->isWireless = $isWireless;
            $deviceSession->exactMatch = $exactMatch;
             
            *
            *
            */
            
            

            
            if (empty($deviceId)) {
                // get the page where user comming from
                $requestUri = new Zend_Session_Namespace('RequestUri');
                $requestUri->uri = $_SERVER['REQUEST_URI'];
                $this->_redirect('/device');
            }
            // Set device id and name to session
            $deviceSession->deviceId = $deviceId;
            $model = !empty($marketing_name) ? $marketing_name : $modelName;
            $deviceSession->deviceName = $brandName . ' ' . $model;
            $deviceSession->inputMethod = $inputMethod;
            $deviceSession->isWireless = $isWireless;
            $deviceSession->exactMatch = $exactMatch;

            if ($osVersion) {
                $deviceSession->osVersion = $osVersion;
                setcookie('os_version', $deviceSession->osVersion, time() + (3600 * 12 * 36), '/');
            }

            // set cookies
            setcookie('device_id', $deviceId, time() + (3600 * 12 * 36), '/');
            setcookie('device_name', $deviceSession->deviceName, time() + (3600 * 12 * 36), '/');

            //adding to statics table


            $statisticsDevice = new Model_StatisticDevice();
            $statisticsDevice->updateDeviceStatistics($deviceId);
        }
        // set to the view
        $this->view->phone = isset($deviceSession->deviceName) ? $deviceSession->deviceName : $this->getDeviceName();

        // chech widgit version headers
        if (isset($_SERVER['HTTP_X_NEXVA_APPVERSION_MAJOR'])) {
            $msg = '';
            $msg = $this->checkWidgitHeaders();
            $this->view->specialMessage = $msg;
        }
        if (isset(Zend_Auth::getInstance()->getIdentity()->id)) {
            $userMeta = new Model_UserMeta();
            $userMeta->setEntityId(Zend_Auth::getInstance()->getIdentity()->id);
            $name = $userMeta->FIRST_NAME;
        }

        $this->view->messages = array('', ''); //just in case someone is using it somewhere. Removed the appcount -JP
        // show recomended apps on front page
        $this->view->showRecomendedApp = true;
        // load custom white lables
        $wlLayout = $this->getCustomLayout();
        if (!empty($wlLayout)) {
            $this->_helper->layout->setLayout("mobile/whitelabels/$wlLayout");
            $this->view->showRecomendedApp = false;
        }

        // start      
        $sitename = $_SERVER['HTTP_HOST'];
        $themeMeta = new Model_ThemeMeta();
        $row = $themeMeta->fetchRow('meta_name = "WHITELABLE_URL" AND meta_value = "' . mysql_escape_string($sitename) . '"');


        //Please add ID of the CHAP, to the array when you create mobile site for appstore apps, This has to be done in 
        //Mobile_IndexController controller's init function as well 
        $chapId = array(8056, 9860, 21677, 131024, 129330, 25022, 230676); 


        $this->view->hideFooter = (isset($this->themeMeta->USER_ID) && $this->themeMeta->USER_ID == 131024) ? TRUE : FALSE; // Added for hide footer in MTN Iran
        
        if (isset($row['user_id']) && in_array($row['user_id'], $chapId)) {
            $this->_helper->layout->setLayout('mobile/partner');
        }
    }

    /**
     * Load whitelable theme file
     */
    protected function loadThemeFile() {
        if (($themeData = $this->loadThemeFileFromHost()) === false) {
            $whiteLabel = '';
            $whiteLabel = $this->_request->nexva_whitelabel;
            $fileName = (isset($whiteLabel) && !empty($whiteLabel) && ($whiteLabel != 'www')) ? $this->_request->nexva_whitelabel : 'nexva';

            $wlHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Whitelabel');
            $this->_conf = $wlHelper->getConf($fileName);

            //see if we can find anything in the meta tables to customize the site
            $sitename = $this->_conf->basic->sitename;
            $cache = Zend_Registry::get('cache');
            $key = 'THEME_META_' . md5($sitename);
            $themeData = null;
            if (($themeData = $cache->get($key)) === false) {
                $themeMeta = new Model_ThemeMeta();
                $row = $themeMeta->fetchRow('meta_name = "WHITELABLE_SITE_NAME" AND meta_value = "' . mysql_escape_string($sitename) . '"');

                if (!is_null($row) || !empty($row)) {
                    $userId = $row['user_id'];
                    $themeMeta->setEntityId($userId);
                    $themeData = $themeMeta->getAll();
                    $themeData->USER_ID = $userId;
                } else {
                    $themeData = null;
                }
                $cache->set($themeData, $key);
            }
            if (strtolower($sitename) != 'nexva' && ($_SERVER['SERVER_NAME'] != trim($themeData->WHITELABLE_URL))) {
                $url = stripos($themeData->WHITELABLE_URL, 'http://') === false ? 'http://' . $themeData->WHITELABLE_URL : $themeData->WHITELABLE_URL;
                $this->_redirect($url);
            }
        } else {
            $wlHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Whitelabel');
            $this->_conf = $wlHelper->getConf($themeData->WHITELABLE_THEME_NAME);
        }
        $this->themeMeta = $themeData;
        $this->view->themeMeta = $themeData; //setting the theme meta for use in theme
    }

    /**
     * This method was added so that whitelabels can have their own domain
     * Basically checks the server HTTP_HOST to see if it's there in our meta tables
     */
    protected function loadThemeFileFromHost() {
        $host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
        $host = (substr($host, 0, 3) == 'www') ? substr($host, 3) : $host;
        //see if we can find anything in the meta tables to customize the site
        $key = 'THEME_META_' . md5($host);
        $themeData = null;
        $themeMeta = new Model_ThemeMeta();
        $row = $themeMeta->fetchRow('meta_name = "WHITELABLE_URL" AND meta_value = "' . mysql_escape_string($host) . '"');
        if (!is_null($row) || !empty($row)) {
            $userId = $row['user_id'];
            $themeMeta->setEntityId($userId);
            $themeData = $themeMeta->getAll();
            $themeData->USER_ID = $userId;
        } else {
            return false;
        }
        return $themeData;
    }

    /**
     * 
     * Checks whether this IP is allowed to access the site. For chaps 
     */
    protected function isAuthenticatedClient() {

        if ($this->themeMeta->WHITELABLE_ACCESS_TYPE == 'OPEN') {
            return true;
        }

        $accessNs = new Zend_Session_Namespace('Access');

        //a check to clear our session in case we want to check that
        if ($this->_getParam('byebye', false) !== false) {
            $accessNs->isAuthorized = false;
            return false;
        }

        //Put in a handy backdoor for debugging and what not
        $sessionSet = isset($accessNs->isAuthorized) ? $accessNs->isAuthorized : false;
        $phraseSet = ($this->_getParam('allyourbase', false) === false) ? false : true; //string empty is true in this case
        if ($sessionSet || $phraseSet) {
            $accessNs->isAuthorized = true;
            return true;
        }

        //return true if we're going to the static page
        if (($this->_request->getControllerName() == 'page' && $this->_request->getActionName() == 'static')
                || $this->_request->getControllerName() == 'error' || $this->themeMeta->WHITELABLE_ACCESS_TYPE == 'OPEN') {
            return true;
        }

        /**
         * By : Sudha 
         * On : 2012-04-26
         * Purpose : Removed hardcoded airtel ip filter.
         * Description : Removed hardcoded airtel ip filter to allow anyother client / operator to have ip filtering.
         * Note : Create a .ipf file where the name of the file should match the whitelabel name under 
         *        application\modules\mobile\whitelabels\ipfilters\<whitlabel name>.ipf
         */
        $ipFilter = new Nexva_Util_NetworkFilter_IpFilter(strtolower($this->themeMeta->WHITELABLE_SITE_NAME));
        // End of change <2012-04-26>
        //$ipFilter   = new Nexva_Util_NetworkFilter_IpFilter('airtel');
        //Might have to change this to look at HTTP headers if this doesn't work
        $request = new Zend_Controller_Request_Http();
        $status = $ipFilter->ipFilter($request->getClientIp(true));

        if ($status) {
            return true;
        }
        return false;
    }

    public function postDispatch() {
        $this->dumpLog();

        //message handling
        $this->view->flashMessages = $this->_helper->FlashMessenger->getMessages();
        $this->_helper->FlashMessenger->setNamespace('ERROR');
        $this->view->flashErrors = $this->_helper->FlashMessenger->getMessages();
    }

    protected function trimAndElipsis($string, $count, $ellipsis = ' ...') {
        $length = '';
        if (strlen($string) > $count) {
            $length -= strlen($ellipsis);  // $length =  $length – strlen($end);
            $string = substr($string, 0, $count);
            $string .= $ellipsis;  //  $string =  $string . $end;
        }
        return $string;
    }

    /**
     * GEt the device ID
     * @return <type>
     */
    public function getDeviceId() {
        $deviceSession = new Zend_Session_Namespace('Device');
        return $deviceSession->deviceId;
    }

    /**
     * Set the device ID
     * @param <type> $deviceId
     */
    public function setDeviceId($deviceId) {
        $deviceSession = new Zend_Session_Namespace('Device');
        $deviceSession->deviceId = $deviceId;
    }

    /**
     * Get device name <brand>|<model>
     * @return <type>
     */
    public function getDeviceName() {
        $deviceSession = new Zend_Session_Namespace('Device');
        return $deviceSession->deviceName;
    }

    /**
     * Set the device Name
     * @param <type> $deviceName
     */
    public function setDeviceName($deviceName) {
        $deviceSession = new Zend_Session_Namespace('Device');
        $deviceSession->deviceName = $deviceName;
    }

    /**
     * Set all categories list to private $_categories
     */
    protected function setCategories() {
        $categories = new Mobile_Model_Category();
        $this->_categories = $categories->getCategorylist($this->themeMeta->USER_ID);
    }

    /**
     * Enalbe the paginator for pages
     * @param <type> $results
     * @return <type>
     */
    protected function enablePagenator($results) {
        $paginater = Zend_Paginator::factory(
                        $results
        );
        $paginater->setCurrentPageNumber($this->_request->getParam('go', 0));
        $paginater->setItemCountPerPage(Zend_Registry::get('config')->mobile->paginator->limit);
        return $paginater;
    }

    /**
     * Check widget headers and check the version of the widget
     * @return <type>
     */
    protected function checkWidgitHeaders() {
        $new_version_available = FALSE;
        $widgitVersionHeaders = array(
            MAJOR => isset($_SERVER['HTTP_X_NEXVA_APPVERSION_MAJOR']) ? $_SERVER['HTTP_X_NEXVA_APPVERSION_MAJOR'] : '',
            MINOR => isset($_SERVER['HTTP_X_NEXVA_APPVERSION_MINOR']) ? $_SERVER['HTTP_X_NEXVA_APPVERSION_MINOR'] : '',
            REVISION => isset($_SERVER['HTTP_X_NEXVA_APPVERSION_REVISION']) ? $_SERVER['HTTP_X_NEXVA_APPVERSION_REVISION'] : ''
        );

        $config = Zend_Registry::get("config");
        $widgetId = $config->nexva->application->widget->id;
        $model = new Model_Product();
        $product = $model->getProductDetailsById($widgetId);
        $version = $product['version'];
        $currentVersion = explode('.', $version);
        $widgitCurrentVersion = array(
            MAJOR => isset($currentVersion[0]) ? $currentVersion[0] : 0,
            MINOR => isset($currentVersion[1]) ? $currentVersion[1] : 0,
            REVISION => isset($currentVersion[2]) ? $currentVersion[2] : 0,
        );

        $typeOfUpgrade = NULL;
        foreach ($widgitVersionHeaders as $key => $value) {
            if ($value < $widgitCurrentVersion[$key]) {
                $typeOfUpgrade = $key;
                break;
            }
        }

        switch ($typeOfUpgrade) {
            case MAJOR:
                //@TODO : add redirect to users
                return '<a href="app/show/id/' . $widgetId . '/#content">New major version of widgit is available for your ' . $this->getDeviceName() . ', Click here to Upgrade!.</a>';
                break;
            case MINOR:
                return '<a href="app/show/id/' . $widgetId . '/#content">New minor version of widgit is available for your ' . $this->getDeviceName() . ', Click here to Upgrade!.</a>';
                break;
            case REVISION:
                return '<a href="app/show/id/' . $widgetId . '/#content">New revision of widgit is available for your ' . $this->getDeviceName() . ', Click here to Upgrade!.</a>';
                break;
        }
    }

    /**
     * Load custom white lable layout
     * @return <type>
     */
    private function getCustomLayout() {
        $whiteLable = '';
        $whiteLable = $this->_request->nexva_whitelabel;
        $config = Zend_Registry::get("config");
        $count = $config->nexva->mobile->whitelabels->count();
        for ($i = 0; $i < $count; $i++) {
            $wl = $config->nexva->mobile->whitelabels->$i;
            if ($whiteLable == $wl)
                return $wl;
        }
        return false;
    }

    private function initLog() {
        if (APPLICATION_ENV == 'development') {
            $this->__logEnabled = false;
        }


        if ($this->__logEnabled) { //don't want this activating for all - JP
            $db = Zend_Registry::get('db');
            $profiler = new Nexva_Db_Profiler_Generic();
            $profiler->setEnabled(true);
            $db->setProfiler($profiler);
            Zend_Registry::set('db', $db);
        }
    }

    private function dumpLog() {
        if ($this->__logEnabled) {//don't want this activating for all - JP
            $db = Zend_Registry::get('db');
            $profiler = $db->getProfiler();
            $config = Zend_Registry::get('config');
            $total = 0;
            $params = $this->_request->getParams();
            $traces = $profiler->getCallers();

            $filename = $config->nexva->application->logDirectory . '/queries-' . $params['controller'] . '-' . $params['action'] . '.log';
            $fd = fopen($filename, 'w');
            fwrite($fd, "DURATION\t\t\t\tQUERY\n\n");

            foreach ($profiler->getQueries() as $id => $query) {
                $duration = $query->getElapsedSecs();
                fwrite($fd, $duration . "\t\t");
                fwrite($fd, $traces[$id] . "\t\t");
                fwrite($fd, (str_ireplace(array("\n", "\r"), ' ', $query->getQuery())) . "\n");
                $total += $duration;
            }
            $totalQueries = count($profiler->getQueries());
            $footer = "Total Queries : {$totalQueries}\nTotal Time Spent : {$total} seconds";
            fwrite($fd, "\n\n\n" . $footer);
            fclose($fd);
        }
    }

    private function __setVersion() {
        //get the cache object and see what the site version is
        $cache = Zend_Registry::get('cache');
        if (($version = $cache->get('SITE_VERSION')) === false) {
            //no version, load it from file
            if (file_exists(APPLICATION_PATH . '/.version')) {
                $version = trim(file_get_contents(APPLICATION_PATH . '/.version'));
            } else {
                $version = '2.0';
            }
            $cache->set($version, 'SITE_VERSION');
        }

        $this->view->siteVersion = $version;
        $this->view->VER = '?' . $version; // for brevity
    }

    private function __disableMobileCheck() {
        $ns = new Zend_Session_Namespace('application');

        $flag = $this->_request->getParam('noredirect', false);
        $state = $ns->disableMobileDetection;
        if (!$flag && $ns->disableMobileDetection == true) {
            return true;
        }

        if ($flag == 1) {
            $ns->disableMobileDetection = true;
        } else {
            $ns->disableMobileDetection = false;
        }
        if ($ns->disableMobileDetection) {
            return true;
        }
        return false;
    }

    protected function __addMessage($message = '') {
        $this->_helper->FlashMessenger->resetNamespace();
        $this->_helper->FlashMessenger->addMessage($message);
    }

    protected function __addErrorMessage($message = '') {
        $this->_helper->FlashMessenger->setNamespace('ERROR');
        $this->_helper->FlashMessenger->addMessage($message);
        $this->_helper->FlashMessenger->resetNamespace();
    }

    protected function __addNoticeMessage($message = '') {
        $this->_helper->FlashMessenger->setNamespace('NOTICE');
        $this->_helper->FlashMessenger->addMessage($message);
        $this->_helper->FlashMessenger->resetNamespace();
    }

}

?>
