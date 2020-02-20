<?php

class Api_Model_PaymentGatewayUsers extends Zend_Db_Table_Abstract
{
    protected $_name = 'payment_gateway_user';
    protected $_id = 'id';
    
    /**
     * Returns the payement gateway details of a particular CHAP
     * @param - $chapId    
     */
    public function getGatewayDetailsByChap($chapId)
    {        
        $selectSql = $this->select();
        $selectSql->from(array('pu' => $this->_name), array('pu.payment_gateway_id'))
                  ->setIntegrityCheck(false)
                  ->join(array('p' => 'payment_gateways'), 'pu.payment_gateway_id = p.id', array('p.gateway_id')) 
                  ->where('pu.status = ?',1)  
                  ->where('pu.chap_id = ?',$chapId);
        
        return $this->fetchRow($selectSql);
    }
}