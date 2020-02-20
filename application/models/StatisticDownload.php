<?php

/**
 *
 * @copyright   neXva.com
 * @package     Admin
 * @version     $Id$
 */
class Model_StatisticDownload extends Zend_Db_Table_Abstract {

    protected $_id = 'id';
    protected $_name = 'statistics_downloads';

    function __construct() {
        parent::__construct();
    }

    public function updateProductDownloadCount($product_id, $chapId = null, $deviceId = null, $deviceName = null, $setlanguageId = false, $np = null) {
        if (! isset ( $product_id )) {
            throw new Exception ( "Product id must be provided" );
            return false;
        }

        //$deviceSession = new Zend_Session_Namespace ( 'Device' );
        //$deviceId = $deviceSession->deviceId;

        $buildProd  = new Model_ProductBuild();
        
        if($setlanguageId) {
        	$build = $buildProd->getBuildByProductAndDevice($product_id, $setlanguageId);
        } else { 
       		$build = $buildProd->getBuildByProductAndDevice($product_id);
       	}
       		
        $request     = new Zend_Controller_Request_Http();
        $source     = 'OPEN';
        
        if($np) $source = 'NEXPAGE';
        
        if (is_numeric($chapId)) {
            $themeMeta  = new Model_ThemeMeta();
            $themeMeta->setEntityId($chapId);
            $chapName   = $themeMeta->WHITELABLE_THEME_NAME;
            $ipFilter   = new Nexva_Util_NetworkFilter_IpFilter($chapName);
            if ($ipFilter->isFilterDefined()) {
                if ($ipFilter->ipFilter($request->getClientIp(true))) {
                    $source = 'NETWORK';    
                } 
            }
        }

        $geoData = Nexva_GeoData_Ip2Country_Factory::getProvider();
        $country  =   $geoData->getCountry($_SERVER['REMOTE_ADDR']);
        

        $aiKey = $this->insert(
        $data = array(
          'id'          => NULL,
          'date'        => date('Y-m-d H:i:s'),
          'product_id'  => $product_id,
          'session_id'  => session_id(),
          'device_id'   => $deviceId,
          'ip'          => $request->getClientIp(),
          'build_id'    => $build->id,
          'chap_id'     => $chapId,
          'iso' => $country['code'],
          'source'      => $source
        ));
        
        
        /**
         * If a chapId is not set, that means it's a request by the developers simluating a 
         * mobile. In any case, we can't log it without a CHAP
         */
        if ($chapId) 
            {
            /**
             * Statistics on mongo
             */
            $productModel   = new Model_Product();
            $product        = $productModel->getProductDetailsById($product_id, true);
            $stats          = new Nexva_Analytics_ProductDownload();
            $opts   = array(        
                'app_id'    => $product_id,
                'build_id'  => $build->id,
                'platform_id'   => isset($build->platform_id) ? $build->platform_id : null,
                'device_id' => $deviceId,
                'device_name'   => $deviceName,
                'chap_id'   => $chapId,
                'cp_id'     => $product['uid'],
                'source'    => $source,
                'download_id'   => $aiKey,
            );
            $stats->log($opts);
            
            if ($opts['platform_id'] == null) {
                /**
                 * Some builds don't seem to be giving us their platform ID. Logging to get more info
                 */
                $message    = "\n\n---------------------- BUILD PLATFORM_ID MISSING ISSUE BEGIN --------------------\n";
                $message    .= print_r($build, true);
                $message    .= print_r($opts, true);
                $message    .= "\n\n---------------------- BUILD PLATFORM_ID MISSING ISSUE END --------------------\n";
                Zend_Registry::get('logger')->err($message);
            }
        }
        
        return true;
    }

    /**
     * Get sum of the file size for given statistic_downloaded.id
     * @param int or array  $statistic_id
     * @return int sum of files sizes rounded in to nearest KB
     */

