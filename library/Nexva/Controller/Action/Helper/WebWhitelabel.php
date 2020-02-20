<?php
class Nexva_Controller_Action_Helper_WebWhitelabel extends Zend_Controller_Action_Helper_Abstract {
    
    private $viewObject   = null;
    
    public function init() {
        $this->viewObject     = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $this->viewObject->initView(); // make sure the view object is initialized
    }
    
    public function initializeChap() {
    
        $theme      = $this->getChap();
        if (!$theme) {
            //if you get nothing, redirect them to our main site
            $this->getActionController()->getHelper('Redirector')
                ->gotoUrl('http://' . Zend_Registry::get('config')->nexva->application->base->url);
            exit;
        }   
        //convert it to an object for easy use
        $theme  = (object) $theme;
        $this->getActionController()->setChap($theme);
        $this->getActionController()->setChapId($theme->WHITELABLE_USER_ID);
        
        
        
        $settings       = new Nexva_Whitelabel_Setting();
        $options        = (isset($theme->WHITELABLE_SETTINGS)) ? json_decode($theme->WHITELABLE_SETTINGS) : new stdClass();
        $settings->setOptions($options);
        $this->viewObject->view->SETTINGS       = $settings;
        $this->viewObject->view->CHAP           = $theme;
        $this->viewObject->view->CHAP_ID        = $theme->WHITELABLE_USER_ID;
    }
    
    public function initializePlatforms() {
        $platformModel  = new Model_Platform();
        $platforms      = $platformModel->getAllPlatforms();
        $this->viewObject->view->PLATFORMS  = $platforms;
        
        $whitelabelNs               = new Zend_Session_Namespace('whitelabel');
        
        if (isset($whitelabelNs->platform->id)) {
            $this->viewObject->view->PLATFORM       = $whitelabelNs->platform;    
            $this->viewObject->view->PLATFORM_ID    = $whitelabelNs->platform->id;
        } else {
            $this->viewObject->view->PLATFORM       = null;    
            $this->viewObject->view->PLATFORM_ID    = null;
        }
        
             
    }
    
    public function getChap() {
        $hostname   = $_SERVER['SERVER_NAME'];
        $themeMeta  = new Model_ThemeMeta();
        
            
        //match this agaisnt our web urls.
        return $themeMeta->getThemeByHostName($hostname);
    }
}