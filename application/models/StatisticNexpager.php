<?php

class Model_StatisticNexpager extends Zend_Db_Table_Abstract {

    protected $_id = 'id';
    protected $_name = 'statistics_nexpager';

    function __construct() {
        parent::__construct();
    }

    public function updateNpStats($cpId, $source, $deviceId = null,  $chapId = null) {
        
        $geoData = Nexva_GeoData_Ip2Country_Factory::getProvider();
        $country  =   $geoData->getCountry($_SERVER['REMOTE_ADDR']);
        
        $request     = new Zend_Controller_Request_Http();
        //$request->getHeader('referer');
        //$this->getRequest()->getHeader('Referer')->getUri();
        //$this->getRequest()->getHeader('Referer')->uri()->getPath();
        
        $data = array(
        		'id'          => NULL,
        		'cp_id'  => $cpId,
        		'iso' => $country['code'],
        		'referer' => $_SERVER['HTTP_REFERER'],
        		'ip'          => $request->getClientIp(),
        		'source'      => $source,
        		'date'        => date('Y-m-d H:i:s'),
        		'device_id'   => $deviceId,
        		'chap_id'     => $chapId
        		 
        );
        
        
        $aiKey = $this->insert($data
       );
        
      
    }
    
    
    public function getAllViews($cpid, $firstDayThisMonth=NULL, $lastDayThisMonth=NULL ) {
    
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01');
    	if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');
    
    
    	if($cpid)	{
    		$select = $this->fetchAll( $this->select()
    				->from('statistics_nexpager', array('statistics_nexpager.id'))
    				->where("statistics_nexpager.cp_id=?", $cpid)
    				->where("statistics_nexpager.date between '$firstDayThisMonth'and '$lastDayThisMonth'"));
    	}
    	 
    	return $select;
    }
    
    

}

?>
