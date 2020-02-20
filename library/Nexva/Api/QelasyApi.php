<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 5/28/14
 * Time: 1:55 PM
 * To change this template use File | Settings | File Templates.
 */


    /*
     * This is for Qelasy Api calls as they are going to restrict apps based upon Grades(Their own criteria).
     * Therefore all the queries have to be modified accordingly & we are using separate class.
     */
class Nexva_Api_QelasyApi {

    protected $serverPathThumb = "http://thor.nexva.com/vendors/phpThumb/phpThumb.php?src=";
    protected $serverPath = "http://thor.nexva.com/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/";

    protected $__serverS3Path  = '';


    function __construct() {

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
    public function allAppsByChapAction($chapId, $deviceId, $category, $grade, $limit = 10, $offset = 0, $userType = null)
    {
        //Instantiate the Api_Model_ChapProducts model
        $chapProductModel = new Api_Model_ChapProducts();

        //get the app details
        $products = $chapProductModel->getQelasyProductsAll($chapId, $deviceId, $category, $grade, $limit, $offset, $userType);

        $apiCall = true;

        //get Product details
        if (count($products) > 0) {
            //$ApiModel = new Nexva_Api_NexApi();
            //$products = $ApiModel->getProductDetails($products, $deviceId, $apiCall);
            $products = $this->getProductDetails($products, $deviceId, $apiCall);
        }

        return $products;
    }

    /**
     *
     * Returns the featured apps
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID
     * returns $products array
     */
    public function featuredAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $grade = null, $thumbWidth, $thumbHeight, $userType = null) {

        $viaualPath = Zend_Registry::get('config')->product->visuals->dirpath;

        //get product Ids
        $modelChapProducts = new Api_Model_ChapProducts();
        //$deviceId = null;
        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getQelasyFeaturedProductsbyDevice($chapId, $deviceId, $limit, $grade, $userType);
        } else {
            $products = $modelChapProducts->getQelasyFeaturedProductIds($chapId, $limit, $grade, $userType);
        }

        //get Product details
        if (count($products) > 0) {
            //$ApiModel = new Nexva_Api_NexApi();
            //$products = $ApiModel->getProductDetails($products, $deviceId, $apiCall,'',$thumbWidth, $thumbHeight, $chapId);
            $products = $this->getProductDetails($products, $deviceId, $apiCall,'',$thumbWidth, $thumbHeight, $chapId);
        }

