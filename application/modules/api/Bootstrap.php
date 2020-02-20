<?php

class Api_Bootstrap extends Zend_Application_Module_Bootstrap {
    
        protected function _initAutoLoad() {
            
 

        $autoloader = new Zend_Application_Module_Autoloader(
            array(
                    'namespace' => 'Api_',
                    'basePath'  => APPLICATION_PATH .'/modules/api',
                    'resourceTypes' => array (
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
        

     
        
        //Just making everything UTF8. This is a hack, need to find the proper Zend way
        $db     = Zend_Registry::get('db');
        $db->query('SET CHARACTER SET utf8')->execute();
    	
                                  
        $config = Zend_Registry::get ( "config" );
        $domain = $config->nexva->application->api->url;
        
        $routeHostName = new Zend_Controller_Router_Route_Hostname ( $domain );
      
        
        // v1 controller actions 
           $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                ':controller/:action/*', array (
                                              'module' => 'api',
                                              'controller' => 'index',
                                              'action' => 'index'
                                            ) 
                                   )
        
          );
          
          $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                '/inapp/1.0/authenticate.json', array (
                                              'module' => 'api',
                                              'controller' => 'inapp10',
                                              'action' => 'auth',
                                              'output' => 'json'
                                            ) 
                                   )
        
          );
          
          $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                '/inapp/1.0/payment/*', array (
                                              'module' => 'api',
                                              'controller' => 'inapp10',
                                              'action' => 'payment',
      
                                            ) 
                                   )
        
          );
          
          $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                '/inapp/1.0/callback/*', array (
                                              'module' => 'api',
                                              'controller' => 'inapp10',
                                              'action' => 'callback',
                                                            ) 
                                   )
        
          );
          
          $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                '/inapp/1.0/update-user-info/*', array (
                                              'module' => 'api',
                                              'controller' => 'inapp10',
                                              'action' => 'update-user-info',
                                                            ) 
                                   )
        
          );
          
          
           $routes[] =  $routeHostName->chain(  new Zend_Controller_Router_Route_Regex(
            "inapp/1.0/authenticate.([A-Za-z-0-9]+)",
            array (
                                              'module' => 'api',
                                              'controller' => 'inapp10',
                                              'action' => 'auth',
                                            ) ,
            array(1=> 'output'),
            'inapp/1.0/authenticate/%s'
            
        ));
        
           $routes[] =  $routeHostName->chain(  new Zend_Controller_Router_Route_Regex(
            "inapp/1.0/check-subscription.([A-Za-z-0-9]+)",
            array (
                                              'module' => 'api',
                                              'controller' => 'inapp10',
                                              'action' => 'check-subscription',
                                            ) ,
            array(1=> 'output'),
            'inapp/1.0/authenticate/%s'
            
        ));
        
          
          // v2 controller actions 
          
          $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                '/inapp/2.0/authenticate.json', array (
                                              'module' => 'api',
                                              'controller' => 'inapp20',
                                              'action' => 'auth',
                                              'output' => 'json'
                                            ) 
                                   )
        
          );
          
          $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                '/inapp/2.0/payment/*', array (
                                              'module' => 'api',
                                              'controller' => 'inapp20',
                                              'action' => 'payment',
      
                                            ) 
                                   )
        
          );
          
          $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                '/inapp/2.0/callback/*', array (
                                              'module' => 'api',
                                              'controller' => 'inapp20',
                                              'action' => 'callback',
                                                            ) 
                                   )
        
          );
          
          $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                '/inapp/2.0/update-user-info/*', array (
                                              'module' => 'api',
                                              'controller' => 'inapp20',
                                              'action' => 'update-user-info',
                                                            ) 
                                   )
        
          );
          
          
           $routes[] =  $routeHostName->chain(  new Zend_Controller_Router_Route_Regex(
            "inapp/2.0/authenticate.([A-Za-z-0-9]+)",
            array (
                                              'module' => 'api',
                                              'controller' => 'inapp20',
                                              'action' => 'auth',
                                            ) ,
            array(1=> 'output'),
            'inapp/2.0/authenticate/%s'
            
        ));
        
           $routes[] =  $routeHostName->chain(  new Zend_Controller_Router_Route_Regex(
            "inapp/2.0/check-subscription.([A-Za-z-0-9]+)",
            array (
                                              'module' => 'api',
                                              'controller' => 'inapp20',
                                              'action' => 'check-subscription',
                                            ) ,
            array(1=> 'output'),
            'inapp/2.0/authenticate/%s'
            
        ));
        
        
           $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                '/inapp/2.0/select-payment-gateway/*', array (
                                              'module' => 'api',
                                              'controller' => 'inapp20',
                                              'action' => 'select-payment-gateway',
                                                            ) 
                                   )
        
          );
          
           $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                '/inapp/2.0/validate-submitted-param/*', array (
                                              'module' => 'api',
                                              'controller' => 'inapp20',
                                              'action' => 'validate-submitted-param',
                                                            ) 
                                   )
        
          );
          
          $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                '/inapp/2.0/paynow/*', array (
                                              'module' => 'api',
                                              'controller' => 'inapp20',
                                              'action' => 'paynow',
                                                            ) 
                                   )
        
          );
          
          $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                '/inapp/2.0/callback-paythru/*', array (
                                              'module' => 'api',
                                              'controller' => 'inapp20',
                                              'action' => 'callback-paythru',
                                                            ) 
                                   )
        
          );
          
          
          $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                '/inapp/2.0/callback-google-checkout/*', array (
                                              'module' => 'api',
                                              'controller' => 'inapp20',
                                              'action' => 'callback-google-checkout',
                                                            ) 
                                   )
        
          );
          
          $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                '/inapp/2.0/postback-google-checkout/*', array (
                                              'module' => 'api',
                                              'controller' => 'inapp20',
                                              'action' => 'postback-google-checkout',
                                                            ) 
                                   )
        
          );
			
		// if it is api.nexva.com and then load the routings for api module
		if (php_sapi_name () != 'cli') {
			
			if ($_SERVER ['SERVER_NAME'] == $domain) {
				
				Zend_Controller_Front::getInstance ()->getRouter ()->addRoutes ( $routes );
			
			}
        
        }

                          

                                   
        
    }

}


