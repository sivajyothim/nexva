<?php
class Nexva_Analytics_PromotionCode extends Nexva_Analytics_Base {
    
    public function __construct() {
        parent::__construct();
        $this->tableName    = 'promocode_applies';
    }
    
    public function log($opts) {
        $required   = array(
            'app_id', 'code_id', 'device_id', 'device_name', 'chap_id', 'code_owner_id'
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
        $opts['device_id']  = (int) $opts['device_id'];
        $opts['code_owner_id']  = (int) $opts['code_owner_id'];

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
     * Returns the promo code applies grouped by date
     * @param int $userId the owner of the promotion code
     * @param timestamp $startDate in seconds
     * @param timestamp $endDate in seconds
     */
    public function getCodeAppliesByDate($userId, $startDate, $endDate, $gap = 86400) { 
        
        $opts   = array(
            'code_owner_id'    => (int) $userId,
            'timestamp' => array('$gt' => $startDate, '$lt' => $endDate) 
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
        
        $applies        = $collection->group($keys, $initial, $reduce, $opts);
        $appliesByDate  = array();
        if (isset($applies['retval'])) {
            foreach ($applies['retval'] as $row) {
                $appliesByDate[(string) $row['day']]    = $row['count'];
            }
        } else {
            return array();//error
        }
        $appliesByDate  = $this->padDataArrayWithDatesUsingMiliseconds($appliesByDate, $startDate * 1000, $endDate * 1000, $gap * 1000);// * 1000 because secs => ms
        return $appliesByDate;
    }
    
 
}