    public function getFileSize($ids) {
        if (empty($ids)) return 0;
        $fileSize = 0;
        $modelBuildFiles = new Model_BuildFiles ( );

        if (is_array ($ids)) {
            $select = $this->select()
                ->setIntegrityCheck( false )
                ->from( 'statistics_downloads', array () )
                ->joinInner ( 'build_files', 'statistics_downloads.build_id =  build_files.build_id', array ('sum(build_files.filesize) as filesize' ) )
                ->where ( 'statistics_downloads.id IN (?)', $ids );
            $fileInfo = $this->fetchall ( $select );
            $fileSize = round ( $fileInfo [0]->filesize / 1024 );
        } else {
            $rowset = $this->find ( $id );
            $buildRow = $rowset->current ();
            $fileSize = $modelBuildFiles->getBuildFileSize ( $buildRow->build_id );
        }
        return $fileSize;
    }
    
    
    public function getFileSizesForDownloads($ids) {
        if (empty($ids)) return 0;
        $fileSize = 0;
        $modelBuildFiles = new Model_BuildFiles ( );

        if (is_array ($ids)) {
            $select = $this->select()
                ->setIntegrityCheck( false )
                ->from('statistics_downloads', array ('statistics_downloads.id'))
                ->joinInner ('build_files', 'statistics_downloads.build_id =  build_files.build_id', array('sum(build_files.filesize) AS filesize') )
                ->where ('statistics_downloads.id IN (?)', $ids)
                ->group('statistics_downloads.id');
            $fileInfo   = $this->fetchall ( $select );
            
            $sizes      = array();
            if ($fileInfo) {
                foreach ($fileInfo as $row) {
                    $sizes[$row->id]    = $row->filesize;
                }
            } 
            return $sizes;
        } 
        return array();
    }
    
    
    public function getAllDownloadsByDate($cpid, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL ) {
        
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');
        //$cpid = 21134;
		if($cpid)	{
        $select = $this->select()
                    	->from('products', array())
                    	->join(array('sd' => $this->_name), 'products.id = sd.product_id', array("date"  => "DATE_FORMAT(sd.date,'%Y-%m-%d')", "count" => "count(sd.id)"))
                    	->where("products.user_id=?", $cpid)
                    	//->where("sd.date between '$firstDayThisMonth'and '$lastDayThisMonth'")
                        ->where('DATE(sd.date) >= ?',$firstDayThisMonth)
                        ->where('DATE(sd.date) <= ?',$lastDayThisMonth)
                        ->where('products.status = ?','APPROVED')
                        ->where('products.deleted = ?',0)
                    	->group(array("DATE_FORMAT(sd.date,'%Y-%m-%d')"))
        //echo $select->assemble();die();
                        ->query()
                        ->fetchAll();
		}    else    {
		$select = $this->select()
                       ->from(array('sd' => $this->_name), array('date'  => "DATE_FORMAT(sd.date,'%Y-%m-%d')", 'count' => "count(sd.id)"))
                        ->setIntegrityCheck(false)
                       ->join(array('p' => 'products'),'sd.product_id = p.id')
                       //->where("sd.date between '$firstDayThisMonth'and '$lastDayThisMonth'")
                        ->where('DATE(sd.date) >= ?',$firstDayThisMonth)
                        ->where('DATE(sd.date) <= ?',$lastDayThisMonth)
                        ->where('p.status = ?','APPROVED')
                        ->where('p.deleted = ?',0)
                       ->group(array("DATE_FORMAT(sd.date,'%Y-%m-%d')"))
         //echo $select->assemble();die();
                       ->query()
                       ->fetchAll();
		}                      
		return $select;
    
    }
    
    
    public function getAllDownloadsByDateProduct($productId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL ) {
    
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01');
    	if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');
    	//$cpid = 21134;
    	if($productId)	{
    		$select = $this->select()
    		->from('products', array())
    		->join(array('sd' => $this->_name), 'products.id = sd.product_id', array("date"  => "DATE_FORMAT(sd.date,'%Y-%m-%d')", "count" => "count(sd.id)"))
    		//->where("sd.date between '$firstDayThisMonth'and '$lastDayThisMonth'")
    		->where('DATE(sd.date) >= ?',$firstDayThisMonth)
    		->where('DATE(sd.date) <= ?',$lastDayThisMonth)
    		->where('products.id = ?',$productId)
    		->group(array("DATE_FORMAT(sd.date,'%Y-%m-%d')"))
    		->query()
    		->fetchAll();
    	}    else    {
    		$select = $this->select()
    		->from(array('sd' => $this->_name), array('date'  => "DATE_FORMAT(sd.date,'%Y-%m-%d')", 'count' => "count(sd.id)"))
    		->setIntegrityCheck(false)
    		->join(array('p' => 'products'),'sd.product_id = p.id')
    		//->where("sd.date between '$firstDayThisMonth'and '$lastDayThisMonth'")
    		->where('DATE(sd.date) >= ?',$firstDayThisMonth)
    		->where('DATE(sd.date) <= ?',$lastDayThisMonth)
    		->where('p.status = ?','APPROVED')
    		->where('p.deleted = ?',0)
    		->group(array("DATE_FORMAT(sd.date,'%Y-%m-%d')"))
    		//echo $select->assemble();die();
    		->query()
    		->fetchAll();
    	}
    	return $select;
    
    }
    public function getAllDownloadsByDateForChap($chapId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL ) {
        
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

        //we define types of source
        $source = array('API','MOBILE');

        $select = $this->select()
                    	->from(array('sp' => $this->_name), array("date"  => "sp.date", "count" => "count(sp.id)"))
                    	->where("sp.chap_id in(".implode(',',$chapId).")")
                    	->where("sp.date between '$firstDayThisMonth'and '$lastDayThisMonth'")
                    	->group(array("DATE_FORMAT(sp.date,'%Y-%m-%d')"))
                        ->where('sp.source IN (?)',$source)
                        ->query()->fetchAll()
                        ;
        //echo $select->assemble();die();
		return $select;
    
    }
    
    
    /**
     * Get products downlods during given period 
     * @param $cpId
     * @param $starDate
     * @param $endDate
     * @return app count by date
     */
    
    
     public function getAllDownloads($cpid, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL)	{
     	
     	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

		if($cpid)    {
        $select = $this->fetchRow( $this->select()
                    	->from('products', array())
                    	->join(array('sp' => $this->_name), 'products.id = sp.product_id', array("count" => "count(sp.id)"))
                    	->where("products.user_id=?", $cpid)
                    	->where("sp.date between '$firstDayThisMonth'and '$lastDayThisMonth'"));
                    	
		}    else    {
		$select = $this->fetchRow( $this->select()
                       ->from('products', array())
                       ->join(array('sp' => $this->_name), 'products.id = sp.product_id', array("count" => "count(sp.id)"))
                       ->where("sp.date between '$firstDayThisMonth'and '$lastDayThisMonth'"));
                    		
		}
           
        return $select;
     	
     }
     
     
    /**
     * Get total download count 
     * @param $productId
     * @return app count
     */
    
         
     public function totalDownloadAppCount($productId=null, $starDate = null, $endDate = null){

     		if($productId)	{
     	
            $count  =  $this->select()
            				->setIntegrityCheck(false)
                    		//->from("statistics_downloads", "count(id) as count")
                            ->from(array('sd'=>'statistics_downloads'),array("count" => "count(sd.id)"))
                            ->join(array('p'=>'products'),'sd.product_id = p.id',array())
                    		->where("sd.product_id = '$productId'")
                            ->where('DATE(sd.date) >= ?',$starDate)
                            ->where('DATE(sd.date) <= ?',$endDate)
                            ->where('p.status = ?','APPROVED')
                            ->where('p.deleted = ?',0)
                    		->query()
                    		->fetch();
     		}	else {
     			
     		$count  =  $this->select()
            				->setIntegrityCheck(false)
                    		//->from("statistics_downloads", "count(id) as count")
                            ->from(array('sd'=>'statistics_downloads'),array("count" => "count(sd.id)"))
                            ->join(array('p'=>'products'),'sd.product_id = p.id',array())
                            ->where('DATE(sd.date) >= ?',$starDate)
                            ->where('DATE(sd.date) <= ?',$endDate)
                            ->where('p.status = ?','APPROVED')
                            ->where('p.deleted = ?',0)
                //echo $count->assemble();die();
                    		->query()
                    		->fetch();
     		}
            //return $count->assemble();
            return($count->count);
           
        }
        
