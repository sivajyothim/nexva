<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj   product_channel
 * Date: 11/1/13
 * Time: 1:33 PM
 * To change this template use File | Settings | File Templates.
 */
class Cpbo_Model_ProductChannel extends Zend_Db_Table_Abstract {

    protected $_name    = 'product_channel';
    protected $_primary = 'id';

    function  __construct() {
        parent::__construct();
    }

    function getChannel($productId,$chapId)
    {
        $sql =  $this   ->select()
            ->from(array('pc'=>$this->_name),array('pc.id'))
            ->setIntegrityCheck(false)
            ->where('pc.product_id = ?', $productId )
            ->where('pc.chap_id = ?', $chapId )
        ;
        return $this->fetchAll($sql);
    }

    function setChannel($productId,$chapId)
    {
        $sql =  $this   ->select()
                ->from(array('pc'=>$this->_name))
                ->setIntegrityCheck(false)
                ->where('pc.product_id = ?', $productId )
                ->where('pc.chap_id = ?', $chapId )
                ;
        $rowset = $this->fetchAll($sql);
        if(count($rowset) == 0)
        {
            $data = array(
                'product_id'  => $productId,
                'chap_id'    => $chapId,
                'date'=> date_format(new DateTime(), 'Y-m-d H:i:s')
            );
            $this->insert($data);
        }
    }

    function removeChannel($productId,$chapId)
    {
        $this->delete( array('product_id = ?' => $productId, 'chap_id = ?' => $chapId));
    }

}