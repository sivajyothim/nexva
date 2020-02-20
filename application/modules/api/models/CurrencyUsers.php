<?php

class Api_Model_CurrencyUsers extends Zend_Db_Table_Abstract
{
    protected $_name = 'currency_users';
    protected $_id = 'id';
    
    public function getCurrencyDetailsByChap($chapId)
    {
        $selectSql   = $this->select(); 
        $selectSql->from(array('cu' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('c' => 'currencies'), 'cu.currency_id = c.id', array('c.name','c.code','c.symbol','c.rate','c.symbol_web'))
                    ->where('c.enabled = ?',1)
                    ->where('cu.status = ?',1)
                    ->where('cu.user_id = ?', $chapId);
        


        
        $result = $this->fetchRow($selectSql);
        
        if($result)
        {
            return $result->toArray();    
        }
        else {
            return null;
        }      
    }
    
}