    public function getMostDownloadsAppsDateRange($starDate, $endDate, $limit=10, $chapId=NULL) {
        
            	
        $select = $this->select()
        				->setIntegrityCheck(false);
                    	$select->from('statistics_downloads', array("count" => "count(statistics_downloads.id)"));
                    	$select->join(array('p' => 'products'), "p.id = statistics_downloads.product_id", array('name'));
                    	$select->where("statistics_downloads.date between '$starDate' and '$endDate'");
                        if($chapId) $select->where("chap_id=?", $chapId);
                    	$select->group(array("statistics_downloads.product_id"));
                    	$select->order(array("count(statistics_downloads.id) DESC"));
                    	$select->limit($limit);
        //echo $select->assemble();die();
              	$select->query();

		return  $this->fetchAll($select);
    
    }
    
     public function getAllDownloadsForChap($chapId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL ) {
        
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

        $select = $this->fetchRow($this->select()
                    	->from('statistics_downloads', array( "count" => "count(statistics_downloads.id)"))
                    	->where("statistics_downloads.chap_id=?", $chapId)
                    	->where("statistics_downloads.date between '$firstDayThisMonth' and '$lastDayThisMonth'"));
                    	
                    
        return $select->count;
       }


    /**
     * @param $chapId
     * @param null $firstDayThisMonth
     * @param null $lastDayThisMonth
     * @return all downloads for particular CHAP, for a particular time duration.
     */
    function getDownloadsForChap($chapId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL)
    {
        if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01');
        if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

        $sql    =$this->select()
                ->from(array('sd'=>'statistics_downloads'),array("count" => "count(sd.id)"))
                ->setIntegrityCheck(false)
                ->join(array('p'=>'products'),'sd.product_id = p.id',array())
                ->where('sd.chap_id =?',$chapId)
                ->where('p.status =?','APPROVED')
                ->where('p.deleted =?',0)
                ->where('sd.date >=?',$firstDayThisMonth)
                ->where('sd.date <=?',$lastDayThisMonth)
                ;
        //echo $sql->assemble();die();
        $result = $this->fetchRow($sql);
        return $result->count;
    }
       
       
    public function getAllDownloadsForChapType($chapId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL,  $freeOrPremium = 'free') {
        
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

	    if($freeOrPremium == 'premium')    {
		
               $select = $this->select()
                    	->from('statistics_downloads', array( "count" => "count(statistics_downloads.id)"))
                    	->join(array('p' => 'products'), 'statistics_downloads.product_id = p.id', array())
                    	->where("statistics_downloads.chap_id=?", $chapId)
                    	->where("p.price > 0")
                    	->where("statistics_downloads.date between '$firstDayThisMonth' and '$lastDayThisMonth'");
                    	
                    	
		} else {
			
		       $select = $this->select()
                    	->from('statistics_downloads', array( "count" => "count(statistics_downloads.id)"))
                    	->join(array('p' => 'products'), 'statistics_downloads.product_id = p.id', array())
                    	->where("statistics_downloads.chap_id=?", $chapId)
                    	->where("p.price <= 0")
                    	->where("statistics_downloads.date between '$firstDayThisMonth' and '$lastDayThisMonth'");
                    	
			
			
		}
                    
        return $this->fetchRow($select)->count;
       }

