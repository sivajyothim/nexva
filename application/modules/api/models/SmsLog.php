<?php

class Api_Model_SmsLOg extends Zend_Db_Table_Abstract
{
    protected $_name = 'sms_log';
    protected $_id = 'id';
    
    function loggedSMS($massage,$mobileNumber,$status) {
        $values = array(
            'massage' =>$massage,
            'mobile_number' => $mobileNumber,
            'sent_time' => date('Y-m-d H:i:s'),
            'status' => $status
        );
        $result=false;
        $this->insert($values);
        if(Zend_Registry::get('db')->lastInsertId()){
            $result= TRUE;
        }
        return $result;
    }

}