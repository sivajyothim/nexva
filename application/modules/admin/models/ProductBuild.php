<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 10/17/13
 * Time: 4:12 PM
 * To change this template use File | Settings | File Templates.
 */

class Admin_Model_ProductBuild extends Zend_Db_Table_Abstract {
    protected  $_id     = 'id';
    protected  $_name   = 'product_builds';

    /**
     * @param $buildId
     * Updates the avg_Approved status, when the avg approval receives
     */
    function updateAvgStatus($buildId)
    {
        $data = array(
            'avg_Approved'      => '1'
        );
        $this->update($data,'id = '.$buildId);
    }

    function getAvgApproved($productId)
    {
        $select =   $this->select()
            ->from(array('pb'=>$this->_name),array('pb.id AS build_id'))
            ->where('pb.product_id = ?',$productId)
            ->where('pb.avg_approved = ?',1)
        ;
        return $result = $this->fetchAll($select);
    }
}