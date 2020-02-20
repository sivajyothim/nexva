<?php
class Nexva_Product_Filter_Rule_Platform extends Nexva_Product_Filter_Rule_Abstract {
    
    /**
     * @param Zend_Db_Table_Select $query
     */
    function modifyQueryObject($query = null) {
        if ($this->ruleRow->type == 'EXCLUSION') {
            $query->where('product_builds.platform_id <> ?', $this->ruleRow->value);    
        } else {
            $query->where('product_builds.platform_id  = ?', $this->ruleRow->value);
        }
        
        return $query;
    }
}