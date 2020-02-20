<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Mobile
 * @version     $Id$
 */

include_once APPLICATION_PATH."/../library/Nexva/PaymentGateway/Adapter/PaypalMobile/library/paypal.php";

class Mobile_AppController extends Nexva_Controller_Action_Mobile_MasterController {

    protected $_deviceId;
    protected $_appCount;

    public function init() {

        $config = Zend_Registry::get("config");
        if(substr($_SERVER['HTTP_HOST'], 0, 3) == 'www')
            $this->_redirect("http://" . $config->nexva->application->mobile->url);
        
        parent::init();
        $this->view->showToplinks = TRUE;
    }

    function indexAction() {
        $categories = new Mobile_Model_Category();
        $list       = $categories->getRootCategoriesForChap($this->themeMeta->USER_ID); 
        $this->view->enabledCategories         = $list; 
        $this->view->enableCategories   = true;
        $this->view->all                = TRUE;
        $this->view->htmlTitle          = 'All App Categories';
        
        $this->setCategories();
        $this->view->categories = $this->_categories;
        
        
    }
    
    
    /**
     * Show Free Applications
     */
    public function freeAction() {
        // set session to get returinig url
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $_SERVER["REQUEST_URI"];
        // set categories
        $this->view->enableCategories = true;
        $this->setCategories();
        //set to view
        $this->view->categories = $this->_categories;

        $this->view->showPageTitle = false;
        $this->view->pageTitle = 'Free Apps for ' . $this->getDeviceName();
        $product = new Model_Product();
        $product->setAppFilterRules($this->themeMeta->USER_ID);
        /**
         * Language is english, so lets make english apps a priority
         * This should only happen for english apps
         */
        $sessionLanguage = new Zend_Session_Namespace('application');  
        if ($sessionLanguage->languageId == $sessionLanguage->defaultLangId) { 
            $product->languageId        = $sessionLanguage->languageId;
            $product->defaultLanguageId = $sessionLanguage->defaultLangId;
        }  
        
        $product->appFilter = $this->themeMeta->WHITELABLE_APP_FILTER;
        $limit      = Zend_Registry::get('config')->mobile->paginator->limit;
        $page       = $this->_getParam('page', 1); 
        $products = $product->getFreeAppsByDeviceId($this->getDeviceId(), $limit, $page);
        $this->view->products   = $products;
        $this->view->pageNum    = $page;
        
        
        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();

        $this->view->baseUrlfo = $_SERVER ['SERVER_NAME'] . '/' . $controllerName . '/' . $actionName;
    }

    /**
     * Show only premium Applications
     */
    public function premiumAction() {
        // set session to get returinig url
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $_SERVER["REQUEST_URI"];
        // set categories
        $this->view->enableCategories = true;
        $this->setCategories();
        //set to view
        $this->view->categories = $this->_categories;

        $this->view->showPageTitle = false;
        $this->view->pageTitle = 'Premium Apps for ' . $this->getDeviceName();
        $product = new Model_Product();
        $product->setAppFilterRules($this->themeMeta->USER_ID);
        /**
         * Language is english, so lets make english apps a priority
         * This should only happen for english apps
         */
        $sessionLanguage = new Zend_Session_Namespace('application');
        if ($sessionLanguage->languageId == $sessionLanguage->defaultLangId) { 
            $product->languageId        = $sessionLanguage->languageId;
            $product->defaultLanguageId = $sessionLanguage->defaultLangId;
        }   
                
        $product->appFilter = $this->themeMeta->WHITELABLE_APP_FILTER;
        $limit      = Zend_Registry::get('config')->mobile->paginator->limit;
        $page       = $this->_getParam('page', 1); 
        $products   = $product->getPremiumAppsByDeviceId($this->getDeviceId(), $limit, $page);
        $this->view->products   = $products;
        $this->view->pageNum    = $page;
        

        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();
        // $paramQ = $this->getRequest()->getParam('q');
        if ($actionName == 'search')
            $search = '/q/' . $paramQ;

        $this->view->baseUrlfo = $_SERVER ['SERVER_NAME'] . '/' . $controllerName . '/' . $actionName;
    }

