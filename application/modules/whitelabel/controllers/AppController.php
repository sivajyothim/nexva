<?php


class Whitelabel_AppController extends Nexva_Controller_Action_Whitelabel_MasterController {
    
    public function viewAction() {
        $id     = $this->_getParam('id');
        
        /**
         * @var $productModel Whitelabel_Model_Product
         */
        $productModel   = Nexva_Factory_Whitelabel_Model::getModel('Product', $this->_getEnvironmentOptions());
        $product        = $productModel->getProduct($id);
        $this->view->appExists = FALSE;
        
        if (!$product) {
            $this->_redirect('/');
            exit;
        }
        else if($product->deleted == 1)
        {
            $this->view->appExists = FALSE;
        }
        else
        {
            $this->view->appExists = TRUE;      
            //get the platform info
            $productBuildModel  = new Model_ProductBuild();
            $platforms          = $productBuildModel->getPlatformsForProduct($product->id);
            $this->view->platforms  = $platforms;

            //get the category breadcrumb
            $productCategoryModel       = new Model_ProductCategories();
            $categoryId                 = $productCategoryModel->selectedSubCategory($product->id);
            $categoryModel              = new Model_Category();
            $breadcrumb                 = $categoryModel->getCategoryBreadcrumb($categoryId);
            $this->view->breadcrumb     = $breadcrumb;

            //page title
            $this->view->PAGE_TITLE  = $product->name . ' | ' . $product->userMeta->COMPANY_NAME;

            $this->view->product    = $product; 
        }    
            
        
    }
    
    public function recentAction() {
        $productModel   = Nexva_Factory_Whitelabel_Model::getModel('Product', $this->_getEnvironmentOptions());
        
        $page                   = $this->_getParam('page', 1);
        
        $this->view->products   = $productModel->getAllProducts($page, 20);
        
        $paging = array(
            'count'     => $productModel->getAllProductsCount(),
            'page'      => $page,
            'limit'     => 20,
            'baseUrl'   => "/app/recent"
        );
        
        $this->view->paging = $paging;
    }
}