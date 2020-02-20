<?php
/**
 * 
 * Cache generator for ProductView analytic methods. 
 * method name follows a standard for easy lookup
 * 
 * e.g. - cacheXXXXXX where XXXXXX is the original method name in the class  
 *
 * Should only be run on CLI mode
 * 
 * @author John
 *
 */
class Nexva_Analytics_CacheBuilder_ProductView extends Nexva_Analytics_Base {
    protected $tableName  = 'app_views';
    private $verbose    = false;
    
    public function createCache($beVerbose = false) {
        $this->verbose  = $beVerbose;
        
        /**
         * 
         * Creates the caches for Nexva_Analytics_ProductView::getAppViewsByDateForAllApps
         * and Nexva_Analytics_ProductView::getAppViewsByDateForApp
         */
        
        $this->output('BEGIN Caching app views by region');
        $this->cacheGetTotalViewsByRegion();
        $this->output('END Caching app views by by region');
        $this->output('');
        
        $this->output('BEGIN Caching app views by date for all apps');
        $this->cacheGetAppViewsByDateForAllApps();
        $this->output('END Caching app views by date for all apps');
        $this->output('');
        
        $this->output('BEGIN Caching app views by date for single apps');
        $this->cacheGetAppViewsByDateForApp();
        $this->output('END Caching app views by date for single apps');
        $this->output('');
        
        $this->output('BEGIN Caching app views by device');
        $this->cacheGetAppViewsByDevice();
        $this->output('END Caching app views by device');
        $this->output('');
        
        $this->output('BEGIN Caching app views grouped by app');
        $this->cacheGetAppViewsByApp();
        $this->output('END Caching app views grouped by app');
        $this->output('');
        
        $this->output('BEGIN Caching app views grouped by CHAP');
        $this->cacheGetAppViewsByChap();
        $this->output('END Caching app views grouped by CHAP');
        $this->output('');
    }
    
    
    