    /**
     * @param $chapId
     * @param null $firstDayThisMonth
     * @param null $lastDayThisMonth
     * @param string $freeOrPremium
     * @return download count for premium/free apps for particular CHAP, in a given time duration
     */
    function getDownloadsTypeForChap($chapId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL,  $freeOrPremium = 'free')
    {
        if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01');
        if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');
        //$firstDayThisMonth = '2013-01-01';$lastDayThisMonth = '2013-10-31';

        $sql    =$this->select()
                ->from(array('sd'=>'statistics_downloads'),array('count' => 'count(sd.id)'))
                ->setIntegrityCheck(false)
                ->join(array('p'=>'products'),'sd.product_id = p.id',array())
                ->where('sd.chap_id =?',$chapId)
                ->where('p.status =?','APPROVED')
                ->where('sd.date >=?',$firstDayThisMonth)
                ->where('sd.date <=?',$lastDayThisMonth)
                ->where('p.deleted <=?',0)
                ;
        if($freeOrPremium == 'free')
        {
            $sql->where('p.price = ?',0);
        }
        else
        {
            $sql->where('p.price > ?',0);
        }

        $result = $this->fetchRow($sql);
        return $result->count;
    }
       
       
    public function getAllDownloadsByChap($cpId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL ) {
        
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');
		
		if($cpId)    {
         $select = $this->select()
                    	->from(array('sd' => $this->_name), array("count" => "count(sd.id)"))
                    	->setIntegrityCheck(false)
                    	->join(array('tm' => 'theme_meta'), 'tm.user_id = sd.chap_id', array('tm.meta_value'))
                    	->join(array('p' => 'products'), 'p.id = sd.product_id', array('id','name'))
                    	->where("tm.meta_name = 'WHITELABLE_SITE_NAME'")
                    	->where("p.user_id  = ?", $cpId)
                    	->group(array("sd.chap_id"))
                    	->where("sd.date between '$firstDayThisMonth' and '$lastDayThisMonth'")
                    	->query()
                    	->fetchAll();
		}    else    {
			
		 $select = $this->select()
                    	->from(array('sd' => $this->_name), array("count" => "count(sd.id)"))
                    	->setIntegrityCheck(false)
                    	->join(array('tm' => 'theme_meta'), 'tm.user_id = sd.chap_id', array('tm.meta_value'))
                    	->join(array('p' => 'products'), 'p.id = sd.product_id', array('id','name'))
                    	->where("tm.meta_name = 'WHITELABLE_SITE_NAME'")
                    	->group(array("sd.chap_id"))
                    	->where("sd.date between '$firstDayThisMonth' and '$lastDayThisMonth'")
                        ->order(array("count DESC"))
                    	->query()
                    	->fetchAll();
			
			
		}

		return $select;
    
    }
    
