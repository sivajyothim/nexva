<?php

/**
 * This is the base class for the new Analytics module 
 * @author John
 */
class Nexva_Analytics_Base {
    
    protected $tableName       = null;
    private $conn               = null;
    private $connTemp           = null;
    private $analyticsEnabled   = null;
    
    
    
    public function __construct() {
       // $this->_checkStatus();
      //  $this->__initConnection();        
    }
    
    
    public function _getConnection() {
        return $this->conn;
    }
    
    /**
     * 
     * This is the connection we use to access all those temporary collections. Keep things separate
     */
    public function _getConnectionForTempCollections() {
        return $this->connTemp;
    }
    
    private function __initConnection() {
        $host       = Zend_Registry::get('config')->analytics->mongodb->host;
        $port       = Zend_Registry::get('config')->analytics->mongodb->port;
        $dbname     = Zend_Registry::get('config')->analytics->mongodb->dbname;
        $this->analyticsEnabled = Zend_Registry::get('config')->analytics->enabled; 
        try {
            $mongo      = new Mongo("mongodb://{$host}:{$port}", array("persist" => "nexva_analytics"));
            $this->conn = $mongo->selectDB($dbname);
            $this->connTemp = $mongo->selectDB($dbname . '_temps');  
        } catch (Exception $ex) {
            if ($this->analyticsEnabled == 1) {
                throw $ex; //throw error only if analytics is enabled    
            }
        }
        
        
    }
    
    /**
     * You don't really need to call this method to get the collection
     * Due to the way Mongo works, it will just create a collection if it's not 
     * there. I do it this way so it will fail if the connection is not present 
     * @param string $table
     * @param boolean $fromTemp
     * @throws Exception
     */
    public function _getCollection($table = null, $fromTemp = false) {
        if (!$this->conn) {
            throw new Exception("We're sorry, Analytics is not avaialble at the moment. Please contact site neXva support. ");     
        }
        
        if (!$table) {
            $table  = $this->tableName;
        }
        
        if ($fromTemp) {
            return $this->connTemp->selectCollection($table);    
        } else {
            return $this->conn->selectCollection($table);
        }
        
    }
    
    protected function _save($data, $table) {
        try {
            if ($this->conn) {
                $collection     = $this->conn->$table;
                $collection->insert($data);
            }
        } catch (Exception $ex) {
            /**
             * @todo log the error?
             */
            $message    = $ex->getMessage();
            Zend_Registry::get('logger')->err($message);
        }
        
    }
    
    /**
     * Checks whether analytics is enabled and returns false if not
     */
    protected function _checkStatus() {
        if ($this->analyticsEnabled === null) {
            $this->analyticsEnabled = Zend_Registry::get('config')->analytics->enabled;         
        } 
        if ($this->analyticsEnabled != 1) { 
            throw new Exception("We're sorry, Analytics is disabled at the moment");
        }
    }
    
    /**
     * Drops a temporary collection after use. Usually used by the aggragation functions
     * to clear out temporary collections. Keep in mind that Temporary collections 
     * have _ (underscore) in front of the name. Only collections in the temp DB can be removed with this method
     * @param string $collection collection name
     */
    public function _dropCollection($collection = '') {
        $this->connTemp->selectCollection($collection)->drop();
        //add other cleanup if required
        return true;    
    }
    
    /**
     * This method pads the data to fill start and end times. 
     * NOTE - Uses miliseconds NOT seconds 
     * @param $data - Data must be sorted Past => Future (time ascending)
     * @param $startDate
     * @param $endDate
     * @param $gap
     */
    public function padDataArrayWithDatesUsingMiliseconds($data = array(), $startDate = null, $endDate = null, $gap = 3600) {
        if (!$startDate || !$endDate) {
            return $data;
        }
        
        /**
         * 
         * This block of code basically goes through the given array to add 0s 
         * to appropriate data areas. This is useful because otherwise the graph
         * just plots the date as non-existent. 
         */
        $count  = 0;
        $old    = '';
        $innerPadding  = array();
        foreach ($data as $time => $value) {
            $count++;
            if ($count == 1) {
               $old    = (float) $time;
               continue; 
            }
            
            $gapInData  = ($old - (float)$time);  //figure out what gap is missing
            if ($gapInData > $gap) {
                $blocks = round($gapInData / $gap); //the missing periods
                while ($blocks > 0) {
                    $key    = (float)$time + (float)($gap * $blocks); //add the missing values and set to 0
                    $innerPadding[(string)$key]  = 0;
                    $blocks--;
                }
            }
            
            $old    = (float)$time;
        }//we'll add this later
        
        //pad front
        $dates      = array_keys($data);
        //most recent dates first
        if (empty($dates)) {
            $dataStart    = time() * 1000;    
        } else {
            $dataStart    = $dates[sizeof($dates) - 1];
        }
        
        
        if ($startDate < $dataStart) {
            for ($i = $startDate; $i < $dataStart; $i += $gap) {
                if (isset($data[$i . ''])) {continue;}
                $data[$i . '']   = 0;
            }
        }
        
        
        if (isset($dates[0])) {
            $dataEnd  = $dates[0];
        } else {
            $dataEnd  = time() * 1000;
        }

        
        if ($endDate > $dataEnd) {
            for ($i = $dataEnd + $gap; $i < $endDate; $i += $gap) {
                if (isset($data[$i . ''])) {continue;}
                $data[$i . '']   = 0;
            }
        }
        $data   = $data + $innerPadding;  
        krsort($data);
        return $data;
    }

