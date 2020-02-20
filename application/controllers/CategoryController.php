<?php

class Default_CategoryController extends Nexva_Controller_Action_Web_MasterController {

  public function init() {
    /* Initialize action controller here */
    parent::init();
    $this->setLastRequestedUrl();
  }

  public function indexAction() {
    if (is_null($this->_request->id))
      $this->_redirect('/');

    $devices = $this->getSelectedDevices();

    // get payment gateways
    $paymentGatewaysModel = new Model_PaymentGateway();
    $paymentGateways = $paymentGatewaysModel->fetchAll('status = 1 AND supports_web = 1');
    $this->view->paymentGateways = $paymentGateways;
    
    if (is_null($this->_request->device_id)) { // try to select the first device selected (if there are devices selected)
      if (count($devices) > 0) {
        $device = current($devices);
        $this->_request->setParam('device_id', $device['id']);
      }
    }
    
    


    $categoryModel = new Model_Category();
    $productsModel = new Model_Product();
    
    $categoryData               = $categoryModel->getCategoryInfo($this->_request->id);
    $this->view->categoryInfo   = $categoryData ;
    $this->view->categoryBreadcrumbs = $categoryModel->getCategoryBreadcrumb($this->_request->id);

    $deviceId = $this->getRequest()->getParam('device_id', null);
    $products = $productsModel->getProductsForCategoryProductList($deviceId, $this->_request->id); //@todo: page this!

    $paginator = Zend_Paginator::factory($products);
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

    $config = Zend_Registry::get("config");

    if ($this->_request->device_id) {
      $this->view->baseUrl = $config->nexva->application->base->url . '/category/' . $this->_request->slug . '/' . $this->_request->id . '/' . $this->_request->device_id . '/page/';
    } else {
      $this->view->baseUrl = $config->nexva->application->base->url . '/category/' . $this->_request->slug . '/' . $this->_request->id . '/page/';
    }

    $productsDisplay = array();

    if (!is_null($products)) {
      foreach ($paginator as $row) {
        $productsDisplay[] = $productsModel->getProductDetailsById($row->product_id);
      }
    }

    $this->view->products = $productsDisplay;
    $this->view->paginator = $paginator;


    if (!isset($devices[$this->_request->device_id])) {
      //We're being requested to show products for a device thats not actually selected.
      //This could happen when a url is bookmarked or pasted into the browser.
      //We will automagically set the device for the user now:
      //@todo:
    }

    //echo "index action of the category controller";
    //Zend_Debug::dump($this->_request);
    
    $this->view->headTitle()->setSeparator(' - ');
    $this->view->headTitle($categoryData->name);
    $this->view->headTitle("neXva.com");
    
    $this->view->categoryId = $this->_request->id;

  }

}

