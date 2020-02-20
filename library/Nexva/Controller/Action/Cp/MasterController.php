<?php
class Nexva_Controller_Action_Cp_MasterController extends Zend_Controller_Action {
    
    function preDispatch() {
       


        
        if (! Zend_Auth::getInstance ()->hasIdentity ()) {
            $skip_action_names = array ('login', 'register','registern', 'forgotpassword', 'resetpassword', 'claim' );
            if (! in_array ( $this->getRequest ()->getActionName (), $skip_action_names )) {
                $requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
                $session = new Zend_Session_Namespace ( 'lastRequest' );
                $session->lastRequestUri = $requestUri;
                $session->lock ();
                $this->_redirect ( '/user/login' );
            } else {
                if ($this->getRequest ()->getActionName () == 'claim') {
                    $requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
                    $session = new Zend_Session_Namespace ( 'lastRequest' );
                    $session->lastRequestUri = $requestUri;
                    $session->lock ();
                    $this->_redirect ( '/user/login/ref/appclaim' );
                } else
                $this->_redirect ( '/user/login' );

            }
        }
        
        //message handling
        $this->view->flashMessages  = $this->_helper->FlashMessenger->getMessages();
        $this->_helper->FlashMessenger->setNamespace('ERROR');
        $this->view->flashErrors   = $this->_helper->FlashMessenger->getMessages();
               
    }
   
    
    function postDispatch() {
        
      $session = new Zend_Session_Namespace('chap');    
      if(isset($session->chap->id))	{
      $chapId = $session->chap->id;   
      }
      //$chapId = 28549;
      if(!empty($chapId) && isset($chapId))
      {
        $themeMetaModel = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($chapId);
        
        $this->view->cpTitle = ($themeMetaModel->CP_PANEL_TITLE) ? $themeMetaModel->CP_PANEL_TITLE : '';
        $this->view->cpHeaderColour = ($themeMetaModel->CP_HEADER_COLOUR) ? $themeMetaModel->CP_HEADER_COLOUR : '';
        $this->view->cpLogo = ($themeMetaModel->CP_HEADER_LOGO) ? $themeMetaModel->CP_HEADER_LOGO : '';
        $this->view->cpFavicon = ($themeMetaModel->WHITELABLE_SITE_FAVICON) ? $themeMetaModel->WHITELABLE_SITE_FAVICON : '';
        $this->view->cpFooter = ($themeMetaModel->CP_FOOTER) ? $themeMetaModel->CP_FOOTER : '';
        $this->view->customCss = ($themeMetaModel->CP_CUSTOM_CSS) ? $themeMetaModel->CP_CUSTOM_CSS : '';
        $this->view->folderPath = ($themeMetaModel->WHITELABLE_THEME_NAME) ? $themeMetaModel->WHITELABLE_THEME_NAME : '';
      }
        //get messages for CP
        $announceModel  = new Model_Announcement();
        $messageCount   = $announceModel->getUnreadMessageCount($this->_getCpId());
        
        $this->view->unreadMessageCount     = $messageCount; 
    }

    protected function _getCpId() {
        $cpauth = Zend_Auth::getInstance();
        $identity   = $cpauth->getIdentity();
        if (isset($identity->id)) {
            return $cpauth->getIdentity()->id;
        }
        return null;
    }
    
    protected function _getCp() {
        $cpauth = Zend_Auth::getInstance();
        $identity   = $cpauth->getIdentity();
        if (empty($identity)) {
            return null;
        }
        return $cpauth->getIdentity();
    }

    protected function _getCHAPid()
    {
        $cpauth = Zend_Auth::getInstance();
        $identity   = $cpauth->getIdentity();
        if (isset($identity->id))
        {
            $userModel = new Cpbo_Model_User();
            $userDetails =  $userModel->getUserDetails($identity->id);
            return $userDetails[0]->chap_id;
        }
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