<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_ProductBuild extends Zend_Db_Table_Abstract {

    protected $_name = 'product_builds';
    protected $_id = 'id';
    protected $_dependentTables = array(
      'Model_ProductBuildDevices',
      'Model_ProductBuildFile'
    );

    function __construct() {
        parent::__construct();
    }

    public function save($data) {
        if (null === ($id = $data['id'])) {
            unset($data['id']);
            return $this->insert($data);
        } else {
            $this->update($data, array('id = ?' => $id));
            return false;
        }
    }

    /**
     * 
     * Returns all the platforms for a given product
     * @param int $productId
     */
    public function getPlatformsForProduct($productId) {
        $query      = $this->select(false)->setIntegrityCheck(false)
                        ->from('product_builds', array())
                        ->joinLeft('platforms', 'product_builds.platform_id = platforms.id', array('*'))
                        ->where('product_id = ?', $productId)
                        ->distinct();
        $results    = $this->fetchAll($query);
        if ($results) {
            $platforms  = array();
            foreach ($results as $row) {
                $platforms[]    = (object) $row->toArray();
            }
            return $platforms;
        } 
        return array();
    }
    
    /**
     * Get builds by product id
     * @param <type> $productId
     * @return <type>
     */
    public function getBuildsByProductId($productId) {
        $rows = $this->fetchAll(
                    $this->select()
                    ->setIntegrityCheck(false)
                    ->from('product_builds')
                    ->join('languages', 'languages.id = product_builds.language_id', array('name as language_name'))
                    ->where('product_builds.product_id = ?', $productId)
        );
        if (isset($rows))
            return $rows;
        else
            return false;
    }
    public function getLatastBuildsIDByProductId($productId) {
        $rows = $this->fetchRow(
                    $this->select()
                    ->setIntegrityCheck(false)
                    ->from('product_builds')
                    ->join('languages', 'languages.id = product_builds.language_id', array('name as language_name'))
                    ->where('product_builds.product_id = ?', $productId)
                    ->order('product_builds.id desc')
                    ->limit(1)
        );
        
        if (isset($rows['id']))
            return $rows['id'];
        else
            return false;
    }

    /**
     * Get build name
     * @param <type> $buildId
     * @return <type>
     */
    public function getBuildName($buildId) {
        $row = $this->find($buildId);
        $current = $row->current();
        if (isset($current))
            return $current->name;
        else
            return false;
    }

    /**
     *
     * @param <type> $buildId
     * @return <type>
     */
    public function getBuildPlatform($buildId) {
        $row = $this->find($buildId);
        $current = $row->current();
        if (isset($current))
            return $current->platform_id;
        else
            return false;
    }
    
    /**
     * Returns platform id information for a given set of builds
     * @param $buildIds
     */
    public function getBuildPlatformInfo($buildIds) {
        if (!is_array($buildIds) || empty($buildIds)) {
            return array();
        }
        $select = $this->select(false)->setIntegrityCheck(false);
        $select->from('product_builds', array('id', 'product_id', 'build_type', 'language_id'))
            ->joinLeft('platforms', 'product_builds.platform_id = platforms.id', array('name', 'description', 'icon', 'status'))
            ->where('product_builds.id IN (?)', $buildIds);
        $results    = $this->fetchAll($select);
        return $results;
    } 
    
    /**
     *
     * @param <type> $buildId
     * @return <type>
     */
    public function getBuildLanguage($buildId) {
        $row = $this->find($buildId);
        $current = $row->current();
        if (isset($current))
            return $current->language_id;
        else
            return false;
    }

    /**
     * Get the device selection method
     * @param <type> $buildId
     * @return <type>
     */
    public function getBuildDeviceSelectionMethod($buildId) {
        $row = $this->find($buildId);
        $current = $row->current();
        if (isset($current))
            return $current->device_selection_type;
        else
            return false;
    }

    /**
     * Get build content tyep file/urls
     * @param <type> $buildId
     * @return <type>
     */
    public function getBuildContentType($buildId) {
        $row = $this->find($buildId);
        $current = $row->current();
        if (isset($current))
            return $current->build_type;
        else
            return false;
    }

    /**
     * Get build devices
     * @param <type> $productId
     * @return <type>
     */
    public function getSelectedDevices($buildId) {
        $phones = array();
        $devices = new Model_Device();
//        $productTable = new Model_Product();
        $productRowset = $this->find($buildId);
        $buildIs = $productRowset->current();
        if ($buildIs->device_selection_type == 'BY_ATTRIBUTE') {
            $devicesByBuild = $devices->attribFilterDevices('device_os=iPhone OS');
            foreach ($devicesByBuild as $device) {
            	
            	if(empty($device->device_id))              	
                    continue;
            	else
            		$phones = array_merge($phones, $devices->getDeviceAttributesById($device->device_id));
            }
//            return $devices->attribFilterDevices('device_os=iPhone OS');
        } else {
            $select = $devices->select()
                    ->order('brand ASC')
                    ->order('model ASC');
            $devicesByProduct = $buildIs->findDependentRowset('Model_ProductBuildDevices');
            foreach ($devicesByProduct as $device) {
                $phones = array_merge($phones, $devices->getDeviceAttributesById($device->device_id));
            }
        }
        return $phones;
    }

    /**
     * Get build filetered by device and product
     * @param <type> $productId
     * @return <type>
     */
    public function getBuildByProductAndDevice($productId, $setlanguageId = false ) {
        if(!$setlanguageId) {
            $sessionLanguage = new Zend_Session_Namespace('application');
            $languageId = $sessionLanguage->language_id;
        } else	{
            $languageId = $setlanguageId;
        }
        
        if (!is_numeric($languageId) || $languageId == 0) {
            
            /**
             * Hardcoding to language english in case we don't get a language at this point
             * Basically, some phones were failing on Airtel because the lang wasn't set. 
             * This should fix it, leave it here till the root cause is found - JP 
             * 
             */
            $languageId = 1;
        }
        
        

         
        
        // get devices id 
        $deviceSession = new Zend_Session_Namespace('Device');
        $deviceId = $deviceSession->deviceId;
        if (empty($deviceId) || empty($productId))
            return;
        

            
        $buildDevice = new Model_ProductBuildDevices();
        
        // Search for the build based on $deviceId, $productId, $language->id assigned
        $select = $buildDevice->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->setIntegrityCheck(false);
        if (isset($deviceId))
            $select->where('device_id = ?', $deviceId);
            
        $select->join('product_builds', 'product_builds.id = build_devices.build_id')
               ->where('product_builds.product_id = ?', $productId)
               ->where('product_builds.language_id = ?', $languageId);
        

        

        
        try {
            $custom = $this->fetchRow($select);
        } catch (Exception $ex) {
            $gap    = "\n=========================================\n";
            Zend_Registry::get('logger')->err($gap . $select . $gap);
            throw $ex;
        }
        



        
        
         // if the build is not available for given $language->id then search for the default build (English language)
         if (empty($custom))    {
         	
         	 // Search for the build based on $deviceId, $productId default build (English language)
            $select = $buildDevice->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
            $select->setIntegrityCheck(false);
            if (isset($deviceId))
                $select->where('device_id = ?', $deviceId);
            
            $select->join('product_builds', 'product_builds.id = build_devices.build_id')
                   ->where('product_builds.product_id = ?', $productId)
                   ->where('product_builds.language_id = ?', 1);
        
            $custom = $this->fetchRow($select);
            
         }
        
        if(!empty($custom)) {
            return $custom;
        }
            
        $deviceModel            = new Model_Device();
        $deviceAttributeArray   = $deviceModel->getDeviceAttributes($deviceId);
        $deviceAttrib           = array();
        foreach ($deviceAttributeArray as $deviceAttribute) {
            $deviceAttrib[$deviceAttribute->device_attribute_definition_id] = $deviceAttribute->value;
        }  
        
        // if no custom device found for the product on the database then select by attributes
        // get the device Id and products' attributes check
        $deviceAttributes = new Model_DeviceAttributes();
        
        
        // Search for the build based on $deviceId, $productId $language->id by attributes
        $selectAttributes = $deviceAttributes->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $selectAttributes->setIntegrityCheck(false)
            ->joinInner('product_device_saved_attributes', 'device_attributes.device_attribute_definition_id = product_device_saved_attributes.device_attribute_definition_id AND device_attributes.value = product_device_saved_attributes.value')
            ->joinInner('product_builds', 'product_device_saved_attributes.build_id = product_builds.id')
            ->joinInner('products', 'product_builds.product_id = products.id', 'products.id as product_id')
            ->joinInner('devices', 'devices.id = device_attributes.device_id')
            ->where("device_id IN ($deviceId)")
            ->where("products.id = $productId")
            ->where("product_builds.language_id = $languageId");  
            
        /**
         * This bit of code actually looks as the device OS to match products.
         * The IF clause checks whether the platform is set, if it's not ANY (0), then we do a OS match on it. 
         * If it's 0, we simply 1=1 which evaluates to true
         */
        if (isset($deviceAttrib[1])) {
            $selectAttributes->where('IF(product_builds.platform_id = 0, 1 = 1, product_device_saved_attributes.value = ?)'
                , $deviceAttrib[1]);
        }    
        $rowsAttributes = $deviceAttributes->fetchAll($selectAttributes);
        
        //if the build is not available for the given $language->id check for the default build (English language)
        if(empty($rowsAttributes))  {
        	$selectAttributes = '';        	
        	$selectAttributes = $deviceAttributes->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
            $selectAttributes->setIntegrityCheck(false)
                ->joinInner('product_device_saved_attributes', 'device_attributes.device_attribute_definition_id = product_device_saved_attributes.device_attribute_definition_id AND device_attributes.value = product_device_saved_attributes.value')
                ->joinInner('product_builds', 'product_device_saved_attributes.build_id = product_builds.id')
                ->joinInner('products', 'product_builds.product_id = products.id', 'products.id as product_id')
                ->joinInner('devices', 'devices.id = device_attributes.device_id')
                ->where("device_id IN ($deviceId)")
                ->where("products.id = $productId")
                ->where("product_builds.language_id = 1");
                
            /**
             * This bit of code actually looks as the device OS to match products.
             * The IF clause checks whether the platform is set, if it's not ANY (0), then we do a OS match on it. 
             * If it's 0, we simply 1=1 which evaluates to true
             */
            if (isset($deviceAttrib[1])) {
                $selectAttributes->where('IF(product_builds.platform_id = 0, 1 = 1, product_device_saved_attributes.value = ?)'
                    , $deviceAttrib[1]);
            }    
            $rowsAttributes = $deviceAttributes->fetchAll($selectAttributes); 	
        }
        
        $newClass = new stdClass();
        $i = 0; 
   		
        foreach($rowsAttributes as $key => $val)	{
       		$buildSupportedVersionModel = new  Admin_Model_BuildSupportedVersions();   
      		$deviceOsVersion =  $buildSupportedVersionModel->getBuildSupportedVersion($val->build_id);
			if($deviceOsVersion) {
      		 	if ($deviceSession->osVersion) {
    				if ($deviceOsVersion->min_version) {
						$i++;
    					if ($deviceOsVersion->or_better) {
    						if (version_compare($deviceSession->osVersion, $deviceOsVersion->min_version, '>='))	{
    							$newClass->id = $val->build_id;
    							$newClass->build_id       = $val->build_id;
    							$newClass->device_selection_type = 'BY_ATTRIBUTE';
    							return $newClass;
    						}
    					} else {
    						if (version_compare($deviceSession->osVersion, $deviceOsVersion->min_version, '='))	{
        						$newClass->id             = $val->build_id;
        						$newClass->build_id       = $val->build_id;
        						$newClass->device_selection_type = 'BY_ATTRIBUTE';
        						return $newClass;
    						}
    					}
    				}
				}
			}
        }

		if($i >= 1 ) {	
			return false;
		}


        if (!empty($rowsAttributes[0])) {
            $attributes = (object)  $rowsAttributes[0]->toArray();
            $attributes->id = $attributes->build_id;
            return $attributes;
        }
        else
            return false;
    }
    
    
    /**
     * Get builds in other languages available than the defualt language  filtered by device, product        
     * @param <type> $productId
     * @return <type>
     */    
    
    
	public function getBuildByProductDeviceAndLanguage($productId) {

        $sessionLanguage = new Zend_Session_Namespace('application');
        $languageId = $sessionLanguage->language_id;
        
        // get devices id 
        $deviceSession = new Zend_Session_Namespace('Device');
        $deviceId = $deviceSession->deviceId;
        if (empty($deviceId) || empty($productId))
            return;
            
        $buildDevice = new Model_ProductBuildDevices();
        
        // check for the custom device builds
        // Search for the build based on $deviceId, $productId, $language->id assigned
        $select = $buildDevice->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->setIntegrityCheck(false);
        if (isset($deviceId))
            $select->where('device_id = ?', $deviceId);
            
        $select->join('product_builds', 'product_builds.id = build_devices.build_id')
        	   ->joinInner('languages', 'languages.id = product_builds.language_id', array('languages.name as language_name', 'languages.common_name as language_common_name'))
               ->where('product_builds.product_id = ?', $productId)
               ->where('product_builds.language_id <> ?', $languageId);
                   
        $rowsAttributes = $this->fetchAll($select);
    
               
         if(count($rowsAttributes) <= 0)	{
         	
         	 $deviceAttributes = new Model_DeviceAttributes();
        
         	// if no custom device found for the product on the database then select by attributes
        	// get the device Id and products' attributes check
        	// Search for the build based on $deviceId, $productId $language->id by attributes
        	$selectAttributes = $deviceAttributes->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        	$selectAttributes->setIntegrityCheck(false)
            	->joinInner('product_device_saved_attributes', 'device_attributes.device_attribute_definition_id = product_device_saved_attributes.device_attribute_definition_id AND device_attributes.value = product_device_saved_attributes.value')
            	->joinInner('product_builds', 'product_device_saved_attributes.build_id = product_builds.id')
            	->joinInner('products', 'product_builds.product_id = products.id', 'products.id as product_id')
            	->joinInner('devices', 'devices.id = device_attributes.device_id')
           		->joinInner('languages', 'languages.id = product_builds.language_id', array('languages.name as language_name', 'languages.common_name as language_common_name'))
            	->where("device_id IN ($deviceId)")
            	->where("products.id = $productId")
            	->where("product_builds.language_id <> $languageId");

       		$rowsAttributes = $deviceAttributes->fetchAll($selectAttributes);
         }
        
  		$result = array();
  	

  		// check for the file exists in the S3 before displaying other language builds  
        foreach ($rowsAttributes as $attribute) {
        	
        	if ($attribute->build_type != 'urls') {
                $files = $this->getFiles($attribute->build_id);

                $downloadablefileTypes = array('jad', 'cod', 'apk', 'prc', 'sis', 'sisx', 'cab', 'mp3', 'alx', 'ipk', 'wgz', 'jpg', 'jpeg', 'png', 'gif', 'pdf');
                $downloadFile = '';
                foreach ($files as $file) {
                    $filename = $file->filename;
                    if (in_array(end(explode(".", $filename)), $downloadablefileTypes))		{
    
                    	$s3FileCheck = new Model_Product();
                    	$objectTmp = $attribute->product_id."/$filename"; 	

        				$s3FileExists = $s3FileCheck->s3FileExist($objectTmp);   

        				if($s3FileExists)	{
        					    	  $result[] = array(
				            	  			'build_id' => $attribute->build_id, 
				            	  			'language_id' => $attribute->language_id, 
				            	  			'language_name' => $attribute->language_name,
				            	  			'language_common_name' => $attribute->language_common_name
				            	  			);
        				}
                    }      
                        
                }
                
            }	else {
            	  $result[] = array(
            	  			'build_id' => $attribute->build_id, 
            	  			'language_id' => $attribute->language_id, 
            	  			'language_name' => $attribute->language_name,
            	  			'language_common_name' => $attribute->language_common_name
            	  			);
            }
        	

        }

        
        
        if (count($result) > 0) {
            return $result;
        }

        
        
    }
    
    
  /**
     * Get builds in other versions available than the set device version filtered by device, product, language        
     * @param <type> $productId
     * @return <type> array $buildVersions containig build Versions 
     */    
    
    
    
    public function getBuildByProductDeviceLanguageAndVersion($productId) {

        $sessionLanguage = new Zend_Session_Namespace('application');
        $languageId = $sessionLanguage->language_id;
        
        // get devices id 
        $deviceSession = new Zend_Session_Namespace('Device');
        $deviceId = $deviceSession->deviceId;
        if (empty($deviceId) || empty($productId))
            return;
            
        $buildDevice = new Model_ProductBuildDevices();
        $deviceAttributes = new Model_DeviceAttributes();

        	$selectAttributes = '';        	
        	$selectAttributes = $deviceAttributes->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
            $selectAttributes->setIntegrityCheck(false)
                ->joinInner('product_device_saved_attributes', 'device_attributes.device_attribute_definition_id = product_device_saved_attributes.device_attribute_definition_id AND device_attributes.value = product_device_saved_attributes.value', array())
                ->joinInner('product_builds', 'product_device_saved_attributes.build_id = product_builds.id')
                ->joinInner('products', 'product_builds.product_id = products.id', 'products.id as product_id')
                ->joinInner('devices', 'devices.id = device_attributes.device_id', array() )
                ->where("device_id IN ($deviceId)")
                ->where("products.id = $productId")
                ->where('product_builds.language_id = ?', $languageId);
	
        $rowsAttributes = $deviceAttributes->fetchAll($selectAttributes);	

		$buildSupportedVersionModel = new  Admin_Model_BuildSupportedVersions();   
      	$buildsVersions = array();  
      	$s3FileCheck = new Model_Product();
        
      if (count($rowsAttributes) >= 1)	{      
      	       
			foreach($rowsAttributes as $key => $val)	{

			    if ($val->build_type != 'urls') {
                	$files = $this->getFiles($val->id);

                	$downloadablefileTypes = array('jad', 'cod', 'apk', 'prc', 'sis', 'sisx', 'cab', 'mp3', 'alx', 'ipk', 'wgz', 'jpg', 'jpeg', 'png', 'gif', 'pdf');
                	$downloadFile = '';
                	foreach ($files as $file) {
                    	$filename = $file->filename;
  
                    	
                    	if (in_array(end(explode(".", $filename)), $downloadablefileTypes))		{
    
                          	$objectTmp = $val->product_id."/$filename"; 	

        					$s3FileExists = $s3FileCheck->s3FileExist($objectTmp);



        					if($s3FileExists)	{
        					    	 
        					
        				 	$deviceOsVersion =  $buildSupportedVersionModel->getBuildSupportedVersion($val->id);
				 		 	if($deviceOsVersion)	{
				 	
				 				$buildsVersions[] = array('minOsVersion' => $deviceOsVersion->min_version, 'or_better' => $deviceOsVersion->or_better);
				 
				 			}
        					
        					
        				}
                    }      
                        
                }
                
            }	else	{
				 

				 $deviceOsVersion =  $buildSupportedVersionModel->getBuildSupportedVersion($val->id);
				 if($deviceOsVersion)	{
				 	
				 $buildsVersions[] = array('minOsVersion' => $deviceOsVersion->min_version, 'or_better' => $deviceOsVersion->or_better);
				 
				 }
				      		  
			}
			
			}
		
			
		   	return $buildsVersions;
      	}	else 	
    		return false;

    
    
	}
    
    

    /**
     *
     * @param <type> $productId
     * @return <type>
     */
    public function getFiles($buildId) {
            
          $phones = array();
          $productRowset = $this->find($buildId);
          $current = $productRowset->current();
          return $current->findDependentRowset('Model_ProductBuildFile');

    }
    
    /**
     * Get build file names with build details 
     * @param <type> $buildId
     * @return <type> obj $fileInfo
     */
    
    public function getFilesFullDetails($buildId) {
        
         $select = $this->select()
                        ->setIntegrityCheck( false )
                        ->from( 'product_builds', array ('product_builds.build_type'))
                        ->joinInner ( 'build_files', 'product_builds.id = build_files.build_id', array (
                        																		'build_files.id', 'build_files.filename', 
                           																		'build_files.id as build_id', 
                           																		'build_files.filesize') 
                           																		)
                        ->where('product_builds.id = '. $buildId);
                                
        $fileInfo = $this->fetchall ( $select );
        
        return $fileInfo;
        
    }

    /**
     * Get build details
     * @param <type> $buildId
     * @return <type>
     */
    public function getBuildDetails($buildId) {
        $row = $this->find($buildId);
        $current = $row->current();
        if (isset($current))
            return $current;
        else
            return false;
    }

    public function getBuildsByAttributes($deviceId) {
        $deviceModel = new Model_Device();
        $deviceModel->getDeviceAttributes($deviceId);
    }


    public function getDeviceAttributes($devices) {
        $db = Zend_Registry::get('db');
        $sql = "SELECT
            DISTINCT(device_attributes.value),
            device_attributes.device_attribute_definition_id
            FROM
            devices
            Inner Join device_attributes ON devices.id = device_attributes.device_id
            WHERE
            devices.id IN ($devices)";
        $stmt = $db->query($sql); //probably not a good idea this - but I could not fig out how to use GROUP_CONCAT in a select() object
        $resultRows = $stmt->fetchAll();
        return $resultRows;
    }
    
    
    public function getProductBuildsWithZeroFileSize() {
        $db = Zend_Registry::get('db');
        $sql = "SELECT 
        		products.`name`, 
        		products.id AS Pro_id, 
        		product_builds.id AS Build_Id, 
        		products.user_id, 
        		users.username, 
        		users.email, 
        		build_files.filename, 
        		build_files.filesize, 
        		products.deleted, products.`status` 
        		FROM build_files 
        		INNER JOIN product_builds ON build_files.build_id = product_builds.id 
        		INNER JOIN products ON product_builds.product_id = products.id 
        		INNER JOIN users ON products.user_id = users.id 
        		WHERE (build_files.filename LIKE '%.apk' OR build_files.filename LIKE '%.ja%') AND 
        		product_builds.build_type = 'files' AND
        		(build_files.filesize = 0 OR build_files.filesize IS NULL) AND 
        		products.deleted = 0 and products.`status` = 'APPROVED' ORDER BY 1";
        $stmt = $db->query($sql); //probably not a good idea this - but I could not fig out how to use GROUP_CONCAT in a select() object
        $resultRows = $stmt->fetchAll();
        return $resultRows;
    }
    
    
    
    /** 
     * get products build files info 
     * @param <type> none
     * @return Obj buildfiles info
     * Chathura
     */
    
    public function getBuildsFiles() {
        $select = $this->select()
                           ->setIntegrityCheck( false )
                           ->from( 'product_builds', array ('product_builds.product_id') )
                           ->joinInner ( 'build_files', 'product_builds.id = build_files.build_id', array ('build_files.filename', 'build_files.id as build_id') )
                           ->where('build_files.filesize IS NULL');
                                
        $fileInfo = $this->fetchall ( $select );
        
        return $fileInfo;

    }
    
    
    /** 
     * check if it is url or file product
     * @param <type> none
     * @return Obj buildfiles info
     * Chathura
     */
   
    public function isUrlProduct($id) {
    	
        $cache          = Zend_Registry::get('cache');
        $cacheKey       = 'PRODUCT_IS_URL' . trim($id);
        $cacheKey       = str_ireplace('-', '_', $cacheKey);
		$isUrlProduct = false;
		
        if (($isUrlProduct = $cache->get($cacheKey)) === false) {
        	$buildsSet = $this->fetchAll('product_id = ' . $id);
        	
    		foreach ($buildsSet as $build )	{
    			
    			if($build->build_type == 'urls')
    				$isUrlProduct = true;
    			
    		}
          $cache->set($isUrlProduct, $cacheKey); //cachin

    	}
    	return $isUrlProduct;
    	
    }
    
 	public function checkProductBuildUpdated($productId, $platformId, $languageId, $downlaodedDate) {
        
         $select = $this->select()
                        ->from('product_builds')
                        ->where('product_builds.product_id  ='. $productId)
                        ->where('product_builds.platform_id ='. $platformId)
                        ->where('product_builds.language_id ='. $languageId)
                        ->where("product_builds.created_date > '$downlaodedDate'");
                        
                                
        $buildInfo = $this->fetchall($select);
        
		/*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
			 echo $select->assemble().'<br/>';
		}*/
				
        $rowCount = count($buildInfo);
        
        if($rowCount > 0)
            return true;
        else 
            return false;
    }

    /**
     * @param $productId
     * checks a product have a avg approved builds
     * @return true/false
     */
    function getAvgApproved($productId)
    {
        $select =   $this->select()
                    ->from('product_builds')
                    ->where('product_builds.product_id = ?',$productId)
                    ->where('product_builds.avg_approved = ?',1)
                    ;
        $result = $this->fetchAll($select);
        $rowCount = count($result);
        if($rowCount > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

}

?>
