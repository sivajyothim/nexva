<?php

class Pbo_Bootstrap extends Zend_Application_Module_Bootstrap {

    protected function _initAutoLoad() {
        

        $autoloader = new Zend_Application_Module_Autoloader(
                        array(
                            'namespace' => 'Pbo_',
                            'basePath' => APPLICATION_PATH . '/modules/pbo',
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
        $domain = $config->nexva->application->pbo->url;

        $routeHostName = new Zend_Controller_Router_Route_Hostname($domain);

        //Default routing
        $routes[] = $routeHostName->chain(
                new Zend_Controller_Router_Route(
                        ':controller/:action/*', array(
                    'module' => 'pbo',
                    'controller' => 'index',
                    'action' => 'index'
                        )
                )
        );

        // if it is pbo.nexva.com and then load the routings for pbo module
        if (php_sapi_name() != 'cli') {

            if ($_SERVER ['SERVER_NAME'] == $domain) {

                Zend_Controller_Front::getInstance()->getRouter()->addRoutes($routes);
            }
        }
    }
    
    //Initilaize zend ACL
    public function _initAcl()
    {
        //Omit the process in CLI mode
        if (php_sapi_name() != 'cli') 
        {        
            $helper = new Nexva_Controller_Action_Helper_AclPbo();
            $helper->setRoles();
            $helper->setResources();
            $helper->setPrivilages();
            $helper->setAcl();

            //Register the ACL plugin - Then it will be called automatically,whenever an acion is called
            $frontController = Zend_Controller_Front::getInstance(); 
            $frontController->registerPlugin(new Nexva_Plugin_AclPbo());           
        
        }
    }
    
    //Populate menu items according to the Privileges on ACL
    protected function _initNavigation()
    {
        //Omit the process in CLI mode
        if (php_sapi_name() != 'cli') 
        {
            $frontController = Zend_Controller_Front::getInstance();             
            $frontController->registerPlugin(new Nexva_Plugin_NavigationPbo());
        }
    }
    
}

