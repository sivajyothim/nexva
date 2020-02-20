<?php

class Api_Model_InappPayments extends Zend_Db_Table_Abstract
{
    
    protected $_name = 'inapp_payments';
    protected $_id = 'id';
    

    public function add($chapId, $mobileNo, $price, $paymentGatewayId, $activationCode, $nexvAppId, $currencyCode)
    {        
        $data = array
                (
                    'chap_id' => $chapId,
                    'nexva_app_id' => $nexvAppId,
                    'mobile_no' => $mobileNo,
                    'date_transaction' => new Zend_Db_Expr('NOW()'),
                    'price' => $price,
                    'trans_id' => 0,  
                    'payment_gateway_id' => $paymentGatewayId,
                    'activation_code' => $activationCode,
                    'currency_code'  => $currencyCode,
                );
        
       $id =  $this->insert($data);
       return $id;
    }
    
    public function updatePayments($paymentId, $transId, $status, $mode)
    {
    	$data = array
    	(
    			'trans_id' => $transId,
    			'status' => $status,
    	        'mode' => $mode
    	);
    
    	$where = array
    	(
    			'id = ?' => $paymentId
    	);
    
    	$this->update($data, $where);

    }
    
    public function updatePaymentsWithPackageAssert($paymentId, $transId, $status, $mode, $packageName, $assertName)
    {
    	$data = array
    	(
    			'trans_id' => $transId,
    			'status' => $status,
    			'mode' => $mode,
    	        'package_name' => $packageName, 
    	        'assert_name' => $assertName
    	        
    	);
    
    	$where = array
    	(
    			'id = ?' => $paymentId
    	);
    
    	$this->update($data, $where);
    
    }
    
    
    public function updateRecurreingStatus($status, $mdn, $nexvaAppId)
    {
        $data = array
    	(
    			'status' => $status,
    	);
    
    	$where = array
    	(
    			'mobile_no = ?' => $mdn,
    	        'nexva_app_id = ?' => $nexvaAppId
    	);

    	return$this->update($data, $where);
    }
    
    public function subscriptionValidDate($mdn, $nexvaAppId)
    {
    	$sql = $this->select();
    	$sql->from($this->_name, array('*'))
    	    ->where('mobile_no = ?', $mdn)
    	    ->where('nexva_app_id = ?', $nexvaAppId);

    	return $this->fetchRow($sql);
    }
    
    public function subscriptionValidDateWithPackageAssert($mdn, $nexvAppId, $packageName, $assertName)
    {
    	$sql = $this->select();
    	$sql->from($this->_name, array('*'))
    	->where('mobile_no = ?', $mdn)
    	->where('nexva_app_id = ?', $nexvAppId)
    	->where('package_name = ?', $packageName)
    	->where('assert_name = ?', $assertName);
    
    	return $this->fetchRow($sql);
    }
    
    public function getPayment($paymentId)
    {
    	$sql = $this->select();
    	$sql->from($this->_name, array('*'))
    	->where('id = ?', $paymentId);
    
    	//   echo  $sql->assemble();
    	return $this->fetchRow($sql);
    }
    
    //This function will be used only for YCoins
    public function addYcoinInApp($chapId, $appId, $phone, $enduserPrice, $paymentGatewayId, $activationCode, $status, $transactionId, $service, $revenue, $errormessage )
    {        
        $data = array
                (
                    'chap_id' => $chapId,
                    'nexva_app_id' => $appId,
                    'mobile_no' => $phone,
                    'date_transaction' => new Zend_Db_Expr('NOW()'),
                    'price' => $enduserPrice,
                    'trans_id' => $transactionId,  
                    'payment_gateway_id' => $paymentGatewayId,
                    'activation_code' => $activationCode,
                    'status' => $status,
                    'service_id' => $service,
                    'revenue' => $revenue,
                    'error_msg' => $errormessage,
                );
        
       $id =  $this->insert($data);
       return $id;
    }

}