<?php

class Default_CpController extends Nexva_Controller_Action_Web_MasterController {

  public function init() {
    //    parent::init();
    /* Initialize action controller here */
      
      
    parent::init();
    $this->view->headTitle()->setSeparator(' - ');
    $this->view->headTitle("neXva.com");
    $this->setLastRequestedUrl();
  }

	public function indexAction() {
		$deviceId = false;
	    $productsModel = new Model_Product();
	    $devices = $this->getSelectedDevices(); 
	    if(is_array($devices))
	    {
	        foreach($devices as  $device)
	      	$deviceId[] = $device['id'];
	        
	    }	else {
	        	
	       	$deviceId = false;
	    }
	    
	    $products = $productsModel->getProductsForSameUserProductList(  $deviceId, $this->_request->cpid);
	   	
	
	    $userMeta = new Model_UserMeta();
	    $compnayName = $userMeta->getAttributeValue($this->_request->cpid, 'COMPANY_NAME');
	    $this->view->cpname = $compnayName;
	    $page = $this->_request->getParam('page', 1);
	    $this->view->page = $this->_request->getParam('page', 1);
	    $paginator = Zend_Paginator::factory($products);
	    $paginator->setItemCountPerPage(20);
	    $paginator->setCurrentPageNumber($page);
	    $this->view->paginator = $paginator;
	    // get payment gateways
	    $paymentGatewaysModel = new Model_PaymentGateway();
	    $paymentGateways = $paymentGatewaysModel->fetchAll('status = 1 AND supports_web = 1');
	    $this->view->paymentGateways = $paymentGateways;
	    $products = array();
	
	    if (!is_null($paginator)) {
	    	
	   
	
	      foreach ($paginator as $row) {
	      	
	
	        $products[] = $productsModel->getProductDetailsById($row->product_id);
	      }
	    }
	
	    $this->view->products = $products;
	    $this->view->paginator = $paginator;
	
	    $config = Zend_Registry::get("config");
	    $this->view->cpid = $this->_request->cpid;
	    $this->view->language = $this->_request->language;
	    $this->view->lan = $this->_request->lan;
	    $this->view->page = $this->_request->page;
	
	    $this->view->headTitle()->append($compnayName);
	    $this->view->headMeta()->appendName('keywords', 'neXva, nexva.com,' . $compnayName);
	
	    if (!empty($this->_request->cpid) and !empty($this->_request->cpname) and !empty($this->_request->language)) {
	      $this->view->baseUrl = $config->nexva->application->base->url . '/cp/' . $this->_request->cpname . '.' . $this->_request->cpid . '' . $this->_request->language;
	    }
	
	    if (!empty($this->_request->cpid) and !empty($this->_request->cpname) and empty($this->_request->language)) {
	      $this->view->baseUrl = $config->nexva->application->base->url . '/cp/' . $this->_request->cpname . '.' . $this->_request->cpid;
	    }
	
	
	    if (!empty($this->_request->cpid) and empty($this->_request->cpname) and empty($this->_request->language)) {
	      $this->view->baseUrl = $config->nexva->application->base->url . '/cp/' . $this->_request->cpid;
	    }
	
	    if (!empty($this->_request->cpid) and empty($this->_request->cpname) and !empty($this->_request->language)) {
	      $this->view->baseUrl = $config->nexva->application->base->url . '/cp/' . $this->_request->cpid . '' . $this->_request->language;
	    }
	
	    if (!empty($this->_request->cpid)) {
	      $this->view->cpid = $this->_request->cpid;
	    }
  }

}

