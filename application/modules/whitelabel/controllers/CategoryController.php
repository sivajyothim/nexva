<?php
class Whitelabel_CategoryController extends Nexva_Controller_Action_Whitelabel_MasterController {
    
    public function viewAction() {
        $id     = $this->_getParam('id');
        
        /**
         * @var Whitelabel_Model_Category
         */
        $categoryModel  = Nexva_Factory_Whitelabel_Model::getModel('Category', $this->_getEnvironmentOptions());
        $productModel   = Nexva_Factory_Whitelabel_Model::getModel('Product', $this->_getEnvironmentOptions());
        $categoryModel->setProduct($productModel);//resource injected product model
         
        $category       = $categoryModel->find($id);
        if (!isset($category[0])) {
            $this->_addErrorMessage('Category does not exist');
            $this->_redirect('/');
            exit();
        }
        
        $defaultCategoryModel       = new Model_Category();
        $breadcrumb                 = $defaultCategoryModel->getCategoryBreadcrumb($id);
        $this->view->breadcrumb     = $breadcrumb;
        
        $page       = $this->_getParam('page', 1);
        
        $this->view->category       = $category[0];
        $this->view->products       = $categoryModel->getProductsForCategory($id, $page, 20);
        
        $paging = array(
            'count'     => $categoryModel->getProductsCountForCategory($id),
            'page'      => $page,
            'limit'     => 20,
            'baseUrl'   => "/category/view/id/{$id}"
        );
        $this->view->paging = $paging;
    }

    public function setPlatformAction() {
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();            
        
        $id             = $this->_getParam('id');
        $platformModel  = new Model_Platform();
        $platform       = $platformModel->fetchRow('id = ' . $id);
        if ($platform) {
            $whitelabelNs               = new Zend_Session_Namespace('whitelabel');
            $whitelabelNs->platform     = (object) $platform->toArray();     
        }
        //$this->_redirect($this->_request->getHeader('referer'));
        $this->_redirect('/');
    }
}