        return $products;
    }

    /**
     * Identical to the nexapi functionality
     */
    /**
     *
     * Returns the details of a particular app - Different than above function
     * @param $appId App ID
     * returns app details
     */
    public function appDetailsById($appId, $deviceId = null,  $screenWidth = 320, $screenHeight = 480, $chapLangId = NULL, $thumbWidth = 80, $thumbHeight = 80, $chapId = null, $userId = null) {

        // Set image path from config parameter
        $viewHelp = new Nexva_View_Helper_ProductImages ();
        $this->serverPathThumb = $viewHelp->productImages() . '/vendors/phpThumb/phpThumb.php?src=';
        $this->serverPath = $viewHelp->productImages() . '/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/';

        //Instantiate the Default Product model
        $productModel = new Model_Product ();

        $lightMode = true;

        //Get the prdocut details of the app
        $appDetails = $productModel->getProductDetailsById($appId, $lightMode, $chapLangId);

        $appDetailsArray = array();

        if (!empty($appDetails) || $appDetails !== null) {
            unset($appDetails['device_selection_type'], $appDetails['thumb_name'], $appDetails['user_meta'], $appDetails['registration_model'], $appDetails['status'], $appDetails['platform_id'], $appDetails['platform_name'], $appDetails['supported_platforms'], $appDetails['created'], $appDetails['changed'], $appDetails['deleted'], $appDetails['featured'], $appDetails['id']);

            foreach ($appDetails as $key => $value) {

                if ($key == "thumb") {

                    $value = $this->serverPathThumb . $value . "&w=$thumbWidth&h=$thumbHeight&aoe=0&fltr[]=ric|15|15&q=100&f=png";

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


        $statisticDownloadModel = new Model_StatisticDownload();
        $hasDownloaded = $statisticDownloadModel->chapUserHasDownloaded($chapId, $appId, $buildId, $userId);
        $appDetailsArray['has_downloaded'] = $hasDownloaded;

        return $appDetailsArray;
    }


    /**
     * Identical to the nexapi functionality
     */
    /**
     *
     * Returns the set of app categories (Parent Categories)
     *
     */

    public function categoryAction($chapId, $langId = null, $grade = null) {

        $cat = new Model_ProductCategories();

        $categoryModel = new Model_Category();

        //Get Main Categories
        //Check the language id and if the language id is not equal to english (1) call the translation function
        if($langId == 1 || empty($langId)):
            $categories = $categoryModel->getParentCategories($chapId, $grade);
        else:
            $categories = $categoryModel->getParentCategoriesByLangId($langId, $chapId, $grade);
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
                    $subCat = $categoryModel->getSubCatsByID($value["main_cat"],$chapId,$grade);
                else:
                    $subCat = $categoryModel->getSubCatsByIDAndLangId($value["main_cat"],$langId,$chapId,$grade);
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
        return $allCategories;
    }


    /**
     *
     * Returns the Free apps
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID
     * returns $products array
     */
    public function freeAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $grade = null, $thumbWidth, $thumbHeight, $userType = null) {
        //get product Ids
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getQelasyFreeProductIdsByDevice($chapId, $deviceId, $limit, $grade, $userType);
        } else {
            $products = $modelChapProducts->getQelasyFreeProductIds($chapId, $limit, $grade, $userType);
        }

        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId, $apiCall,'', $thumbWidth, $thumbHeight, $chapId);
        }

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
    public function paidAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $grade = null, $thumbWidth, $thumbHeight, $userType = null) {
        //get product Ids
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given

        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getQelasyPaidProductIdsByDevice($chapId, $deviceId, $limit, $grade, $userType);
        } else {
            $products = $modelChapProducts->getQelasyPaidProductIds($chapId, $limit, $grade, $userType);
        }

        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId, $apiCall ,'', $thumbWidth, $thumbHeight, $chapId);
        }

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
    public function topRatedAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $grade = null, $thumbWidth, $thumbHeight, $userType = null) {
        //get product Ids
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getQelasyTopRatedProductIdsByDevice($chapId, $deviceId, $limit, $grade, $userType);
        } else {
            $products = $modelChapProducts->getQelasyTopRatedProductIds($chapId, $limit, $grade, $userType);
        }

        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products,$deviceId,'','', $thumbWidth, $thumbHeight, $chapId);
        }

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
    public function banneredAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $grade = null, $thumbWidth, $thumbHeight, $userType = null) {

        //get product Ids
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId))
        {
            $products = $modelChapProducts->getQelasyBanneredProductsbyDevice($chapId, $deviceId, $limit, $category, $grade, $userType);
        }
        else
        {
            $products = $modelChapProducts->getQelasyBanneredProductIds($chapId, $limit, $grade, $userType);
        }


        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId, $apiCall,'',$thumbWidth, $thumbHeight, $chapId);
        }

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
    public function newestAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null, $grade = null, $thumbWidth, $thumbHeight, $userType = null) {

        //get product Ids
        $modelChapProducts = new Api_Model_ChapProducts();

        $products = $modelChapProducts->getNewestProductIds($chapId, $limit);

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getQelasyNewestProductIdsByDevice($chapId, $deviceId, $limit, $grade, $userType);
        } else {
            $products = $modelChapProducts->getQelasyNewestProductIds($chapId, $limit, $grade, $userType);
        }

        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId, $apiCall,'', $thumbWidth, $thumbHeight, $chapId);
        }

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

                $product["thumbnail"] = $this->serverPath . $product["thumbnail"] . "&w=".$thumbWidth."&h=".$thumbHeight."&aoe=0&fltr[]=ric|15|15&q=100&f=png";

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

                if($result)
                    $product['build_type'] =  str_replace('.', '', strrchr( $result->filename, '.'));


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

}