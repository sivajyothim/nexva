<?php
class Pbo_Model_StatisticsDownloads extends Zend_Db_Table_Abstract {

    protected $_name = 'statistics_downloads';
    protected $_id = 'id';

    

/**
     * Returns app download count
     * @param - $chapId
     * @param - $appType
     * @param - $fromDate
     * @param - $toDate    
     */
    public function getDownloadCount($chapId, $appType=null ,$fromDate=null, $toDate=null)
    {   
        $downloadSql   = $this->select();
        $downloadSql->from(array('sd' => $this->_name),array('count(sd.id) as download_count'))
                ->setIntegrityCheck(false)  
                ->join(array('p' => 'products'), 'sd.product_id = p.id', array(''))                 
                ->where('sd.chap_id = ?', $chapId)
                ->where('p.status = ?', 'APPROVED')
                ->where('p.deleted = ?', 0);
                
        if(!is_null($appType) && !empty($appType))
        {
            if($appType == 'free')
            {
                $downloadSql->where('p.price = ?', 0);
            }
            else
            {
                $downloadSql->where('p.price > ?', 0);
            }            
        }

        if(!is_null($fromDate) && !is_null($toDate) &&!empty($fromDate) && !empty($toDate))
        {
            $downloadSql->where('DATE(sd.date) >= ?', $fromDate)
                    ->where('DATE(sd.date) <= ?', $toDate);
        }  

        $downloadCount =  $this->fetchRow($downloadSql);
        return $downloadCount->download_count;
    }
    
    /**
     * Returns sales values based on the params given
     * @param - $chapId
     * @param - $appType
     * @param - $fromDate
     * @param - $toDate    
     */
    public function getSalesValue($chapId, $fromDate=null, $toDate=null)
    {   
        $downloadSql   = $this->select();
        $downloadSql->from(array('sd' => $this->_name),array('sum(p.price) as totle_val'))
                ->setIntegrityCheck(false)  
                ->join(array('p' => 'products'), 'sd.product_id = p.id', array(''))                 
                ->where('sd.chap_id = ?', $chapId)
                ->where('p.status = ?', 'APPROVED')
                ->where('p.deleted = ?', 0)
                ->where('p.product_type = ?', 'COMMERCIAL');
      
        if(!is_null($fromDate) && !is_null($toDate) && !empty($fromDate) && !empty($toDate))
        {
            $downloadSql->where('DATE(sd.date) >= ?', $fromDate)
                    ->where('DATE(sd.date) <= ?', $toDate);
        }
        elseif(!is_null($fromDate) && !empty($fromDate) && (is_null($toDate) || empty($toDate)))
        {
            $downloadSql->where('DATE(sd.date) >= ?', $fromDate);
        }
        elseif(!is_null($toDate) && !empty($toDate) && (is_null($fromDate) || empty($fromDate)))
        {
            $downloadSql->where('DATE(sd.date) <= ?', $toDate);
        }
        
        $downloadCount =  $this->fetchRow($downloadSql);
        
        if(is_null($downloadCount->totle_val))
        {
            return '0.00';
        }
        else
        {
           return $downloadCount->totle_val;
        }
        
    }
    
    
    /**
     * Returns app download counts daily wise
     * @param - $chapId
     * @param - $appType
     * @param - $fromDate
     * @param - $toDate    
     */
    public function getDownloadCountsMonthly($chapId, $fromDate=null, $toDate=null, $appType=null)
    {
        $source = array('API','MOBILE');
        $downloadSql   = $this->select();
        $downloadSql->from(array('sd' => $this->_name),array('DATE(sd.date) as download_date','count(sd.id) as download_count'))
                ->setIntegrityCheck(false)  
                ->join(array('p' => 'products'), 'sd.product_id = p.id', array(''))                 
                ->where('sd.chap_id = ?', $chapId)
                ->where('DATE(sd.date) >= ?', $fromDate)
                ->where('DATE(sd.date) < ?', $toDate)
                ->where('sd.source IN (?)',$source)
                ->group('download_date')
                ->order('download_date');
        
        if(!is_null($appType) && !empty($appType))
        {
            if($appType == 'free')
            {
                $downloadSql->where('p.price = ?', 0);
            }
            else
            {
                $downloadSql->where('p.price > ?', 0);
            }            
        } 

        return $this->fetchAll($downloadSql);
        
    }
    
    
    /**
     * Returns app download counts user wise
     * @param - $userId
     * @param - $userType
     */
    public function getDownloadCountsByUser($userId, $userType)
    {
        $downloadSql   = $this->select();
        $downloadSql->from(array('sd' => $this->_name),array('count(sd.id) as download_count'))
                ->where('user_id = ?',$userId);
               
        $downloadCount =  $this->fetchRow($downloadSql);
        
        return $downloadCount->download_count;
        
    }
    
