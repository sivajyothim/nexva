<?php

class Partnermobile_IndexController extends Nexva_Controller_Action_Partnermobile_MasterController {
	
    public function init() 
    {
        parent::init(); 
            
    }
    
     public function ipAction() {
    
    
    	$_SERVER['HTTP_USER_AGENT'];
    
    	$to = 'chathura@nexva.com';
    	$subject = "neXva test";
    	$txt = "neXva test";
    	        $headers = "From: chathura@nexva.com" . "\r\n" .
        		"CC: luqmany@mtnnigeria.net, mustafG@mtnnigeria.net, masudm@mtnnigeria.net, AbioduAk@mtnnigeria.net, TitiloB@mtnnigeria.net,surajuA@mtnnigeria.net, boluwaf@mtnnigeria.net, kehindomo@mtnnigeria.net";
    	
    	        
    	        
    	        
    	Zend_Debug::dump($_SERVER);
    	 
    //	$a = var_export($_SERVER);
    
    	$dump = var_export($_SERVER, true);
    
    	mail($to,'User-Agent',$dump,$headers);
    
    	die('<b>Thank you</b>');
    
    }
    
    public function testAction() {
        
        
        $deviceDetection =  Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
        $deviceInfo = $deviceDetection->getNexvaDeviceId($_SERVER['HTTP_USER_AGENT']);
        //If this is not a wireless device redirect to the main site
        $deviceId = $deviceInfo->id;
        $session->deviceId = $deviceId;
        $session->is_check = true;
        

        
        	Zend_Debug::dump($deviceInfo);
        	die();

        
    
    //	$_SERVER['HTTP_USER_AGENT'];
    
    	$to = 'chathura@nexva.com';
    	$subject = "neXva test";
    	$txt = "neXva test";
    	$headers = "From: chathura@nexva.com" . "\r\n" .
    			"CC: ";
    	
    	Zend_Debug::dump(apache_request_headers());
    	//Zend_Debug::dump($_SERVER);
    
    	//	$a = var_export($_SERVER);
    
    	$dump = var_export($_SERVER, true);
    
    	mail($to,'User-Agent',$dump,$headers);
    
    	die('<b>Thank you</b>');
    
    
    
    }
    
   
    public function indexAction() 
    {

        $deviceId = $this->_deviceId;
        $chapId = $this->_chapId;
        $isLowEndDevice = $this->_isLowEndDevice;
    	
        $chapProducts = new Partner_Model_ChapProducts();
        
        $noOfStaffPicks = !empty($this->_featuredApps) ? $this->_featuredApps : 15;
        $noOfBanners = !empty($this->_bannerCount) ? $this->_bannerCount : 6;                  
        
        /************** Bannered apps ******************************/
        
        $featuredApps = $chapProducts->getBanneredProductsbyDevice($this->_chapId, $deviceId, $noOfBanners, '', $this->_grade);

        //get Product details of bannered apps
        if (count($featuredApps) > 0) 
        {
            $featuredApps = $this->getProductDetails($featuredApps, $deviceId);
        }

        /************** Featured apps = Staff Picks (previously) ******************************/
       
        $staffPicks = $chapProducts->getFeaturedProductsbyDevice($this->_chapId, $deviceId, $noOfStaffPicks, '', $this->_grade);
        //get Product details of feaatured apps
        if (count($staffPicks) > 0) 
        {
            $staffPicks = $this->getProductDetails($staffPicks, $deviceId);
        }

        $this->view->featuredApps = $featuredApps;
        $this->view->staffPicks = $staffPicks;
        
        $this->view->isLowEndDevice = $isLowEndDevice;

        //currency details by chap
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
        $this->view->currencyDetails = $currencyDetails;
        
        //Get the device OS
        $deviceModel = new Model_Device();
        $devicePlatform = $deviceModel->getDevicePlatformById($this->_deviceId);
        $this->view->devicePlatform = $devicePlatform;
        
        //Get the Appstore app details
        $themeMeta  = new Model_ThemeMeta();
        $themeMeta->setEntityId($this->_chapId);
        $chapInfo  = $themeMeta->getAll();
        $this->view->chapInfo = $chapInfo;
        $appstoreAppId = $chapInfo->APPSTORE_APP_ID;
        
        $nexApi = new Nexva_Api_NexApi();
        $appStoreAppDetails = $nexApi->detailsAppAction($appstoreAppId);
        $this->view->appStoreAppDetails = $appStoreAppDetails;
        
        //If user can download free apps with out logging
        $this->view->allowFreeDownload = ($this->_userCanDownloadFree || ($this->_headerEnrichment)) ? TRUE : FALSE;
    }