    public function getAllDownloadsByChapByApp($appId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL ) {
    
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01');
    	if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');
      			
    		$select = $this->select()
    		->from(array('sd' => $this->_name), array("count" => "count(sd.id)"))
    		->setIntegrityCheck(false)
    		->join(array('tm' => 'theme_meta'), 'tm.user_id = sd.chap_id', array('tm.meta_value'))
    		->join(array('p' => 'products'), 'p.id = sd.product_id', array('id','name'))
    		->where("tm.meta_name = 'WHITELABLE_SITE_NAME'")
    		->group(array("sd.chap_id"))
    		->where("sd.product_id = ?", $appId)
    		->where("sd.date between '$firstDayThisMonth' and '$lastDayThisMonth'")
    		->query()
    		->fetchAll();
    
    	return $select;
    
    }
    
    
    public function getMostDownloadsAppsDateRangeForCp($cpid, $startDate, $endDate, $limit=10) {
        
    	if($cpid)    {
    		
        $select = $this->select()
        				->setIntegrityCheck(false)
                    	->from('statistics_downloads', array("count" => "count(statistics_downloads.id)"))
                    	->join(array('p' => 'products'), "p.id = statistics_downloads.product_id", array('name'))
                        ->where("statistics_downloads.date between '$startDate' and '$endDate'")
                    	->where("p.user_id=?", $cpid)
                    	->group(array("statistics_downloads.product_id"))
                    	->order(array("count(statistics_downloads.id) DESC"))
                    	->limit($limit)
            //echo $select->assemble();die();
                    	->query()
                    	->fetchAll();
    	}    else    {
    		
    	$select = $this->select()
                       ->setIntegrityCheck(false)
                       ->from('statistics_downloads', array("count" => "count(statistics_downloads.id)"))
                       ->join(array('p' => 'products'), "p.id = statistics_downloads.product_id", array('name'))
                       ->where("statistics_downloads.date between '$startDate' and '$endDate'")
                       ->group(array("statistics_downloads.product_id"))
                       ->order(array("count(statistics_downloads.id) DESC"))
                       ->limit($limit)
            //echo $select->assemble();die();
                       ->query()
                       ->fetchAll();
    		
    	}
                    	
        return $select;
    
   }
   
