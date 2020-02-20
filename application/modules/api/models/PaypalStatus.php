
<?php

class Api_Model_PaypalStatus extends Zend_Db_Table_Abstract {

    protected $_name = 'paypal_status';
    protected $_id = 'id';

    public function insertPaypalStatus($chapId,$appId,$buildId,$email,$amount,$sellers_paypal_account,$payment_status,$payment_date_time,$currency) {
        
            $data=array(
                'chap_id'=>$chapId,
                'app_id'=>$appId,
                'build_id'=>$buildId,
                'email'=>$email,
                'amount'=>$amount,
                'currency'=>$currency,
                'sellers_paypal_account'=>$sellers_paypal_account,
                'payment_status'=>$payment_status,
                'payment_date_time'=>$payment_date_time
            );
            $this->insert($data);
    }        
       
    

}
