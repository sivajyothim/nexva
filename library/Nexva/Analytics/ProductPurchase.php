<?php
class Nexva_Analytics_ProductPurchase extends Nexva_Analytics_Base {
    
        
    public function __construct() {
        parent::__construct();
        $this->tableName    = 'app_purchases';
    }
    
    public function log($opts) {
        $required   = array(
            'app_id', 'device_id', 'device_name', 'price'  
        );
        
        foreach ($required as $key) {
            if (!isset($opts[$key])) {
                throw new Nexva_Analytics_Exception($key . ' has not been set');
            }
        }
        
        $request        = new Zend_Controller_Request_Http();
        $opts['ip']     = empty($opts['ip']) ? $request->getClientIp() : $opts['ip'];
        $opts['ua']     = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        $opts['app_id']     = (int) $opts['app_id'];
        $opts['chap_id']    = (int) $opts['chap_id'];
        $opts['price']      = (float) $opts['price'];
        $opts['device_id']  = (int) $opts['device_id'];

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
        }
    }
    
    
   /**
     * 
     * Returns purchases that used a promocode grouped by date
     * @param int $userId the owner of the promotion code
     * @param timestamp $startDate in seconds
     * @param timestamp $endDate in seconds
     */
    public function getCodePurchasesByDate($userId, $startDate, $endDate, $gap = 86400) { 
        $opts   = array(
            'code'                  => array('$exists' => true),
            'code.code_owner_id'    => (int) $userId,
            'timestamp'             => array('$gt' => $startDate, '$lt' => $endDate) 
        );
        
        $collection = $this->_getCollection();  
        
        $keys       = array("timestamp" => 1);
        
        $keys       = new MongoCode('
            function(doc) { 
                return {day : Math.round((doc.timestamp * 1000) / 86400000) * 86400000}; 
            }'
        );
        
        $initial    = array('count' => 0);
        $reduce     = "function (obj, prev) {
              prev.count++;
        }";
        
        $purchases        = $collection->group($keys, $initial, $reduce, $opts);  
        $purchasesByDate  = array();
        if (isset($purchases['retval'])) {
            foreach ($purchases['retval'] as $row) {
                $purchasesByDate[(string) $row['day']]    = $row['count'];
            }
        } else {
            return array();//error
        } 
        $purchasesByDate  = $this->padDataArrayWithDatesUsingMiliseconds($purchasesByDate, $startDate * 1000, $endDate * 1000, $gap * 1000);// * 1000 because secs => ms
        return $purchasesByDate;
    }
}