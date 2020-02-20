<?php
class Nexva_Product_Filter_Rule_Category extends Nexva_Product_Filter_Rule_Abstract {
    
    
    /**
     * @param Zend_Db_Table_Select $query
     */
    function modifyQueryObject($query = null) {
        $query->joinLeft('product_categories', 'product_categories.product_id = products.id', array());
        $value      = explode(',', $this->ruleRow->value);
        if ($this->ruleRow->type == 'EXCLUSION') {
            $query->where('product_categories.category_id NOT IN(?)', $value);    
        } else {
            $query->where('product_categories.category_id IN (?)', $value);
        }
        
        return $query;
    }
}