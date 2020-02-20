<?php
/**
 * Master controller for chap side controllers
 *
 * @copyright neXva.com
 * @author John Pereira
 * @date 1st November 2011
 */
class Nexva_Controller_Action_Chap_MasterController extends Zend_Controller_Action {
    
    public function preDispatch() {
        
         if( !Zend_Auth::getInstance()->hasIdentity() ) {

            $skip_action_names = array ('login', 'register', 'forgotpassword', 'resetpassword', 'claim', 'impersonate');
            if (! in_array ( $this->getRequest()->getActionName (), $skip_action_names)) {
                $requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
                $session = new Zend_Session_Namespace ( 'lastRequest' );
                $session->lastRequestUri = $requestUri;
                $session->lock ();
                $this->_redirect ( '/user/login' );
            } 
        }
        
        //message handling
        $this->view->flashMessages  = $this->_helper->FlashMessenger->getMessages();
        $this->_helper->FlashMessenger->setNamespace('ERROR');
        $this->view->flashErrors   = $this->_helper->FlashMessenger->getMessages();
    }
    
    protected function __addMessage($message = '') {
        $this->_helper->FlashMessenger->resetNamespace();
        $this->_helper->FlashMessenger->addMessage($message);
    }

    protected function __addErrorMessage($message = '') {
        $this->_helper->FlashMessenger->setNamespace('ERROR');
        $this->_helper->FlashMessenger->addMessage($message);
        $this->_helper->FlashMessenger->resetNamespace();
    }
    
    protected function _getChapSite() {
        $chapSession    = new Zend_Session_Namespace('chap_whitelabel');
        if (empty($chapSession->themeMeta)) {
            $themeMeta  = new Model_ThemeMeta();
            $themeMeta->setEntityId(Zend_Auth::getInstance()->getIdentity()->id);
            $themeData  = $themeMeta->getAll();
            $chapSession->themeMeta = $themeData;
        } 
        return $chapSession->themeMeta;
    }
    
    protected function _getUserId() {
        return Zend_Auth::getInstance()->getIdentity()->id;
    }
}