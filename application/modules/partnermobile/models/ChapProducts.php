<?php
class Partnermobile_Model_ChapProducts extends Zend_Db_Table_Abstract
{
    protected $_name = 'chap_products';
    protected $_id = 'id';
    
    public function getTopCategoryProducts($chapId, $deviceId, $categoryId)
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
                    ->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
                    ->where('pc.category_id = ?', $categoryId)                    
                    ->group('sd.product_id');
                     
    /// if($deviceAttrib[1] == 'RIM OS' && $deviceAttrib[3] == '10.0')
       // if($deviceAttrib[1] == 'BlackBerry' && $deviceAttrib[3] == '10.0')
         if(($deviceAttrib[1] == 'RIM OS' or $deviceAttrib[1] == 'BlackBerry') && $deviceAttrib[3] == '10.0')
        {
            $productSql2->where('pda.value = ?', $deviceAttrib[3]);
        }
        else
        {
            $productSql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
            
        }
        
        $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                            ->order('product_id DESC');                    

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
    
        
    /**This is for show apps for MTN Developer Challenge, we retrieve them by flagging apps in the PBO
     * @return : app details
     */
    function getDeveloperChallengeApps($chapId)
    {
        $sql        = $this->select();
        $sql        ->from(array('cp' => $this->_name), array())
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
                    ->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId",array('count(sd.id) AS download_count'))
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('cp.flagged = ?',1)
                    ->where('p.status = ?','APPROVED')
                    ->where('p.deleted != ?',1)
                    ->order('product_id DESC')
                    ->group('cp.product_id')
                    ;
        return $sql;
    }
    
    /**
     * This is for get flagged apps by device
     */
    function getFlaggedAppsByDevice($chapId,$deviceId)
    {
        $deviceAttrib = $this->getDeviceAttributes($deviceId);     
        
        //if results cached we return it straightaway
        $cache  = Zend_Registry::get('cache');
        $key    = 'BANNERED_APPS_WITH_DEVICE_'.$chapId.'_'.$deviceId;
        if (($results = $cache->get($key)) !== false)
        {
            return $results;
        }

        $sql1   = $this->select();
        $sql1->from(array('cp' => $this->_name), array())
            ->setIntegrityCheck(false)
            ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
            ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
            ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
            ->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId", array('count(sd.id) AS download_count'))
            //->join(array('sd'=>'statistics_downloads'),'sd.product_id = p.id', array('count(sd.id) AS download_count'))
            //->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array(''))
            //->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array(''))
            ->where('cp.chap_id = ?',$chapId)
            //->where('sd.chap_id = ?',$chapId)
            ->where('bd.device_id = ?',$deviceId)
            ->where('p.status = ?','APPROVED')
            ->where('p.deleted != ?',1)
            ->where('pb.device_selection_type = ?', 'CUSTOM')
            //->where('p.user_id != ?', 5981)
            ->where('cp.flagged = ?',1)
            ->group('p.id');


        $sql2   = $this->select();
        $sql2->from(array('cp' => $this->_name), array())
            ->setIntegrityCheck(false)
            ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
            ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
            //->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array(''))
            ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
            ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
            ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
            ->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId", array('count(sd.id) AS download_count'))
            //->join(array('sd'=>'statistics_downloads'),'sd.product_id = p.id', array('count(sd.id) AS download_count'))
            //->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array(''))
            ->where('cp.chap_id = ?',$chapId)
            //->where('sd.chap_id = ?',$chapId)
            ->where('d.id = ?',$deviceId)
            ->where('p.status = ?','APPROVED')
            ->where('p.deleted != ?',1)
            ->where('pb.device_selection_type != ?', 'CUSTOM')
            //->where('p.user_id != ?', 5981)
            ->where('cp.flagged = ?',1)
            //->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
            ->group('p.id');


        if(($deviceAttrib[1] == 'RIM OS' or $deviceAttrib[1] == 'BlackBerry') && $deviceAttrib[3] == '10.0')
        {
            $sql2->where('pda.value = ?', $deviceAttrib[3]);
        }
        else
        {
            $sql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
        }
        
        $joinSql = $this->select()->union(array("($sql1)", "($sql2)"))->order('download_count DESC');
        
        $results = $this->fetchAll($joinSql)->toArray();
        $cache->set($results, $key, 3600);
        return $results;

    }
    
    
    
