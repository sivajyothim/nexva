<?php
class Nexva_Analytics_ProductDownload extends Nexva_Analytics_Base {
    
    
    public function __construct() {
        parent::__construct();
        $this->tableName    = 'app_downloads'; 
    }
    
    /**
     * 
     * Logs the data for an app download
     * Required keys are - app_id, build_id, device_id, device_name, chap_id, source, download_id
     * Optional keys are - ip
     * @todo See if we can filter out admin views. 
     */
    public function log($opts) {
        
        $required   = array(
            'app_id', 'build_id', 'device_id',
            'device_name', 'chap_id', 'source'
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
            
            //Zend_Debug::dump($opts);die();
            
            $this->_save($opts, $this->tableName);
        } catch (Exception $ex) {
            /**
             * @todo log the error?
             */
        }
    }
    
    /**
     * 
     * Returns an array containing two values. The total downloads for the given period
     * as well as the total downloads for the same period before startdate. 
     * @param $cpId
     * @param $startDate
     * @param $endDate
     * @param $productId
     */
    public function getTotalDownloadsTrends($cpId = null, $startDate = null, $endDate = null, $productId = null) {
        
    	$download = new Model_StatisticDownload();

    	
		$m = date('n'); 
		$lastMonthStartDate = date('Y-m-d',mktime(1,1,1,$m-1,1,date('Y'))); 
		$lastMonthEndDate = date('Y-m-d',mktime(1,1,1,$m,0,date('Y'))); 
		
		$downloadCountThisMonth = $download->getAllDownloads($cpId);
		$downloadCountLastMonth = $download->getAllDownloads($cpId, $lastMonthStartDate, $lastMonthEndDate);
    	
        $trend          = array(
            'NOW'       => $downloadCountThisMonth->count,
            'PREVIOUS'  => $downloadCountLastMonth->count
        );

        return $trend;
    	
    }

    /**
     * 
     * Gives the number of hits from a given country
     * @param int $cpId
     * @param timestamp $startDate
     * @param timestamp $endDate
     * @param int $productId
     */
    public function getTotalDownloadsByRegion($cpId = null, $startDate = null, $endDate = null, $productId = null) {
        $opts   =   array();
        if ($cpId) {$opts['_id.cp_id']              = (int) $cpId;}
        if ($productId) {$opts['_id.app_id']    = (string) $productId;}
        
        
        $gap            = $endDate - $startDate;
        $collectionStr  = '_downloadCountsGroupedByRegion_' . $gap;  
        $collection = $this->_getCollection($collectionStr, true);
        
        $keys       = array('_id.country_code' => 1, '_id.country_name' => 1);
        $initial    = array('sum' => 0);
        $reduce     = "function (obj, prev) {prev.sum += obj.value;}";  
        
        $results    = $collection->group($keys, $initial, $reduce, $opts);
        $regions    = array();
        foreach ($results['retval'] as $region) {
            $regions[$region['_id.country_code'] . '-' . $region['_id.country_name']]  = $region['sum'];
        }
        arsort($regions);
        return $regions;
    }
    
    
    /**
     * 
     * Shows aggregated trafic broken down by CHAP
     * @param $cpId
     * @param $productId
     * @param $startDate
     * @param $endDate
     */
    public function getAppDownloadsByChap($cpId = null, $startDate = null, $endDate = null, $productId = null) {
    	
    	$statisticDownloads = new Model_StatisticDownload();
    	if($cpId)
    	    $chapViewCount = $statisticDownloads->getAllDownloadsByChap($cpId, $startDate, $endDate);
    	else 
    	    $chapViewCount = $statisticDownloads->getAllDownloadsByChap($cpId, $startDate, $endDate);
    	
    	if($productId)
    	    $chapViewCount = $statisticDownloads->getAllDownloadsByChapByApp($productId, $startDate, $endDate);
    	

        $chaps      = array();
        foreach ($chapViewCount as $chapValue) {
            $chaps[$chapValue->meta_value] = $chapValue->count;
        }

        return $chaps;        
    }
    