    /**
     * Category Product
     */
    public function catAction() {
        // set session to get returinig url
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $_SERVER["REQUEST_URI"];

        $categoryId = $this->_request->category;
        $this->view->categoryId = $categoryId;
        if (empty($categoryId)) {
            $this->_redirect('/app/#content');
        }
        
        $this->view->showPageTitle = false;
        
        $categoryModel  = new Model_Category();
        $breadcrumbs    = array();
        if (is_numeric($categoryId)) {
            $catid          = $categoryId; 
            
            $category       = $categoryModel->find($catid);
            $category       = $category->toArray();
            
            if (empty($category)) {
                $this->_redirect('/app/#content');
            }
            
            $this->view->category   = $category[0];
            $breadcrumbs[]  = $category[0];
            
            if ($category[0]['parent_id'] != 0) {
                $category   = $categoryModel->find($category[0]['parent_id']);
                $category       = $category->toArray();
                $breadcrumbs[]  = $category[0];
            }
            //won't be more than 2 levels deep so loop not required
        } else {
            $this->_redirect('/app/');
        }
        $this->view->breadcrumbs    = array_reverse($breadcrumbs);
        
        $this->view->pageTitle      = $categoryModel->getCatgoryNameById($categoryId) . ' for ' . $this->getDeviceName();
        $this->view->enableSearch   = true;
        $this->view->enableCategories = true;
        $this->view->selectedCategory = $categoryId;
        $this->setCategories();
        $this->view->categories = $this->_categories;
        $product = new Model_Product();
        $product->setAppFilterRules($this->themeMeta->USER_ID);
        $product->appFilter = $this->themeMeta->WHITELABLE_APP_FILTER; //and then override it with whitelable
        
        /**
         * Language is english, so lets make english apps a priority
         * This should only happen for english apps
         */
        $sessionLanguage = new Zend_Session_Namespace('application');
        if ($sessionLanguage->languageId == $sessionLanguage->defaultLangId) { 
            $product->languageId        = $sessionLanguage->languageId;
            $product->defaultLanguageId = $sessionLanguage->defaultLangId;
        }        
        
        //check for category filter
        $type   = $this->_getParam('type', false);
        if ($type && $this->themeMeta->WHITELABLE_APP_FILTER == 'ALL') {
            $product->appFilter = strtoupper($type); 
            $this->view->mode         = strtolower($product->appFilter);                  
        }
        
        $limit      = Zend_Registry::get('config')->mobile->paginator->limit;
        $page       = $this->_getParam('page', 1); 
        $products   = $product->getCategoryProducts($this->getDeviceId(), $categoryId, $limit, $page);
        
        $this->view->products   = $products;
        $this->view->pageNum    = $page;
        

        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();
        $paramCategory = $this->getRequest()->getParam('category');


        $this->view->baseUrlfo = $_SERVER ['SERVER_NAME'] . '/' . $controllerName . '/' . $actionName . '/category/' . $paramCategory;
    }

    //get purchased products
    public function purchasedAction() {
        // set session to get returinig url
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $_SERVER["REQUEST_URI"];
        // get the use id
        $userId = Zend_Auth::getInstance()->getIdentity()->id;
        if (empty($userId))
            $this->_redirect('/user/login/#content');

        $this->view->showPageTitle = true;
        $this->view->pageTitle = 'Purchased Apps for ' . $this->getDeviceName();
        
        /**
         * Hack to get a fix through quick
         * Purchases were not tagged to user (duh!) so I'm pulling 
         * from orders and then running them through getProductDetails
         * Need to refactor later.
         * _jp 
         */
        $product = new Model_Product();
        $products   = $product->getPurchasedProductsByDeviceId($this->getDeviceId(), $userId);
        $this->view->products = $this->enablePagenator($products);

        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();
        // $paramQ = $this->getRequest()->getParam('q');
        if ($actionName == 'search')
            $search = '/q/' . $paramQ;

        $this->view->baseUrlfo = $_SERVER ['SERVER_NAME'] . '/' . $controllerName . '/' . $actionName;
        $this->view->showToplinks = TRUE;
        $this->view->login = TRUE;
    }

    /**
     * Category Product
     */
    public function searchAction() {
        // set session to get returinig url
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $_SERVER["REQUEST_URI"];

        $keyword    = $this->_request->q;
        $category   = $this->_getParam('selcat', null);
        
        $this->view->keyword = $keyword;
        $this->view->showPageTitle = true;
        $this->view->searchKeyword = $keyword;
        if (!empty($keyword)) {
            $this->view->pageTitle = 'Search Results for "' . $keyword . '"';
        } else {
            $this->_redirect('/app');
        }
        
        $this->setCategories();        
        $this->view->enableCategories = true;
        $this->view->categories = $this->_categories;
 
        $product = new Model_Product();
        $product->setAppFilterRules($this->themeMeta->USER_ID);
        $product->appFilter = $this->themeMeta->WHITELABLE_APP_FILTER;
        
        $limit      = Zend_Registry::get('config')->mobile->paginator->limit;
        $page       = $this->_getParam('page', 1); 
        $products = $product->getSearchProductsByKey($this->getDeviceId(), $keyword, $category, $limit, $page);
        $this->view->products   = $products;
        $this->view->pageNum    = $page;

        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();
        $paramQ = $this->getRequest()->getParam('q');
        if ($actionName == 'search') {
            $search = '/q/' . $paramQ . '/selcat/' . $category;
        }

        $this->view->baseUrlfo = $_SERVER ['SERVER_NAME'] . '/' . $controllerName . '/' . $actionName . $search;
    }

    public function removeCodeAction() {
        $pid            = $this->_getParam('id', false);
        if ($pid) { 
            $promoCodeLib   = new Nexva_PromotionCode_PromotionCode();
            $promoCodeLib->clearPromotionCode();
            $this->_redirect('/app/show/id/' . $pid);
        } 
        $this->_redirect('/');
    }
    
