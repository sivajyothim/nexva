<?php

class Api_Model_ChapProducts extends Zend_Db_Table_Abstract
{
    protected $_name = 'chap_products';
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
    
    
    public function getChapProductsAll($chapId,$deviceId,$categoryId,$limit = 10,$offset = 0)
    {       
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.thumbnail','p.price','p.user_id','p.keywords'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array()) 
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')  
                    ->where('p.deleted != ?',1)
                    ->where('c.id =?',$categoryId)
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls')
                    ->group('product_id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.thumbnail','p.price','p.user_id','p.keywords'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array()) 
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')  
                    ->where('p.deleted != ?',1)
                    ->where('c.id =?',$categoryId)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls')
                    ->group('product_id');
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))                           
                           ->order('product_id DESC')                           
                           ->limit($limit, $offset);

         //echo $allProductsSql->assemble();die();

         return $this->fetchAll($allProductsSql)->toArray(); 
    }
    
    
    public function getProductCountByChap($chapId,$appId)
    {        
        $sql = $this->select();
        $sql->from($this->_name,array('COUNT(*) AS num'))
            ->where('chap_id = ?',$chapId)
            ->where('product_id = ?',$appId);        
         
        return $this->fetchRow($sql)->num;
        
    }
    
    
    public function getNewestProductIds($chapId,$limit)
    {
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)                      
                    ->order('product_id DESC')
                    ->limit($limit);
                    
        return $this->fetchAll($productSql)->toArray(); 
    }
    
    
    public function getNewestProductIdsByDevice($chapId,$deviceId,$limit)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('p.user_id != ?', 5981)
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('product_id DESC')
                           ->limit($limit);
         
         return $this->fetchAll($allProductsSql)->toArray(); 
        
    }
    
    public function getFreeProductIds($chapId,$limit)
    {
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)
                    ->where('p.price <= ?',0)
                    ->order('RAND(NOW())')
                    ->limit($limit);
                    
        return $this->fetchAll($productSql)->toArray(); 
    }
    
    
    public function getFreeProductIdsByDevice($chapId,$deviceId,$limit)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.price <= ?',0)
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.price <= ?',0)
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('RAND(NOW())')
                           ->limit($limit);
         
         return $this->fetchAll($allProductsSql)->toArray(); 
        
    }
    
    
    public function getPaidProductIds($chapId,$limit)
    {
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)   
                    ->where('p.price > ?',0)
                    ->order('RAND(NOW())')
                    ->limit($limit);
                    
        return $this->fetchAll($productSql)->toArray(); 
    }
    
    
    public function getPaidProductIdsByDevice($chapId,$deviceId,$limit)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.price > ?',0)
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.price > ?',0)
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('RAND(NOW())')
                           ->limit($limit);
         
         return $this->fetchAll($allProductsSql)->toArray(); 
        
    }
    
    /**
     * 
     * Returns the Top Downloaded apps
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * returns $products array
     */
    public function getTopProductIds($chapId,$limit)
    {
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)
                    ->order('pro_count DESC')
                    ->group('sd.product_id')
                    ->limit($limit);        
                    
        return $this->fetchAll($productSql)->toArray(); 
    }
    
    
    public function getTopProductIdsByDevice($chapId,$deviceId,$limit)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array()) 
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->group('sd.product_id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array()) 
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')                    
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->group('sd.product_id');
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('pro_count DESC')
                           ->limit($limit);
                  
         return $this->fetchAll($allProductsSql)->toArray(); 
        
    }
    
    
    /**
     * 
     * Returns the Top Rated apps
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * returns $products array
     */
    public function getTopRatedProductIds($chapId,$limit)
    {
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->join(array('r' => 'ratings'), 'r.product_id = p.id', array('rate_count'=>'COUNT(r.rating)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)
                    ->order('rate_count DESC')
                    ->group('r.product_id')
                    ->limit($limit);        
                    
        return $this->fetchAll($productSql)->toArray(); 
    }
    /**
     * 
     * Returns the Top Rated apps by device
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID   
     * returns $products array
     */
    public function getTopRatedProductIdsByDevice($chapId,$deviceId,$limit)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array()) 
                    ->join(array('r' => 'ratings'), 'r.product_id = p.id', array('rate_count'=>'COUNT(r.rating)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->group('r.product_id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array()) 
                    ->join(array('r' => 'ratings'), 'r.product_id = p.id', array('rate_count'=>'COUNT(r.rating)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')                    
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->group('r.product_id');
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('rate_count DESC')
                           ->limit($limit);
                   
         return $this->fetchAll($allProductsSql)->toArray(); 
        
    }
    
    
    public function getMostViewedProductIds($chapId,$limit)
    {        
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->join(array('sp' => 'statistics_products'), 'sp.product_id = p.id', array('pro_count'=>'COUNT(sp.product_id)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)
                    ->order('pro_count DESC')
                    ->group('sp.product_id')
                    ->limit($limit);
        
        return $this->fetchAll($productSql)->toArray(); 
    }
    
    
    public function getMostViewedProductIdsByDevice($chapId,$deviceId,$limit)
    {
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array()) 
                    ->join(array('sp' => 'statistics_products'), 'sp.product_id = p.id', array('pro_count'=>'COUNT(sp.product_id)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->group('sp.product_id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array()) 
                    ->join(array('sp' => 'statistics_products'), 'sp.product_id = p.id', array('pro_count'=>'COUNT(sp.product_id)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->group('sp.product_id');
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('pro_count DESC')
                           ->limit($limit);
         
         return $this->fetchAll($allProductsSql)->toArray(); 
        
    }
    
     /**
     * Returns featured products - for Home page of the white label site
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * returns $products array
     */ 
    public function getFeaturedProductIds($chapId,$limit)
    {
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)  
                    ->where('cp.featured = ?',1)
                    ->order('product_id DESC')
                    ->group('p.id')
                    ->limit($limit);
                    
        return $this->fetchAll($productSql)->toArray(); 
    }
    
    
    /**
     * Returns featured products when device is selected - for Home page of the white label site
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID 
     * @param  $categoryId   category Id
     * returns $products array
     */   
    public function getFeaturedProductsbyDevice($chapId,$deviceId,$limit,$categoryId)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        //Device detection Method 1
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array());
        
        //Check if Category is given   
        if($categoryId != null && !empty($categoryId))
        {
              $productSql1->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                          ->join(array('c' => 'categories'), 'pc.category_id = c.id', array())
                          ->where('c.id =?',$categoryId);
        }
                
        $productSql1->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')  
                    ->where('cp.featured = ?',1)
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');
                  
                         
        
        //Device detection Method 2
        $productSql2   = $this->select(); 
        $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array());
        
        //Check if Category is given   
        if($categoryId != null && !empty($categoryId))
        {
              $productSql2->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                          ->join(array('c' => 'categories'), 'pc.category_id = c.id', array())
                          ->where('c.id =?',$categoryId);
        }
                
        $productSql2->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('cp.featured = ?',1)
                    ->where('p.deleted != ?',1)
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('product_id DESC')
                           ->limit($limit);
         
         /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
             echo $allProductsSql->assemble(); die();         
         }*/
         
         return $this->fetchAll($allProductsSql)->toArray(); 
        
    }
    
    
    public function getFlaggedProductsbyDevice($chapId,$deviceId,$limit,$categoryId)
    {
    	$deviceAttrib = $this->getDeviceAttributes($deviceId);
    
    	//Device detection Method 1
    	$productSql1   = $this->select();
    	$productSql1->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array());
    
    	//Check if Category is given
    	if($categoryId != null && !empty($categoryId))
    	{
    		$productSql1->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
    		->join(array('c' => 'categories'), 'pc.category_id = c.id', array())
    		->where('c.id =?',$categoryId);
    	}
    
    	$productSql1->where('cp.chap_id = ?',$chapId)
    	->where('bd.device_id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('cp.flagged = ?',1)
    	->where('p.deleted != ?',1)
    	->where('pb.device_selection_type = ?', 'CUSTOM')
    	->where('pb.build_type != ?','urls')
    	->group('p.id');
    
    
    
    	//Device detection Method 2
    	$productSql2   = $this->select();
    	$productSql2->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
    	->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
    	->join(array('d' => 'devices'), 'd.id = da.device_id', array());
    
    	//Check if Category is given
    	if($categoryId != null && !empty($categoryId))
    	{
    		$productSql2->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
    		->join(array('c' => 'categories'), 'pc.category_id = c.id', array())
    		->where('c.id =?',$categoryId);
    	}
    
    	$productSql2->where('cp.chap_id = ?',$chapId)
    	->where('d.id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('cp.flagged = ?',1)
    	->where('p.deleted != ?',1)
    	->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
    	->where('pb.device_selection_type != ?', 'CUSTOM')
    	->where('pb.build_type != ?','urls')
    	->group('p.id');
    
    
    	$allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
    	->order('product_id DESC')
    	->limit($limit);
    
    	return $this->fetchAll($allProductsSql)->toArray();
    
    }
    
    /**
     * Returns bannerd products - for Home page of the white label site
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * returns $products array
     */ 
    public function getBanneredProductIds($chapId,$limit)
    {
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)  
                    ->where('cp.is_banner = ?',1)
                    ->order('product_id DESC')
                    ->limit($limit);
                    
        return $this->fetchAll($productSql)->toArray(); 
    }    
   
    /**
     * Returns bannerd products when device is selected - for Home page of the white label site
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID 
     * @param  $categoryId   category Id
     * returns $products array
     */    
    public function getBanneredProductsbyDevice($chapId,$deviceId,$limit = null,$categoryId = null, $grade = null)
    {       
        $deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        //Device detection Method 1
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array());
        
        //Check if Category is given   
        if($categoryId != null && !empty($categoryId))
        {
              $productSql1->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                          ->join(array('c' => 'categories'), 'pc.category_id = c.id', array())
                          ->where('c.id =?',$categoryId);
        }
                
        $productSql1->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')  
                    ->where('cp.is_banner = ?',1)
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');
                  
                         
        
        //Device detection Method 2
        $productSql2   = $this->select(); 
        $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array());
        
        //Check if Category is given   
        if($categoryId != null && !empty($categoryId))
        {
              $productSql2->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                          ->join(array('c' => 'categories'), 'pc.category_id = c.id', array())
                          ->where('c.id =?',$categoryId);
        }
                
        $productSql2->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('cp.is_banner = ?',1)
                    ->where('p.deleted != ?',1)
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('product_id DESC')
                           ->limit($limit);
         
         /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
             echo $allProductsSql->assemble(); die();         
         }*/
         
         return $this->fetchAll($allProductsSql)->toArray(); 
        
    }
    
    
    public function getFreeProducts($chapId, $deviceId = null)
    {
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)
                    ->where('p.price <= ?',0)
                    ->order('RAND(NOW())');
                    
        return  $productSql; 
    }
    
    public function getPremiumProducts($chapId, $deviceId = null)
    {
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)   
                    ->where('p.price > ?',0)
                    ->order('RAND(NOW())');
                    
        return $productSql; 
    }
    
    public function getFeaturedProducts($chapId, $deviceId = null)
    {
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)  
                    ->where('cp.featured = ?',1)
                    ->order('product_id DESC');
                    
        return $productSql; 
    }
    
    
    public function getNewestProducts($chapId, $deviceId = null)
    {
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)                      
                    ->order('product_id DESC');
                    
        return $productSql; 
    }
    
    public function getTopProducts($chapId, $deviceId = null)
    {
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)
                    ->order('pro_count DESC')
                    ->group('sd.product_id');
                    
        return  $productSql; 
    }
    
    
    public function getMostViewdProduct($chapId, $deviceId = null)
    {        
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->join(array('sp' => 'statistics_products'), 'sp.product_id = p.id', array('pro_count'=>'COUNT(sp.product_id)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)
                    ->order('pro_count DESC')
                    ->group('sp.product_id');
        
         return  $productSql;
    }
    
        
    public function getProductsByCategory($chapId, $category)
    {        
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.id as product_id'))  
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('pc.category_id = ?', $category) 
                    ->where('p.deleted != ?',1)
                    ->group('cp.product_id');
         return  $productSql;
    }
    
    public function getProductSummary($chapId,$prodId)
    {
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 
                                 'cp.product_id = p.id', 
                                  array('p.name',
                                        'p.id as product_id',
                                        'p.price',
                                        'p.thumbnail'
                                      )
                            )  
                    ->join(array('pb' => 'product_builds'), 
                                 'pb.product_id = p.id', 
                                 array('pb.id as build_id')
                          )   
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('cp.product_id = ?',$prodId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1);
        return $this->fetchAll($productSql1)->toArray(); 
    }
    
    public function getFreeProductsByDevice($chapId,$deviceId)
    {
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->where('p.price <= ?',0);
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->where('p.price <= ?',0);
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('RAND(NOW())');
         
         return $allProductsSql; 
        
    }
    
    
    public function getPremiumProductsByDevice($chapId,$deviceId)
    {
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->where('p.price > ?',0);
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->where('p.price > ?',0);
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('RAND(NOW())');
         
         return $allProductsSql ; 
        
    }
    
    public function getTopProductsByDevice($chapId, $deviceId, $dataSet= null, $limit = null, $offset = null)
    {
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array()) 
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->where('pb.build_type != ?','urls')
                    ->group('sd.product_id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array()) 
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->group('sd.product_id');
                     
        
                    if($limit)	{
                    	
                    	$allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('pro_count DESC')
                           ->limit($limit, $offset);
                    	
                    } else {
                    	
                    	$allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('pro_count DESC');
                           
                    }
                    
                    if($dataSet)
                    	 return $this->fetchAll($allProductsSql)->toArray(); 

         
         return $allProductsSql; 
        
    }
    
    
    public function getNewestProductsByDevice($chapId,$deviceId)
    {
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls')
                    ->where('p.user_id != ?', 5981);
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->where('pb.build_type != ?','urls');
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('product_id DESC');
         
         return $allProductsSql; 
        
    }
    
    public function getFeatureProductsbyDevice($chapId,$deviceId)
    {
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array()) 
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')  
                    ->where('cp.featured = ?',1)
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array()) 
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('cp.featured = ?',1)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls');
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('product_id DESC');
         
         return $allProductsSql; 
        
    }
    
    public function getTopProductsIdByDevice($chapId,$deviceId)
    {
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array()) 
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->group('sd.product_id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array()) 
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->group('sd.product_id');
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('pro_count DESC');
         
         return $allProductsSql; 
        
    }
    
    
    public function getMostViewedProductsByDevice($chapId,$deviceId)
    {
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array()) 
                    ->join(array('sp' => 'statistics_products'), 'sp.product_id = p.id', array('pro_count'=>'COUNT(sp.product_id)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->group('sp.product_id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array()) 
                    ->join(array('sp' => 'statistics_products'), 'sp.product_id = p.id', array('pro_count'=>'COUNT(sp.product_id)'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('pb.build_type != ?','urls')
                    ->group('sp.product_id');
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('pro_count DESC');
         
         return $allProductsSql; 
        
    }
    
    public function getProductBuildId($productId, $deviceId)
    {    
        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        $productSql1   = $this->select(); 
        $productSql1->from(array('p' => 'products'), array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))                   
                    ->setIntegrityCheck(false)  
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->where('p.id = ?',$productId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('pb.device_selection_type = ?', 'CUSTOM');
                
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('p' => 'products'), array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))                   
                    ->setIntegrityCheck(false)  
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->where('p.id = ?',$productId)
                    ->where('d.id = ?',$deviceId)
                    //->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('pb.device_selection_type != ?', 'CUSTOM');

        if(($deviceAttrib[1] == 'RIM OS' or $deviceAttrib[1] == 'BlackBerry') && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
        }
        else
        {
            $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);

        }

         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))->order('build_id DESC');
         //echo $allProductsSql->assemble();die();
         $build =  $this->fetchRow($allProductsSql);
        
        return $build['build_id'];
        
    }
    
    /**
     * 
     * Returns the build id of the chapter language  
     * @param $productId App ID
     * @param $deviceId Device ID
     * @param $langId Chapter default language
     * returns app details
     */
    
    public function getProductBuildChapIdByLanId($productId, $deviceId,$chapLangId)
    {    
        
        $deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        $productSql1   = $this->select(); 
        $productSql1->from(array('p' => 'products'), array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))                   
                    ->setIntegrityCheck(false)  
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id','pb.language_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->where('p.id = ?',$productId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('pb.language_id = ?',$chapLangId);
                
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('p' => 'products'), array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))                   
                    ->setIntegrityCheck(false)  
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id','pb.language_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->where('p.id = ?',$productId)
                    ->where('d.id = ?',$deviceId)
                    //->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('pb.language_id = ?',$chapLangId);

        if(($deviceAttrib[1] == 'RIM OS' or $deviceAttrib[1] == 'BlackBerry') && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
        }
        else
        {
            $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);

        }

         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))->order('build_id DESC');

         $build =  $this->fetchRow($allProductsSql);
        return $build['build_id'];
        
    }
    
    public function getTopCategoryProducts($chapId, $deviceId, $categoryId, $limit, $offset)
    {
    
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array()) 
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array(''))  
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array(''))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)    
                    ->where('pc.category_id = ?', $categoryId)  
                    ->where('pb.build_type != ?','urls')
                    ->group('sd.product_id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))  
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array(''))    
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array()) 
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array(''))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)    
                    ->where('pc.category_id = ?', $categoryId)  
                    ->where('pb.build_type != ?','urls')
                    ->group('sd.product_id');
                     
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->limit($limit, $offset);
                    

         return $this->fetchAll($allProductsSql)->toArray(); 
        
    }

    /**
     * @param $chapId
     * @return Zend_Db_Table_Rowset_Abstract
     */

 function myOpenMobileApps($chapId)
    {
        
    
        $productSql    =   $this->select();
        $productSql        ->from(array('cp' => 'chap_products'))
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'),'p.id = cp.product_id')
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id', 'pb.name as build_name',  'pb.language_id'))
                    ->join(array('pf' => 'build_files'), 'pf.build_id = pb.id', array())
                    ->join(array('u' => 'users'), 'u.id = p.user_id', array('u.email'))
                    ->where('pb.build_type = ?', 'files')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('pb.platform_id = ?', 12)
                    ->where('p.deleted <> ?', 1)
                    ->where('p.status = ?','APPROVED')
                    ->where('p.user_id <> ?',3068)
                    ->where('p.user_id <> ?', 5981)
                    ->order('p.created_date desc')
                    ->group('p.id');
        
      //  Zend_Debug::dump($sql->assemble());
 
        /*
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
        ->where('p.user_id <> ?',3068)
        ->where('p.user_id <> ?', 5981)
        ->where('p.user_id <> ?', 1)
        ->where('p.status = ?','APPROVED')
        ->order('p.created_date desc')
        ->group('p.id');
             */
       // die();
        
        return $productSql;

     //   return $this->fetchAll($sql);
    }
    
    function myOpenMobileNoApps($chapId)
    {
     
    	$productSql    =   $this->select();
    	$productSql        ->from(array('cp' => 'chap_products'))
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'),'p.id = cp.product_id')
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id', 'pb.name as build_name',  'pb.language_id'))
    	->join(array('pf' => 'build_files'), 'pf.build_id = pb.id', array())
    	->join(array('u' => 'users'), 'u.id = p.user_id', array('u.email'))
    	->where('pb.build_type = ?', 'files')
    	->where('cp.chap_id = ?',$chapId)
    	->where('pb.platform_id = ?', 12)
    	->where('p.deleted <> ?', 1)
    	->where('p.status = ?','APPROVED')
    	->where('p.user_id <> ?',3068)
    	->where('p.user_id <> ?', 5981)
    	->order('p.created_date desc')
    	->group('p.id');

    	/*
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
    	->where('p.user_id <> ?',3068)
    	->where('p.user_id <> ?', 5981)
    	->where('p.user_id <> ?', 1)
    	->where('p.status = ?','APPROVED')
    	->order('p.created_date desc')
    	->group('p.id');
    	 */
    
    
    	return $this->fetchAll($productSql)->count();
    }
    
     /**
     * Returns the attibutes of a particular device
     * @param $deviceId
     * @return device attributes array
     */
    protected function getDeviceAttributes($deviceId)
    {
        $deviceModel        = new Model_Device();
        $deviceAttributes   = $deviceModel->getDeviceAttributes($deviceId);
        $deviceAttrib       = array();

        foreach ($deviceAttributes as $deviceAttribute) 
        {
            $deviceAttrib[$deviceAttribute->device_attribute_definition_id] = $deviceAttribute->value;
        }
        return $deviceAttrib;
    }


