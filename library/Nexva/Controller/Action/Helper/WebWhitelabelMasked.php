<?php
class Nexva_Controller_Action_Helper_WebWhitelabelMasked extends Zend_Controller_Action_Helper_Abstract {
    
    public function preDispatch() {
        /**
         * Here we want to check whether this is a whitelabel url, and if so switch the module. 
         * 
         */
        if ($this->isWhitelabelUrl() && $this->getRequest()->getModuleName() == 'default') {
            $this->getRequest()->setModuleName('whitelabel');
            $this->getRequest()->setDispatched(false);    
        }
    }
    
    private function isWhitelabelUrl() {
        $hostname   = $_SERVER['HTTP_HOST'];
        $themeMeta  = new Model_ThemeMeta();
        
        //match this agaisnt our web urls.
        $theme      = $themeMeta->getThemeByHostName($hostname);
        return ($theme !== false);
    }
    
}