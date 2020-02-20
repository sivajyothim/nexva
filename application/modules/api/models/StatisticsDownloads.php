<?php

class Api_Model_StatisticsDownloads extends Zend_Db_Table_Abstract
{
    protected $_name = 'statistics_downloads';
    protected $_id = 'id';
    
    public function addDownloadStat($productId, $chapId, $source, $ip, $userId, $buildId, $platformId, $languageId, $deviceId, $sessionId)
    {
    	
    	$geoData = Nexva_GeoData_Ip2Country_Factory::getProvider();
        $country  =   $geoData->getCountry($ip);
    	
       $data = array
                (
                	'date' => new Zend_Db_Expr('NOW()'),
                    'ip' => $ip,
                    'product_id' => $productId,
                   	'build_id' => $buildId,
                  	'chap_id' => $chapId,
                    'user_id' => $userId,
                	'platform_id' => $platformId,
                    'language_id' => $languageId,
                    'source' => $source,
                    'device_id' => $deviceId,
                    'iso' => $country['code'],
                    'session_id' => $sessionId
                );
        
       $id =  $this->insert($data);
       return $id;
    }
    
    
     public function getDownloadedBuilds($userId, $limit=10, $offset=0, $source = 'API') 	
     { 
     	
     	$downlaodedApps = $this->select()
                                ->from('statistics_downloads', array('product_id', 'language_id', 'platform_id', 'date'))
                                ->setIntegrityCheck(false)  
                                ->join(array('p' => 'products'), 'statistics_downloads.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
                                ->where('statistics_downloads.user_id=?',$userId)
  
                                ->order('date desc')
                                ->group('statistics_downloads.product_id')
                                ->limit($limit, $offset);
     							

        $rowset = $this->fetchAll($downlaodedApps)->toArray();

     	return $rowset;
     	
     }
     
     public function checkDownloadedAppByuser($userId, $appId, $buildId = null)
     {
     
     	$downlaodedApps = $this->select()
     	                       ->from('statistics_downloads', array('*'))
     	                       ->where('statistics_downloads.user_id = ?', $userId)
     	                       ->where('statistics_downloads.product_id = ?', $appId);

     	$rowset = $this->fetchAll($downlaodedApps);
     	return $rowset;
     
     }
     
     
    
    public function checkNewBuildUploaded($productId, $platformId, $createdDate)
    {
       $check_queue = $this->fetchAll(
       							$this->select()->where("product_id=?", $appId)
       										   ->where("user_id=?", $userId)
       										   ->where("date=?", $chapId)
       						);
        
       $id =  $this->insert($data);
       return $id;
    }
    
    /*
     * This function will chek the following parameters are already exist in same row.
     * @pram $userId
     * @pram $chapId
     * @pram $buildId
     * @pram $deviceId
     */
    
    public function checkThisStatExist($userId, $chapId, $buildId, $deviceId){
        $sql =    $this->select()
                       ->from($this->_name, array('exist_count' => 'COUNT(id)'))
                       ->where('user_id = ?', $userId)
                       ->where('chap_id = ?', $chapId)
                       ->where('build_id = ?', $buildId)
                       ->where('device_id = ?', $deviceId)
                        ;
        //echo $sql->assemble(); die();
     	$statRow = $this->fetchRow($sql);
        if(count($statRow)){
            return $statRow->toArray();
        }
     	else{
            return array('exist_count'=> 0);
        }
    }
    
    public function checkThisStatExistWithSession($userId, $chapId, $buildId, $deviceId, $sessionId){
    	$sql =    $this->select()
    	->from($this->_name, array('exist_count' => 'COUNT(id)'))
    	->where('user_id = ?', $userId)
    	->where('chap_id = ?', $chapId)
    	->where('build_id = ?', $buildId)
    	->where('device_id = ?', $deviceId)
    	->where("session_id = '".$sessionId."'")
    	;
    	
    	
    	$statRow = $this->fetchRow($sql);
    	if($statRow['exist_count'] >= 1){
    	    if($_SERVER['REMOTE_ADDR'] == '119.235.2.149') {
    	    	//echo Zend_Debug::dump($statRow); Zend_Debug::dump($sql->assemble());die();
    	    }
    		return $statRow['exist_count'];
    	}
    	else{
    		return array('exist_count'=> 'no');
    	}
    }
    
     /*
     * This function will chek the following parameters are already exist in same row.
     * @pram $userId
     * @pram $chapId
     * @pram $buildId
     * @pram $deviceId
     */
    
    public function checkThisDownloadExist($appId, $chapId, $userId, $buildId){
        $sql =    $this->select()
                       ->from($this->_name, array('exist_count' => 'COUNT(id)'))
                       ->where('user_id = ?', $userId)
                       ->where('chap_id = ?', $chapId)
                       ->where('build_id = ?', $buildId)
                       ->where('product_id = ?', $appId)
                        ;
      

                        
     	$statRow = $this->fetchRow($sql);

     	
        if($statRow['exist_count'] > 0){
            return array('exist_count'=> 1);
        }
     	else{
            return array('exist_count'=> 0);
        }
    }

    /**
     * @param $appId
     * @param $chapId
     * @return Download count for particular app under particular CHAP
     */
    public function getDownloadCountByAppChap($appId, $chapId){
        $sql =      $this->select()
                    ->from(array('sd' => $this->_name),array('count(sd.id) as count'))
                    ->where('sd.product_id = ?',$appId)
                    ->where('sd.chap_id = ?',$chapId)
                    ;

        $result = $this->fetchAll($sql)->toArray();

        if($result[0]['count'] > 0){
            return $result[0]['count'];
        } else {
            return 0;
        }

    }
}