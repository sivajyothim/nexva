<?php

class Api_Model_StatisticsProducts extends Zend_Db_Table_Abstract
{
    protected $_name = 'statistics_products';
    protected $_id = 'id';
    
    //changed the order of pram by rooban on 22-11-2013 - (earlier $userId, $deviceId)
    public function addViewStat($productId, $chapId, $source, $ip, $deviceId, $userId)
    {
    	
    	
        $geoData = Nexva_GeoData_Ip2Country_Factory::getProvider();
        $country  =   $geoData->getCountry($ip);
        
       $data = array
                (
                    'product_id' => $productId,
                    'chap_id' => $chapId,
                    'source' => $source,
                    'date' => new Zend_Db_Expr('NOW()'),
                    'session_id' => Zend_Session::getId(),
                    'ip' => $ip,
                    'device_id' => $deviceId,
                    'iso' => $country['code'],
                    'user_id' => $userId
                
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
    
    public function checkThisStatExist($productId, $chapId, $source, $ipAddress, $device_id = null){
        $sql =    $this->select()
                       ->from($this->_name, array('exist_count' => 'COUNT(id)'))
                       ->where('product_id = ?', $productId)
                       ->where('chap_id = ?', $chapId)
                       ->where('source = ?', $source)
                       ->where('ip = ?', $ipAddress)
                ;
        
        //Following condition for web only and not for mobile - matching the session
        if($source == 'WEB'){
             $sql = $sql->where('session_id = ?', Zend_Session::getId());
        }
        
        //Following condition for mobile web only
        if($device_id){
            $sql = $sql->where('device_id = ?', $device_id);
        }
        
        //echo $sql->assemble(); die();
     	$statRow = $this->fetchRow($sql);
        if(count($statRow)){
            return $statRow->toArray();
        }
     	else{
            return array('exist_count'=> 0);
        }
    }

    /**
     * @param $appId
     * @param $chapId
     * @return Views count for particular app under particular CHAP
     */
    public function getViewCountByAppChap($appId, $chapId){
        $sql =      $this->select()
                    ->from(array('sp' => $this->_name),array('count(sp.id) as count'))
                    ->where('sp.product_id = ?',$appId)
                    ->where('sp.chap_id = ?',$chapId)
                    ;
        $result = $this->fetchAll($sql)->toArray();

        if($result[0]['count'] > 0){
            return $result[0]['count'];
        } else {
            return 0;
        }

    }
}