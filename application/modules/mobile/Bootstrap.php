<?php

class Mobile_Bootstrap extends Zend_Application_Module_Bootstrap {

  protected function _initAutoload() {
      


    $autoloader = new Zend_Application_Module_Autoloader(
            array(
              'namespace' => 'Mobile_',
              'basePath' => APPLICATION_PATH . '/modules/mobile',
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

    //load up the ip2country plugin
    
     //Don't run this, if this is cli or api.nexva.com call
     //strcmp(substr($_SERVER['SERVER_NAME'],0,4), 'api.') !== 0  check for the api.nexva.com is requested.. 
    
        
      if (php_sapi_name() != 'cli') {
        	
        	if(strcmp(substr($_SERVER['SERVER_NAME'],0,4), 'api.') !== 0 ) {
    
              Zend_Controller_Action_HelperBroker::addHelper(new Nexva_Controller_Action_Helper_Locale());
              
        	}
        	
      }
    
    return $autoloader;
  }

  public function _initRoutes() {



        $config = Zend_Registry::get("config");
        $domain = $config->nexva->application->mobile->url;
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'URI_SERVER_'.strtoupper(strtr($_SERVER['SERVER_NAME'], '.', '_'));
        
        if(!empty($_SERVER['SERVER_NAME'])) {

            
            if (($domain = $cache->get($key)) !== false)
            {
                if($domain == 'none')
                {                    
                    $domain = '';
                
                } else {
                    $domain;
                }
            
            } else {
            
                $db     = Zend_Registry::get("db");
                $query  = $db->query("  SELECT theme_meta.user_id, users.`status`
                                    FROM theme_meta LEFT JOIN users ON users.id = theme_meta.user_id
                                    WHERE meta_name = 'WHITELABLE_URL' AND meta_value = '{$_SERVER['SERVER_NAME']}'"
                                );
            
                    if ($row = $query->fetch()) {
                        if (isset($row->user_id) && $row->status == 1) {
                            $domain         = $_SERVER['SERVER_NAME'];
                    }
                } 
                
                if($domain == '' ) {
                    $domain = 'none';
                }
            
                $cache->set($domain, $key, 3600);
            
            }
      
            
           
 
            
            
        }
    
        $routeHostName = new Zend_Controller_Router_Route_Hostname($domain);
        $routes[] = $routeHostName->chain(
                new Zend_Controller_Router_Route(
                    ':controller/:action/*', array(
                  'module' => 'mobile',
                  'controller' => 'index',
                  'action' => 'index'
                )
            )
        );

        $routes[] = $routeHostName->chain(
            new Zend_Controller_Router_Route_Regex(
                "app/([A-Za-z0-9_-]*)\.([0-9]*)((\.([A-Za-z]*))?)",
                array('controller' => 'app', 'action' => 'show', 'id' => null, 'language' => 'en', 'module' => 'mobile'),
                array(1 => 'slug', 2 => 'id', 3 => 'language'),
                '%s.%s.%s'
        ));
        
        $routes[] = $routeHostName->chain(
        		new Zend_Controller_Router_Route_Regex(
        				"app/id/([0-9]+)",
        				array('controller' => 'app', 'action' => 'show', 'id' => null, 'language' => 'en', 'module' => 'mobile'),
        				array( 1 => 'id'),
        				'%s.%s.%s'
        		));
        
        $routes[] = $routeHostName->chain(
            new Zend_Controller_Router_Route_Regex(
                "cp/([A-Za-z0-9_-]*)\.([0-9]*)((\.([A-Za-z]*))?)",
                array('controller' => 'app', 'action' => 'cpproducts', 'id' => null, 'language' => 'en', 'module' => 'mobile'),
                array(1 => 'slug', 2 => 'id', 3 => 'language'),
                '%s.%s.%s'
        ));
        
       $routes[] = $routeHostName->chain(
            new Zend_Controller_Router_Route_Regex(
             "nt/([0-9]+)",
                array('controller' => 'rca-app', 'action' => 'show', 'id' => null, 'module' => 'mobile'),
                array(1 => 'id'),
                '%s'
        ));
   
        $routes[] = $routeHostName->chain(
            new Zend_Controller_Router_Route_Regex(
                "([0-9]+)(?:.([A-Za-z]+))?",
                array('controller' => 'app', 'action' => 'show', 'id' => null, 'language' => 'en', 'module' => 'mobile'),
                array(1 => 'id', 2 => 'language'),
                '%s.%s'
        ));
        
         
        
            

        $routes[] = $routeHostName->chain(
            new Zend_Controller_Router_Route_Regex(
                "([0-9]+)",
                array('controller' => 'app', 'action' => 'show', 'id' => null, 'module' => 'mobile'),
                array(1 => 'id'),
                '%s'
        ));
        
        $routes[] = $routeHostName->chain(
            new Zend_Controller_Router_Route_Regex(
             "np/([0-9]+)(\.([A-Za-z0-9_-]*))*",
                array('controller' => 'nexpage', 'action' => 'index', 'id' => null, 'module' => 'mobile'),
                array(1 => 'id'),
                '%s'
        ));
        
        
         $routes[] = $routeHostName->chain(
            new Zend_Controller_Router_Route_Regex(
             "np/([0-9]+)(\.([A-Za-z0-9_-]*))*",
                array('controller' => 'nexpage', 'action' => 'index', 'id' => null, 'module' => 'mobile'),
                array(1 => 'id'),
                '%s'
        ));
        
          $routes[] = $routeHostName->chain(
            new Zend_Controller_Router_Route_Regex(
             "np/([0-9]+)/go/([0-9]+)",
                array('controller' => 'nexpage', 'action' => 'index', 'id' => null, 'module' => 'mobile'),
                array(1 => 'id', 2 => 'go'),
                '%s'
        ));
        

          
          
		if (php_sapi_name () != 'cli') {
			if ($_SERVER ['SERVER_NAME'] == $domain) {
				Zend_Controller_Front::getInstance()->getRouter()->addRoutes($routes);
			}
		}

  }

}

;