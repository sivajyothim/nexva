<?php

class Partner_AppController extends Nexva_Controller_Action_Partner_MasterController {

    public function init() {
        parent::init();
    }

    public function detailAction() 
    {
        $productId = $this->getRequest()->getParam('id', 1);
        $nexApi = new Nexva_Api_NexApi();
        $productDetails = $nexApi->detailsAppAction($productId,null,$this->_chapId,$this->_chapLanguageId);
        

        $reviewModel = new Model_Review();
        $reviews = $reviewModel->getReviewsByContentId($this->_request->id);

        $this->view->pageName = $productDetails['name'];
        $this->view->reviews = $reviews;
        $this->view->product = $productDetails;

        $themeMeta = new Model_ThemeMeta();
        $themeMeta->setEntityId($this->_chapId);
        $this->view->chapInfo = $themeMeta->getAll();

        $productBuildModel = new Partner_Model_ProductBuilds();
        $avgApproved = $productBuildModel->getAvgApproved($productId);
        $this->view->avgApproved = $avgApproved;

        /************* Add Statistics - Views ************************ */
        $source = "WEB";
        $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');

        $modelViewStats = new Api_Model_StatisticsProducts();
        
         /*
         * same ip, same product, same device, same chap stats are not allowed to insert
         */
        $rowView = $modelViewStats->checkThisStatExist($productId, $this->_chapId, $source, $ipAddress);
        //echo $rowBuild['exist_count']; die();

        //If the record not exist only stat will be inserted
        if($rowView['exist_count'] == 0){
            $modelViewStats->addViewStat($productId, $this->_chapId, $source, $ipAddress, NULL, $this->_userId);
        }

        /********************End Statistics ***************************** */

        //------------------get Download Counts--------------------------
        $statisticsDownloadsModel = new Api_Model_StatisticsDownloads();
        $downloads = $statisticsDownloadsModel->getDownloadCountByAppChap($productId, $this->_chapId);
        $this->view->downloads = $downloads;

        //------------------get View Counts------------------------------
        $statisticsProductsModel = new Api_Model_StatisticsProducts();
        $views = $statisticsProductsModel->getViewCountByAppChap($productId, $this->_chapId);
        $this->view->views = $views;

        $chap = new Zend_Session_Namespace('partner');
        $this->view->chap_id = $chap->id;

    }

    //Free apps
    public function freeAction() 
    {
        $chap = new Zend_Session_Namespace('partner');
        $this->view->chap_id = $chap->id;

        $productsModel = new Model_Product();
        $chapProducts = new Partner_Model_ChapProducts();

        if ($this->getDeviceId()) {

            $products = $chapProducts->getFreeProductsByDevice($this->_chapId, $this->getDeviceId(), $this->_grade);
        } else {

            $products = $chapProducts->getFreeProducts($this->_chapId, $this->_grade);
        }

        $paginator = Zend_Paginator::factory($products);
        $paginator->setItemCountPerPage($this->_productsPerPage);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

        $productsDisplay = array();

        if (!is_null($products)) {
            foreach ($paginator as $row) {
                $productsDisplay[] = $productsModel->getProductDetailsById($row->product_id,FALSE,$this->_chapLanguageId,NULL,$this->_chapId);
            }
        }


        if ($this->_request->device_id) {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/free/page/' . $categoryId . $this->_request->device_id;
        } else {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/free/page/';
        }


        $this->view->products = $productsDisplay;
        $this->view->paginator = $paginator;

        $this->view->pageName = 'Free Apps';
    }

    //Premium apps
    public function premiumAction() 
    {
        $chap = new Zend_Session_Namespace('partner');
        $this->view->chap_id = $chap->id;

        $productsModel = new Model_Product();
        $chapProducts = new Partner_Model_ChapProducts();

        if ($this->getDeviceId()) {

            $products = $chapProducts->getPremiumProductsByDevice($this->_chapId, $this->getDeviceId(), $this->_grade);
        } else {

            $products = $chapProducts->getPremiumProducts($this->_chapId, $this->_grade);
        }



        $paginator = Zend_Paginator::factory($products);
        $paginator->setItemCountPerPage($this->_productsPerPage);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

        $productsDisplay = array();

        if (!is_null($products)) {
            foreach ($paginator as $row) {
                $productsDisplay[] = $productsModel->getProductDetailsById($row->product_id,FALSE,$this->_chapLanguageId,NULL,$this->_chapId);
            }
        }


        if ($this->_request->device_id) {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/premium/page/' . $categoryId . $this->_request->device_id;
        } else {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/premium/page/';
        }


        $this->view->products = $productsDisplay;
        $this->view->paginator = $paginator;

        $this->view->pageName = 'Premium Apps';
    }

