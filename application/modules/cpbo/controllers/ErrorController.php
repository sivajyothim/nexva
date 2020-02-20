<?php

class Cpbo_ErrorController extends Nexva_Controller_Action_Web_MasterController
{

    public function errorAction() {
        
        $errors = $this->_getParam('error_handler');
        
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');  
        switch ($errors->type) {
           
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = $translate->translate("Page not found");
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                
                $this->view->message = $translate->translate("Application error");
    
                $line       = "\n\n ---------------------CP MODULE-------------------------------- \n\n ";
                $message    = $line . $this->view->message . "\n";
                $message    .= $errors->exception . "\n";
                $message    .= $errors->exception->getTraceAsString() . $line;
                
                
                Zend_Registry::get('logger')->err($message);
                
                break;
        }
        
        $cpauth = Zend_Auth::getInstance();
        $userData   = array();
        if ($cpauth->getIdentity()) {
            $user       =   new Cpbo_Model_UserMeta();
            $user->setEntityId($cpauth->getIdentity()->id);
            $userData   = $user->getAll(); 
        }
        
        $message        =   print_r($userData, true) . '<br><br>' .
                            $errors->exception->getMessage() . ' <br><br>' .
                            $errors->exception->getTraceAsString() . ' <br><br>' . 
                            var_export($errors->request->getParams(), true) . ' <br><br>' .
                            print_r($_SERVER, true);
        
        $errSession     = new Zend_Session_Namespace("errors");
        $errSession->lastError  = $message;
        
        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->crit($this->view->message, $errors->exception);
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request   = $errors->request;
    }

    
    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasPluginResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }


}

