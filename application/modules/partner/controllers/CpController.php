<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 1/9/14
 * Time: 3:38 PM
 * To change this template use File | Settings | File Templates.
 */

class Partner_CpController extends Nexva_Controller_Action_Partner_MasterController {
    public function init() {
        parent::init();
    }

    public function indexAction() {

        $deviceId = false;
        $productsModel = new Model_Product();

        $session = new Zend_Session_Namespace("devices");
        $devices = $session->selectedDevices;

        if(is_array($devices))
        {
            foreach($devices as  $device)
                $deviceId[] = $device['id'];

        }	else {

            $deviceId = false;
        }

        $products = $productsModel->getProductsForSameUserProductList($deviceId, $this->_request->uid);

        $paginator = Zend_Paginator::factory($products);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->_request->page,1);
        $this->view->paginator = $paginator;

        $userMeta = new Model_UserMeta();
        $compnayName = $userMeta->getAttributeValue($this->_request->uid, 'COMPANY_NAME');

        $this->view->pageName = $compnayName;
        $this->view->controllerName = 'vendor';

        $products = array();
        if (!is_null($paginator)) {

            foreach ($paginator as $row) {

                $products[] = $productsModel->getProductDetailsById($row->product_id);
            }
        }
        $this->view->products = $products;
        $this->view->chapId = $this->_chapId;
        $this->view->cpid = $this->_request->uid;

        $this->view->baseUrl = $this->_baseUrl . '/cp/index/page/';

    }
}
?>
