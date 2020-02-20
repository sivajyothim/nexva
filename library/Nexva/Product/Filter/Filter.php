<?php

/**
 * App filter class. This class is given an array of rules which will modify a query object
 * @author John
 *
 */
class Nexva_Product_Filter_Filter {
    private $rules  = array();
    
    /**
     * Applies the rules to the query
     * @param Zend_Db_Table_Abstract $query
     */
    public function applyRules($query = null, $rules = array()) {
        $this->setRules($rules);
        
        if (empty($this->rules)) {
            return $query;
        }
        
        foreach ($this->rules as $rule) {
            if ($rule->isEnabled()) {
                $query  = $rule->initTables($query);
                $query  = $rule->modifyQueryObject($query);    
            }
        }
        return $query;
    }
    
    /**
     * 
     * Given an array of rule DB rows, the method will create rule objets and populate its array
     * @param array $rules
     */
    protected function setRules($rules = array()) {
        $this->rules    = array();
        foreach ($rules as $rule) {
            $ruleObject = self::getRule($rule);
            if ($ruleObject) {
                $this->rules[]  = $ruleObject;
            }
        }
    }
    
    /**
     * This method accepts a rule DB row and returns a rule object
     * @param $ruleRow
     */
    public static function getRule($ruleRow = null) {
        if (!isset($ruleRow->filter)) return false;
        
        $filename   = 'Nexva_Product_Filter_Rule_' . ucfirst(strtolower($ruleRow->filter));
        if (class_exists($filename)) {
            return new $filename($ruleRow);
        }
        return false;
    }
}