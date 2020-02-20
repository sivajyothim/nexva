<?php

class Nexva_Api_NexApii {

    protected $serverPathThumb = "http://thor.nexva.com/vendors/phpThumb/phpThumb.php?src=";
    protected $serverPath = "http://thor.nexva.com/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/";
    protected $__serverS3Path = '';

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
    public function allAppsByChapAction($chapId, $deviceId, $category, $limit = 10, $offset = 0) {
        //Instantiate the Api_Model_ChapProducts model
        $chapProductModel = new Api_Model_ChapProducts();

        //get the app details
        $products = $chapProductModel->getChapProductsAll($chapId, $deviceId, $category, $limit, $offset);

        $apiCall = true;

        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId, $apiCall);
        }

        return $products;
    }

    /**
     * 
     * Returns the details of a particular app  
     * @param $appId App ID
     * returns app details
     */
    public function detailsAppAction($appId) {
        $productModel = new Model_Product();
        $appDetails = $productModel->getProductDetailsById($appId);
        return $appDetails;
    }

    /**
     * 
     * Returns the details of a particular app - Different than above function
     * @param $appId App ID
     * returns app details
     */
    public function appDetailsById($appId, $deviceId = null) {

        // Set image path from config parameter
        $viewHelp = new Nexva_View_Helper_ProductImages ();
        $this->serverPathThumb = $viewHelp->productImages() . '/vendors/phpThumb/phpThumb.php?src=';
        $this->serverPath = $viewHelp->productImages() . '/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/';



        //Instantiate the Default Product model
        $productModel = new Model_Product ();

        $lightMode = true;

        //Get the prdocut details of the app
        $appDetails = $productModel->getProductDetailsById($appId, $lightMode);

        $appDetailsArray = array();

        if (!empty($appDetails) || $appDetails !== null) {
            unset($appDetails['device_selection_type'], $appDetails['thumb_name'], $appDetails['user_meta'], $appDetails['registration_model'], $appDetails['status'], $appDetails['platform_id'], $appDetails['platform_name'], $appDetails['supported_platforms'], $appDetails['created'], $appDetails['changed'], $appDetails['categories'], $appDetails['deleted'], $appDetails['featured'], $appDetails['id']);


            foreach ($appDetails as $key => $value) {
                if ($key == "thumb") {


                    $value = $this->serverPathThumb . $value . "&w=80&h=80&aoe=0&fltr[]=ric|0|0&q=100&f=png";

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


                        $screenshotsArray[$i] = $this->serverPathThumb . $value[$i] . "&w=200&h=270&aoe=0&fltr[]=ric|0|0&q=100&f=png";
                        //$screenshotsArray[$i] = $this->__serverS3Path.'/productimg/'.$appId.'/screenshot'.'/200x270/'.basename($value[$i]);
                    }

                    $appDetailsArray[$key] = $screenshotsArray;
                } else {
                    $appDetailsArray[$key] = strip_tags($value);
                    $ratingModel = new Model_Rating();
                    $appDetailsArray['votes'] = $ratingModel->getTotalRatingByProduct($appId);
                    $appDetailsArray['rating'] = $ratingModel->getAverageRatingByProduct($appId);
                }
            }

            unset($appDetailsArray['uid']);
        }

        $apiModelChapProducts = new Api_Model_ChapProducts();
        $buildId = $apiModelChapProducts->getProductBuildId($appId, $deviceId);

        $appDetailsArray['build_id'] = $buildId;

        return $appDetailsArray;
    }

    /**
     * 
     * Returns the set of app categories (Parent Categories)
     * 
     */
    public function categoryAction($chapId = null) {

        $cat = new Model_ProductCategories();

        $categoryModel = new Model_Category();
        //Get Main Categories
        $categories = $categoryModel->getParentCategories();
        $category_main= array();
        //Loop manin categories and add sub Cats
        foreach ($categories as $key => $value) {

            $noOfAppsMain = $cat->productCountByCategory($value['main_cat'], 8056);

            if ($noOfAppsMain)
                $value['app_count'] = $noOfAppsMain->app_count;
            else
                $value['app_count'] = 0;

            $allCategories[$key] = $value;

            if ($value["main_cat"]) {

                //Get Sub Categories
                $subCat = $categoryModel->getSubCatsByID($value["main_cat"]);

                foreach ($subCat as $keySub => &$valueSub) {

                    $noOfAppsSub = $cat->productCountByCategory($valueSub['cat_id'], 8056);

                    if ($noOfAppsSub)
                        $valueSub['app_count'] = $noOfAppsSub->app_count;
                    else
                        $valueSub['app_count'] = 0;
                }

                $allCategories[$key]['sub_cat'] = $subCat;
            }

            $noOfAppsMain = '';
            $category_main["category"]=$allCategories;
        }


        return $category_main;
    }

    /**
     * 
     * Returns the featured apps
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID   
     * returns $products array
     */
    public function featuredAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null) {
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
            $products = $this->getProductDetails($products, $deviceId, $apiCall);
        }

        return $products;
    }

    /**
     * 
     * Returns the bannered apps
     * @param $chapId Chap ID (HTTP request headers)
     * @param $limit Limit
     * @param $deviceId Device ID   
     * returns $products array
     */
    public function banneredAppsAction($chapId, $limit = null, $deviceId = null, $apiCall = false, $category = null) {

        //get product Ids
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getBanneredProductsbyDevice($chapId, $deviceId, $limit, $category);
        } else {
            $products = $modelChapProducts->getBanneredProductIds($chapId, $limit);
        }


        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId, $apiCall);
        }

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
    public function MostViewdAppsAction($chapId, $limit = null, $deviceId = null) {
        //get product Ids
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getMostViewedProductIdsByDevice($chapId, $deviceId, $limit);
        } else {
            $products = $modelChapProducts->getMostViewedProductIds($chapId, $limit);
        }


        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId);
        }

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
    public function topDownloadAppsAction($chapId, $limit = null, $deviceId = null) {
        //get product Ids
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getTopProductIdsByDevice($chapId, $deviceId, $limit);
        } else {
            $products = $modelChapProducts->getTopProductIds($chapId, $limit);
        }


        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId);
        }

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
    public function freeAppsAction($chapId, $limit = null, $deviceId = null) {
        //get product Ids
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getFreeProductIdsByDevice($chapId, $deviceId, $limit);
        } else {
            $products = $modelChapProducts->getFreeProductIds($chapId, $limit);
        }

        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId);
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
    public function paidAppsAction($chapId, $limit = null, $deviceId = null) {
        //get product Ids
        $modelChapProducts = new Api_Model_ChapProducts();

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) {
            $products = $modelChapProducts->getPaidProductIdsByDevice($chapId, $deviceId, $limit);
        } else {
            $products = $modelChapProducts->getPaidProductIds($chapId, $limit);
        }

        //get Product details
        if (count($products) > 0) {
            $products = $this->getProductDetails($products, $deviceId);
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
    public function newestAppsAction($chapId, $limit = null, $deviceId = null) {
        //get product Ids
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
            $products = $this->getProductDetails($products, $deviceId);
        }

        return $products;
    }

    public function getProductDetails($products, $deviceId, $apiCall = false, $s3file = false) {
        // Set image path from config parameter
        $viewHelp = new Nexva_View_Helper_ProductImages ();

        $this->serverPathThumb = $viewHelp->productImages() . '/vendors/phpThumb/phpThumb.php?src=';
        $this->serverPath = $viewHelp->productImages() . '/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/';

        $visualPath = Zend_Registry::get('config')->product->visuals->dirpath;

        $productMeta = new Model_ProductMeta();
        $productImages = new Model_ProductImages();
        $userMeta = new Model_UserMeta();
        $ratingModel = new Model_Rating();

        foreach ($products as &$product) {
            //if it is an API call, different method for getting app properties
            if ($product["thumbnail"] && $apiCall == false) {

                $product["thumbnail"] = $visualPath . '/' . $product["thumbnail"];

                //$product["thumbnail"] = $this->__serverS3Path."/productimg/".$product['product_id']."/thumbnails/80x80/".$product["thumbnail"];

                $productMeta->setEntityId($product['product_id']);
                $product['description'] = stripslashes(nl2br(strip_tags($productMeta->FULL_DESCRIPTION, "<br>")));
                $product['brief_description'] = stripslashes(strip_tags($productMeta->BRIEF_DESCRIPTION));

                //Add product images
                $productImage = $productImages->getImageById($product['product_id']);
                if (count($productImage) > 0) {
                    //  $product['image'] = $visualPath . '/' . $productImage->filename;
                    $product['image'] = $visualPath . '/' . $productImage->filename;

                    //  $product['image'] = $this->__serverS3Path.'/productimg/'.$product['product_id'].'/screenshot'.'/200x270/'.basename($productImage->filename);
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
            } else {
                $product["thumbnail"] = $this->serverPath . $product["thumbnail"] . "&w=80&h=80&aoe=0&fltr[]=ric|0|0&q=100&f=png";
                //$product["thumbnail"] = $this->__serverS3Path."/productimg/".$product['product_id']."/thumbnails/80x80/".$product["thumbnail"];
                //Add Vendor Name
                $userMeta->setEntityId($product['user_id']);
                $product['vendor'] = $userMeta->COMPANY_NAME;

                $product['votes'] = $ratingModel->getTotalRatingByProduct($product['product_id']);
                $product['rating'] = $ratingModel->getAverageRatingByProduct($product['product_id']);
            }


            if ((!array_key_exists('build_id', $product)) and $deviceId) {

                $apiModelChapProducts = new Api_Model_ChapProducts();
                $buildId = $apiModelChapProducts->getProductBuildId($product['product_id'], $deviceId);


                $product['build_id'] = $buildId;
            }
            if (isset($product['build_id'])) {
                if ($s3file) {
                    $productDownloadCls = new Nexva_Api_ProductDownload();
                    $buildUrl = $productDownloadCls->getBuildFileUrl($product['product_id'], $product['build_id']);
                    $product['download_app'] = $buildUrl;
                }
            }


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