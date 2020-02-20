<?php
class Nexva_Product_Filter_Rule_Cp extends Nexva_Product_Filter_Rule_Abstract {
   
    /**
     * @param Zend_Db_Table_Select $query
     */
    function modifyQueryObject($query = null) {
        $value      = explode(',', $this->ruleRow->value);
        if ($this->ruleRow->type == 'EXCLUSION') {
            $query->where('products.user_id NOT IN (?)', $value);
        } else {
            $query->where('products.user_id IN (?)', $value);
        }
        return $query;
    }
}