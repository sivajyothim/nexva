<?php
abstract class Nexva_Product_Filter_Rule_Abstract {
    
    protected $ruleRow   = null;
    
    /**
     * Contains an array of required tables the rule needs to work with 
     * We always assume that the product table is joined
     *
     * NOTE - products table MUST BE JOINED for this to work
     * @var Array
     */
    protected $_requiredTables   = array();
    

    /**
     * @param Zend_Db_Table_Abstract $query
     */
    public function __construct($ruleRow = null) {
        $this->ruleRow  = $ruleRow;
    }

    /**
     * Modifies the query object with the filter
     * @param Zend_Db_Table_Select $query
     * @return Zend_Db_Table_Select
     */
    abstract function modifyQueryObject($query = null);
    
    
    /**
     * Initializes the query with all the required tables  
     * @param Zend_Db_Table_Select $query
     */
    public function initTables($query) {
        if (empty($this->_requiredTables)) {
            return $query;
        }
        
        $parts  =   $query->getPart(Zend_Db_Table_Select::FROM);
        if (!isset($parts['products'])) {
            throw new Nexva_Product_Filter_Exception('Products table is not present in query');    
        }
        
        foreach ($this->_requiredTables as $table => $join) {
            if (!isset($parts[$table])) {
                $query->{$join['joinType']}($table, $join['condition'], $join['select']);
            }
        }
        return $query;
    }
   
    public function isEnabled() {
        if ($this->ruleRow->enabled) {
            return true;
        }
        return false;
    }
} 