    public function appDownloadsPerCpByPlatform($cpid, $startDate, $endDate, $limit = 10) {
     
    
    	if($cpid)    {
    		
        $results = $this->select()
        				->setIntegrityCheck(false)
                    	->from(array('sd' => $this->_name), array("count" => "count(sd.id)"))
                    	->join(array('p' => 'products'), "p.id = sd.product_id", array())
                    	->join(array('pb' => 'product_builds'), "pb.id = sd.build_id", array())
                    	->join(array('pl' => 'platforms'), "pl.id = pb.platform_id", array('pl.name'))
                        ->where("sd.date between '$startDate' and '$endDate'")
                    	->where("p.user_id=?", $cpid)
                    	->group(array("pb.platform_id"))
                    	->order(array("count(sd.id) DESC"))
                    	->limit($limit)
                    	->query()
                    	->fetchAll();    
    	}    else    {
    		
        $results = $this->select()
            			->setIntegrityCheck(false)
                    	->from(array('sd' => $this->_name), array("count" => "count(sd.id)"))
                    	->join(array('p' => 'products'), "p.id = sd.product_id", array())
                    	->join(array('pb' => 'product_builds'), "pb.id = sd.build_id", array())
                    	->join(array('pl' => 'platforms'), "pl.id = pb.platform_id", array('pl.name'))
                        ->where("sd.date between '$startDate' and '$endDate'")
                       	->group(array("pb.platform_id"))
                    	->order(array("count(sd.id) DESC"))
                    	->limit($limit)
                    	->query()
                    	->fetchAll();    

    	}
                    	
       return $results;
                    	
   }
   
   
   
  public function getAllStatRecords()	{
  	
 	  $results = $this->select()
  	                  ->from(array('sd' => $this->_name), array('sd.id', 'sd.ip'))
  	                  ->where("sd.ip IS NOT NULL")
  	                  ->order(array("sd.id DESC"))
                      ->query()->fetchAll();

  	  return  $results;
  	
  	
  }
  
  public function updateCountyCode($id, $code)	{
  	
        $this->update(array("iso" => $code), "id =$id" );
  	
  	
  }
  
  
   public function appDownloadsByRegion($startDate, $endDate, $limit = 100, $appId )	{

    $chapSession = new Zend_Session_Namespace('chapAnalytics');

   	$results = $this->select()
        				->setIntegrityCheck(false)
                    	->from(array('sd' => $this->_name), array("count" => "count(sd.id)", 'iso'))
                    	->join(array('c' => 'countries'), "c.iso = sd.iso", array('c.printable_name'))
                        ->where("sd.date between '$startDate' and '$endDate'")
                        ;
                        
       if($appId) 
           $results->where('sd.product_id = ?',$appId);
       
       if(isset($chapSession->id)) {
           $results->where('sd.chap_id = ?',$chapSession->id);
       }
            $results->group(array("sd.iso"))
                    	->order(array("count(sd.id) DESC"))
                    	->limit($limit)
                        ;
                        //;
                    	//->query()
                    	//->fetchAll()
                        //;

       return $this->fetchAll($results);
       //return $results;
   	
   	
   }
   