    /**
     * Applies a promotion code
     */
    public function applyCodeAction() {
        $pid        = $this->_getParam('id', false);
        $codeId     = trim($this->_getParam('code', false));
        if ($pid && $codeId) {
            $promoCodeLib   = new Nexva_PromotionCode_PromotionCode();
            //URLWORQOKN
            if ($promoCodeLib->applyCode($codeId) && $promoCodeLib->checkPromotiocodeValidityForProduct($pid)) {
                $promoStats     = new Nexva_Analytics_PromotionCode();
                $appliedCode    = $promoCodeLib->getAppliedCode()->codeObject;
                $data   = array(
                    'app_id'        => $pid,
                    'code_id'       => $appliedCode['code'],
                    'code_owner_id' => $appliedCode['user_id'],  
                    'device_id'     => $this->getDeviceId(),
                    'device_name'   => $this->getDeviceName(),
                    'chap_id'       => isset($this->themeMeta->USER_ID) ? $this->themeMeta->USER_ID : null  
                );
                $promoStats->log($data);
            } else {
                $this->__addErrorMessage('Invalid promotion code');
            }
            $this->_redirect('/app/show/id/' . $pid);
        }
        
        $this->_redirect('/');
    }
    
    /**
     * show Full application detail
     */
    public function showAction() {

        
        //show categories
        $this->view->enableCategories   = true;
        $this->view->enableSearch       = true;
        // set no message
        $this->view->messages = array();
        // set session to get returinig url
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $_SERVER["REQUEST_URI"];
        $productModel = new Model_Product();
        $productModel->appFilter = $this->themeMeta->WHITELABLE_APP_FILTER; 
        $productId = $this->_request->id;

        $deviceSession = new Zend_Session_Namespace('Device');
        $deviceId = $deviceSession->deviceId;


        $product = $productModel->getProductDetailsById($productId);
                
        if (is_null($product) || (isset($product['deleted']) && $product['deleted'] == 1)) {
            $this->_redirect('/app/notfound/id/' . $productId);
        }
        $this->view->originialPrice = $product['cost']; 
                
        $deviceSession          = new Zend_Session_Namespace('Device');
        $this->view->osVersion  = $deviceSession->osVersion;

        // check the compatibility of the application with the device
	$builds = new Model_ProductBuild();
 	$buildMissing = $builds->getBuildByProductAndDevice($productId); 
        // check the compatibility of the application with the device
        if (!$buildMissing) {
            $this->view->warningMessage = true;
	        $buildProduct          = new Model_ProductBuild();
			$buildVersionInfo      = $buildProduct->getBuildByProductDeviceLanguageAndVersion($this->_request->id);
			if($buildVersionInfo)	{
     			$this->view->buildVersionInfo = $buildVersionInfo;
    		} else {
        		$buildInfo = $buildProduct->getBuildByProductDeviceAndLanguage($this->_request->id);
        		if($buildInfo) {
	        		$this->view->buildsInOtherLanguage   = true;
	        		$this->view->buildDetails            = $buildInfo;
        		}
    		}
        }
        
        // increment view count
        $statics = new Model_StatisticProduct();
        $chapId = isset ( $this->themeMeta->USER_ID ) ? $this->themeMeta->USER_ID : null;
        $deviceId = 0;
        if($this->getDeviceId()) {
            $deviceId    = $this->getDeviceId();    
        }    else    {
            $deviceId  = '-1';
        }
        $statics->updateProductStatistics($productId, $deviceId, $chapId, 'MOBILE');
        
       
        
        $breadcrumbs    = array();
        $tempCats       = $product['categories'];
        if (is_array($product['categories'])) {
            $catid          = array_pop($tempCats); //getting the last id which is usually the leaf
            $categoryModel  = new Model_Category();
            $category       = $categoryModel->find($catid);
            $category       = $category->toArray(); 
            if (!empty($category)) {
                $breadcrumbs[]  = $category[0];
                
                if ($category[0]['parent_id'] != 0) {
                    $category   = $categoryModel->find($category[0]['parent_id']);
                    $category       = $category->toArray();
                    $breadcrumbs[]  = $category[0];
                }    
            } 
            //won't be more than 2 levels deep so loop not required
        }
        $this->view->breadcrumbs    = array_reverse($breadcrumbs);
        
        $session->productId = $productId;
        // Set categories
        // @TODO : choose the correct category
        // if this is a URL product
        $this->view->isUrl = false;
        if ($product['content_type'] == 'URL') {
            $this->view->isUrl = true;
        }
            
        
         /** Promotion codes **/
        $promoCodeLib       = new Nexva_PromotionCode_PromotionCode();
        $promoSession       = $promoCodeLib->getAppliedCode();
        $promotionApplied   = false;
        $codeValid          = false;
        $promocode          = null;
        if ($promoSession->codeApplied === true && $promoSession->codeActive === true) {
            $promotionApplied   = true;
            $promocode          = $promoSession->codeObject;
            foreach ($promocode['products'] as $promocodeProduct) {
                if ($promocodeProduct->id == $productId) {
                    $codeValid          = true;
                    break;
                }
            }
        }
        
        if ($codeValid  == true) {
            $promoCodeType      = Nexva_PromotionCode_Factory::getPromotionCodeType($promocode['code']);
            $product['cost']    = $promoCodeType->applyPriceModification($product['cost']);
        }
        
        $this->view->promocode  = $promocode;
        $this->view->validCode  = $codeValid;
        
        //apply promotion codes    


        if (isset($product['categories'][1]))
            $this->view->selectedCategory = $product['categories'][1];
        $this->setCategories();
        $this->view->categories = $this->_categories;
        // check product is already downloaded then direct to download
        $order = new Model_Order();
        $this->view->purchased = false;
        if ($order->isPruchsed($productId))
            $this->view->purchased = true;
        $this->view->product = $product;
        // show page title
        $this->view->showPageTitle = false;
        // Page Title
        $this->view->pageTitle = $product['name'];
        $this->view->s3Object = $product['id'] . '/' . $product['file'];
        // get apps by same developer
        $this->view->appsByCp = $productModel->getAppsByCp($deviceId, $product['uid'], 4, $product['id']);
        
        /** Reviews **/
        $reviewModel    = new Model_Review();
        $reviews        = $reviewModel->getReviewsByContentId($productId, 2);
        $evasReviews    = array();
        if (!isset($this->themeMeta->WHITELABLE_EVA_SHOW_APP) || $this->themeMeta->WHITELABLE_EVA_SHOW_APP == 1) {
            $evasReviews    = $reviewModel->getReviewsByContentId($productId, 1, 'EVA');
        }
        $this->view->reviews    = $reviews;
        $this->view->evaReview  = $evasReviews;
        
        
        /***** Rating *****/
        
        //setup the new rating model and pull avg from there
        $ratingModel    = new Mobile_Model_Rating();        
        
       
        $this->view->avgRating      = $ratingModel->getAverageRatingByProduct($this->_request->id);    
        $this->view->totalRatings   = $ratingModel->getTotalRatingByProduct($this->_request->id);
        
        $rateCount = $ratingModel->checkProductRatedByUser(@$_SERVER['REMOTE_ADDR'],$this->_request->id);
        //$this->view->productId = $this->_request->id;
        
        $productRated = FALSE;
        
        if($rateCount > 0)
        {
            $productRated = TRUE;
        }
        else
        {
            $productRated = FALSE;
        }
                                        
       $this->view->productRated = $productRated;
       
       $this->view->messageSuccess = $this->_helper->flashMessenger->getMessages();

        $this->view->user_id = $this->themeMeta->USER_ID;
    }

    
    /**
     * GEt the recomended app list per add by get
     */
    public function recommendedAction() {
        $productId = $this->_request->id;
        $this->view->pageTitle = 'Recommended Apps';
        $deviceSession = new Zend_Session_Namespace('Device');
        $deviceId = $deviceSession->deviceId;
        $recommendationsObj = new Nexva_Recommendation_Recommendation($this->themeMeta->WHITELABLE_APP_FILTER);
        //first param is the id, second is the build and the third is the # of recommendations to return.
        $products = $recommendationsObj->getRecommendationsForProduct($productId, $deviceId, 10);
        $this->view->products = $this->enablePagenator($products);
        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();
        $this->view->baseUrlfo = $_SERVER ['SERVER_NAME'] . '/' . $controllerName . '/' . $actionName . '/id/' . $productId;
    }

