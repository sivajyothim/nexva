<?php
/**
 * 
 * Check If device is detected properly, otherwise redirect to device not found page
 *
 */
class Nexva_Controller_Action_Helper_CheckDevice extends Zend_Controller_Action_Helper_Abstract {

    public function CheckDevice($deviceId)
    {        
         $request = $this->getRequest();
        
        if($request->getRequestUri() != '/index/device-not-found') 
        {
            if($deviceId == 1)
            {
                $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'redirector' );
                $redirector->gotoUrl ('http://'. $_SERVER ['HTTP_HOST'].'/index/device-not-found');
            }
        }
        
    }
}