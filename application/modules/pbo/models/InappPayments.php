<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 11/12/13
 * Time: 4:46 PM
 * To change this template use File | Settings | File Templates.
 */
class Pbo_Model_InappPayments extends Zend_Db_Table_Abstract
{
    protected $_name = 'inapp_payments';
    protected $_id = 'id';

    function getAllInappPaymentByChap($chap_Id)
    {
        $sql = $this->select()
                ->where('chap_id=?',$chap_Id)
                ->order('date_transaction desc');
        //echo $sql->assemble($sql);die();
        return $this->fetchAll($sql);
    }

}
?>