    public function helpAction()
    {
        $isLowEndDevice = $this->_isLowEndDevice;

        if($isLowEndDevice):
            $this->_helper->viewRenderer('help-low-end');
        endif;
    }
    
    
    public function aboutusAction()
    {
        
        
        $pageModel = new Partner_Model_WlPage();
        $pageDetails = $pageModel->getPageDetailsByTitle($this->_chapId, 'About Us', $this->_chapLanguageId);
        
        $this->view->pageDetails = $pageDetails;
        $this->view->pageName = isset($pageDetails->title) ? $pageDetails->title : '';
  
        
    	$isLowEndDevice = $this->_isLowEndDevice;
    
    	if($isLowEndDevice):
    	$this->_helper->viewRenderer('aboutus-low-end');
    	endif;
    }
    
    public function termsAction()
    {
    	$pageModel = new Partner_Model_WlPage();
        $pageDetails = $pageModel->getPageDetailsByTitle($this->_chapId, 'Terms & Conditions', $this->_chapLanguageId);
        
        $this->view->pageDetails = $pageDetails;
        $this->view->pageName = isset($pageDetails->title) ? $pageDetails->title : '';
  
        
    	$isLowEndDevice = $this->_isLowEndDevice;
    
    	if($isLowEndDevice):
    	$this->_helper->viewRenderer('terms-low-end');
    	endif;
    }

    /**
     * serach apps for front search box
     */
    public function searchAction()
    {
        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');
        
        $isLowEndDevice = $this->_isLowEndDevice;
                
        $keyword    = $this->_request->q;
        $this->view->keyword = $this->view->escape($keyword);

        if (!empty($keyword)) {
            $this->view->pageTitle = $translate->translate('Search Results for').' "'. $keyword . '"';
        } else {
            $this->_redirect('/');
        }

        /*$product = new Model_Product();

        $page       = $this->_getParam('page', 1);
        $this->view->deviceID = $this->_deviceId;
        $products = $product->getSearchProductsByKey($this->_deviceId, $keyword, NULL, NULL, $page);

        $pagination = Zend_Paginator::factory($products);
        $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
        $pagination->setItemCountPerPage(10);

        //$clsNexApi =  new Nexva_Api_NexApi();
        //$test = $clsNexApi->getProductDetails($pagination, $this->_deviceId);

        $this->view->products = $pagination;*/

        $chapId = $this->_chapId;

        $productModel = new Partnermobile_Model_Products();
        $productsModel  = new Model_Product();
        
        $this->view->deviceID = $this->_deviceId;
        //$products = $productModel->getSearchProductsByKey($this->_deviceId, $keyword, $chapId, NULL, NULL, NULL);
        $products = array();
        //Get the records from product_language_meta 
        
        /* commented out by chathura to support orange
        if($this->_chapLanguageId != 1){
            //Added as same as the partner web
            //$products = $productsModel->getSearchQueryPartnerWithDeviceProductLangMeta( $keyword,$this->_deviceId, false, $chapId, 10, true, $this->_chapLanguageId );
            
            $products = $productModel->getSearchProductsByKeyProductLangMeta($this->_deviceId, $keyword, $chapId, NULL, NULL, NULL, $this->_chapLanguageId);
        }

        if(count($products) < 1){
            //Added as same as the partner web
            //$products = $productsModel->getSearchQueryPartnerWithDevice( $keyword,$this->_deviceId, false, $chapId, 10, true );
            
            $products = $productModel->getSearchProductsByKey($this->_deviceId, $keyword, $chapId, NULL, NULL, NULL);
        }
        
        */
        
        $products = $productModel->getSearchProductsByKey($this->_deviceId, $keyword, $chapId, NULL, NULL, NULL);

        $pagination = Zend_Paginator::factory($products);
        $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
        $pagination->setItemCountPerPage(10);

        $this->view->products = $pagination;

        //currency details by chap
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
        $this->view->currencyDetails = $currencyDetails;

        if($isLowEndDevice):
            $this->_helper->viewRenderer('search-result-low-end');
        endif;

    }
    