    //Newest apps
    public function newestAction() 
    {
        $chap = new Zend_Session_Namespace('partner');
        $this->view->chap_id = $chap->id;

        $productsModel = new Model_Product();
        $chapProducts = new Partner_Model_ChapProducts();        

        if ($this->getDeviceId()) {

            $products = $chapProducts->getNewestProductsByDevice($this->_chapId, $this->getDeviceId(), $this->_grade, $this->_userType);
        } else {

            $products = $chapProducts->getNewestProducts($this->_chapId, '', $this->_grade, $this->_userType);

        }

        $paginator = Zend_Paginator::factory($products);
        $paginator->setItemCountPerPage($this->_productsPerPage);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

        $productsDisplay = array();

        if (!is_null($products)) {
            foreach ($paginator as $row) {
                $productsDisplay[] = $productsModel->getProductDetailsById($row->product_id,FALSE,$this->_chapLanguageId,NULL,$this->_chapId);
            }
        }



        if ($this->_request->device_id) {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/newest/page/' . $categoryId . $this->_request->device_id;
        } else {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/newest/page/';
        }


        $this->view->products = $productsDisplay;
        $this->view->paginator = $paginator;

        $this->view->pageName = 'Newest Apps';
    }

    //Featured apps
    public function featuredAction() 
    {
        $chap = new Zend_Session_Namespace('partner');
        $this->view->chap_id = $chap->id;

        $productsModel = new Model_Product();        
        $chapProducts = new Partner_Model_ChapProducts();

        if ($this->getDeviceId()) {

            $products = $chapProducts->getFeatureProductsbyDevice($this->_chapId, $this->getDeviceId(), $this->_grade, $this->_userType);
        } else {

            $products = $chapProducts->getFeaturedProducts($this->_chapId, $this->_grade, $this->_userType);
        }


        $paginator = Zend_Paginator::factory($products);
        $paginator->setItemCountPerPage($this->_productsPerPage);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

        $productsDisplay = array();

        if (!is_null($products)) {
            foreach ($paginator as $row) {
                $productsDisplay[] = $productsModel->getProductDetailsById($row->product_id,FALSE,$this->_chapLanguageId,NULL,$this->_chapId);
            }
        }


        if ($this->_request->device_id) {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/featured/page/' . $categoryId . $this->_request->device_id;
        } else {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/featured/page/';
        }


        $this->view->products = $productsDisplay;
        $this->view->paginator = $paginator;

        $this->view->pageName = 'Featured Apps';
    }

    //Top downloaded apps
    public function topAction() {
        
        $chap = new Zend_Session_Namespace('partner');
        $this->view->chap_id = $chap->id;

        $productsModel = new Model_Product();
        $chapProducts = new Partner_Model_ChapProducts();

        if ($this->getDeviceId()) {

            $products = $chapProducts->getTopProductsIdByDevice($this->_chapId, $this->getDeviceId(), $this->_grade);
        } else {

            $products = $chapProducts->getTopProducts($this->_chapId, $this->_grade);
        }

        $paginator = Zend_Paginator::factory($products);
        $paginator->setItemCountPerPage($this->_productsPerPage);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

        $productsDisplay = array();

        if (!is_null($products)) {
            foreach ($paginator as $row) {
                $productsDisplay[] = $productsModel->getProductDetailsById($row->product_id,FALSE,$this->_chapLanguageId,NULL,$this->_chapId);
            }
        }


        if ($this->_request->device_id) {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/top/page/' . $categoryId . $this->_request->device_id;
        } else {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/top/page/';
        }


        $this->view->products = $productsDisplay;
        $this->view->paginator = $paginator;

        $this->view->pageName = 'Top Downloaded Apps';
    }
    
