<?php
/**
 * ClassDescription
 * 
 * @author Maheel De Silva
 * @date Jan 25, 2012
 */
class Mobile_Model_Rating extends Zend_Db_Table_Abstract {

    protected $_name    = 'ratings';
    protected $_primary = 'id';
    
    function __construct() {
        parent::__construct();
    }    
    
    //add rating for a product
    function addRatingForProduct($proId,$rating)
    {
        $data   = array(
                        'product_id'    => $proId,
                        'rating'        => $rating,
                        'ip'            => @$_SERVER['REMOTE_ADDR']
                        );
        
        $this->insert($data);
        $this->clearCache($proId);
        
       return $this->getAdapter()->lastInsertId();
       
    }
    
    //checck if user has rated this product already
    function checkProductRatedByUser($user_ip,$product_id)
    {
        $productSql    = $this->select();  
        $productSql
                ->from(array('ratings'),array('count(*) as amount'))
                ->where('ip = ?',$user_ip)
                ->where('product_id = ?',$product_id);
               
        $rowCount = $this->fetchAll($productSql);
        return($rowCount[0]->amount); 
      
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
}