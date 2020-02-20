<?php

class Api_Model_Currencies extends Zend_Db_Table_Abstract
{
    protected $_name = 'currencies';
    protected $_id = 'id';
            
    public function getCurrencyRate($currency)
    {
        $currencieSql = $this->select(); 
        $currencieSql->from($this->_name, array('*'))
                     ->where('code = ?', $currency);       
       
         return $this->fetchRow($currencieSql);
    }

}