    /**
     * Apps by CP
     */
    public function cpAction() {
        $productId = $this->_request->id;
        $deviceSession = new Zend_Session_Namespace('Device');
        $deviceId = $deviceSession->deviceId;
        $productModel = new Model_Product();
        $productModel->setAppFilterRules($this->themeMeta->USER_ID);
        $productModel->appFilter = $this->themeMeta->WHITELABLE_APP_FILTER;
        $product = $productModel->getProductDetailsById($productId);
        $this->view->pageTitle = 'Apps by ' . $product['user_meta']->COMPANY_NAME;
        
        $products = $productModel->getAppsByCp($deviceId, $product['uid'], 10, $productId);
        
        $this->view->products = $this->enablePagenator($products);
        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();
        $this->view->baseUrlfo = $_SERVER ['SERVER_NAME'] . '/' . $controllerName . '/' . $actionName . '/id/' . $productId;
    }
    
    public function cpproductsAction() {
        $cpId = $this->_request->id;
        
     
        $deviceSession = new Zend_Session_Namespace('Device');
        $deviceId = $deviceSession->deviceId;
        $productModel = new Model_Product();
        $productModel->appFilter = $this->themeMeta->WHITELABLE_APP_FILTER;
        $user = new Cpbo_Model_UserMeta ( );
        $user->setEntityId ( $cpId );

        $this->view->pageTitle = 'Apps by ' .  $user->COMPANY_NAME;
        
        $products = $productModel->getAppsByCp($deviceId, $cpId, 10);
        
        $this->view->products = $this->enablePagenator($products);
        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();
        $this->view->baseUrlfo = $_SERVER ['SERVER_NAME'] . '/' . $controllerName . '/' . $actionName . '/id/' . $cpId;
    }