    //Most viewed apps
    public function mostViewedAction() 
    {
        $chap = new Zend_Session_Namespace('partner');
        $this->view->chap_id = $chap->id;

        $productsModel = new Model_Product();
        $chapProducts = new Partner_Model_ChapProducts();

        if ($this->getDeviceId()) {

            $products = $chapProducts->getMostViewedProductsByDevice($this->_chapId, $this->getDeviceId(), $this->_grade);
        } else {

            $products = $chapProducts->getMostViewdProduct($this->_chapId, $this->_grade);
        }

        $paginator = Zend_Paginator::factory($products);
        $paginator->setItemCountPerPage($this->_productsPerPage);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

        $productsDisplay = array();

        if (!is_null($products)) {
            foreach ($paginator as $row) {
                $productsDisplay[] = $productsModel->getProductDetailsById($row->product_id,FALSE,$this->_chapLanguageId,NULL,$this->_chapId);
            }
        }


        if ($this->_request->device_id) {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/most-viewed/page/' . $categoryId . $this->_request->device_id;
        } else {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/most-viewed/page/';
        }


        $this->view->products = $productsDisplay;
        $this->view->paginator = $paginator;

        $this->view->pageName = 'Most Viewed';
    }

    
    public function reviewAction() 
    {

        $auth = Zend_Auth::getInstance();

        $productId = $this->_getParam('product_id', null);

        $review = new stdClass();
        $review->body = preg_replace("/\s+/", ' ', substr($this->_getParam('body', false), 0, 1000));
        $review->rating = max(min($this->_getParam('rating', 5), 5), 1); //getting value between 1 and 5 
        $review->reviewer = $this->_getParam('reviewer', false);
        $review->title = $this->_getParam('title', null);

        $data = array();
        $data['user_id'] = $auth->getIdentity()->id;
        $data['product_id'] = $productId;
        $data['name'] = $review->reviewer;
        $data['review'] = $review->body;
        $data['title'] = trim($review->title);
        $data['type'] = 'USER';
        $data['rating'] = $review->rating;
        $data['status'] = 'NOT_APPROVED';

        $reviewModel = new Model_Review();
        $reviewModel->insert($data);

        $this->_redirect($this->_baseUrl . '/' . $productId);
    }

    /**
     * This is a function for showing MTN developer challenge apps
     */

    public function mtnDeveloperChallengeAction()
    {
        $this->view->chap_id = $this->_chapId;
        $this->view->pageName = 'MTN Developer Challenge';
        
        if($this->_chapId == 276531 ) {
            
            $this->view->pageName = 'MTN Application Day';
        }

        $productsModel = new Model_Product();
        $chapProductsModel = new Partner_Model_ChapProducts();

        if ($this->getDeviceId()) {
            $products = $chapProductsModel->getFlaggedAppsByDevice($this->_chapId, $this->getDeviceId());
        } else {
            $products = $chapProductsModel->getFlaggedApps($this->_chapId);
        }

        $paginator = Zend_Paginator::factory($products);
        $paginator->setItemCountPerPage($this->_productsPerPage);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

        $productsDisplay = array();

        if (!is_null($products)) {
            foreach ($paginator as $row) {
                $productsDisplay[] = $productsModel->getProductDetailsById($row->product_id,FALSE,$this->_chapLanguageId,NULL,$this->_chapId);
            }
        }

        $this->view->products = $productsDisplay;
        $this->view->paginator = $paginator;

        if ($this->_request->device_id) {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/mtn-developer-challenge/page/' . $this->_request->device_id;
        } else {
            $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/mtn-developer-challenge/page/';
        }

    }
    
    public function appstitudeAction()
    {
    	$this->view->chap_id = $this->_chapId;
    	$this->view->pageName = 'MTN Appstitude';
    
    	if($this->_chapId == 276531 ) {
    
    		$this->view->pageName = 'MTN Application Day';
    	}
    
    	$productsModel = new Model_Product();
    	$chapProductsModel = new Partner_Model_ChapProducts();
    
    	if ($this->getDeviceId()) {
    		$products = $chapProductsModel->getAppstitudeAppsByDevice($this->_chapId, $this->getDeviceId());
    	} else {
    		$products = $chapProductsModel->getAppstitudeApps($this->_chapId);
    	}
    
    	$paginator = Zend_Paginator::factory($products);
    	$paginator->setItemCountPerPage($this->_productsPerPage);
    	$paginator->setCurrentPageNumber($this->_request->getParam('page', 1));
    
    	$productsDisplay = array();
    
    	if (!is_null($products)) {
    		foreach ($paginator as $row) {
    			$productsDisplay[] = $productsModel->getProductDetailsById($row->product_id,FALSE,$this->_chapLanguageId,NULL,$this->_chapId);
    		}
    	}
    
    	$this->view->products = $productsDisplay;
    	$this->view->paginator = $paginator;
    
    	if ($this->_request->device_id) {
    		$this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/appstitude/page/' . $this->_request->device_id;
    	} else {
    		$this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/appstitude/page/';
    	}
    
    }
    
