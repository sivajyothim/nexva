<?php

/**
 *
 * @copyright   neXva.com
 * @author      Cheran@nexva.com
 * @package     cp
 *
 */
define('PLATFORM_JAVA', 3);
define('PLATFORM_RIM', 6);

class Model_Product extends Zend_Db_Table_Abstract {

    protected $_name = 'products';
    protected $_id = 'id';
    protected $_dependentTables = array(
      'Model_ProductDevices',
      'Model_ProductFiles',
      'Model_ProductImages',
      'Model_ProductCategories',
      'Model_ProductMeta'
    );
    protected $_referenceMap = array(
      'user' => array(
        'columns' => array('user_id'),
        'refTableClass' => 'Model_User',
        'refTableColumns' => array('id')
      )
    );
    protected $appRules = ''; // This is the new and preferred way to handle app filter. Sadly the word filter has already been taken by price filters
    
    public $appFilter   = 'ALL'; //filters apps based on price
    public $defaultLanguageId   = false;
    public $languageId          = false;
    
    
    function __construct() {
        parent::__construct();
    }

    /**
     * --- BE VERY VERY CAREFUL WHEN CHANGING THE STRUCTURE OF THIS ARRAY
     * The structure returned here is coupled to many parts of the system. Yes, it's ugly. 
     * 
     * Get product details by product ID
     * @param int $productId
     * @param bool $lightMode indicates whether the method should return a light product
     * @return array $prod containig product informations
     */

    public function getProductDetailsById($productId, $lightMode = false, $langId = null, $setlanguageId = null, $chapId = null, $utfDecode = null) {

        $prod = array();
        $product        = new Model_Product();
        $productMeta    = new Model_ProductMeta();
		$modelDownloadStats = new Model_StatisticDownload();
        $model_ProductBuild = new Model_ProductBuild();

        //let cache do it's thing
        $cache          = Zend_Registry::get('cache');
        $cacheKey       = 'PRODUCT_BASE_' . trim($productId);
        $cacheKey       = str_ireplace('-', '_', $cacheKey);
        

        if (($prod = $cache->get($cacheKey)) === false) {

            $rowset = $product->find($productId);
            $productRow = $rowset->current();
            if (empty($productRow))
                return null;

   			$loggedInUserId = @Zend_Auth::getInstance()->getIdentity()->id; 
            //Get build updates if available
            $downloadedProduct=array();
            $prod["updates_available"] = 0;
            if($loggedInUserId){
                $downloadedProduct = $modelDownloadStats->getDownloadedBuildsByUserId($loggedInUserId, 'MOBILE', $productId);
            }
            if(count($downloadedProduct)>0){
                $prod["updates_available"] = $model_ProductBuild->checkProductBuildUpdated($downloadedProduct[0]['product_id'], $downloadedProduct[0]['platform_id'], $downloadedProduct[0]['language_id'], $downloadedProduct[0]['date']);
            }
			
			/*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
				echo $loggedInUserId;
			 	print_r($downloadedProduct); echo $prod["updates_available"]; die();
				
			}*/
	
            // config visual path\
            $viaualPath         = Zend_Registry::get('config')->product->visuals->dirpath;
            $prod['thumb']      = $viaualPath . '/' . $productRow->thumbnail;
            $prod['thumb_name'] =  $productRow->thumbnail;
            $prod['cost']       = $productRow->price;
            $prod['name']       = $productRow->name;
            $prod['id']         = $productRow->id;
    
            $prod['uid']        = $productRow->user_id;
            $userMeta           = new Model_UserMeta();
            $userMeta->setEntityId($productRow->user_id);

            //Zend_Debug::dump($productRow->user_id);
            $prod['user_meta']  = $userMeta;
            //test
            $prod['vendor'] = $userMeta->COMPANY_NAME;
            $prod['registration_model'] = $productRow->registration_model;
            $prod['product_type']   = $productRow->product_type;
            $prod['status']         = $productRow->status;

            //set entity Id
            $productMeta->setEntityId($productRow->id);

            $prod['desc'] = stripslashes(nl2br(strip_tags($productMeta->FULL_DESCRIPTION), "<br><a></a>"));
            $prod['desc_brief'] = stripslashes(nl2br(strip_tags($productMeta->getAll()->BRIEF_DESCRIPTION)));
            
 
/*
         if($utfDecode) {
                //htmlentities($test, UTF-8);
                $prod['desc'] = nl2br(strip_tags($productMeta->FULL_DESCRIPTION, "<br><a></a>"));
                $prod['desc_brief'] = strip_tags($productMeta->BRIEF_DESCRIPTION,'<a></a>');
            } else {
                $prod['desc'] = stripslashes(nl2br(strip_tags($productMeta->FULL_DESCRIPTION, "<br><a></a>")));
                $prod['desc_brief'] = stripslashes(strip_tags($productMeta->BRIEF_DESCRIPTION,'<a></a>'));
            }

*/
            $prod['google_id'] = $productRow->google_id;
            $prod['apple_id'] = $productRow->apple_id;

            $platformModel = new Model_Platform();
            /**
             * @deprecated With multiple builds per product feature, $prod['platform_id'] and $prod['platform_name'] are now deprecated. Please delete once all references are removed
             */
            $prod['platform_id']    = $productRow->platform_id;
            $prod['platform_name']  = $platformModel->getPlatformName($productRow->platform_id);

            $prod['keywords'] = $productRow->keywords;
            $prod['featured'] = $productRow->is_featured;
            
            $prod['device_selection_type'] = $productRow->device_selection_type;
            
            //get scrennshots
            $screenshots            = $productRow->findDependentRowset('Model_ProductImages');
            $prod['screenshots']    = array();
            foreach ($screenshots as $img) {
                $prod['screenshots'][] = $viaualPath . '/' . $img->filename;
            }
    
            $prod['created'] = $productMeta->CREATED_DATE;
            $prod['changed'] = $productMeta->PRODUCT_CHANGED;
            $prod['version'] = ($productMeta->PRODUCT_VERSION) ? $productMeta->PRODUCT_VERSION : '1.0';
            
            //get categories
            $categoryIds= $this->getCategoryById($productRow);

            $categoryModel = new Model_Category();
            foreach($categoryIds as $category){
                $categoryName = $categoryModel->getParentCatgoryNameById($category);
                $prod['categories'] = $categoryName->name;
            }
            
            $categoryModel = new Model_Category();
            foreach($categoryIds as $category){
                $categoryName = $categoryModel->getParentCatgoryNameById($category);
                $prod['categories'] = $categoryName->name;
            }
            
            
             $categoryModelProduct = new Model_ProductCategories();
            $categoryModelProductInfo =  $categoryModelProduct->getCategotyByProductId($productId);
             
             $prod['categories_sub'] = $categoryModelProductInfo->name;

            //die();
            $prod['deleted']    = $productRow->deleted;
         
            
            $cache->set($prod, $cacheKey); //caching the as much as we can
        } else {
            //making a fake prodcut object for the for the rest of the method
            $productRow = new stdClass();
            $productRow->id = $prod['id'];
        }

        //this is temporary for MTN Iran cell
        /* @todo: needs to be removed once we bags all translations
         */
        /*if(23045 != $chapId){
            if ($langId) {
                //translate the product
                $proLang        = new Model_ProductLanguageMeta();
                $prod           = $proLang->translateProduct($prod, $langId);
            }
        }*/

        if($_SERVER['REMOTE_ADDR'] == '61.245.173.84'){
            //echo $langId.'##'.$chapId; die();
        }

        /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            echo 'Hi there';
            Zend_Debug::dump($chapId); die();
        }*/

        /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            echo 'Hi there';
            Zend_Debug::dump($chapId); die();
        }*/

        /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            echo 'Before';
            //Zend_Debug::dump($prod); die();
        }*/

        //if(115189 != $chapId){ //ycoins

            /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                echo 'Inside';
                //Zend_Debug::dump($prod); die();
            }*/

            if ($langId) {

                $proLang        = new Model_ProductLanguageMeta();
                $prod           = $proLang->translateProduct($prod, $langId, $chapId);
         
            }
        //}

        /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            echo 'Before';
            Zend_Debug::dump($prod); //die();
        }*/

        if ($lightMode) { //we only cache the full product

            return $prod;
        }
        