    // load the static menu view
    public function mainMenuAction()
    {
        
    }
    
    // load the static menu view
    public function searchLowEndAction()
    {
        
    }

    
    /**
     * Returns product details based on product IDs
     * @param $products product ids array
     * @param $deviceId Device ID 
     */    
    public function getProductDetails($products, $deviceId)
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
        $productModel = new Model_Product();
        $productLanguageMeta = new Model_ProductLanguageMeta();

        foreach ($products as &$product) 
        { 
                        
            /* 
             * If chapter language id is not equal to english (1) get the app build for chap's default language
             * Else get the english language build
             */
            
            $apiModelChapProducts = new Api_Model_ChapProducts();

            if($this->_chapLanguageId != 1):
                $buildId = $apiModelChapProducts->getProductBuildChapIdByLanId($product['product_id'], $this->_deviceId, $this->_chapLanguageId);
                if(!empty($buildId)):
                    $product['build_id'] =  $buildId;
                endif;
            endif;            
            
            $product["thumbnail"] = $visualPath . '/' . $product["thumbnail"];

            $productMeta->setEntityId($product['product_id']);
            
            /* Add translation. 
             * Get the translations for product name,description,brief_description and overite if the translations are available.
             * If not let it be with english language.
             */
            $productTranslation = $productLanguageMeta->loadTranslation($product['product_id'], $this->_chapLanguageId);

            //this is temporary for MTN Iran cell
            /* @todo: needs to be removed once we bags all translations
             */
            //if(23045 != $this->_chapId){
                $product["name"] = ($productTranslation->PRODUCT_NAME)? stripslashes(strip_tags($productTranslation->PRODUCT_NAME)) : $product["name"];
            //}

            $product["description"] = ($productTranslation->PRODUCT_DESCRIPTION)? stripslashes(strip_tags($productTranslation->PRODUCT_DESCRIPTION)) : stripslashes(strip_tags($productMeta->FULL_DESCRIPTION));
            $product["brief_description"] = ($productTranslation->PRODUCT_SUMMARY)? stripslashes(strip_tags($productTranslation->PRODUCT_SUMMARY)) : stripslashes(strip_tags($productMeta->BRIEF_DESCRIPTION));

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
            
            $product['supported_platforms'] = $productModel->getSupportedPlatforms($product['product_id']);

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
    
    //Redirect to this when device is not detected
    public function deviceNotFoundAction()
    {
        
    }
    
    public function ipTestAction() {
    
    
    	$_SERVER['HTTP_USER_AGENT'];
    
    	$to = 'chathura@nexva.com';
    	$subject = "neXva test";
    	$txt = "neXva test";
    	$headers = "From: chathura@nexva.com" . "\r\n" .
    			"CC: luqmany@mtnnigeria.net, mustafG@mtnnigeria.net, masudm@mtnnigeria.net, AbioduAk@mtnnigeria.net, TitiloB@mtnnigeria.net,surajuA@mtnnigeria.net, boluwaf@mtnnigeria.net, kehindomo@mtnnigeria.net";
    	 
    	 
    	 
    	 
    	Zend_Debug::dump($_SERVER);
    
    	//	$a = var_export($_SERVER);
    
    	$dump = var_export($_SERVER, true);
    
    	mail($to,'User-Agent',$dump,$headers);
    
    	die('<b>Thank you</b>');
    
    
    
    }
    
}