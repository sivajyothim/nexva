<?php

class Pbo_Model_PaymentGatewayUsers extends Zend_Db_Table_Abstract
{
    protected $_name = 'payment_gateway_user';
    protected $_id = 'id';
    
    
    /**
     * Returns the payement gateway pertains to the CHAP
     * @param - $chapId    
     */
    public function getGatewayByChap($chapId)
    {        
        $selectSql   = $this->select(); 
        $selectSql->from($this->_name, array('id','payment_gateway_id'))
                  ->where('status = ?',1)  
                  ->where('chap_id = ?',$chapId);
        
        return $this->fetchRow($selectSql);
    }
    
    /**
     * change the status of the payment gateway of the CHAP (disable)
     * @param - $chapId    
     */
    public function disableGatewayByChap($chapId)
    {
        $data = array('status' => 0);
        $where = array('chap_id = ?' => $chapId, 'status = ?' => 1);
                
        $rowsAffected = $this->update($data,$where);
        
        if($rowsAffected > 0)
        {
            return  TRUE;
            
        }
        else
        {
            return FALSE;
        }
    }
    
    
    /**
     * Add CHAP payment gateway
     * @param - $chapId,
     * @param - $paymentGatewayId    
     */
    public function addChapGateway($chapId,$paymentGatewayId,$status = 1)
    {
        
        $data = array
                (
                    'chap_id' => $chapId,  
                    'payment_gateway_id' => $paymentGatewayId,
                    'status' => $status,
                    'date_added' => new Zend_Db_Expr('NOW()')
                );
        
       $id =  $this->insert($data);
       return $id;
    }
    
}