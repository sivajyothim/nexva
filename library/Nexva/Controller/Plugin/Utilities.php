<?php
class Nexva_Controller_Plugin_Utilities extends Zend_Controller_Plugin_Abstract
{
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $front = Zend_Controller_Front::getInstance();
        if (! ($front->getPlugin('Zend_Controller_Plugin_ErrorHandler') instanceof Zend_Controller_Plugin_ErrorHandler))
            return;
        $error = $front->getPlugin('Zend_Controller_Plugin_ErrorHandler');
        $testRequest = new Zend_Controller_Request_HTTP();
        $testRequest->setModuleName($request->getModuleName())
            ->setControllerName($error->getErrorHandlerController())
            ->setActionName($error->getErrorHandlerAction());
        if ($front->getDispatcher()->isDispatchable($testRequest)) {
            $error->setErrorHandlerModule($request->getModuleName());
        }
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request) {      
        $this->stagingAuth($request);
    }

    /**
     * Fires of a HTTP Auth challenge if the code is running in staging enviroment.
     *     
     * @return void
     */
    protected function stagingAuth(Zend_Controller_Request_Abstract $request) {
        
        // disabled authetication on staging chathura 2013-10-02
        
        return;
        
        if( 'staging' != APPLICATION_ENV) return; //we only require authentication in staging
        
        if( "api"  == $request->getModuleName() ) return;
        
        if ('postbackpayment' == $request->getActionName() && 'app' == $request->getControllerName()) return ; //for payment gateway callbacks, we allow the request to go through. @todo: find a better way to do this.

        //we will skip authentication for api module since most of the api calls rely on their own http authentication mechanisms:

        $config = Zend_Registry::get('config');

        $session = new Zend_Session_Namespace('stage_auth');

        if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {

            $o = new Nexva_Util_Auth_Htpasswd();
            $ret = $o->authenticate($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'],$config->nexva->application->authfile);

            if($ret)
                $session->authenticated  = true;
        }

        if( !$session->authenticated )  {
            header('WWW-Authenticate: Basic realm="Stage Server"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Access denied';
            die();
        }



    }
}