    function getAppstitudeAppsByDevice($chapId,$deviceId)
    {
    	$deviceAttrib = $this->getDeviceAttributes($deviceId);
    
    	//if results cached we return it straightaway
    	$cache  = Zend_Registry::get('cache');
    	$key    = 'APPSTITUDE_APPS_WITH_DEVICE_'.$chapId.'_'.$deviceId;
    	if (($results = $cache->get($key)) !== false)
    	{
    		return $results;
    	}
    
    	$sql1   = $this->select();
    	$sql1->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
    	->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId", array('count(sd.id) AS download_count'))
    	//->join(array('sd'=>'statistics_downloads'),'sd.product_id = p.id', array('count(sd.id) AS download_count'))
    	//->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array(''))
    	//->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array(''))
    	->where('cp.chap_id = ?',$chapId)
    	//->where('sd.chap_id = ?',$chapId)
    	->where('bd.device_id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->where('pb.device_selection_type = ?', 'CUSTOM')
    	//->where('p.user_id != ?', 5981)
    	->where('cp.appstitude = ?',1)
    	->group('p.id');
    
    
    	$sql2   = $this->select();
    	$sql2->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	//->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array(''))
    	->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
    	->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
    	->join(array('d' => 'devices'), 'd.id = da.device_id', array())
    	->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId", array('count(sd.id) AS download_count'))
    	//->join(array('sd'=>'statistics_downloads'),'sd.product_id = p.id', array('count(sd.id) AS download_count'))
    	//->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array(''))
    	->where('cp.chap_id = ?',$chapId)
    	//->where('sd.chap_id = ?',$chapId)
    	->where('d.id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->where('pb.device_selection_type != ?', 'CUSTOM')
    	//->where('p.user_id != ?', 5981)
    	->where('cp.appstitude = ?',1)
    	//->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
    	->group('p.id');
    
    
    	if(($deviceAttrib[1] == 'RIM OS' or $deviceAttrib[1] == 'BlackBerry') && $deviceAttrib[3] == '10.0')
    	{
    		$sql2->where('pda.value = ?', $deviceAttrib[3]);
    	}
    	else
    	{
    		$sql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
    	}
    
    	$joinSql = $this->select()->union(array("($sql1)", "($sql2)"))->order('download_count DESC');
    
    	$results = $this->fetchAll($joinSql)->toArray();
    	$cache->set($results, $key, 3600);
    	return $results;
    
    }
    
    
    function getIslamicAppsByDevice($chapId,$deviceId)
    {
    	$deviceAttrib = $this->getDeviceAttributes($deviceId);
    
    	//if results cached we return it straightaway
    	$cache  = Zend_Registry::get('cache');
    	$key    = 'ISLAMIC_APPS_WITH_DEVICE_'.$chapId.'_'.$deviceId;
    	if (($results = $cache->get($key)) !== false)
    	{
    		return $results;
    	}
    
    	$sql1   = $this->select();
    	$sql1->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
    	->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId", array('count(sd.id) AS download_count'))
    	//->join(array('sd'=>'statistics_downloads'),'sd.product_id = p.id', array('count(sd.id) AS download_count'))
    	//->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array(''))
    	//->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array(''))
    	->where('cp.chap_id = ?',$chapId)
    	//->where('sd.chap_id = ?',$chapId)
    	->where('bd.device_id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->where('pb.device_selection_type = ?', 'CUSTOM')
    	//->where('p.user_id != ?', 5981)
    	->where('cp.islamic = ?',1)
    	->group('p.id');
    
    
    	$sql2   = $this->select();
    	$sql2->from(array('cp' => $this->_name), array())
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))
    	->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
    	//->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array(''))
    	->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
    	->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
    	->join(array('d' => 'devices'), 'd.id = da.device_id', array())
    	->joinLeft(array('sd'=>'statistics_downloads'),"sd.product_id = p.id AND sd.chap_id = $chapId", array('count(sd.id) AS download_count'))
    	//->join(array('sd'=>'statistics_downloads'),'sd.product_id = p.id', array('count(sd.id) AS download_count'))
    	//->join(array('sd' => 'statistics_downloads'), 'sd.product_id = p.id', array(''))
    	->where('cp.chap_id = ?',$chapId)
    	//->where('sd.chap_id = ?',$chapId)
    	->where('d.id = ?',$deviceId)
    	->where('p.status = ?','APPROVED')
    	->where('p.deleted != ?',1)
    	->where('pb.device_selection_type != ?', 'CUSTOM')
    	//->where('p.user_id != ?', 5981)
    	->where('cp.islamic = ?',1)
    	//->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1])
    	->group('p.id');
    
    
    	if(($deviceAttrib[1] == 'RIM OS' or $deviceAttrib[1] == 'BlackBerry') && $deviceAttrib[3] == '10.0')
    	{
    		$sql2->where('pda.value = ?', $deviceAttrib[3]);
    	}
    	else
    	{
    		$sql2->where('IF(pb.platform_id = 0, 1 = 1, pda.value = ?)', $deviceAttrib[1]);
    	}
    
    	$joinSql = $this->select()->union(array("($sql1)", "($sql2)"))->order('download_count DESC');
    
    	$results = $this->fetchAll($joinSql)->toArray();
    	$cache->set($results, $key, 3600);
    	return $results;
    
    }
}