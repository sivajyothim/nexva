<?php

class Partner_NexvaController extends Nexva_Controller_Action_Partner_MasterController {

    public function init() {
        parent::init();
    }

  
    //Free apps
    public function indexAction() 
    {
        $chap = new Zend_Session_Namespace('partner');
        $this->view->chap_id = $chap->id;

        $productsModel = new Model_Product();
        $chapProducts = new Partner_Model_ChapProducts();

        if ($this->getDeviceId()) {

            $products = $chapProducts->getFreeProductsByDevice($this->_chapId, $this->getDeviceId(), $this->_grade);
        } else {

            $products = $chapProducts->getNexvaProducts($this->_chapId, $this->_grade);
        }

        $paginator = Zend_Paginator::factory($products);
        $paginator->setItemCountPerPage($this->_productsPerPage);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

        $productsDisplay = array();

        if (!is_null($products)) {
            foreach ($paginator as $row) {
                $productsDisplay[] = $productsModel->getProductDetailsById($row->product_id,FALSE,$this->_chapLanguageId,NULL,$this->_chapId);
            }
        }


        if ($this->_request->device_id) {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/nexva/page/' . $categoryId . $this->_request->device_id;
        } else {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/nexva/index/page/';
        }


        $this->view->products = $productsDisplay;
        $this->view->paginator = $paginator;

        $this->view->pageName = ' App Wall';
    }

   

}