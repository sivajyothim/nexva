<?php
class Admin_Model_ProductUserRules extends Nexva_Db_Model_MasterModel {
    protected  $_id     = 'id';
    protected  $_name   = 'products_users_rules';
    
    /**
     * Returns the rules for a given chap id
     * @param $chapId
     */
    function getRules($chapId = null) {
        if (!$chapId) {
            return array();
        }
        $query  = $this->select()->where('user_id = ?', $chapId);
        $results    = $query->query()->fetchAll();
        if ($results) {
            return $results;
        } else {
            return array();
        }
    }

}