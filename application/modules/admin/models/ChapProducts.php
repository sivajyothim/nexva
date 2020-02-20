<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 6/10/13
 * Time: 11:50 AM
 * To change this template use File | Settings | File Templates.
 */

class Admin_Model_ChapProducts extends Zend_Db_Table_Abstract {

    protected  $_id     = 'id';
    protected  $_name   = 'chap_products';

    public function  __construct() {
        parent::__construct();
    }

    public function chapAnalytics($chapId,$from,$to)
    {
        $sql = $this->select()
                        ->setIntegrityCheck(false)
                        ->from('statistics_downloads',array('count(statistics_downloads.id) as download_count','MAX(statistics_downloads.date) as date','product_ids'=>new Zend_Db_Expr('GROUP_CONCAT(statistics_downloads.product_id)')))
                        ->join('users','statistics_downloads.chap_id = users.id','users.username as chapName')
                        ->join('products','statistics_downloads.product_id = products.id',array('products.price as price'))
                        ->where('products.status = ?', 'APPROVED')
                        ->where('products.deleted = ?', 0);
                        if(!empty($chapId)){
                            $sql->where('statistics_downloads.chap_id = '.$chapId);
                        }
                        if(!empty($from)){
                            $sql->where('statistics_downloads.date >= ? ',$from);
                        }
                        if(!empty($to)){
                            $sql->where('statistics_downloads.date <= ? ',$to);
                        }
        $sql->group('statistics_downloads.chap_id');
        $products = $this->fetchAll($sql)->toArray();
        return $products;
    }
    
    public function getAllChapsByProduct($pId){
        $sql = $this->select()
                ->from($this->_name,array('chap_id'))
                ->where('product_id=?',$pId);
        $chaps= $this->fetchAll($sql);
        $chapsId=array();
        foreach ($chaps as $chap){
            array_push($chapsId, $chap->chap_id);
        }
        return $chapsId;
    }
}
