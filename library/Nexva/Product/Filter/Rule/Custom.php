<?php
class Nexva_Product_Filter_Rule_Custom extends Nexva_Product_Filter_Rule_Abstract {
    
    /**
     * @param Zend_Db_Table_Select $query
     */
    function modifyQueryObject($query = null) {
        /*$db         = Zend_Registry::get('db');
        $subquery   = $db->select(false)
                        ->from('products_users_filter', array('product_id'))
                        ->where('rule_id = ?', $this->ruleRow->id); */
        
        $value      = explode(',', $this->ruleRow->value);
        if ($this->ruleRow->type == 'INCLUSION') {
            $query->where('products.id IN (?)', $value);
        } else {
            $query->where('products.id NOT IN (?)', $value);
        }
        
        return $query;
    }
}