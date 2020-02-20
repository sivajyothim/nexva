<?php
class Nexva_Product_Filter_Rule_Price extends Nexva_Product_Filter_Rule_Abstract {
   
    /**
     * @param Zend_Db_Table_Select $query
     */
    function modifyQueryObject($query = null) {
        //The price component is defined like this 
        // OPERATOR SPACE VALUE - E.G. = > 3.00 // price larger than 3 dollars
        
        //get the sign component first
        $validOperators = array('=', '>', '<');
        $operator       = substr(trim($this->ruleRow->value), 0, 1);
        if (!in_array($operator, $validOperators)) {
            return $query;
        }
        $price      = substr($this->ruleRow->value, 1);
        
        $query->where('products.price ' . $operator . '?', $price);
        
        return $query;
    }
}