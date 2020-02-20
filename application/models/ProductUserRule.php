<?php
class Model_ProductUserRule extends Nexva_Db_Model_MasterModel {
        
    protected  $_id     = 'id';
    protected  $_name   = 'products_users_rules';
    
    /**
     * Gets the rules  
     * @param $userid
     */
    public function getRules($userId = null) {
        $query      = $this->select();
        $query->where('user_id = ?', $userId);  
        $rules      = $this->fetchAll($query);
        if (!empty($rules)) {
            return $rules;
        }
        return array();
    }
    
    public function parseRules() {
        
    }
    
    /**
     * 
     * Applies the filters to the query given a chap and a query
     * @param $userid User ID of the CHAP in question
     * @param $query The query that the filter must be applied to 
     * @param $opts an optional array of options that will be used later
     */
    public function applyRules($userId, $query, $opts = array()) {
        $appFilter          = new Nexva_Product_Filter_Filter();
        $query              = $appFilter->applyRules($query, $this->getRules($userId));
        
        return $query;
    }
}