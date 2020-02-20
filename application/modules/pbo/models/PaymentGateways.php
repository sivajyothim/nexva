<?php

class Pbo_Model_PaymentGateways extends Zend_Db_Table_Abstract
{
    protected $_name = 'payment_gateways';
    protected $_id = 'id';
    
    
    /**
     * Returns all the pages peratains to a CHAP
     * @param - $chapId    
     */
    public function getAllPaymentGateways($chapId)
    {       
        $selectSql   = $this->select(); 
        $selectSql->from($this->_name, array('id','name'))
                  ->where('status = ?',1)  
                  ->where('supports_wl = ?',1)
                  ->order('name DESC');
        
        return $this->fetchAll($selectSql);
    }
}