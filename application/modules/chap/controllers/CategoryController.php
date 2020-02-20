<?php
class Chap_CategoryController extends Nexva_Controller_Action_Chap_MasterController {
    
    function customizeAction() {
        $catModel       = new Chap_Model_Category();
        
        if ($this->_request->isPost() && $this->_getParam('categories')) {
            if ($this->_getParam('mode', 'hide') == 'hide') { 
                foreach ($this->_getParam('categories') as $category) {
                    if (trim($category)) {
                        $catModel->disableCategoryForChap($category, $this->_getUserId());    
                    }
                }
            } else {
                foreach ($this->_getParam('categories') as $category) {
                    if (trim($category)) {
                        $catModel->enableCategoryForChap($category, $this->_getUserId());    
                    }
                }
            }
            $this->_redirect('/category/customize');
        }
        
        
        
        $enabledCats    = $catModel->fetchAll('status = 1', 'parent_id ASC');
        $disabledCats   = $catModel->getDisabledCategoriesForChap($this->_getUserId());
        
        //format the data
        $enabledCatList = array();
        foreach ($enabledCats as $cat) { 
            if (!isset($enabledCatList[$cat->parent_id])){
                $enabledCatList[$cat->parent_id]    = array();
            }
            $enabledCatList[$cat->parent_id][$cat->id]  = $cat;
        }
        
        $disabledCatList    = array();
        foreach ($disabledCats as $cat) {
            if (!isset($disabledCatList[$cat->parent_id])){
                $disabledCatList[$cat->parent_id]    = array();
            }
            $disabledCatList[$cat->parent_id][$cat->id]  = $cat;
        }
        
        $this->view->disabledCatList            = $disabledCatList;        
        $this->view->enabledCatList             = $enabledCatList;
        $this->view->disabledCategories         = $disabledCats;
    } 
}