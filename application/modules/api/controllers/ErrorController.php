<?php 

class Api_ErrorController extends Zend_Controller_Action
{

    public function init() {
        $this->_helper->layout ()->disableLayout ();
        $this->_helper->viewRenderer->setNoRender (true);
    }


    public function errorAction() {

        $errors = $this->_getParam('error_handler');

        if($_SERVER['REMOTE_ADDR'] == '220.247.236.99' || $_SERVER['REMOTE_ADDR'] == '106.185.32.199'){
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            print_r($this->getResponse());
        }
        
        switch ($errors->type) {

            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);

                $response = array (
                  "status" => 500,
                  "message" => "API service error. Your request has been logged. Please try again later."
                );
               
                break;
        }

        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->crit($this->view->message, $errors->exception);
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $response['exception'] = "<pre>". nl2br($errors->exception)."</pre>";
            $response['request'] = $errors->request;            
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');        
        header('HTTP/1.0 500 Application Error');

        echo json_encode($response);
        
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