    /**
     * 
     * Returns the amount of downloads for a array of products
     * @param $appIds array
     * @param $gap period for which the data should be pulled
     * @param $opts array of options to pass to the query
     */
    public function getAppDownloadsByAppId($appIds, $gap = '604800', $chap_id = null) {
        
        $tempDb         = $this->_getConnectionForTempCollections();
        $collectionStr  = "_downloadCountsGroupedByChap_" . $gap;
        
        $opts   = array();
        $opts['_id.app_id']['$in']  = $appIds;
        
        if($chap_id) $opts['_id.chap_id']     = $chap_id;
        
        $totalDownloads     = $tempDb->selectCollection($collectionStr)->find($opts)->sort(array('value' => -1));
        
        
        $downloadsByApp = array();
        foreach ($totalDownloads as $download) { 
            if (!isset($downloadsByApp[$download['_id']['app_id']])) {
                $downloadsByApp[$download['_id']['app_id']] = 0;
            }
            $downloadsByApp[$download['_id']['app_id']] += $download['value'];   
        }
        return $downloadsByApp;
    }
    
    
    /**
     * 
     * Returns the top app downloads for a given CP
     * @param int $cpId
     * @param int $starDate
     * @param int $endDate
     * @param int $limit
     */
    public function getAppDownloadsByApp($cpId = null, $startDate = null, $endDate = null, $limit = 100) {
        
    	$statisticDownload = new Model_StatisticDownload();
    	$values = $statisticDownload->getMostDownloadsAppsDateRangeForCp($cpId, $startDate, $endDate);
    	
    	$newArray =  Array();
    	
    	foreach($values as $value)	{
    		$newArray[$value->name] =   $value->count;
    	}
    	return $newArray;

              
    }
    
    public function getMostDownloadedAppsByDateRange($startDate, $endDate, $limit = 10, $chapId=null)	{
    	
    
		$statisticDownload = new Model_StatisticDownload();
		$mostdownlaodsAppsByApps = $statisticDownload->getMostDownloadsAppsDateRange($startDate, $endDate, $limit, $chapId);
		
		return $mostdownlaodsAppsByApps;
    	
    }
    

    /**
     * 
     * Gives you OS build information for a given CP/app
     * @param int $cpId
     * @param date $starDate
     * @param date $endDate
     * @param int $limit
     * @param int $productId
     */
    
    public function getAppDownloadsByBuild($cpId = null, $starDate = null, $endDate = null, $limit = 10, $productId = null) {
        $statisticDownloadModel   = new Model_StatisticDownload();
        if($cpId)
            $downloadsByPlatform  = $statisticDownloadModel->appDownloadsPerCpByPlatform($cpId, $starDate, $endDate );
        else 
            $downloadsByPlatform  = $statisticDownloadModel->appDownloadsPerCpByPlatform($cpId, $starDate, $endDate );
        
        return $downloadsByPlatform; 
    }
    
    
    /**
     * This is a much lighter method to get the total  number of records
     * No aggregation 
     * @param $productId
     * @param $starDate
     * @param $endDate
     */
    
    function getTotalDownloadsForApp($productId = null, $starDate = null, $endDate = null) {
    	
    	$statisticDownload = new Model_StatisticDownload();
    	$appCount = $statisticDownload->totalDownloadAppCount($productId,  $starDate, $endDate);

    	return $appCount;
    	
    }
    
    
    /**
     * 
     * Returns the grouped app views by date for a CP. 
     * @param unknown_type $cpId CP to group by
     * @param unknown_type $starDate
     * @param unknown_type $endDate
     * @param unknown_type $gap The time lapse that is used to group views
     */
    public function getAppDownloadsByDate($cpId = null, $starDate = null, $endDate = null, $gap = 3600, $productId = null) { 
        if ($productId) {
            return $this->getAppDownloadsByDateForApp($cpId = null, $starDate, $endDate, $gap = 3600, $productId);
        } else {
	        return $this->getAppDownloadsByDateForAllApps($cpId, $starDate, $endDate, $gap);
        }
    }
    
