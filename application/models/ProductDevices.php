<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_ProductDevices extends Zend_Db_Table_Abstract {

    // Product count
    private $_productCount = null;
    // product count with limit applied
    private $_productCountOnLimit = null;
    protected $_name = 'product_devices';
    protected $_id = 'id';
    protected $_referenceMap = array(
      'Model_Product' => array(
        'columns' => array('product_id'),
        'refTableClass' => 'Model_Product',
        'refColumns' => array('id')
      )
    );
    public $defaultLanguageId   = false;
    public $languageId          = false;
    public $appFilters          = array();
    
    function __construct() {
        parent::__construct();
    }

    /**
     *
     * @return <type>
     */
    public function getProductCount() {
        return $this->_productCount;
    }

    /**
     *
     * @return <type>
     */
    public function getProductCountOnLimit() {
        return $this->_productCountOnLimit;
    }

    /**
     *
     * @param <type> $deviceId
     * @param <type> $start
     * @param <type> $limit
     * @return <type>
     */
    public function getProductsById($deviceId, $start = 0, $limit = 0) {
        $rows = $this->fetchAll(
                    $this->select()
                    ->where('device_id = ?', $deviceId)
                    ->order('product_id ASC'));
        if ($start != 0 && $limit != 0)
            $this->limit($limit, $start);
        $this->_productCountOnLimit = $rows->count();
        return $rows;
    }

    /**
     * Get all products and products count.
     * @param array $deviceId
     * @return <type>
     * @todo Replace this with a join tables of Products table and get products which are approved
     */
    
    
    /**
     * 
     * Get all products and products count.
     * @param $deviceIds - Device IDs. This can be either scalar or an array
     * @param $filter - A filter array that contains joins/conditions and other SQL
     * @param $limit - used for paging
     * @param $page - used for paging
     * @param $cache - Used to indicate whether we should look in the cache or not. Default is to pull from cache
     * @todo Replace this with a join tables of Products table and get products which are approved
     */
    public function getAllProductsByDevId($deviceIds = null, $filter = null, $limit = null, $page = null, $getFromCache = true) {
        if ($limit !== null) {
            $start  = $limit * ($page - 1);
            $filter['limit']    = $limit * 3;
            $filter['offset']   = $start; //just to get a buffer so that we won't fail hard .. 
            //... if checkAttributes() blocks out any of the results
        }
        //Modified  - 15/03/2012    
        //$insert = $this->getProducts($deviceIds, $filter); //this was replaced with getProductsMobile - 15/3/2012
        $insert = $this->getProductsMobile($deviceIds, $filter); 
        

        
        $key    = 'DEVICEPRODUCT_KEY_' . md5((string) $insert);
        $cache  = Zend_Registry::get('cache');
        if ((($rows = $cache->get($key)) === false) || !$getFromCache) {
            $db     = Zend_Registry::get('db');
            $rows   = $db->fetchAll($insert);
            $cache->set($rows, $key);    
        } 
        $this->_productCount = count($rows); 
        $productIds = new stdClass();
        $count      = 0;
        foreach ($rows as $row) { 
            if ($this->checkAttributes($row->product_id, $deviceIds)) {
                $productIds->{$row->product_id} = new stdClass;
                $productIds->{$row->product_id}->product_id = $row->product_id;
                $count++;
            }

            if ($limit !== null && $count >= $limit) {
                return $productIds;                
            }
        }  
        return $productIds;
        
    }

    public function checkAttributes($productId, $deviceIds) {
    
        if (empty($deviceIds)) {
            return TRUE;
        }
        
        $deviceModel    = new Model_Device();
        $productModel   = new Model_ProductBuild();
        $buildAttributesModel = new Model_productDeviceAttributes();
        $width = $height = null;
        //get device attributes
        $deviceAttributes = $deviceModel->getDeviceAttributes($deviceIds);
        
        foreach ($deviceAttributes as $deviceAttribute) {
            $deviceAttrib[$deviceAttribute->device_attribute_definition_id] = $deviceAttribute->value;
        }
        
        // get all builds
        $build = $productModel->getBuildByProductAndDevice($productId);  
        if ($build === false) {
            return false;
        }
 
        if (isset($build->device_selection_type) && $build->device_selection_type == 'BY_ATTRIBUTE') {
            $buildAttriButes = array();
            $buildAttriButes = $buildAttributesModel->getSelectedDeviceAttributesById($build->build_id);
            
            if (isset($buildAttriButes[7])) {
                $width = $buildAttriButes[7];
            }
            if (isset($buildAttriButes[8])) {
                $height = $buildAttriButes[8];
            }
            
            // get the diff
            $diff = array_diff($buildAttriButes, $deviceAttrib);
            // if not enmty then there is some mis match on the attributes
            if (empty($diff)) {  
                return TRUE;
            } else if ($width > $deviceAttrib[7] && $height > $deviceAttrib[8]) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     *
     * @param <type> $deviceId
     * @return <type>
     * @todo Replace this with a join tables of Products table and get products which are approved
     */
    public function getAppCountByDevice($deviceId) {
        $this->getAllProductsByDevId($deviceId);
        return $this->_productCount;
    }

    /**
     * Get all products and products count.
     * @param array $deviceId device ids
     * @param array $filter filter devices using join and where
     * @param bool $category if it is set to true it needs to be order so that featured apps come first in the list 
     * @return object of DatabaseModel
     *
     */
    public function getProducts($deviceIds = null, $filter = null, $category = false) {
        // if devices are in an array then implode by comma
        if (is_array($deviceIds)) {
            $devices = implode(', ', $deviceIds);
        } else {
            $devices = (int) $deviceIds;
        }


        $buildDevices = new Model_ProductBuildDevices();
        $selectCustom = $buildDevices->select();
        $selectCustom->setIntegrityCheck(false);
        $selectCustom->from('build_devices', array());
        if (!empty($deviceIds))
            $selectCustom->where("device_id IN ($devices)");
        $selectCustom->join('product_builds', 'product_builds.id = build_devices.build_id', array('product_builds.product_id as product_id', 'product_builds.language_id'))
            ->join('products', 'products.id = product_builds.product_id', array('products.is_featured as is_featured'))
            ->where('product_builds.device_selection_type = ?', 'CUSTOM')
            ->where('products.status = ?', 'APPROVED')
            ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
            ->where("products.deleted <> '1'");

        // if there are any other join to use for further filtering
        if (!empty($filter['join'])) {
            foreach ($filter['join'] as $key => $join) {
                $selectCustom->join($key, $join, array());
            }
        }
        // if there are any othere where to use for further filtering
        if (!empty($filter['where'])) {
            foreach ($filter['where'] as $where) {
                $selectCustom->where($where);
            }
        }
        
         if (!empty($filter['order'])) {
            foreach ($filter['order'] as $order) {
                $selectCustom->order($order);
            }
        }
        
        
        // ge the products by attributes which are saved as attrubutes values.
        $deviceAttributes = new Model_DeviceAttributes();
        $selectAttributes = $deviceAttributes->select();
        $selectAttributes->from('device_attributes', array());
        $selectAttributes->setIntegrityCheck(false)
//        ->columns('product_builds.product_id as product_id')
            ->joinInner('product_device_saved_attributes', 'device_attributes.device_attribute_definition_id = product_device_saved_attributes.device_attribute_definition_id AND device_attributes.value = product_device_saved_attributes.value', array())
            ->joinInner('product_builds', 'product_device_saved_attributes.build_id = product_builds.id', array('product_builds.language_id'))
            ->joinInner('products', 'product_builds.product_id = products.id', array('products.id as product_id', 'products.is_featured as is_featured'))
            ->joinInner('devices', 'devices.id = device_attributes.device_id', array());
        if (!empty($deviceIds)) {
            $selectAttributes->where("device_id IN ($devices)");
        }
        $selectAttributes->where('product_builds.device_selection_type <> ?', 'CUSTOM')
            ->where('products.status = ?', 'APPROVED')
            ->where("products.deleted <> '1'")
            
            ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
            ->group(array('products.id', 'product_builds.language_id'));
            
        //get the device OS and add that to the mix as well
        $deviceModel        = new Model_Device();
        $deviceAttributes   = $deviceModel->getDeviceAttributes($deviceIds);
        $deviceAttrib       = array();
        foreach ($deviceAttributes as $deviceAttribute) {
            $deviceAttrib[$deviceAttribute->device_attribute_definition_id] = $deviceAttribute->value;
        }
        
        /**
         * This bit of code actually looks as the device OS to match products.
         * The IF clause checks whether the platform is set, if it's not ANY (0), then we do a OS match on it. 
         * If it's 0, we simply 1=1 which evaluates to true
         */
        if (isset($deviceAttrib[1])) {
            $selectAttributes->where('IF(product_builds.platform_id = 0, 1 = 1, product_device_saved_attributes.value = ?)'
                , $deviceAttrib[1]);
        }
            
        // if there are any other join to use for further filtering
        if (!empty($filter['join'])) {
            foreach ($filter['join'] as $key => $join) {
                $selectAttributes->join($key, $join, array());
            }
        }
        // if there are any othere where to use for further filtering
        if (!empty($filter['where'])) {
            foreach ($filter['where'] as $where) {
                $selectAttributes->where($where);
            }
        }
        
        if (!empty($filter['order'])) {
            foreach ($filter['order'] as $order) {
                $selectAttributes->order($order);
            }
        }
        /**
         * app filter code
         */
        if (!empty($this->appFilters)) {
            $appFilter          = new Nexva_Product_Filter_Filter();
            $selectAttributes   = $appFilter->applyRules($selectAttributes, $this->appFilters);
            $selectCustom       = $appFilter->applyRules($selectCustom, $this->appFilters);
        }
        
        //Zend_Debug::dump($selectAttributes->__toString());die();
        // get union of the custom and attribute wise devices
        $db = Zend_Registry::get('db');
        $pageselect = $db->select()->union(array("($selectAttributes)", "($selectCustom)"));

                
        if ($this->languageId && $this->defaultLanguageId) { //ordering by preferred languange
            $pageselect->order("FIELD(language_id, {$this->defaultLanguageId}, {$this->languageId}) DESC");
        }
        
        
        if (isset($filter['union']['order'])) {
            $pageselect->order($filter['union']['order']);
        }
        
        // if there are any limit in the filter variable
        $filter['offset']   = isset($filter['offset']) ? $filter['offset'] : 0;
        if (!empty($filter['limit'])) {
            $pageselect->limit($filter['limit'], $filter['offset']);
        }
        
        // for all category listings featured app is listed first    
        if($category) {
			$pageselect->order('is_featured DESC');
        }
        
        return $pageselect;
        
    }
    
    
    
    /**
     * Get all products for chap same as getProducts() but added  saperate ->join(array('cp' => 'chap_products'), 'cp.product_id = products.id', array())
     * so fucntion won't get comlicated.
     * @param array $deviceId device ids
     * @param array $filter filter devices using join and where
     * @param bool $category if it is set to true it needs to be order so that featured apps come first in the list 
     * @return object of DatabaseModel
     *
     */
    
    
	public function getProductsChap($deviceIds = null, $filter = null, $category = false) {
        // if devices are in an array then implode by comma
        if (is_array($deviceIds)) {
            $devices = implode(', ', $deviceIds);
        } else {
            $devices = (int) $deviceIds;
        }


        $buildDevices = new Model_ProductBuildDevices();
        $selectCustom = $buildDevices->select();
        $selectCustom->setIntegrityCheck(false);
        $selectCustom->from('build_devices', array());
        if (!empty($deviceIds))
            $selectCustom->where("device_id IN ($devices)");
        $selectCustom->join('product_builds', 'product_builds.id = build_devices.build_id', array('product_builds.product_id as product_id', 'product_builds.language_id'))
            ->join('products', 'products.id = product_builds.product_id', array('products.is_featured as is_featured'))
            ->join(array('cp' => 'chap_products'), 'cp.product_id = products.id', array()) 
            ->where('product_builds.device_selection_type = ?', 'CUSTOM')
            ->where('products.status = ?', 'APPROVED')
            ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
            ->where("products.deleted <> '1'");

        // if there are any other join to use for further filtering
        if (!empty($filter['join'])) {
            foreach ($filter['join'] as $key => $join) {
                $selectCustom->join($key, $join, array());
            }
        }
        // if there are any othere where to use for further filtering
        if (!empty($filter['where'])) {
            foreach ($filter['where'] as $where) {
                $selectCustom->where($where);
            }
        }
        
         if (!empty($filter['order'])) {
            foreach ($filter['order'] as $order) {
                $selectCustom->order($order);
            }
        }
        
        
        // ge the products by attributes which are saved as attrubutes values.
        $deviceAttributes = new Model_DeviceAttributes();
        $selectAttributes = $deviceAttributes->select();
        $selectAttributes->from('device_attributes', array());
        $selectAttributes->setIntegrityCheck(false)
//        ->columns('product_builds.product_id as product_id')
            ->joinInner('product_device_saved_attributes', 'device_attributes.device_attribute_definition_id = product_device_saved_attributes.device_attribute_definition_id AND device_attributes.value = product_device_saved_attributes.value', array())
            ->joinInner('product_builds', 'product_device_saved_attributes.build_id = product_builds.id', array('product_builds.language_id'))
            ->joinInner('products', 'product_builds.product_id = products.id', array('products.id as product_id', 'products.is_featured as is_featured'))
            ->joinInner(array('cp' => 'chap_products'), 'cp.product_id = products.id', array())
            ->joinInner('devices', 'devices.id = device_attributes.device_id', array());
        if (!empty($deviceIds)) {
            $selectAttributes->where("device_id IN ($devices)");
        }
        $selectAttributes->where('product_builds.device_selection_type <> ?', 'CUSTOM')
            ->where('products.status = ?', 'APPROVED')
            ->where("products.deleted <> '1'")
            
            ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
            ->group(array('products.id')); //removed by chathura   ->group(array('products.id', 'product_builds.language_id'));
            
        //get the device OS and add that to the mix as well
        $deviceModel        = new Model_Device();
        $deviceAttributes   = $deviceModel->getDeviceAttributes($deviceIds);
        $deviceAttrib       = array();
        foreach ($deviceAttributes as $deviceAttribute) {
            $deviceAttrib[$deviceAttribute->device_attribute_definition_id] = $deviceAttribute->value;
        }
        
        /**
         * This bit of code actually looks as the device OS to match products.
         * The IF clause checks whether the platform is set, if it's not ANY (0), then we do a OS match on it. 
         * If it's 0, we simply 1=1 which evaluates to true
         */
        if (isset($deviceAttrib[1])) {
            $selectAttributes->where('IF(product_builds.platform_id = 0, 1 = 1, product_device_saved_attributes.value = ?)'
                , $deviceAttrib[1]);
        }
            
        // if there are any other join to use for further filtering
        if (!empty($filter['join'])) {
            foreach ($filter['join'] as $key => $join) {
                $selectAttributes->join($key, $join, array());
            }
        }
        // if there are any othere where to use for further filtering
        if (!empty($filter['where'])) {
            foreach ($filter['where'] as $where) {
                $selectAttributes->where($where);
            }
        }
        
        if (!empty($filter['order'])) {
            foreach ($filter['order'] as $order) {
                $selectAttributes->order($order);
            }
        }
        /**
         * app filter code
         */
        if (!empty($this->appFilters)) {
            $appFilter          = new Nexva_Product_Filter_Filter();
            $selectAttributes   = $appFilter->applyRules($selectAttributes, $this->appFilters);
            $selectCustom       = $appFilter->applyRules($selectCustom, $this->appFilters);
        }
        
        //Zend_Debug::dump($selectAttributes->__toString());die();
        // get union of the custom and attribute wise devices
        $db = Zend_Registry::get('db');
        $pageselect = $db->select()->union(array("($selectAttributes)", "($selectCustom)"));

                
        if ($this->languageId && $this->defaultLanguageId) { //ordering by preferred languange
            $pageselect->order("FIELD(language_id, {$this->defaultLanguageId}, {$this->languageId}) DESC");
        }
        
        
        if (isset($filter['union']['order'])) {
            $pageselect->order($filter['union']['order']);
        }
        
        // if there are any limit in the filter variable
        $filter['offset']   = isset($filter['offset']) ? $filter['offset'] : 0;
        if (!empty($filter['limit'])) {
            $pageselect->limit($filter['limit'], $filter['offset']);
        }
        
        // for all category listings featured app is listed first    
        if($category) {
			$pageselect->order('is_featured DESC');
        }
        
        return $pageselect;
        
    }
    
    
    public function getProductsMobile($deviceIds = null, $filter = null, $category = false) {
        // if devices are in an array then implode by comma
        if (is_array($deviceIds)) {
            $devices = implode(', ', $deviceIds);
        } else {
            $devices = (int) $deviceIds;
        }


        $buildDevices = new Model_ProductBuildDevices();
        $selectCustom = $buildDevices->select();
        $selectCustom->setIntegrityCheck(false);
        $selectCustom->from('build_devices', array());
        if (!empty($deviceIds))
            $selectCustom->where("device_id IN ($devices)");
        $selectCustom->join('product_builds', 'product_builds.id = build_devices.build_id', array('product_builds.product_id as product_id', 'product_builds.language_id'))
            ->join('products', 'products.id = product_builds.product_id', array('products.is_featured as is_featured'))
            ->where('product_builds.device_selection_type = ?', 'CUSTOM')
            ->where('products.status = ?', 'APPROVED')
            //->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
            ->where("products.deleted <> '1'");

        // if there are any other join to use for further filtering
        if (!empty($filter['join'])) {
            foreach ($filter['join'] as $key => $join) {
                $selectCustom->join($key, $join, array());
            }
        }
        // if there are any othere where to use for further filtering
        if (!empty($filter['where'])) {
            foreach ($filter['where'] as $where) {
                $selectCustom->where($where);
            }
        }
        
         if (!empty($filter['order'])) {
            foreach ($filter['order'] as $order) {
                $selectCustom->order($order);
            }
        }
        
        
        // ge the products by attributes which are saved as attrubutes values.
        $deviceAttributes = new Model_DeviceAttributes();
        $selectAttributes = $deviceAttributes->select();
        $selectAttributes->from('device_attributes', array());
        $selectAttributes->setIntegrityCheck(false)
//        ->columns('product_builds.product_id as product_id')
            ->joinInner('product_device_saved_attributes', 'device_attributes.device_attribute_definition_id = product_device_saved_attributes.device_attribute_definition_id AND device_attributes.value = product_device_saved_attributes.value', array())
            ->joinInner('product_builds', 'product_device_saved_attributes.build_id = product_builds.id', array('product_builds.language_id'))
            ->joinInner('products', 'product_builds.product_id = products.id', array('products.id as product_id', 'products.is_featured as is_featured'))
            ->joinInner('devices', 'devices.id = device_attributes.device_id', array());
        if (!empty($deviceIds)) {
            $selectAttributes->where("device_id IN ($devices)");
        }
        $selectAttributes->where('product_builds.device_selection_type <> ?', 'CUSTOM')
            ->where('products.status = ?', 'APPROVED')
            ->where("products.deleted <> '1'")
            
            //->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
            ->group(array('products.id', 'product_builds.language_id'));
            
        //get the device OS and add that to the mix as well
        $deviceModel        = new Model_Device();
        $deviceAttributes   = $deviceModel->getDeviceAttributes($deviceIds);
        $deviceAttrib       = array();
        foreach ($deviceAttributes as $deviceAttribute) {
            $deviceAttrib[$deviceAttribute->device_attribute_definition_id] = $deviceAttribute->value;
        }
        
        /**
         * This bit of code actually looks as the device OS to match products.
         * The IF clause checks whether the platform is set, if it's not ANY (0), then we do a OS match on it. 
         * If it's 0, we simply 1=1 which evaluates to true
         */
        
        if (isset($deviceAttrib[1])) {
      $selectAttributes->where('IF(product_builds.platform_id = 0, 1 = 1, product_device_saved_attributes.value = ?)'
         //   $selectAttributes->where('product_device_saved_attributes.value = ?'
                , $deviceAttrib[1]);
        }
            
        // if there are any other join to use for further filtering
        if (!empty($filter['join'])) {
            foreach ($filter['join'] as $key => $join) {
                $selectAttributes->join($key, $join, array());
            }
        }
        // if there are any othere where to use for further filtering
        if (!empty($filter['where'])) {
            foreach ($filter['where'] as $where) {
                $selectAttributes->where($where);
            }
        }
        
        if (!empty($filter['order'])) {
            foreach ($filter['order'] as $order) {
                $selectAttributes->order($order);
            }
        }
        /**
         * app filter code
         */
        if (!empty($this->appFilters)) {
            $appFilter          = new Nexva_Product_Filter_Filter();
            $selectAttributes   = $appFilter->applyRules($selectAttributes, $this->appFilters);
            $selectCustom       = $appFilter->applyRules($selectCustom, $this->appFilters);
        }
       
        // get union of the custom and attribute wise devices
        $db = Zend_Registry::get('db');
        $pageselect = $db->select()->union(array("($selectAttributes)", "($selectCustom)"));

                
        if ($this->languageId && $this->defaultLanguageId) { //ordering by preferred languange
            $pageselect->order("FIELD(language_id, {$this->defaultLanguageId}, {$this->languageId}) DESC");
        }
        
        
        if (isset($filter['union']['order'])) {
            $pageselect->order($filter['union']['order']);
        }
        
        // if there are any limit in the filter variable
        $filter['offset']   = isset($filter['offset']) ? $filter['offset'] : 0;
        if (!empty($filter['limit'])) {
            $pageselect->limit($filter['limit'], $filter['offset']);
        }
        
        // for all category listings featured app is listed first    
        if($category) {
			$pageselect->order('is_featured DESC');
        }
        
        return $pageselect;
        
    }

}


