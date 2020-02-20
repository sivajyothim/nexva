<?php

class Api_Model_PaypalResponce extends Zend_Db_Table_Abstract {

    protected $_name = 'paypal_responce';
    protected $_id = 'id';

    public function insertResponce($responce,$transactionId) {
        foreach ($responce as $key=>$value){
            $data=array(
                'transaction_id'=>$transactionId,
                'responce_key' =>$key,
                'responce_value' =>$value
            );
            
            $id = $this->insert($data);
        }
        
        return $id;
    }

}