    public function recentAction() {
        // set session to get returning url
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $_SERVER["REQUEST_URI"];
        $this->view->recent = TRUE;

        $this->view->showUtility = true;
        // set search
        $this->view->enableSearch = true;
        // set categories
        $this->view->enableCategories = true;
        $this->setCategories();
        //set to view
        $this->view->categories = $this->_categories;
        // Page Title
        $this->view->htmlTitle = 'Recent Apps for ' . $this->getDeviceName();
        // show page title
        $this->view->showPageTitle = true;
        // action body
        // echo "Load theme for user: ". $this->getRequest()->getParam('username', 'nexva') ;
        // get products by device ID
        $product = new Model_Product();
        $product->setAppFilterRules($this->themeMeta->USER_ID);
        $product->appFilter = $this->themeMeta->WHITELABLE_APP_FILTER;
        
        /**
         * Language is english, so lets make english apps a priority
         * This should only happen for english apps
         */
        $sessionLanguage = new Zend_Session_Namespace('application');
        if ($sessionLanguage->languageId == $sessionLanguage->defaultLangId) { 
            $product->languageId        = $sessionLanguage->languageId;
            $product->defaultLanguageId = $sessionLanguage->defaultLangId;
        }
        
        
        $products = $product->getRecentAppsByDeviceId($this->getDeviceId());
        $this->view->products = $this->enablePagenator($products);

        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();

        $this->view->baseUrlfo = $_SERVER ['SERVER_NAME'] . '/' . $controllerName . '/' . $actionName;
    }

    /**
     * Buy action
     */
    public function buyAction() {
        $this->view->enableSearch = true;
        $this->view->messages = array();
        $this->view->pageTitle = 'Choose a payment method';
        $this->view->showPageTitle = true;
        $paymentGatewaysModel = new Model_PaymentGateway();
        $paymentGateways = $paymentGatewaysModel->fetchAll('status = 1 AND supports_mobile = 1');
        if ($paymentGateways->count() == 1)
        	$this->_redirect('/app/checkout/id/'.$paymentGateways[0]->id);
        $this->view->paymentGateways = $paymentGateways;
    }

    /**
     * Checkout Action
     */
    public function checkoutAction() {
        // @TODO : add the download counter
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        // payment gateway ID
        $pgID = $this->_request->id;

        $session = new Zend_Session_Namespace('lastRequest');
        $session->paymentGateway = $pgID;

        // TODO : get the payment dateway name and send it to factory
        $payementGatewaysModel = new Model_PaymentGateway();
        $pgRow = $payementGatewaysModel->find($pgID);
        $pgRow = $pgRow->current();
        $factoryProduct = $pgRow->gateway_id;
        if ($factoryProduct == 'PayThru') {
            $factoryProduct = 'PayThru';
            $product = 'Paythru';
        }
        else
            $product = $factoryProduct;

        $paymentGateway = Nexva_PaymentGateway_Factory::factory($product, $factoryProduct);
//    $paymentGateway = Nexva_PaymentGateway_Factory::factory('GoogleCheckout', 'GoogleCheckout');
//    $paymentGateway = Nexva_PaymentGateway_Factory::factory('Paythru', 'PayThru');
        $userId = Zend_Auth::getInstance()->getIdentity()->id;
        if (empty($userId))
            $this->_redirect('/user/login/#content');

        // $sessionUser = new Zend_Session_Namespace ( 'mob_user' );
        $sessionUser = new Zend_Session_Namespace('api_user');
        $sessionUser->id = $userId;

        $userMeta = new Model_UserMeta();
        $userMeta->setEntityId($userId);
        // sucees url call back
        // session Id
        $sessionId = Zend_Session::getId();
        
        $product = new Model_Product();
        $prodcutDetails = $product->getProductDetailsById($session->productId);

        $postback = "http://" . $_SERVER['SERVER_NAME'] . '/app/postbackpayment/?sessionid=' . $sessionId;

        // this is to update the user entered user infomation upon payment
        $callback_url_pay = "http://" . $_SERVER['SERVER_NAME'] . "/app/update-user-info/?userid=" . $userId;
        
        $cancel_return_url = "http://" . $_SERVER['SERVER_NAME']. "app/show/id/".$prodcutDetails['id']."/#content";

        //get product details

        $vars = array(
          'currency' => 'USD',
          'salutation' => $userMeta->TITLE,
          'first_name' => $userMeta->FIRST_NAME,
          'surname' => $userMeta->LAST_NAME,
          'mobile_phone' => $userMeta->MOBILE,
          'address_1' => $userMeta->ADDRESS,
          'town' => $userMeta->CITY,
          'postcode' => $userMeta->ZIPCODE,
          'version' => 2,
          'token_uses' => 1,
          'success_url' => $postback,
          'callback_url' => $callback_url_pay,
          'cancel_return' => $cancel_return_url,
          'item_name' => $prodcutDetails['name'],
          'item_desc' =>  $prodcutDetails['name'],
          'item_price' =>  $prodcutDetails['cost'],
          'item_reference' => $prodcutDetails['id']
        );

        /**
         * Apply promotion code
         */
        $promoCodeLib     = new Nexva_PromotionCode_PromotionCode();
        $validCode        = $promoCodeLib->checkPromotiocodeValidityForProduct($session->productId);
        if ($validCode && $prodcutDetails['content_type'] != 'URL') {
            $promocodeSession       = $promoCodeLib->getAppliedCode();
            $promocode              = $promocodeSession->codeObject;
            $promoCodeType          = Nexva_PromotionCode_Factory::getPromotionCodeType($promocode['code']);
            $vars['item_price']     = $promoCodeType->applyPriceModification($prodcutDetails['cost']);//apply new price                        
        }
        $paymentGateway->Prepare($vars);
        $paymentGateway->Execute();
    }

