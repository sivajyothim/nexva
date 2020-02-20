<?php
/**
 * Encapsulated model to clear cache variables from memcached
 *
 * @copyright neXva.com
 * @author John Pereira
 * @date Mar 9, 2011
 */
class Model_CacheUtil extends Zend_Db_Table_Abstract {
    /**
     * 
     * For the keygroups, make sure you only add the group (the part before the 
     * last underscore. Also, make sure to add to all relevant groups. More than 1
     * is encouraged! 
     * 
     * @link http://trac.nexva.com/wiki/Documentation/Db/Cache
     */
    
    private $keyGroupProduct    = array(
        'PRODUCT_BASE', 'DEVICEPRODUCT', 'COMPATIBLE_PRODUCTS', 'COMPATIBLE_PRODUCTS_RESULTS',
        'COMPATIBLE_DEVICES', 'CATEGORY_ID_BY_PRODUCT', 'PRODUCT_IS_FEATURED', 
        'PRODUCT_PRICE', 'PRODUCT_RATING_COUNTSUM', 'PRODUCT_CP_ID', 'PRODUCT_AVG_RATING',
        'PRODUCT_TOTAL_RATING', 'EAV_FULL_Model_ProductMeta'
    );
    
    private $keyGroupCategory = array(
        'SITE_CATEGORIES', 'FRONTPAGE_CATEGORIES', 'CATEGORY_ID_BY_PRODUCT', 
        'CATEGORY_BREADCRUMB', 'PRODUCT_BASE'
    );
    
    private $keyGroupDevice = array(
        'DEVICEATTR_KEY', 'DEVICEPRODUCT_KEY', 'COMPATIBLE_DEVICES'
    );
    
    private $keyGroupMeta = array(
        'EAV_FULL_Model_ProductMeta', 'EAV_FULL_Model_UserMeta', 
        'EAV_FULL_Model_DeviceMeta', 'EAV_FULL_Model_ThemeMeta' 
    );
    
    private $keyGroupTheme = array(
        'EAV_FULL_Model_UserMeta', 'EAV_FULL_Model_ThemeMeta', 'THEME_META', 'EAV_FULL_Model_UserMeta_'
    );
    
    private $keyGroupUser = array(
        'EAV_FULL_Model_UserMeta', 'THEME_META', 'EAV_FULL_Model_UserMeta_'
    );
    
    private $keyGroupPhrase = array('PHRASES_');
    
    /**
     * 
     * Be very careful with this method. Clears a whole bunch of keys, use ONLY if you really 
     * need to clear all those keys
     * @param unknown_type $type
     */
    function clearType($type) {
        $keyGroups   = array();
        $theKey = 'keyGroup' . ucfirst($type);
        if (isset($this->$theKey)) {
            $keyGroups   = $this->$theKey;
        }

        $cache      = Zend_Registry::get('cache');
        $keys       = $cache->get('SITEKEYS');
        foreach ($keyGroups AS $keyGroup) {
            if (isset($keys[$keyGroup])) {
                foreach ($keys[$keyGroup] AS $singleKey) { 
                    $cache->remove($singleKey);
                } 
                $keys[$keyGroup]    = array();
            }
        }
        $cache->set($keys, 'SITEKEYS');
    }
    
    /**
     * 
     * Basic method to clear the prodcuct caches. Clears some device 
     * specific caches as well. Device caches will be cleared fully
     */
    function clearProduct($id = null) {
        $keys   = array(
            "PRODUCT_BASE_{$id}",
            "PRODUCT_IS_FEATURED_{$id}",
            "PRODUCT_PRICE_{$id}",
            "PRODUCT_RATING_COUNTSUM_{$id}",
            "PRODUCT_CP_ID_{$id}",
            "PRODUCT_AVG_RATING_{$id}",
            "PRODUCT_TOTAL_RATING_{$id}",
            "EAV_FULL_Model_ProductMeta_{$id}"
        );
        $this->clear($keys);
        $this->clearType('device'); //because device types are stored by hash. need to clear all
        $this->clearType('category'); //ditto for category
    }
    
    /**
     * Clears phrases for a section
     * @param $pageCode
     * @param $langId
     */
    function clearPhrases($pageCode, $langId = null) {
        $pageCode   = str_ireplace('-', '', $pageCode); 
        if ($langId === null) {
            $langModel  = new Model_Language();
            $langs      = $langModel->fetchAll();
            $keys       = array();
            foreach ($langs as $lang) {
                $keys   = 'PHRASES__' . $pageCode . $lang->id;
            }    
        } else {
            $keys   = array('PHRASES__' . $pageCode . $langId);
        }
        $this->clear($keys);
    }
 
    
    function clearUser($id) {
        $keys   = array(
            "EAV_FULL_Model_ThemeMeta_{$id}",
            "EAV_FULL_Model_UserMeta_{$id}"
        );
        $this->clear($keys);
    }
    
    function clear($keys = array()) {
        $cache      = Zend_Registry::get('cache');
        foreach ($keys as $key) {
            @$cache->remove($key);
        }
    }
}