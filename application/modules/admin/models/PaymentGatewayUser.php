<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 7/9/13
 * Time: 3:23 PM
 * To change this template use File | Settings | File Templates.
 */

class Admin_Model_PaymentGatewayUser extends Zend_Db_Table_Abstract
{
    protected  $_id     = 'id';
    protected  $_name   = 'payment_gateway_user';

    public function  __construct() {
        parent::__construct();
    }

    public function getPaymentGatewayUserDetails($id)
    {
        $sql = $this->select()
            ->from('payment_gateway_user')
            ->where('payment_gateway_user.chap_id = ?',$id)
            ->order('payment_gateway_user.id DESC');
        $paymentGatewayUsers = $this->fetchAll($sql)->toArray();
        return $paymentGatewayUsers;
    }

    function disableGatewayByChap($chapId)
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

    public function updatePaymentGateway($userID,$paymentGatewayID)
    {
        $date = new DateTime();
        $data = array(
            'payment_gateway_id'  => $paymentGatewayID,
            'chap_id'    => $userID,
            'date_added'=> date_format($date, 'Y-m-d H:i:s')
        );
        $this->insert($data);
        //return true;
    }
}