<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Cpbo_AnnouncementController extends Nexva_Controller_Action_Cp_MasterController {

  public function preDispatch() {

    $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/datepicker/css/ui.daterangepicker.css');
    $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/datepicker/css/redmond/jquery-ui-1.7.1.custom.css');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/datepicker/js/jquery-ui-1.7.1.custom.min.js');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/datepicker/js/daterangepicker.jQuery_announcements.js');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/datepicker/js/startdate_announcement.js');
    $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/facebox/facebox.css');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/facebox/facebox.js');
//        if( !Zend_Auth::getInstance()->hasIdentity() ) {
//
//            if($this->_request->getActionName() != "login") {
//                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
//                $session = new Zend_Session_Namespace('lastRequest');
//                $session->lastRequestUri = $requestUri;
//                $session->lock();
//
//            }
//            if( $this->_request->getActionName() != "login" )
//                $this->_redirect('/user/login');
//        }
  }

  function indexAction() {
    $this->_redirect('/announcement/create');
  }

  function viewmoreAction() {
    $this->_helper->layout()->disableLayout();

    $announcementModle = new Model_Announcement();
    $this->view->announcement = $announcementModle->fetchRow("id  =" . $this->_request->id)->toArray();
  }

}

?>
