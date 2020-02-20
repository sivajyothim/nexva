<?php
class Whitelabel_View_Helper_CategoryMenu extends Zend_View_Helper_Abstract {
    
    public function categoryMenu() {
        return $this;
    }
    
    public function getSortedCategories($chapId) {
        $catModel   = new Whitelabel_Model_Category();
        $categories = $catModel->getCategories($chapId);
        
        if (empty($categories)) {
            return array();
        }        
        
        $sorted = array();
        foreach ($categories as $cat) {
            if ($cat->parent_id == 0) {
                $cat->children      = array();
                $sorted[$cat->id]   = $cat;
            } else {
                $sorted[$cat->parent_id]->children[]    = $cat;
            }
        }
        return $sorted;
    }
}