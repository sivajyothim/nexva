<?php
/**
 *
 * @copyright   neXva.com
 * @package     Admin
 * @version     $Id$
 */


class Model_StatisticProduct extends Zend_Db_Table_Abstract {

    protected  $_id =   "id";
    protected  $_name = "statistics_products";


    function  __construct() {
        parent::__construct();
    }

    public function updateProductStatistics($product_id, $device_id = null, $chapId = null, $source = null) {

        if(!isset($product_id)) {
            throw new Exception("Product id must be provided");
            return false;
        }
        
         $geoData = Nexva_GeoData_Ip2Country_Factory::getProvider();
         $country  =   $geoData->getCountry($_SERVER['REMOTE_ADDR']);
        
         $data  = array(
                'id' =>NULL,
                'date'=>date('Y-m-d'),
                'product_id' => $product_id,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'session_id' => session_id(),
                'device_id'    => $device_id,
                'chap_id'    => $chapId,
         		'iso' => $country['code']
            );
            
        if($source) $data['source'] = $source;
        
        $this->insert($data);

        return true;
        
    }

    public function getTodayViewCount($productId) {
        $row = $this->select()->where('product_id = ' . $productId . ' and date = "' . date('Y-m-d') . '"')->query()->rowCount();
        return $row;
    }
    
    public function getAllViewCount($productId) {
        $count = $this->select()->where('product_id = ' . $productId)->query()->rowCount();
        
        return $count;
    }
    
    /**
     * Get getAllViewCountByDateAndAppId
     * @param $productId
     * @param $from
     * @param $to
     * @return total download count
     */
    
    
    public function getAllViewCountByDateAndAppId($from, $to, $productId=null) {
    	
    	if($productId)	{
    	
			$count = $this->select()->where('product_id = ' . $productId)->where("date between '$from'and '$to'")->query()->rowCount();
     
    	} else {
    	
    		$count = $this->select()->where("date between '$from'and '$to'")->query()->rowCount();
    		
    	}
    	

        return $count;
    }
    
    
      /**
     * Get getViewCountWithApp
     * @param $from
     * @param $to
     * @return app view count with product id
     */
    public function getViewCountWithApp($from, $to) {
        $select = $this->select()
                        ->from('products', array())
                        ->join(array('sp' => 'statistics_products'), 'products.id = sp.product_id', array("product_id" => "sp.product_id", "count" => "count(sp.id)"))
                        ->where("sp.date between '$from'and '$to'")
                        ->group('sp.product_id')
                        ->order(array("count(sp.id) DESC"))
                        ->query()->fetchAll();
        return $select;
    }

    /**
     * Get products views during given period 
     * @param $cpId
     * @param $starDate
     * @param $endDate
     * @return app count by date
     */
    
    
 public function getAllViewsByDate($cpid, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL ) {
        
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

		
		if($cpid)    {
        $select = $this->select()
                       ->from('products', array())
                       ->join(array('sp' => 'statistics_products'), 'products.id = sp.product_id', array("date"  => "DATE_FORMAT(sp.date,'%Y-%m-%d')", "count" => "count(sp.id)"))
                       ->where("products.user_id=?", $cpid)
                       ->where("sp.date between '$firstDayThisMonth'and '$lastDayThisMonth'")
                       ->group(array("DATE_FORMAT(sp.date,'%Y-%m-%d')"))->query()->fetchAll();
		}    else    {
        $select = $this->select()
                       ->from(array('sp' => 'statistics_products'), array("date"  => "DATE_FORMAT(sp.date,'%Y-%m-%d')", "count" => "count(sp.id)"))
                       ->where("sp.date between '$firstDayThisMonth'and '$lastDayThisMonth'")
                       ->group(array("DATE_FORMAT(sp.date,'%Y-%m-%d')"))->query()->fetchAll();
		}
		
		return $select;
    
    }
    
    

    public function getAllViews($cpid, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL ) {
        
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');
		
		     	
       	if($cpid)	{
        $select = $this->fetchRow( $this->select()
                    	->from('products', array())
                    	->join(array('sp' => 'statistics_products'), 'products.id = sp.product_id', array( "count" => "count(sp.id)"))
                    	->where("products.user_id=?", $cpid)
                    	->where("sp.date between '$firstDayThisMonth'and '$lastDayThisMonth'"));
       	}    else    {
       	 $select = $this->fetchRow( $this->select()
                    	->from('products', array())
                    	->join(array('sp' => 'statistics_products'), 'products.id = sp.product_id', array( "count" => "count(sp.id)"))
                    	->where("sp.date between '$firstDayThisMonth'and '$lastDayThisMonth'"));		
       	}
           
        return $select;
    }

    
    