    /**
     * Returns apps downloaded by the user
     * @param - $userId
     * @param - $userType
     */
    public function getDownloadedAppsByUser($userId, $userType)
    {
        $downloadSql   = $this->select();
        $downloadSql->from(array('sd' => $this->_name),array('DATE(sd.date) as date'))
                ->setIntegrityCheck(false)  
                ->join(array('p' => 'products'), 'sd.product_id = p.id', array('p.id', 'p.name'))                 
                ->where('sd.user_id = ?', $userId)
                ->order(array('p.id DESC', 'date DESC'));
        
        return $this->fetchAll($downloadSql)->toArray(); 
        
    }
    
    /**
     * Returns device wise apps download counts
     * @param - $chapId
     * @param - $txtSearchKey (added later for search option)
     */
    public function countDownloadedAppsByDevice($chapId, $txtSearchKey = NULL)
    {          
        $downloadSql   = $this->select();
        $downloadSql->from(array('sd' => $this->_name),array('d.id','d.brand','d.model','count(sd.id) as download_count'))
                ->setIntegrityCheck(false)  
                ->join(array('d' => 'devices'), 'sd.device_id = d.id', array(''))                 
                ->where('sd.chap_id = ?', $chapId)
                ->group('sd.device_id')
                ->order('download_count DESC');
        
        if(!is_null($txtSearchKey) && !empty($txtSearchKey))
        {
            $downloadSql->where('d.brand LIKE ?', '%'.$txtSearchKey.'%');
        }
        
        //echo $downloadSql->assemble(); die();
        return $this->fetchAll($downloadSql);
    }

    
    public function countDownloadedAppsByUser($chapId, $phoneno)
    {
    	$downloadSql   = $this->select();
    	$downloadSql->from(array('sd' => $this->_name),array('count(sd.id) as download_count', 'sd.user_id'))
    	->setIntegrityCheck(false)
    	->join(array('u' => 'users'), 'sd.user_id = u.id', array('u.mobile_no'))
    	->join(array('p' => 'products'), 'sd.product_id = p.id',array('sum(p.price) as total'))
    	->where('sd.chap_id = ?', $chapId)
    	->group('sd.user_id')
    	->order('download_count DESC');
       
    	if(!is_null($phoneno) && !empty($phoneno))
    	{
    		$downloadSql->where('u.mobile_no LIKE ?', '%'.$phoneno.'%');
    	}
    

    	return $this->fetchAll($downloadSql);
    }
    
     /**
     * Returns apps downloaded by the device
     * @param - $deviceId
     */
    public function getDownloadedAppsByDevice($deviceId, $chapId)
    {
        $downloadSql   = $this->select();
        $downloadSql->from(array('sd' => $this->_name),array('DATE(sd.date) as date'))
                ->setIntegrityCheck(false)  
                ->join(array('p' => 'products'), 'sd.product_id = p.id', array('p.id', 'p.name','p.price'))
                ->where('sd.chap_id = ?', $chapId)
                ->where('sd.device_id = ?', $deviceId)        
                ->order(array('p.id DESC', 'date DESC'));
        //Zend_Debug::dump($downloadSql->assemble());die();
        return $this->fetchAll($downloadSql)->toArray();         
    }
    
    
    public function getDownloadedAppsDetailsByUser($userId, $chapId,$from=null,$to=null)
    {
    	$downloadSql   = $this->select();
    	$downloadSql->from(array('sd' => $this->_name),array('DATE(sd.date) as date'))
    	->setIntegrityCheck(false)
    	->join(array('p' => 'products'), 'sd.product_id = p.id', array('p.id', 'p.name','p.price'))
    	->where('sd.chap_id = ?', $chapId)
    	->where('sd.user_id = ?', $userId);
        
        if( (isset($from) && !empty($from)) && (isset($to) && !empty($to))){            
            $downloadSql->where(" sd.date between '$from' and '$to' ");
        }
        
    	$downloadSql->order(array('p.id DESC', 'date DESC'));
       return $this->fetchAll($downloadSql)->toArray();
    }


