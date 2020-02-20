<?php

class Chap_Model_ProductChapFeatured extends Zend_Db_Table_Abstract
{
    protected $_name = 'product_chap_featured';
    protected $_id = 'id';
    
    public function addChapFeaturedProduct($productId,$platformId,$chapId)
    {
        
        $data = array
                (
                    'product_id' => $productId,
                    'platform_id' => $platformId,
                    'chap_id'      => $chapId,
                    'date_created' => new Zend_Db_Expr('NOW()')
                );
        
       $id =  $this->insert($data);
       return $id;
    }
    
    
    public function getFeaturedProductsByPlatform($chapId,$platfromId)
    {       
        $productSql   = $this->select(); 
        $productSql->from(array('pf' => $this->_name), array('pf.id'))                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'pf.product_id = p.id', array('p.name','p.id as product_id'))                   
                    ->where('pf.chap_id = ?',$chapId)                   
                    ->where('pf.platform_id = ?', $platfromId)
                    ->where('p.deleted != ?', 1)
                    ->order('p.name ASC');
        
            //Zend_Debug::dump($searchSql->__toString());die();
            return $this->fetchAll($productSql);
    }
    
    public function deleteFeaturedApp($featuredAppId)
    {
        $rowsAffected = $this->delete( array('id = ?' => $featuredAppId, 'chap_id = ?' => Zend_Auth::getInstance()->getIdentity()->id));
        
        if($rowsAffected > 0)
        {
            return  TRUE;
            
        }
        else
        {
            return FALSE;
        }
        
    }
    
    
    //get the application count
    public function getAppCount($chapId,$platformId,$productId)
    {
        $countSql = $this->select();
        $countSql ->from($this->_name,array('count(*) as app_count'))
                ->where('chap_id = ?',$chapId)
                ->where('platform_id = ?',$platformId)
                ->where('product_id = ?',$productId);
               
        $rowCount = $this->fetchAll($countSql);
        
        if(($rowCount[0]->app_count) > 0)
        {
             return  TRUE;
        } 
        else
        {
            return FALSE;
        }
    }
    
     /**
     * 
     * Returns chap specific Featured products.
     * @param $platformId if a platform is passed, filters by platform
     * @param $chapId
     */
    public function getFeatureAppsQueryByChap($chapId,$platformId = 0)
    {
        
        $productSql   = $this->select(); 
        $productSql->from(array('pf' => $this->_name),array())                   
                    ->setIntegrityCheck(false)                   
                    ->join('products', 'pf.product_id = products.id', array('products.id'))  
                    ->where('pf.chap_id = ?',$chapId) 
                    ->order('pf.date_created DESC'); 

                
                if($platformId >0)
                {
                    $productSql->where('pf.platform_id = ?', $platformId)
                                ->distinct();
                }  
         
            return $productSql;
    }
    
}