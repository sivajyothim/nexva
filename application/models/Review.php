<?php

/**
 *
 * @copyright   neXva.com
 * @author      Cheran@nexva.com
 * @package     web
 *
 */
class Model_Review extends Zend_Db_Table_Abstract {

  protected $_name = "reviews";
  protected $_id = "id";
  protected $_totalReviews = 0;

    function __construct() {
        parent::__construct();
    }

    
    function getReviewsCountByContentId($contentId, $type = 'USER') {
        $select     = $this->select(false);
        $select
            ->from('reviews', array('count(id)'))
            ->where('product_id = ?', $contentId)
            ->where('status = ?', 'APPROVED');

        if ($type) {
            $select->where('type = ?', $type);
        }    
        
        $db         = Zend_Registry::get('db'); 
        
        $numRows    = $db->fetchOne($select);
        return $numRows;
    }
    
    function getReviewsByContentId($contentId, $limit=3, $type = 'USER') {
        $select     = $this->select();
        $select
            ->where('product_id = ?', $contentId)
            ->where('status = ?', 'APPROVED')
            ->limit($limit)
            ->order('date DESC');

        if ($type) {
            $select->where('type = ?', $type);
        }    
            
        $reviews = $this->fetchAll($select)->toArray();
        return $reviews;
    }

  /**
   * Returns the user id of the product that has been reviewed 
   */
    function getReviwedProductAndUser($reviewId) {
        $reviews  = $this->select();
      
        $reviews->setIntegrityCheck(false)
            ->from(array('R' => "reviews"), array())
            ->where("R.id = ?", $reviewId)
            ->joinLeft(array('P' => 'products'), 'P.id = R.product_id', array('P.id AS product_id', 'P.name AS product_name'))
            ->joinLeft(array('U' => 'users'), 'U.id = P.user_id', array('U.id AS user_id', 'U.username', 'U.email'))
            ->limit(1)
            ->query();
        $data = $this->fetchRow($reviews)->toArray();
        return $data;
  }
    
    function hasUserReviewedProduct($userId, $productId) {
        $db     = Zend_Registry::get('db');
        $result = $db->query('SELECT COUNT(id) AS count FROM reviews WHERE user_id = ? AND product_id = ?', array($userId, $productId))->fetch();
        return $result->count > 0;
    }
  
  
  function getReviewPercentage($contentId, $limit) {

    $reviews = $this->getReviewsByContentId($contentId, $limit);

    $reviewsCount = count($reviews);
    if ($reviewsCount > 0) {
      $total = NULL;

      foreach ($reviews as $review) {
        $total += $review['rating'];
      }

      $per = (($total / 5) / $reviewsCount) * 100;


      if ($per > 0 and $per <= 20) {
        $starCount = 1;
      } elseif ($per > 20 and $per <= 40) {
        $starCount = 2;
      } elseif ($per > 40 and $per <= 60) {
        $starCount = 3;
      } elseif ($per > 60 and $per <= 80) {
        $starCount = 4;
      } elseif ($per > 80 and $per <= 100) {
        $starCount = 5;
      }
      return $starCount;
    } else {
      return 0;
    }


    return $reviewPercentage;
  }

  function getLatestReviews($limit) {

    $reviews = $this->select();

    $reviews->setIntegrityCheck(false)
        ->from("reviews", array('reviews.id', 'reviews.user_id', 'reviews.product_id', 'reviews.review', 'reviews.rating', 'reviews.date', 'reviews.status'))
        ->joinInner('users', 'reviews.user_id = users.id', 'email')
        ->join('products', 'reviews.product_id = products.id', 'name')
        ->order('date desc')
        ->limit($limit)
        ->query();

    return $this->fetchAll($reviews)->toArray();
  }

  function unApprovedlistReview() {

    $select = $this->select()->where('status <> ?', 'APPROVED');
    $rowSet = $this->fetchAll($select);
    $rowCount = count($rowSet);


    return $rowCount;
  }

  /*
   * This funtion returns the average ratings for a product id
   * @param int product id
   * @return float productRating or NULL
   */

  function getAverageRating($productId) {

    $productRating = $this->select();

    $cache     = Zend_Registry::get('cache');
    $key        = 'PRODUCT_RATING_COUNTSUM_' . $productId;
    if (($productRatingVal = $cache->get($key)) === false) {
        $productRating->from("reviews", array_merge(array(new Zend_Db_Expr('SUM(rating) AS sum_of_rating')),
                array(new Zend_Db_Expr('COUNT(id) AS no_of_rating'))))
        ->where('product_id = ?', $productId)
        ->query();

        $productRatingVal = $this->fetchAll($productRating)->toArray();
        $cache->set($productRatingVal, $key);
    }
    

    if ($productRatingVal[0]['no_of_rating'] != 0) {
      $this->_totalReviews = $productRatingVal[0]['sum_of_rating'];
      $productRating = $productRatingVal[0]['sum_of_rating'] / $productRatingVal[0]['no_of_rating'];
      return round($productRating, 1);
    } else {
      return NULL;
    }
  }

  /**
   * Get number of reviews
   * @param <type> $productId
   * @return <type>
   */
  function getNumberOfReviews() {
    return $this->_totalReviews;
  }
    
    
    /**
     * 
     * Enter description here ...
     * @param $deviceId the device you're getting the reviews for 
     * @param $appFilter the price filter that applies only to chaps
     */
    function getReviewsForCompatibleApps($deviceId, $page = 0, $limit = 20, $appFilter = 'ALL') {
        $productDevices = new Model_ProductDevices(); 
        $filter['join'] = array(
            'reviews'   => 'reviews.product_id = products.id'
        );
        
        /**
         * This stuff shouldn't be here, but legacy code sucks :( 
         */
        $filter['where'] = array(
            'reviews.type = "EVA"',
            "reviews.status = 'APPROVED'"
        );
        if ($appFilter == 'FREE') {
            $filter['where'][]    = ' price = 0 ';
        } else if ($appFilter == 'PAID') {
            $filter['where'][]    = ' price > 0 ';
        }  
        
        
        $filter['order'] = array(
            'reviews.id DESC'
        );
        
        $filter['limit']        = $limit;
        $filter['offset']       = $limit * $page;
        
        /**
         * This query just returns a list of products that have reviews
         *  and are compatible with the device. Would have been nice to 
         *  just pull it from the DB, but that's not possible right this moment
         */
        $query      = $productDevices->getProducts(array($deviceId), $filter);
        $db         = Zend_Registry::get('db');
        $results    = $db->fetchAll($query);
        $ids    = array();
        if ($results) {
            foreach ($results as $row) {
                $ids[]  = $row->product_id;
            }
        } else {
            return array();
        }
        //now load the actual reviews
        $productFields  = array(
            'id as product_id', 'name AS product_name', 'thumbnail'
        );
        $reviews    = $this->select(false)->setIntegrityCheck(false)
                        ->from('reviews', array('id', 'product_id', 'name', 'title', 'review', 'rating', 'date'))
                        ->joinLeft('products', 'products.id = reviews.product_id', $productFields)
                        ->where('reviews.type = ?', "EVA")
                        ->where('reviews.status = ?', "APPROVED")
                        ->where('product_id IN(?)', $ids); 
        return $this->fetchAll($reviews);
    }
}

?>
