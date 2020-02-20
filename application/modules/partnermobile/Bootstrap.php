<?php

class Partnermobile_Bootstrap extends Zend_Application_Module_Bootstrap {

    protected function _initAutoLoad() {
        


        $autoloader = new Zend_Application_Module_Autoloader(
                        array(
                            'namespace' => 'Partnermobile_',
                            'basePath' => APPLICATION_PATH . '/modules/partnermobile',
                            'resourceTypes' => array(
                                'form' => array(
                                    'path' => 'forms',
                                    'namespace' => 'Form',
                                ),
                                'model' => array(
                                    'path' => 'models',
                                    'namespace' => 'Model',
                                ),
                            )
                        )
        );

        return $autoloader;
    }
    
    public function _initRoutes() {
        


        $config = Zend_Registry::get("config");
        $domain = $config->nexva->application->partnermobile->url;

        // Don't run this, if this is cli or api.nexva.com call
        if (php_sapi_name() != 'cli') 
        {
            

            
            if (strcmp(substr($_SERVER['SERVER_NAME'], 0, 4), 'api.') !== 0) 
            {
                // check if the domain exist in the theme meta if then start the route 
                $themeMeta = new Model_ThemeMeta();          
                $domainUser = $themeMeta->getThemeByHostNameForPartner($_SERVER['SERVER_NAME']);
                

                

                                 
                $sessionChapDetails = new Zend_Session_Namespace('partnermobile');
                $sessionChapDetails->id = $domainUser;     

   
                
                //$deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();
                //$deviceDetector->detectDeviceByUserAgent();
                //$isWireless = $deviceDetector->getDeviceAttribute('is_wireless_device', 'product_info');
                

            $session = new Zend_Session_Namespace("devices_partner_web");
            $isWireless = $session->is_mobile_device;
            
            if(!$isWireless and $session->is_check == false) {
            	$deviceDetection =  Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
            	$deviceInfo = $deviceDetection->getNexvaDeviceId($_SERVER['HTTP_USER_AGENT']);
            	//$deviceInfo = $deviceDetection->getNexvaDeviceId("Mozilla/5.0 (Linux; U; Android 2.1-update1; de-de; HTC Desire 1.19.161.5 Build/ERE27) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17");
            	
            	//If this is not a wireless device redirect to the main site
            	$deviceId = $deviceInfo->id;
            	$session->deviceId = $deviceId;
            	$isWireless = $deviceInfo->is_mobile_device;
            	$session->is_mobile_device = $isWireless;
            	$session->is_check = true;
            	$session->platfrom = $deviceInfo->platform;
            	
            	
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
            	
            }
           
                //Zend_Debug::dump($domainUser);die();
                if($isWireless)
                {  

                    
                         if ($domainUser) 
                         {
                            $domain = $_SERVER['SERVER_NAME'];

                            $routeHostName = new Zend_Controller_Router_Route_Hostname($domain);


                            
                            
                            $routes[] = $routeHostName->chain(
                            		new Zend_Controller_Router_Route(
                            				':controller/:action/*', array(
                            						'module' => 'partnermobile',
                            						'controller' => 'index',
                            						'action' => 'index'
                            				)
                            		)
                            );
                            
                            $routes[] = $routeHostName->chain(
                                        new Zend_Controller_Router_Route(
                                                                        ':id', array(
                                                                            'module' => 'partnermobile',
                                                                            'controller' => 'app',
                                                                            'action'     => 'detail'
                                                                        )
                                                                    )
                                                             );
                            
           
           /*                 
                            $routes[] = $routeHostName->chain(
                            		new Zend_Controller_Router_Route_Regex(
                            				"([0-9]+)",
                            				array('controller' => 'app', 'action' => 'detail', 'id' => null, 'module' => 'partnermobile'),
                            				array(1 => 'id'),
                            				'%s'
                            		));
                            
                            */
                   
                            
                            $routes[] = $routeHostName->chain(
                            		new Zend_Controller_Router_Route(
                            				"nexva",
                            				array('module' => 'partnermobile', 'controller' => 'nexva', 'action' => 'index', 'id' => null),
                            				array(2 => 'slug', 3 => 'id'))
                            );
                            
                            
                            $routes[] = $routeHostName->chain(
                            		new Zend_Controller_Router_Route(
                            				"neXva",
                            				array('module' => 'partnermobile', 'controller' => 'nexva', 'action' => 'index', 'id' => null),
                            				array(2 => 'slug', 3 => 'id'))
                            );
                            
                            $routes[] = $routeHostName->chain(
                                new Zend_Controller_Router_Route(
                                    "apk",
                                    array('module' => 'partnermobile', 'controller' => 'apk', 'action' => 'index', 'id' => null),
                                    array(2 => 'slug', 3 => 'id'))
                            );
                            


                            if ($_SERVER ['SERVER_NAME'] == $domain) {

                                Zend_Controller_Front::getInstance()->getRouter()->addRoutes($routes);
                            }
                            else
                            {
             
                            }
                        }
                }

            }
        }
}

/**
     * Initiate trnaslation if CHAP's language is not english an available anywhere in partnermobile module, using $this->translate()
     */
   protected function _initTranslate() {

       if (php_sapi_name() != 'cli') {

            if (strcmp(substr($_SERVER['SERVER_NAME'], 0, 4), 'api.') !== 0) {

                //Register translate plugin
                $frontController = Zend_Controller_Front::getInstance(); 
                $frontController->registerPlugin(new Nexva_Plugin_Translate());
            }
        }
   }
   

         
   //Initialize error controller
 
    
    
     /*protected function _initZFDebug() { 

        if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                     Zend_Controller_Front::getInstance()->getBaseUrl();

                $autoloader = Zend_Loader_Autoloader::getInstance ();
                $autoloader->registerNamespace ( 'ZFDebug' );

                $db = Zend_Registry::get ( 'db' );

                $cache = Zend_Cache::factory ( 'Core', 'File' );

                Zend_Controller_Front::getInstance()->getBaseUrl();
                //APPLICATION_PATH
                $options = array ('plugins' => array ('Variables', 'Database' => array ('adapter' => $db ), 'File' => array ('basePath' => Zend_Controller_Front::getInstance ()->getBaseUrl () ), 'Memory', 'Time', 'Registry', 'Cache' => array ('backend' => $cache->getBackend () ), 'Exception' ) );

                $debug = new ZFDebug_Controller_Plugin_Debug ( $options );

                $this->bootstrap ( 'frontController' );
                $frontController = $this->getResource ( 'frontController' );
                $frontController->registerPlugin ( $debug ); 
                }

    }*/
}

