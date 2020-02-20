<?php
error_reporting(0);
class Nexva_Api_NexApi {

    protected $serverPathThumb = "http://thor.nexva.com/vendors/phpThumb/phpThumb.php?src=";
    protected $serverPath = "http://thor.nexva.com/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/";

    protected $__serverS3Path  = '';


    function __construct() {
    	
            //Un comment this if s3 images are enable    	
    	    // $this->__serverS3Path = 'http://'.Zend_Registry::get('config')->aws->s3->bucketname . '.s3.amazonaws.com';
    }
    
    /**
     * 
     * Returns the set of applications based on Device and Chap with a page limit   
     * 
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param $chapId - Chap ID
     * @param $deviceId - Device Id 
     * @param $category - App Category
     * @param $limit - App limit (Optional)
     * @param $offset (Optional)
     * returns Apps array
     */
    public function allAppsByChapAction($chapId, $deviceId, $category, $limit = 10, $offset = 0) 
    {
        //Instantiate the Api_Model_ChapProducts model
        $chapProductModel = new Api_Model_ChapProducts();
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'allAppsByChapAction'.$chapId.$deviceId.$category.$limit.$offset;
        if (($products = $cache->get($key)) !== false)
        {
        
            return $products;
        
        }
        
        //get the app details
        $products = $chapProductModel->getChapProductsAll($chapId, $deviceId, $category, $limit, $offset);

        $apiCall = true;

        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId, $apiCall, '', '' , '', $chapId);
        }
        
        $cache->set($products, $key, 3600);

        return $products;
    }

    /**
     * 
     * Returns the details of a particular app  
     * @param $appId App ID
     * returns app details
     */
    public function detailsAppAction($appId, $deviceId = null, $chapId = null, $chapLangId = null) {
        
        
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'detailsAppAction'.$appId.$deviceId.$chapId.$chapLangId;
        if (($appDetails = $cache->get($key)) !== false)
        {
        
        	return $appDetails;
        
        }
        
        
        $productModel = new Model_Product();
        
        
        $appDetails = $productModel->getProductDetailsById($appId,FALSE,$chapLangId,NULL,$chapId);

        if($deviceId != null)
        {
            $apiModelChapProducts = new Api_Model_ChapProducts();
            
            /* 
             * If chapter language id is not equal to english (1) get the app build for chap's default language
             * Else get the english language build
             */

            $buildId = NULL;
            if($chapLangId != 1):
                $buildId = $apiModelChapProducts->getProductBuildChapIdByLanId($appId, $deviceId, $chapLangId);
            endif;
            
            /*
             * If the build is not available in chapter's default language the following function will get the english build
             */
            if(empty($buildId)):
                $buildId = $apiModelChapProducts->getProductBuildId($appId, $deviceId);
            endif;
	     //print_r($buildId); die();
            $appDetails['build_id'] =  $buildId;
        }
        
        $cache->set($appDetails, $key, 3600);
        
        return $appDetails;
    }

    /**
     * 
     * Returns the details of a particular app - Different than above function
     * @param $appId App ID
     * returns app details
     */
    public function appDetailsById($appId, $deviceId = null,  $screenWidth = 320, $screenHeight = 480, $chapLangId = NULL, $thumbWidth = 80, $thumbHeight = 80, $chapId) {

        $cache  = Zend_Registry::get('cache');
        $key    = 'appDetailsById'.$appId.$deviceId.$screenWidth.$screenHeight.$chapLangId.$thumbWidth.$thumbHeight.$chapId;
        if (($appDetailsArray = $cache->get($key)) !== false)
        {
        
        	return $appDetailsArray;
        
        }
        
        // Set image path from config parameter
        $viewHelp = new Nexva_View_Helper_ProductImages ();
        $this->serverPathThumb = $viewHelp->productImages() . '/vendors/phpThumb/phpThumb.php?src=';
        $this->serverPath = $viewHelp->productImages() . '/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/';

        //Instantiate the Default Product model
        $productModel = new Model_Product ();

        $lightMode = true;

        //Get the prdocut details of the app
        $appDetails = $productModel->getProductDetailsById($appId, $lightMode, $chapLangId, '', $chapId, true);

        $appDetailsArray = array();

        if (!empty($appDetails) || $appDetails !== null) {
            unset($appDetails['device_selection_type'], $appDetails['thumb_name'], $appDetails['user_meta'], $appDetails['registration_model'], $appDetails['status'], $appDetails['platform_id'], $appDetails['platform_name'], $appDetails['supported_platforms'], $appDetails['created'], $appDetails['changed'], $appDetails['deleted'], $appDetails['featured'], $appDetails['id']);

            foreach ($appDetails as $key => $value) {

                //Zend_Debug::dump($key);
                //Zend_Debug::dump($value);
                //die();
                if ($key == "thumb") {
                	
                	
                    $value = $this->serverPathThumb . $value . "&w=$thumbWidth&h=$thumbHeight&aoe=0&fltr[]=ric|0|0&q=100&f=png";
                    
                    //$value = $this->__serverS3Path."/productimg/".$appId."/thumbnails/80x80/".basename($value);
                    
                    
                    $appDetailsArray[$key] = $value;
                } else if ($key == "uid") {
                    //Add Vendor Name
                    $userMeta = new Model_UserMeta();
                    $userMeta->setEntityId($value);
                    $appDetailsArray["vendor"] = $userMeta->COMPANY_NAME;
                } else if ($key == "screenshots") {

                    $screenshotsArray = array();

                    for ($i = 0; $i < count($value); $i++) {

                    	$screenshotsArray[$i] = $this->serverPathThumb . $value[$i] . "&w=$screenWidth&h=$screenHeight&aoe=0&fltr[]=ric|0|0&q=100&f=png";
                    	//$screenshotsArray[$i] = $this->__serverS3Path.'/productimg/'.$appId.'/screenshot'.'/200x270/'.basename($value[$i]);
                    }

                    $appDetailsArray[$key] = $screenshotsArray;
                } else {
                    $appDetailsArray[$key] = strip_tags($value);
                    //Zend_Debug::dump($value);
                    $ratingModel = new Model_Rating();
                    $appDetailsArray['votes'] = $ratingModel->getTotalRatingByProduct($appId);
                    $appDetailsArray['rating'] = $ratingModel->getAverageRatingByProduct($appId);
                }
            }
            //die();
            //Zend_Debug::dump($appDetailsArray);die();
            unset($appDetailsArray['uid']);
        }

             /*$apiModelChapProducts = new Api_Model_ChapProducts();
	     $buildId = $apiModelChapProducts->getProductBuildId($appId, $deviceId);
	     $appDetailsArray['build_id'] =  $buildId;*/
             
             $apiModelChapProducts = new Api_Model_ChapProducts();

            /* 
             * If chapter language id is not equal to english (1) get the app build for chap's default language
             * Else get the english language build
             */
            $buildId = NULL;
            if($chapLangId != 1){
                $buildId = $apiModelChapProducts->getProductBuildChapIdByLanId($appId, $deviceId, $chapLangId);
            }

            /*
             * If the build is not available in chapter's default language the following function will get the english build
             */
            if(empty($buildId)){
                $buildId = $apiModelChapProducts->getProductBuildId($appId, $deviceId);
            }

            $appDetailsArray['build_id'] =  $buildId;

        $downloadStats = new Api_Model_StatisticsDownloads();
        $downloads = $downloadStats->getDownloadCountByAppChap($appId, $chapId);
        $appDetailsArray['downloads'] = $downloads;

        $viewStats = new Api_Model_StatisticsProducts();
        $views = $viewStats->getViewCountByAppChap($appId, $chapId);
        $appDetailsArray['views'] = $views;

        $buildFileModel = new Api_Model_BuildFiles();
        $fileInfo = $buildFileModel->fetchAll($buildFileModel->select()->where('build_id = ?',isset($appDetailsArray['build_id']))?$appDetailsArray['build_id']:"");

        foreach($fileInfo as $file){
            if(strpos($file->filename,'.apk') !== false ){
                $fileName = $file->filename;
            }
        }

        if($fileName){
            $productModel = new Model_Product ( );
            $fileSize = $productModel->getS3Fileinfo ( $appId . '/'.$fileName );
        }

        if($fileSize !== null){
            $appDetailsArray['file_size'] = $fileSize;
        }
        
        $cache->set($appDetailsArray, $key, 3600);

        return $appDetailsArray;

    }

    /**
     * 
     * Returns the set of app categories (Parent Categories)
     * 
     */
    
    public function categoryAction($chapId = null, $langId = null) {
    	
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'categoryAction'.$chapId.$langId;
        if (($allCategories = $cache->get($key)) !== false)
        {
        
        	return $allCategories;
        
        }
        
         $cat = new Model_ProductCategories();
  
        $categoryModel = new Model_Category();
        
        //Get Main Categories
        //Check the language id and if the language id is not equal to english (1) call the translation function
        if($langId == 1 || empty($langId)):
            $categories = $categoryModel->getParentCategories($chapId);
        else:
            $categories = $categoryModel->getParentCategoriesByLangId($langId, $chapId);
        endif;

        //check if the categories are existing
        if(count($categories) == 0):
            $allCategories = array();
        endif;
        
        //Loop manin categories and add sub Cats
        foreach ($categories as $key => $value) {
 
            $noOfAppsMain =  $cat->productCountByCategory($value['main_cat'], $chapId);

            if($noOfAppsMain)
                $value['app_count'] = $noOfAppsMain->app_count;
            else 
                $value['app_count'] = 0;
        
            $allCategories[$key] = $value;

            if ($value["main_cat"]) {
            	
                //Get Sub Categories
                //Check the language id and if the language id is not equal to english (1) call the translation function
                if($langId == 1 || empty($langId)):
                    $subCat = $categoryModel->getSubCatsByID($value["main_cat"], $chapId);
                else:
                    $subCat = $categoryModel->getSubCatsByIDAndLangId($value["main_cat"],$langId,$chapId);
                endif;
        
                
              
			foreach ($subCat as $keySub => &$valueSub) {

				   $noOfAppsSub =  $cat->productCountByCategory($valueSub['cat_id'], $chapId);
                
                            if($noOfAppsSub)
                            $valueSub['app_count'] = $noOfAppsSub->app_count;
                            else 
                        $valueSub['app_count'] = 0;
                }
           
		    	$allCategories[$key]['sub_cat'] = $subCat;
                    
            }
            
            $noOfAppsMain = '';
        }
        
        $cache->set($allCategories, $key, 3600);
        
       return $allCategories;
    }
    
    public function categoryMtsAction($chapId = null, $langId = null) {
    	
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'categoryAction'.$chapId.$langId;
        if (($allCategories = $cache->get($key)) !== false)
        {
        
        	return $allCategories;
        
        }
        
         $cat = new Model_ProductCategories();
  
        $categoryModel = new Model_Category();
        
        //Get Main Categories
        //Check the language id and if the language id is not equal to english (1) call the translation function
        if($langId == 1 || empty($langId)):
            $categories = $categoryModel->getParentCategoriesForMts($chapId);
        else:
            $categories = $categoryModel->getParentCategoriesByLangIdForMts($langId, $chapId);
        endif;

        //check if the categories are existing
        if(count($categories) == 0):
            $allCategories = array();
        endif;
        //Loop manin categories and add sub Cats
        foreach ($categories as $key => $value) {
 
            $noOfAppsMain =  $cat->productCountByCategory($value['main_cat'], $chapId);

            if($noOfAppsMain)
                $value['app_count'] = $noOfAppsMain->app_count;
            else 
                $value['app_count'] = 0;
        
            $allCategories[$key] = $value;

            if ($value["main_cat"]) {
            	
                //Get Sub Categories
                //Check the language id and if the language id is not equal to english (1) call the translation function
                if($langId == 1 || empty($langId)):
                    $subCat = $categoryModel->getSubCatsByID($value["main_cat"], $chapId);
                else:
                    $subCat = $categoryModel->getSubCatsByIDAndLangId($value["main_cat"],$langId,$chapId);
                endif;
        
                
              
			foreach ($subCat as $keySub => &$valueSub) {

				   $noOfAppsSub =  $cat->productCountByCategory($valueSub['cat_id'], $chapId);
                
                            if($noOfAppsSub)
                            $valueSub['app_count'] = $noOfAppsSub->app_count;
                            else 
                        $valueSub['app_count'] = 0;
                }
                        
		    	$allCategories[$key]['sub_cat'] = $subCat;
                    
            }
            
            $noOfAppsMain = '';
        }
        
        $cache->set($allCategories, $key, 3600);
        
       return $allCategories;
    }
    
    /**
     * 
     * Returns the featured apps
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID   
     * returns $products array
     */
    public function featuredAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $thumbWidth, $thumbHeight) {

        $cache  = Zend_Registry::get('cache');
        $key    = 'featuredAppsAction'.$chapId.$limit.$deviceId.$apiCall.$category.$thumbWidth.$thumbHeight;
        if (($products = $cache->get($key)) !== false)
        {
        
        	return $products;
        
        }
        
        $viaualPath = Zend_Registry::get('config')->product->visuals->dirpath;

        //get product Ids
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getFeaturedProductsbyDevice($chapId, $deviceId, $limit, $category);
        } else {
            $products = $modelChapProducts->getFeaturedProductIds($chapId, $limit);
        }
        


        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId, $apiCall,'',$thumbWidth, $thumbHeight, $chapId);
        }
        

        $cache->set($products, $key, 3600);
        
        return $products;
    }
    
    
    
    public function flaggedAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $thumbWidth, $thumbHeight) {
    
        $cache  = Zend_Registry::get('cache');
        $key    = 'flaggedAppsAction'.$chapId.$limit.$deviceId.$apiCall.$category.$thumbWidth.$thumbHeight;
        if (($products = $cache->get($key)) !== false)
        {
        
        	return $products;
        
        }
        
    	$viaualPath = Zend_Registry::get('config')->product->visuals->dirpath;
    
    	//get product Ids
    	$modelChapProducts = new Api_Model_ChapProducts();
    
    	//Differnt methods will be called when Device id is given
    	if ($deviceId !== null && !empty($deviceId)) {
    		$products = $modelChapProducts->getFlaggedProductsbyDevice($chapId, $deviceId, $limit, $category);
    	} else {
    		$products = $modelChapProducts->getFlaggedProductsbyDevice($chapId, $limit);
    	}
    
    
    
    	//get Product details
    	if (count($products) > 0) {
    		$products = $this->getProductDetails($products, $deviceId, $apiCall,'',$thumbWidth, $thumbHeight, $chapId);
    	}
    
    	$cache->set($products, $key, 3600);
    
    	return $products;
    }
    
    
    
    
    /**
     * 
     * Returns the bannered apps / Promoted apps for the API
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID   
     * returns $products array
     */
    public function banneredAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $thumbWidth=null, $thumbHeight=null) {
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'banneredAppsAction'.$chapId.$limit.$deviceId.$apiCall.$category.$thumbWidth.$thumbHeight;
        if (($products = $cache->get($key)) !== false)
        {
        
        	return $products;
        
        }
        //get product Ids
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) 
        {
            $products = $modelChapProducts->getBanneredProductsbyDevice($chapId, $deviceId, $limit, $category);
        } 
        else 
        {
            $products = $modelChapProducts->getBanneredProductIds($chapId, $limit);
        }


        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId, $apiCall,'',$thumbWidth, $thumbHeight, $chapId);
        }

        $cache->set($products, $key, 3600);
        
        return $products;
    }

    /**
     * 
     * Returns the Most Viewd apps
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID   
     * returns $products array
     */
    public function MostViewdAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $thumbWidth, $thumbHeight) {
        //get product Ids
        $cache  = Zend_Registry::get('cache');
        $key    = 'MostViewdAppsAction'.$chapId.$limit.$deviceId.$apiCall.$category.$thumbWidth.$thumbHeight;
        if (($products = $cache->get($key)) !== false)
        {
        
        	return $products;
        
        }
        
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getMostViewedProductIdsByDevice($chapId, $deviceId, $limit);
        } else {
            $products = $modelChapProducts->getMostViewedProductIds($chapId, $limit);
        }


        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId, $apiCall,'',$thumbWidth, $thumbHeight, $chapId);
        }

        $cache->set($products, $key, 3600);
        
        return $products;
    }

    /**
     * 
     * Returns the Top Downloded apps
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID   
     * returns $products array
     */
    public function topDownloadAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $thumbWidth, $thumbHeight) {
        //get product Ids
        $cache  = Zend_Registry::get('cache');
        $key    = 'topDownloadAppsAction'.$chapId.$limit.$deviceId.$apiCall.$category.$thumbWidth.$thumbHeight;
        if (($products = $cache->get($key)) !== false)
        {
        
        	return $products;
        
        }
        
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getTopProductIdsByDevice($chapId, $deviceId, $limit);
        } else {
            $products = $modelChapProducts->getTopProductIds($chapId, $limit);
        }


        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId, $apiCall,'',$thumbWidth, $thumbHeight, $chapId);
        }
        
        $cache->set($products, $key, 3600);

        return $products;
    }

    /**
     * 
     * Returns the Top Rated apps
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID   
     * returns $products array
     */
    public function topRatedAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $thumbWidth, $thumbHeight) {
        //get product Ids
        $cache  = Zend_Registry::get('cache');
        $key    = 'topRatedAppsAction'.$chapId.$limit.$deviceId.$apiCall.$category.$thumbWidth.$thumbHeight;
        if (($products = $cache->get($key)) !== false)
        {
        
        	return $products;
        
        }
        
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getTopRatedProductIdsByDevice($chapId, $deviceId, $limit);
        } else {
            $products = $modelChapProducts->getTopRatedProductIds($chapId, $limit);
        }


        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products,$deviceId,'','', $thumbWidth, $thumbHeight, $chapId);
        }
        
        $cache->set($products, $key, 3600);

        return $products;
    }
    
    /**
     * 
     * Returns the Free apps
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID   
     * returns $products array
     */
    public function freeAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $thumbWidth, $thumbHeight) {
        //get product Ids
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'freeAppsAction'.$chapId.$limit.$deviceId.$apiCall.$category.$thumbWidth.$thumbHeight;
        if (($products = $cache->get($key)) !== false)
        {
        
        	return $products;
        
        }
        
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getFreeProductIdsByDevice($chapId, $deviceId, $limit);
        } else {
            $products = $modelChapProducts->getFreeProductIds($chapId, $limit);
        }

        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId, $apiCall,'', $thumbWidth, $thumbHeight, $chapId);
        }

        $cache->set($products, $key, 3600);
        
        return $products;
    }

    /**
     * 
     * Returns the Paid apps
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID   
     * returns $products array
     */
    public function paidAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $thumbWidth, $thumbHeight) {
        //get product Ids
        $cache  = Zend_Registry::get('cache');
        $key    = 'freeAppsAction'.$chapId.$limit.$deviceId.$apiCall.$category.$thumbWidth.$thumbHeight;
        if (($products = $cache->get($key)) !== false)
        {
        
        	return $products;
        
        }
        
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getPaidProductIdsByDevice($chapId, $deviceId, $limit);
        } else {
            $products = $modelChapProducts->getPaidProductIds($chapId, $limit);
        }

        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId, $apiCall ,'', $thumbWidth, $thumbHeight, $chapId);
        }

        $cache->set($products, $key, 3600);
        
        return $products;
    }

    /**
     * 
     * Returns the Newest apps
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID   
     * returns $products array
     */
    public function newestAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $thumbWidth, $thumbHeight) {
        //get product Ids
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'newestAppsAction'.$chapId.$limit.$deviceId.$apiCall.$category.$thumbWidth.$thumbHeight;
        if (($products = $cache->get($key)) !== false)
        {
        
        	return $products;
        
        }
        
        $modelChapProducts = new Api_Model_ChapProducts();

        $products = $modelChapProducts->getNewestProductIds($chapId, $limit);

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getNewestProductIdsByDevice($chapId, $deviceId, $limit);
        } else {
            $products = $modelChapProducts->getNewestProductIds($chapId, $limit);
        }

        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId, $apiCall,'', $thumbWidth, $thumbHeight, $chapId);
        }

        $cache->set($products, $key, 3600);
        
        return $products;
    }

    public function getProductDetails($products, $deviceId, $apiCall = false, $s3file = false, $thumbWidth = NULL , $thumbHeight = NULL, $chapId = NULL)
    {
     
        
        // Set image path from config parameter
        $viewHelp = new Nexva_View_Helper_ProductImages ();
        
        $this->serverPathThumb = $viewHelp->productImages() . '/vendors/phpThumb/phpThumb.php?src=';
        $this->serverPath = $viewHelp->productImages() . '/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/';

        $visualPath = Zend_Registry::get('config')->product->visuals->dirpath;

        $productMeta = new Model_ProductMeta();
        $productImages = new Model_ProductImages();
        $userMeta = new Model_UserMeta();
        $ratingModel = new Model_Rating();
        $buildFilesModel = new Model_BuildFiles();
		
		$modelDownloadStats = new Model_StatisticDownload();
        $model_ProductBuild = new Model_ProductBuild();
        
        
   


        
        foreach ($products as &$product) 
        {
            //if it is an API call, different method for getting app properties
            if ($product["thumbnail"] && $apiCall == false) {
               
            	$product["thumbnail"] = $visualPath . '/' . $product["thumbnail"];

                $productMeta->setEntityId($product['product_id']);
                $product['description'] = stripslashes(nl2br(strip_tags($productMeta->FULL_DESCRIPTION, "<br>")));
                $product['brief_description'] = stripslashes(strip_tags($productMeta->BRIEF_DESCRIPTION));

                //Add product images
                $productImage = $productImages->getImageById($product['product_id']);
                
                if (count($productImage) > 0) {
                  
                   $product['image'] = $visualPath . '/' . $productImage->filename;                 
                }
         

                //Add Vendor Name
                $userMeta->setEntityId($product['user_id']);
                $product['vendor'] = $userMeta->COMPANY_NAME;

                //Add Rating
                $product['avg_rating'] = $ratingModel->getAverageRatingByProduct($product['product_id']);
                $product['total_ratings'] = $ratingModel->getTotalRatingByProduct($product['product_id']);
         

                //check if it's already rated
                $ratingNamespace = new Zend_Session_Namespace('Ratings');

                $productRated = false;
                $userRating = 1;
                if (isset($ratingNamespace->ratedProducts)) {
                    $ratedProducts = $ratingNamespace->ratedProducts;
                    if (isset($ratedProducts[$product['product_id']])) {
                        $productRated = true;
                        $userRating = $ratedProducts[$product['product_id']];
                    }
                }

                $product['product_rated'] = $productRated;
            } 
            else 
            {
                $product["thumbnail"] = $this->serverPath . $product["thumbnail"] . "&w=".$thumbWidth."&h=".$thumbHeight."&aoe=0&fltr[]=ric|0|0&q=100&f=png";

                //Add Vendor Name
                $userMeta->setEntityId($product['user_id']);
                $product['vendor'] = $userMeta->COMPANY_NAME;
                
        

                $product['votes'] = $ratingModel->getTotalRatingByProduct($product['product_id']);
                $product['rating'] = $ratingModel->getAverageRatingByProduct($product['product_id']);
                
                //Add brief and full description
                $productMeta->setEntityId($product['product_id']);
                $product['description'] = stripslashes(nl2br(strip_tags($productMeta->FULL_DESCRIPTION, "<br>")));
                $product['brief_description'] = stripslashes(strip_tags($productMeta->BRIEF_DESCRIPTION));
				
				$loggedInUserId = @Zend_Auth::getInstance()->getIdentity()->id; 
				
		
                //Get build updates if available
                $product["updates_available"] = 0;
                if($loggedInUserId){
                    $downloadedProduct = $modelDownloadStats->getDownloadedBuildsByUserId($loggedInUserId, 'MOBILE', $product['product_id']);
                }
                if(count($downloadedProduct)>0){
                    $product["updates_available"] = $model_ProductBuild->checkProductBuildUpdated($downloadedProduct[0]['product_id'], $downloadedProduct[0]['platform_id'], $downloadedProduct[0]['language_id'], $downloadedProduct[0]['date']);
                }
				
				/*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
					print_r($downloadedProduct); echo '<br/>';
				}*/
            }
            
             
            if((!array_key_exists('build_id', $product)) and $deviceId)    
            {                
	                $apiModelChapProducts = new Api_Model_ChapProducts();
	                $buildId = $apiModelChapProducts->getProductBuildId($product['product_id'], $deviceId);
	                
                
	                $product['build_id'] =  $buildId;                
                
            }
            if (isset($product['build_id'])) 
            {
                if ($s3file) 
                {
                    $productDownloadCls = new Nexva_Api_ProductDownload();
                    $buildUrl = $productDownloadCls->getBuildFileUrl($product['product_id'], $product['build_id']);
                    $product['download_app'] = $buildUrl;
              
                }

                $result = $buildFilesModel->getFileTypeapi($product['build_id']);
                
             //   Zend_Debug::dump($result);

              //  $arr = explode(".", $result[0]->filename);
              //  $arr = array_reverse($arr);
             //   $product['build_type'] = $arr[0];
             
                
                /*
                if($result[0]) {
                    
                    if($result[0]->filename) 
                        $product['build_type'] =  str_replace('.', '', strrchr( $result[0]->filename, '.'));
                    
                }
                
             */
                
            // Zend_Debug::dump($result,'dddddddddd');
            
                if($result)
                    $product['build_type'] =  str_replace('.', '', strrchr( $result->filename, '.'));
                
                //echo $result[0]->filename;
                
              
            }
            
 
 			/* Add translation. 
             * Get the translations for product name,description,brief_description and overite if the translations are available.
             * If not let it be with english language.
             */
            

            
            
            //Get language id of chap
			$chapId = ($chapId) ? $chapId : 1 ;
			$languageUsersModel = new Partner_Model_LanguageUsers();
			$chapLangId = $languageUsersModel->getLanguageIdByChap($chapId);
			
			
		
		
            $productLanguageMeta = new Model_ProductLanguageMeta();
            
            $productTranslation = $productLanguageMeta->loadTranslation($product['product_id'], $chapLangId);

            //this is temporary for MTN Iran cell
            /* @todo: needs to be removed once we bags all translations
             */

            $product["name"] = ($productTranslation->PRODUCT_NAME)? stripslashes(strip_tags($productTranslation->PRODUCT_NAME)) : $product["name"];
            

            $product["description"] = ($productTranslation->PRODUCT_DESCRIPTION)? stripslashes(strip_tags($productTranslation->PRODUCT_DESCRIPTION)) : stripslashes(strip_tags($productMeta->FULL_DESCRIPTION));
            $product["brief_description"] = ($productTranslation->PRODUCT_SUMMARY)? stripslashes(strip_tags($productTranslation->PRODUCT_SUMMARY)) : stripslashes(strip_tags($productMeta->BRIEF_DESCRIPTION));
   
       
            unset($product['id']);
            unset($product['session_id']);
            unset($product['ip']);
            unset($product['chap_id']);
            unset($product['language_id']);
            unset($product['source']);
            unset($product['platform_id']);
            unset($product['user_id']);
            unset($product['keywords']);
            unset($product['pro_count']);
        }


        return $products;
    }

    
    /**
     * 
     * Returns the text pages of an CHAP whitelabel
     * @param $chapId Chap ID (HTTP request headers)
     
     * returns $pages array
     */
    public function getTextPagesAction($chapId) 
    {
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'getTextPagesAction'.$chapId;
        if (($pages = $cache->get($key)) !== false)
        {
        
        	return $pages;
        
        }
        //get product Ids
        $modelPages = new Api_Model_WlPages();

        $pages = $modelPages->getAllPages($chapId);
        
        $cache->set($pages, $key, 3600);

        return $pages;
    }
}