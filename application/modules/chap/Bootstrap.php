<?php

class Chap_Bootstrap extends Zend_Application_Module_Bootstrap {


    protected function _initAutoload() {

        

        $autoloader = new Zend_Application_Module_Autoloader(   
            array(
                    'namespace' => 'Chap_',
                    'basePath'  => APPLICATION_PATH .'/modules/chap',
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
    	

                                  
        $config = Zend_Registry::get ( "config" );
        $domain = $config->nexva->application->chap->url;
        
        $routeHostName = new Zend_Controller_Router_Route_Hostname ( $domain );
      
        
        // v1 controller actions 
           $routes[] =  $routeHostName->chain( 
                           new Zend_Controller_Router_Route ( 
                                ':controller/:action/*', array (
                                              'module' => 'chap',
                                              'controller' => 'index',
                                              'action' => 'index'
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

    