/*         if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
        	//echo 'After';
        	Zend_Debug::dump($langId);
        	Zend_Debug::dump($prod,'dd'); die();
        } */

    



        $purchased = false;
        if (Zend_Auth::getInstance()->hasIdentity()) {
            // is purchased product
            $order = new Model_Order();
            if ($order->isPruchsed($productId))
                $purchased = true;
        }
        $prod['is_purchased'] = $purchased;

        // filter out url products and files
        //Setting the default content type to URL. IF the methods return FALSE set it to become a normal app
        $prod['content_type'] = 'URL';
        
        $checkUrlProduct = $this->checkUrlProducts($productRow, $setlanguageId);
        
        if ($checkUrlProduct === false) {
            $prod['content_type'] = '';
        }

        if ($prod['content_type'] == 'URL')	{
        	$prod['file'] = $this->getFileUrls($productRow, $setlanguageId);
        } else {
            $prod['file'] = array();
            $prod['file'][] = 'file_array_kept_to_avoid_any_warnings';
        }

        // append (Lite) to title
        if ($prod['product_type'] == 'DEMO')
            $prod['name'] = $prod['name'] . ' (Lite)';

        // get statics details
        $prod['today_hits'] = 0;
        $prod['hits'] = 0;
        $prod['today_downloads'] = 0;
        $prod['downloads'] = 0;
        
        
        $ratingModel = new Model_Rating();
        //Add Rating
        $prod['avg_rating'] = $ratingModel->getAverageRatingByProduct($productId);
        $prod['total_ratings'] = $ratingModel->getTotalRatingByProduct($productId);
             //check if it's already rated
        $ratingNamespace = new Zend_Session_Namespace('Ratings');
        $productRated = false;
        $userRating = 1;
        if (isset($ratingNamespace->ratedProducts)) {
            $ratedProducts = $ratingNamespace->ratedProducts;
            if (isset($ratedProducts[$productId])) {
                $productRated = true;
                $userRating = $ratedProducts[$productId];
            }
        }
        
        //// add to cache later -- begin ////
        //Get chapter platform if the chapter id is set
        $chapterPlatformType = NULL;
        if($chapId):
            $themeMetaModel = new Model_ThemeMeta();
            $themeMetaModel->setEntityId($chapId);
            $chapterPlatformType = $themeMetaModel->WHITELABLE_PLATEFORM;
        endif;
        
        $prod['supported_platforms'] = $this->getSupportedPlatforms($productId, $chapterPlatformType);
            
        //check and add if the app is contain multiple platforms or only android
        $prod['app_platform_type'] = $this->checkAppPlatforms($prod['supported_platforms'], $prod['id'], $chapterPlatformType);
        
        //// add to cache later -- end ////
        
       	$prod['product_rated'] = $productRated;

        return $prod;
    }

    /**
     *
     * @param <type> $productId
     * @return <type>
     */
    public function getProductDetailsByIdInapp($productId) {

        $prod = array();

        $product = new Model_Product();
        $productMeta = new Model_ProductMeta();
        $rowset = $product->find($productId);
        $productRow = $rowset->current();
        if (empty($productRow))
            return null;
        $prod['cost'] = $productRow->price;
        $prod['name'] = $productRow->name;
        $prod['id'] = $productRow->id;

        $prod['uid'] = $productRow->user_id;


        return $prod;
    }

    /**
     * Check for URL products, validate the file is an url or not
     * @param <type> $productRow
     * @return <type>
     */
    protected function checkUrlProducts($productRow, $setlanguageId = false) {
        $buildProd = new Model_ProductBuild();
        
        if($setlanguageId)
        	$buildId = $buildProd->getBuildByProductAndDevice($productRow->id, $setlanguageId);
        else 
        	$buildId = $buildProd->getBuildByProductAndDevice($productRow->id);

        if (empty($buildId))
            return false;
        $files = $buildProd->getFiles($buildId->id);
        
        foreach ($files as $file) {
            if (filter_var(trim($file->filename), FILTER_VALIDATE_URL) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * If file type is url then get the files from the build
     * @param <type> $productRow
     * @return <type>
     */
    protected function getFileUrls($productRow,  $setlanguageId = false ) {
        $buildProd = new Model_ProductBuild();
        if($setlanguageId)
        	$buildId = $buildProd->getBuildByProductAndDevice($productRow->id, $setlanguageId);
        else 
        	$buildId = $buildProd->getBuildByProductAndDevice($productRow->id);
        	
        if (empty($buildId))
            return;
        $files = $buildProd->getFiles($buildId->id);
        $urls = array();
        foreach ($files as $file) {
//            $filename = $file->filename;
            $urls[] = $file->filename;
        }
        return $urls;
    }

    /**
     * Get files to download and set them to public access
     * @param <type> $productId
     * @return <type>
     */
    public function getDownloadFile($productId, $setlanguageId = false) {
        $product = new Model_Product();
        $rowset = $product->find($productId);
        $productRow = $rowset->current();
        $buildProd = new Model_ProductBuild();
        
        if($setlanguageId)
        	$buildId = $buildProd->getBuildByProductAndDevice($productRow->id, $setlanguageId);
        else 
        	$buildId = $buildProd->getBuildByProductAndDevice($productRow->id);
            
        if (empty($buildId))
            return 'error_no_build.jad';

        $files = $buildProd->getFiles($buildId->id);

        $downloadablefileTypes = array('jad', 'cod', 'apk', 'prc', 'sis', 'sisx', 'cab', 'mp3', 'alx', 'ipk', 'wgz', 'jpg', 'jpeg', 'png', 'gif', 'pdf');

        $downloadFile = '';
        foreach ($files as $file) {
            $filename = $file->filename;
            //set S3 headers to public and make correct Meta Type
            $this->setS3FileHeaders($productRow->id . '/' . $filename);
            if (in_array(end(explode(".", $filename)), $downloadablefileTypes))
                $downloadFile = $filename;
        }
        
        
        return $downloadFile;
    }
    
    /**
     * Get file type
     * @param <type> $productId
     * @return <type>
     */
    public function getDownloadFileType($productId, $setlanguageId = false) {
        $product = new Model_Product();
        $rowset = $product->find($productId);
        $productRow = $rowset->current();
        $buildProd = new Model_ProductBuild();
        
        if($setlanguageId)	{
        	$buildId = $buildProd->getBuildByProductAndDevice($productRow->id, $setlanguageId );
        } else {
        	$buildId = $buildProd->getBuildByProductAndDevice($productRow->id);
        }
        
        
        
        if (empty($buildId))
            return 'error_no_build.jad';

        $files = $buildProd->getFiles($buildId->id);

        $downloadablefileTypes = array('jad', 'cod', 'apk', 'prc', 'sis', 'sisx', 'cab', 'mp3', 'alx', 'ipk', 'wgz', 'jpg', 'jpeg', 'png', 'PNG', 'gif', 'pdf');

        $downloadFile = '';
        foreach ($files as $file) {
            $filename = $file->filename;
            //set S3 headers to public and make correct Meta Type
            $this->setS3FileHeaders($productRow->id . '/' . $filename);
            if (in_array(end(explode(".", $filename)), $downloadablefileTypes))
                $downloadFileType = end(explode(".", $filename));
        }
//    exit;
        return $downloadFileType;
    }
    
    /**
     * Get files to download and set them to public access
     * @param <type> $productId
     * @return <type>
     */
    protected function getDownloadFileFileCheck($productId, $setlanguageId = false) {
        $product = new Model_Product();
        $rowset = $product->find($productId);
        $productRow = $rowset->current();
        $buildProd = new Model_ProductBuild();
        if($setlanguageId)	
        	$buildId = $buildProd->getBuildByProductAndDevice($productRow->id, $setlanguageId);
        else 
        	$buildId = $buildProd->getBuildByProductAndDevice($productRow->id);
        
       
  

        if (empty($buildId))
        	return 'error_no_build';

        $files = $buildProd->getFiles($buildId->id);
        //Zend_Debug::dump($files);
        //die();
        $downloadablefileTypes = array('jad', 'apk', 'cod', 'prc', 'sis', 'sisx', 'cab', 'mp3', 'alx', 'ipk', 'wgz', 'jpg', 'jpeg', 'png', 'PNG', 'gif', 'pdf');

        $downloadFile = '';
        
        foreach ($files as $file) {
            $filename = $file->filename;
            if (in_array(end(explode(".", $filename)), $downloadablefileTypes))
                $downloadFile = $filename;
        }
        return $downloadFile;
    }
    
    

    /**
     * Set HTTP headers of the files at the S3
     * @param <type> $file
     */
    protected function setS3FileHeaders($file) {
        // @TODO : remove hardcode values
        // hardocde productfile
        $file = 'productfile/' . $file;
        
        // get the mime type for android
        $fileMimeData = new Model_ProductFileTypes();
        $fileMime = $fileMimeData->getMimeByFile($file);
        // add the the cron job queue
        // @TODO : add validations
        // @TODO : update the field id it is there
        $s3PublicFiles = new Model_S3PublicFile();
        $id = $s3PublicFiles->getIdByFilename($file);
        $data = array('id' => $id, 'filename' => $file, 'time' => new Zend_Db_Expr('NOW()'));
        $s3PublicFiles->save($data);

        // load S3 configs
        $config = Zend_Registry::get('config');
        $awsKey = $config->aws->s3->publickey;
        $awsSecretKey = $config->aws->s3->secretkey;
        $bucketName = $config->aws->s3->bucketname;
        try {
            $s3 = new Nexva_Util_S3_S3($awsKey, $awsSecretKey);
            $meta = array(
              Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
              Zend_Service_Amazon_S3::S3_CONTENT_TYPE_HEADER => $fileMime
            );
            $error = $s3->copyObject($bucketName . '/' . $file, $bucketName . '/' . $file, $meta);
        } catch (Exception $ex) {
//            print_r($ex);
//            exit;
        }
    }

    /**
     * Get produt category from the product row
     * @param <type> $productRow
     * @return <type>
     */
    protected function getCategoryById($productRow) {
        //get categories
        $catData = array();
        $categories = $productRow->findDependentRowset('Model_ProductCategories');
        foreach ($categories as $cat) {
            $catData[] = $cat->category_id;
        }
        return $catData;
    }
    
    /**
     * This is a short circuit method. The call to  getCompatibleAppsByDeviceId() 
     * is too exensive when no dvice is present so we short circuit it.
     */
    function getAllProducts($filter, $limit = null, $page = null, $getFromCache = true) {
         if ($limit !== null) {
            $start  = $limit * ($page - 1);
            $filter['limit']    = $limit; 
            $filter['offset']   = $start; 
        }

        
        $selectCustom   = $this->getAllProductsSelect($filter);
        
        $key    = 'DEVICEPRODUCT_KEY_' . md5((string) $selectCustom);
        $cache  = Zend_Registry::get('cache');
        if ((($rows = $cache->get($key)) === false)  || !$getFromCache) {
            $db     = Zend_Registry::get('db');
            $rows   = $db->fetchAll($selectCustom);
            $cache->set($rows, $key);    
        } 
        $this->_productCount = count($rows);

        $productIds = new stdClass();
        foreach ($rows as $row) {
            $productIds->{$row->product_id} = new stdClass();
            $productIds->{$row->product_id}->product_id = $row->product_id;
        }
        return $productIds;
    }
    
    
    /**
     * Returns the select object for getting products when devices are not set
     */
    function getAllProductsSelect($filter = array()) {
        $selectCustom = $this->select(false);
        $selectCustom->setIntegrityCheck(false);
        
        $selectCustom
            ->distinct()
            ->from('products', 'products.id AS product_id')
            ->where('products.status = ?', 'APPROVED')            
            ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
            ->where("products.deleted <> '1'");

        // if there are any other join to use for further filtering
        if (!empty($filter['join'])) {
            foreach ($filter['join'] as $key => $join) {
                $selectCustom->join($key, $join, array());
            }
        }
        //print_r($selectCustom->join());die();
        // if there are any othere where to use for further filtering
        //Zend_Debug::dump($filter['where']);die();
        if (!empty($filter['where'])) {
            foreach ($filter['where'] as $where) {
                $selectCustom->where($where);
            }
        }

        // if there are any sorting field in the filter variable
        if (!empty($filter['order'])) {
            foreach ($filter['order'] as $order) {
                $selectCustom->order($order);
            }
        }
        // if there are any limit in the filter variable
        $filter['offset']   = isset($filter['offset']) ? $filter['offset'] : 0;
        if (!empty($filter['limit'])) {
            $selectCustom->limit($filter['limit'], $filter['offset']);
        }

        //Zend_Debug::dump($filter);die();
        //Zend_Debug::dump($selectCustom->__toString());die();
        //Zend_Debug::dump($selectCustom->assemble());die();
        return $selectCustom;

        
    }

    /**
     * Get compatible App for the device
     * @param <type> $deviceId
     * @param <type> $filter
     * @return <type> 
     */
    protected function getCompatibleAppsByDeviceId($deviceId, $filter = null, $limit = null, $page = null, $getFromCache = true) {

        $productDevices = new Model_ProductDevices();
        $productDevices->languageId         = $this->languageId;  
        $productDevices->defaultLanguageId  = $this->defaultLanguageId;
        $productDevices->appFilters         = $this->appRules;
        
        $sqlFrag        = $this->getPriceSqlFragment(true); 

        if (!empty($sqlFrag)) {
            if (isset($filter['where'])) {
                if (is_array($filter['where'])) {
                    $filter['where'][]  = $sqlFrag;
                } else {
                    $filter['where'] = array($filter['where'], $sqlFrag);    
                }
            } else {
                $filter['where']  = array($sqlFrag);
            }
        }
        //Zend_Debug::dump($filter);die();
        //check if device has been used,
        if (empty($deviceId )) {
            unset($filter['union']); //this method doesn't use a union 
            return $this->getAllProducts($filter, $limit, $page, $getFromCache);
        } else {
            unset($filter['order']); //ordering needs to be done on the union
            return $productDevices->getAllProductsByDevId($deviceId, $filter, $limit, $page, $getFromCache);    
        }
    }

    /**
     * Get product to display in first page
     * @param <type> $deviceId
     * @return <type> 
     */
    
    public function getFrontPageProducts($deviceId, $limit = 5, $cpId='', $nexpager = false, $langId = false) {
        $prodID = Zend_Registry::get("config")->nexva->application->widget->id;
        $extraConds       = $this->getPriceSqlFragment();
        $filterNexpager = ' ';
        //$extraConds       = empty($extraConds) ? '' : $extraConds; 
        
        if($nexpager == true)
          $filterNexpager = ' AND products.show_in_nexpager = 1 ' ;
       
        if ($cpId) {
            $where = array (
            'where' => array ('products.user_id = '.$cpId . ' '. $filterNexpager . $extraConds), 
            'limit' => isset ( $limit ) ? $limit : 5 );
        } else {
			$where = array (
			'where' => array ('products.id <> ' . $prodID . ' AND products.user_id <> 2228 AND products.is_featured = 0' . ' ' . $extraConds),
			'limit' => isset ( $limit ) ? $limit : 5 
			);
		}

        $productIds = $this->getCompatibleAppsByDeviceId($deviceId, $where);
        $productsInfo = array();
        foreach ($productIds as $key => $value) {
            $productsInfo[] = $this->getProductDetailsById($value->product_id, false, $langId);
        }
        shuffle($productsInfo);
        return $productsInfo;
    }


    //
    function featuredApps($deviceID,$limit,$langId = false)
    {
        $prodID = Zend_Registry::get("config")->nexva->application->widget->id;
        $extraConds       = $this->getPriceSqlFragment();
        $filterNexpager = ' ';
        //$extraConds       = empty($extraConds) ? '' : $extraConds;

        $filter = array (
            'join' =>array('product_chap_featured'=>'products.id = product_chap_featured.product_id'),
            'where' => array ('product_chap_featured.chap_id = "5945"'),
            //'where' => array ('products.user_id = "5945"'),
            'limit' =>  5 );

        $productIds = $this->getCompatibleAppsByDeviceId($deviceID,$filter);
        $productsInfo = array();
        foreach ($productIds as $key => $value) {
            $productsInfo[] = $this->getProductDetailsById($value->product_id, false, $langId);
        }
        shuffle($productsInfo);
        return $productsInfo;
    }


    public function countProductsByCpIdAndDeviceId($deviceId,  $cpId, $nexpager = false) {
        
        $filterNexpager = '';
        
        if($nexpager == true)
          $filterNexpager = ' AND products.show_in_nexpager = 1 ' ;
        
        if ($cpId) {
            $where = array (
            'where' => array ('products.user_id = '.$cpId .$filterNexpager ));
        } 
        
        $productIds = $this->getCompatibleAppsByDeviceId($deviceId, $where);
        $productsInfo = array();
        foreach ($productIds as $key => $value) {
            $productsInfo[] = $this->getProductDetailsById($value->product_id);
        }
        return count($productsInfo);
    }

    public function getAppsByCp($deviceId, $cpid, $limit = 5, $exempt = null) {

        $extraConds       = $this->getPriceSqlFragment();
        $where = array(
          'where' => array('products.user_id = ' . $cpid . $extraConds),
          'limit' => isset($limit) ? $limit : 5,
          'union' => array(
              'order' => array('product_id DESC')
          )
        );
        
        $productIds = $this->getCompatibleAppsByDeviceId($deviceId, $where);
        $productsInfo = array();
        foreach ($productIds as $key => $value) {
            if (isset($exempt) && $exempt == $value->product_id)
                continue;
            $productsInfo[] = $this->getProductDetailsById($value->product_id);
        }
        shuffle($productsInfo);
        return $productsInfo;
    }

    /**
     * Get app count by device Id
     * @param <type> $deviceId
     * @return <type>
     */
    public function getAppCountByDeviceId($deviceId) {
        $productDevices = new Model_ProductDevices();
        return $productDevices->getAppCountByDevice($deviceId);
    }

    /**
     * Get only free apps
     * @param <type> $deviceId
     * @return <type>
     */ 
    public function getFreeAppsByDeviceId($deviceId, $limit = null, $page = null) { 
        $where = array('where' => array('products.price = 0')); 
        $productIds = $this->getCompatibleAppsByDeviceId($deviceId, $where, $limit, $page);
        $productsInfo = array();
        foreach ($productIds as $key => $value) {
            $productsInfo[] = $this->getProductDetailsById($value->product_id);
        }
        return $productsInfo;
    }

    /**
     * Get premium apps
     * @param <type> $deviceId
     * @return <type>
     */
    public function getPremiumAppsByDeviceId($deviceId, $limit = null, $page = null) {
        $where = array('where' => array('products.price > 0'));
        $productIds = $this->getCompatibleAppsByDeviceId($deviceId, $where, $limit, $page);
        $productsInfo = array();
        foreach ($productIds as $key => $value) {
            $productsInfo[] = $this->getProductDetailsById($value->product_id);
        }
        return $productsInfo;
    }

    /**
     * Get all products
     * @param <type> $deviceId
     * @return <type>
     */
    public function getAllAppsByDeviceId($deviceId, $limit = null, $page = null) {
        $productIds = $this->getCompatibleAppsByDeviceId($deviceId, null, $limit, $page);
        $productsInfo = array();
        foreach ($productIds as $key => $value) {
            $productsInfo[] = $this->getProductDetailsById($value->product_id);
        }
        return $productsInfo;
    }

    /**
     * Get recent products
     * @param <type> $deviceId
     * @return <type>
     */
    public function getRecentAppsByDeviceId($deviceId) {
        $filter = array();
        $filter = array(
//          'where' => array('categories.id =  ' . $categoryId . " OR categories.parent_id = " . $categoryId),
            'limit' => 20,
            'union' => array(
                'order' => array('product_id DESC')
            )
        );
        $productIds = $this->getCompatibleAppsByDeviceId($deviceId, $filter);
        $productsInfo = array();
        foreach ($productIds as $key => $value) {
            $productsInfo[] = $this->getProductDetailsById($value->product_id);
        }
        return $productsInfo;
    }

    /**
     * get products by category id and device Id
     * @param <type> $deviceId
     * @param <type> $catId
     * @return <type>
     */
    public function getCategoryProducts($deviceId, $categoryId, $limit = null, $page = null) {
        $db = Zend_Registry::get('db');
        $insert = $this->getProductsForCategoryProductList($deviceId, $categoryId, $limit, $page);
        try {
            $productIds = $db->fetchAll($insert);
        } catch (Exception $ex) {
            $gap    = "\n=========================================\n";
            Zend_Registry::get('logger')->err($gap . $insert . $gap);
            throw $ex;
        }
        $productsInfo = array();
        foreach ($productIds as $key => $value) {
            $productsInfo[] = $this->getProductDetailsById($value->product_id);
        }
        return $productsInfo;
    }

    /**
     * Returns a list of products by a category.
     *
     *
     * @param array $deviceId Products will be filtered by these device IDs
     * @param int $categoryId The category id
     *
     * @return obj for pagination
     * Chathura Jayasekara
     *
     */
    public function getProductsForCategoryProductList($deviceId, $categoryId, $limit = null, $page = null) {
        
		$priceConds = $this->getPriceSqlFragment();
        $filter = array();
        $filter = array(
          'join' => array(
            'product_categories' => 'product_categories.product_id = products.id',
            'categories' => 'categories.id = product_categories.category_id'), 
          'where' => array('(categories.id =  ' . $categoryId . " OR categories.parent_id = " . $categoryId . ') ' . $priceConds),
          //'order' => array(array('products.is_featured desc'))
          'order' => array(
              'products.id desc',
              'products.is_featured desc'
          )
        );
        
        if ($limit !== null) {
            $start  = $limit * ($page - 1);
            $filter['limit']    = $limit;
            $filter['offset']   = $start; 
        }
        
        
        if (empty($deviceId)) {
            $resultRows = $this->getAllProductsSelect($filter);
        } else {
            $productDevices = new Model_ProductDevices();
            $productDevices->appFilters         = $this->appRules;
            $resultRows = $productDevices->getProducts($deviceId, $filter, true);    
        }
        
        return $resultRows;
    }
    
    
    
    public function getProductsForSameUserProductList($deviceId, $cpId, $limit = null, $page = null) {
        

        $filter = array();
     	$filter = array(
          	'where' => array( 'products.user_id ='. $cpId)
    	);
        
        if ($limit !== null) {
            $start  = $limit * ($page - 1);
            $filter['limit']    = $limit;
            $filter['offset']   = $start; 
        }
        
        
        if (empty($deviceId)) {
            $resultRows = $this->getAllProductsSelect($filter);
        } else {
            $productDevices = new Model_ProductDevices();
            $resultRows = $productDevices->getProducts($deviceId, $filter, true);    
        }
        
        return $resultRows;
    }
    

    /**
     * Returns a list of products by a category.
     *
     *
     * @param array $deviceId Products will be filtered by these device IDs
     * @param int $categoryId The category id
     * @param int $limit Limits number of products to be returned.
     * @return array
     *
     * @todo: getCategoryProducts() does the same thing - expect it does it for 1 single device. Consider merging this function with that.
     */
    /*public function getProductsForCategory($deviceId, $categoryId, $limit=null) {
        $filter = array();
        $filter = array(
          'join' => array(
            'product_categories' => 'product_categories.product_id = products.id',
            'categories' => 'categories.id = product_categories.category_id'),
          'where' => array('categories.id =  ' . $categoryId . " OR categories.parent_id = " . $categoryId),
          'limit' => $limit,
          //'order' => array('RAND(NOW())'), //ORDER BY RAND(NOW()) is VERY bad - incurrs a full table scan that kills performance. until a better way is found to select randomly, this is disabled
        );
        $resultRows = $this->getCompatibleAppsByDeviceId($deviceId, $filter);
        $products = array();
        if (!is_null($resultRows)) {
            foreach ($resultRows as $row) {
                $products[] = $this->getProductDetailsById($row->product_id);
            }
        }

        Zend_Debug::dump($products);die();
        return $products;
    }*/

    /*  */
    public function getNewest($deviceId, $categoryId, $limit=null)
    {

        $filter = array();
        $filter = array(
            //'select' => 'products',

            /*'select' => array('user_meta.meta_name AS vendor'),*/
            //'join' => array('user_meta' => 'products.user_id = user_meta.user_id'),
            //'join' =>array('product_chap_featured'=>'products.id = product_chap_featured.product_id'),

            'where' => array('products.status =  "APPROVED" AND products.deleted <> 1 AND products.inapp IS NULL AND products.user_id != 5981'),
            'order' => array('products.id DESC'),
            'limit' => $limit,
            //'order' => array('RAND(NOW())'), //ORDER BY RAND(NOW()) is VERY bad - incurrs a full table scan that kills performance. until a better way is found to select randomly, this is disabled
        );

        $resultRows = $this->getCompatibleAppsByDeviceId($deviceId, $filter);
        $products = array();
        if (!is_null($resultRows)) {
            foreach ($resultRows as $row) {
                $products[] = $this->getProductDetailsById($row->product_id);
            }
        }
        return $products;
    }

    public function getNewestGame($category,$numItems)
    {
        $session = new Zend_Session_Namespace("devices");
        $devieArray = $session->selectedDevices;
        $deviceIds = array();
        if(!empty($devieArray))
        {
            foreach($devieArray as $key => $value)
            {
                $deviceIds[] =  $key;
            }
            $deviceId = join(',',$deviceIds);
        }
        if(empty($deviceId))
        {
            $newestGameSQL = $this->select();
            $newestGameSQL  ->from('products')
                            ->setIntegrityCheck(false)
                            ->columns(array('products.name AS product_name','products.id AS pro_id'))
                            ->join('product_categories', 'product_categories.product_id = products.id')
                            ->join('categories','product_categories.category_id = categories.id')
                            ->where('categories.id = ?',$category)
                            //->where('categories.parent_id = ?',$category)
                            ->where('products.status = ?','APPROVED')
                            ->where('products.deleted != ?','1')
                            ->where('products.user_id != ?',5981)
                            ->order('products.id DESC')
                            ->limit($numItems);
                            //->where('categories.id = 7');
            $products = $this->fetchAll($newestGameSQL)->toArray();
            //Zend_Debug::dump($products);die();
            $i=0;
            foreach($products as $product)
            {
                $products[$i][] = $this->getProductDetailsById($product['pro_id']);
                $i++;
            }

            return $products;
        }
        else
        {
            $newestGameSQL = $this->select();
            $newestGameSQL  ->from('products')
                ->setIntegrityCheck(false)
                ->columns(array('products.name AS product_name','products.id AS pro_id'))
                ->join('product_categories', 'product_categories.product_id = products.id')
                ->join('categories','product_categories.category_id = categories.id')
                ->join('product_builds', 'product_builds.product_id = products.id')
                ->join('product_device_saved_attributes', 'product_device_saved_attributes.build_id = product_builds.id')
                ->join('device_attributes', 'device_attributes.device_attribute_definition_id = product_device_saved_attributes.device_attribute_definition_id AND device_attributes.value = product_device_saved_attributes.value')
                ->join('devices', 'devices.id = device_attributes.device_id')
                ->where('devices.id IN ('.$deviceId.')')
                ->where('categories.id = ?',$category)
                //->where('categories.parent_id = ?',$category)
                ->where('products.status = ?','APPROVED')
                ->where('products.deleted != ?','1')
                ->where('products.user_id != ?',5981)
                ->order('products.id DESC')
                ->limit($numItems);
            $products = $this->fetchAll($newestGameSQL)->toArray();

            $i=0;
            foreach($products as $product)
            {
                $products[$i][] = $this->getProductDetailsById($product['pro_id']);
                $i++;
            }

            return $products;
        }
    }

    public function getNewestGameSQL($category,$numItems)
    {
        $session = new Zend_Session_Namespace("devices");
        $devieArray = $session->selectedDevices;
        $deviceIds = array();
        if(!empty($devieArray))
        {
            foreach($devieArray as $key => $value)
            {
                $deviceIds[] =  $key;
            }
            $deviceId = join(',',$deviceIds);
        }
        if(empty($deviceId))
        {
            $newestGameSQL = $this->select();
            $newestGameSQL  ->from('products')
                ->setIntegrityCheck(false)
                ->columns(array('products.name AS product_name','products.id AS pro_id'))
                ->join('product_categories', 'product_categories.product_id = products.id')
                ->join('categories','product_categories.category_id = categories.id')
                ->where('categories.id = ?',$category)
                ->where('products.status = ?','APPROVED')
                ->where('products.deleted != ?','1')
                ->where('products.user_id != ?',5981)
                ->order('products.id DESC')
                ->limit($numItems);
            //->where('categories.id = 7');
            return $newestGameSQL;
            $products = $this->fetchAll($newestGameSQL)->toArray();
            //Zend_Debug::dump($products);die();
            $i=0;
            foreach($products as $product)
            {
                $products[$i][] = $this->getProductDetailsById($product['pro_id']);
                $i++;
            }

            //return $products;
        }
        else
        {
            $newestGameSQL = $this->select();
            $newestGameSQL  ->from('products')
                ->setIntegrityCheck(false)
                ->columns(array('products.name AS product_name','products.id AS pro_id'))
                ->join('product_categories', 'product_categories.product_id = products.id')
                ->join('categories','product_categories.category_id = categories.id')
                ->join('product_builds', 'product_builds.product_id = products.id')
                ->join('product_device_saved_attributes', 'product_device_saved_attributes.build_id = product_builds.id')
                ->join('device_attributes', 'device_attributes.device_attribute_definition_id = product_device_saved_attributes.device_attribute_definition_id AND device_attributes.value = product_device_saved_attributes.value')
                ->join('devices', 'devices.id = device_attributes.device_id')
                ->where('devices.id IN ('.$deviceId.')')
                ->where('categories.id = ?',$category)
                ->where('products.status = ?','APPROVED')
                ->where('products.deleted != ?','1')
                ->where('products.user_id != ?',5981)
                ->order('products.id DESC')
                ->limit($numItems);
            return $newestGameSQL;
            $products = $this->fetchAll($newestGameSQL)->toArray();

            $i=0;
            foreach($products as $product)
            {
                $products[$i][] = $this->getProductDetailsById($product['pro_id']);
                $i++;
            }

            //return $products;
        }
    }

    public function getMostViewed($limit=null) {
        $session = new Zend_Session_Namespace("devices");
        $devieArray = $session->selectedDevices;
        $deviceIds = array();
        
        

        if(!empty($devieArray))
        {
            foreach($devieArray as $key => $value)
            {
                $deviceIds[] =  $key;
            }
            $deviceId = join(',',$deviceIds);
        }

        if(!empty($deviceId))
        {
            $productSql1   = $this->select();
            $productSql1->from(array('cp' => $this->_name), array())
                ->columns(array('sp.product_id','cp.*'))
                ->setIntegrityCheck(false)
                ->join(array('pb' => 'product_builds'), 'pb.product_id = cp.id', array('pb.id as build_id'))
                ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                ->join(array('sp' => 'statistics_products'), 'sp.product_id = cp.id', array('pro_count'=>'COUNT(sp.product_id)'))
                ->where('bd.device_id IN('.$deviceId.')')
                ->where('cp.status = ?','APPROVED')
                ->where('cp.deleted != ?',1)
                ->where('pb.device_selection_type = ?', 'CUSTOM')
                ->where('cp.user_id != ?', 5981)
                ->group('sp.product_id');

            $productSql2   = $this->select();
            $productSql2->from(array('cp' => $this->_name), array())
                ->columns(array('sp.product_id','cp.*'))
                ->setIntegrityCheck(false)
                ->join(array('pb' => 'product_builds'), 'pb.product_id = cp.id', array('pb.id as build_id'))
                ->join(array('pda' => 'product_device_saved_attributes'), 'pda.build_id = pb.id', array())
                ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pda.device_attribute_definition_id AND da.value = pda.value', array())
                ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                ->join(array('sp' => 'statistics_products'), 'sp.product_id = cp.id', array('pro_count'=>'COUNT(sp.product_id)'))
                ->where('d.id IN ('.$deviceId.')')
                ->where('cp.status = ?','APPROVED')
                ->where('cp.deleted != ?',1)
                ->where('pb.device_selection_type != ?', 'CUSTOM')
                ->where('cp.user_id != ?', 5981)
                ->group('sp.product_id');


            $allProductsSql = $this->select()->union(array("($productSql1)", "($productSql2)"))
                ->order('pro_count DESC')
                ->limit($limit);

            $products = $this->fetchAll($allProductsSql)->toArray();
            $i=0;
            foreach($products as $product)
            {
                $products[$i][] = $this->getProductDetailsById($product['product_id']);
                $i++;
            }
            return $products;

        }else{
            

            $productSql   = $this->select();
            $productSql->from(array('cp' => $this->_name), array())
                ->columns(array('sp.product_id','cp.*'))
                ->setIntegrityCheck(false)
                ->join(array('sp' => 'statistics_products'), 'sp.product_id = cp.id', array('pro_count'=>'COUNT(sp.product_id)'))
                ->where('cp.status = ?','APPROVED')
                ->where('cp.user_id != ?', 5981)
                ->where('cp.deleted != ?',1)
                ->order('pro_count DESC')
                ->group('sp.product_id')
                ->limit($limit);
            
            
            $products = $this->fetchAll($productSql)->toArray();

            $i=0;
            foreach($products as $product)
            {
                $product['vendor'] = 'Testing Vendor';
                $products[$i][] = $this->getProductDetailsById($product['product_id']);
                $i++;
            }

   
            
            return $products;

        }

    }

    /**
     * Returns a list of products by a cp.
     *
     *
     * @param array $deviceId Products will be filtered by these device IDs
     * @param int $categoryId The category id
     * @param int $limit Limits number of products to be returned.
     * @return array
     *
     * @todo: getCategoryProducts() does the same thing - expect it does it for 1 single device. Consider merging this function with that.
     */
    public function getProductsByCp($deviceId, $cpId, $limit=null) {


        $db = Zend_Registry::get('db');
        $productsModel = new Model_Product();

        //if( !is_array($deviceId) || count($deviceId) == 0 )
        if (empty($deviceId)) {

            $select = $productsModel->select()
                    ->setIntegrityCheck(false)
                    ->from('products')
                    ->columns(array('thumbnail as thumb', 'price as cost', 'id as product_id'))
                    ->where('products.user_id =  ' . $cpId)
                    ->where("products.status = 'APPROVED'")
                    ->where('products.deleted <> 1')
                    ->where('products.inapp IS NULL')
                    ->order('products.id DESC')
                    ->group('products.name');




            return $select;
        } else {

            if (is_array($deviceId))
                $devices = implode($deviceId, ",");
            else
                $devices = $deviceId;


            $select = $productsModel->select()
                    ->from('products')
                    ->setIntegrityCheck(false)
                    ->join('product_builds', 'product_builds.product_id = products.id')
                    ->join('build_devices', 'product_builds.id = build_devices.build_id')
                    ->join('users', 'users.id = products.user_id', array('users.id as user_id'))
                    ->where("build_devices.device_id IN($devices)")
                    ->where('users.id =  ' . $cpId)
                    ->where("products.status = 'APPROVED'")
                    ->where('products.deleted <> 1')
                    ->where('products.inapp IS NULL')
                    ->order('products.id DESC')
                    ->group('products.name');


            return $select;
        }

//
//    $modelProdDevice = new Model_ProductDevices();
//    $filter = array();
//    $filter = array(
//       'where' => array('users.id =  ' . $cpId),
//      'limit' => $limit
//    );
//    $resultRows = $modelProdDevice->getProducts($deviceId, $filter);
//    return $resultRows;
    }

    /**
     * Returns an array of product Ids that are compatible with $productId
     * If $deviceId is specified, it would filter the products by the devices(s)
     *
     * @param int $productId
     * @param mixed $deviceId
     * @return array
     */
    public function getCompatibleProducts($productId, $deviceId=null) {

        /**
         * @var Zend_Db $db
         */
        $db = Zend_Registry::get('db');

        $select = $db->select()
                ->from('products', array('products.id'))
                ->join('product_builds', 'products.id = product_builds.product_id')
                ->join('build_devices', 'product_builds.id = build_devices.build_id', array('build_id'))
                ->join('devices', 'devices.id = build_devices.device_id', array('devices.id'))
                ->where("products.id = " . $productId)
                ->where("product_builds.status = 1")
                ->where("products.status = 'APPROVED'")
                ->where('products.deleted <> 1')
                ->where('products.inapp IS NULL')
                ->group('build_devices.device_id');

        if ($this->appFilter == 'FREE') {
            $select->where('price = 0');
        } else if ($this->appFilter == 'PAID') {
            $select->where('price > 0');
        }                
                
                
        if (!is_null($deviceId)) {

            if (is_array($deviceId))
                $devices = implode($deviceId, ",");
            else
                $devices = $deviceId;

            $select->where("build_devices.device_id IN ($devices)");
        }
        else {
            $select->limit(100);
        }

        
        $key        = 'COMPATIBLE_PRODUCTS_' . md5((string) $select);
        $cache      = Zend_Registry::get('cache');
        if (($resultRows = $cache->get($key)) === false) {
            $resultRows = $db->fetchall($select); 
            $cache->set($resultRows, $key);
        } 

        if (0 == count($resultRows))
            return array();

        //get all devices
        $devices = array();
        foreach ($resultRows as $row) {
            array_push($devices, $row->id);
        }


        $select = $db->select()
                ->from('products', array('products.id'))
                ->join('product_builds', 'products.id = product_builds.product_id')
                ->join('build_devices', 'product_builds.id = build_devices.build_id', array('build_id'))
                ->join('devices', 'devices.id = build_devices.device_id', array('devices.id'))
                ->where("build_devices.device_id IN (" . implode(",", $devices) . ")")
                ->where("product_builds.status = 1")
                //I don't consider external apps (URLS) or platform independent stuff (most probably majority of them are free and would skew results)
                ->where("products.content_type <> 'URL'")
                ->where("products.platform_id NOT IN (0)")
                ->where("products.id <> " . $productId)
                ->where('products.deleted <> 1')
                ->where('products.inapp IS NULL')
                ->where("products.status = 'APPROVED'")
                ->group('products.id')
                ->order('RAND(NOW())') //see below
                ->limit(15); //limit set for now due to performance reasons. @todo: when we have memcached in place, remove this

                
        if ($this->appFilter == 'FREE') {
            $select->where('price = 0');
        } else if ($this->appFilter == 'PAID') {
            $select->where('price > 0');
        }                       


        $key        = 'COMPATIBLE_PRODUCTS_RESULTS_' . md5((string) $select);
        if (($resultRows = $cache->get($key)) === false) {
            $resultRows = $db->fetchall($select);
            $cache->set($resultRows, $key);
        }  

        $products = array();
        if (!is_null($resultRows)) {
            foreach ($resultRows as $row) {
                $products[] = $row->product_id;
            }
        }
        return $products;
    }

    /**
     * Get already purchased products
     * @param <type> $deviceId
     * @return <type>
     */
    public function getPurchasedProductsByDeviceId($deviceId, $userId) {
        $where = array(
            'join' => array(
                'order_details' => 'order_details.product_id = products.id' ,
                'orders' => 'orders.id = order_details.order_id'
            ),
            'where' => array(
                'orders.user_id = ' . $userId 
            )
        );
        $productIds = $this->getCompatibleAppsByDeviceId($deviceId, $where, null, null, false);//setting no cache, no paging
        $productsInfo = array();
        foreach ($productIds as $key => $value) {
            $productsInfo[] = $this->getProductDetailsById($value->product_id);
        }
        return $productsInfo;
    }

    /**
     * Search products by keyword
     * @param <type> $deviceId
     * @param <type> $keyword
     * @param $category filters by category as well
     * @param $limit limit of the products
     * @param $page page for paging
     * @return <type>
     */
    public function getSearchProductsByKey($deviceId, $keyword, $category = null, $limit = null, $page = null) {
        $catFilter  = ''; 
        
        $where = array( 
          'where' => array('products.name LIKE "%' . $keyword . '%" ' . $this->getPriceSqlFragment())
        );
        
        if (!empty($category)) {
            $where['join'] = array(
                'product_categories'    => 'product_categories.product_id = products.id',
                'categories'            => 'categories.id = product_categories.category_id'
            );
          $where['where'][] = 'categories.id =  ' . $category . " OR categories.parent_id = " . $category; 
        }

        $productIds = $this->getCompatibleAppsByDeviceId($deviceId, $where, $limit, $page);
        $productsInfo = array();
        foreach ($productIds as $key => $value) {
            $productsInfo[] = $this->getProductDetailsById($value->product_id);
        }
        return $productsInfo;
    }

    /**
     *
     * @param <type> $keywords
     * @param <type> $devices
     * @param <type> $start
     * @param <type> $limit
     */
    public function searchProduct($keywords, $devices=null, $start=0, $limit=20) {

        if (is_null($devices))
            $rows = $this->fetchAll("name LIKE %keywords%", 'id DESC', $limit, $start);
        else {
            $db = Zend_Registry::get('db');

            if (is_array($deviceId))
                $devices = implode($devices, ",");

            $select = $db->select()
                    ->from('product_categories')
                    ->distinct()
                    ->join('products', 'products.id = product_categories.product_id', array('products.id'))
                    ->join('categories', 'categories.id = product_categories.category_id', array('categories.name'))
                    ->join('product_builds', 'product_builds.product_id = product_categories.product_id')
                    ->join('build_devices', 'product_builds.id = build_devices.build_id')
                    ->where("build_devices.device_id IN($devices)")
                    ->where('categories.id =  ' . $categoryId . " OR categories.parent_id = " . $categoryId)
                    ->where("products.status = 'APPROVED'")
                    ->where('products.deleted <> 1')
                    ->where('products.inapp IS NULL')
                    ->group('products.name')
                    ->limit($limit);
        }
    }

    /**
     * Get mini product details to show in every where
     * @param <type> $productId
     * @return <type>
     */
    public function getProduct($productId) {
        $product = new Model_Product();
        $productMeta = new Model_ProductMeta();
        $prod = '';
        $rowset = $product->find($productId);
        $productRow = $rowset->current();
        // config visual path\
        $viaualPath = Zend_Registry::get('config')->product->visuals->dirpath;
        $prod['thumb'] = $viaualPath . '/' . $productRow->thumbnail;
        $prod['cost'] = $productRow->price;
        $prod['name'] = $productRow->name;
        $prod['id'] = $productRow->id;
        $prod['uid'] = $productRow->user_id;
        // set entity Id
        $productMeta->setEntityId($productRow->id);
        $prod['desc'] = strip_tags($productMeta->FULL_DESCRIPTION);
        // get statics details
        $statics = new Model_StatisticProduct();
        $prod['hits'] = $statics->getAllViewCount($productId);

        return $prod;
    }

    //get selected devices
    /**
     *
     * @param <type> $productId
     * @return <type>
     */
    public function getSelectedDevices($productId) {
        $phones = array();
//        $productTable = new Model_Product();
        $productRowset = $this->find($productId);
        $productIs = $productRowset->current();
        $devicesByProduct = $productIs->findDependentRowset('Model_ProductDevices');
        $deviceTable = new Model_Device();
        foreach ($devicesByProduct as $device) {
            $phones = array_merge($phones, $deviceTable->getDeviceAttributesById($device->device_id));
        }
        return $phones;
    }

    /**
     * Save product data, update and insetrt in one shot
     * @param <type> $data
     * @return <type>
     */
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
     * Get build for a product
     * @param <type> $productId
     * @return <type>
     */
    public function getBuilds($productId) {
        $productBuilds = new Model_ProductBuild();
        return $productBuilds->getBuildsByProductId($productId);
    }

    // @TODO : Move the $id to the contruct to set it in the construct
    /**
     * Change prodcut status
     * @param <type> $status
     * @param <type> $id
     */
    public function setStatus($status, $id) {
        $data = array('status' => $status);
        $this->update($data, array('id = ?' => $id));
    }

    /**
     * Get all featured products
     * @param <type> $compatibleDevices
     * @param <type> $limit
     * @return <type>
     */
    public function getFeaturedProducts($compatibleDevices = null, $limit=10) {
        
        
        $prodID         = Zend_Registry::get("config")->nexva->application->widget->id;
        
        $extraConds     = $this->getPriceSqlFragment();
        $filter = array(
          'where' => array('products.is_featured = 1 AND products.id <> ' . $prodID . ' ' . $extraConds),
          //'where' => array('products.is_featured = 1 AND products.id <> ' . $prodID . ' ' . $extraConds.'AND products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)'),
          'order' => array('RAND()'), //for when this is a web call
          'union' => array('order' => array('RAND()')), //for when it's mobile. The system unsets the correct index
          'limit' => isset($limit) ? $limit : 10
        );
        $products = $this->getCompatibleAppsByDeviceId($compatibleDevices, $filter, null, null, false);

        $result = array();
        foreach ($products as $product) {
            $result[] = $this->getProductDetailsById($product->product_id);
        }
        return $result;
    }
    
    /**
     * Get all featured products for the Mobile version - new function added - 10/04/2012
     * @param <type> $compatibleDevices
     * @param <type> $limit
     * @return <type>
     */
    public function getFeaturedProductsMobile($compatibleDevices = null, $limit=10) {
        
        
        $prodID         = Zend_Registry::get("config")->nexva->application->widget->id;
        
        $extraConds     = $this->getPriceSqlFragment();
        $filter = array(
          'where' => array('products.id <> ' . $prodID . ' ' . $extraConds),
          //'where' => array('products.is_featured = 1 AND products.id <> ' . $prodID . ' ' . $extraConds.'AND products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)'),
          'order' => array('RAND()'), //for when this is a web call
          'union' => array('order' => array('RAND()')), //for when it's mobile. The system unsets the correct index
          'limit' => isset($limit) ? $limit : 10
        );
        $products = $this->getCompatibleAppsByDeviceId($compatibleDevices, $filter, null, null, false);

        $result = array();
        foreach ($products as $product) {
            $result[] = $this->getProductDetailsById($product->product_id);
        }
        return $result;
    }
    
    /**
     * 
     * based on whether or not a app filter is set, a price condition is set.
     * @param $noCondition drops the AND keyword and just returns the fragment
     */
    private function getPriceSqlFragment($noCondition = false) {
        $condition      = ' AND '; 
        $priceFilter    = null; 
        if ($this->appFilter == 'FREE') {
            $priceFilter    = ' price = 0 ';
        } else if ($this->appFilter == 'PAID') {
            $priceFilter    = ' price > 0 ';
        } else {
            return null;
        }
        return ($noCondition) ? $priceFilter : $condition . $priceFilter;
    }
    
    /**
     * Promoted apps
     * @param <type> $productId
     * @param <type> $deviceId
     * @return <type>
     */
    public function getPromotedProducts($deviceId) {
        if (isset($_SERVER['HTTP_X_NEXVA_APPVERSION_MAJOR']))
            return;
        $prodID = Zend_Registry::get("config")->nexva->application->widget->id;
        if (!$this->isCompatible($prodID, $deviceId))
            return;
        $result = array();
        $result[] = $this->getProductDetailsById($prodID);
        return $result;
    }

    /**
     * Returns a **formatted** list (grouped by device brand) of compatible devices for a product.
     * Compatible device models will be delimited with a ","
     *
     * This is useful when you want to show the compatible devices in a tabular manner:
     *
     *  Nokia    3310, 8800 (Sirroco), 6600, NGAGE-QD, 6500 (Slide), 6500 Classic
     *  RIM      Blackberry 9000 (Bold), Blackberry 6120
     *
     * @param int $id Product Id
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getFormattedProductCompatibleDevices($id, $buildId = false, $deviceSelectionType = false ) {
        $db = Zend_Registry::get('db');
        
        return; 
        
        if( $buildId == false )	{
        
	        $sql = "
	            SELECT
	            devices.brand,
	
	             GROUP_CONCAT(DISTINCT devices.model
	                    ORDER BY devices.model DESC SEPARATOR ', ') AS 'models'
	
	            FROM
	            build_devices
	            Inner Join product_builds ON build_devices.build_id = product_builds.id
	            Inner Join devices ON build_devices.device_id = devices.id
	            Inner Join products ON products.id = product_builds.product_id
	            WHERE
	            products.id =  $id and product_builds.device_selection_type = 'CUSTOM'
	
	            group by(devices.brand)
	
	                UNION ALL
	
	            SELECT
	            devices.brand,
	            GROUP_CONCAT(DISTINCT devices.model
	                                ORDER BY devices.model DESC SEPARATOR ', ') AS 'models'
	            FROM device_attributes INNER JOIN devices ON device_attributes.device_id = devices.id
	               INNER JOIN product_device_saved_attributes ON product_device_saved_attributes.device_attribute_definition_id = device_attributes.device_attribute_definition_id AND product_device_saved_attributes.value = device_attributes.value
	               INNER JOIN product_builds ON product_builds.id = product_device_saved_attributes.build_id
	               INNER JOIN products ON products.id = product_builds.product_id
	            WHERE products.id = $id
	            group by(devices.brand)";
			
	      
	        
	        $cache          = Zend_Registry::get('cache');
	        $cacheKey       = 'COMPATIBLE_DEVICES_' . $id;
	        if (($resultRows = $cache->get($cacheKey)) === false) {
	            $stmt       = $db->query($sql); //probably not a good idea this - but I could not fig out how to use GROUP_CONCAT in a select() object
	            $resultRows = $stmt->fetchAll();
	            $cache->set($resultRows, $cacheKey);
	        }
        
        }
        else {
        	
        	
        	
        	if($deviceSelectionType == 'CUSTOM' )	{
        		
        		$sql = "
		            SELECT
		            devices.brand,
		
		             GROUP_CONCAT(DISTINCT devices.model
		                    ORDER BY devices.model DESC SEPARATOR ', ') AS 'models'
		
		            FROM
		            build_devices
		            Inner Join product_builds ON build_devices.build_id = product_builds.id
		            Inner Join devices ON build_devices.device_id = devices.id
		            Inner Join products ON products.id = product_builds.product_id
		            WHERE
		            products.id =  $id and product_builds.device_selection_type = 'CUSTOM' and product_builds.id = $buildId
		
		            group by(devices.brand)";
        		
        		
        	}
        	
        	if($deviceSelectionType == 'BY_ATTRIBUTE' )	{
        		
        		 $sql = "
		            SELECT
		            devices.brand,
		            GROUP_CONCAT(DISTINCT devices.model
		                                ORDER BY devices.model DESC SEPARATOR ', ') AS 'models'
		            FROM device_attributes INNER JOIN devices ON device_attributes.device_id = devices.id
		               INNER JOIN product_device_saved_attributes ON product_device_saved_attributes.device_attribute_definition_id = device_attributes.device_attribute_definition_id AND product_device_saved_attributes.value = device_attributes.value
		               INNER JOIN product_builds ON product_builds.id = product_device_saved_attributes.build_id
		               INNER JOIN products ON products.id = product_builds.product_id
		            WHERE products.id = $id and product_builds.id = $buildId
		            group by(devices.brand)";
        		
        	}
        	
        	if($deviceSelectionType == 'ALL_DEVICES' )	{
        
	        $sql = "
	            SELECT
	            devices.brand,
	
	             GROUP_CONCAT(DISTINCT devices.model
	                    ORDER BY devices.model DESC SEPARATOR ', ') AS 'models'
	
	            FROM
	            build_devices
	            Inner Join product_builds ON build_devices.build_id = product_builds.id
	            Inner Join devices ON build_devices.device_id = devices.id
	            Inner Join products ON products.id = product_builds.product_id
	            WHERE
	            products.id =  $id and product_builds.device_selection_type = 'CUSTOM' and product_builds.id = $buildId
	
	            group by(devices.brand)
	
	                UNION ALL
	
	            SELECT
	            devices.brand,
	            GROUP_CONCAT(DISTINCT devices.model
	                                ORDER BY devices.model DESC SEPARATOR ', ') AS 'models'
	            FROM device_attributes INNER JOIN devices ON device_attributes.device_id = devices.id
	               INNER JOIN product_device_saved_attributes ON product_device_saved_attributes.device_attribute_definition_id = device_attributes.device_attribute_definition_id AND product_device_saved_attributes.value = device_attributes.value
	               INNER JOIN product_builds ON product_builds.id = product_device_saved_attributes.build_id
	               INNER JOIN products ON products.id = product_builds.product_id
	            WHERE products.id = $id and product_builds.id = $buildId
	            group by(devices.brand)";
	        
        	}

           $cache          = Zend_Registry::get('cache');
	            $cacheKey       = 'COMPATIBLE_APPS' . $buildId.$id;
	            if (($resultRows = $cache->get($cacheKey)) === false) {
	            	$stmt       = $db->query($sql); //probably not a good idea this - but I could not fig out how to use GROUP_CONCAT in a select() object
	            	$resultRows = $stmt->fetchAll();
	            	$cache->set($resultRows, $cacheKey);
	            }
	            
        	
	        }
        	
    
        
		//  $resultRows2 = $db->query($sql2)->fetchAll();
        return $resultRows;
    }

    /**
     * Download product, will add to statics and chane the files headers on S3 and will change the ACL
     * @param <type> $productId
     * @return <type>
     */
    public function downloadProduct($productId, $chapId = null, $checkForPurchased = true, $trackDownload = true, $setlanguageId = false, $np = null) {
    	if($setlanguageId)
    		$productInfo = $this->getProductDetailsById($productId, false, null, $setlanguageId);
    	else 
    		$productInfo = $this->getProductDetailsById($productId);
        
    	
    	/**
    	 * This isn't ideal. The promotion code is persisted in the serssion and we call the lib to get it back
    	 */	
		$promoCodeLib     = new Nexva_PromotionCode_PromotionCode();
		$validCode        = $promoCodeLib->checkPromotiocodeValidityForProduct($productId);
        if ($validCode) {
            $promocodeSession       = $promoCodeLib->getAppliedCode();
            $promocode              = $promocodeSession->codeObject;
            $promoCodeType          = Nexva_PromotionCode_Factory::getPromotionCodeType($promocode['code']);
            $productInfo['cost']    = $promoCodeType->applyPriceModification($productInfo['cost']);

            if ($productInfo['cost'] <= 0) {
                //a product that's been made free because of a promocode.
                //someone's downloading it so do post processing on the promocode
                $promoCodeType->doPostProcess($productId);
                
                $deviceSession = new Zend_Session_Namespace('Device');
                
                $promoStats = new Nexva_Analytics_ProductPurchase();
                $data   = array(
                    'app_id'    => $productInfo['id'], 
                    'price'     => 0.00, 
                    'device_id' => $deviceSession->deviceId,
                    'device_name'   => $deviceSession->deviceName,
                    'chap_id'   => $chapId,
                    'platform'  => 'MOBILE',
                    'code'      => array(
                        'code_id'       => $promocode['id'],    
                        'code'          => $promocode['code'],
                        'code_owner_id' => $promocode['user_id']
                    )
                ); 
                $promoStats->log($data);
                
            }
        }
		
        // eheck for product download. if it is URL then don't check this
        if ($checkForPurchased) {
            if ($productInfo['cost'] != 0) {
                $order = new Model_Order();
                if ($productInfo['content_type'] != 'URL' && $productInfo['cost'] > 0)	{
                	if (! $order->isPruchsed($productId)) {
                    	return '/app/buy';
                	}
                }
            }
        }


        if ($productInfo['content_type'] != 'URL') 
        {

                if($setlanguageId)
        		$fileNameForS3Check = $this->getDownloadFileFileCheck($productId, $setlanguageId);
        	else
        		$fileNameForS3Check = $this->getDownloadFileFileCheck($productId);
        	
  
        	if(strlen($fileNameForS3Check) == 0)
                    return 'app/notfound/id/' . $productId;

        	$objectTmp = "$productId/$fileNameForS3Check"; 
               
        	$s3FileExists = $this->s3FileExist($objectTmp);
              
            if($setlanguageId)
            	$fileName = $this->getDownloadFile($productId, $setlanguageId);
            else 
            	$fileName = $this->getDownloadFile($productId);
            
            $object = "$productId/$fileName";
            
            // check ojbect exists if not 404 not found
            if (!$s3FileExists and  $productInfo) 
            {
                
            	if($fileNameForS3Check != 'error_no_build')		
                 {
	            	// send mail to admins
	                unset ($productInfo['user_meta']);
	                $config = Zend_Registry::get("config");
	                $mailer = new Nexva_Util_Mailer_Mailer();
	                $mailer->setSubject('neXva - File not found');
	                
	                //get the product build info
	                $buildProduct = new Model_ProductBuild();
	       			$buildInfo = $buildProduct->getBuildByProductAndDevice($productId);
	       			
	       			$language = new Model_Language();
	       			$languageInfo = $language->getLanguageByid($buildInfo->language_id);
	                
	                if( APPLICATION_ENV != 'production')
	                    $mailer->addTo('chathura@nexva.com');
	                else
	                    $mailer->addTo(explode(',', $config->nexva->application->content_admin->contact));
	                    
	                $deviceSession = new Zend_Session_Namespace('Device');    
	              
	                $mailer->setLayout("generic_mail_template")
	                    ->setMailVar("data", $productInfo)
	                    ->setMailVar("user_id", isset(Zend_Auth::getInstance ()->getIdentity()->id) ? Zend_Auth::getInstance ()->getIdentity()->id : 'N/A')
	                    ->setMailVar("device_id", $deviceSession->deviceId)
	                    ->setMailVar("device_name", $deviceSession->deviceName)
	                    ->setMailVar("build_name", $buildInfo->name)
	                    ->setMailVar("build_language_id", $languageInfo->id)
	                    ->setMailVar("build_language", $languageInfo->common_name)
	                    ->setMailVar("filename", $config->aws->s3->bucketname . '/productfile/' . $object)
	                    ->sendHTMLMail('file_download_failed.phtml');
            	}
                
            	return 'app/notfound/id/' . $productId;
            }
            
            //set S3 headers
            $this->setS3FileHeaders($object);
            // construct the pvt url
           
            $s3Url = new Nexva_View_Helper_S3Url();
            $url = $s3Url->S3Url($object);

        }
        else {
            $url = $productInfo['file'][0];
        }
        // Add to statics
        if ($trackDownload) {
            $deviceSession = new Zend_Session_Namespace('Device');    
            $statics = new Model_StatisticDownload();
            if($np)
   			    $statics->updateProductDownloadCount($productId, $chapId, $deviceSession->deviceId, $deviceSession->deviceName, $setlanguageId, $np);
            else 
                $statics->updateProductDownloadCount($productId, $chapId, $deviceSession->deviceId, $deviceSession->deviceName, $setlanguageId);
            
            
            //************* Add codengo *************************
            $condengo = new Nexva_Util_Http_SendRequestCodengo;
            $condengo->send($productId);
            
        }

        return $url;
    }

    /**
     *
     * @param <type> $productId
     * @param <type> $deviceId
     * @return <type> 
     */
    public function isCompatible($productId, $deviceIds) {
        if (is_array($deviceIds)) {
            $devices = implode(', ', $deviceIds);
        } else {
            $devices = (int) $deviceIds;
        }
        $builds = new Model_ProductBuild();
        $manual = $builds->getBuildByProductAndDevice($productId);

       
        if ($manual)
            return true;
        else
            return false;
    }

    /**
     * Get product category by product Id
     * @param <type> $productId
     * @return <type>
     */
    function getProductCategoryByProductId($productId) {

        $product = $this->select();

        $product->setIntegrityCheck(false)
            ->from('products', null)
            ->where('products.id= ?', $productId)
            ->joinInner('product_categories', 'product_categories.product_id = products.id', array('category_id'))
            ->joinInner('categories', 'categories.id = product_categories.category_id', 'product_categories.category_id');

        $cache  = Zend_Registry::get('cache');
        $key    = 'CATEGORY_ID_BY_PRODUCT_' . $productId;
        
        if (($categoryId = $cache->get($key)) === false) {
            $product->query();
            $categoryId = $this->fetchAll($product)->toArray();
            $cache->set($categoryId, $key);    
        }

        if (isset($categoryId[0]['category_id']))
            return $categoryId[0]['category_id'];
        else
            return array();
    }

    /**
     * Check if the products is featured or not by product id
     * @author Chathura
     * @param    int productId
     * @return product is  featured or not 1 or 0
     */
    function getProductFeaturedProductId($productId) {

        $cache     = Zend_Registry::get('cache');
        $cache     = Zend_Registry::get('cache');
        $key        = 'PRODUCT_IS_FEATURED_' . $productId;
        if (($categoryId = $cache->get($key)) !== false) {
            return $categoryId;
        }
        
        $product = $this->select();
        $product->setIntegrityCheck(false)
            ->from('products', array('is_featured'))
            ->where('id= ?', $productId)
            ->query();

        $categoryId = $this->fetchAll($product)->toArray();
        
        $cache->set($categoryId[0]['is_featured'], $key);
        return $categoryId[0]['is_featured'];
    }

    /**
     * Get the list of products by cps id
     * @author Chathura
     * @param    int cpid
     * @return product info array
     *
     */
    public function getProductsOfSameUser($userId, $limit='all', $nexpager = false) {
    	
        $filterNexpager = ' ';
        
        if($nexpager == true)
          $filterNexpager = ' AND products.show_in_nexpager = 1 ' ;


        if (!is_null($userId)) {
            
        	if($limit == 'all')   {
        	
            $select = $this->select()
                    ->from('products')
                    ->where('user_id = ?', $userId)
                    ->where("products.status = 'APPROVED'". $filterNexpager )
                    ->where('products.deleted <> 1')
                    ->where('products.inapp IS NULL')
                    ->order('id DESC');
                    
        	}
        	else   {
        		
        	 $select = $this->select()
                    ->from('products')
                    ->where('user_id = ?', $userId)
                    ->where("products.status = 'APPROVED'". $filterNexpager )
                    ->where("products.deleted <> 1")
                    ->order('id DESC')
                    ->limit($limit);
        		
        	}

            $resultRows = $this->fetchall($select);
        }
        /*
          $products = array();

          if (!is_null($resultRows)) {

          foreach ($resultRows as $row) {
          $products[] = $this->getProductDetailsById($row->id);
          }
          }


          return $products;

         */

        return $resultRows;
    }
    
    public function getProductsCountOfUser($userId, $nexpager = false) {

    	 $filterNexpager = ' ';
        
        if($nexpager == true)
          $filterNexpager = ' AND products.show_in_nexpager = 1 ' ;

        if (!is_null($userId)) {
            
            
            $select = $this->select()
                    ->from('products')
                    ->where('user_id = ?', $userId)
                    ->where("products.status = 'APPROVED'".  $filterNexpager )
                    ->where('products.deleted <> 1')
                    ->where('products.inapp IS NULL')
                    ->order('id DESC');

            $noOfApps = $this->fetchall($select)->count();

         }
         
        return $noOfApps;
    }

    /**
     * Get the list of products by keywords and name
     * @author Chathura
     * @param    int cpid
     * @return product info array
     *
     */
    public function getProductsBySearchKeyWords($name, $keyword, $tmpDeviceId) {

        $db = Zend_Registry::get('db');

        $keysArr = explode(' ', $name);
        $keyStr = "name LIKE " . $db->quote("%" . $name . "%") . " OR keywords LIKE " . $db->quote("%" . $keyword . "%");

        if ($tmpDeviceId == NULL) {

            $select = $this->select()
                    ->distinct()
                    ->from('products', array('products.id'))
                    ->where($keyStr)
                    ->where('products.deleted <> 1')
                    ->where('products.inapp IS NULL')
                    ->where("products.status = 'APPROVED'");
        } else {

            $select = $this->select()
                    ->distinct()
                    ->setIntegrityCheck(false)
                    ->from('products', array('products.id'))
                    ->joinInner('product_devices', 'product_devices.product_id = products.id', array('product_id'))
                    ->where('device_id IN (?)', $tmpDeviceId)
                    ->where($keyStr)
                    ->where('products.deleted <> 1')
                    ->where('products.inapp IS NULL')
                    ->where("products.status = 'APPROVED'");
        }


        $resultRows1 = $this->fetchall($select)->toArray();




        if (count($keysArr) > 1) {
            foreach ($keysArr as $keyval) {
                $keyStr .= " OR name LIKE " . $db->quote("%" . $keyval . "%") . " OR keywords LIKE " . $db->quote("%" . $keyval . "%");
            }





            if ($tmpDeviceId == NULL) {

                $select = $this->select()
                        ->distinct()
                        ->from('products', array('products.id'))
                        ->where($keyStr)
                        ->where('products.deleted <> 1')
                        ->where('products.inapp IS NULL')
                        ->where("products.status = 'APPROVED'");
            } else {

                $select = $this->select()
                        ->distinct()
                        ->setIntegrityCheck(false)
                        ->from('products', array('products.id'))
                        ->joinInner('product_devices', 'product_devices.product_id = products.id', array('product_id'))
                        ->where('device_id IN (?)', $tmpDeviceId)
                        ->where($keyStr)
                        ->where('products.deleted <> 1')
                        ->where('products.inapp IS NULL')
                        ->where("products.status = 'APPROVED'");
            }

            $resultRows2 = $this->fetchall($select)->toArray();
        }



        if (!empty($resultRows2)) {
            if (count($resultRows1) >= 1) {
                foreach ($resultRows1 as $valRow) {
                    array_unshift($resultRows2, $valRow);
                }
            }

            $newResults = $resultRows2;
        } else {
            $newResults = $resultRows1;
        }


        return $newResults;
    }

    /**
     * Get product name  to show in every where
     * @param <type> $productId
     * @return <type> string product Name
     * Chathura Jayasekara
     */
    public function getProductNameById($productId) {
        $product = new Model_Product();
        $productMeta = new Model_ProductMeta();
        $prod = '';
        $rowset = $product->find($productId);
        $productRow = $rowset->current();


        return $productRow->name;
    }

    /**
     * Get product name  to show in every where
     * @param <type> $productId
     * @return <type> string product Name
     * Chathura Jayasekara
     */
    public function getProductValById($productId) {
        $product = new Model_Product();
        $rowset = $product->find($productId);
        $productRow = $rowset->current();


        return $productRow;
    }

    /**
     * Checks if the product with a similar name ($name) and price ($price) already exists on the DB. 
     * 
     * @param $name
     * @param $price
     * @return boolean TRUE if exists, error message if not
     */
    function checkDuplicateProduct($name, $price, $userId) {
        $check_product = $this->fetchRow(
                    $this->select()->where("UPPER(name)=?", strtoupper($name))
                    ->where('products.deleted <> 1')
                    ->where('products.inapp IS NULL')
                    ->where("products.price = $price")
                    ->where("products.user_id = $userId")
        );
        if (!is_null($check_product)) {
            $error = " Sorry, but an application with this name already exists in your portfolio.
                       If you think this is a mistake, please contact us at <a href='mailto:contact@nexva.com>contact@nexva.com </a>.";
        }

        if (!isset($error))
            return true;
        else
            return $error;
    }

    /**
     * Returns an associated array of platforms the app supports.
     *    
     * @param int $productID
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getSupportedPlatforms($productId, $chapPlatformType = NULL) {

        //if the chapter platform is 'ANDROID_PLATFORM_CHAP_ONLY' than get only the android platforms
        if($chapPlatformType == 'ANDROID_PLATFORM_CHAP_ONLY'):
            $select = Zend_Registry::get('db')->select()
                ->from('products', array('platforms.id'))
                ->join('product_builds', 'product_builds.product_id = products.id', array('product_builds.platform_id','product_builds.id as build_id'))
                ->join('platforms', 'platforms.id = product_builds.platform_id', array('platforms.name'))
                ->where('products.id = ?', $productId)
                ->where('platforms.id = ?', 12)
                ->order('platforms.name')
                ->group('platforms.id');
        else:
            $select = Zend_Registry::get('db')->select()
                ->from('products', array('platforms.id'))
                ->join('product_builds', 'product_builds.product_id = products.id', array('product_builds.platform_id','product_builds.id as build_id'))
                ->join('platforms', 'platforms.id = product_builds.platform_id', array('platforms.name'))
                ->where('products.id = ?', $productId)
                //->where('product_builds.build_type = ?', 'files')
                ->order('platforms.name')
                ->group('platforms.id');
        endif;
        
        $rows = Zend_Registry::get('db')->fetchAll($select);
        //$rows = $this->fetchAll($select);

        return $rows;
    }
    
   /**
     * Returns procut id by encryptedid for inapp
     *    
     * @param int $productEncId
     * @return int id
     */
    
    public function getProductIdByEncryptedId($productEncId) {

    	$salt = Zend_Registry::get('config')->nexva->application->salt;
    	
        $select = Zend_Registry::get('db')->select()
                ->from('products', array('products.id'))
                ->where("md5(concat('".$salt."', products.id)) ='$productEncId'");
        $rows = Zend_Registry::get('db')->fetchRow($select);

        return $rows->id;
    }

    public function checkFilesinS3($productId) {
        $buildProd = new Model_ProductBuild();
        $builds = $buildProd->getBuildsByProductId($productId);
        $files = array();
//        $return = TRUE;
        foreach ($builds as $build) {
            if ($build->build_type != 'file')
                continue;
            $files = $buildProd->getFiles($build->id);
            foreach ($files as $file) {
                $s3 = new Nexva_View_Helper_S3FileCheck();
                if (!$s3->S3FileCheck($productId . '/' . $file->filename))
                    return FALSE;
            }
        }
        return TRUE;
    }
    
    /**
     * 
     * Returns the Select object needed to build the query for pagination
     * @param $keywords
     * @param $tmpDeviceId array
     * @author John
     */
    public function getSearchQuery($keywords, $tmpDeviceId, $simpleSearch = false) {
               
        $db         = Zend_Registry::get('db');
        $keysArr    = explode(' ', $keywords);
        
        
        if ($simpleSearch) {
            $keyStr     = 'products.name LIKE ' . $db->quote("%" . $keywords . "%");
        } else {
            $keyStr     = 'products.name LIKE ' . $db->quote("%" . $keywords . "%") . ' OR ' . 
                'products.keywords LIKE ' . $db->quote("%" . $keywords . "%");
            
            foreach ($keysArr as $keyval) {
                $keyStr .= " OR products.name LIKE " . $db->quote("%" . $keyval . "%") . 
                    " OR products.keywords LIKE " . $db->quote("%" . $keyval . "%");
            }    
        }
        
        if ($tmpDeviceId == NULL) {
            $select = $this->select()
                    ->distinct()
                    ->from('products', array('products.id'))
                    ->where($keyStr)
                    ->where('products.deleted <> 1')
                    ->where('products.inapp IS NULL')
                    ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                    ->where("products.status = 'APPROVED'");
        } else {
            $filter                 = array();
            $filter['where']        = array($keyStr);
            $filter['where'][]      = 'products.deleted <> 1';
            $filter['where'][]      = 'products.inapp IS NULL';
            $filter['where'][]      = 'products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)';
            $filter['where'][]      = "products.status = 'APPROVED'";
            
            $deviceModel        = new Model_ProductDevices();
            $select             = $deviceModel->getProducts($tmpDeviceId, $filter);
            
           
        }
         
        return $select;
    }
    
    
    
    
	public function getSearchQueryChap($keywords, $tmpDeviceId, $simpleSearch = false, $chapId = null, $limit = null, $dataSet = null ) {
               
        $db         = Zend_Registry::get('db');
        $keysArr    = explode(' ', $keywords);

        if ($simpleSearch) {
            $keyStr     = 'products.name LIKE ' . $db->quote("%" . $keywords . "%");
        } else {
            $keyStr     = 'products.name LIKE ' . $db->quote("%" . $keywords . "%") . ' OR ' . 
                'products.keywords LIKE ' . $db->quote("%" . $keywords . "%") .' OR ' .
                'product_meta.meta_value LIKE '. $db->quote("%" . $keywords . "%") .' OR ' .
                'user_meta.meta_value LIKE '. $db->quote("%" . $keywords . "%")
            ;
            
            foreach ($keysArr as $keyval) {
                $keyStr .= " OR products.name LIKE " . $db->quote("%" . $keyval . "%") . 
                    " OR products.keywords LIKE " . $db->quote("%" . $keyval . "%").
                    " OR product_meta.meta_value LIKE " . $db->quote("%" . $keyval . "%").
                    " OR user_meta.meta_value LIKE " . $db->quote("%" . $keyval . "%")
                ;
            }    
        }

        if($chapId){

        	if ($tmpDeviceId == NULL) {
        		
        		if($limit)	{
        		
	            $select = $this->select()
	                    ->distinct()
	                    ->from('products', array('products.id as product_id', 'products.name'))
	                    ->join(array('cp' => 'chap_products'), 'cp.product_id = products.id', array())  
	                    ->join(array('' => 'product_meta'), 'products.id = product_meta.product_id', array())
	                    ->join(array('' => 'user_meta'), 'products.user_id = user_meta.user_id', array())
	                    ->where($keyStr)
	                    ->where('products.deleted <> 1')
	                    ->where('cp.chap_id = ?',$chapId)
	                    ->where('products.inapp IS NULL')
	                    ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
	                    ->where("products.status = 'APPROVED'")
	                    ->where("product_meta.meta_name = 'FULL_DESCRIPTION'")
	                    ->where("user_meta.meta_name = 'COMPANY_NAME'")
                        ->order('products.name ASC')
	                    ->limit($limit);

        		} else {
        			
        			  $select = $this->select()
	                    ->distinct()
	                    ->from('products', array('products.id as product_id', 'products.name'))
	                    ->join(array('cp' => 'chap_products'), 'cp.product_id = products.id', array())
                        ->join(array('' => 'product_meta'), 'products.id = product_meta.product_id', array())
                        ->join(array('' => 'user_meta'), 'products.user_id = user_meta.user_id', array())
	                    ->where($keyStr)
	                    ->where('products.deleted <> 1')
	                    ->where('cp.chap_id = ?',$chapId)
	                    ->where('products.inapp IS NULL')
	                    ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
	                    ->where("products.status = 'APPROVED'")
                        ->where('product_meta.meta_name','FULL_DESCRIPTION')
                        ->where("user_meta.meta_name = 'COMPANY_NAME'")
                        ->order('products.name ASC');
        		}

	        } else {
	            $filter                 = array();
	            $filter['where']        = array($keyStr);
	            $filter['where'][]      = 'products.deleted <> 1';
	            $filter['where'][]      = 'products.inapp IS NULL';
	            $filter['where'][]      = 'cp.chap_id ='.$chapId;
	            $filter['where'][]      = 'products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)';
	            $filter['where'][]      = "products.status = 'APPROVED'";
	            $filter['where'][]      = "product_meta.meta_name = 'FULL_DESCRIPTION'";
	            $filter['where'][]      = "user_meta.meta_name = 'COMPANY_NAME'";

                $filter['join']         = array(
                                            'product_meta' => 'products.id = product_meta.product_id',
                                            'user_meta' => 'products.user_id = user_meta.user_id'
                                        );

                $filter['order']        = 'products.name ASC';

	            if($limit) 
	            	 $filter['limit'][]      = $limit;

	            $deviceModel        = new Model_ProductDevices();
	         
	            $select             = $deviceModel->getProductsChap($tmpDeviceId, $filter);
        	}
        }

        if($dataSet)
            return $this->fetchAll($select);
        else 
            return $select;

    }

    /*
     * This function will check and return search query for non english chap sites
     */
    public function getSearchQueryChapByLangId($keywords, $tmpDeviceId, $simpleSearch = false, $chapId = null, $chapLangId = null, $limit = null, $dataSet = null ) {
               
        $db         = Zend_Registry::get('db');
        $keysArr    = explode(' ', $keywords);

        if ($simpleSearch) {
            $keyStr     = 'product_language_meta.meta_value LIKE ' . $db->quote("%" . $keywords . "%");
        } else {
            $keyStr     = 'product_language_meta.meta_value LIKE ' . $db->quote("%" . $keywords . "%") . ' OR ' .
                'products.keywords LIKE ' . $db->quote("%" . $keywords . "%") . ' OR ' .
                'product_meta.meta_value LIKE '. $db->quote("%" . $keywords . "%") .' OR ' .
                'user_meta.meta_value LIKE '. $db->quote("%" . $keywords . "%")
            ;
            
            foreach ($keysArr as $keyval) {
                $keyStr .= " OR product_language_meta.meta_value LIKE " . $db->quote("%" . $keyval . "%") .
                    " OR products.keywords LIKE " . $db->quote("%" . $keyval . "%").
                    " OR product_meta.meta_value LIKE " . $db->quote("%" . $keyval . "%").
                    " OR user_meta.meta_value LIKE " . $db->quote("%" . $keyval . "%")
                ;
            }    
        }

        if($chapId){

        	if ($tmpDeviceId == NULL) {
        		
        		if($limit)	{
        		
	            $select = $this->select()
                        ->setIntegrityCheck(false)
	                    ->distinct()
	                    ->from('products', array('products.id as product_id'))
	                    ->join(array('cp' => 'chap_products'), 'cp.product_id = products.id', array())  
                        ->join(array('' => 'product_language_meta'), 'cp.product_id = plm.product_id', array('meta_value as name','meta_name'))
                        ->join(array('' => 'product_meta'), 'products.id = product_meta.product_id', array())
                        ->join(array('' => 'user_meta'), 'products.user_id = user_meta.user_id', array())
                        ->where('product_language_meta.meta_name = ?','PRODUCT_NAME')
                        ->where('product_language_meta.language_id = ?',$chapLangId)
	                    ->where($keyStr)
	                    ->where('products.deleted <> 1')
	                    ->where('cp.chap_id = ?',$chapId)
	                    ->where('products.inapp IS NULL')
	                    ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
	                    ->where("products.status = 'APPROVED'")
                        ->where("product_meta.meta_name = 'FULL_DESCRIPTION'")
                        ->where("user_meta.meta_name = 'COMPANY_NAME'")
                        ->order('products.name ASC')
	                    ->limit($limit);
	                    
        		} else {
        			
        		$select = $this->select()
                            ->setIntegrityCheck(false)
	                    ->distinct()
	                    ->from('products', array('products.id as product_id', 'products.name'))
	                    ->join(array('cp' => 'chap_products'), 'cp.product_id = products.id', array())
                        ->join(array('' => 'product_language_meta'), 'cp.product_id = plm.product_id', array('meta_value as name','meta_name'))
                        ->join(array('' => 'product_meta'), 'products.id = product_meta.product_id', array())
                        ->join(array('' => 'user_meta'), 'products.user_id = user_meta.user_id', array())
                        ->where('product_language_meta.meta_name = ?','PRODUCT_NAME')
                        ->where('product_language_meta.language_id = ?',$chapLangId)
	                    ->where($keyStr)
	                    ->where('products.deleted <> 1')
	                    ->where('cp.chap_id = ?',$chapId)
	                    ->where('products.inapp IS NULL')
	                    ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
	                    ->where("products.status = 'APPROVED'")
                        ->where("product_meta.meta_name = 'FULL_DESCRIPTION'")
                        ->where("user_meta.meta_name = 'COMPANY_NAME'")
                        ->order('products.name ASC')
                        ;
        		}

	        } else {


	            $filter                 = array();
                $filter['join']         = array(
                                                'product_meta' => 'products.id = product_meta.product_id',
                                                'user_meta' => 'products.user_id = user_meta.user_id',
                                                'product_language_meta'   => 'product_language_meta.product_id = products.id'
                                            );
	            $filter['where']        = array($keyStr);
	            $filter['where'][]      = 'products.deleted <> 1';
	            $filter['where'][]      = 'products.inapp IS NULL';
	            $filter['where'][]      = 'cp.chap_id ='.$chapId;
	            $filter['where'][]      = 'products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)';
	            $filter['where'][]      = "products.status = 'APPROVED'";
                $filter['where'][]      = "product_meta.meta_name = 'FULL_DESCRIPTION'";
                $filter['where'][]      = "user_meta.meta_name = 'COMPANY_NAME'";



                $filter['order']        = 'products.name ASC';

	            if($limit) 
	            	 $filter['limit'][]      = $limit;

	            $deviceModel        = new Model_ProductDevices();
	            $select             = $deviceModel->getProductsChap($tmpDeviceId, $filter);

        	}
        }

        if($dataSet)
            return $this->fetchAll($select);
        else 
            return $select;

    }
    
    public function getSearchQueryPartner($keywords, $simpleSearch = false, $chapId = null, $limit = null, $dataSet = null ) {

        $db         = Zend_Registry::get('db');

        if($chapId){
              
            $sql1 = $this->select()
                        ->distinct()
                        ->from('products', array('products.id as product_id', 'products.name'))
                        ->join(array('cp' => 'chap_products'), 'cp.product_id = products.id', array())
                        ->where('products.name LIKE ?',"%$keywords%")
                        ->where('products.deleted <> 1')
                        ->where('cp.chap_id = ?',$chapId)
                        ->where('products.inapp IS NULL')
                        ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                        ->where("products.status = 'APPROVED'")
                        ->order('products.id DESC');
                        //->limit(10);

            $sql2 = $this->select()
                        ->distinct()
                        ->from('products', array('products.id as product_id', 'products.name'))
                        ->join(array('cp' => 'chap_products'), 'cp.product_id = products.id', array())
                        ->where('products.keywords LIKE ?',"%$keywords%")
                        ->where('products.deleted <> 1')
                        ->where('cp.chap_id = ?',$chapId)
                        ->where('products.inapp IS NULL')
                        ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                        ->where("products.status = 'APPROVED'")
                        ->order('products.id DESC');
                        //->limit(5);

            $joinsql = $this->select()->union(array("($sql1)", "($sql2)"));

                            if($limit)
                            {
                                $joinsql->limit($limit);
                            }
                            

                            
            return $this->fetchAll($joinsql);
           
            //
        }
    }
    
    //This function will search and return product details by given keyword. This will only used for non english chapters 
     public function getSearchQueryPartnerProductLangMeta($keywords, $simpleSearch = false, $chapId = null, $limit = null, $dataSet = null, $chapLangId = null ) {
        $db         = Zend_Registry::get('db');
        if($chapId){
            $sql = $this->select()
                        ->setIntegrityCheck(false)
                        ->distinct()
                        ->from('products', array('products.id as product_id', 'products.name'))
                        ->join(array('cp' => 'chap_products'), 'cp.product_id = products.id', array())
                        ->join(array('plm' => 'product_language_meta'), 'cp.product_id = plm.product_id', array('meta_value as name','meta_name'))
                        ->where('plm.meta_name = ?','PRODUCT_NAME')
                        ->where('plm.language_id = ?',$chapLangId)
                        ->where('plm.meta_value LIKE ?',"%$keywords%")
                        ->where('products.deleted <> 1')
                        ->where('cp.chap_id = ?',$chapId)
                        ->where('products.inapp IS NULL')
                        ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                        ->where("products.status = 'APPROVED'");

            if($limit)
            {
                $sql->limit($limit);
            }
            //echo $sql->assemble(); die();
            return $this->fetchAll($sql);
            //return $joinsql->assemble();
        }
    }
    
    // testing function
    /*public function getSearchQueryPartnerByAllLang($keywords, $simpleSearch = false, $chapId = null, $limit = null, $dataSet = null, $chapLangId = null ) {

        $db         = Zend_Registry::get('db');

        if($chapId){
            $metaName = "'PRODUCT_NAME'";
            $sql1 = $this->select()
                        ->setIntegrityCheck(false)
                        ->distinct()
                        ->from('products', array('products.id as product_id', 'products.name'))
                        ->join(array('cp' => 'chap_products'), 'cp.product_id = products.id', array())
                        ->joinLeft(array('plm' => 'product_language_meta'), 'cp.product_id = plm.product_id AND plm.meta_name = '.$metaName.' AND plm.language_id='.$chapLangId.' OR plm.language_id=1', array('meta_value as name','meta_name'))
                        //->where('plm.meta_name = ?','PRODUCT_NAME')
                        ->where('products.name LIKE ?',"%$keywords%")
                        ->orWhere('plm.meta_name LIKE ?',"%$keywords%")
                        ->where('products.deleted <> 1')
                        ->where('cp.chap_id = ?',$chapId)
                        ->where('products.inapp IS NULL')
                        ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                        ->where("products.status = 'APPROVED'");
                        //->order('products.name ASC');
                        //->limit(10);

            $sql2 = $this->select()
                        ->setIntegrityCheck(false)
                        ->distinct()
                        ->from('products', array('products.id as product_id', 'products.name'))
                        ->join(array('cp' => 'chap_products'), 'cp.product_id = products.id', array())
                        ->joinLeft(array('plm' => 'product_language_meta'), 'cp.product_id = plm.product_id AND plm.meta_name = '.$metaName.' AND plm.language_id='.$chapLangId.' OR plm.language_id=1', array('meta_value as name','meta_name'))
                        //->where('plm.meta_name = ?','PRODUCT_NAME')
                        ->where('products.keywords LIKE ?',"%$keywords%")
                        ->orWhere('plm.meta_value LIKE ?',"%$keywords%")
                        ->where('products.deleted <> 1')
                        ->where('cp.chap_id = ?',$chapId)
                        ->where('products.inapp IS NULL')
                        ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                        ->where("products.status = 'APPROVED'");
                        //->limit(5);

            $joinsql = $this->select()->union(array("($sql1)", "($sql2)"));

                            if($limit)
                            {
                                $joinsql->limit($limit);
                            }
                            
            //echo $joinsql->assemble(); die();
            return $this->fetchAll($sql2);
            //return $joinsql->assemble();
        }
    }*/

    public function getSearchQueryPartnerWithDevice($keywords,$deviceId, $simpleSearch = false, $chapId = null, $limit = null, $dataSet = null)
    {
        $db         = Zend_Registry::get('db');
        if($chapId){

            $deviceAttrib = $this->getDeviceAttributes($deviceId);
            $sql1 = $this   ->select()
                            ->distinct()
                            ->setIntegrityCheck(false)
                            ->from('products', array('products.id as product_id', 'products.name'))
                            ->join(array('cp' => 'chap_products'),'cp.product_id = products.id', array())
                            ->join(array('pb' => 'product_builds'),'pb.product_id = products.id', array())
                            ->join(array('pdsa' => 'product_device_saved_attributes'),'pdsa.build_id = pb.id',array())
                            ->join(array('da' => 'device_attributes'),'da.value = pdsa.value',array())
                            ->join(array('d' => 'devices'),'d.id = da.device_id ',array())
                            ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                            ->where('cp.chap_id = ?',$chapId)
                            ->where('bd.device_id IN (?)',$deviceId)
                            ->where('products.status = ?','APPROVED')
                            ->where('products.deleted != ?',1)
                            ->where('pb.device_selection_type != ?','CUSTOM')
                            ->where('products.name LIKE ?',"%$keywords%")
                            ->where('products.inapp IS NULL')
                            ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                            ;

            $sql2 = $this   ->select()
                            ->distinct()
                            ->setIntegrityCheck(false)
                            ->from('products', array('products.id as product_id', 'products.name'))
                            ->join(array('cp' => 'chap_products'),'cp.product_id = products.id', array())
                            ->join(array('pb' => 'product_builds'), 'pb.product_id = products.id', array())
                            ->join(array('pdsa' => 'product_device_saved_attributes'), 'pdsa.build_id = pb.id', array())
                            ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pdsa.device_attribute_definition_id AND da.value = pdsa.value', array())
                            ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                            ->where('cp.chap_id = ?',$chapId)
                            ->where('d.id IN (?)',$deviceId)
                            ->where('products.status = ?','APPROVED')
                            ->where('products.deleted != ?',1)
                            ->where('pb.device_selection_type != ?', 'CUSTOM')
                            ->where('products.name LIKE ?',"%$keywords%")
                            ->where('products.inapp IS NULL')
                            ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                            ->where('IF(pb.platform_id = 0, 1 = 1, pdsa.value = ?)', $deviceAttrib[1])
                            ;


            $sql3 = $this   ->select()
                            ->distinct()
                            ->setIntegrityCheck(false)
                            ->from('products', array('products.id as product_id', 'products.name'))
                            ->join(array('cp' => 'chap_products'),'cp.product_id = products.id', array())
                            ->join(array('pb' => 'product_builds'),'pb.product_id = products.id', array())
                            ->join(array('pdsa' => 'product_device_saved_attributes'),'pdsa.build_id = pb.id',array())
                            ->join(array('da' => 'device_attributes'),'da.value = pdsa.value',array())
                            ->join(array('d' => 'devices'),'d.id = da.device_id ',array())
                            ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                            ->where('cp.chap_id = ?',$chapId)
                            ->where('bd.device_id IN (?)',$deviceId)
                            ->where('products.status = ?','APPROVED')
                            ->where('products.deleted != ?',1)
                            ->where('pb.device_selection_type != ?','CUSTOM')
                            ->where('products.keywords LIKE ?',"%$keywords%")
                            ->where('products.inapp IS NULL')
                            ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                            ;

            $sql4 = $this   ->select()
                            ->distinct()
                            ->setIntegrityCheck(false)
                            ->from('products', array('products.id as product_id', 'products.name'))
                            ->join(array('cp' => 'chap_products'),'cp.product_id = products.id', array())
                            ->join(array('pb' => 'product_builds'), 'pb.product_id = products.id', array())
                            ->join(array('pdsa' => 'product_device_saved_attributes'), 'pdsa.build_id = pb.id', array())
                            ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pdsa.device_attribute_definition_id AND da.value = pdsa.value', array())
                            ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                            ->where('cp.chap_id = ?',$chapId)
                            ->where('d.id IN (?)',$deviceId)
                            ->where('products.status = ?','APPROVED')
                            ->where('products.deleted != ?',1)
                            ->where('pb.device_selection_type != ?', 'CUSTOM')
                            ->where('products.keywords LIKE ?',"%$keywords%")
                            ->where('products.inapp IS NULL')
                            ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                            ->where('IF(pb.platform_id = 0, 1 = 1, pdsa.value = ?)', $deviceAttrib[1])
                            ;
            $joinsql = $this->select()->union(array("($sql1)", "($sql2)", "($sql3)","($sql4)"));
            if($limit)
            {
                $joinsql->limit($limit);
            }
            return $this->fetchAll($joinsql);
            //return $joinsql->assemble();
        }
    }

    //Return producti details by chapter language and device id
    public function getSearchQueryPartnerWithDeviceProductLangMeta($keywords,$deviceId, $simpleSearch = false, $chapId = null, $limit = null, $dataSet = null, $chapLangId = null)
    {
        
        $keywords =  strtolower($keywords);
        $db         = Zend_Registry::get('db');
        if($chapId){

            $deviceAttrib = $this->getDeviceAttributes($deviceId);
            $sql1 = $this   ->select()
                            ->distinct()
                            ->setIntegrityCheck(false)
                            ->from('products', array('products.id as product_id', 'products.name'))
                            ->join(array('cp' => 'chap_products'),'cp.product_id = products.id', array())
                            ->join(array('pb' => 'product_builds'),'pb.product_id = products.id', array())
                            ->join(array('pdsa' => 'product_device_saved_attributes'),'pdsa.build_id = pb.id',array())
                            ->join(array('da' => 'device_attributes'),'da.value = pdsa.value',array())
                            ->join(array('d' => 'devices'),'d.id = da.device_id ',array())
                            ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id', array())
                            ->join(array('plm' => 'product_language_meta'), 'cp.product_id = plm.product_id', array('meta_value as name','meta_name'))
                            ->where('plm.meta_name = ?','PRODUCT_NAME')
                            ->where('plm.language_id = ?',$chapLangId)
                            ->where->expression('LOWER(plm.meta_value) LIKE ?',"%$keywords%")
                            ->where('cp.chap_id = ?',$chapId)
                            ->where('bd.device_id IN (?)',$deviceId)
                            ->where('products.status = ?','APPROVED')
                            ->where('products.deleted != ?',1)
                            ->where('pb.device_selection_type != ?','CUSTOM') 
                            ->where('products.inapp IS NULL')
                            ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                            ;

            $sql2 = $this   ->select()
                            ->distinct()
                            ->setIntegrityCheck(false)
                            ->from('products', array('products.id as product_id', 'products.name'))
                            ->join(array('cp' => 'chap_products'),'cp.product_id = products.id', array())
                            ->join(array('pb' => 'product_builds'), 'pb.product_id = products.id', array())
                            ->join(array('pdsa' => 'product_device_saved_attributes'), 'pdsa.build_id = pb.id', array())
                            ->join(array('da' => 'device_attributes'), 'da.device_attribute_definition_id = pdsa.device_attribute_definition_id AND da.value = pdsa.value', array())
                            ->join(array('d' => 'devices'), 'd.id = da.device_id', array())
                            ->join(array('plm' => 'product_language_meta'), 'cp.product_id = plm.product_id', array('meta_value as name','meta_name'))
                            ->where('plm.meta_name = ?','PRODUCT_NAME')
                            ->where('plm.language_id = ?',$chapLangId)
                            ->where->expression('LOWER(plm.meta_value) LIKE ?',"%$keywords%")
                            ->where('cp.chap_id = ?',$chapId)
                            ->where('d.id IN (?)',$deviceId)
                            ->where('products.status = ?','APPROVED')
                            ->where('products.deleted != ?',1)
                            ->where('pb.device_selection_type != ?', 'CUSTOM')
                            ->where('products.inapp IS NULL')
                            ->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                            ->where('IF(pb.platform_id = 0, 1 = 1, pdsa.value = ?)', $deviceAttrib[1])
                            ;

            $joinsql = $this->select()->union(array("($sql1)", "($sql2)"));
            if($limit)
            {
                $joinsql->limit($limit);
            }
            return $this->fetchAll($joinsql);
            //return $joinsql->assemble();
        }
    }
    
    /**
     * Returns the attibutes of a particular device
     * @param $deviceId
     * @return device attributes array
     */
    protected function getDeviceAttributes($deviceId)
    {
        $deviceModel        = new Model_Device();
        $deviceAttributes   = $deviceModel->getDeviceAttributes($deviceId);
        $deviceAttrib       = array();

        foreach ($deviceAttributes as $deviceAttribute)
        {
            $deviceAttrib[$deviceAttribute->device_attribute_definition_id] = $deviceAttribute->value;
        }

        return $deviceAttrib;
    }


	/**
	 * 
	 * check if the file is there in the s3 server
	 * @param $fileName file name of the build file
	 * @return bool true if the file exist
	 * @author chathura
	 */
	public function s3FileExist($fileName) {

	    $config = Zend_Registry::get('config');
        $awsKey = $config->aws->s3->publickey;
        $awsSecretKey = $config->aws->s3->secretkey;
        $bucketName = $config->aws->s3->bucketname;
        $defaultS3Url = $config->aws->s3->defaulturl;
        


        $s3 = new Zend_Service_Amazon_S3($awsKey, $awsSecretKey);
    
        $object = $bucketName . '/productfile/' . $fileName;
        
        $fileExist = $s3->isObjectAvailable ( $object );
         
        return $fileExist;

		/* 
        this is for referances
			
		// get the list of buckets
		Zend_Debug::dump ( $s3->getBuckets(), 'list of buckets' );
		
   
		// get the file info
		Zend_Debug::dump ( $s3->getInfo ( 'production.applications.nexva.com/productfile/2424/CIGNASCARota.apk' ), 'file info' );
		

		$object = $bucketName . '/productfile/' . $fileName;

		// check if the file is available
		$fileExist = $s3->isObjectAvailable ( 'production.applications.nexva.com/productfile/1040/SonyEricsson_T610_T630_Z600_T616_T618/CandyCrusher.jar' );
		
		die();
		*/
	
	}
	
	/**
	 * 
	 * get the file info of the given file name
	 * @param $fileName file name of the build file
	 * @return array file info 
	 * @author chathura
	 */
	public function getS3Fileinfo($fileName) {
		
		$config = Zend_Registry::get ( 'config' );
		$awsKey = $config->aws->s3->publickey;
		$awsSecretKey = $config->aws->s3->secretkey;
		$bucketName = $config->aws->s3->bucketname;
		$defaultS3Url = $config->aws->s3->defaulturl;
		
		$s3 = new Zend_Service_Amazon_S3 ( $awsKey, $awsSecretKey );
		//Zend_Debug::dump ( $s3->getBuckets (), 'list of buckets' );
		$object = $bucketName . '/productfile/' . $fileName;
		
		$fileInfo = $s3->getInfo ( $object );
		
		return $fileInfo ["size"];
	
	}
	
	/**
	 * Download product, will add to statics and chane the files headers on S3 and will change the ACL
	 * @param <type> $productId
	 * @return <type>
	 */
	public function getProductContentType($productId, $setlanguageId = false) {
		
		if($setlanguageId)	
			$productInfo = $this->getProductDetailsById($productId, false,  null, $setlanguageId );
		else
			$productInfo = $this->getProductDetailsById($productId );
		
		return $productInfo ['content_type'];
	
	}
	
	/**
	 * This method sets the app filter rules for a given chap
	 * @param $rules
	 */
	public function setAppFilterRules($chapId = null) {
	    $appRules  = new Model_ProductUserRule();
	    $rules     = $appRules->getRules($chapId);
	    $this->appRules    = $rules;
	}
 
        
        
        /**
	 * This method get the products for given search key
	 * @param $searchKey
	 */
        public function  getProdcutDetailsbySearch($searchKey)
        {
            $searchSql   = $this->select(); 
            $searchSql->from(array('p' => $this->_name), array('*'))                   
                    ->setIntegrityCheck(false)  
                    ->join(array('pl' => 'platforms'), 'p.platform_id = pl.id', array('pl.name as platform'))
                    ->where('p.user_id = ?',Zend_Auth::getInstance()->getIdentity()->id)
                    ->where('p.deleted != ?', 1)
                    ->where('p.status = ?','APPROVED')
                    ->where('p.name LIKE ?', '%'.$searchKey.'%')                    
                    ->order('p.name DESC'); 
        
            //Zend_Debug::dump($searchSql->__toString());die();
            return $this->fetchAll($searchSql);
        }
        
        
        public function getProductsbyPlatform($searchKey,$platformId)
        {   
            $productSql   = $this->select(); 
            $productSql->from(array('p' => $this->_name), array('p.name','p.id as product_id'))                   
                    ->setIntegrityCheck(false)  
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array())
                    ->where('pb.platform_id = ?',$platformId)
                    ->where('p.deleted != ?', 1)
                    ->where('p.status = ?','APPROVED')
                    ->where('p.name LIKE ?', '%'.$searchKey.'%')  
                    ->limit(10)
                    ->order('p.name DESC'); 
        
            //Zend_Debug::dump($productSql->__toString());die();
            return $this->fetchAll($productSql);
            
        }
        
   /**
     * Returns a list of products by a cp.
     * 
     * @param int $cpId
     * @param int $limit Limits number of products to be returned.
     * @return array
     * Chathura 
     * 
     */
        
    public function getProductsListByCp($cpId, $limit=null) {

    	$select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from('products')
                    ->where('products.user_id =  ' . $cpId)
                    ->where("products.status = 'APPROVED'")
                    ->where('products.deleted <> 1')
                    ->where('products.inapp IS NULL')
                    ->order('products.id DESC')
                    ->limit($limit)
                    ->query()
                    ->fetchAll();

            return $select;
    }
    
    
    public function getProductsListByCpWithDownloadAndViewCount($cpId, $firstDayThisMonth = null, $lastDayThisMonth = null) {
    	
    	if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01'); 
		if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

    	$select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from('products', array( 'id',
                                              'name', 
                                              'views' => new Zend_Db_Expr("(SELECT count(statistics_products.id) FROM statistics_products WHERE statistics_products.product_id = products.id and date between '$firstDayThisMonth' and '$lastDayThisMonth' )"),
                                              'downloads' =>  new Zend_Db_Expr("( SELECT count(statistics_downloads.id) FROM statistics_downloads WHERE statistics_downloads.product_id = products.id and date between '$firstDayThisMonth' and '$lastDayThisMonth')")                    
                                             )
                    )
                    ->where('products.user_id =  ' . $cpId)
                    ->where("products.status = 'APPROVED'")
                    ->where('products.deleted <> 1')
                    ->where('products.inapp IS NULL')
                    ->order('products.id DESC')
                    ->query()
                    ->fetchAll();

            return $select;
    }

    /**
     * @param $numOfApps
     * @return array
     */
    public function getAndroidApps($numOfApps)
    {
        $session = new Zend_Session_Namespace("devices");
        $devieArray = $session->selectedDevices;
        $deviceIds = array();
        //$deviceId;
        if(!empty($devieArray))
        {
            foreach($devieArray as $key => $value)
            {
                $deviceIds[] =  $key;
            }
            $deviceId = join(',',$deviceIds);
        }

        //Zend_Debug::dump($deviceId);die();

        //0 for all platforms & 12 for android
        $platforms = array(0,12);

        $sql   = $this->select();
        $sql->from(array('p' => $this->_name), array('p.name AS product_name','p.*'))
            ->setIntegrityCheck(false)
            ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id')
            ;
            if(isset($deviceId))
            {
                $sql->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id')
                    ->where('bd.device_id IN('.$deviceId.')');
            }
        $sql->where('p.status = ?','APPROVED')
            ->where('p.deleted != ?',1)
            ->where('pb.platform_id = ?', 12)
            ->group('p.id')
            ->order('p.id DESC')
            ->limit($numOfApps);
        //Zend_Debug::dump($sql->assemble());die();
        //return $this->fetchAll($sql);
        $products = $this->fetchAll($sql)->toArray();
        $i=0;
        foreach($products as $product)
        {
            $products[$i][] = $this->getProductDetailsById($product['product_id']);
            $i++;
        }
        return $products;
    }

    /**
     * @param $deviceID
     * @return array
     * developed for mobile site, but not in use
     */
    public function getAndroidAppsByDeviceId($deviceID)
    {
        $sql =  $this->select();
        $sql    ->from(array('p'=>$this->_name),array('p.*'))
                ->setIntegrityCheck(false)
                ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id')
                ->join(array('bd' => 'build_devices'), 'bd.build_id = pb.id')
                ->where('bd.device_id =?',$deviceID)
                ->where('p.status = ?','APPROVED')
                ->where('p.deleted != ?',1)
                //->where('pb.device_selection_type = ?', 'CUSTOM')
                ->where('p.platform_id = ?',12)
                ->group('p.id')
                ->order('bd.id DESC');
        $products = $this->fetchAll($sql)->toArray();
        $i=0;
        foreach($products as $product)
        {
            $products[$i][] = $this->getProductDetailsById($product['product_id']);
            $i++;
        }
        return $products;
    }
    
    //return build details for single app and platform
    public function getBuildDetailsByProductIdAndLang($productId, $chapId, $chapLangId){
        $buildSql   = $this->select(); 
        $buildSql   ->from(array('p' => 'products'), array('p.id as product_id'))                   
                    ->setIntegrityCheck(false)  
                    ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.id as build_id'))
                    ->where('p.id = ?',$productId)
                    //->where('pb.language_id = ?',$chapLangId)
                    ;
               
         $builds =  $this->fetchAll($buildSql);
         return $builds;
    }


    //this function contain the logic for the apps weather they contain multiple platform builds or android platform build
    public function checkAppPlatforms($supportedPlatforms, $appId, $chapterPlatformType)
    {
        /*
         * var $chapterPlatformType is - ANDROID_PLATFORM_CHAP_ONLY or MULTIPLE_PLATFORM_CHAP_ONLY or ANDROID_AND_MULTIPLE_PLATFORM_CHAP
         */
        
        $appPlatformType = '';

        //Get platform type for the appId. If app has more than 1 platform it's MULTIPLE_PLATFORM
        $countAppPlatforms = count($supportedPlatforms);
        
        if($countAppPlatforms > 1):
            //check if android exist
            if($this->checkIfExistAndroidPlatform($supportedPlatforms, 12)):
                $appPlatformType = 'ANDROID_AND_MULTIPLE_PLATFORM_APP';
            else:
                $appPlatformType = 'NON_ANDROID_MULTIPLE_PLATFORM_APP';
            endif;

        elseif($countAppPlatforms == 1):
            //check if android exist
            if($this->checkIfExistAndroidPlatform($supportedPlatforms, 12)):
                $appPlatformType = 'ANDROID_PLATFORM_APP';
            else:
                $appPlatformType = 'NON_ANDROID_PLATFORM_APP';
            endif;
        endif;
        
        //If the app is containing mulltiple platform and chapter platform is multiple return true
        //MTN and MTN Congo
        if(($chapterPlatformType == 'ANDROID_AND_MULTIPLE_PLATFORM_CHAP') && ($appPlatformType == 'ANDROID_AND_MULTIPLE_PLATFORM_APP')):
            return 'MULTIPLE_AND_ANDROID';
        //CCW
        elseif(($chapterPlatformType == 'ANDROID_PLATFORM_CHAP_ONLY') && $this->checkPlatformArrayValue($supportedPlatforms, 12)):
            return 'ANDROID_ONLY';
        //App-Etite
        elseif(($chapterPlatformType == 'MULTIPLE_PLATFORM_CHAP_ONLY')):
            return 'MULTIPLE_ONLY';
        //ALL
        else:
            return 'MULTIPLE_AND_ANDROID';
        endif;
        
    }
    

    public function verifyPlatformType($supportedPlatforms, $prodId)
    {

        $appPlatformType = '';

        //Get platform type for the appId. If app has more than 1 platform it's MULTIPLE_PLATFORM
        $countAppPlatforms = count($supportedPlatforms);
        
        if($countAppPlatforms > 1):
            //check if android exist
            if($this->checkPlatformArrayValue($supportedPlatforms, 12)):
                $appPlatformType = 'ANDROID_AND_MULTIPLE_PLATFORM_APP';
            else:
                $appPlatformType = 'NON_ANDROID_PLATFORM_APP';
            endif;

        elseif($countAppPlatforms == 1):
            //check if android exist
            if($this->checkPlatformArrayValue($supportedPlatforms, 12)):
                $appPlatformType = 'ANDROID_PLATFORM_APP';
            else:
                $appPlatformType = 'NON_ANDROID_PLATFORM_APP';
            endif;
        endif;
        
        return $appPlatformType;
    }
    
    public function checkPlatformArrayValue($supportedPlatforms, $platformId)
    {
       //Get the build type
       $modelProductBuild = new Model_ProductBuild();

       foreach($supportedPlatforms as $key => $supportedPlatform)
       {
           //Check the build type wheather it's file type is files.
           $buildType = $modelProductBuild->getBuildContentType($supportedPlatform->build_id);
           
           if ( $supportedPlatform->platform_id === $platformId && $buildType == 'files' )
           {
              return $supportedPlatform->platform_id;
           }
       }
       
       return false;
    }
    
    
     public function checkIfExistAndroidPlatform($supportedPlatforms, $platformId)
    {
       //Get the build type
       $modelProductBuild = new Model_ProductBuild();

       foreach($supportedPlatforms as $key => $supportedPlatform)
       {
           //Check the build type wheather it's file type is files.
           $buildType = $modelProductBuild->getBuildContentType($supportedPlatform->build_id);
           
           if ( $supportedPlatform->platform_id === $platformId)
           {
              return $supportedPlatform->platform_id;
           }
       }
       
       return false;
    }
    
    public function setUpdateDate($id) {
    
        $data = array('updated_date' => date('Y-m-d'));
        $this->update($data, array('id = ?' => $id));
    }
    
    public function getProductStatusById($productId){
        $Sql   = $this->select();
        $Sql   ->from(array('p' => 'products'), array('p.status as status'))
        ->where('p.id = ?',$productId);
         
        $status =  $this->fetchRow($Sql);
        return $status->status;
    }
}