    /**
     * Returns aggregated view information about one app
     * @param $cpId
     * @param $starDate
     * @param $endDate
     * @param $gap
     */
    function getAppDownloadsByDateForApp($cpId = null, $starDate = null, $endDate = null, $gap = 3600, $productId = null) {
        


        $downloadViews = new Model_StatisticDownload();
  	 
        // get the no of views within date range
        if($productId)
            $resultsDownloadView = $downloadViews->getAllDownloadsByDateProduct($productId, $starDate, $endDate);
        else    
  	 	    $resultsDownloadView = $downloadViews->getAllDownloadsByDate($cpId, $starDate, $endDate, $productId);

  	  

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
     * Get total download count by cp ID 
     * @param $cpId
     * @param $starDate
     * @param $endDate
     * @param $gap
     * @return (array) total app downlaods by date  { timestamp => count  }
     * @author Chathura 
     */ 
    
    
    function getAppDownloadsByDateForAllApps($cpId, $starDate = null, $endDate = null) {
        


        $downloadViews = new Model_StatisticDownload();
  	 
        // get the no of views within date range
  	 	$resultsDownloadView = $downloadViews->getAllDownloadsByDate($cpId, $starDate, $endDate);

  	  

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
     * Get total download count by chap
     * @param $chapId
     * @param $starDate
     * @param $endDate
     * @param $gap
     * @return (array) total app downlaods by date for chap { timestamp => count  }
     * @author Chathura 
     */ 
    
   
    function getAppDownloadsByDateForChap($chapIds, $startDate = null, $endDate = null) {
        
        $statDownloadModel = new Model_StatisticDownload();
  	 
        // get the no of views within date range
  	 	$resultsDownload =  $statDownloadModel->getAllDownloadsByDateForChap($chapIds, $startDate, $endDate);

	
  	 	// format the array so it comes as array, date as key and count as value
  	 	foreach ($resultsDownload as $val) {
 	 		
            $key = (string) (substr($val->date, 0, 10));
            $resultsDownloadsAsDateCount[$key] = $val->count;
        }	
         
     $dates =   $this->__getDaysInBetween($endDate, $startDate);
     
     $datesOfTheMonth = array();
     
     foreach($dates as $day)	{
     	
     if (isset($resultsDownloadsAsDateCount[$day])) {

            	$datesOfTheMonth[$day] = $resultsDownloadsAsDateCount[$day];
            
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
   
   
    /**
     * Get total download count for thrends 
     * @param $cpId
     * @param $starDate
     * @param $endDate
     * @param $gap
     * @return (array) total app downlaods by date  { timestamp => count  }
     * @author Chathura 
     */ 
   
   
   function getTotaldownloadTrends($cpId, $starDate = null, $endDate = null) {	
   
   	    $download = new Model_StatisticDownload();
     	
		$m = date('n'); 
		$lastMonthStartDate = date('Y-m-d',mktime(1,1,1,$m-1,1,date('Y'))); 
		$lastMonthEndDate = date('Y-m-d',mktime(1,1,1,$m,0,date('Y'))); 
		
		$downloadCountThisMonth = $download->getAllDownloads($cpId);
		$downloadCountLastMonth = $download->getAllDownloads($cpId, $lastMonthStartDate, $lastMonthEndDate);
		
   	
        $trend          = array(
            'NOW'       => $downloadCountThisMonth->count,
            'PREVIOUS'  => $downloadCountLastMonth->count
        );

        return $trend;
   	
   }
   
   
   public function getAllDownloadsByChap($cpId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL) {
        
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

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
                    	
		return $select;
		
    }
    
   public function getTotalDownloadByRegion($startDate, $endDate, $appId = null )	{

       $download = new Model_StatisticDownload();
       if($appId)
           $downloadByRegion = $download->appDownloadsByRegion($startDate, $endDate, 100, $appId);
       else 
           $downloadByRegion = $download->appDownloadsByRegion($startDate, $endDate);
       
       $countsPerRegion = array();
        
       foreach($downloadByRegion as $region)	{
       	
           $countsPerRegion[$region->iso.'-'.$region->printable_name]  = $region->count;
       	
       }
     
        arsort($countsPerRegion);
        return $countsPerRegion;
   	
   	
   	
   	
   }
   
    public function getAllAppDownloadsByDate($startDate = null, $endDate = null) {

        $statisticDownload = new Model_StatisticDownload();

        $productDownload = $statisticDownload->getDownloadCountWithApp($startDate, $endDate);
        $productDownloadCount = '';
        
        // format the array so it comes as array, date as key and count as value
        foreach ($productDownload as $valOne) {
            $productDownloadCount[$valOne->product_id] = $valOne->count;
        }

        return $productDownloadCount;
    }
   
   
}
