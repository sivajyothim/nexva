<?php

class Pbo_Model_ChapProducts extends Zend_Db_Table_Abstract
{
    protected $_name = 'chap_products';
    protected $_id = 'id';
    
    public function getChapAllProducts($chapId, $filterVal = 'all', $searchKey = null, $ordering = null, $orderColumn = null, $platform = null, $appId = null, $languageId = null, $category = null, $grade = null)
    {

        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array('cp.featured','cp.is_banner','cp.flagged','cp.id','cp.approved', 'cp.appstitude', 'cp.islamic', 'cp.nexva'))
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name','p.id as product_id','p.thumbnail','p.price','p.user_id','p.created_date','p.google_download_count'))
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array('c.name as cat_name'))
                    ->join(array('pb' => 'product_builds'),'pb.product_id = p.id',array('pb.platform_id AS platform'))
                    ->join(array('pl' => 'platforms'),'pb.platform_id = pl.id',array('pl.description AS platform_name'))
                    ->joinLeft(array('sd' => 'statistics_downloads'), "cp.product_id = sd.product_id AND sd.chap_id = $chapId", array('count(sd.id) as download_count'))
					->joinLeft(array('plm'=>'product_language_meta'),'p.id = plm.product_id',array('plm.id AS plm_id'))
                    ->where('c.parent_id != ?', 0)
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED')
                    ->where('p.deleted != ?',1)
                    ->group('cp.product_id');

        if($filterVal == 'featured')
        {
            $productSql->where('cp.featured = ?',1);
        }
        
        if($filterVal == 'bannered')
        {
            $productSql->where('cp.is_banner = ?',1);
        }
        
        if($filterVal == 'premium')
        {
        	$productSql->where('p.price > ?',0);
        }
        
        if($filterVal == 'nexpager')
        {
        	$productSql->where('cp.nexva = ?',1);
        }
        
        if(!empty($searchKey) && !is_null($searchKey))
        {
            $productSql->where('p.name LIKE ?', '%'.$searchKey.'%');
        }
        
        if(!empty($appId) && !is_null($appId))
        {
            $productSql->where('p.id = ?', $appId);
        }
       
        //Order by downloads column
        if(!empty($orderColumn) && !is_null($orderColumn))
        {
            //check if Ascending or Descending
            if(!empty($ordering) && !is_null($ordering))
            {
                $productSql->order('download_count '. $ordering);
            }
            else
            {
                $productSql->order('download_count DESC');
            }
        }
        else // default ordering
        {
            $productSql->order('cp.id DESC');
        }

        if(!empty($platform) && !is_null($platform))
        {
            $productSql->where('pb.platform_id = ?',$platform);
        }
		
		if(!is_null($languageId) && !empty($languageId))
        {
            $productSql->where('(pb.language_id = ?) OR (plm.language_id = ?)',$languageId);
        }

        if(!is_null($category) && !empty($category))
        {
            $productSql ->where('c.id = ?', $category);
        }

        if(!empty($grade) && !is_null($grade)){
            $productSql ->join(array('qgc'=>'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                ->where('qgc.grade_id = ?',$grade)
                ->where('qgc.status = ?',1)
                ;
            if(!is_null($category) && !empty($category)){
                $productSql ->where('qgc.category_id = ?',$category)
                ;
            }
        }
        if($_SERVER['REMOTE_ADDR'] == '119.235.2.203'){
       // echo $productSql->assemble();die();
        }
        return $productSql;
        
    }
    
    //featuring a Chap Product 
    public function updateFeaturedProduct($chapId, $appId, $status)
    {        
        $data = array('featured' => $status);
        $where = array('product_id = ?' => $appId,'chap_id = ?' => $chapId);
                
        $rowsAffected = $this->update($data,$where);
        
        if($rowsAffected > 0)
        {
            return  TRUE;
            
        }
        else
        {
            return FALSE;
        }
    }
    
    //Add product to banner list 
    public function updateFlaggedProduct($chapId, $appId, $status)
    {        
        $data = array('flagged' => $status);
        $where = array('id = ?' => $appId,'chap_id = ?' => $chapId);
                
        $rowsAffected = $this->update($data,$where);
        
        if($rowsAffected > 0)
        {
            return  TRUE;
            
        }
        else
        {
            return FALSE;
        }
    } 
    
    public function updateAppstitudeProduct($chapId, $appId, $status)
    {
    	$data = array('appstitude' => $status);
    	$where = array('id = ?' => $appId,'chap_id = ?' => $chapId);
    
    	$rowsAffected = $this->update($data,$where);
    
    	if($rowsAffected > 0)
    	{
    		return  TRUE;
    
    	}
    	else
    	{
    		return FALSE;
    	}
    }
    
    public function updateIslamicProduct($chapId, $appId, $status)
    {
    	$data = array('islamic' => $status);
    	$where = array('id = ?' => $appId,'chap_id = ?' => $chapId);
    
    	$rowsAffected = $this->update($data,$where);
    
    	if($rowsAffected > 0)
    	{
    		return  TRUE;
    
    	}
    	else
    	{
    		return FALSE;
    	}
    }
    
    
    public function updateNexvaProduct($chapId, $appId, $status)
    {
    	$data = array('nexva' => $status);
    	$where = array('id = ?' => $appId,'chap_id = ?' => $chapId);
    
    	$rowsAffected = $this->update($data,$where);
    
    	if($rowsAffected > 0)
    	{
    		return  TRUE;
    
    	}
    	else
    	{
    		return FALSE;
    	}
    }
    
	//Change status of an app(approve or disapprove)
    public function updateApprovedProduct($chapId, $appId, $status)
    {        
        $data = array('approved' => $status);
        $where = array('id = ?' => $appId,'chap_id = ?' => $chapId);
                
        $rowsAffected = $this->update($data,$where);
        
        if($rowsAffected > 0)
        {
            return  TRUE;
            
        }
        else
        {
            return FALSE;
        }
    }
	
    //Flaggin gan un flagging products
    public function updateBanneredProduct($chapId, $appId, $status)
    {        
        $data = array('is_banner' => $status);
        $where = array('product_id = ?' => $appId,'chap_id = ?' => $chapId);
                
        $rowsAffected = $this->update($data,$where);
        
        if($rowsAffected > 0)
        {
            return  TRUE;
            
        }
        else
        {
            return FALSE;
        }
    }
    
    //Returns banner count of a CHAP
    public function getBannerdProductCountByChap($chapId)
    {
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array( "banner_count" => "count(cp.id)"))
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('')) 
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('cp.is_banner = ?',1)
                    ->where('p.status = ?','APPROVED')                    
                    ->where('p.deleted != ?',1);
        
        $bannerCount = $this->fetchRow($productSql);
        
        return $bannerCount->banner_count;
    }
    
    //Returns featured products count of a CHAP
    public function getFeaturedProductCountByChap($chapId)
    {
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array( "featured_count" => "count(cp.id)"))
                    ->setIntegrityCheck(false)  
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('')) 
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('cp.featured = ?',1)
                    ->where('p.status = ?','APPROVED')                    
                    ->where('p.deleted != ?',1);
        
        $bannerCount = $this->fetchRow($productSql);
        //return $this->fetchAll($productSql);
        return $bannerCount->featured_count;
    }


    public function getFeaturedProductPlatformCountByChap($chapId,$appId,$platform)
    {
        $sql = $this->select();
        $sql    ->from(array('pb' => 'product_builds'),array('app_count' => new Zend_Db_Expr('count(DISTINCT(p.id))')))
                ->setIntegrityCheck(false)
                ->join(array('p' => 'products'),'pb.product_id = p.id',array())
                ->join(array('pl'=>'platforms'),'pb.platform_id = pl.id',array('pl.id','pl.name'))
                ->join(array('cp'=>'chap_products'),'pb.product_id = cp.product_id',array())
                ->where('p.status = ?','APPROVED')
                ->where('cp.chap_id = ?',$chapId)
                ->where('p.deleted != ?',1)
                ->where('cp.featured = ?',1)
                ->where('pb.platform_id IN (?)',$platform)
                ->group('pl.id');
        //echo $sql->assemble();die();
        return $this->fetchAll($sql);
    }

    public function getBannerProductPlatformCountByChap($chapId,$appId,$platform)
    {
        $sql = $this->select();
        $sql    ->from(array('pb' => 'product_builds'),array('app_count' => new Zend_Db_Expr('count(DISTINCT(p.id))')))
                ->setIntegrityCheck(false)
                ->join(array('p' => 'products'),'pb.product_id = p.id',array())
                ->join(array('pl'=>'platforms'),'pb.platform_id = pl.id',array('pl.id','pl.name'))
                ->join(array('cp'=>'chap_products'),'pb.product_id = cp.product_id',array())
                ->where('p.status = ?','APPROVED')
                ->where('cp.chap_id = ?',$chapId)
                ->where('p.deleted != ?',1)
                ->where('cp.is_banner = ?',1)
                ->where('pb.platform_id IN (?)',$platform)
                ->group('pl.id');

        return $this->fetchAll($sql);
    }

    //Delete a Chap Product
    public function deleteProduct($chapId, $prodId)
    {
        $rowsAffected = $this->delete( array('id = ?' => $prodId, 'chap_id = ?' => $chapId));

        if($rowsAffected > 0)
        {
            return  TRUE;

        }
        else
        {
            return FALSE;
        }
    }

    public function deleteProductChap($chapId, $prodId)
    {
        $rowsAffected = $this->delete( array('product_id = ?' => $prodId, 'chap_id = ?' => $chapId));

        if($rowsAffected > 0)
        {
            return  TRUE;

        }
        else
        {
            return FALSE;
        }
    }
    
    //Add product to a CHAP
    public function addProductToChap($chapId, $prodId)
    {
        $data = array
                (
                    'chap_id' => $chapId,
                    'product_id' => $prodId,                            
                    'created_date' => new Zend_Db_Expr('NOW()')
                );
        
       $id =  $this->insert($data);
       return $id;
    }
    
    //Add product to a CHAP(Sujith)
    public function addProductToChapToApproved($chapId, $prodId, $approved)
    {
        $data = array
                (
                    'chap_id' => $chapId,
                    'product_id' => $prodId,                            
                    'approved' => $approved,                            
                    'created_date' => new Zend_Db_Expr('NOW()')
                );
       $id =  $this->insert($data);
       return $id;
    }
    
    public function getAllProductsCountForChap($chapId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL, $featured=false ) {
        
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

		if($featured)	{
		
        $select = $this->fetchRow($this->select()
                       ->from($this->_name, array( "count" => "count(id)"))
                       ->where("chap_id = ?", $chapId)
                       ->where("featured = ?", $featured)
                       ->where("created_date between '$firstDayThisMonth' and '$lastDayThisMonth'"));
                       
                       
                      
                       
		}    else    {
			
		$select = $this->fetchRow($this->select()
                       ->from($this->_name, array( "count" => "count(id)"))
                       ->where("chap_id = ?", $chapId)
                       ->where("created_date between '$firstDayThisMonth' and '$lastDayThisMonth'"));
                       
                     

         
                       
			
		}
                    
        return $select->count;
       }
       
       
       
      public function getAllFreeProductsCountForChap($chapId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL, $freeOrPremium = 'free' ) {
        
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

		if($freeOrPremium  == 'free')	{
			
			
		

		$select = $this->fetchRow($this->select()
                       ->from(array('cp' => $this->_name), array( "count" => "count(cp.id)"))
                       ->join(array('p' => 'products'), 'cp.product_id = p.id', array())
                       ->where("chap_id = ?", $chapId)
                       ->where("p.price <= 0")
			           ->where("p.deleted <> 1")
			           ->where("p.status = 'APPROVED'")
                       ->where("cp.created_date between '$firstDayThisMonth' and '$lastDayThisMonth'"));
		}
		            
        if($freeOrPremium  == 'premium')	{
			$select = $this->fetchRow($this->select()
                       ->from(array('cp' => $this->_name), array( "count" => "count(cp.id)"))
                       ->join(array('p' => 'products'), 'cp.product_id = p.id', array())
                       ->where("chap_id = ?", $chapId)
                       ->where("p.price > 0")
			           ->where("p.deleted <> 1")
			           ->where("p.status = 'APPROVED'")
                       ->where("cp.created_date between '$firstDayThisMonth' and '$lastDayThisMonth'"));

		}
		
		//return $select->toArray();
        return $select->count;
       }

    public function excelReport($chapId)
    {
        $sql = $this->select()
                    ->setIntegrityCheck(false)
                    ->from('chap_products')
                    ->joinLeft(array('sd' => 'statistics_downloads'),'chap_products.product_id = sd.product_id', array('count(sd.id) as download_count'))
                    ->join('products','chap_products.product_id = products.id',array('products.name as product_name','products.price as product_price','products.product_type as pro_type'))
                    ->join('product_builds','products.id = product_builds.product_id')
                    ->join('build_files','product_builds.id = build_files.build_id',array('build_files.filename AS build_name'))
                    ->where('chap_products.chap_id = '.$chapId)
                    ->group('chap_products.product_id');

        $products = $this->fetchAll($sql)->toArray();

        $i=0;
        foreach($products as $product)
        {
            $products[$i][] = $this->getCategory($product['product_id']);
            $i++;
        }
        return $products;
    }

    function getCategory($product_ID){
        $sql = $this->select()
                    ->setIntegrityCheck(false)
                    ->from('product_categories')
                    ->join('categories','product_categories.category_id = categories.parent_id')
                    ->where('product_categories.product_id = '.$product_ID);
        $products = $this->fetchAll($sql)->toArray();
        return $products;
    }

    /**
     * @param $chapId
     * @param null $firstDayThisMonth
     * @param null $lastDayThisMonth
     * @param string $freeOrPremium
     * @return appCount
     */

    function getProductsCountForChap($chapId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL, $freeOrPremium = 'free' )
    {
        if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01');
        if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

        $select = $this->select()
            ->from(array('p' => 'products'), array( "count" => "count(p.id)"))
            ->setIntegrityCheck(false)
            ->join(array('u' => 'users'), 'p.user_id = u.id', array())
            ->where('u.chap_id =?', $chapId)
            ->where('p.deleted =?',0)
            ->where('p.status =?', 'APPROVED')
            ->where('u.type =?', 'CP')
            ->where('u.status =?', 1)
            ->where('u.status =?', 1)
            ->where('p.created_date >= ?',$firstDayThisMonth)
            ->where('p.created_date <= ?',$lastDayThisMonth)
            ;
        if($freeOrPremium  == 'free')
        {
            $select->where('p.price =?', 0);
        }
        else
        {
            $select->where('p.price >?', 0);
        }

        $result = $this->fetchRow($select);
        return $result->count;

    }

    function appWiseStatistics($chapId,$startDate,$endDate,$appType,$noOfApps,$omitDuplicates)
    {
        $sql = $this->select();
        if($omitDuplicates)
        {
            $sql    ->from(array('sd'=>'statistics_downloads'),array('COUNT(DISTINCT sd.user_id, sd.product_id, sd.device_id, sd.chap_id) AS count','p.name','p.id AS productId','u.email','DATE(p.created_date) AS submit_date'))
                    ->setIntegrityCheck(false)
                    ->join(array('p'=>'products'),'sd.product_id = p.id',array())
                    ->join(array('cp'=>'chap_products'),'p.id = cp.product_id',array())
                    ->where('sd.chap_id = ?',$chapId)
                    ->where('sd.user_id != ?','NULL')
                    ->group('p.name')
                    ;
        }
        else
        {
            $sql    ->from(array('cp'=>$this->_name),array('count(sd.id) AS count','p.name','u.email','p.id AS productId','DATE(p.created_date) AS submit_date'))
                    ->setIntegrityCheck(false)
                    ->join(array('p'=>'products'),'cp.product_id = p.id',array())
                    ->join(array('pc'=>'product_categories'),'pc.product_id = p.id',array())
                    ->join(array('c'=>'categories'),'pc.category_id = c.id',array())
                    ->join(array('pb'=>'product_builds'),'pb.product_id = p.id',array())
                    ->joinLeft(array('sd'=>'statistics_downloads'),"cp.product_id = sd.product_id AND sd.chap_id = $chapId",array())
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('c.parent_id != ?',0)
                    ->group('cp.product_id')
                    ;
        }
            $sql    ->join(array('u'=>'users'),'p.user_id = u.id')
                    ->where('p.status =?','APPROVED')
                    ->where('p.deleted =?',0)
                    ;
        if($appType == 'free')
        {
            $sql->where('p.price = ?',0);
        }
        if($appType == 'premium')
        {
            $sql->where('p.price > ?',0);
        }
        if($appType == 'flagged')
        {
            $sql->where('cp.flagged = ?',1);
        }
        if($startDate)
        {
            $sql->where('DATE(sd.date) >=?',$startDate);
        }

        if($endDate)
        {
            $sql->where('DATE(sd.date) <=?',$endDate);
        }

        $sql    ->order('count DESC')
                ;
        if($noOfApps != 'all')
        {
            $sql->limit($noOfApps);
        }
        return $this->fetchAll($sql);
    }
    
    /*
     * This function will call when ajax request (auto complete) comes from pbo manage app section
     */
    public function getChapAllProductNames($chapId, $searchString, $platform=null, $status=null)
    {       
        $productSql   = $this->select(); 
        $productSql->from(array('cp' => $this->_name), array())                   
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name'))
                    ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
                    ->join(array('c' => 'categories'), 'pc.category_id = c.id', array())
                    ->join(array('pb' => 'product_builds'),'pb.product_id = p.id',array())
                    ->join(array('pl' => 'platforms'),'pb.platform_id = pl.id',array())
                    ->joinLeft(array('sd' => 'statistics_downloads'), "cp.product_id = sd.product_id AND sd.chap_id = $chapId", array())
                    ->where('c.parent_id != ?', 0)
                    ->where('cp.chap_id = ?',$chapId)
                    ->where('p.status = ?','APPROVED')
                    ->where('p.deleted != ?',1)
                    ->where('p.name LIKE ?', '%'.$searchString.'%')
                    ->group('cp.product_id')
                    ->limit(10, 0);
        
        if(!empty($platform) && !is_null($platform))
        {
            $productSql->where('pb.platform_id = ?',$platform);
        }
        
        if(!empty($status) && !is_null($status))
        {
            if($status == 'featured')
            {
                $productSql->where('cp.featured = ?',1);
            }

            if($status == 'bannered')
            {
                $productSql->where('cp.is_banner = ?',1);
            }

            if($status == 'premium')
            {
                $productSql->where('p.price > ?',0);
            }
        }
        
        //echo $productSql->assemble();
        $results =$this->fetchAll($productSql);
        
        if(count($results) > 0){
            return $results->toArray();
        }
        else{
            return array();
        }
    }

    /**
     * @param $chapId
     * @return Zend_Db_Table_Select
     *  get all chap products, ascending order it's product name
     */
    public function getAllChapProducts($chapId){
        $sql    = $this->select();
        $sql    ->from(array('cp' => $this->_name))
                ->setIntegrityCheck(false)
                ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name'))
                ->where('cp.chap_id = ?',$chapId)
                ->order('p.name ASC')
                ;
        return $this->fetchAll($sql);
    }
    
    public function checkProductsadded($chapId, $productId){
    	$sql    = $this->select();
    	$sql    ->from(array('cp' => $this->_name))
    	        ->setIntegrityCheck(false)
    	        ->join(array('p' => 'products'), 'cp.product_id = p.id', array('p.name'))
    	        ->where('cp.chap_id = ?',$chapId)
    	        ->where('cp.product_id = ?',$productId)
    	;
   
     $results =$this->fetchAll($sql);
         
        if(count($results) > 0){
            return $results;
        }
        else{
            return false;
        }
    }

}