    /**
     * Postback payment action
     * @return <type>
     */
    public function postbackpaymentAction() {
        
     
        
        // @TODO :  Send them the licence Key
        // Send them an email
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        $config = Zend_Registry::get('config');
        $transId = $this->_request->transkey;
       
        $sessionId = $this->_request->sessionid;
        // recreate the session
        session_id($sessionId);
        // Save the Transaction Id to the database
        // get user Id
 
        // if token is not empty then this is processed though the paypal mobile
        if(!empty($this->_request->token))  {
            
            // values should pass to paypal to validate the token 
            define ( 'API_USERNAME', $config->paypal->mobile->merchant_id );
            define ( 'API_PASSWORD', $config->paypal->mobile->merchant_password );
            define ( 'API_SIGNATURE', $config->paypal->mobile->merchant_key );
            define ( 'API_ENDPOINT', $config->paypal->mobile->endpoint_url );
            
            if (APPLICATION_ENV == 'production')
                define ( 'PAYPAL_URL', 'https://www.sandbox.paypal.com/wc?t=' );
            else
                define ( 'PAYPAL_URL', 'https://www.sandbox.paypal.com/wc?t=' );
            
            define ('USE_PROXY',FALSE);
            define ('VERSION', '3.0');
            
            // validate the token with the paypal and fetch the payment info
            $resArray = hash_call ( 'DoMobileCheckoutPayment', '&token=' . $this->_request->token );
            
            // run an if against the ACK value - it'll return either SUCCESS or FAILURE
            if (strtoupper ( $resArray ['ACK'] ) == 'SUCCESS') {
                //echo 'DoMobileCheckoutPayment success: ' . $resArray ['CURRENCYCODE'] . ' ' . $resArray ['AMT'] . ' from ' . $resArray ['EMAIL'] . ' 
                //  (' . $resArray ['FIRSTNAME'] . ' ' . $resArray ['LASTNAME'] . ')';
                $transId = $resArray ['TRANSACTIONID'];

            // ends SUCCESS 
            } else {
                // DoMobileCheckoutPayment failed
                echo 'DoMobileCheckoutPayment failed ' . $resArray ['L_SHORTMESSAGE0'] . ' ' . $resArray ['L_ERRORCODE0'] . ' ' . $resArray ['L_LONGMESSAGE0'];
                die();    
            }
        }
        
        $userId = Zend_Auth::getInstance()->getIdentity()->id;
        // get product id from session
        $session = new Zend_Session_Namespace('lastRequest');

        $payementGatewaysModel = new Model_PaymentGateway();
        $pgRow = $payementGatewaysModel->find($session->paymentGateway);
        $pgRow = $pgRow->current();
        $paymentGateway = $pgRow->gateway_id;

        $productId = $session->productId;
        if (empty($productId))
            $this->_redirect($session->lastRequestUri);
            
        $pgId = $session->paymentGateway;
        // unset the product Id
        unset($session->productId);
        // check the product already exists on the order details table
        $orders = new Model_Order();
        //  if product id is already in the table then redirec to download file
        if ($orders->isPruchsed($productId))
            return;

        $product        = new Model_Product();
        $productDetail  = $product->getProductDetailsById($productId, true);
        // Orders

        /**
         * Apply promotion code
         */
        $promoCodeLib     = new Nexva_PromotionCode_PromotionCode();
        $promocode        = null;
        $validCode        = $promoCodeLib->checkPromotiocodeValidityForProduct($productId, $promoCodeLib->getAppliedCode());
        $newPrice         = null;
        $promoCodeType    = null;
        if ($validCode && $productDetail['content_type'] != 'URL') {
            $promocodeSession       = $promoCodeLib->getAppliedCode();
            $promocode              = $promocodeSession->codeObject;
            $promoCodeType          = Nexva_PromotionCode_Factory::getPromotionCodeType($promocode['code']);
            $newPrice               = $promoCodeType->applyPriceModification($productDetail['cost']);//apply new price                        
        }
        
        /**
         * This stuff shouldn't be done here, but can't really move it at this point
         * so adding a transaction around it to make sure we don't have loose ends 
         */
        $db     = Zend_Registry::get('db');
        $db->beginTransaction();
        
        try {
            $orderData = array(
              'user_id'     => $userId,
              'order_date'  => new Zend_Db_Expr('NOW()'),
              'merchant_id' => $paymentGateway,
              'transaction_id' => $transId
            );
            if ($promocode) {
                $orderData['promo_id']  = $promocode['id'];
            }
            
            $orderId = $orders->insert($orderData);
            // Orders details
            $orderDetails = new Model_OrderDetail();
            $orderDetailsData = array(
              'order_id'    => $orderId,
              'product_id'  => $productDetail['id'],
              'price'       => $productDetail['cost']
            );
        
            
            $payout     = null;
            if ($promocode) {
                $orderDetailsData['price']  = $newPrice;
                $promoCodeType->doPostProcess($productId);
                
                //If the user paid anything debit the CP
                if ($newPrice) { //this should never be 0/null here, but just in case
                    $payoutModel    = new Model_Payout();
                    if ($promocode['payout_id']) {
                        $payout         = $payoutModel->fetchRow('id = ' . $promocode['payout_id']);                     
                    } else {
                        $payout         = $payoutModel->getPayoutForUser($productDetail['uid']);
                    }
                }
                //Make sure to use the proper payout system
            } 
            
            $orderDetails->insert($orderDetailsData);
            $userAccount = new Model_UserAccount();
            
            $chapId = isset($this->themeMeta->USER_ID) ? $this->themeMeta->USER_ID : null;
            
            $userAccount->saveRoyalities($productId, $pgId, 'MOBILE', $payout, $promoCodeType, $chapId);
    
            $data = array(
              'order_id'        => $orderId,
              'transaction_id'  => $transId,
              'payment_gateway' => $paymentGateway
            );
    
            $orders->sendInvoice($productId, $data);
            $orders->sendNotificationOfPurchase($orderId);
            
            $db->commit();
            
            $promoStats = new Nexva_Analytics_ProductPurchase();
            $data   = array(
                'app_id'    => $productDetail['id'], 
                'price'     => $productDetail['cost'], 
                'device_id' => $this->getDeviceId(),
                'device_name'   => $this->getDeviceName(),
                'chap_id'   => isset($this->themeMeta->USER_ID) ? $this->themeMeta->USER_ID : null,
                'platform'  => 'MOBILE'
            );
            if (isset($promocode['code'])) {
                $data['code']           = array(
                    'code_id'       => $promocode['id'],    
                    'code'          => $promocode['code'],
                    'code_owner_id' => $promocode['user_id']
                ); 
            }
            $promoStats->log($data);
            
            $promoCodeLib->clearPromotionCode();
        } catch (Exception $ex) {
            $db->rollback();
            
            $msg    = $ex->getTraceAsString();
            $msg    .= print_r($orderData, true);
            $msg    .= "Order saving failed" . $msg;
            $mailer     = new Nexva_Util_Mailer_Mailer();
            $mailer->setSubject('An exception was thrown during order save');
            $mailer->addTo(explode(',', Zend_Registry::get('config')->nexva->application->dev->contact))
                ->setLayout("generic_mail_template")     
                ->setMailVar("error", $msg);
                
            if (APPLICATION_ENV == 'production') {
                $mailer->sendHTMLMail('error_report.phtml');  
            } else {
                echo $mailer->getHTMLMail('error_report.phtml');
                die();
            }
            $this->__addErrorMessage('An error occured while trying to save your order. Please contact support');
            $this->_redirect('/');
            exit();
        } 
        

        // @ TODO : check all success
//        $this->_redirect($session->lastRequestUri);
        $this->_redirect('/app/show/id/' . $productId . '/#content');
    }

