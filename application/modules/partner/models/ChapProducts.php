<?php

class Partner_Model_ChapProducts extends Zend_Db_Table_Abstract
{
    protected $_name = 'chap_products';
    protected $_id = 'id';
    
    
    public function getProductCountByChap($chapId,$appId)
    {        
        $sql = $this->select();
        $sql->from($this->_name,array('COUNT(*) AS num'))
            ->where('chap_id = ?',$chapId)
            ->where('product_id = ?',$appId);        
         
        return $this->fetchRow($sql)->num;
        
    }
    
    
    public function getNewestProductIds($chapId,$limit, $grade = null, $userType = null)
    {
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'NEWEST_APPS_'.$chapId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }
        
        //if not cached we do query
        $userTypeJoin = ($userType) ? ' AND qgc.qelasy_user_type = '.$userType : '';

        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'));

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;

            if($grade != null && !empty($grade)){
                $productSql      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)                      
                    ->order('cp.product_id DESC')
                    ->limit($limit);
        //echo $productSql->assemble();die();
        $results = $this->fetchAll($productSql)->toArray();
        $cache->set($results, $key, 3600);
        return $results;
    }
    
    
    public function getNewestProductIdsByDevice($chapId,$deviceId,$limit, $grade = null, $userType = null)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'NEWEST_APPS_WITH_DEVICE'.$chapId.'_'.$deviceId;
        if (($results = $cache->get($key)) !== false)
        {
            //return $results;
        }
        
        //if not cached we do query
        $userTypeJoin = ($userType) ? ' AND qgc.qelasy_user_type = '.$userType : '';

        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array());

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql1    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql1      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql1->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->group('p.id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array());

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql2    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql2      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql2->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->group('p.id');
                     
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
        }
        else
        {
            $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
            
        }
        
        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('product_id DESC')
                           ->limit($limit);

        /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            echo $allProductsSql->assemble();die();
        }*/

        $results = $this->fetchAll($allProductsSql)->toArray(); 
        $cache->set($results, $key, 3600);
        return $results;
    }
    
    public function getFreeProductIds($chapId,$limit, $grade = null, $userType = null)
    {
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'FREE_APPS_'.$chapId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }
        
        //if not cached we do query
        $userTypeJoin = ($userType) ? ' AND qgc.qelasy_user_type = '.$userType : '';

        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'));

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                ->where('qgc.status = ?',1)
                ->where('c.parent_id != ?',0)
            ;

            if($grade != null && !empty($grade)){
                $productSql      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)
                    ->where('p.price <= ?',0)
                    ->order('RAND(NOW())')
                    ->limit($limit);
                    
        $results = $this->fetchAll($productSql)->toArray();
        $cache->set($results, $key, 3600);
        return $results;
    }
    
    
    public function getFreeProductIdsByDevice($chapId,$deviceId,$limit, $grade = null, $userType = null)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'FREE_APPS_WITH_DEVICE'.$chapId.'_'.$deviceId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }
        
        //if not cached we do query
        $userTypeJoin = ($userType) ? ' AND qgc.qelasy_user_type = '.$userType : '';

        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array());

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql1     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql1      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql1->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.price <= ?',0)
                    ->group('p.id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array());

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql2     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql2      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql2->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.price <= ?',0)
                    ->group('p.id');
                     
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
        }
        else
        {
            $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
            
        }
        
        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('RAND(NOW())')
                           ->limit($limit);

        /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            Zend_Debug::dump($allProductsSql->assemble());die();
        }*/


        $results = $this->fetchAll($allProductsSql)->toArray(); 
        $cache->set($results, $key, 3600);
        return $results;
    }
    
    
    public function getNexvaProductIdsByDevice($chapId,$deviceId,$limit, $grade = null, $userType = null)
    {
    	$deviceAttrib = $this->getDeviceAttributes($deviceId);
    
    	//if results cached we return it straightaway
    	$cache  = Zend_Registry::get('cache');
    	$key    = 'NEXVA_APPS_WITH_DEVICE'.$chapId.'_'.$deviceId;
    	if (($results = $cache->get($key)) !== false)
    	{
    		return $results;
    	}
    
    	//if not cached we do query
    	$userTypeJoin = ($userType) ? ' AND qgc.qelasy_user_type = '.$userType : '';
    
    	$productSql1   = $this->select();
    	$productSql1->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array());
    
    	//this is for qelasy
    	if($userType){
    
    		$userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
    
    		$productSql1     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
    		->join(array('c'=>'categories'),'pc.category_id = c.id',array())
    		->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
    		->where('qgc.status = ?',1)
    		->where('c.parent_id != ?',0)
    		;
    
    		if($grade != null && !empty($grade)){
    			$productSql1      ->where('qgc.grade_id = ?',$grade);
    		}
    
    	}
    
    	$productSql1->where('cp.chap_id = ?',$chapId)
    	->where('cp.nexva = 1')
    	->where('bd.device_id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->where('pb.device_selection_type = ?', 'CUSTOM')
    	->where('p.user_id != ?', 5981)
    	->group('p.id');
    
    	 
    	$productSql2   = $this->select();
    	$productSql2->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
    	->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
    	->join(array('d' => 'devices'), 'd.id = da.device_id', array());
    
    	//this is for qelasy
    	if($userType){
    
    		$userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
    
    		$productSql2     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
    		->join(array('c'=>'categories'),'pc.category_id = c.id',array())
    		->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
    		->where('qgc.status = ?',1)
    		->where('c.parent_id != ?',0)
    		;
    
    		if($grade != null && !empty($grade)){
    			$productSql2      ->where('qgc.grade_id = ?',$grade);
    		}
    
    	}
    
    	$productSql2->where('cp.chap_id = ?',$chapId)
    	->where('d.id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->where('pb.device_selection_type != ?', 'CUSTOM')
    	->where('p.user_id != ?', 5981)
    	->where('cp.nexva =1')
    	->group('p.id');
    	 
    	if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
    	{
    		$productSql2->where('pda.value = ?', $deviceAttrib[3]);
    	}
    	else
    	{
    		$productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
    
    	}
    
    	$allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
    	->order('RAND(NOW())')
    	->limit($limit);
    
    	/*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
    	 Zend_Debug::dump($allProductsSql->assemble());die();
    	}*/
    
    
    	$results = $this->fetchAll($allProductsSql)->toArray();
    	$cache->set($results, $key, 3600);
    	return $results;
    }
    
    
    public function getPaidProductIds($chapId,$limit, $grade = null, $userType = null)
    {
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'PAID_APPS_'.$chapId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }
        
        //if not cached we do query
        $userTypeJoin = ($userType) ? ' AND qgc.qelasy_user_type = '.$userType : '';

        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'));

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)   
                    ->where('p.price > ?',0)
                    ->order('RAND(NOW())')
                    ->limit($limit);
                    
        $results = $this->fetchAll($productSql)->toArray();
        $cache->set($results, $key, 3600);
        return $results;
    }
    
    
    public function getPaidProductIdsByDevice($chapId,$deviceId,$limit, $grade = null, $userType = null)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'PAID_APPS_WITH_DEVICE'.$chapId.'_'.$deviceId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }
        
        //if not cached we do query
        $userTypeJoin = ($userType) ? ' AND qgc.qelasy_user_type = '.$userType : '';

        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array());

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql1    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql1      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql1->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.price > ?',0)
                    ->group('p.id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array());

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql2    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql2      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql2->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.price > ?',0)
                    ->group('p.id');
                     
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
        }
        else
        {
            $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
            
        }
        
        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('RAND(NOW())')
                           ->limit($limit);
         
        $results = $this->fetchAll($allProductsSql)->toArray();
        $cache->set($results, $key, 3600);
        return $results;
        
    }
    
    public function getTopProductIds($chapId,$limit, $grade = null, $userType = null)
    {
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'TOP_APPS_'.$chapId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }
        
        //if not cached we do query
        $userTypeJoin = ($userType) ? ' AND qgc.qelasy_user_type = '.$userType : '';

        $productSql   = $this->select(); 
        $productSql ->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'));

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)
                    ->order('pro_count DESC')
                    ->group('sd.product_id')
                    ->limit($limit);        
                    
        $results = $this->fetchAll($productSql)->toArray(); 
        $cache->set($results, $key, 3600);
        return $results;
    }
    
    
    public function getTopProductIdsByDevice($chapId,$deviceId,$limit,$grade = null, $userType = null)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'TOP_APPS_WITH_DEVICE'.$chapId.'_'.$deviceId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }
        
        //if not cached we do query
        $userTypeJoin = ($userType) ? ' AND qgc.qelasy_user_type = '.$userType : '';

        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array()) 
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'));

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql1     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql1      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql1->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)                     
                    ->group('sd.product_id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array()) 
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'));

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql2     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
            ;

            if($grade != null && !empty($grade)){
                $productSql2      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql2->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)                     
                    ->group('sd.product_id');
                     
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
        }
        else
        {
            $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);

        }
        
        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('pro_count DESC')
                           ->limit($limit);
        //echo $allProductsSql->assemble();die();
        $results = $this->fetchAll($allProductsSql)->toArray(); 
        $cache->set($results, $key, 3600);
        return $results;
    }
    
    
    public function getTopProductIdsByApple($chapId,$deviceId,$limit,$grade = null, $userType = null)
    {
    	$deviceAttrib = $this->getDeviceAttributes($deviceId);
    
    	//if results cached we return it straightaway
    	$cache  = Zend_Registry::get('cache');
    	$key    = 'TOP_APPS_WITH_APPLE'.$chapId.'_'.$deviceId;
    	if (($results = $cache->get($key)) !== false)
    	{
    		return $results;
    	}
    
    	//if not cached we do query
    	$userTypeJoin = ($userType) ? ' AND qgc.qelasy_user_type = '.$userType : '';
    
    	$productSql1   = $this->select();
    	$productSql1->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
    	->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'));
    
    	//this is for qelasy
    	if($userType){
    
    		$userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
    
    		$productSql1     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
    		->join(array('c'=>'categories'),'pc.category_id = c.id',array())
    		->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
    		->where('qgc.status = ?',1)
    		->where('c.parent_id != ?',0)
    		;
    
    		if($grade != null && !empty($grade)){
    			$productSql1      ->where('qgc.grade_id = ?',$grade);
    		}
    
    	}
    
    	$productSql1->where('cp.chap_id = ?',$chapId)
    	->where('bd.device_id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->where('p.price == 0')
    	->where('pb.device_selection_type = ?', 'CUSTOM')
    	->where('p.user_id != ?', 5981)
    	->group('sd.product_id');
    
    	 
    	$productSql2   = $this->select();
    	$productSql2->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
    	->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
    	->join(array('d' => 'devices'), 'd.id = da.device_id', array())
    	->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'));
    
    	//this is for qelasy
    	if($userType){
    
    		$userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;
    
    		$productSql2     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
    		->join(array('c'=>'categories'),'pc.category_id = c.id',array())
    		->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
    		->where('qgc.status = ?',1)
    		->where('c.parent_id != ?',0)
    		;
    
    		if($grade != null && !empty($grade)){
    			$productSql2      ->where('qgc.grade_id = ?',$grade);
    		}
    
    	}
    
    	$productSql2->where('cp.chap_id = ?',$chapId)
    	->where('d.id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->where('p.price == 0')
    	->where('pb.device_selection_type != ?', 'CUSTOM')
    	->where('p.user_id != ?', 5981)
    	->group('sd.product_id');
    	 
    	if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
    	{
    		$productSql2->where('pda.value = ?', $deviceAttrib[3]);
    	}
    	else
    	{
    		$productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
    
    	}
    
    	$allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
    	->order('pro_count DESC')
    	->limit($limit);
    	//echo $allProductsSql->assemble();die();
    	$results = $this->fetchAll($allProductsSql)->toArray();
    	$cache->set($results, $key, 3600);
    	return $results;
    }
    
    
    public function getMostViewedProductIds($chapId,$limit, $grade = null, $userType = null)
    {      
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'MOSTVIEWED_APPS_'.$chapId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }
        
        //if not cached we do query
        $userTypeJoin = ($userType) ? ' AND qgc.qelasy_user_type = '.$userType : '';

        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->join(array('sp' => 'statistics_products'), 'sp.product_id = p.id', array('pro_count'=>'COUNT(sp.product_id)'));

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)
                    ->order('pro_count DESC')
                    ->group('sp.product_id')
                    ->limit($limit);
        //echo $productSql->assemble();die();
        $results = $this->fetchAll($productSql)->toArray(); 
        $cache->set($results, $key, 3600);
        return $results;
    }
    
    
    public function getMostViewedProductIdsByDevice($chapId,$deviceId,$limit, $grade = null, $userType = null)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'MOSTVIEWED_APPS_WITH_DEVICE'.$chapId.'_'.$deviceId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }
        
        //if not cached we do query
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array()) 
                    ->join(array('sp' => 'statistics_products'), 'sp.product_id = p.id', array('pro_count'=>'COUNT(sp.product_id)'));

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql1    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql1      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql1->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->group('sp.product_id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array()) 
                    ->join(array('sp' => 'statistics_products'), 'sp.product_id = p.id', array('pro_count'=>'COUNT(sp.product_id)'));

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql2    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql2      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql2->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->group('sp.product_id');
                     
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
        }
        else
        {
            $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);

        }
        
        
        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('pro_count DESC')
                           ->limit($limit);
         
        $results = $this->fetchAll($allProductsSql)->toArray(); 
        $cache->set($results, $key, 3600);
        return $results;
    }
    
     /**
     * Returns featured products - for Home page of the white label site
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * returns $products array
     */ 
    public function getFeaturedProductIds($chapId,$limit,$grade = null, $userType = null)
    {
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'FEATURED_APPS_'.$chapId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }
        
        //if not cached we do query
        $userTypeJoin = ($userType) ? ' AND qgc.qelasy_user_type = '.$userType : '';

        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ;

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)  
                    ->where('cp.featured = ?',1)
                    ->order('cp.product_id DESC')
                    ->group('p.id')
                    ->limit($limit);
        //echo $productSql->assemble();die();
        $results = $this->fetchAll($productSql)->toArray();
        $cache->set($results, $key, 3600);
        return $results;
    }
    
    
    /**
     * Returns featured products when device is selected - for Home page of the white label site
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID 
     * @param  $categoryId   category Id
     * returns $products array
     */   
    public function getFeaturedProductsbyDevice($chapId,$deviceId,$limit, $categoryId = null, $grade = null, $userType = null)
    {

        $deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'FEATURED_APPS_WITH_DEVICE'.$chapId.'_'.$deviceId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }
        
        //if not cached we do query
        $userTypeJoin = ($userType) ? ' AND qgc.qelasy_user_type = '.$userType : '';

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

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql1     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql1      ->where('qgc.grade_id = ?',$grade);
            }

        }
                
        $productSql1->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')  
                    ->where('cp.featured = ?',1)
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
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

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql2     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql2      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql2->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('cp.featured = ?',1)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->group('p.id');
                     
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
            
        }
        else
        {
           $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
        }
        
        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('product_id DESC')
                           ->limit($limit);
        //echo $allProductsSql->assemble();die();
        $results = $this->fetchAll($allProductsSql)->toArray(); 
        $cache->set($results, $key, 3600);
        return $results;
        
    }
    
    
    /**
     * Returns bannerd products - for Home page of the white label site
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * returns $products array
     */ 
    public function getBanneredProductIds($chapId,$limit,$grade = null, $userType = null)
    {

        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'BANNERED_APPS_'.$chapId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }

        //if not cached we do query
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail','p.google_id','p.apple_id'))
                    ;

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql      ->where('qgc.grade_id = ?',$grade);
            }

        }

        /*else {
            $productSql ->join(array('pc'=>'product_categories'),'pc.product_id = cp.product_id')
                        ->join(array('cc'=>'chap_categories'),'cc.category_id = pc.category_id')
                        ->where('cc.chap_id = ?',$chapId)
                        ->where('cc.status = ?',1)
                        ;
        }*/

        $productSql ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)  
                    ->where('cp.is_banner = ?',1)
                    ->order('cp.product_id DESC')
                    ->group('cp.product_id')
                    ->limit($limit);
        //echo $productSql->assemble();die();
        $results = $this->fetchAll($productSql)->toArray();
        $cache->set($results, $key, 3600);
        return $results;
    }    
   
    /**
     * Returns bannerd products when device is selected - for Home page of the white label site
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID 
     * @param  $categoryId   category Id
     * returns $products array
     */
    public function getBanneredProductsbyDevice($chapId, $deviceId, $limit = null, $categoryId = null, $grade = null, $userType = null)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'BANNERED_APPS_WITH_DEVICE'.$chapId.'_'.$deviceId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }
        
        //if not cached we do query
        $userTypeJoin = ($userType) ? ' AND qgc.qelasy_user_type = '.$userType : '';

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

        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql1    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql1      ->where('qgc.grade_id = ?',$grade);
            }

        }
                
        $productSql1->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')  
                    ->where('cp.is_banner = ?',1)
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
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
        //this is for qelasy
        if($userType){

            $userTypeJoin = ' AND qgc.qelasy_user_type = '.$userType ;

            $productSql2    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;

            if($grade != null && !empty($grade)){
                $productSql2      ->where('qgc.grade_id = ?',$grade);
            }

        }

        $productSql2->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('cp.is_banner = ?',1)
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->group('p.id'); 
        
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {            
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
        }
        else
        {
            $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
        }

        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('product_id DESC')
                           ->limit($limit);
        //echo $allProductsSql->assemble();die();
        $results = $this->fetchAll($allProductsSql)->toArray();
        $cache->set($results, $key, 3600);
        return $results;
        
    }
    
    
    /*public function getBanneredProductssbyDevice($chapId, $deviceId, $limit = null, $categoryId = null)
    {
    	$deviceAttrib = $this->getDeviceAttributes($deviceId);
        
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'FEATURED_APPS_WITH_DEVICE'.$chapId.'_'.$deviceId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }
        
        //if not cached we do query
        
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
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->group('p.id');
                     
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
            
        }
        else
        {
           $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
        }
        
        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('product_id DESC')
                           ->limit($limit);
        
        Zend_Debug::dump($allProductsSql->assemble());
    	
    	die();
    	
    	$results = $this->fetchAll($allProductsSql)->toArray();
    	$cache->set($results, $key, 3600);
    	return $results;
    
    }*/
    
    
    
    public function getFreeProducts($chapId, $deviceId = null, $grade = null)
    {
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'FREE_PRODUCTS_'.$chapId;
        if (($productSql = $cache->get($key)) !== false)
        {
            return $productSql;
        }

        //if not cached we do query
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                    ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                    ->join(array('cc'=>'chap_categories'),'cc.category_id = pc.category_id', array())
                    ;

        if($grade != null && !empty($grade)){
            $productSql     ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;
        }
        $productSql ->where('cp.chap_id = ?',$chapId)
                    ->where('cc.chap_id = ?',$chapId)
                    ->where('cc.status = ?',1)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)
                    ->where('p.price <= ?',0)
                    ->where('c.parent_id != ?',0)
                    ->group('p.id')
                    ->order('RAND(NOW())');

        $cache->set($productSql, $key, 3600);
        return $productSql;
    }
    
    
    public function getNexvaProducts($chapId, $deviceId = null, $grade = null)
    {
    	//if results cached we return it straightaway
    	$cache  = Zend_Registry::get('cache');
    	$key    = 'NEXVA_PRODUCTS_'.$chapId;
    	if (($productSql = $cache->get($key)) !== false)
    	{
    		return $productSql;
    	}
    
    	//if not cached we do query
    	$productSql   = $this->select();
    	$productSql->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
    	->join(array('c'=>'categories'),'pc.category_id = c.id',array())
    	->join(array('cc'=>'chap_categories'),'cc.category_id = pc.category_id', array())
    	;
    
    	$productSql ->where('cp.chap_id = ?',$chapId)
    	->where('cc.chap_id = ?',$chapId)
    	->where('cc.status = ?',1)
    	->where('p.status = ?','APPROVED')
    	->where('p.user_id != ?', 5981)
    	->where('p.deleted != ?',1)
    	->where('cp.nexva = 1')
    	->where('c.parent_id != ?',0)
    	->group('p.id');
    
    	$cache->set($productSql, $key, 3600);
    	return $productSql;
    }
    
    public function getPremiumProducts($chapId, $deviceId = null, $grade = null)
    {
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'PREMIUM_PRODUCTS_'.$chapId;
        if (($productSql = $cache->get($key)) !== false)
        {
            return $productSql;
        }

        //if not cached we do query
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                    ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                    ->join(array('cc'=>'chap_categories'),'cc.category_id = pc.category_id', array())
                    ;

        if($grade != null && !empty($grade)){
            $productSql     ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;
        }
        $productSql ->where('cp.chap_id = ?',$chapId)
                    ->where('cc.chap_id = ?',$chapId)
                    ->where('cc.status = ?',1)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)   
                    ->where('p.price > ?',0)
                    ->where('c.parent_id != ?',0)
                    ->group('p.id')
                    ->order('RAND(NOW())');

        $cache->set($productSql, $key, 3600);
        return $productSql;
    }
    
    
    public function getFeaturedProducts($chapId, $deviceId = null, $grade = null, $userType = null)
    {
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'FEATURED_PRODUCTS_'.$chapId;
        if (($productSql = $cache->get($key)) !== false)
        {
            return $productSql;
        }

        //if not cached we do query
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                    ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                    ->join(array('cc'=>'chap_categories'),'cc.category_id = pc.category_id', array())
                    ;

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

        /*if($grade != null && !empty($grade)){
            $productSql     ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;
        }*/

        if($grade || $userType){    //user_type & grade doesn't have values, no need to join qgc table
            $productSql     ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id '.$userTypeJoin.$gradeJoin,array())
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;
        }

        $productSql ->where('cp.chap_id = ?',$chapId)
                    ->where('cc.chap_id = ?',$chapId)
                    ->where('cc.status = ?',1)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)  
                    ->where('cp.featured = ?',1)
                    ->where('c.parent_id != ?',0)
                    ->group('p.id')
                    ->order('cp.product_id DESC');

        //echo $productSql->assemble();die();

        $cache->set($productSql, $key, 3600);
        return $productSql;
    }
    
    
    public function getNewestProducts($chapId, $deviceId = null, $grade = null, $userType = null)
    {

        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'NEWEST_PRODUCTS_'.$chapId;
        if (($productSql = $cache->get($key)) !== false)
        {
           //return $productSql;
        }

        //if not cached we do query
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                    ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                    ->join(array('cc'=>'chap_categories'),'cc.category_id = pc.category_id', array())
                    ;

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
        
        $productSql ->where('cp.chap_id = ?',$chapId)
                    ->where('cc.chap_id = ?',$chapId)
                    ->where('cc.status = ?',1)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)
                    ->where('c.parent_id != ?',0)
                    ->group('p.id')
                    ->order('cp.product_id DESC');

        //echo $productSql->assemble();die();
        $cache->set($productSql, $key, 3600);
        return $productSql;
    }
    
    public function getTopProducts($chapId, $deviceId = null, $grade = null)
    {
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'TOP_PRODUCTS_'.$chapId;
        if (($productSql = $cache->get($key)) !== false)
        {
            return $productSql;
        }

        //if not cached we do query
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'))
                    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                    ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                    ->join(array('cc'=>'chap_categories'),'cc.category_id = pc.category_id', array())
                    ;
        if($grade != null && !empty($grade)){
            $productSql     ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;
        }
        $productSql ->where('cp.chap_id = ?',$chapId)
                    ->where('cc.chap_id = ?',$chapId)
                    ->where('cc.status = ?',1)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)
                    ->where('c.parent_id != ?',0)
                    ->order('pro_count DESC')
                    ->group('sd.product_id');

        $cache->set($productSql, $key, 3600);
        return $productSql;
    }
    
    
    public function getMostViewdProduct($chapId, $deviceId = null, $grade = null)
    {
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'MOSTVIEWED_PRODUCTS_'.$chapId;
        if (($productSql = $cache->get($key)) !== false)
        {
            return $productSql;
        }

        //if not cached we do query
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                    ->join(array('sp' => 'statistics_products'), 'sp.product_id = p.id', array('pro_count'=>'COUNT(sp.product_id)'))
                    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                    ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                    ->join(array('cc'=>'chap_categories'),'cc.category_id = pc.category_id', array())
                    ;
        if($grade != null && !empty($grade)){
            $productSql     ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;
        }
        $productSql ->where('cp.chap_id = ?',$chapId)
                    ->where('cc.chap_id = ?',$chapId)
                    ->where('cc.status = ?',1)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.deleted != ?',1)
                    ->where('c.parent_id != ?',0)
                    ->order('pro_count DESC')
                    ->group('sp.product_id');

        $cache->set($productSql, $key, 3600);
        return $productSql;
    }
    
        
    public function getProductsByCategory($chapId, $category)
    {
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'CATEGORY_APPS_'.$chapId.'_'.$category;
        if (($productSql = $cache->get($key)) !== false)
        {
            return $productSql;
        }

        //if not cached we do query
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
                    ->order('product_id DESC')
                    ->group('cp.product_id');

        $cache->set($productSql, $key, 3600);
        return $productSql;
    }
    
    public function getProductsByCategoryAndDevice($chapId, $category, $deviceId)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'CATEGORY_APPS_WITH_DEVICE'.$chapId.'_'.$deviceId.'_'.$category;
        if (($allProductsSql = $cache->get($key)) !== false)
        {
            return $allProductsSql;
        }

        //if not cached we do query
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail')) 
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('pc.category_id = ?', $category);
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail')) 
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
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
                    ->where('pc.category_id = ?', $category);
                     
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
            
        }
        else
        {
           $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
        }
        
        
        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('product_id DESC');

        $cache->set($allProductsSql, $key, 3600);
        return $allProductsSql;
        
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
    
    public function getFreeProductsByDevice($chapId,$deviceId, $grade = null)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'FREE_PRODUCTS_WITH_DEVICE'.$chapId.'_'.$deviceId;
        if (($allProductsSql = $cache->get($key)) !== false)
        {
            return $allProductsSql;
        }

        //if not cached we do query
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array());
        if($grade != null && !empty($grade)){
            $productSql1    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;
        }
        $productSql1 ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.price <= ?',0);
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array());
        if($grade != null && !empty($grade)){
            $productSql2    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;
        }
        $productSql2 ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.price <= ?',0);
                     
        
         if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
            
        }
        else
        {
           $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
        }
        
        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('RAND(NOW())');

        $cache->set($allProductsSql, $key, 3600);
        return $allProductsSql;
        
    }
    
    
    
    public function getNexvaProductsByDevice($chapId,$deviceId, $grade = null)
    {
    	$deviceAttrib = $this->getDeviceAttributes($deviceId);
    
    	//if results cached we return it straightaway
    	$cache  = Zend_Registry::get('cache');
    	$key    = 'NEXVA_PRODUCTS_WITH_DEVICE'.$chapId.'_'.$deviceId;
    	if (($allProductsSql = $cache->get($key)) !== false)
    	{
    		return $allProductsSql;
    	}
    
    	//if not cached we do query
    	$productSql1   = $this->select();
    	$productSql1->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array());
    	if($grade != null && !empty($grade)){
    		$productSql1    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
    		->join(array('c'=>'categories'),'pc.category_id = c.id',array())
    		->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
    		->where('qgc.grade_id = ?',$grade)
    		->where('qgc.status = ?',1)
    		->where('c.parent_id != ?',0)
    		;
    	}
    	$productSql1 ->where('cp.chap_id = ?',$chapId)
    	->where('cp.nexva = 1')
    	->where('bd.device_id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->where('pb.device_selection_type = ?', 'CUSTOM')
    	->where('p.user_id != ?', 5981);

    
    	 
    	$productSql2   = $this->select();
    	$productSql2->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
    	->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
    	->join(array('d' => 'devices'), 'd.id = da.device_id', array());
    	if($grade != null && !empty($grade)){
    		$productSql2    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
    		->join(array('c'=>'categories'),'pc.category_id = c.id',array())
    		->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
    		->where('qgc.grade_id = ?',$grade)
    		->where('qgc.status = ?',1)
    		->where('c.parent_id != ?',0)
    		;
    	}
    	$productSql2 ->where('cp.chap_id = ?',$chapId)
    	->where('cp.nexva = 1')
    	->where('d.id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->where('pb.device_selection_type != ?', 'CUSTOM')
    	->where('p.user_id != ?', 5981);
    	 
    
    	if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
    	{
    		$productSql2->where('pda.value = ?', $deviceAttrib[3]);
    
    	}
    	else
    	{
    		$productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
    	}
    
    	$allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
    	->order('RAND(NOW())');
    
    	$cache->set($allProductsSql, $key, 3600);
    	return $allProductsSql;
    
    }
    
    
    public function getPremiumProductsByDevice($chapId,$deviceId, $grade = null)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'PREMIUM_PRODUCTS_WITH_DEVICE_'.$chapId.'_'.$deviceId;
        if (($allProductsSql = $cache->get($key)) !== false)
        {
            return $allProductsSql;
        }

        //if not cached we do query
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array());
        if($grade != null && !empty($grade)){
            $productSql1    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;
        }
        $productSql1->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.price > ?',0);
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array());
        if($grade != null && !empty($grade)){
            $productSql2    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;
        }
        $productSql2->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->where('p.price > ?',0);
                     
        
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
            
        }
        else
        {
           $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
        }
        
        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('RAND(NOW())');

        $cache->set($allProductsSql, $key, 3600);
        return $allProductsSql;
        
    }
    
    public function getTopProductsByDevice($chapId, $deviceId, $dataSet= null, $limit = null, $offset = null)
    {
        
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'TOP_PRODUCTS_BY_WITH_DEVICE_'.$chapId.'_'.$deviceId;
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
                    ->group('sd.product_id');
                     
        
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
            
        }
        else
        {
           $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
        }
        
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
         
         $cache->set($allProductsSql, $key, 3600);
        
    }
    
    
    public function getNewestProductsByDevice($chapId,$deviceId, $grade = null, $userType = null)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'NEWEST_PRODUCTS_WITH_DEVICE_'.$chapId.'_'.$deviceId;
        if (($allProductsSql = $cache->get($key)) !== false)
        {
            return $allProductsSql;
        }

        //if not cached we do query
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                    ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                    ->join(array('cc'=>'chap_categories'),'cc.category_id = pc.category_id', array())
                    ;

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

        $productSql1->where('cp.chap_id = ?',$chapId)
                    ->where('cc.chap_id = ?',$chapId)
                    ->where('cc.status = ?',1)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->where('c.parent_id != ?',0);
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                     ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                     ->join(array('cc'=>'chap_categories'),'cc.category_id = pc.category_id', array())
                    ;

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

        $productSql2->where('cp.chap_id = ?',$chapId)
                    ->where('cc.chap_id = ?',$chapId)
                    ->where('cc.status = ?',1)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)
                    ->where('c.parent_id != ?',0);
                     
        
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
            
        }
        else
        {
           $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
        }
        
        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                            ->group('p.id')
                           ->order('product_id DESC');

        $cache->set($allProductsSql, $key, 3600);
        return $allProductsSql;
        
    }
    
    public function getFeatureProductsbyDevice($chapId,$deviceId, $grade = null, $userType = null)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'FEATURED_PRODUCTS_WITH_DEVICE_'.$chapId.'_'.$deviceId;
        if (($allProductsSql = $cache->get($key)) !== false)
        {
            return $allProductsSql;
        }

        //if not cached we do query
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                    ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                    ->join(array('cc'=>'chap_categories'),'cc.category_id = pc.category_id', array())
                    ;

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

        $productSql1 ->where('cp.chap_id = ?',$chapId)
                    ->where('cc.chap_id = ?',$chapId)
                    ->where('cc.status = ?',1)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')  
                    ->where('cp.featured = ?',1)
                    ->where('p.deleted != ?',1)
                    ->where('c.parent_id != ?',0)
                    ->where('pb.device_selection_type = ?', 'CUSTOM');

         $productSql2   = $this->select();
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                     ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                     ->join(array('cc'=>'chap_categories'),'cc.category_id = pc.category_id', array())
                     ;

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

        $productSql2->where('cp.chap_id = ?',$chapId)
                    ->where('cc.chap_id = ?',$chapId)
                    ->where('cc.status = ?',1)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('cp.featured = ?',1)
                    ->where('p.deleted != ?',1)
                    ->where('c.parent_id != ?',0)
                    ->where('pb.device_selection_type != ?', 'CUSTOM');
              
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
            
        }
        else
        {
           $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
        }
        
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                            ->group('p.id')
                           ->order('product_id DESC');

        $cache->set($allProductsSql, $key, 3600);
        return $allProductsSql;
        
    }
    
    public function getTopProductsIdByDevice($chapId,$deviceId, $grade = null)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'TOP_PRODUCTS_WITH_DEVICE_'.$chapId.'_'.$deviceId;
        if (($allProductsSql = $cache->get($key)) !== false)
        {
            return $allProductsSql;
        }

        //if not cached we do query
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array()) 
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'));
        if($grade != null && !empty($grade)){
            $productSql1     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;
        }
        $productSql1 ->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)                     
                    ->group('sd.product_id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array()) 
                    ->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array('pro_count'=>'COUNT(sd.product_id)'));
        if($grade != null && !empty($grade)){
            $productSql2     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;
        }
        $productSql2 ->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981)                     
                    ->group('sd.product_id');
                     
        
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
            
        }
        else
        {
           $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
        }
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('pro_count DESC');

        $cache->set($allProductsSql, $key, 3600);
        return $allProductsSql;
        
    }
    
    
    public function getMostViewedProductsByDevice($chapId,$deviceId, $grade = null)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'MOSTVIEWED_PRODUCTS_WITH_DEVICE_'.$chapId.'_'.$deviceId;
        if (($allProductsSql = $cache->get($key)) !== false)
        {
            return $allProductsSql;
        }

        //if not cached we do query
        $productSql1   = $this->select(); 
        $productSql1->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array()) 
                    ->join(array('sp' => 'statistics_products'), 'sp.product_id = p.id', array('pro_count'=>'COUNT(sp.product_id)'));
        if($grade != null && !empty($grade)){
            $productSql1     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;
        }
        $productSql1->where('cp.chap_id = ?',$chapId)
                    ->where('bd.device_id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED')                      
                    ->where('p.deleted != ?',1)                    
                    ->where('pb.device_selection_type = ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->group('sp.product_id');
                  
                         
         $productSql2   = $this->select(); 
         $productSql2->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))    
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))   
                    ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array()) 
                    ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array()) 
                    ->join(array('d' => 'devices'), 'd.id = da.device_id', array()) 
                    ->join(array('sp' => 'statistics_products'), 'sp.product_id = p.id', array('pro_count'=>'COUNT(sp.product_id)'));
        if($grade != null && !empty($grade)){
            $productSql2     ->join(array('pc'=>'product_categories'),'pc.product_id = p.id')
                            ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                            ->join(array('qgc' => 'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                            ->where('qgc.grade_id = ?',$grade)
                            ->where('qgc.status = ?',1)
                            ->where('c.parent_id != ?',0)
                            ;
        }
        $productSql2->where('cp.chap_id = ?',$chapId)
                    ->where('d.id = ?',$deviceId)
                    ->where('p.status = ?','APPROVED') 
                    ->where('p.deleted != ?',1)
                    ->where('pb.device_selection_type != ?', 'CUSTOM')
                    ->where('p.user_id != ?', 5981) 
                    ->group('sp.product_id');
                     
        
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
            
        }
        else
        {
           $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
        }
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->order('pro_count DESC');

        $cache->set($allProductsSql, $key, 3600);
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
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('pb.device_selection_type != ?', 'CUSTOM');
      
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"));
               
         $build =  $this->fetchRow($allProductsSql);
        
        return $build['build_id'];
        
    }
    
    
    public function getTopCategoryProducts($chapId, $deviceId, $categoryId, $limit, $offset)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);
    
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
                    ->group('sd.product_id');
                     
        
        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
            
        }
        else
        {
           $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
        }
        
         $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                           ->limit($limit, $offset);
                    

         return $this->fetchAll($allProductsSql)->toArray(); 
        
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

    /**
     * This is for get flagged apps
     * @return : app details
     */
    function getFlaggedApps($chapId)
    {
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'FLAGGED_APPS_'.$chapId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }

        //if not cached we do query
        $sql        = $this->select();
        $sql        ->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId",array('count(sd.id) AS download_count'))
                    ->where('cp.chap_id = ?',$chapId)
                    //->where('sd.chap_id = ?',$chapId)
                    ->where('cp.flagged = ?',1)
                    ->where('p.status = ?','APPROVED')
                    ->where('p.deleted != ?',1)
                    ->order('download_count DESC')
                    ->group('cp.product_id')
                    ;
        $results = $sql;
        $cache->set($results, $key, 3600);
        return $results;
    }
    
    function getAppstitudeApps($chapId)
    {
    	//if results cached we return it straightaway
    	$cache  = Zend_Registry::get('cache');
    	$key    = 'APPSTITUDE_APPS_'.$chapId;
    	if (($results = $cache->get($key)) !== false)
    	{
    		return $results;
    	}
    
    	//if not cached we do query
    	$sql        = $this->select();
    	$sql        ->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId",array('count(sd.id) AS download_count'))
    	->where('cp.chap_id = ?',$chapId)
    	//->where('sd.chap_id = ?',$chapId)
    	->where('cp.appstitude = ?',1)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->order('download_count DESC')
    	->group('cp.product_id')
    	;
    	$results = $sql;
    	$cache->set($results, $key, 3600);
    	return $results;
    }
    
    function getIslamicApps($chapId)
    {
    	//if results cached we return it straightaway
    	$cache  = Zend_Registry::get('cache');
    	$key    = 'ISLAMIC_APPS_'.$chapId;
    	if (($results = $cache->get($key)) !== false)
    	{
    		return $results;
    	}
    
    	//if not cached we do query
    	$sql        = $this->select();
    	$sql        ->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId",array('count(sd.id) AS download_count'))
    	->where('cp.chap_id = ?',$chapId)
    	//->where('sd.chap_id = ?',$chapId)
    	->where('cp.islamic = ?',1)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->order('download_count DESC')
    	->group('cp.product_id')
    	;
    	$results = $sql;
    	$cache->set($results, $key, 3600);
    	return $results;
    }

    /**
     * This is for get flagged apps by device
     */
    function getFlaggedAppsByDevice($chapId,$deviceId)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);

        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'FLAGGED_APPS_WITH_DEVICE'.$chapId.'_'.$deviceId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }

        //if not cached we do query
        $sql1   = $this->select();
        $sql1->from(array('cp' => $this->_name), array())
            ->setIntegrityCheck(false)
            ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
            ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
            ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
            ->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId", array('count(sd.id) AS download_count'))
            ->where('cp.chap_id = ?',$chapId)
            //->where('sd.chap_id = ?',$chapId)
            ->where('bd.device_id = ?',$deviceId)
            ->where('p.status = ?','APPROVED')
            ->where('p.deleted != ?',1)
            ->where('pb.device_selection_type = ?', 'CUSTOM')
            ->where('cp.flagged = ?',1)
            ->group('p.id');


        $sql2   = $this->select();
        $sql2->from(array('cp' => $this->_name), array())
            ->setIntegrityCheck(false)
            ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
            ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
            ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
            ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
            ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
            ->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId", array('count(sd.id) AS download_count'))
            ->where('cp.chap_id = ?',$chapId)
            //->where('sd.chap_id = ?',$chapId)
            ->where('d.id = ?',$deviceId)
            ->where('p.status = ?','APPROVED')
            ->where('p.deleted != ?',1)
            ->where('pb.device_selection_type != ?', 'CUSTOM')
            ->where('cp.flagged = ?',1)
            ->group('p.id');


        if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
        {
            $sql2->where('pda.value = ?', $deviceAttrib[3]);
        }
        else
        {
            $sql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
        }

        $results = $this->select()
                        ->union(array("($sql1)", "($sql2)"))
                        ->order('download_count DESC')
                        ;
        $cache->set($results, $key, 3600);
        return $results;

    }
    
    
    function getAppstitudeAppsByDevice($chapId,$deviceId)
    {
    	$deviceAttrib = $this->getDeviceAttributes($deviceId);
    
    	//if results cached we return it straightaway
    	$cache  = Zend_Registry::get('cache');
    	$key    = 'APPSTITUDE_APPS_WITH_DEVICE'.$chapId.'_'.$deviceId;
    	if (($results = $cache->get($key)) !== false)
    	{
    		return $results;
    	}
    
    	//if not cached we do query
    	$sql1   = $this->select();
    	$sql1->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
    	->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId", array('count(sd.id) AS download_count'))
    	->where('cp.chap_id = ?',$chapId)
    	//->where('sd.chap_id = ?',$chapId)
    	->where('bd.device_id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->where('pb.device_selection_type = ?', 'CUSTOM')
    	->where('cp.appstitude = ?',1)
    	->group('p.id');
    
    
    	$sql2   = $this->select();
    	$sql2->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
    	->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
    	->join(array('d' => 'devices'), 'd.id = da.device_id', array())
    	->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId", array('count(sd.id) AS download_count'))
    	->where('cp.chap_id = ?',$chapId)
    	//->where('sd.chap_id = ?',$chapId)
    	->where('d.id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->where('pb.device_selection_type != ?', 'CUSTOM')
    	->where('cp.appstitude = ?',1)
    	->group('p.id');
    
    
    	if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
    	{
    		$sql2->where('pda.value = ?', $deviceAttrib[3]);
    	}
    	else
    	{
    		$sql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
    	}
    
    	$results = $this->select()
    	->union(array("($sql1)", "($sql2)"))
    	->order('download_count DESC')
    	;
    	$cache->set($results, $key, 3600);
    	return $results;
    
    }
    
    function getIslamicAppsByDevice($chapId,$deviceId)
    {
    	$deviceAttrib = $this->getDeviceAttributes($deviceId);
    
    	//if results cached we return it straightaway
    	$cache  = Zend_Registry::get('cache');
    	$key    = 'ISLAMIC_APPS_WITH_DEVICE'.$chapId.'_'.$deviceId;
    	if (($results = $cache->get($key)) !== false)
    	{
    		return $results;
    	}
    
    	//if not cached we do query
    	$sql1   = $this->select();
    	$sql1->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
    	->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId", array('count(sd.id) AS download_count'))
    	->where('cp.chap_id = ?',$chapId)
    	//->where('sd.chap_id = ?',$chapId)
    	->where('bd.device_id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->where('pb.device_selection_type = ?', 'CUSTOM')
    	->where('cp.islamic = ?',1)
    	->group('p.id');
    
    
    	$sql2   = $this->select();
    	$sql2->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
    	->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
    	->join(array('d' => 'devices'), 'd.id = da.device_id', array())
    	->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId", array('count(sd.id) AS download_count'))
    	->where('cp.chap_id = ?',$chapId)
    	//->where('sd.chap_id = ?',$chapId)
    	->where('d.id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->where('pb.device_selection_type != ?', 'CUSTOM')
    	->where('cp.islamic = ?',1)
    	->group('p.id');
    
    
    	if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
    	{
    		$sql2->where('pda.value = ?', $deviceAttrib[3]);
    	}
    	else
    	{
    		$sql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
    	}
    
    	$results = $this->select()
    	->union(array("($sql1)", "($sql2)"))
    	->order('download_count DESC')
    	;
    	$cache->set($results, $key, 3600);
    	return $results;
    
    }
    
}