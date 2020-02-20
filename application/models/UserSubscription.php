<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */

class Model_UserSubscription extends Zend_Db_Table_Abstract {
    protected $_name = 'user_subscriptions';
    protected $_id   = 'id';

    public function getAll() {

    	
    	
    	 $select = $this->select()
    	                ->setIntegrityCheck(false)
                        ->from('user_subscriptions', array('user_subscriptions.id', 'user_subscriptions.end_date',  'user_subscriptions.start_date', 'user_subscriptions.interval', 'user_subscriptions.order_id'))
                        ->joinInner('orders', 'orders.id = user_subscriptions.order_id', array('orders.transaction_id', 'orders.user_id', 'orders.merchant_id'))
                        ->joinInner('order_details', 'orders.id = order_details.order_id', array('order_details.product_id', 'order_details.price'))
                        ->where(new Zend_Db_Expr('DATEDIFF(CURDATE() , user_subscriptions.end_date) >=  1'))
                        ->where("user_subscriptions.status = 1");
  
                        
                     //  echo  $select->assemble();
                        
                        
        $resultRows = $this->fetchall($select);
    	//Zend_Debug::Dump($resultRows1);
    	
    	return  $resultRows;
    	  
    	//Zend_Debug::Dump( $this->fetchAll(array(new Zend_Db_Expr('DATEDIFF(CURDATE() , end_date) >=  1'), 'status' => 'enable'))->toArray() );
         // return $this->fetchAll(array(new Zend_Db_Expr('DATEDIFF(CURDATE() , end_date) >=  1'), "status = 'enable'"));
    

    }
    
    
    public function getUserSubscription($userName, $productId) {

        
        
         $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from('user_subscriptions', array('user_subscriptions.id', 'user_subscriptions.end_date',  'user_subscriptions.start_date'))
                        ->joinInner('orders', 'orders.id = user_subscriptions.order_id', array('orders.transaction_id'))
                        ->joinInner('order_details', 'orders.id = order_details.order_id', array('orders.payment_method'))
                        ->joinInner('users', 'users.id = orders.user_id', array('users.username'))
                        ->where(new Zend_Db_Expr('DATEDIFF(CURDATE() , user_subscriptions.end_date) <  1'))
                        ->where("user_subscriptions.status = 1")
                        ->where('order_details.product_id =?', $productId)
                        ->where('users.email =?', $userName);
  
                        
                       //echo  $select->assemble();
                        
                        
        $resultRows = $this->fetchall($select);
        //Zend_Debug::Dump($resultRows1);
        
        return  $resultRows;
          
        //Zend_Debug::Dump( $this->fetchAll(array(new Zend_Db_Expr('DATEDIFF(CURDATE() , end_date) >=  1'), 'status' => 'enable'))->toArray() );
         // return $this->fetchAll(array(new Zend_Db_Expr('DATEDIFF(CURDATE() , end_date) >=  1'), "status = 'enable'"));
    

    }
    
    
 public function createSubscription($userSubscriptionData) {
        $values = array(
        
                "payment_gateway_id"          => $userSubscriptionData['payment_gateway_id'],
                "start_date"        => date('Y-m-d'),
                "end_date"          => $userSubscriptionData['end_date'],
                "interval"          => $userSubscriptionData['interval'],
                "mdn"          => $userSubscriptionData['mdn'],
                "status"            => '1',
                "product_id" => $userSubscriptionData['product_id'],
                "price"  => $userSubscriptionData['price']
        );
            $res  =   $this->insert($values);
        return Zend_Registry::get('db')->lastInsertId();
    }
    

    public function createSubscriptionWithPackageAssert($userSubscriptionData) {
    	$values = array(
    
    			"payment_gateway_id"          => $userSubscriptionData['payment_gateway_id'],
    			"start_date"        => date('Y-m-d'),
    			"end_date"          => $userSubscriptionData['end_date'],
    			"interval"          => $userSubscriptionData['interval'],
    			"mdn"          => $userSubscriptionData['mdn'],
    			"status"            => '1',
    	        "package_name"          => $userSubscriptionData['package_name'],
    			"assert_name"            => $userSubscriptionData['assert_name'],
    	        "product_id" => $userSubscriptionData['product_id'],
    	        "price"  => $userSubscriptionData['price']
    	);
    	$res  =   $this->insert($values);
    	return Zend_Registry::get('db')->lastInsertId();
    }
    
    
    public function subscriptionValidDate($mdn, $nexvaAppId)
    {
        $sql = $this->select();
        $sql->from($this->_name, array('*'))
                ->where('mdn = ?', $mdn)
                ->where('product_id = ?', $nexvaAppId)
                ->order('id DESC')
                ->limit(1);

        return $this->fetchRow($sql);
    }

    public function subscriptionValidDateWithPackageAssert($mdn, $nexvAppId, $packageName, $assertName)
    {
        $sql = $this->select();
        $sql->from($this->_name, array('*'))
                ->where('mdn = ?', $mdn)
                ->where('product_id = ?', $nexvAppId)
                ->where('package_name = ?', $packageName)
                ->where('assert_name = ?', $assertName)
                ->order('id DESC')
                ->limit(1);
        
        return $this->fetchRow($sql);
    }

   
}

?>
