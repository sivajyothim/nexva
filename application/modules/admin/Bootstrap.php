<?php

class Admin_Bootstrap extends Zend_Application_Module_Bootstrap {


    protected function _initAutoLoad() {
        
    

        $autoloader = new Zend_Application_Module_Autoloader(
            array(
                    'namespace' => 'Admin_',
                    'basePath'  => APPLICATION_PATH .'/modules/admin',
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
	
	protected function _initActionHelpers() {
	    

		
		Zend_Controller_Action_HelperBroker::addPrefix ( 'Nexva_Controller_Action_Helper' );

	
	}
 
 
   
    
    public function _initRoutes() {
        

        $config = Zend_Registry::get ( "config" );
        $domain = $config->nexva->application->admin->url;
            
        $routeHostName = new Zend_Controller_Router_Route_Hostname ($domain);

        //Default routing 
        $routes [] = new Zend_Controller_Router_Route ( 
                                ':controller/:action/*', array (
                                              'module' => 'admin',
                                              'controller' => 'index',
                                              'action' => 'index'
                                            ) 
                                   );
        
		
		if (php_sapi_name () != 'cli') {
			
			if ($_SERVER ['SERVER_NAME'] == $domain) {
				
				Zend_Controller_Front::getInstance ()->getRouter ()->addRoutes ( $routes );
			
			}
		
		}
        

           
    }
    

    
    
    

}

