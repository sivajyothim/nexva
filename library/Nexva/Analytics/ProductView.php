<?php
/**
 * Analytics class for app views
 * @author John
 *
 */
class Nexva_Analytics_ProductView extends Nexva_Analytics_Base {

    private $viewCount  = 0;
    private $topAppViews        = array();
    private $topAppViewsRaw     = array();

    public function __construct() {
        parent::__construct();
        $this->tableName    = 'app_views';
    }

    /**
     *
     * Logs the data for a product page view
     * Required keys are - app_id, cp_id, device_id (array)
     * Optional keys are - ip
     * @todo See if we can filter out admin views.
     */
    public function log($opts) {
        
        $required   = array(
            'app_id', 'device_id', 'cp_id',  
        );

        foreach ($required as $key) {
            if (!isset($opts[$key])) {
                throw new Nexva_Analytics_Exception($key . ' has not been set');
            }
        }
        $request        = new Zend_Controller_Request_Http();
        $opts['ip']     = empty($opts['ip']) ? $request->getClientIp() : $opts['ip'];
        $opts['ua']     = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        $opts['app_id'] = (string) $opts['app_id'];
        $opts['cp_id']  = (int) $opts['cp_id'];

        $referrer   = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        if (filter_var(trim($referrer), FILTER_VALIDATE_URL) !== false) {
            $parsedUrl  = parse_url($referrer);

            $opts['referrer']    = array(
                'url'   => $referrer,
                'base'  => $parsedUrl['host']
            );
        } else {
            $opts['referrer']    = null;
        }

        $opts['timestamp']   = time();
        try {
            $this->_save($opts, $this->tableName);
        } catch (Exception $ex) {
            /**
             * @todo log the error?
             */
            $message    = $ex->getMessage();
            Zend_Registry::get('logger')->err($message);
        }
    }


    /**
     *
     * The total views for this month and last month 
     * @param $cpId
     * @return (array) this month count and last month count
     * @author Chathura 
     */
    public function getTotalViewTrends($cpId = null) {


    	$views = new Model_StatisticProduct();
     	
		$m = date('n'); 
		$lastMonthStartDate = date('Y-m-d',mktime(1,1,1,$m-1,1,date('Y'))); 
		$lastMonthEndDate = date('Y-m-d',mktime(1,1,1,$m,0,date('Y'))); 
		
		$viewCountThisMonth = $views->getAllViews($cpId);
		$viewCountLastMonth = $views->getAllViews($cpId, $lastMonthStartDate, $lastMonthEndDate);
    	
        $trend          = array(
            'NOW'       => $viewCountThisMonth->count,
            'PREVIOUS'  => $viewCountLastMonth->count
        );

        return $trend;
    }
    
    
    
    
    /**
     *
     * The total views by app id or total app views 
     * @param $cpId
     * @return (array) this month count and last month count
     * @author Chathura 
     */
    public function getTotalViews($from, $to, $productId=null) {

	   	$views = new Model_StatisticProduct();

		$viewCount = $views->getAllViewCountByDateAndAppId($from, $to, $productId);

        return $viewCount;
    }
    


    /**
     *
     * Returns the amount of views for a array of products
     * @param $appIds array
     * @param $gap period for which the data should be pulled
     * @param $opts array of options to pass to the query
     */
    public function getAppViewsByAppId($appIds, $gap = '604800', $chap_id = null) {

        $tempDb         = $this->_getConnectionForTempCollections();
        $collectionStr  = "_viewCountsGroupedByChap_" . $gap;

        $opts   = array();
        $opts['_id.app_id']['$in']  = $appIds;

        if($chap_id) $opts['_id.chap_id']     = $chap_id;

        $totalViews     = $tempDb->selectCollection($collectionStr)->find($opts)->sort(array('value' => -1));


        $viewsByApp = array();
        foreach ($totalViews as $view) {
            if (!isset($viewsByApp[$view['_id']['app_id']])) {
                $viewsByApp[$view['_id']['app_id']] = 0;
            }
            $viewsByApp[$view['_id']['app_id']] += $view['value'];
        }
        return $viewsByApp;
    }

