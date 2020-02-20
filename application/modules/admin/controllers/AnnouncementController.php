<?php

error_reporting(E_ALL);
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Admin_AnnouncementController extends Zend_Controller_Action {

  public function preDispatch() {

    if (!Zend_Auth::getInstance()->hasIdentity()) {

      if ($this->_request->getActionName() != "login") {
        $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $requestUri;
        $session->lock();
      }
      if ($this->_request->getActionName() != "login")
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/login');
    }
  }

  function indexAction() {
    $this->_redirect(ADMIN_PROJECT_BASEPATH.'announcement/create');
  }

  
    function createAction() {

        $id         = $this->_getParam('id', false);
        $chap_id    = $this->_getParam('cp_chap', false);
        //echo $chap_id;die();
        $errors     = array();
        $errorMessage   = '';
          
        $announcementModel  = new Model_Announcement();

        $userModel = new Model_User();
        $chaps = $userModel->getCHAPs();

        $this->view->chaps = $chaps;

        if ($this->getRequest()->isPost()) {
            $required   = array('title', 'message');
            $fields     = $announcementModel->getPopulatedArray($this->_getAllParams());
            //Zend_Debug::dump($fields);echo '<br/><br/><br/><br/>';
            foreach ($required as $field) {
                if (empty($fields[$field])) {
                    $errors[]   = ucfirst($field) . ' is required';
                }
            }
            if (empty($errors)) {
                unset($fields['id']);
                if ($id) {
                    $announcementModel->update($fields, "id = {$id}");
                } else {
                    $fields['created']  = date('Y-m-d H:i:s');
                    //Zend_Debug::dump($fields);die();
                    $id = $announcementModel->insert($fields);
                }
                if ($fields['status'] == 1) {
                    $temp =  $announcementModel->addUsers($id,$chap_id); //adding the users only if the announcement is approved
                    //Zend_Debug::dump($temp);die();
                }
                
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'announcement/list/');
            } else {
                $errorMessage   = 'The following errors prevented this announcement from being saved. <br>' . implode('<br>', $errors); 
            }
        }
        
        if ($id) {
            $announcement       = $announcementModel->fetchRow("id = {$id}");
        } else {
            $announcement       = $announcementModel->getPopulatedObject($this->_getAllParams());
            //Zend_Debug::dump($announcement);die();
        }
        $this->view->error          = $errorMessage;
        $this->view->announcement   = $announcement;
    }

  function deleteAction() {
    $this->_helper->viewRenderer->setNoRender(true);
    $announcementModel = new Model_Announcement();
    $announcementModel->delete("id =" . $this->_request->id);
    $this->_redirect(ADMIN_PROJECT_BASEPATH.'announcement/list');
  }

  function listAction() {
    $announcementModel = new Model_Announcement();
    $announcements = $announcementModel->fetchAll();

    if ($announcements->current() == NULL or count($announcements->current()) <= 0) {
      $this->view->empty_msg = "Announcement list is empty <a href='/announcement/create/'>Create one</a>";
    }

    $pagination = Zend_Paginator::factory($announcements);
    $pagination->setCurrentPageNumber($this->_request->getParam('page', 0));
    $pagination->setItemCountPerPage(10);

    $this->view->announcements = $pagination;
  }

}

?>
