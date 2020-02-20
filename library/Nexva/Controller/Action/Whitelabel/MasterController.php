<?php
class Nexva_Controller_Action_Whitelabel_MasterController extends Zend_Controller_Action {
    
    /**
     * This is automatically injected by the WebWhitelabel plugin
     * Contains all the meta data related to a chap
     * @var stdClass
     */
    protected  $_chap   = null;
    protected  $_chapId = null; //user ID of the currently loaded chap
    
    public function init() {
        /**
         * Does the chap initializations. If there is a valid chap, it will load 
         * the chap into the controller. Otherwise it will redirect to the main site
         */
        $this->_helper->getHelper('WebWhitelabel')->initializeChap();
        $this->_helper->getHelper('WebWhitelabel')->initializePlatforms();
    }
    
    public function preDispatch() {
        $this->view->flashMessages  = $this->_helper->FlashMessenger->getMessages();
    }

    /**
     * Returns the environment options that the model factory needs 
     */
    protected function _getEnvironmentOptions() {
        
        $whitelabelNs   = new Zend_Session_Namespace('whitelabel');
        $platformId     = isset($whitelabelNs->platform->id) ? $whitelabelNs->platform->id : null; 
        
        $opts          = array(
           'chapId'        => $this->getChapId(),
           'chap'          => $this->getChap(),
           'platformId'    => $platformId
        );
        return $opts;
    }
    
    /**
     * Message functions
     */
    protected function _addMessage($message = '') {
        $this->_helper->FlashMessenger->resetNamespace();
        $this->_helper->FlashMessenger->addMessage($message);
    }
    
    protected function _addNoticeMessage($message = '') {
        $this->_helper->FlashMessenger->setNamespace('NOTICE');
        $this->_helper->FlashMessenger->addMessage($message);
        $this->_helper->FlashMessenger->resetNamespace();
    }

    protected function _addErrorMessage($message = '') {
        $this->_helper->FlashMessenger->setNamespace('ERROR');
        $this->_helper->FlashMessenger->addMessage($message);
        $this->_helper->FlashMessenger->resetNamespace();
        
    }    
    
    /**
     * Returns the owning user ID of the current chap site
     */
    public function getChapId() {
        return $this->_chapId;
    }
    
    public function setChapId($id) {
        $this->_chapId  = $id;
    }
    
    /**
     * Returns the meta data for the current chap
     */
    public function getChap() {
        return $this->_chap;
    }
    
    public function setChap($chap) {
        $this->_chap    = $chap;
    }
    
  
}