    /**
     * Returns the top app views
     * This method is heavily cached. Since we've limited the number of date ranges possible
     * We generate a collection for each of those ranges so we can use the gap between the dates
     * to serve the pages we want.
     * @param int $cpId
     * @param timestamp $starDate
     * @param timestamp $endDate
     * @param int $limit
     * @param $downloadCounts if you pass in a $downloadCounts param, it will be populated with the download count for those apps
     */
    
    
    public function getMostViewedAppsByDateRanges($cpId = null, $starDate = null, $endDate = null, $limit = 10, &$downloadCounts = array()) {
        if (!empty($this->topAppViews)) {
            return $this->topAppViews;
            //Call the clearCachedData() method if you want to clear the cache
        }

        //because we validate the date when it comes through, there won't be more than 3 collections
        //at any given time. Unless we add more date ranges
        $gap        = $endDate  - $starDate;

        $opts       = array();
        if ($cpId) {$opts['_id.cp_id']   = $cpId;}

        $db         = $this->_getConnection();
        $tempDb     = $this->_getConnectionForTempCollections();
        $collectionStr = '_viewCountsGroupedByApp_' . $gap;
        $collection = $this->_getCollection($collectionStr, true);

        if ($collection->count() == 0) {
            throw new Nexva_Analytics_NoCacheException();
        }

        $totalViews     = $tempDb->selectCollection($collectionStr)->find($opts)->sort(array('value' => -1))->limit($limit);

        $viewsByApp     = array();
        $ids            = array();
        foreach ($totalViews as $view) {
            $viewsByApp[$view['_id']['app_id']] = $view['value'];
            $ids[]  = (string)$view['_id']['app_id'];
        }

        if (empty($viewsByApp)) {
            return array();
        }

        //get conversion data
        $productDownloads   = new Nexva_Analytics_ProductDownload();
        $downloadCounts = $productDownloads->getAppDownloadsByAppId($ids, $gap);

        $this->topAppViewsRaw   = $viewsByApp;

        $productModel   = new Model_Product();
        $productIds     = array_keys($viewsByApp);
        $select         = $productModel->select();
        $productIdstring    = implode(',', $productIds);
        $select
        ->from('products', array('id', 'name'))
        ->where('id IN (?)', $productIds)
        ->order('FIELD(id, ' . $productIdstring .')');
        $products       = $productModel->fetchAll($select);
        $viewByAppName  = array();
        foreach ($products as $product) {
            $viewByAppName["{$product->id} " . $product->name]    = $viewsByApp[$product->id];
        }

        $this->topAppViews  = $viewByAppName;
        return $viewByAppName;
    }

    /**
     * This clears the cached data in the object and not any other caching mechanism
     */
    function clearCachedData() {
        $this->topAppViews  = array();
    }

    /**
     *
     * Returns the app views grouped by device
     * @param $cpId
     * @param $starDate
     * @param $endDate
     * @param $limit
     * @param $productId
     */
    public function getAppViewsByDevice($startDate, $endDate, $cpId = null, $productId = null, $limit = 100 ) {

    	$views = new Model_StatisticProduct();
    	
    	if($cpId)
            $viewsByDevice = $views->appViewsPerCpByDevice($startDate, $endDate, $cpId);
        else 
            $viewsByDevice = $views->appViewsPerCpByDevice($startDate, $endDate);
            
        return $viewsByDevice;
    }

    /**
     *
     * Gives the number of hits from a given country
     * @param int $cpId
     * @param timestamp $startDate
     * @param timestamp $endDate
     * @param int $productId
     */
    public function getTotalViewsByRegion($startDate = null, $endDate = null, $cpId = null, $productId = null, $source = 'ALL') {

       $views = new Model_StatisticProduct();
       $viewsByRegion = $views->appViewsByRegion($startDate, $endDate);

       $countsPerRegion = array();
       
       foreach($viewsByRegion as $region)	{
           $countsPerRegion[$region->iso.'-'.$region->printable_name]  = $region->count;
       	
       }
        arsort($countsPerRegion);
        return $countsPerRegion;
    }