    /**
     * Returns sales values based on the params given
     * @param - $chapId
     * @param - $appType
     * @param - $fromDate
     * @param - $toDate    
     */
    public function getDevelopersByChap($chapId, $fromDate=null, $toDate=null)
    {
        $userType = array('CP','USER');
        $downloadSql   = $this->select();
        $downloadSql->from(array('sd' => $this->_name),array('count(sd.id) AS downloads'))
                ->setIntegrityCheck(false)  
                ->join(array('p' => 'products'), 'sd.product_id = p.id', array('GROUP_CONCAT(p.id) AS productIDs','p.price'))
                ->join(array('u' => 'users'), 'p.user_id = u.id', array('u.id AS userID','p.price','u.username AS developer'))
                ->where('sd.chap_id = ?', $chapId)
                ->where('p.status = ?', 'APPROVED')
                ->where('p.deleted = ?', 0)
                ->where('p.price > ?', 0)
                ->where('p.product_type = ?', 'COMMERCIAL')
                ->where('u.status = ?', 1)
                ->where('u.type IN (?)', $userType);
        if(!is_null($fromDate) && !is_null($toDate) && !empty($fromDate) && !empty($toDate))
        {
            $downloadSql->where('DATE(sd.date) >= ?', $fromDate)
                        ->where('DATE(sd.date) <= ?', $toDate);
        }
        elseif(!is_null($fromDate) && !empty($fromDate) && (is_null($toDate) || empty($toDate)))
        {
            $downloadSql->where('DATE(sd.date) >= ?', $fromDate);
        }
        elseif(!is_null($toDate) && !empty($toDate) && (is_null($fromDate) || empty($fromDate)))
        {
            $downloadSql->where('DATE(sd.date) <= ?', $toDate);
        }
        $downloadSql->group('u.id');
        //return $downloadSql->assemble();
        return $this->fetchAll($downloadSql)->toArray();
    }
	
	/**
     * @return product wise statistics
     * @param - $chapId
     * @param - $userID
     * @param - $fromDate
     * @param - $toDate    
     */
    function getProductWiseStats($chapId,$userID, $fromDate=null, $toDate=null)
    {
        $userType = array('CP','USER');
        $sql = $this->select();
        $sql    ->from(array('sd' => $this->_name),array('sd.id','sd.date AS down_date','count(p.id) AS downloads'))
                ->setIntegrityCheck(false)
                //->join(array('p' => 'products'), 'sd.product_id = p.id', array('GROUP_CONCAT(p.id) AS productIDs','p.price','p.name','p.price'))
                ->join(array('p' => 'products'), 'sd.product_id = p.id', array('p.id AS productID','p.price','p.name','p.price'))
                ->join(array('u' => 'users'), 'p.user_id = u.id', array('u.id AS userID','u.username AS developer'))
                ->where('sd.chap_id = ?', $chapId)
                ->where('u.id = ?', $userID)
                ->where('p.status = ?', 'APPROVED')
                ->where('p.deleted = ?', 0)
                ->where('p.price > ?', 0)
                ->where('p.product_type = ?', 'COMMERCIAL')
                ->where('u.status = ?', 1)
                ->where('u.type IN (?)', $userType);
            if(!empty($fromDate) && !empty($toDate))
            {
                $sql->where('DATE(sd.date) >= ?', $fromDate)
                    ->where('DATE(sd.date) <= ?', $toDate);
            }
                $sql->group('p.id');
        return $this->fetchAll($sql)->toArray();
        //echo $records['productIDs'];

    }