    public function islamicAction()
    {
    	$this->view->chap_id = $this->_chapId;
    	$this->view->pageName = 'Islamic APPs';
    
    	if($this->_chapId == 276531 ) {
    
    		$this->view->pageName = 'MTN Application Day';
    	}
    
    	$productsModel = new Model_Product();
    	$chapProductsModel = new Partner_Model_ChapProducts();
    
    	if ($this->getDeviceId()) {
    		$products = $chapProductsModel->getIslamicAppsByDevice($this->_chapId, $this->getDeviceId());
    	} else {
    		$products = $chapProductsModel->getIslamicApps($this->_chapId);
    	}
    
    	$paginator = Zend_Paginator::factory($products);
    	$paginator->setItemCountPerPage($this->_productsPerPage);
    	$paginator->setCurrentPageNumber($this->_request->getParam('page', 1));
    
    	$productsDisplay = array();
    
    	if (!is_null($products)) {
    		foreach ($paginator as $row) {
    			$productsDisplay[] = $productsModel->getProductDetailsById($row->product_id,FALSE,$this->_chapLanguageId,NULL,$this->_chapId);
    		}
    	}
    
    	$this->view->products = $productsDisplay;
    	$this->view->paginator = $paginator;
    
    	if ($this->_request->device_id) {
    		$this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/islamic/page/' . $this->_request->device_id;
    	} else {
    		$this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/app/islamic/page/';
    	}
    
    }
    
    //Redirect the header with the latest app store buid for the chap
    public function getLatestAppstoreBuildUrlAction(){
       
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(true);
        
        $latestBuildUrl = null;
        $themeMeta   = new Model_ThemeMeta();
        $themeMeta->setEntityId($this->_chapId);

        //Get the S3 URL of the Relevant build
        $productDownloadCls = new Nexva_Api_ProductDownload();

        $buildUrl = null;

        if($themeMeta->WHITELABLE_SITE_APPSTORE_APP_ID && $themeMeta->WHITELABLE_SITE_APPSTORE_BUILD_ID){
            $buildUrl = $productDownloadCls->getBuildFileUrl($themeMeta->WHITELABLE_SITE_APPSTORE_APP_ID, $themeMeta->WHITELABLE_SITE_APPSTORE_BUILD_ID);
        }
        
        $sessionId = Zend_Session::getId();
        $source = "WEB";
        $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');
        
        $modelDownloadStats = new Api_Model_StatisticsDownloads();
        $modelDownloadStats->addDownloadStat($themeMeta->WHITELABLE_SITE_APPSTORE_APP_ID, $this->_chapId, $source, $ipAddress, $this->_chapId, $themeMeta->WHITELABLE_SITE_APPSTORE_BUILD_ID, 12, $this->_chapLanguageId, $this->_deviceId, $sessionId);
        
        //echo $buildUrl; die();
        if($buildUrl){
            $this->_redirect($buildUrl);
        }
    }
    
    public function getappAction(){
    	 
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    
    	$latestBuildUrl = null;
    	$themeMeta   = new Model_ThemeMeta();
    	$themeMeta->setEntityId($this->_chapId);
    
    	//Get the S3 URL of the Relevant build
    	$productDownloadCls = new Nexva_Api_ProductDownload();
    
    	$buildUrl = null;
    
    	if($themeMeta->WHITELABLE_SITE_APPSTORE_APP_ID && $themeMeta->WHITELABLE_SITE_APPSTORE_BUILD_ID){
    		$buildUrl = $productDownloadCls->getBuildFileUrl($themeMeta->WHITELABLE_SITE_APPSTORE_APP_ID, $themeMeta->WHITELABLE_SITE_APPSTORE_BUILD_ID);
    	}
    
    	$sessionId = Zend_Session::getId();
    	$source = "WEB";
    	$ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');
    
    	$modelDownloadStats = new Api_Model_StatisticsDownloads();
    	$modelDownloadStats->addDownloadStat($themeMeta->WHITELABLE_SITE_APPSTORE_APP_ID, $this->_chapId, $source, $ipAddress, $this->_chapId, $themeMeta->WHITELABLE_SITE_APPSTORE_BUILD_ID, 12, $this->_chapLanguageId, $this->_deviceId, $sessionId);
    
    	//echo $buildUrl; die();
    	if($buildUrl){
    		$this->_redirect($buildUrl);
    	}
    }
    
    
    public function adultAction(){
    	 
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    
    	$content = $this->_getParam('content', null);
    	
    	$sessionAdultsContent = new Zend_Session_Namespace('content');

    	if($content ==1)
    	    $sessionAdultsContent->content = 'adults';
    	else
    	    $sessionAdultsContent->content = 'normal';


    	$this->_redirect('http://'.$_SERVER['SERVER_NAME']);
    }

}