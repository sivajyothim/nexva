<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 5/23/13
 * Time: 5:33 AM
 * To change this template use File | Settings | File Templates.
 */

class Default_ItemsController extends Nexva_Controller_Action_Web_MasterController{

    /*public function init() {

        parent::init();
        $this->setLastRequestedUrl();
    }*/

    public function indexAction(){
        //$this->_redirect ( '/items/newest' );
    }

    public function newestAction(){

        // get payment gateways
        $paymentGatewaysModel = new Model_PaymentGateway();
        $paymentGateways = $paymentGatewaysModel->fetchAll('status = 1 AND supports_web = 1');
        $this->view->paymentGateways = $paymentGateways;

        $devices        = $this->getSelectedDeviceIds();
        $productModel = new Model_Product();
        $newest = $productModel->getNewest($devices,1,30);

        $products = Zend_Paginator::factory($newest);
        $products->setCurrentPageNumber($this->_getParam('page'),1);
        $products->setItemCountPerPage(10);
        //Zend_Debug::dump($products);die();

        $this->view->products = $products;

    }

    public function newestgamesAction(){

        $paymentGatewaysModel = new Model_PaymentGateway();
        $paymentGateways = $paymentGatewaysModel->fetchAll('status = 1 AND supports_web = 1');
        $this->view->paymentGateways = $paymentGateways;

        //$devices        = $this->getSelectedDeviceIds();
        $productModel = new Model_Product();
        //$newest = $productModel->getNewest($devices,1,23);
        $newest = $productModel->getNewestGame(7,30);

        //$sql = $productModel->getNewestGameSQL(7,23);

        $products = Zend_Paginator::factory($newest);
        $products->setCurrentPageNumber($this->_getParam('page'),1);
        $products->setItemCountPerPage(10);
        //Zend_Debug::dump($products);die();

        $this->view->products = $products;
        //$this->view->pagination = $pagination;
    }

    public function androidAction()
    {
        // get payment gateways
        $paymentGatewaysModel = new Model_PaymentGateway();
        $paymentGateways = $paymentGatewaysModel->fetchAll('status = 1 AND supports_web = 1');
        $this->view->paymentGateways = $paymentGateways;

        $productModel   = new Model_Product();
        $androids = $productModel->getAndroidApps(100);
        //Zend_Debug::dump($androids);die();

        $products = Zend_Paginator::factory($androids);
        $products->setCurrentPageNumber($this->_getParam('page'),1);
        $products->setItemCountPerPage(10);

        $this->view->products = $products;
    }


}