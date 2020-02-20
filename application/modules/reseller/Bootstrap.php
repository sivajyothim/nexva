<?php

class Reseller_Bootstrap extends Zend_Application_Module_Bootstrap {


    protected function _initAutoLoad() {
        



        $autoloader = new Zend_Application_Module_Autoloader(
            array(
                    'namespace' => 'Reseller_',
                    'basePath'  => APPLICATION_PATH .'/modules/reseller',
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
        $domain = $config->nexva->application->reseller->url;

        $routeHostName = new Zend_Controller_Router_Route_Hostname ($domain);

        //Default routing
        $routes [] = new Zend_Controller_Router_Route (
                                ':controller/:action/*', array (
                                              'module' => 'reseller',
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
?>
