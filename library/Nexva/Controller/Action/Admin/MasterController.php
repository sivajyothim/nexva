<?php
/**
 * Master controller for admin side controllers
 *
 * @copyright neXva.com
 * @author John Pereira
 * @date Jan 31, 2011
 */
class Nexva_Controller_Action_Admin_MasterController extends Zend_Controller_Action {
    
    public function preDispatch() {
        
         if( !Zend_Auth::getInstance()->hasIdentity() ) {

            if($this->_request->getActionName() != "login") {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();

            }
            if( $this->_request->getActionName() != "login" )
            $this->_redirect('/user/login');
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
}