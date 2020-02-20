<?php

class Api_Model_InteropPayments extends Zend_Db_Table_Abstract
{
    
    protected $_name = 'interop_payments';
    protected $_id = 'id';
    
    
    /**
     * 
     * Insert Interop payment transaction to the table, before the transaction happens
     * @param $appId App Id
     * @param $chapId Chap Id
     * @param $buildId Build Id
     * @param $mobileNo Mobile No
     * @param $price App price(transaction amount)
     * returns record id
     */
    public function addInteropPayment($chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId, $smsVerificationToken = null)
    {        
        $data = array
                (
                    'chap_id' => $chapId,
                    'app_id' => $appId,
                    'build_id' => $buildId,
                    'mobile_no' => $mobileNo,
                    'date_transaction' => new Zend_Db_Expr('NOW()'),
                    'price' => $price,
                    'trans_id' => 0,  
                    'payment_gateway_id' => $paymentGatewayId,
                    'token' => $smsVerificationToken
                );
        
        
       $id =  $this->insert($data);
       return $id;
    }
    
    /**
     * 
     * Update a particular Interop payment transaction after the transaction happens
     * @param $paymentId Record Id
     * @param $transDate transaction date
     * @param $transId transaction id
     * @param $status transaction status
     * @param $buildUrl amozon s3 url which was sent to the android app in oreder to download  
     */
    
    /*         $data = array
     (
     		'chap_id' => $chapId,
     		'app_id' => $appId,
     		'build_id' => $buildId,
     		'mobile_no' => $mobileNo,
     		'date_transaction' => new Zend_Db_Expr('NOW()'),
     		'price' => $price,
     		'trans_id' => 0,
     		'payment_gateway_id' => $paymentGatewayId,
     		'token' => $smsVerificationToken
     ); */
    
    public function updateInteropPayment($paymentId, $transDate, $transId ,$status, $buildUrl, $token = null, $request = null, $response = null)
    { 
        $data = array
                (   
                    'trans_timestamp' => $transDate,
                    'trans_id' => $transId,
                    'status' => $status,
                    'downlaod_link'=> $buildUrl,
                    'token' => $token,
                    'response'=> $response,
                    'request' => $request
                        
                );
        
        $where = array 
                (
                    'id = ?' => $paymentId
                );
        
        
/*         Zend_Debug::dump($request);        Zend_Debug::dump($response); die();
        
        $db = Zend_Registry::get ( 'db' );
        $db->getProfiler()->setEnabled(true);
        
    
        
        Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
        Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
        $db->getProfiler()->setEnabled(false);
        die(); */
        $id =  $this->update($data, $where);

       return $id;
    }
    
    /**
     * 
     * Select a particular Interop payment transaction after the transaction happens
     * @param $paymentTimeStamp Record Id
     */
    public function selectInteropPayment($sessionId, $interopPaymentId)
    {
        $sql = $this->select();
        $sql->from($this->_name,array('app_id','build_id','price','trans_timestamp','trans_id','downlaod_link','status','token','mobile_no'))
            ->where('id = ?',$interopPaymentId);        
         
        //echo $sql->assemble();
        return $this->fetchRow($sql);

    }
    
    public function getInteropPayment($chapId, $startDate, $endDate)
    {
    	$sql = $this->select();
    	$sql->from($this->_name,array('id','chap_id', 'app_id','build_id','price','date_transaction','trans_timestamp','trans_id', 'payment_gateway_id','downlaod_link','status','token','mobile_no'))
    	->where('chap_id = ?',$chapId)
    	->where('date_transaction >= ?', $startDate)
    	->where('date_transaction <= ?', $endDate);
    	 
    	//echo $sql->assemble();
    	return $this->fetchAll($sql);
    
    }
    
    public function getInteropPaymentInfo($chapId, $startDate, $endDate, $status,$trans_id=null)
    {
    	$sql = $this->select();
    	$sql->from(array('ip' => $this->_name),array('id','chap_id', 'app_id','build_id','price','date_transaction','trans_timestamp','trans_id', 'payment_gateway_id','downlaod_link','status','token','mobile_no'))
    	->setIntegrityCheck(false)
    	->join(array('pg' => 'payment_gateways'), 'ip.payment_gateway_id = pg.id', array('pg.maketing_name'))
    	->where('chap_id = ?',$chapId)
    	->where('date_transaction >= ?', $startDate)
    	->where('date_transaction <= ?', $endDate);
    	
    	if($status)
    	    $sql->where('ip.status = ?',$status);
        
        /*filter by transaction_id*/        
        if( $trans_id <> NULL &&  (!empty($trans_id))){
            $sql->where('trans_id = ?',$trans_id);
        }
        /*end*/

    	return $this->fetchAll($sql);
    
    }
    
    
    public function getInteropPaymentSum($chapId, $fromDate = null, $toDate = null)
    {
    	$sql = $this->select();
    	$sql->from(array('ip' => $this->_name),array('sum(ip.price) as totle_val'))
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'ip.app_id = p.id', array(''))
    	->where('ip.chap_id = ?',$chapId)
   		->where('ip.status = ?','Success');
    	
    	if(!is_null($fromDate) && !is_null($toDate) && !empty($fromDate) && !empty($toDate))
    	{
    		$sql->where('DATE(date_transaction) >= ?', $fromDate)
    		->where('DATE(date_transaction) <= ?', $toDate);
    	}
    	elseif(!is_null($fromDate) && !empty($fromDate) && (is_null($toDate) || empty($toDate)))
    	{
    		$sql->where('DATE(date_transaction) >= ?', $fromDate);
    	}
    	elseif(!is_null($toDate) && !empty($toDate) && (is_null($fromDate) || empty($fromDate)))
    	{
    		$sql->where('DATE(date_transaction) <= ?', $toDate);
    	}
    	

    	
    	$downloadCount =  $this->fetchRow($sql);
    	
    	if(is_null($downloadCount->totle_val))
    	{
    		return '0.00';
    	}
    	else
    	{
    		return $downloadCount->totle_val;
    	}

    }
}