    /**
     * Download action
     */
    public function downloadAction() {
       
        set_time_limit(0);
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        $productId  = $this->_request->id;
        $testing  = isset($this->_request->t)? $this->_request->t :0;
        $languageId = $this->_getParam('language_id', false);
        

        // mobparter affiliate implementation
        // @TODO : add this to factory/observer
        $nexvaWidgetId = Zend_Registry::get("config")->nexva->application->widget->id;
        if ($nexvaWidgetId == $productId) 
        {
            $affiliateSession = new Zend_Session_Namespace('affiliate');
            $baseUrl = Zend_Registry::get("config")->nexva->application->base->url;
            $cookieValue = FALSE;
            $cookieValue = isset($_COOKIE['affiliate']);
            if (isset($affiliateSession->code) && !$cookieValue) {
                $affiliateCode = $affiliateSession->code;
                unset($affiliateSession->code);
                $mobPartner = new Nexva_Affiliate_MobPartner($affiliateCode, $productId);
                $mobPartner->track();
                setcookie("affiliate", TRUE, (time() + (3600 * 24 * 30)));

            }
        }
        
        $product = new Model_Product();
        $status=$product->getProductStatusById($productId);
        /* Does not allow to download un-approved content*/
        
        if(!in_array($status, array("APPROVED")) && $testing !=1 ){
            Die();
        }
        /* End */
        // redirect to download page
        $chapId = isset ( $this->themeMeta->USER_ID ) ? $this->themeMeta->USER_ID : null;
        
        $accessNs           = new Zend_Session_Namespace('Access');
        $sessionSet         = isset($accessNs->isAuthorized) ? $accessNs->isAuthorized : false;
        if ($sessionSet) {
            $chapId = null; //we don't want downloads to be tracked against CHAPS IF the access phrase has been set
        }
        
        //if the language is set this means default language build is not found so this is a request to download other language build
        
        $np = $this->_getParam('np', false);
        
        //Fetch the S3 app URL to be downloaded
        $url               = $product->downloadProduct($productId, $chapId, true, true, $languageId, $np);

    	$contentType       = $product->getProductContentType($productId, $languageId);
           
    	if ($url != '/app/buy') { //there doesn't seem to be any other way to do it. 
    	    //All I want to do is, to remove the promocode after download
    	    $promoCodeLib     = new Nexva_PromotionCode_PromotionCode(); 
            $promoCodeLib->clearPromotionCode();
    	}
	

    	
        //This is to chek if the app has a build or just a URL to Itunes/google  play
        if($contentType != 'URL') {
            
       
		
            $type       = $product->getDownloadFileType($productId, $languageId);
            $fileName   = $product->getDownloadFile($productId, $languageId);

            if ($type == 'png' or $type == 'jpg' or $type == 'jpeg' or $type == 'gif') 
            {
                $this->_redirect($url);
                
                header('Content-Description: File Transfer');
                header('Content-Type: image/' . $type);
                header('Content-Disposition: attachment; filename=' . $fileName);
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($url));
                ob_clean();
		flush ();
		readfile($url);
		exit ();
            }
            else 
            {                
                $this->_redirect($url);
            }
	} else {
            $this->_redirect($url);
        }

			
            
    }

    public function notcompatibleAction() {
        $deviceSession = new Zend_Session_Namespace('Device');
        $deviceId = $deviceSession->deviceId;
        $this->view->enableCategories = true;
        $this->view->showPageTitle = false;
        $this->view->pageTitle = $this->getDeviceName();
        $this->setCategories();
        $this->view->categories = $this->_categories;
        $productId = $this->_request->id;
        $productModel = new Model_Product();
        $product = $productModel->getProductDetailsById($productId);
        $this->view->appName = $product['name'];
        $this->view->product = $product;
        $this->view->msg = ' is not compatible with your device (' . $this->getDeviceName() . ')';
        $this->view->products = $productModel->getAppsByCp($deviceId, $productId, 4, $productId);
    }

    public function notfoundAction() {
        $this->view->enableCategories = true;
        $this->view->showPageTitle = false;
        $this->view->pageTitle = 'Content not found.';
        $this->setCategories();
        $this->view->categories = $this->_categories;
        $productId = $this->_request->id;
        $productModel = new Model_Product();
        $product = $productModel->getProductDetailsById($productId);
        $this->view->messages = array('Content not found.');
        $this->view->product = $product;
        $this->view->deviceName = $this->getDeviceName();
        $this->view->productId =  $productId;
        
        $deviceSession = new Zend_Session_Namespace('Device');
        $this->view->osVersion = $deviceSession->osVersion;
        
        $buildProduct = new Model_ProductBuild();
     
        
        $buildVersionInfo = $buildProduct->getBuildByProductDeviceLanguageAndVersion($this->_request->id);
        
        if($buildVersionInfo)	{
         	$this->view->buildVersionInfo = $buildVersionInfo;
        }	else	{
	        $buildInfo = $buildProduct->getBuildByProductDeviceAndLanguage($this->_request->id);
	        $sessionLanguage = new Zend_Session_Namespace('application');
	        $languageId = $sessionLanguage->language_id;
	        
	        $language = new Model_Language();

	        $languageName =  $language->getLanguageByid($languageId);
	        
	        $this->view->currentLanguage = $languageName->name;
	        $this->view->buildInfo = $buildInfo;
        }
        
    }

    public function updateUserInfoAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if (empty($this->_request->userid))
            die ();

        $userId = $this->_request->userid;


        $userMeta = new Model_UserMeta ( );

        if (!empty($this->_request->user_title))
            $userMeta->setAttributeValue($userId, 'TITLE', $this->_request->user_title);

        if (!empty($this->_request->user_first_name))
            $userMeta->setAttributeValue($userId, 'FIRST_NAME', $this->_request->user_first_name);

        if (!empty($this->_request->user_surname))
            $userMeta->setAttributeValue($userId, 'LAST_NAME', $this->_request->user_surname);

        if (!empty($this->_request->address_address_1))
            $userMeta->setAttributeValue($userId, 'ADDRESS', $this->_request->address_address_1);

        if (!empty($this->_request->user_mobile_number))
            $userMeta->setAttributeValue($userId, 'MOBILE', $this->_request->user_mobile_number);

        if (!empty($this->_request->address_county))
            $userMeta->setAttributeValue($userId, 'COUNTRY', $this->_request->address_county);

        if (!empty($this->_request->address_town))
            $userMeta->setAttributeValue($userId, 'CITY', $this->_request->address_town);

        if (!empty($this->_request->address_postcode))
            $userMeta->setAttributeValue($userId, 'ZIPCODE', $this->_request->address_postcode);
    }

    /**
     *
     */
    public function androidAction()
    {
        // set session to get returning url
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $_SERVER["REQUEST_URI"];
        $this->view->android = TRUE;

        $product = new Model_Product();
        $products = $product->getAndroidAppsByDeviceId($this->getDeviceId());
        //Zend_Debug::dump($products);die();
        $this->view->products = $this->enablePagenator($products);

        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();
        $this->view->baseUrlfo = $_SERVER ['SERVER_NAME'] . '/' . $controllerName . '/' . $actionName;
    }

}

?>
