<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */

class Admin_MessageController extends Zend_Controller_Action {

    function preDispatch() {
        if( !Zend_Auth::getInstance()->hasIdentity() ) {

            if($this->_request->getActionName() != "login") {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();

            }
            if( $this->_request->getActionName() != "login" )
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/login');
        }
    }

    /* Initialize action controller here */
    public function init() {
        // include Ketchup libs
        $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css');
//        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/ketchup/js/jquery.min.js');
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js');
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages.js');
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js');
        // adding admin JS file
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/js/admin.js');
    }

    public function indexAction() {
        // action body
    }
    
    public function listAction() {
    	
    //	  $this->_helper->layout ()->disableLayout ();
     //   $this->_helper->viewRenderer->setNoRender ( true );
      //  $this->getResponse()->appendBody("sassdasdfsa", 'content');

        
        $userId = $this->_request->id;
        
   
        
        $userMessages = new Model_UserMessage();
        $messages = $userMessages->getAllUserMessage($userId);
      
        $paginator  =  Zend_Paginator::factory($messages);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));
        
        $this->view->paginator = $paginator;
        
        if( count($paginator) == 0) {
            $this->view->isCpMessageListEmpty    =   true;
        }
        

        $this->view->userId = $userId ;
        $this->view->selected_tab = $this->_request->tab;
        
        if($this->_request->isPost())
        {   
        	$check = true;
        	
           if(empty($this->_request->body))    {
                
                $this->view->error = 'Body of the email is required.'; 
                $this->view->subject = $this->_request->subject; 
                $this->view->body = $this->_request->body; 
                $check = false;      
                
            }
        	
            if(empty($this->_request->subject))    {
            	
            	$this->view->error = 'Subject of the email is required.';   
            	$this->view->subject = $this->_request->subject; 
                $this->view->body = $this->_request->body; 
            	$check = false;         	
            }
            
          
        
            if($check)  {
                $userMessages->sendMessage( $this->_request->id, $this->_request->subject, $this->_request->body);
        	    $this->view->message = 'Message is sent';
                
            }
      	
        $this->view->userId = $userId ;
        $this->view->selected_tab = $this->_request->getPost('tab');
            
        	
        }	
        
        
        
       
        
        
    }
    

   
}



