<?php


class Cpbo_Bootstrap extends Zend_Application_Module_Bootstrap {

	
    protected function _initAutoload() {
        

        $autoloader = new Zend_Application_Module_Autoloader(   
            array(
                    'namespace' => 'Cpbo_',
                    'basePath'  => APPLICATION_PATH .'/modules/cpbo',
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

	public function _initRoutes() 	{
		
		
        $config = Zend_Registry::get ( "config" );
        $domain = $config->nexva->application->cpbo->url;
		
  
        if(!isset($_SERVER['SERVER_NAME'])) return;
        
        $theamMeta = new Model_ThemeMeta();
        $chapId = $theamMeta->getUrlByHostNameForDeveloper($_SERVER['SERVER_NAME']);
		
	if($chapId) 
	    $domain = $_SERVER['SERVER_NAME'];
        
       // die($domain);
        
	   if( $_SERVER['SERVER_NAME'] == "testorangecp.nexva.com")  
	       $domain = 'testorangecp.nexva.com';
	  

        $routeHostName = new Zend_Controller_Router_Route_Hostname ($domain);
        

        //Default routing 
        $routes [] = new Zend_Controller_Router_Route ( 
                                ':controller/:action/*', array (
                                              'module' => 'cpbo',
                                              'controller' => 'index',
                                              'action' => 'index'
                                            ) 
                                   );
        
        $routes['r'] = new Zend_Controller_Router_Route(
                    "r/:chap",
        			array('controller' => 'user', 'module' => 'cpbo', 'action' => 'login', 'chap' => null)
        );
        
        
        if (php_sapi_name () != 'cli') {
            
            if ($_SERVER ['SERVER_NAME'] == $domain) {
                
                Zend_Controller_Front::getInstance ()->getRouter ()->addRoutes ( $routes );
            
            }
        
        }
        
           
    }
    
    private function _setChap($chapId = null) {
    	
    	//$chapId = $this->_request->chap != '' ? $this->_request->chap : 'nexva.mobi';
    	if( is_null($chapId) )$chapId = 4348;
    	
    		
    	$userModel = new Model_User();
    	//$chap = $userModel->getUserByUsername($chapId);
    	$chap = $userModel->getUserById($chapId);
    	
    	    	
    	$chapSessionNs = new Zend_Session_Namespace('chap');
    	$chapSessionNs->chap  = $chap;
    	 
    	
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

}