    public function getMostDownloadsAppsDateRange($starDate, $endDate, $limit=10, $chapId=null) {
        
            	
        $select = $this->select()
        				->setIntegrityCheck(false)
                    	->from('statistics_products', array("count" => "count(statistics_products.id)"))
                    	->join(array('p' => 'products'), "p.id = statistics_products.product_id", array('name'))
                        ->where("statistics_products.date between '$starDate' and '$endDate'");
                        
                    	if($chapId) $select->where("chap_id=?", $chapId);
                    	
                    	$select->group(array("statistics_products.product_id"));
                    	$select->order(array("count(statistics_products.id) DESC"));
                    	$select->limit($limit);
                    	$select->query();

        return $this->fetchAll($select);
    
   }
   
    public function getMostViewedAppsDateRangeForCp($cpid, $starDate, $endDate, $limit=10) {

    	if($cpid)	{
        $select = $this->select()
        				->setIntegrityCheck(false)
                    	->from('statistics_products', array("count" => "count(statistics_products.id)"))
                    	->join(array('p' => 'products'), "p.id = statistics_products.product_id", array('name'))
                        ->where("statistics_products.date between '$starDate' and '$endDate'")
                    	->where("p.user_id=?", $cpid)
                    	->group(array("statistics_products.product_id"))
                        ->order(array("count(statistics_products.id) DESC"))
                    	->limit($limit)
                    	->query()
                    	->fetchAll();
        }    else    {
        	
        $select = $this->select()
        				->setIntegrityCheck(false)
                    	->from('statistics_products', array("count" => "count(statistics_products.id)"))
                    	->join(array('p' => 'products'), "p.id = statistics_products.product_id", array('name'))
                        ->where("statistics_products.date between '$starDate' and '$endDate'")                	
                    	->group(array("statistics_products.product_id"))
                    	->order(array("count(statistics_products.id) ASC"))
                    	->limit($limit)
                    	->query()
                    	->fetchAll();
                    	
        }

        return $select;
    
   }
    
    
    public function getAllViewsForChap($chapId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL ) {
        
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

        $select = $this->fetchRow($this->select()
                    	->from('statistics_products', array( "count" => "count(statistics_products.id)"))
                    	->where("statistics_products.chap_id=?", $chapId)
                    	->where("statistics_products.date between '$firstDayThisMonth'and '$lastDayThisMonth'"));

                    
        return $select->count;
       }
       
       
       public function getAllViewsChapByType($chapId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL, $freeOrPremium = 'free' ) {
        
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

		if($freeOrPremium == 'premium')	{
		
        $select = $this->select()
                    	->from('statistics_products', array( "count" => "count(statistics_products.id)"))
                    	->join(array('p' => 'products'), 'statistics_products.product_id = p.id', array())
                    	->where("statistics_products.chap_id=?", $chapId)
                    	->where("p.price > 0")
                    	->where("statistics_products.date between '$firstDayThisMonth'and '$lastDayThisMonth'");
          
       
       } else { 
       	
       	$select = $this->select()
                    	->from('statistics_products', array( "count" => "count(statistics_products.id)"))
                    	->join(array('p' => 'products'), 'statistics_products.product_id = p.id', array())
                    	->where("statistics_products.chap_id=?", $chapId)
                    	->where("p.price <= 0")
                    	->where("statistics_products.date between '$firstDayThisMonth'and '$lastDayThisMonth'");
       	
       }

       	 return $this->fetchRow($select)->count;
       	
       }
       
       
       
       
       
    public function getAllDownloadsByDateChap($chapId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL ) {
        
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');
		

        $select = $this->select()
                    	->from(array('sd' => $this->_name), array("date"  => "sd.date", "count" => "count(sd.id)"))
                    	->where("sd.chap_id in(".implode(',',$chapId).")")
                    	->where("sd.date between '$firstDayThisMonth' and '$lastDayThisMonth'")
                    	->group(array("DATE_FORMAT(sd.date,'%Y-%m-%d')"))
                    	->query()
                    	->fetchAll();
                    	
        return $select;
    
    }
    
    public function getAllDownloadsByDateForCp($cpId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL, $limit = 10 ) {
        
    	

    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

        $select = $this->select()
                    	->from(array('sp' => $this->_name), array("date"  => "sp.date", "count" => "count(sp.id)"))
                    	->setIntegrityCheck(false)
                    	->join(array('p' => 'products'), 'sp.product_id = p.id', array('id','name'))
                    	->where("p.user_id = ?", $cpId)
                    	->where("sp.date between '$firstDayThisMonth' and '$lastDayThisMonth'")
                    	->group(array("DATE_FORMAT(sp.date,'%Y-%m-%d')"))
                    	->group(array("p.id"))
                    	->limit($limit)
                    	->query()
                    	->fetchAll();
                    	
		return $select;
    
    }
    
