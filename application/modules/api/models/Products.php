<?php

class Api_Model_Products extends Zend_Db_Table_Abstract
{
    protected $_name = 'products';
    protected $_id = 'id';
            
    public function getProductDetailsbyId($appId)
    {
        $productSql   = $this->select(); 
        $productSql->from($this->_name, array('price','name'))
                    ->where('id = ?',$appId)
                    ->where('deleted = ?',0)
                    ->where('status = ?','APPROVED');       
       
         return $this->fetchRow($productSql);
    }
    

    
    public function getCompatibleAndroidProducts()
    {
        $productSql   = $this->select(); 
        $productSql->from(array('p' => 'products'), array('p.id','p.name','p.thumbnail','p.keywords', 'p.price','p.user_id', 'p.created_date'))
                   ->setIntegrityCheck(false)
                   ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id', 'pb.name as build_name', 'pb.created_date as build_created_date', 'pb.language_id'))  
                   ->join(array('pf' => 'build_files'), 'pf.build_id = pb.id', array())
                   ->join(array('u' => 'users'), 'u.id = p.user_id', array('u.email'))  
                   ->where('pb.build_type = ?', 'files')
                   ->where('pb.platform_id = ?', 12)
                   ->where('p.deleted <> ?', 1)
                   ->where('pb.build_type = ?', 'files')
                   ->where('p.user_id <> ?', 5981)
                   ->where('p.status = ?','APPROVED')
                   ->order('p.created_date desc')
                   ->group('p.id');
        
      //  Zend_Debug::dump($productSql->assemble());
    ///    die();
       
     return $this->fetchAll($productSql);
     
    // return $productSql;
   
    }


}