    /**
     *
     * Returns the grouped app views by date for a CP comparing multiple apps
     * @param int $cpId CP to group by
     * @param timestamp $starDate
     * @param timestamp $endDate
     * @param int $gap The time lapse that is used to group views in seconds
     * @param int $limit number of apps to return
     */
    public function getMultipleAppViewsByDate($cpId = null, $starDate = null, $endDate = null, $gap = 3600, $limit = 10) {
        $opts       = array();

        if ($cpId)      {$opts['cp_id']   = $cpId;}
        if ($starDate)  {$opts['timestamp']['$gte'] = (int) $starDate;}
        if ($endDate)   {$opts['timestamp']['$lte'] = (int) $endDate;}

        $db         = $this->_getConnection();
        $tempDb     = $this->_getConnectionForTempCollections();
        $collection = $this->_getCollection($this->tableName);

        if (empty($this->topAppViewsRaw)) {
            /**
             * We don't care about the output, we just need to get at the
             * raw data that the method caches
             */
            $this->getAppViewsByApp($cpId, $starDate, $endDate, $limit);
        }
        $appIds         = array_keys($this->topAppViewsRaw);
        foreach ($appIds as &$appId) {
            $appId  = (string) $appId;
        }

        $opts['app_id']['$in'] = $appIds;
        $map        = new MongoCode("
            function() {
                var rounded = (Math.round(this.timestamp / {$gap}) * {$gap});
                emit({rounded: rounded, app_id: this.app_id}, 1); 
            }
        ");

        $reduce     = new MongoCode('
            function(k, vals) { 
                var sum = 0;
                for (var i in vals) {
                    sum += vals[i]; 
                }
                return sum; 
            }
        ');

        $tempCollection = '_viewCountsGroupedMultipleByDate_' . $cpId;
        $cmd        = array(
            'mapreduce' => $this->tableName, //source collection
            'map'       => $map,
            'reduce'    => $reduce,
            'out'       => array('replace' => $tempCollection, 'db' => (string) $tempDb) //outputs to a new collection
        );

        $this->addCommonFilters($opts);
        $cmd['query']   = $opts;
        
        $views      = $db->command($cmd);

        $totalViews     = $tempDb->selectCollection($tempCollection)->find()->sort(array('_id' => array('app_id' => -1)));
        $viewsByDate    = array();
        $downloadData        = array();
        foreach ($totalViews as $view) {
            if (!isset($downloadData[$view['_id']['app_id']])) {
                $downloadData[$view['_id']['app_id']]    = array();
            }
            $key    = (string) ($view['_id']['rounded'] * 1000); //doing a secs => ms conversion
            $downloadData[$view['_id']['app_id']][$key] = $view['value'];
        }

        if (empty($downloadData)) {
            $this->_dropCollection($tempCollection);
            return array();
        }

        $productModel   = new Model_Product();
        $productIds     = array_keys($downloadData);
        $select         = $productModel->select();
        $productIdstring    = implode(',', $productIds);
        $select
        ->from('products', array('id', 'name'))
        ->where('id IN (?)', $productIds)
        ->order('FIELD(id, ' . $productIdstring .')');
        $products       = $productModel->fetchAll($select);


        $this->_dropCollection($tempCollection);
        $downloadDataPadded = array();
        foreach ($products as $product) {
            $productData    = $downloadData[$product->id];
            krsort($productData);
            $downloadDataPadded[$product->name]   = $this->padDataArrayWithDatesUsingMiliseconds($productData, $starDate * 1000, $endDate * 1000, $gap * 1000);// * 1000 because secs => ms
        }

        return $downloadDataPadded;
    }

    /**
     *
     * Shows aggregated trafic broken down by CHAP
     * @param $cpId
     * @param $productId
     * @param $startDate
     * @param $endDate
     */
    public function getAppViewsByChap($chapId = null, $startDate = null, $endDate = null, $productId = null) {

    	$statisticProduct = new Model_StatisticProduct();
    	$chapViewCount = $statisticProduct->getAllViewsByChap($chapId, $startDate, $endDate, $productId);
        $chaps      = array();
        foreach ($chapViewCount as $chapValue) {
            $chaps[$chapValue->meta_value] = $chapValue->count;
        }

        return $chaps;
    }


    /**
     *
     * Returns the grouped app views by date for a CP.
     * @param int $cpId CP to group by
     * @param timestamp $starDate
     * @param timestamp $endDate
     * @param int $gap The time lapse that is used to group views in seconds
     * @uses Nexva_Analytics_CacheGenerator_ProductView to create the caches
     */
    public function getAppViewsByDate($cpId = null, $starDate = null, $endDate = null, $gap = 3600, $productId = null) {
        if ($productId) {
            return $this->getAppViewsByDateForApp($productId, $starDate, $endDate, $gap);
        } else {
            return $this->getAppViewsByDateForAllApps($cpId, $starDate, $endDate, $gap);
        }
    }
    
    
    function getAppViewsByDateForApp($productId, $starDate = null, $endDate = null, $gap = 3600) {
    
    	$downloadViews = new Model_StatisticProduct();
    
    	// get the no of views within date range
    	 
    	$resultsDownloadView = $downloadViews->getAllViewsByProduct($productId, $starDate, $endDate);
    
    		
    	// format the array so it comes as array, date as key and count as value
    	foreach ($resultsDownloadView as $val) {
    		$key = (string) (substr($val->date, 0, 10));
    		$resultsDownloadViewAsDateCount[$key] = $val->count;
    	}
    
    	if($starDate == null)    {
    		$noOfDaysInTheMonth = date('t');
    		$yearAndMonth = date('Y-m-');
    		$datesOfTheMonth = array();
    	} else {
    
    		list($year, $month, $day) = explode('-', $starDate);
    		$noOfDaysInTheMonth = date('t', mktime(1,1,1,$month,$day,$year));
    		$yearAndMonth = date('Y-m-',mktime(1,1,1,$month,$day,$year) );
    		$datesOfTheMonth = array();
    			
    
    	}
    
    
    	// generate the dates  calander month
    	for ($day = 1; $day <= $noOfDaysInTheMonth; $day ++) {
    
    		$datesOfTheMonthKey = (string) $yearAndMonth . sprintf("%02s", $day);
    		if (isset($resultsDownloadViewAsDateCount[$datesOfTheMonthKey])) {
    
    			$datesOfTheMonth[$datesOfTheMonthKey] = $resultsDownloadViewAsDateCount[$datesOfTheMonthKey];
    
    		} else {
    
    			$datesOfTheMonth[$datesOfTheMonthKey] = 0;
    			 
    		}
    
    	}
    
    	// return date as timestamp * 1000 in Milliseconds
    	foreach ($datesOfTheMonth as $dKey => $val) {
    		$key = (string) (strtotime($dKey) * 1000);
    		$array[$key] = $val;
    	}
    
    	return $array;
    
    }
     

    /**
     * Returns aggregated view information about one app
     * @param $cpId
     * @param $starDate
     * @param $endDate
     * @param $gap
     * @uses Nexva_Analytics_CacheGenerator_ProductView to create the caches
     */
    function getAppViewsByDateForAllApps($cpId, $starDate = null, $endDate = null, $gap = 3600) {

    	$downloadViews = new Model_StatisticProduct();
  	 
        // get the no of views within date range
  	   	 	
  	 	$resultsDownloadView = $downloadViews->getAllViewsByDate($cpId, $starDate, $endDate);
 
  	 	
  	 	// format the array so it comes as array, date as key and count as value
  	 	foreach ($resultsDownloadView as $val) {
            $key = (string) (substr($val->date, 0, 10));
            $resultsDownloadViewAsDateCount[$key] = $val->count;
        }	
        
    	if($starDate == null)    {
  	 	    $noOfDaysInTheMonth = date('t');
  	 	    $yearAndMonth = date('Y-m-');
  	 	    $datesOfTheMonth = array();
  	    } else {
  	    	
  	    	list($year, $month, $day) = explode('-', $starDate);
  	 	    $noOfDaysInTheMonth = date('t', mktime(1,1,1,$month,$day,$year));
  	 	    $yearAndMonth = date('Y-m-',mktime(1,1,1,$month,$day,$year) );
  	 	    $datesOfTheMonth = array();
  	 	
  	    	
  	    }
        
        
  	 	// generate the dates  calander month
  	 	for ($day = 1; $day <= $noOfDaysInTheMonth; $day ++) {
            
  	 		$datesOfTheMonthKey = (string) $yearAndMonth . sprintf("%02s", $day);
            if (isset($resultsDownloadViewAsDateCount[$datesOfTheMonthKey])) {

            	$datesOfTheMonth[$datesOfTheMonthKey] = $resultsDownloadViewAsDateCount[$datesOfTheMonthKey];
            
            } else {
                
            	$datesOfTheMonth[$datesOfTheMonthKey] = 0;
            	
            }
            
        }
        
        // return date as timestamp * 1000 in Milliseconds
        foreach ($datesOfTheMonth as $dKey => $val) {
            $key = (string) (strtotime($dKey) * 1000);
            $array[$key] = $val;
        }	
        
        return $array;
		
   }
   
   
 function getAppViewsByDateForChap($chap, $startDate = null, $endDate = null, $gap = 3600) {

    	$downloadViews = new Model_StatisticProduct();
  	 
        // get the no of views within date range
  	 	$resultsViews = $downloadViews->getAllDownloadsByDateChap($chap, $startDate, $endDate);
  	 	$resultsDownloadViewAsDateCount = array();
  	 	
  	 	// format the array so it comes as array, date as key and count as value
  	 	foreach ($resultsViews as $val) {
            $key = (string) (substr($val->date, 0, 10));
            $resultsDownloadViewAsDateCount[$key] = $val->count;
        }	

       $datesOfTheMonth = array();
        
       $dates = $this->__getDaysInBetween($endDate, $startDate);
       
        foreach ($dates as $day) {
            if (isset($resultsDownloadViewAsDateCount[$day])) {
                $datesOfTheMonth[$day] = $resultsDownloadViewAsDateCount[$day];
            } else {
                $datesOfTheMonth[$day] = 0;
            	
            }
     }
     
     $array = array();
        

        // return date as timestamp * 1000 in Milliseconds
        foreach ($datesOfTheMonth as $dKey => $val) {
            $key = (string) (strtotime($dKey) * 1000);
            $array[$key] = $val;
        }	

        return $array;
		
   }
   
   
   
   
    public function getMostViewedAppsByDateRange($starDate, $endDate, $limit = 10, $chapId = null)	{
    	
		$statisticProduct = new Model_StatisticProduct();
		$mostViewedlaodsAppsByApps = $statisticProduct->getMostDownloadsAppsDateRange($starDate, $endDate, $limit, $chapId);
		
		return $mostViewedlaodsAppsByApps;
    	
    }
    
    public function getMostViewedAppsCountByDateRangeForCp($cpId, $startDate = null, $endDate = null, $limit = 10)	{
    	
    	$statisticProduct = new Model_StatisticProduct();
    	
    	if($cpId) 
    	    $values = $statisticProduct->getMostViewedAppsDateRangeForCp($cpId, $startDate, $endDate, $limit);
    	else 
    	    $values = $statisticProduct->getMostViewedAppsDateRangeForCp(null, $startDate, $endDate, $limit);
    	    
    	$newArray =  Array();
    	
    	foreach($values as $value)	{
    		
    		$newArray[$value->name] =   $value->count;
    	}
    	
    	return $newArray;

    }
    
    public function getAllViewedAppsCountByDateRangeForCp($cpId, $startDate = null, $endDate = null)	{
    	
    	$statisticProduct = new Model_StatisticProduct();
    	$values = $statisticProduct->getMostDownloadsAppsDateRangeForCp($cpId, $startDate, $endDate);
    	
    	$newArray =  Array();
    	
    	foreach($values as $value)	{
    		
    		$newArray[$value->name] =   $value->count;
    	}
    	
    	return $newArray;

    }
    
    	
    public function getAllAppViewsByDateAndAppForCp($cpId, $startDate = null, $endDate = null, $limit = 10) {
        
    	
    	$dates = $this->__getDaysInBetween($endDate, $startDate);
    	
    	$statisticProduct = new Model_StatisticProduct();
    	$product = new Model_Product();
    	
    	$products = $product->getProductsListByCp($cpId, $limit);
    	$productViewStatsArray = array();
    	
        foreach($products as $product)	{

    		$productView = '';
    		
		    $productView = $statisticProduct->getAllViewsByProduct($product->id, $startDate, $endDate, $limit);
		
		    $resultsDownloadViewAsDateCount = '';
		    
		    $valOne = '';
		    // format the array so it comes as array, date as key and count as value
  	 	    foreach ($productView as $valOne) {
                $key = (string) (substr($valOne->date, 0, 10));
                $resultsDownloadViewAsDateCount[$key] = $valOne->count;
            }	
		

		    $datesOfTheMonth = array();
            $day = '';
            
            foreach ($dates as $day) {
                if (isset($resultsDownloadViewAsDateCount[$day])) {
                    $datesOfTheMonth[$day] = $resultsDownloadViewAsDateCount[$day];
                } else {
                    $datesOfTheMonth[$day] = 0;
            	
                }
            }
            
            $array = array();
        
			$dKey = '';
			$valTwo = '';
            // return date as timestamp * 1000 in Milliseconds
            foreach ($datesOfTheMonth as $dKey => $valTwo) {
                $key = (string) (strtotime($dKey) * 1000);
                $array[$key] = $valTwo;
            }	
            
          $productViewStatsArray[$product->name] = $array;
        }
        
   
    	
        return $productViewStatsArray;
        
    }
    
    
   public function getAllAppViewsByDateAndApp($startDate = null, $endDate = null, $limit = 10) {
        
    	
    	$dates = $this->__getDaysInBetween($endDate, $startDate);
    	
    	$statisticProduct = new Model_StatisticProduct();

    	$productViewStatsArray = array();
    		
		$productView = $statisticProduct->getAllViewsByProduct(null, $startDate, $endDate, $limit);
		
	
		$resultsDownloadViewAsDateCount = '';
		    
		$valOne = '';
		
		// format the array so it comes as array, date as key and count as value
  	 	foreach ($productView as $valOne) {
            $key = (string) (substr($valOne->date, 0, 10));
            $resultsDownloadViewAsDateCount[$key] = $valOne->count;
        }	
		

		$datesOfTheMonth = array();
        $day = '';
            
        foreach ($dates as $day) {
            if (isset($resultsDownloadViewAsDateCount[$day])) {
                $datesOfTheMonth[$day] = $resultsDownloadViewAsDateCount[$day];
            } else {
                $datesOfTheMonth[$day] = 0;
            	
            }
        }
            
            $array = array();
        
			$dKey = '';
			$valTwo = '';
            // return date as timestamp * 1000 in Milliseconds
            foreach ($datesOfTheMonth as $dKey => $valTwo) {
                $key = (string) (strtotime($dKey) * 1000);
                $array[$key] = $valTwo;
            }	
            
          $productViewStatsArray[$product->name] = $array;
     
   
    	
        return $productViewStatsArray;
        
    }
    
    private function __getDaysInBetween($end, $start ) {  

     // Vars  
     $day = 86400; // Day in seconds  
     $format = 'Y-m-d'; // Output format (see PHP date funciton)  
     $sTime = strtotime($start); // Start as time  
     $eTime = strtotime($end); // End as time  
     $numDays = round(($eTime - $sTime) / $day) + 1;  
     $days = array();  
      
     // Get days  
     for ($d = 0; $d < $numDays; $d++) {  
      $days[] = date($format, ($sTime + ($d * $day)));  
     }  
      
     // Return days  
     return $days;  
    }   
   
    
    public function getAllAppViewsByDate($startDate = null, $endDate = null, $limit = 10) {

        $statisticProduct = new Model_StatisticProduct();

        $productView = $statisticProduct->getViewCountWithApp($startDate, $endDate);
        $productViewCount = '';
        
        // format the array so it comes as array, date as key and count as value
        foreach ($productView as $valOne) {
            $productViewCount[$valOne->product_id] = $valOne->count;
        }

        return $productViewCount;
    }

}