    /**
     * Caches geographical data for views
     * @throws Nexva_Analytics_Exception
     */
    private function cacheGetTotalViewsByRegion() {
        $db         = $this->_getConnection();
        $tempDb     = $this->_getConnectionForTempCollections();
        
        $gaps   = $this->getTimePeriods(); 
        foreach ($gaps as $gap) {
            $this->output('Caching period : ' . $gap, 2);
            
            $collectionStr = '_viewCountsGroupedByRegion_' . $gap;
            $collection = $this->_getCollection($collectionStr, true);

            $map        = new MongoCode("
                function() {
                   emit({   cp_id: this.cp_id, 
                            app_id: this.app_id, 
                            chap_id: (this.chap_id) ? this.chap_id : false, 
                            country_name: this.country.name, 
                            country_code: this.country.code}
                        , 1);
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
                'mapreduce' => $this->tableName, //source collection
                'map'       => $map,
                'reduce'    => $reduce,
                'out'       => array('replace' => $collectionStr, 'db' => (string)$tempDb), //outputs to a new collection
                'verbose'   => true
            );
            
            $tempOpts   = array();
            $tempOpts['timestamp']['$gte'] = $this->roundToNearestDay(time() - $gap);
            $tempOpts['timestamp']['$lte'] = $this->roundToNearestDay(time()); 
            $tempOpts['country']['$exists']= true;

            $this->addCommonFilters($tempOpts);
            
            $cmd['query']   = $tempOpts; 
            
            $views          = $db->command($cmd);     
            if ($views['ok'] == 0) {
                throw new Nexva_Analytics_Exception($views['errmsg']);
            }     
        }
    } 
    
    
    /**
     * Caches app views by chap
     */
    private function cacheGetAppViewsByChap() {
        $db         = $this->_getConnection();
        $tempDb     = $this->_getConnectionForTempCollections();
        
        $gaps   = $this->getTimePeriods(); 
        
        foreach ($gaps as $gap) {
            $this->output('Caching period : ' . $gap, 2);
            
            $collectionStr = '_viewCountsGroupedByChap_' . $gap;
            $collection = $this->_getCollection($collectionStr, true);

            $map        = new MongoCode("
                function() {
                   emit({app_id: this.app_id, chap_id: this.chap_id, cp_id: this.cp_id}, 1);
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
                'mapreduce' => $this->tableName, //source collection
                'map'       => $map,
                'reduce'    => $reduce,
                'out'       => array('replace' => $collectionStr, 'db' => (string)$tempDb), //outputs to a new collection
                'verbose'   => true
            );
            
            $tempOpts   = array();
            $tempOpts['timestamp']['$gte'] = $this->roundToNearestDay(time() - $gap);
            $tempOpts['timestamp']['$lte'] = $this->roundToNearestDay(time()); 
            $this->addCommonFilters($tempOpts);
            $cmd['query']   = $tempOpts; 
            
            $views          = $db->command($cmd);     
            if ($views['ok'] == 0) {
                throw new Nexva_Analytics_Exception($views['errmsg']);
            }     
        }
    }
    
    /**
     * 
     * Creates the most popular apps list to show trending
     */
    private function cacheGetAppViewsByApp() {
         //because we validate the date when it comes through, there won't be more than 3 collections
        //at any given time. Unless we add more date ranges
        
        $db         = $this->_getConnection();
        $tempDb     = $this->_getConnectionForTempCollections();
        
        $gaps   = $this->getTimePeriods(); 
        
        foreach ($gaps as $gap) {
            $this->output('Caching period : ' . $gap, 2);
            
            $collectionStr = '_viewCountsGroupedByApp_' . $gap;
            $collection = $this->_getCollection($collectionStr, true);

            $map        = new MongoCode("
                function() {
                   emit({app_id: this.app_id, cp_id: this.cp_id}, 1);
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
                'mapreduce' => $this->tableName, //source collection
                'map'       => $map,
                'reduce'    => $reduce,
                'out'       => array('replace' => $collectionStr, 'db' => (string)$tempDb), //outputs to a new collection
                'verbose'   => true
            );
            
            $tempOpts   = array();
            $tempOpts['timestamp']['$gte'] = $this->roundToNearestDay(time() - $gap);
            $tempOpts['timestamp']['$lte'] = $this->roundToNearestDay(time()); 
            $this->addCommonFilters($tempOpts);
            $cmd['query']   = $tempOpts; 
            
            $views          = $db->command($cmd);     
            if ($views['ok'] == 0) {
                throw new Nexva_Analytics_Exception($views['errmsg']);
            }     
        }
    }
    
    /**
     * 
     * Creates device view cache
     */
    private function cacheGetAppViewsByDevice() {
        $db             = $this->_getConnection();
        $tempDb         = $this->_getConnectionForTempCollections();
        
        $gaps           = $this->getTimePeriods();
        
        
        foreach ($gaps as $gap) {
            /**
             * Since the dates are normalized, we can use that to cache all our results.
             */
            $this->output('Caching period : ' . $gap, 2);
            $collectionStr     = '_viewCountsGroupedByDevice_' . $gap; 
            $collection     = $this->_getCollection($collectionStr, true);
    
            $map        = new MongoCode("
                function() {
                   for (device in this.device_id) {
                        emit({
                            device      : device, 
                            deviceName  : device + '-' + this.device_id[device], 
                            cp_id       : this.cp_id, 
                            app_id      : this.app_id
                        }, 1);
                   } 
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
                'mapreduce' => $this->tableName, //source collection
                'map'       => $map,
                'reduce'    => $reduce,
                'out'       => array('replace' => $collectionStr, 'db' => (string) $tempDb), //outputs to a new collection
                'verbose'   => true
            );
            
            $opts   = array();
            $opts['timestamp']['$gte'] = $this->roundToNearestDay(time()) - $gap;
            $opts['timestamp']['$lte'] = $this->roundToNearestDay(time());
            $this->addCommonFilters($opts);
            $cmd['query']   = $opts;
            $views          = $db->command($cmd);     
            if ($views['ok'] == 0) {
                throw new Nexva_Analytics_Exception($views['errmsg']);
            }       
        }
    }
    
    /**
     * Creates the view cache for all apps
     */
    private function cacheGetAppViewsByDateForApp() {
        $db         = $this->_getConnection();
        $tempDb     = $this->_getConnectionForTempCollections();
        $tempCollectionStr  = '_viewCountsGroupedByDateForApp';
        
        $gap        = $this->getChartGroupGap(null);
        $collection         = $this->_getCollection($tempCollectionStr, true);
        $map        = new MongoCode("
            function() {
                var rounded = (Math.round(this.timestamp / {$gap}) * {$gap});
                emit({timestamp: rounded, app_id: this.app_id}, 1); 
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
            'mapreduce' => $this->tableName, //source collection
            'map'       => $map,
            'reduce'    => $reduce,
            'out'       => array('replace' => $tempCollectionStr, 'db' => (string)$tempDb), //outputs to a new collection
        );

        $opts   = array();
        $opts['timestamp']['$gte'] = $this->getLowerBoundDate();
        $opts['timestamp']['$lte'] = $this->roundToNearestDay(time());
        $this->addCommonFilters($opts);            
        $cmd['query']   = $opts;
        $views          = $db->command($cmd);
        if ($views['ok'] == 0) {
            throw new Nexva_Analytics_Exception($views['errmsg']);
        }    
    }
    
    /**
     * Creates the cache for one app 
     */
    private function  cacheGetAppViewsByDateForAllApps() {
        
        $db                 = $this->_getConnection();
        $tempDb             = $this->_getConnectionForTempCollections();
        $tempCollectionStr  = '_viewCountsGroupedByDateForAllApps';
        /**
         * 
         * We now aggregate data by half day
         * and store in a temp collection for each CP. We then query that collection
         * to make the whole process faster 
         */
        $gap    = $this->getChartGroupGap(null);
        //first see if collection exists, if not create it. This is SLOOOW!
        //sadly mongo/php doesn't have a way to check whether a collection exists, so we do a count
        $map        = new MongoCode("
            function() {
                var rounded = (Math.round(this.timestamp / {$gap}) * {$gap});
                emit({timestamp : rounded, cp_id : this.cp_id}, 1); 
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
            'mapreduce' => $this->tableName, //source collection
            'map'       => $map,
            'reduce'    => $reduce,
            'out'       => array('replace' => $tempCollectionStr, 'db' => (string)$tempDb), //outputs to a new collection
        );

        //The actual filtering doesn't need to apply to the temp collection
        //The temp collection should contain all data for the period - 6 months for now
        $tempOpts   = array();
        $tempOpts['timestamp']['$gte'] = $this->getLowerBoundDate();
        $tempOpts['timestamp']['$lte'] = $this->roundToNearestDay(time());
        $this->addCommonFilters($tempOpts);                 
        $cmd['query']   = $tempOpts;
        
        $views          = $db->command($cmd);     
        if ($views['ok'] == 0) {
            throw new Nexva_Analytics_Exception($views['errmsg']);
        }    
    }
    
    /**
     * 
     * Method to echo out as the system progresses
     */
    private function output($text, $level = 1) {
        if ($this->verbose) {
            $text    = str_pad($text, strlen($text) + ($level * 2), "--", STR_PAD_LEFT);
            echo "|"  . $text . "\n";
        }
    }
    
    
}