    public function getAllDownloadsCount($appId)	{

   	
   	   $results =$this->fetchRow( $this->select()
                  	   ->from(array('sd' => $this->_name), array("download_count" => "count(sd.id)"))
                       ->where("sd.product_id = ? ", $appId));    	 
                   	   
       return $results;
   	
   }
   
         /**
     * Get getViewCountWithApp
     * @param $from
     * @param $to
     * @return app view count with product id
     */
    public function getDownloadCountWithApp($from, $to) {
        $select = $this->select()
                        ->from('products', array('name'))
                        ->setIntegrityCheck(false) 
                        ->join(array('sd' => 'statistics_downloads'), 'products.id = sd.product_id', array("product_id" => "sd.product_id", "count" => "count(sd.id)"))
                        ->where("sd.date between '$from'and '$to'")
                        ->group('sd.product_id')
                        ->order(array("count(sd.id) DESC"))       
                       ->query()->fetchAll();
        return $select;
    }
    
   /*
    * @TO DO
    * Returns build array bu userId -Rooban
    */
   
   public function getDownloadedBuildsByUserId($userId, $source, $appId = null, $group = false) 	
     { 
     	
     	$downlaodedApps = $this->select()
                                ->from('statistics_downloads', array('product_id', 'language_id', 'platform_id', 'date'))
                                ->setIntegrityCheck(false)  
                                ->join(array('p' => 'products'), 'statistics_downloads.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                                ->where('statistics_downloads.user_id=?',$userId)
                                ->where('statistics_downloads.source = ?' , $source)
                                ->order('statistics_downloads.id desc')
                                //->group('statistics_downloads.product_id')
                                ;

		if($group){
            $downlaodedApps->group('statistics_downloads.product_id');
        }
		
        if($appId){
            $downlaodedApps->where('statistics_downloads.product_id = ?' , $appId);
        }
        //echo $downlaodedApps->assemble(); die();
        $rowset = $this->fetchAll($downlaodedApps)->toArray();
     	return $rowset;
     }
	 
	 /**
     * @param $userId
     * @param $proId
     * @return bool
     * check whether an app has downloaded by a particular user
     */
    public function userHasDownloaded($userId,$proId){
            $sql = $this->select();
            $sql->from(array('sd'=>$this->_name))
                ->where('sd.product_id = ?',$proId)
                ->where('sd.user_id = ?',$userId)
            ;

            return $result = $this->fetchAll($sql);

            /*if(count($result)>0){
                return true;   //user has downloaded this app, so we return true
            } else {
                return false;
            }*/
        }

    /**
     * @param $chapId
     * @param $appId
     * @param $buildId
     * @param $userId
     * @return bool
     */
    public function chapUserHasDownloaded($chapId, $appId, $buildId, $userId){
        $sql = $this->select()
                    ->from(array('sd' => $this->_name))
                    ->where('sd.product_id = ?',$appId)
                    ->where('sd.user_id = ?',$userId)
                    ->where('sd.build_id = ?',$buildId)
                    ->where('sd.chap_id = ?',$chapId)
                    ;
        $result = $this->fetchAll($sql);

        if(count($result)){
            return true;
        } else {
            return false;
        }
}

    public function deleteMyDownlod($appId,$userId){
         return $this->delete('product_id="'.$appId.'" and user_id="'.$userId.'"');   
    }
        
}

?>