    public function getAllViewsByProduct($productId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL, $limit = 10 ) {

    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

		if($productId)   {
        $select = $this->select()
                    	->from(array('sp' => $this->_name), array("date"  => "sp.date", "count" => "count(sp.id)"))
                    	->where("sp.product_id = ?", $productId)
                    	->where("sp.date between '$firstDayThisMonth' and '$lastDayThisMonth'")
                    	->group(array("DATE_FORMAT(sp.date,'%Y-%m-%d')"))
                    	->query()
                    	->fetchAll();
		}    else    {
		 $select = $this->select()
                    	->from(array('sp' => $this->_name), array("date"  => "sp.date", "count" => "count(sp.id)"))
                    	->where("sp.date between '$firstDayThisMonth' and '$lastDayThisMonth'")
                    	->group(array("DATE_FORMAT(sp.date,'%Y-%m-%d')"))
                    	->query()
                    	->fetchAll();

		}
		
		
                    	
		return $select;
    
    }
       
    
    public function getAllViewsByChap($cpId, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL ) {
        
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');
		
		if($cpId)	{
        $select = $this->select()
                    	->from(array('sp' => $this->_name), array("count" => "count(sp.id)"))
                    	->setIntegrityCheck(false)
                    	->join(array('tm' => 'theme_meta'), 'tm.user_id = sp.chap_id', array('tm.meta_value'))
                    	->join(array('p' => 'products'), 'p.id = sp.product_id', array('id','name'))
                    	->where("tm.meta_name = 'WHITELABLE_SITE_NAME'")
                    	->where("p.user_id  = ?", $cpId)
                    	->group(array("sp.chap_id"))
                    	->where("sp.date between '$firstDayThisMonth' and '$lastDayThisMonth'")
                    	->query()
                    	->fetchAll();  
		}    else    {
		$select = $this->select()
                       ->from(array('sp' => $this->_name), array("count" => "count(sp.id)"))
                       ->setIntegrityCheck(false)
                       ->join(array('tm' => 'theme_meta'), 'tm.user_id = sp.chap_id', array('tm.meta_value'))
                       ->join(array('p' => 'products'), 'p.id = sp.product_id', array('id','name'))
                       ->where("tm.meta_name = 'WHITELABLE_SITE_NAME'")
                       ->group(array("sp.chap_id"))
                       ->where("sp.date between '$firstDayThisMonth' and '$lastDayThisMonth'")
                        ->order('count desc')
                       ->query()
                       ->fetchAll();  
			
		}     

                    	
         
		return $select;
    
    }
    
    public function appViewsPerCpByDevice($startDate, $endDate, $cpid = null, $limit = 10) {
     
    	if($cpid)	{
    	
        $results = $this->select()
        				->setIntegrityCheck(false)
                    	->from(array('sp' => $this->_name), array("count" => "count(sp.id)"))
                    	->join(array('p' => 'products'), "p.id = sp.product_id", array())
                    	->join(array('d' => 'devices'), "d.id = sp.device_id", array('d.brand', 'd.model'))
                        ->where("sp.date between '$startDate' and '$endDate'")
                    	->where("p.user_id=?", $cpid)
                    	->group(array("sp.device_id"))
                    	->order(array("count(sp.id) DESC"))
                    	->limit($limit)
                    	->query()
                    	->fetchAll();    

    	} else {
    		
   		
    	$results = $this->select()
        				->setIntegrityCheck(false)
                    	->from(array('sp' => $this->_name), array("count" => "count(sp.id)"))
                      	->join(array('d' => 'devices'), "d.id = sp.device_id", array('d.brand', 'd.model'))
                        ->where("sp.date between '$startDate' and '$endDate'")
                       	->group(array("sp.device_id"))
                    	->order(array("count(sp.id) DESC"))
                    	->limit($limit)
                    	->query()
                    	->fetchAll();    	
    		
    	}
                    	
       return $results;
                    	
   }
   
    public function appViewsByRegion($startDate, $endDate, $limit = 100) {

        $chapSession = new Zend_Session_Namespace('chapAnalytics');
        $results = $this->select()
        				->setIntegrityCheck(false)
                    	->from(array('sp' => $this->_name), array("count" => "count(sp.id)", 'iso'))
                    	->join(array('c' => 'countries'), "c.iso = sp.iso", array('c.printable_name'))
                        ->where("sp.date between '$startDate' and '$endDate'");
        if(isset($chapSession->id)) {
            $results->where('sp.chap_id = ?',$chapSession->id);
        }
                $results->group(array("sp.iso"))
                    	->order(array("count(sp.id) DESC"))
                    	->limit($limit)
                        ;
                        //;
                    	//->query()
                    	//->fetchAll();
        //echo $results->assemble();die();
        return $this->fetchAll($results);
       //return $results;
       
       
                    	
   }
   
    /**
     * Get getViewCountWithApp
     * @param $from
     * @param $to
     * @return app view count with product id
     */
    public function getViewsCountWithApp($from, $to) {
        $select = $this->select()
                        ->from('products', array('name'))
                        ->setIntegrityCheck(false) 
                        ->join(array('sp' => 'statistics_products'), 'products.id = sp.product_id', array("product_id" => "sp.product_id", "count" => "count(sp.id)"))
                        ->where("sp.date between '$from'and '$to'")
                        ->group('sp.product_id')
                        ->order(array("count(sp.id) DESC"))       
                       ->query()->fetchAll();
        return $select;
    }

}

?>
