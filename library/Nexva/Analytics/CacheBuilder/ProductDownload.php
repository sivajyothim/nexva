<?php
/**
 * 
 * Cache generator for ProductDownload analytic methods. 
 * method name follows a standard for easy lookup
 * 
 * e.g. - cacheXXXXXX where XXXXXX is the original method name in the class  
 *
 * Should only be run on CLI mode
 * 
 * @author John
 *
 */
class Nexva_Analytics_CacheBuilder_ProductDownload extends Nexva_Analytics_Base {
    protected $tableName  = 'app_downloads';
    private $verbose    = true;
    
    public function createCache($beVerbose = false) {
        $this->verbose  = $beVerbose;
        
        $this->output('BEGIN Caching app downloads by region');
        $this->cacheGetTotalDownloadsByRegion();
        $this->output('END Caching app downloads by by region');
        $this->output('');
        
        $this->output('BEGIN Cache app downloads grouped by date for a single app');
        $this->cacheGetAppDownloadsByDateForApp();     
        $this->output('END Cache app downloads grouped by date for a single app');
        $this->output('');
        
        $this->output('BEGIN Cache app downloads grouped by date for a all app');
        $this->cacheGetAppDownloadsByDateForAllApps();  
        $this->output('END Cache app downloads grouped by date for a all app');
        $this->output('');
        
        $this->output('BEGIN Cache app downloads grouped by CHAP');
        $this->cacheGetAppDownloadsByChap();  
        $this->output('END Cache app downloads grouped by CHAP');
        $this->output('');
    }
 
    
    /**
     * Caches geographical data for downloads
     * @throws Nexva_Analytics_Exception
     */
    private function cacheGetTotalDownloadsByRegion() {
        $db         = $this->_getConnection();
        $tempDb     = $this->_getConnectionForTempCollections();
        
        $gaps   = $this->getTimePeriods(); 
        foreach ($gaps as $gap) {
            $this->output('Caching period : ' . $gap, 2);
            
            $collectionStr = '_downloadCountsGroupedByRegion_' . $gap;
            $collection = $this->_getCollection($collectionStr, true);

            $map        = new MongoCode("
                function() {
                   emit({cp_id: this.cp_id, app_id: this.app_id, chap_id: this.chap_id, country_name: this.country.name, country_code: this.country.code}, 1);
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
    private function cacheGetAppDownloadsByChap() {
        $db         = $this->_getConnection();
        $tempDb     = $this->_getConnectionForTempCollections();
        
        $gaps   = $this->getTimePeriods(); 
        
        foreach ($gaps as $gap) {
            $this->output('Caching period : ' . $gap, 2);
            
            $collectionStr = '_downloadCountsGroupedByChap_' . $gap;
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
     * Cache creator for app downloads by date
     * @throws Nexva_Analytics_Exception
     */
    private function cacheGetAppDownloadsByDateForApp() {
        $db         = $this->_getConnection();
        $tempDb     = $this->_getConnectionForTempCollections();
        $tempCollectionStr  = '_downloadCountsGroupedByDateForApp';
        
        $gap        = $this->getChartGroupGap();
        
        $collection         = $this->_getCollection($tempCollectionStr, true);
        $map        = new MongoCode("
            function() {
                var rounded = (Math.round(this.timestamp / {$gap}) * {$gap});
                emit({timestamp: rounded, app_id: this.app_id, cp_id : this.cp_id}, 1); 
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

        $tempOpts   = array();
        $tempOpts['timestamp']['$gte'] = $this->roundToNearestDay($this->getLowerBoundDate());
        $tempOpts['timestamp']['$lte'] = $this->roundToNearestDay(time());
        $this->addCommonFilters($tempOpts);            
        $cmd['query']   = $tempOpts;
        $result          = $db->command($cmd);     
        if ($result['ok'] == 0) {
            throw new Nexva_Analytics_Exception($result['errmsg']);
        }       
    }
    
    
    private function cacheGetAppDownloadsByDateForAllApps() {
        $db                 = $this->_getConnection();
        $tempDb             = $this->_getConnectionForTempCollections();
        $tempCollectionStr  = '_downloadsGroupedByDateForAllApps';
        /**
         * 
         * We now aggregate data by half day
         * and store in a temp collection for each CP. We then query that collection
         * to make the whole process faster 
         */
        $gap        = $this->getChartGroupGap();
        $tempCollection = $this->_getCollection($tempCollectionStr, true);
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
        $tempOpts['timestamp']['$gte'] = $this->roundToNearestDay($this->getLowerBoundDate());
        $tempOpts['timestamp']['$lte'] = $this->roundToNearestDay(time());
        $this->addCommonFilters($tempOpts);            
        $cmd['query']   = $tempOpts;
        
        $result         = $db->command($cmd);     
        if ($result['ok'] == 0) {
            throw new Nexva_Analytics_Exception($result['errmsg']);
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