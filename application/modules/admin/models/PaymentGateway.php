<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 7/9/13
 * Time: 2:45 PM
 * To change this template use File | Settings | File Templates.
 */

class Admin_Model_PaymentGateway extends Zend_Db_Table_Abstract
{
    protected  $_id     = 'id';
    protected  $_name   = 'payment_gateways';

    public function  __construct() {
        parent::__construct();
    }

    public function getPaymentGateway()
    {
        $sql = $this->select()
                    ->from('payment_gateways')
                    ->where('payment_gateways.status = ?','1')
                    ->where('payment_gateways.supports_wl = ?','1')
                    ->order('payment_gateways.name DESC');
        $paymentGateways = $this->fetchAll($sql)->toArray();
        return $paymentGateways;
    }
}