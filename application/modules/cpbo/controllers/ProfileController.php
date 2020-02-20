<?php

class cpbo_ProfileController extends Nexva_Controller_Action_Cp_MasterController {

  function preDispatch() {
    $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/facebox/facebox.css');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/facebox/facebox.js');
    $this->view->title = "Profile";
    if (!Zend_Auth::getInstance()->hasIdentity()) {
      $skip_action_names =
          array(
            "login",
            "register",
            "forgotpassword",
            "resetpassword"
      );
      if (!in_array($this->getRequest()->getActionName(), $skip_action_names)) {
        $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $requestUri;
        $session->lock();
      }
      if (!in_array($this->getRequest()->getActionName(), $skip_action_names)) {
        $this->_redirect('/user/login');
      }
    }
  }

  function init() {

  }

  function indexAction() {

  }

  function viewAction() {

  }

  function saveAction() {

    if ($this->getRequest()->isPost()) {

      die('hi');
    }
  }

}