//######################################################### Qelasy Fns ###################################################################
//          all qelasy fns bind with the qelasy grades, therefore we use separate queries.
    /**
     * @param $chapId
     * @param $deviceId
     * @param $categoryId
     * @param $grade
     * @param int $limit
     * @param int $offset
     * @return array
     */

    public function getQelasyProductsAll($chapId, $deviceId, $categoryId, $grade, $limit = 10,$offset = 0,$userType)
    {
        $productSql1   = $this->select();
        $productSql1->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.thumbnail','p.price','p.user_id','p.keywords'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql1    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql1->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.deleted != ?',1)
                    ->where('c.id =?',$categoryId)
                    //->orWhere('c.parent_id = ?',$categoryId)
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls')
                    ->group('product_id')
                    ;
        $productSql2   = $this->select();
        $productSql2->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.thumbnail','p.price','p.user_id','p.keywords'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql2    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql2->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.deleted != ?',1)
                    ->where('c.id =?',$categoryId)
                    //->orWhere('c.parent_id = ?',$categoryId)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls')
                    ->group('product_id')
                    ;
        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                        ->order('product_id DESC')
                        ->limit($limit, $offset);
        //echo $allProductsSql->assemble();die();
        return $this->fetchAll($allProductsSql)->toArray();
    }

    /**
     * Returns featured products when device is selected - for Home page of the white label site
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID
     * @param  $categoryId   category Id
     * returns $products array
     */
    public function getQelasyFeaturedProductsbyDevice($chapId, $deviceId, $limit, $grade, $userType)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        //Device detection Method 1
        $productSql1   = $this->select();
        $productSql1->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql1    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql1->where('bd.device_id = ?',$deviceId)
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.featured = ?',1)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id')
                    ;

        //Device detection Method 2
        $productSql2   = $this->select();
        $productSql2->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql2    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql2->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('cp.featured = ?',1)
                    ->where('p.deleted != ?',1)
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id')
                    ;

        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
            ->order('product_id DESC')
            ->limit($limit);
        //echo $allProductsSql->assemble();die();
        return $this->fetchAll($allProductsSql)->toArray();

    }

    /**
     * Returns featured products - for Home page of the white label site
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * returns $products array
     */
    public function getQelasyFeaturedProductIds($chapId, $limit, $grade, $userType)
    {
        $productSql   = $this->select();
        $productSql->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.user_id != ?', 5981)
                    ->where('p.deleted != ?',1)
                    ->where('cp.featured = ?',1)
                    ->order('cp.product_id DESC')
                    ->group('p.id')
                    ->limit($limit)
                    ;
        //echo $productSql->assemble();die();
        return $this->fetchAll($productSql)->toArray();
    }

    public function getQeasyTopCategoryProducts($chapId, $deviceId, $categoryId, $limit, $offset, $grade = null, $userType = null)
    {

        $productSql1   = $this->select();
        $productSql1->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql1    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql1->where('bd.device_id = ?',$deviceId)
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED')
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->where('pc.category_id = ?', $categoryId)
                    ->where('pb.build_type != ?','urls')
                    ->group('sd.product_id')
                    ;

        $productSql2   = $this->select();
        $productSql2->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql2    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql2->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->where('pc.category_id = ?', $categoryId)
                    ->where('pb.build_type != ?','urls')
                    ->group('sd.product_id')
                    ;

        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                    ->limit($limit, $offset);

        //echo $allProductsSql->assemble();die();
        return $this->fetchAll($allProductsSql)->toArray();

    }

    /**
     *
     * Top products
     *
     *
     */
    public function getQelasyTopProductsByDevice($chapId, $deviceId, $dataSet= null, $limit = null, $offset = null, $grade = null, $userType = null)
    {
        $productSql1   = $this->select();
        $productSql1->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'))
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql1    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql1->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->where('pb.build_type != ?','urls')
                    //->where('qgc.grade_id = ?',$grade)
                    ->group('sd.product_id')
                    ;


        $productSql2   = $this->select();
        $productSql2->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'))
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql2    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql2->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->where('pb.build_type != ?','urls')
                    //->where('qgc.grade_id = ?',$grade)
                    ->group('sd.product_id')
                    ;


        if($limit)	{

            $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                ->order('pro_count DESC')
                ->limit($limit, $offset);

        } else {

            $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                ->order('pro_count DESC');

        }
        //echo $allProductsSql->assemble();die();
        if($dataSet)
            return $this->fetchAll($allProductsSql)->toArray();


        return $allProductsSql;

    }

    public function getQelasyFreeProductIdsByDevice($chapId, $deviceId, $limit, $grade, $userType){

        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        $productSql1   = $this->select();
        $productSql1->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql1    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql1->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->where('p.price <= ?',0)
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');

        $productSql2   = $this->select();
        $productSql2->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql2    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql2->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('p.user_id != ?', 5981)
                    ->where('p.price <= ?',0)
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');

        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                                        ->order('RAND(NOW())')
                                        ->limit($limit);
        //echo $allProductsSql->assemble();die();
        return $this->fetchAll($allProductsSql)->toArray();
    }

    public function getQelasyFreeProductIds($chapId, $limit, $grade = null, $userType){
        $productSql   = $this->select();
        $productSql->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'));

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.user_id != ?', 5981)
                    ->where('p.deleted != ?',1)
                    ->where('p.price <= ?',0)
                    ->order('RAND(NOW())')
                    ->limit($limit);

        return $this->fetchAll($productSql)->toArray();
    }

    public function getQelasyPaidProductIdsByDevice($chapId, $deviceId, $limit, $grade = null, $userType){
        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        $productSql1   = $this->select();
        $productSql1->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql1    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql1->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->where('p.price > ?',0)
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');


        $productSql2   = $this->select();
        $productSql2->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql2    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql2->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('p.user_id != ?', 5981)
                    ->where('p.price > ?',0)
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');


        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                    ->order('RAND(NOW())')
                    ->limit($limit);

        return $this->fetchAll($allProductsSql)->toArray();
    }

    public function getQelasyPaidProductIds($chapId, $limit, $grade = null, $userType){
        $productSql   = $this->select();
        $productSql->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.user_id != ?', 5981)
                    ->where('p.deleted != ?',1)
                    ->where('p.price > ?',0)
                    ->order('RAND(NOW())')
                    ->limit($limit);

        return $this->fetchAll($productSql)->toArray();
    }

    public function getQelasyTopRatedProductIdsByDevice($chapId, $deviceId, $limit, $grade = null, $userType){

        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        $productSql1   = $this->select();
        $productSql1->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->join(array('r' => 'ratings'), 'r.product_id = p.id', array('rate_count'=>'COUNT(r.rating)'))
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());
        if($grade != null && !empty($grade)){
            $productSql1        //->join(array('qga' => 'qelasy_grade_apps'),'qga.product_id = cp.product_id AND cp.chap_id = '.$chapId.' AND qga.grade_id = '.$grade,array())
                                ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                                ->where('qgc.grade_id = ?',$grade)
                                ->where('qgc.status = ?',1)
                                ;
        } else {
            //$productSql1    ->join(array('cc' => 'chap_categories'),'cc.category_id = c.id OR cc.category_id = c.parent_id',array());
        }
        $productSql1->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->where('pb.build_type != ?','urls')
                    ->group('r.product_id');

        $productSql2   = $this->select();
        $productSql2->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->join(array('r' => 'ratings'), 'r.product_id = p.id', array('rate_count'=>'COUNT(r.rating)'))
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());
        if($grade != null && !empty($grade)){
            $productSql2    //->join(array('qga' => 'qelasy_grade_apps'),'qga.product_id = cp.product_id AND cp.chap_id = '.$chapId.' AND qga.grade_id = '.$grade,array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ;
        } else {
            //$productSql2    ->join(array('cc' => 'chap_categories'),'cc.category_id = c.id OR cc.category_id = c.parent_id',array());
        }
        $productSql2->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('p.user_id != ?', 5981)
                    ->where('pb.build_type != ?','urls')
                    ->group('r.product_id');

        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                        ->order('rate_count DESC')
                        ->limit($limit);

        return $this->fetchAll($allProductsSql)->toArray();
    }

    public function getQelasyTopRatedProductIds($chapId, $limit, $grade = null, $userType){

        $productSql   = $this->select();
        $productSql->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('r' => 'ratings'), 'r.product_id = p.id', array('rate_count'=>'COUNT(r.rating)'))
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());
        if($grade != null && !empty($grade)){
            $productSql     //->join(array('qga' => 'qelasy_grade_apps'),'qga.product_id = cp.product_id AND cp.chap_id = '.$chapId.' AND qga.grade_id = '.$grade,array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ;
        } else {
            //$productSql    ->join(array('cc' => 'chap_categories'),'cc.category_id = c.id OR cc.category_id = c.parent_id',array());
        }
        $productSql ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.user_id != ?', 5981)
                    ->where('p.deleted != ?',1)
                    ->order('rate_count DESC')
                    ->group('r.product_id')
                    ->limit($limit);

        return $this->fetchAll($productSql)->toArray();
    }

    public function getQelasyBanneredProductsbyDevice($chapId, $deviceId, $limit, $category, $grade, $userType){
        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        //Device detection Method 1
        $productSql1   = $this->select();
        $productSql1->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql1    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql1->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('cp.is_banner = ?',1)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');



        //Device detection Method 2
        $productSql2   = $this->select();
        $productSql2->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql2    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql2->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('cp.is_banner = ?',1)
                    ->where('p.deleted != ?',1)
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');

        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                                ->order('product_id DESC')
                                ->limit($limit);

        return $this->fetchAll($allProductsSql)->toArray();
    }

    public function getQelasyBanneredProductIds($chapId, $limit, $grade,$userType){
        $productSql   = $this->select();
        $productSql ->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.user_id != ?', 5981)
                    ->where('p.deleted != ?',1)
                    ->where('cp.is_banner = ?',1)
                    ->order('product_id DESC')
                    ->limit($limit);


        return $this->fetchAll($productSql)->toArray();
    }

    public  function getQelasyNewestProductIdsByDevice($chapId, $deviceId, $limit, $grade, $userType){

        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        $productSql1   = $this->select();
        $productSql1->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql1    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql1->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');


        $productSql2   = $this->select();
        $productSql2->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql2    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql2->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('p.user_id != ?', 5981)
                    ->where('pb.build_type != ?','urls')
                    ->group('p.id');


        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
            ->order('product_id DESC')
            ->limit($limit);
        //echo $allProductsSql->assemble();die();
        return $this->fetchAll($allProductsSql)->toArray();

    }

    public function getQelasyNewestProductIds($chapId, $limit, $grade, $userType){

        $productSql   = $this->select();
        $productSql->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array());

        if($userType){  //user type has values [1,2]
            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
        } else {    //user type has no value
            $userTypeJoin = '' ;
        }

        if($grade){ //grade has values [1,2,3,...]
            $gradeJoin = ' AND qgc.grade_id = '.$grade ;
        } else {    //grade has no value
            $gradeJoin = '' ;
        }

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql    ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;
        }

        $productSql ->where('p.status = ?','APPROVED')
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.user_id != ?', 5981)
                    ->where('p.deleted != ?',1)
                    ->order('product_id DESC')
                    ->limit($limit);

        return $this->fetchAll($productSql)->toArray();

    }
}