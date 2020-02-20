<?php

class Partner_Bootstrap extends Zend_Application_Module_Bootstrap {

    
    protected function _initAutoLoad() {
        


        $autoloader = new Zend_Application_Module_Autoloader(
                        array(
                            'namespace' => 'Partner_',
                            'basePath' => APPLICATION_PATH . '/modules/partner',
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
 

        // Don't run this, if this is cli or api.nexva.com call
        // strcmp(substr($_SERVER['SERVER_NAME'],0,4), 'api.') !== 0  check for the api.nexva.com is requested.. 



        if (php_sapi_name() != 'cli') {

            if (strcmp(substr($_SERVER['SERVER_NAME'], 0, 4), 'api.') !== 0) {

                // check if the domain exist in the theme meta if then start the route 
                $themeMeta = new Model_ThemeMeta();
                $domainUser = $themeMeta->getThemeByHostNameForPartner($_SERVER['SERVER_NAME']);

                $sessionChapDetails = new Zend_Session_Namespace('partner');
                $sessionChapDetails->id = $domainUser;
                
                $session = new Zend_Session_Namespace("devices_partner_web");
                $isWireless = $session->is_mobile_device;
                
                if(!$isWireless and $session->is_check == false) {
                	$deviceDetection =  Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
                	$deviceInfo = $deviceDetection->getNexvaDeviceId($_SERVER['HTTP_USER_AGENT']);
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
                
            
      
      

                if (!$isWireless) {
                    if ($domainUser) {

                        $domain = $_SERVER['SERVER_NAME'];


                        $routeHostName = new Zend_Controller_Router_Route_Hostname($domain);

                        $routes[] = $routeHostName->chain(
                                new Zend_Controller_Router_Route(
                                        ':controller/:action/*', array(
                                    'module' => 'partner',
                                    'controller' => 'index',
                                    'action' => 'index'
                                        )
                                )
                        );

                        $routes[] = $routeHostName->chain(
                                new Zend_Controller_Router_Route_Regex(
                                        "category/index/id/([0-9]*)/(/page/([0-9]*))?",
                                        array('module' => 'partner', 'controller' => 'category', 'action' => 'index'),
                                        array(1 => 'id', 2 => 'page',),
                                        'category/index/id/%d/page/%d')
                        );


                        $routes[] = $routeHostName->chain(
                                new Zend_Controller_Router_Route_Regex(
                                        "category/index/id/([0-9]*)/(/page/([0-9]*))?",
                                        array('module' => 'partner', 'controller' => 'category', 'action' => 'index'),
                                        array(1 => 'id', 2 => 'page',),
                                        'category/index/id/%d/page/%d')
                        );



                        $routes[] = $routeHostName->chain(
                                new Zend_Controller_Router_Route_Regex(
                                        "app/detail/id/([0-9]*)/(/page/([0-9]*))?",
                                        array('module' => 'partner', 'controller' => 'app', 'action' => 'index'),
                                        array(1 => 'id'),
                                        'app/detail/id/%d/page/%d')
                        );
                        /*
                          $routes[] = $routeHostName->chain(
                          $routes['category'] = new Zend_Controller_Router_Route_Regex(
                          "category/([A-Za-z-0-9]+)/([0-9]+)(/page/([0-9]*))?",
                          array('module' => 'partner', 'controller' => 'category', 'action' => 'index', 'device_id' => null), array(1 => 'slug', 2 => 'id', 4 => 'page'),
                          'category/%s/%d/page/%d')
                          );
                         */

                        $routes[] = $routeHostName->chain(
                                new Zend_Controller_Router_Route_Regex(
                                        "category/([A-Za-z-0-9&]+)/([0-9]+)(/page/([0-9]*))?",
                                        array('module' => 'partner', 'controller' => 'category', 'action' => 'index', 'device_id' => null), array(1 => 'slug', 2 => 'id', 4 => 'page'),
                                        'category/%s/%d/page/%d')
                        );

                        $routes[] = $routeHostName->chain(
                                new Zend_Controller_Router_Route_Regex(
                                        "app/(([A-Za-z-0-9]+).([0-9]+))+",
                                        array('module' => 'partner', 'controller' => 'app', 'action' => 'detail', 'id' => null),
                                        array(2 => 'slug', 3 => 'id'))
                        );



                        /*
                          $routes[] = $routeHostName->chain(
                          new Zend_Controller_Router_Route_Regex(
                          "category/index/page/([0-9]*))?",
                          array('controller' => 'category', 'action' => 'index', 'device_id' => null), array(1 => 'slug', 2 => 'id', 5 => 'page' , 3 => 'device_id'),
                          'category/%s/%d/%d/page/%d')
                          );
                         */

                        /* http://nexva.com/1234.en */
                        $routes[] = $routeHostName->chain(
                                new Zend_Controller_Router_Route_Regex(
                                        "([0-9]+)(?:.([A-Za-z-_]+))?",
                                        array('module' => 'partner', 'controller' => 'app', 'action' => 'detail', 'id' => null, 'language' => 'en'),
                                        array(1 => 'id', 2 => 'language'))
                        );


                        /*
                          $deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();
                          $deviceDetector->detectDeviceByUserAgent();
                          $isWireless = $deviceDetector->getDeviceAttribute('is_wireless_device', 'product_info');

                          //  Zend_Debug::dump($isWireless);

                          //die($domain);

                          if(!$isWireless and $domain == 'nextapps.mtnonline.com') {
                          Zend_Controller_Front::getInstance()->getRouter()->addRoutes($routes);
                          return;
                          }
                         */



                        if ($_SERVER ['SERVER_NAME'] == $domain) {

              

                            Zend_Controller_Front::getInstance()->getRouter()->addRoutes($routes);
                            

                            
                        }
                        
                        
        
                        
                    }
                }
            }
        }
    }
    
    protected function _initZFDebug() {
    
    
    
    
    
    	//  Enabling this method seems to break autocomplete. Use only when needed
    

    
    	 
    
    }

    /**
     * Initiate trnaslation if CHAP's language is not english an available anywhere in partner module, using $this->translate()
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
   
   /**
     * Get the country ISO and set to session
     */
   protected function _initCountry() {

       if (php_sapi_name() != 'cli') {
           if (strcmp(substr($_SERVER['SERVER_NAME'], 0, 4), 'api.') !== 0) {
                $sessionChapDetails = new Zend_Session_Namespace('partner');
                $chapId = $sessionChapDetails->id;
                
               if($chapId == 115189){
                    $geoData = Nexva_GeoData_Ip2Country_Factory::getProvider('Api');
               }
               else{
                    $geoData = Nexva_GeoData_Ip2Country_Factory::getProvider();
               }
               
               $countryCode  =   $geoData->getCountry($_SERVER['REMOTE_ADDR']);
               $sessionCounty = new Zend_Session_Namespace('county');
               $sessionCounty->code = $countryCode['code'];  
           }

          /* if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                $geoData = Nexva_GeoData_Ip2Country_Factory::getProvider('Api');
                $countryCode  =   $geoData->getCountry($_SERVER['REMOTE_ADDR']);
                print_r($countryCode);
           }*/
        }
   }

}

