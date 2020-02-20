<?php
/**
 * ClassDescription
 * 
 * @author John Pereira
 * @date Jan 6, 2011
 */
class Model_Rating extends Zend_Db_Table_Abstract {

    protected $_name    = 'ratings';
    protected $_primary = 'id';
    
    function __construct() {
        parent::__construct();
    }    
    
    function getAverageRatingByProduct($proId = null) {
        if (!$proId) {
            return 0;
        }
        
        $cache     = Zend_Registry::get('cache');
        $key        = 'PRODUCT_AVG_RATING_' . $proId;
        if (($productRating = $cache->get($key)) !== false) {
            return $productRating;
        }
        
        $ratings    = $this->select();  
        $ratings
            ->from(array('ratings'), array('AVG(rating)'))
            ->where('product_id = ?', $proId)
            //->where('chap_id = ?', $chapId)
            ->query();
        $result     = $this->fetchAll($ratings)->toArray();
        $avg        = $result[0]['AVG(rating)'];
        
        if (is_numeric($avg)) {
            $floorVal   = floor($avg);
            $part       = $avg - $floorVal;
            if ($part == 0 || $part == 0.5) {
                $roundup    = $avg;
            } elseif ($part <= 0.5) {
                $roundup    = $floorVal + 0.5;
            } else {
                $roundup    = $floorVal + 1;
            }
        } else {
            $roundup    = 0;
        }
        
        $cache->set($roundup, $key, 3600);
        return $roundup;
    }
    
    function getAverageRatingForManyProducts($proIds = array()) {
        if (empty($proIds)) {
            return array();
        }
        
        $ratings    = $this->select();  
        $ratings
            ->from(array('ratings'), array('product_id, AVG(rating)'))
            ->where('product_id IN (' . implode(',', $proIds) . ')')
            ->group('product_id')
            ->query();
            
        $results    = $this->fetchAll($ratings)->toArray();
        $ratings    = array();
        foreach ($proIds as $id) {
            $ratings[$id]   = 0; //defaults
        } 
        
        foreach ($results as $result) {
            $avg    = $result['AVG(rating)'];
            if (is_numeric($avg)) {
                $floorVal   = floor($avg);
                $part       = $avg - $floorVal;
                if ($part == 0 || $part == 0.5) {
                    $roundup    = $avg;
                } elseif ($part <= 0.5) {
                    $roundup    = $floorVal + 0.5;
                } else {
                    $roundup    = $floorVal + 1;
                }
            } else {
                $roundup    = 0;
            }
            $ratings[$result['product_id']] = $roundup; 
        }
        
        return $ratings;
    }
    
    
    function getTotalRatingByProduct($proId = false) {
        if (!$proId) {
            return 0;
        }

        $cache     = Zend_Registry::get('cache');
        $key        = 'PRODUCT_TOTAL_RATING_' . $proId;
        if (($totalRatings = $cache->get($key)) !== false) {
            return $totalRatings;
        }
        
        $ratings    = $this->select();  
        $ratings
            ->from(array('ratings'), array('COUNT(rating)'))
            ->where('product_id = ?', $proId)
            //->where('chap_id = ?', $chapId)
            ->query();
        $result     = $this->fetchAll($ratings)->toArray();
        $total      = $result[0]['COUNT(rating)'];
        
        $cache->set($total, $key, 3600);
        return $total;
    }
    
    function clearCache($proId = null) {
        if (!$proId) return false;
        
        $cache     = Zend_Registry::get('cache');
        $key        = 'PRODUCT_TOTAL_RATING_' . $proId;
        $cache->remove($key);
        $key        = 'PRODUCT_AVG_RATING_' . $proId;
        $cache->remove($key);
    }

    function userHasRated($userId,$proId){

        $now = date('Y-m-d H:i:s');
        $monthAgo = date('Y-m-d H:i:s', strtotime($now.'-1 month'));// we calculate one month time duration, so the user can rate an app once per month

        $sql    = $this->select();
        $sql->from(array('r'=>$this->_name))
            ->where('r.product_id = ?',$proId)
            ->where('r.user_id = ?',$userId)
            ->where('r.created_at >= ?',$monthAgo)
            ;

        $result = $this->fetchAll($sql);
        //echo count($result);die();

        //$statisticDownloadModel = new Model_StatisticDownload();
        //$downloaded = $statisticDownloadModel->userHasDownloaded($userId,$proId);

        //echo 'rated month ago ',count($result),' - downloaded ',count($downloaded);die();

        /*if(count($result)>0 && count($downloaded)>0){
            return true;   //user has downloaded & rated this app within a month period, so we return true
        } else {
            return false;
        }*/

        if(count($result)){
            return true;
        } else {
            return false;
        }
    }
}