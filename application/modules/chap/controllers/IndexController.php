<?php

/*
 * 
 * 
 * 
 */

class Chap_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }


    public function preDispatch(){
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
    }

    
    public function indexAction()
    {
    	//$this->_helper->layout()->disableLayout();
       // $this->_helper->viewRenderer->setNoRender(true);
        
        

    	
    }
    
	
  
}

