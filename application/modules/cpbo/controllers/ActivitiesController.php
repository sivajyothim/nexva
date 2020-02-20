<?php

class cpbo_ActivitiesController extends Nexva_Controller_Action_Cp_MasterController {

  function preDispatch() {
    $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/facebox/facebox.css');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/facebox/facebox.js');
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
    $this->view->title = "Activities";
    $productLog = new Model_ProductLog();

    $logs = $productLog->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
            ->setIntegrityCheck(false)
            ->where("product_log.user_id = ?", Zend_Auth::getInstance()->getIdentity()->id)
            ->join('products', "product_id=products.id", array("name"))
            ->query()
            ->fetchAll();
    $rowCount = count($logs);
    if (0 == $rowCount) {
      $this->view->empty_msg = "No activities are found.";
    }
    $paginater = Zend_Paginator::factory($logs);
    $paginater->setItemCountPerPage(10);
    $paginater->setCurrentPageNumber($this->_request->getParam('page', 1));
    $this->view->logs = $paginater;
  }

  function viewAction() {

  }

  function moreAction() {

    $productLog = new Model_ProductLog();

    $logs = $productLog->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
            ->setIntegrityCheck(false)
            ->where("product_log.user_id = ?", Zend_Auth::getInstance()->getIdentity()->id)
            ->where("product_log.product_id = ?", $this->_request->log)
            ->join('products', 'product_log.product_id = products.id', array("name"))
            ->order(array("date desc"))
            ->query()
            ->fetchAll();

    $paginater = Zend_Paginator::factory($logs);
    $paginater->setItemCountPerPage(10);
    $paginater->setCurrentPageNumber($this->_request->getParam('page', 1));
    $this->view->logs = $paginater;
    $this->view->log_product = current($logs)->name;
  }

}

?>
