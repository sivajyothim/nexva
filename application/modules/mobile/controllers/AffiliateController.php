<?php

class Mobile_AffiliateController extends Nexva_Controller_Action_Mobile_MasterController {
	
  public function init() {
    parent::init();
    /* Initialize action controller here */
  }

  public function indexAction() {
  	
    //die($this->_request->mobtag);
    // @todo: remember the tag - put it in a session for later use.
    $nexvaWidgetId = Zend_Registry::get("config")->nexva->application->widget->id;
    $mobTag = $this->_request->mobtag;
    $affiliateSession = new Zend_Session_Namespace('affiliate');
    if (!empty($mobTag))
      $affiliateSession->code = $mobTag;
    #
    $this->_redirect("app/show/id/" . $nexvaWidgetId);
  }

  public function mobpartnerAction() {
    $this->indexAction();
  }

  
}