    /**
     * We'll run a few checks on the dates to make sure there isn't any 
     * funny business. Valid date blocks are last week, last month, last 6 months
     * 
     * Make sure not to mess with these dates. If you change these dates, you 
     * need to change the numbers for the cache generator as well.
     * 
     * @param $start
     * @param $end
     */
    public function validateDates(&$start, &$end) {
        $sixMonthsAgo   = strtotime("-6 months");
        $day            = 86400;
        $end            = time();
        
        if (!$start) {
            $start    = strtotime("-7 days");
        }
        
        $gap    = $end - $start; 
        
        //Don't mess the dates!
        switch ($gap) {
            case $gap < ($day * 31) :
                $gap    = $day * 7;
                break;

            case $gap < ($day * 180) ://6 months
                $gap    = $day * 31;
                break;
                
            case $gap >= ($day * 180) : 
            default : 
                $gap    = $day * 180; 
                break;
        }
        
        $end    = time();
        $start  = $end - $gap;
    }
    
    /**
     * Returns the prefered gap period for a given time interval in seconds.
     * This used to return the group period (tick) for a given date range
     * But since we now cache data, we return half day as default
     * @param $period
     */
    public function getChartGroupGap($period = null) {
        return 86400 / 2; //just trying out group by day as default
    }
    
    
    /**
     * 
     * Returns the furthest date analytics is supposed to be 
     * generated for
     * @todo do some rounding to get to the nearest day in seconds
     * 
     * @return timestamp
     */
    protected function getLowerBoundDate() {
        return $this->roundToNearestDay(time() - 15552000); //180 days in secs
    }
    
    /**
     * 
     * Returns the period gaps that are used throughout the system
     */
    protected function getTimePeriods() {
        return array(
            '604800', //7 days 
            '2678400', //31 days
            '15552000' //180 dats
        );
    }
    
    /**
     * Rounds the timestamp to the nearest day
     */
    protected function roundToNearestDay($timestamp) {
        return round($timestamp / 86400) * 86400;
    }
    
    
    /**
     * 
     * Creates a unique list of IPs for a collection. This is used to generate 
     * the country information
     * @param string $tempCollection Name of collection to output the data to
     * @param string $collection Name of the collection you want to scan. Default, current collection defined in class
     * @param string $ipField Name of the ipcolumn name, default "ip"
     */
    public function generateUniqueIpList($tempCollection, $collection = null, $ipField = "ip") {
        if (!isset($tempCollection)) return false;
        
        $db     = $this->_getConnection();
        $tempDb = $this->_getConnectionForTempCollections();
        
        if (!$collection) $collection = $this->tableName;
        $collection = (string) $collection;
        
        
        //have to use a map/reduce because group key max is 12000
            $map        = new MongoCode("
                function() {
                   emit({ip:this.{$ipField}}, 1);
                }
            ");
            
            $reduce     = new MongoCode('
                function(k, vals) {
                    var sum = 0;
                    for (var i in vals) {
                        sum += vals[i]; 
                    }
                    return sum; 
                }
            ');
            
            $cmd        = array(
                'mapreduce' => $collection, //source collection
                'map'       => $map,
                'reduce'    => $reduce,
                'out'       => array('replace' => $tempCollection, 'db' => (string)$tempDb), //outputs to a new collection
                'verbose'   => true
            );
            $tempOpts['country']['$exists']    = false; 
            $cmd['query']   = $tempOpts; 
            
            $views          = $db->command($cmd);     
    }
    
    
    /**
     * Adds the commons filters to analytics 
     * views.Currently we only exclude bots
     * Enter description here ...
     */
    public function addCommonFilters(&$opts = array()) {
        //   ua : {$nin  : [/(bot)|(crawl)|(spider)|(slurp)/i]}          
        $opts['ua']['$not'] = new MongoRegex("/(bot)|(crawl)|(spider)|(slurp)/i"); 
    }
}