<?php

class Whitelabel_Bootstrap extends Zend_Application_Module_Bootstrap {

  protected function _initAutoload() {



 
      
    $autoloader = new Zend_Application_Module_Autoloader(
            array(
              'namespace' => 'Whitelabel_',
              'basePath' => APPLICATION_PATH . '/modules/whitelabel',
              'resourceTypes' => array(
                'form' => array(
                  'path' => 'forms',
                  'namespace' => 'Form',
                ),
                'model' => array(
                  'path' => 'models',
                  'namespace' => 'Model',
                )
              )
            )
    );

    return $autoloader;
  }

  public function _initApplication() {
      
 
       Zend_Controller_Action_HelperBroker::addPath(
            APPLICATION_PATH .'/../library/Nexva/Controller/Action/Helper/Whitelabel');
  }
  
  public function _initRoutes() {

	$config = Zend_Registry::get("config");
	
	/**
	@todo: lookup SERVER_NAME in the DB and check if it's masked. If it's NOT masked, then allow the code below to run. 
	Basically the code below would check the SERVER_NAME to see if it matches the regex pattern [username].wl.[domain.com]. E.g:
					blah.wl.nexva.com  
					blah.wl.mobilereloaded.com 
					.
					etc.
					
	If it does not find a match, it would return thus allowing the default module to load.
	
	**/
 		
	
	if (php_sapi_name () != 'cli') {
	
	$domain = $_SERVER['SERVER_NAME'];
	
	$regex  = '((?:[a-z][a-z0-9_]*))';	// [username] in [username].wl.domain.com 
	$regex .='(\\.)';	// . 
	$regex .='(wl)';	// wl in [username].wl.domain.com
	$regex .='(\\.)';	// .
	$regex .='((?:[a-z][a-z0-9_-]*))';	// domain
	$regex .='(\\.)';	 // .
	$regex .='((?:[a-z][a-z0-9_-]*))';	// TLD
	
	if ($c=preg_match_all ("/".$regex."/is", $domain, $matches)) {	
		$chapName = $matches[1][0]; //@todo: do something useful with $chapName		
  	}	
	else 
		return; //didn't match, return 

  	 
  	$routeHostName = new Zend_Controller_Router_Route_Hostname($domain);
  	$routes[] = $routeHostName->chain(
	  	new Zend_Controller_Router_Route(
	  	                    ':controller/:action/*', array(
	  	                  'module' => 'whitelabel',
	  	                  'controller' => 'index',
	  	                  'action' => 'index'
	  		)
  		)
  	);  

  	// @todo: Write your own routes here
      

  		if ($_SERVER ['SERVER_NAME'] == $domain) {
  			Zend_Controller_Front::getInstance()->getRouter()->addRoutes($routes);
  		}
  	}  	
      
  }
  

}

;