    /**
     * @param $end
     * @param $start
     * @return days between given date range
     */
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
     * @param $chapId
     * @param null $firstDayThisMonth
     * @param null $lastDayThisMonth
     * @param $source
     * @return array of app downloads filtered by source (i.e; API,MOBILE WEB)
     */
    function getAppDownloadsBySource($chapId,$firstDayThisMonth=NULL, $lastDayThisMonth=NULL,$source)
    {
        if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01');
        if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

        $select =   $this->select()
                    ->from(array('sd' => $this->_name), array("date"  => "sd.date", "count" => "count(sd.id)"))
                    ->where('sd.chap_id =?',$chapId)
                    ->where('sd.date >=?',$firstDayThisMonth)
                    ->where('sd.date <=?',$lastDayThisMonth)
                    ->where('sd.source =?',$source)
                    ->group("DATE_FORMAT(sd.date,'%Y-%m-%d')")
                    ->query()
                    ->fetchAll();

        $resultDownloadsByDate = array();
        foreach ($select as $val) {
            $key = (string) (substr($val->date, 0, 10));
            $resultDownloadsByDate[$key] = $val->count;
        }
        //return $resultDownloadsByDate;
        $datesOfTheMonth = array();

        $dates = $this->__getDaysInBetween($lastDayThisMonth, $firstDayThisMonth);

        foreach ($dates as $day) {
            if (isset($resultDownloadsByDate[$day])) {
                $datesOfTheMonth[$day] = $resultDownloadsByDate[$day];
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

    function userWiseStatistics($chapId,$startDate,$endDate,$noOfUsers,$omitDuplicates)
    {
        $countCondition = '';
        if($omitDuplicates)
        {
            $countCondition = 'count(DISTINCT sd.product_id, sd.user_id, sd.device_id, sd.chap_id) AS downloads';
        }
        else
        {
            $countCondition = 'count(sd.id) AS downloads';
        }

        $sql = $this->select();
        $sql    ->from(array('sd'=>$this->_name),array($countCondition,'u.email','u.username','u.mobile_no'))
                ->setIntegrityCheck(false)
                ->join(array('u'=>'users'),'sd.user_id = u.id',array())
                ->where('sd.chap_id =?',$chapId)
                ->where('u.chap_id =?',$chapId)
                ;
        if($startDate)
        {
            $sql->where('DATE(sd.date) >=?',$startDate);
        }
        if($endDate)
        {
            $sql->where('DATE(sd.date) <=?',$endDate);
        }
        $sql    ->group('sd.user_id')
                ->order('downloads DESC')
                ;
        if($noOfUsers != 'all')
        {
            $sql->limit($noOfUsers);
        }

        //Zend_Debug::dump($sql->assemble());die();
        return $this->fetchAll($sql);
    }
    
    //This function will call when ajax request (auto complete) comes from pbo stastics device section
    public function getDeviceNamesByKey($chapId, $txtSearchKey)
    {  
        $downloadSql   = $this->select();
        $downloadSql->from(array('sd' => $this->_name),array('d.brand','d.model'))
                        ->setIntegrityCheck(false)  
                        ->join(array('d' => 'devices'), 'sd.device_id = d.id', array())                 
                        ->where('sd.chap_id = ?', $chapId)
                        ->where('d.brand LIKE ?', '%'.$txtSearchKey.'%')
                        ->orWhere('d.model LIKE ?', '%'.$txtSearchKey.'%')
                        ->group('sd.device_id')
                        ->limit(10,0)
                        ;

        //echo $downloadSql->assemble(); die();
        $results =$this->fetchAll($downloadSql);
        
        if(count($results) > 0){
            return $results->toArray();
        }
        else{
            return array();
        }
    }


    public function getDownloadedUsersByApp($chapId,$appId){
        //$chapId = 4348;
        $sql = $this->select();
        $sql    ->from(array('sd' => $this->_name))
                ->setIntegrityCheck(false)
                ->join(array('u' => 'users'),'sd.user_id = u.id')
                ->where('sd.product_id = ?',$appId)
                ->where('sd.chap_id = ?',$chapId)
                ;
        return $this->fetchAll($sql);
    }

    public function getDownloadedUserByDevice($chapId,$deviceId){
        $sql = $this->select();
        $sql    ->from(array('sd' => $this->_name))
                ->setIntegrityCheck(false)
                ->join(array('u' => 'users'),'sd.user_id = u.id')
                ->where('sd.device_id = ?',$deviceId)
                ->where('sd.chap_id = ?',$chapId)
                ;
        //Zend_Debug::dump($sql->assemble());die();
        return $this->fetchAll($sql);
    }

    /**
     * @param $chapId
     * @param $appId
     * @param $fromDate
     * @param $toDate
     * @return Zend_Db_Table_Select
     * Returns downloaded users for a particular app
     */
    public function getDownloadedUserForApp($chapId,$appId,$fromDate,$toDate){

        $sql = '';
        if($appId){
            $sql = $this->select();
            $sql    ->from(array('sd' => $this->_name))
                ->setIntegrityCheck(false)
                ->join(array('u' => 'users'),'sd.user_id = u.id')
                ->where('sd.product_id = ?',$appId)
                ->where('sd.chap_id = ?',$chapId)
            ;
            if($fromDate){
                $sql->where('DATE(sd.date) >= ?',$fromDate);
            }
            if($toDate){
                $sql->where('DATE(sd.date) <= ?',$toDate);
            }
            return $sql;
        }
    }


    public function getDownloadedAppsByMobile($chapId, $from, $to, $price, $mobile){

            $sql =  $this->select()
                ->from(array('sd' => $this->_name),array('sd.*','COUNT(sd.id) AS count'))
                ->setIntegrityCheck(false)
                ->join(array('p'=>'products'),'sd.product_id = p.id')
                ->join(array('u'=>'users'),'sd.user_id = u.id');
            if(($from != null) && (!empty($from))){
                $sql    ->where('DATE(sd.date) >= ?',$from);
            }
            if(($to != null) && (!empty($to))){
                $sql    ->where('DATE(sd.date) <= ?',$to);
            }
            if(($mobile != null) && (!empty($mobile))){
                $sql    ->where('u.mobile_no = ?',$mobile);
            }
            if($price == 'free'){
                $sql    ->where('p.price = ?',0);
            } else if($price == 'premium'){
                $sql    ->where('p.price > ?',0);
            }
            $sql    ->where('u.mobile_no = ?',$mobile)
                ->where('sd.chap_id = ?',$chapId)
                ->group(array('sd.product_id', 'sd.user_id'))
                ->order('count DESC')
            ;

            return $this->fetchAll($sql);
            //echo $sql->assemble();die();
    }


    public function dataUsage($chapId, $from, $to, $user){

        $sql =  $this   ->select()
                        ->from(array('sd'=>$this->_name),array('SUM(bf.filesize) AS data','COUNT(sd.id) AS downloads'))
                        ->setIntegrityCheck(false)
                        ->join(array('u'=>'users'),'u.id = sd.user_id',array('u.id','u.email','u.mobile_no'))
                        ->join(array('bf'=>'build_files'),'sd.build_id = bf.build_id',array())
                        ->where('sd.chap_id = ?',$chapId);
        if($user){
            if(is_numeric($user)){
                $sql        ->where('u.mobile_no =?',$user);
            } else {
                $sql        ->where('u.email =?',$user);
            }
        }
        $sql            ->where('DATE(sd.date) >= ?',$from)
                        ->where('DATE(sd.date) <= ?',$to)
                        ->group('sd.user_id')
                        ->order('data DESC')
                        ;
        //echo $sql->assemble();die();

        return $this->fetchAll($sql);

    }
    
    
    public function dataUsageTotal($chapId, $from, $to, $user){
    
    	$sql =  $this   ->select()
    	->from(array('sd'=>$this->_name),array('SUM(bf.filesize) AS data'))
    	->setIntegrityCheck(false)
    	->join(array('bf'=>'build_files'),'sd.build_id = bf.build_id',array())
    	->where('sd.chap_id = ?',$chapId);
    	if($user){
    		if(is_numeric($user)){
    			$sql        ->where('u.mobile_no =?',$user);
    		} else {
    			$sql        ->where('u.email =?',$user);
    		}
    	}
    	$sql            ->where('DATE(sd.date) >= ?',$from)
    	->where('DATE(sd.date) <= ?',$to)
    	->order('data DESC')
    	;
    
    	return $this->fetchRow($sql);
    
    }
}