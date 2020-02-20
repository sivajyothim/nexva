<?php
class Nexva_Db_Profiler_Generic extends Zend_Db_Profiler {

    private $callers    = array();
    
    public function queryStart($queryText, $queryType = null) {
        
        
        
        if (!$this->_enabled) {
            return null;
        }

        // make sure we have a query type
        if (null === $queryType) {
            switch (strtolower(substr(ltrim($queryText), 0, 6))) {
                case 'insert':
                    $queryType = self::INSERT;
                    break;
                case 'update':
                    $queryType = self::UPDATE;
                    break;
                case 'delete':
                    $queryType = self::DELETE;
                    break;
                case 'select':
                    $queryType = self::SELECT;
                    break;
                default:
                    $queryType = self::QUERY;
                    break;
            }
        }

        /**
         * @see Zend_Db_Profiler_Query
         */
        require_once 'Zend/Db/Profiler/Query.php';
        $this->_queryProfiles[] = new Zend_Db_Profiler_Query($queryText, $queryType);

        end($this->_queryProfiles);
        
        $key    = key($this->_queryProfiles);
        $trace  = debug_backtrace();
        $index  = 7;
        $data   = array(
            basename(@$trace[$index]['file']),
            @$trace[$index]['function'],
            @$trace[$index]['line'],
        );
        $this->callers[$key]    = implode('/', $data);         

        return $key;
    }
    
    public function queryEnd($queryId) {
        // Don't do anything if the Zend_Db_Profiler is not enabled.
        if (!$this->_enabled) {
            return self::IGNORED;
        }

        // Check for a valid query handle.
        if (!isset($this->_queryProfiles[$queryId])) {
            /**
             * @see Zend_Db_Profiler_Exception
             */
            require_once 'Zend/Db/Profiler/Exception.php';
            throw new Zend_Db_Profiler_Exception("Profiler has no query with handle '$queryId'.");
        }

        $qp = $this->_queryProfiles[$queryId];

        // Ensure that the query profile has not already ended
        if ($qp->hasEnded()) {
            /**
             * @see Zend_Db_Profiler_Exception
             */
            require_once 'Zend/Db/Profiler/Exception.php';
            throw new Zend_Db_Profiler_Exception("Query with profiler handle '$queryId' has already ended.");
        }

        // End the query profile so that the elapsed time can be calculated.
        $qp->end();
        /**
         * If filtering by elapsed time is enabled, only keep the profile if
         * it ran for the minimum time.
         */
        if (null !== $this->_filterElapsedSecs && $qp->getElapsedSecs() < $this->_filterElapsedSecs) {
            unset($this->_queryProfiles[$queryId]);
            return self::IGNORED;
        }

        /**
         * If filtering by query type is enabled, only keep the query if
         * it was one of the allowed types.
         */
        if (null !== $this->_filterTypes && !($qp->getQueryType() & $this->_filterTypes)) {
            unset($this->_queryProfiles[$queryId]);
            return self::IGNORED;
        }

        return self::STORED;
    }
    
    public function getQueries() {
        return $this->_queryProfiles;
    }
    
    public function getCallers() {
        return $this->callers;
    }
}