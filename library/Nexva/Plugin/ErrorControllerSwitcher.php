<?php

/**
 * This Plugin is used to switch error controller based on the module 
 */
class Nexva_Plugin_ErrorControllerSwitcher extends Zend_Controller_Plugin_Abstract 
{
    public function routeShutdown (Zend_Controller_Request_Abstract $request)
    {
        $front = Zend_Controller_Front::getInstance();
        
        if (!($front->getPlugin('Zend_Controller_Plugin_ErrorHandler') instanceof Zend_Controller_Plugin_ErrorHandler)) 
        {
            return;
        }
        $error = $front->getPlugin('Zend_Controller_Plugin_ErrorHandler');
        $testRequest = new Zend_Controller_Request_Http();
        
        $testRequest->setModuleName($request->getModuleName())
                    ->setControllerName($error->getErrorHandlerController())
                    ->setActionName($error->getErrorHandlerAction());
        
        if ($front->getDispatcher()->isDispatchable($testRequest)) 
        {
            $error->setErrorHandlerModule($request->getModuleName());
        }
    }
}