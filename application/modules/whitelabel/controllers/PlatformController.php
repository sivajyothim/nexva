<?php
class Whitelabel_PlatformController extends Nexva_Controller_Action_Whitelabel_MasterController {
    
    public function setAction() {
        $id             = $this->_getParam('id');
        $platformModel  = new Model_Platform();
        $platform       = $platformModel->fetchRow('id = ' . $id);
        if ($platform) {
            $whitelabelNs               = new Zend_Session_Namespace('whitelabel');
            $whitelabelNs->platform     = (object) $platform->toArray();     
        }
        $this->_redirect($this->_request->getHeader('referer'));
    }
    
  
}