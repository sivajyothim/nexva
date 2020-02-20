<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 1/20/14
 * Time: 11:57 AM
 * To change this template use File | Settings | File Templates.
 */

class Api_Model_Reviews extends Zend_Db_Table_Abstract{
    protected $_name = 'reviews';
    protected $_id = 'id';

    public function getReviewsByAppId($appId){
        $sql    = $this->select();
        $sql    ->from(array('r' => $this->_name),array('DATE(r.date) AS formatted-date','r.*'))
                ->setIntegrityCheck(false)
                ->where('r.product_id = ?',$appId)
                ->where('r.status = ?','APPROVED')
                ;
        //Zend_debug::dump($sql->assemble());
        return $this->fetchAll($sql)->toArray();
    }
}