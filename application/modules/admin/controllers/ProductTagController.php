<?php
class Admin_ProductTagController extends Nexva_Controller_Action_Admin_MasterController {
    
    public function tagAction() {
        $proId  = $this->_getParam('pro_id');
        //get the build names and show them

        if ($this->_request->isPost() && $this->_getParam('tags')) {
            $tagModel   = new Model_ProductBuildTag();
            foreach ($this->_getParam('tags') as $buildId => $tags) {
                $tagModel->delete('product_build_id = ' . $buildId);
                if (trim($tags)) { 
                    $data   = array();
                    $data['product_build_id']   = $buildId;
                    $data['tags']               = $tagModel->padTag(trim($tags));
                    $tagModel->insert($data);    
                }
            }
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'product-tag/tag/pro_id/' . $proId);
        }
        
        $productModel   = new Model_Product();
        $product        = $productModel->getProductDetailsById($proId);
        
        if (!$product) {
            $this->__addErrorMessage('Invalid product');
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'product/view/tab/tab-all');
        }
        
        $proBuildModel  = new Model_ProductBuild($proId);
        $builds         = $proBuildModel->getBuildsByProductId($proId);
        if (!count($builds)) {
            $this->__addErrorMessage('This product does not have builds');
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'product/view/tab/tab-all');
        }
        
        $buildIds       = array();
        foreach ($builds as $build) {
            $buildIds[] = $build->id;
        }
        
        $tagModel       = new Model_ProductBuildTag();
        $allTags        = $tagModel->getAvailableTags();
        
        $buildTagObjects    = $tagModel->getBuildTagsByBuildId($buildIds); 
        $buildTags          = array();
        foreach ($buildTagObjects as $buildTag) {
            $buildTags[$buildTag->product_build_id] = $tagModel->unPadTag($buildTag->tags);
        } 
        $this->view->buildTags  = $buildTags;
        $this->view->product    = $product;
        $this->view->allTags    = $allTags;
        $this->view->builds     = $builds;
        
    }
}