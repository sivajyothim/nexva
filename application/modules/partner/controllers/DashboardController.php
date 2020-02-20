<?php

class Partner_DashboardController extends Nexva_Controller_Action_Partner_MasterController {

    public function init()
    {
         //Check the actions and authonticate for allowed users
         if (!Zend_Auth::getInstance()->hasIdentity()) 
         {
            $notAllowedActions = array ('index');
            
            if (in_array ( $this->getRequest()->getActionName(), $notAllowedActions )) 
            {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace( 'lastRequest' );
                $session->lastRequestUri = $requestUri;
                $session->lock();
                $this->_redirect ( '/info/login' );
            }
        }
        
        $this->view->headLink()->appendStylesheet('/partner/default/assets/css/ticketing_styles.css');
        
        parent::init();
    }

    public function indexAction() 
    {        
        $this->view->userId = $this->_userId;
    }
}