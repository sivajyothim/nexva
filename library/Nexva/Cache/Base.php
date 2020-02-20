<?php
/**
 * Base cache control class. Basically works with only the CORE front end
 *
 * @copyright neXva.com
 * @author John Pereira
 * @date Feb 7, 2011
 */
class Nexva_Cache_Base {
    private $cache;
    const CACHE_MEMCACHED   = 'Memcached';
    const CACHE_FILECACHE   = 'File';
    
    public function __construct($backend = null, $opts = array()) {
        $config     =  $this->getConfig(); 
        $backend    = (!$backend) ? $config->cache->defaultMethod : $backend;
        
        $cache      =   Zend_Cache::factory('Core',
                            $backend,
                            $this->getFrontEndOptions($opts),
                            $this->getBackEndOptions($backend)
                        );
        $this->cache    = $cache;
    }
    
    function get($key) {
        $key        = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
        return @$this->cache->load($key);
    }
    
    function set($data, $key, $lifetime = null) {

        $key        = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
        $config     = $this->getConfig();
        $lifetime   = (!$lifetime) ? $config->cache->lifetime : $lifetime;
        $this->storeKey($key);        
        return $this->cache->save($data, $key, array(), $lifetime);
    }
    
    function remove($id) { 
        $id        = preg_replace('/[^a-zA-Z0-9_]/', '_', $id);
        return $this->cache->remove($id);
    }

    /**
     * This method groups and stores key for later retrieval. This is used because we sometimes don't
     * know the key being used 
     */
    protected function storeKey($key) {
        $baseKey    = $key;
        if (substr_count($key, '_') > 1) {
            $baseKey    = substr($key, 0, strrpos($key, '_'));
        }
        
        /**
         * 
         * Exclusion list so we don't end up storing all keys. 
         */
        $excludes       = array(
            'EAV_FULL_Model_ProductMeta', 'EAV_FULL_Model_UserMeta', 
            'EAV_FULL_Model_DeviceMeta', 'EAV_FULL_Model_ThemeMeta',
            'PRODUCT_BASE', 'CATEGORY_ID_BY_PRODUCT',  'PRODUCT_AVG_RATING',
            'PRODUCT_PRICE', 'PRODUCT_RATING_COUNTSUM', 'PRODUCT_CP_ID', 
            'PRODUCT_TOTAL_RATING'
        ); 
        if (array_search($baseKey, $excludes) !== false) {
            return;   
        }
        
        $keyForKeys     = 'SITEKEYS';
        if (($keys = $this->get($keyForKeys)) === false) {
            $keys   = array();
            $keys[$baseKey] = array();            
        }
        $keys[$baseKey][$key] = $key;
        
        $config     =  $this->getConfig();
        $lifetime   = $config->cache->lifetime;
        $this->cache->save($keys, $keyForKeys, array(), ($lifetime * 2));
    } 
    
    /**
     * Gets the front end options for the cache object. in this case 'CORE'
     */
    protected function getFrontEndOptions($opts = array()) {
        $config     =  $this->getConfig();
        
        //merge user supplied opts with defaults
        $defaults       = array(
            'lifetime'          => $config->cache->lifetime,
            'caching'           => ($config->cache->enabled == 1) ? true : false,
            'cache_id_prefix'   => $config->cache->cacheIdPrefix,
            'automatic_serialization' => true
        );
        
        $opts       = array_merge($defaults, $opts);
        
        $frontendOptions = array(
            'lifetime'          => $opts['lifetime'],
            'caching'           => $opts['caching'],
            'cache_id_prefix'   => $opts['cache_id_prefix'],
            'automatic_serialization' => $opts['automatic_serialization']
        );
        
        return $frontendOptions;
    }
    
    /**
     * Returns the specific cache backend given the caching mechanism
     * @param $backend
     */
    protected function getBackEndOptions($backend = null) {
        $config     =  $this->getConfig();
        $opts       = array();
        
        switch ($backend) {
            case self::CACHE_FILECACHE :  
                $opts['cache_dir']   = $config->cache->file->dir;
                $opts['hashed_directory_level']   = $config->cache->file->hashedDirectoryLevel;
                break;
                
            case self::CACHE_MEMCACHED :
            default:  
                $server     = array(
                    'host' => $config->cache->memcached->host, 
                    'port' => $config->cache->memcached->port, 
                    'persistent' => true, 
                    'weight' => 1, 
                    'timeout' => $config->cache->memcached->timeout, 
                    'retry_interval' => $config->cache->memcached->retryInterval, 
                    'status' => true, 
                    'failure_callback' => null
                );
                $opts['servers']        = array($server);
                $opts['compression']    = true;
                break;
        }
        return $opts;
    }
    
    protected function getConfig() {
        return new Zend_Config_Ini(APPLICATION_PATH . '/configs/cache.ini', APPLICATION_ENV); 
    }
}