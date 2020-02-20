<?php

class Pbo_AppController extends Zend_Controller_Action
{
    
    public function preDispatch()
    {        
         if( !Zend_Auth::getInstance()->hasIdentity() ) 
         {

            $skip_action_names = array ('login', 'register', 'forgotpassword', 'resetpassword', 'claim', 'impersonate', 'translate','addtranslation','removetranslation');
            
            if (! in_array ( $this->getRequest()->getActionName (), $skip_action_names)) 
            {            
                $requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
                $session = new Zend_Session_Namespace ( 'lastRequest' );
                $session->lastRequestUri = $requestUri;
                $session->lock ();
                $this->_redirect ( '/user/login' );
            } 
        }    
    
        $this->_helper->layout->setLayout('pbo/pbo'); 
    }

   /* Get all apps of a chap
    */
    public function indexAction()
    {

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;        
        $apps = "";
        $this->view->chap_id = $chapId;

        //get the white label url for the chap
        $themeMetaModel = new Pbo_Model_ThemeMeta();
        $whiteLabelUrl = $themeMetaModel->getMetaValueByMetaName($chapId,'WHITELABLE_URL_WEB');

        if(count($whiteLabelUrl)>0){
            $this->view->whiteLabelUrl = $whiteLabelUrl[0]->meta_value;
        }

        $orderType = isset($this->_request->ord) ? $this->_request->ord : null;
        //die();
        $ordering = ($orderType == 'desc') ? 'DESC' : 'ASC';
        
        $orderColumn = trim($this->_request->col);

        if($this->_request->isPost())
        {
            $filterVal =  trim($this->_request->getPost('chkFilter'));
            $searchKey =  trim($this->_request->getPost('txtSearchKey'));
            $platform = $this->_request->getPost('platform');
            $appId = trim($this->_request->getPost('txtSearchId'));
			$languageId = $this->_request->getPost('language');
			$category = $this->_request->getPost('category');
			$grade = $this->_request->getPost('grade',null);
        }
        else
        {
            $filterVal =  trim($this->_request->chk_filter);   
            $searchKey =  trim($this->_request->search_key);
            $platform = trim($this->_request->platform);
            $appId = trim($this->_request->txtSearchId);
			$languageId = trim($this->_request->language);
			$category = trim($this->_request->category);
            $grade = trim($this->_request->grade);
        }

        $validator = new Zend_Validate_Digits();
        
        if($appId && !$validator->isValid($appId)){
            //Set the messages if exists
            $this->_helper->flashMessenger->setNamespace('error')->addMessage('Please enter a numeric value for App ID.');
        }
        
        //This is for Openmobile since they are using only  Android
        if($chapId == 23142) {
            $platform = 12;
        }
        
        $chapProductModel = new Pbo_Model_ChapProducts();
        //Fetch apps
        $apps = $chapProductModel->getChapAllProducts($chapId, $filterVal, $searchKey, $ordering, $orderColumn, $platform, $appId, $languageId, $category, $grade);

        //Set the filter params
        $this->view->filterVal = $filterVal;
        $this->view->searchKey = $searchKey;
        $this->view->platform = $platform;
        $this->view->txtSearchId = $appId;
		$this->view->language = $languageId;
		$this->view->category = $category;
		$this->view->grade = $grade;

        $this->view->showResults = FALSE;
        
        $paginator = Zend_Paginator::factory($apps);            
        $appCount = count($paginator);
        
        if($appCount > 0)
        {
            $this->view->showResults = TRUE;

            $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));
            $paginator->setItemCountPerPage(10);
            
            $this->view->apps = $paginator;
            unset($paginator);        
        }

        $this->view->currentPage = $this->_request->getParam('page', 1);

        $this->view->title = "Apps : Manage Apps";
        $this->view->downloadIcon = FALSE;

        if($orderType == 'asc')
        {
            $orderType = 'desc';
            $this->view->downloadIcon = TRUE;
        }
        elseif($orderType == 'desc')
        {
            $orderType = 'asc';
            $this->view->downloadIcon = TRUE;
        }
        else
        {
            $orderType = 'desc';
        }

        $this->view->downloadOrder = $orderType;

         //Set the messages if exists
        $this->view->appMessages = $this->_helper->flashMessenger->setNamespace('success')->getMessages();
        //Set the messages if exists
        $this->view->appErrorMessages = $this->_helper->flashMessenger->setNamespace('error')->getMessages();

        //get active platforms
        $platformModel = new Model_Platform();
        
        //This is for Openmobile since they are using only  Android
        if($chapId == 23142) {
            $platforms = $platformModel->getPlatformByid(12);
        } else {
            $platforms = $platformModel->getPlatforms();
        }

        $this->view->platforms = $platforms;
		
		 //get languages
        $languageModel = new Pbo_Model_Languages();
        $languages = $languageModel->getAllLanguages();
		$this->view->languages = $languages;

        //get chap categories
        $chapCategoryModel = new Pbo_Model_ChapCategories();
        $categories = $chapCategoryModel->getAllChapCategories($chapId);
        $this->view->categories = $categories;

        //get qelasy grades
        $qelasyGradesModel = new Pbo_Model_QelasyGrades();
        $qelasyGrades = $qelasyGradesModel->getAllQelasyGrades();
        $this->view->grades = $qelasyGrades;

        //$this->view->headScript()->appendFile('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js');
        //$this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/datepicker/css/ui.daterangepicker.css');

    }
    
   /* Feature or unfeature an app
    * @param - id
    * @param - status
    */
    public function doFeatureAction()
    {
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        $chapProductModel = new Pbo_Model_ChapProducts();
        $page = '';
        $chkFilter  = '';
        $searchKey = '';
        $urlStr = '';
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;     
        $appId = trim($this->_request->id);   
        $status = trim($this->_request->status); 
        $page = trim($this->_request->page); 
        $chkFilter = trim($this->_request->chk_filter); 
        $searchKey = trim($this->_request->search_key);
        $platform = $this->_request->platform;
        $platformFilter = $this->_request->platform_filter;
        $language = $this->_request->language_id;

        $urlStr .= ($page)? '/page/'.$page : '' ;
        $urlStr .= ($chkFilter)? '/chk_filter/'.$chkFilter : '' ;
        $urlStr .= ($searchKey)? '/search_key/'.$searchKey : '' ;
        $urlStr .= ($platformFilter)? '/platform/'.$platformFilter : '' ;
        $urlStr .= ($status)? '/status/'.$status : '' ;
        $urlStr .= ($language) ? '/language/'.$language : '';

        $cache  = Zend_Registry::get('cache');
        $key    = 'FEATURED_APPS_'.$chapId;

        $themeMetaModel = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($chapId);

        $featuresProCount = ($themeMetaModel->WHITELABLE_SITE_FETURED_APPS) ? $themeMetaModel->WHITELABLE_SITE_FETURED_APPS : '';
        
        //If going to add a new banner, check if limit has exceeded
        if($status == 1)
        {
            $currentFeauredPlatformCount = $chapProductModel->getFeaturedProductPlatformCountByChap($chapId,$appId,$platform);

            if(count($currentFeauredPlatformCount) == 0)
            {
                $chapProductModel->updateFeaturedProduct($chapId, $appId, $status);

                $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully featured.');

                //after successful app feature we remove the featured apps cache, newly featured apps will immediately start to appear in the front
                $cache->remove($key);
                $this->_redirect('/app/index'.$urlStr);
            }
            else
            {
                if($currentFeauredPlatformCount[0]->app_count >= $featuresProCount)
                {
                    $this->_helper->flashMessenger->setNamespace('error')->addMessage('Featured app limit has already exceeded. Please remove Featured apps from the list and try again');
                    $this->_redirect('/app/index'.$urlStr);
                }
            }
        }

        $chapProductModel->updateFeaturedProduct($chapId, $appId, $status);        
        
        if($status == 1)
        {
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully featured.');
        }
        else
        {
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully unfeatured.');
        }

        //after successful app feature we remove the featured apps cache, newly featured apps will immediately start to appear in the front
        $cache->remove($key);

        $this->_redirect('/app/index'.$urlStr);
    }
    
    
    /* Add or remove an app as a banner
    * @param - id
    * @param - status
    */
    public function doBannerAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout(); 
        
        $page = '';
        $chkFilter  = '';
        $searchKey = '';
        $urlStr = '';
        
        $chapProductModel = new Pbo_Model_ChapProducts();
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;     
        $appId = trim($this->_request->id);   
        $status = trim($this->_request->status);
        $platform = $this->_request->platform;
        $platformFilter = $this->_request->platform_filter;
        $language = $this->_request->language_id;
        
        $page = trim($this->_request->page); 
        $chkFilter = trim($this->_request->chk_filter); 
        $searchKey = trim($this->_request->search_key); 
        $urlStr .= ($page)? '/page/'.$page : '' ;
        $urlStr .= ($chkFilter)? '/chk_filter/'.$chkFilter : '' ;
        $urlStr .= ($searchKey)? '/search_key/'.$searchKey : '' ;
        $urlStr .= ($platformFilter)? '/platform/'.$platformFilter : '' ;
        $urlStr .= ($status)? '/status/'.$status : '' ;
        $urlStr .= ($language) ? '/language/'.$language : '';
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'BANNERED_APPS_'.$chapId;

        $themeMetaModel = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($chapId);

        $bannerCount = ($themeMetaModel->WHITELABLE_SITE_BANNER_COUNT) ? $themeMetaModel->WHITELABLE_SITE_BANNER_COUNT : '';

        //If going to add a new banner, check if limit has exceeded
        if($status == 1)
        {
            $currentBannerPlatformCount = $chapProductModel->getBannerProductPlatformCountByChap($chapId,$appId,$platform);

            if(count($currentBannerPlatformCount) == 0)
            {
                $chapProductModel->updateBanneredProduct($chapId, $appId, $status);
                $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully added to banner list.');

                //after successful app banner we remove the bannered apps cache, newly bannered apps will immediately start to appear in the front
                $cache->remove($key);
                $this->_redirect('/app/index'.$urlStr);
            }
            else
            {
                if($currentBannerPlatformCount[0]->app_count >= $bannerCount)
                {
                    $this->_helper->flashMessenger->setNamespace('error')->addMessage('Banner limit has already exceeded. Please remove banner/s from the list and try again');
                    $this->_redirect('/app/index'.$urlStr);
                }
            }

        }
                
        
        $chapProductModel->updateBanneredProduct($chapId, $appId, $status);        
        
        if($status == 1)
        {
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully added to banner list.');
        }
        else
        {
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully removed from the banner list.');
        }

        //after successful app banner we remove the bannered apps cache, newly bannered apps will immediately start to appear in the front
        $cache->remove($key);
        $this->_redirect('/app/index'.$urlStr);
    }
    
    
    /* Add or remove an app as a banner
    * @param - id
    * @param - status
    */
    public function doFlagAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout(); 
        
        $page = '';
        $chkFilter  = '';
        $searchKey = '';
        $urlStr = '';
        
        $chapProductModel = new Pbo_Model_ChapProducts();
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;     
        $chapProductId = trim($this->_request->id);   
        $status = trim($this->_request->status); 
        $page = trim($this->_request->page); 
        $chkFilter = trim($this->_request->chk_filter); 
        $searchKey = trim($this->_request->search_key);
        $platform = $this->_request->platform;
        $platformFilter = $this->_request->platform_filter;

        $urlStr .= ($page)? '/page/'.$page : '' ;
        $urlStr .= ($chkFilter)? '/chk_filter/'.$chkFilter : '' ;
        $urlStr .= ($searchKey)? '/search_key/'.$searchKey : '' ;
        $urlStr .= ($platformFilter)? '/platform/'.$platformFilter : '' ;
        $urlStr .= ($status)? '/status/'.$status : '' ;             
        
        $chapProductModel->updateFlaggedProduct($chapId, $chapProductId, $status);        
        
        if($status == 1)
        {
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully flagged.');
        }
        else
        {
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully un-flagged.');
        }        
        
        $this->_redirect('/app/index'.$urlStr);
    }
    
    public function appstitudeAction()
    {
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->getHelper('layout')->disableLayout();
    
    	$page = '';
    	$chkFilter  = '';
    	$searchKey = '';
    	$urlStr = '';
    
    	$chapProductModel = new Pbo_Model_ChapProducts();
    
    	$chapId = Zend_Auth::getInstance()->getIdentity()->id;
    	$chapProductId = trim($this->_request->id);
    	$status = trim($this->_request->status);
    	$page = trim($this->_request->page);
    	$chkFilter = trim($this->_request->chk_filter);
    	$searchKey = trim($this->_request->search_key);
    	$platform = $this->_request->platform;
    	$platformFilter = $this->_request->platform_filter;
    
    	$urlStr .= ($page)? '/page/'.$page : '' ;
    	$urlStr .= ($chkFilter)? '/chk_filter/'.$chkFilter : '' ;
    	$urlStr .= ($searchKey)? '/search_key/'.$searchKey : '' ;
    	$urlStr .= ($platformFilter)? '/platform/'.$platformFilter : '' ;
    	$urlStr .= ($status)? '/status/'.$status : '' ;
    
    	$chapProductModel->updateAppstitudeProduct($chapId, $chapProductId, $status);
    
    	if($status == 1)
    	{
    		$this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully flagged.');
    	}
    	else
    	{
    		$this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully un-flagged.');
    	}
    
    	$this->_redirect('/app/index'.$urlStr);
    }
    
    public function islamicAction()
    {
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->getHelper('layout')->disableLayout();
    
    	$page = '';
    	$chkFilter  = '';
    	$searchKey = '';
    	$urlStr = '';
    
    	$chapProductModel = new Pbo_Model_ChapProducts();
    
    	$chapId = Zend_Auth::getInstance()->getIdentity()->id;
    	$chapProductId = trim($this->_request->id);
    	$status = trim($this->_request->status);
    	$page = trim($this->_request->page);
    	$chkFilter = trim($this->_request->chk_filter);
    	$searchKey = trim($this->_request->search_key);
    	$platform = $this->_request->platform;
    	$platformFilter = $this->_request->platform_filter;
    
    	$urlStr .= ($page)? '/page/'.$page : '' ;
    	$urlStr .= ($chkFilter)? '/chk_filter/'.$chkFilter : '' ;
    	$urlStr .= ($searchKey)? '/search_key/'.$searchKey : '' ;
    	$urlStr .= ($platformFilter)? '/platform/'.$platformFilter : '' ;
    	$urlStr .= ($status)? '/status/'.$status : '' ;
    
    	$chapProductModel->updateIslamicProduct($chapId, $chapProductId, $status);
    
    	if($status == 1)
    	{
    		$this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully flagged.');
    	}
    	else
    	{
    		$this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully un-flagged.');
    	}
    
    	$this->_redirect('/app/index'.$urlStr);
    }
    
    
    public function nexvaAction()
    {
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->getHelper('layout')->disableLayout();
    
    	$page = '';
    	$chkFilter  = '';
    	$searchKey = '';
    	$urlStr = '';
    
    	$chapProductModel = new Pbo_Model_ChapProducts();
    
    	$chapId = Zend_Auth::getInstance()->getIdentity()->id;
    	$chapProductId = trim($this->_request->id);
    	$status = trim($this->_request->status);
    	$page = trim($this->_request->page);
    	$chkFilter = trim($this->_request->chk_filter);
    	$searchKey = trim($this->_request->search_key);
    	$platform = $this->_request->platform;
    	$platformFilter = $this->_request->platform_filter;
    
    	$urlStr .= ($page)? '/page/'.$page : '' ;
    	$urlStr .= ($chkFilter)? '/chk_filter/'.$chkFilter : '' ;
    	$urlStr .= ($searchKey)? '/search_key/'.$searchKey : '' ;
    	$urlStr .= ($platformFilter)? '/platform/'.$platformFilter : '' ;
    	$urlStr .= ($status)? '/status/'.$status : '' ;
    
    	$chapProductModel->updateNexvaProduct($chapId, $chapProductId, $status);
    
    	if($status == 1)
    	{
    		$this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully flagged.');
    	}
    	else
    	{
    		$this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully un-flagged.');
    	}
    
    	$this->_redirect('/app/index'.$urlStr);
    }
    
    
    /* Approve or Disapprove an app
    * @param - id
    * @param - status
    */
    public function doApproveAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout(); 
        
        $page = '';
        $chkFilter  = '';
        $searchKey = '';
        $urlStr = '';
        
        $chapProductModel = new Pbo_Model_ChapProducts();
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;     
        $chapProductId = trim($this->_request->id);   
        $status = trim($this->_request->status); 
        $page = trim($this->_request->page); 
        $chkFilter = trim($this->_request->chk_filter); 
        $searchKey = trim($this->_request->search_key);
        $platform = $this->_request->platform;
        $platformFilter = $this->_request->platform_filter;

        $urlStr .= ($page)? '/page/'.$page : '' ;
        $urlStr .= ($chkFilter)? '/chk_filter/'.$chkFilter : '' ;
        $urlStr .= ($searchKey)? '/search_key/'.$searchKey : '' ;
        $urlStr .= ($platformFilter)? '/platform/'.$platformFilter : '' ;
        $urlStr .= ($status)? '/status/'.$status : '' ;             
        
        $chapProductModel->updateApprovedProduct($chapId, $chapProductId, $status);        
        
        if($status == 1)
        {
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully approved.');
        }
        else
        {
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully disapproved.');
        }        
        
        $this->_redirect('/app/index'.$urlStr);
    }
    
    
   /* Delete an app
    * @param - id
    */
    public function doDeleteAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout(); 
        
        $page = '';
        $chkFilter  = '';
        $searchKey = '';
        $urlStr = '';
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        $chapProdId = trim($this->_request->id);
        $platformFilter = $this->_request->platform_filter;
        $status = trim($this->_request->status); 
        $page = trim($this->_request->page); 
        $chkFilter = trim($this->_request->chk_filter); 
        $searchKey = trim($this->_request->search_key); 
        
        $urlStr .= ($page)? '/page/'.$page : '' ;
        $urlStr .= ($chkFilter)? '/chk_filter/'.$chkFilter : '' ;
        $urlStr .= ($searchKey)? '/search_key/'.$searchKey : '' ;
        $urlStr .= ($platformFilter)? '/platform/'.$platformFilter : '' ;
        $urlStr .= ($status)? '/status/'.$status : '' ;
        
        $cache  = Zend_Registry::get('cache');
        //these are the keys for apps showing sliders
        $newestKey    = 'NEWEST_APPS_'.$chapId;
        $freeKey    = 'FREE_APPS_'.$chapId;
        $paidKey    = 'PAID_APPS_'.$chapId;
        $topKey    = 'TOP_APPS_'.$chapId;
        $mostViewedKey    = 'MOSTVIEWED_APPS_'.$chapId;
        $banneredKey    = 'BANNERED_APPS_'.$chapId;
        $featuredKey    = 'FEATURED_APPS_'.$chapId;

        //these are the keys for apps showing with main menu links
        $freeProductKey    = 'FREE_PRODUCTS_'.$chapId;
        $premiumProductKey    = 'PREMIUM_PRODUCTS_'.$chapId;
        $topProductKey    = 'TOP_PRODUCTS_'.$chapId;
        $newestProductKey    = 'NEWEST_PRODUCTS_'.$chapId;
        $featuredProductKey    = 'FEATURED_PRODUCTS_'.$chapId;
        $mostviewedProductKey    = 'MOSTVIEWED_PRODUCTS_'.$chapId;
        $flaggedProductKey    = 'FLAGGED_APPS_'.$chapId;

        //after successfully adding a app to store we remove apps cache, newly added apps will immediately start to appear in the front
        $cache->remove($newestKey);
        $cache->remove($freeKey);
        $cache->remove($paidKey);
        $cache->remove($topKey);
        $cache->remove($mostViewedKey);
        $cache->remove($banneredKey);
        $cache->remove($featuredKey);

        $cache->remove($freeProductKey);
        $cache->remove($premiumProductKey);
        $cache->remove($topProductKey);
        $cache->remove($newestProductKey);
        $cache->remove($featuredProductKey);
        $cache->remove($mostviewedProductKey);
        $cache->remove($flaggedProductKey);

        $chapProductModel = new Pbo_Model_ChapProducts();     

        $chapProductModel->deleteProduct($chapId, $chapProdId);
            
        $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully deleted.');
        
        $this->_redirect( '/app/index'.$urlStr );
    }   
    
    
    
   /* List down all the apps which are not included in the appstore of the CHAP
    * @param - id
    * @param - status
    */
    public function filterAppsAction()
    {
        $this->view->isEmptyApps = TRUE;
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        $this->view->chap_id = $chapId;
        $productModel = new Pbo_Model_Products();     

        if($this->_request->isPost())
        {
            $priceFilter =  $this->_request->getPost('chkPriceFilter');
            $categoryFilter =  $this->_request->getPost('chkCategory');
            $searchKey =  trim($this->_request->getPost('txtSearchKey'));        
            $platform = $this->_request->getPost('platform');
            $language = $this->_request->getPost('language');
            $grade = $this->_request->getPost('grade',null);
        }
        else
        {   
            $priceFilter =  trim($this->_request->price_filter);
            $categoryFilter =  trim($this->_request->cat_filter);
            $searchKey =  trim($this->_request->search_key);
            $platform = trim($this->_request->platform);
            $language = trim($this->_request->language);
            $grade = trim($this->_request->grade);
        }
        
        if($chapId == 23142) {
        	$platform = 12;
        }
        
        
        //Fetch apps
        $apps = $productModel->getNonChapProducts($chapId ,$priceFilter, $categoryFilter, $searchKey, $platform, $language, $grade);

        //set filter values back
        $this->view->priceFilterVal = $priceFilter;
        $this->view->catFilterVal = $categoryFilter;
        $this->view->searchVal = $searchKey;
        $this->view->platform = $platform;
        $this->view->language = $language;
        $this->view->grade = $grade;

        $this->view->showResults = FALSE;
        
        //get the current pagination number
        $this->view->currentPage = $this->_request->getParam('page', 1);
        
        $pagination = Zend_Paginator::factory($apps);
        $appCount = count($pagination);

        if($appCount > 0)
        {
            $this->view->showResults = TRUE;           
            $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
            
            if($chapId == 25022){
                $pagination->setItemCountPerPage(100);
            }
            else{
                $pagination->setItemCountPerPage(10);
            }
            
            $this->view->apps = $pagination;
            unset($pagination);        
        }
                        
        $this->view->title = "Apps : Filter Apps";  

        //Set the success messages if exists
        $this->view->messages = $this->_helper->flashMessenger->setNamespace('success')->getMessages();

        //Set the error messages if exists
        $this->view->errorMessages = $this->_helper->flashMessenger->setNamespace('error')->getMessages();
        
        //Get all categories
        //$categoryModel = new Model_Category();
        //$categories = $categoryModel->getAllCategories();

        //get chap categories
        $chapCategoryModel = new Pbo_Model_ChapCategories();
        $categories = $chapCategoryModel->getAllChapCategories($chapId);

        if(count($categories)==0){
            $this->_helper->flashMessenger->setNamespace('error')->addMessage('Assign Categories for this CHAP');
            $this->_redirect('/category/manage-category');
        }

        $this->view->categories = $categories;


        //get active platforms
        $platformModel = new Model_Platform();

        if($chapId == 23142) {
        	$platforms = $platformModel->getPlatformByid(12);
        } else {
        	$platforms = $platformModel->getPlatforms();
        }

        $this->view->platforms = $platforms;

        //get languages
        $languageModel = new Pbo_Model_Languages();
        $languages = $languageModel->getAllLanguages();
        $this->view->languages = $languages;

        //get qelasy grades
        $qelasyGradesModel = new Pbo_Model_QelasyGrades();
        $qelasyGrades = $qelasyGradesModel->getAllQelasyGrades();
        $this->view->grades = $qelasyGrades;

    }

    /* List down all the apps for a CHAP, generating of excel report
    * @param - id
    */
    public function excelReportAction()
    {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        ini_set('memory_limit','3000M');
        ini_set('max_execution_time', 0);
        //get apps by CHAP id
        $chapProductModel = new Pbo_Model_ChapProducts();
        $chapProducts = $chapProductModel->excelReport($chapId);

        $data = "";
        $line = "Product"."\t"."Build Name"."\t"."Category"."\t"."Price"."\t"."Product Type"."\t"."Downloads". "\t"."Revenue Generated". "\t"."Date Created". "\t"."Banner". "\t"."Featured"."\t"."Flagged"."\t"."\n";

        foreach($chapProducts as $chapProduct)
        {
            $line.= $chapProduct['product_name']."\t";
            $line.= $chapProduct['build_name']."\t";

            foreach($chapProduct[0] as $category)
            {
                $line.= $category['name'].",";
            }
            $line.= "\t";
            $line.= "$".$chapProduct['product_price']."\t";
            $line.= $chapProduct['pro_type']."\t";
            $line.= $chapProduct['download_count']."\t";

            $revenue = $chapProduct['download_count']*$chapProduct['product_price'];
            $line.= $revenue."\t";

            $line.= $chapProduct['created_date']."\t";
            ($chapProduct['is_banner'] == 1)?$line.= "yes\t":$line.="No\t";
            ($chapProduct['featured'] == 1)?$line.= "yes\t":$line.="No\t";
            ($chapProduct['flagged'] == 1)?$line.= "yes\t":$line.="No\t";

            $line .= "\n";
        }
        $data .= trim($line) . "\n";
        $data = str_replace("\r", "", $data);

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename = report.xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        print $data;
        exit;

    }

    /* Add apps to the appstore of the CHAP
    * @param - chap Id
    * @param - product Id
    */
    public function addToStoreAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout(); 
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        $prodId = trim($this->_request->id);

        //31-10-2013 Following codes added for check the app platform type is compatible with the chapter platform type
        $chapterPlatformType = NULL;
        if($chapId){
            $themeMetaModel = new Model_ThemeMeta();
            $themeMetaModel->setEntityId($chapId);
            $chapterPlatformType = $themeMetaModel->WHITELABLE_PLATEFORM;
        }
       
        $productModel = new Model_Product();
        $supportedPlatforms = $productModel->getSupportedPlatforms($prodId);
        $appPlatformType = $productModel->verifyPlatformType($supportedPlatforms, $prodId);
        
        //echo $appPlatformType.'#####'.$chapterPlatformType; die();
        
        //Condition added for adding 'non android' apps for 'android only' chapters - CCW
        if($chapterPlatformType == 'ANDROID_PLATFORM_CHAP_ONLY' && $appPlatformType == 'NON_ANDROID_PLATFORM_APP'){
            $this->_helper->flashMessenger->setNamespace('error')->addMessage('The app is not compatible with the CHAP platform type.');
        }
        else{
            $chapProductModel = new Pbo_Model_ChapProducts();
            $chapProductModel->addProductToChap($chapId, $prodId);

            $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully added to your store.');

            $cache  = Zend_Registry::get('cache');
            $newestKey    = 'NEWEST_APPS_'.$chapId;
            $freeKey    = 'FREE_APPS_'.$chapId;
            $paidKey    = 'PAID_APPS_'.$chapId;
            $topKey    = 'TOP_APPS_'.$chapId;
            $mostViewedKey    = 'MOSTVIEWED_APPS_'.$chapId;

            //after successful app add to store we remove apps cache, newly added apps will immediately start to appear in the front
            $cache->remove($newestKey);
            $cache->remove($freeKey);
            $cache->remove($paidKey);
            $cache->remove($topKey);
            $cache->remove($mostViewedKey);
        }
     
        //get the request values and append with redirect
        $urlStr = '';  
        $urlStr .= (isset($this->_request->price_filter)) ? '/price_filter/'.trim($this->_request->price_filter) : 'all';
        $urlStr .= (isset($this->_request->cat_filter)) ? '/cat_filter/'.trim($this->_request->cat_filter) : 'all';
        $urlStr .= (isset($this->_request->search_key)) ? '/search_key/'.trim($this->_request->search_key) : '';
        $urlStr .= (isset($this->_request->platform)) ? '/platform/'.trim($this->_request->platform) : '';
        $urlStr .= (isset($this->_request->language)) ? '/language/'.trim($this->_request->language) : '';
        $urlStr .= (isset($this->_request->page)) ? '/page/'.trim($this->_request->page) : '';

        $this->_redirect( '/app/filter-apps'.$urlStr);
    }

    /**
     * almost identical add-to-store, here we adds bulk list to the store
     */
    function addBulkAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        $productIDS = array_unique($this->_request->chk);           //here we gets a name array from form submission, therefore to remove duplicated ids if there any used this

        //31-10-2013 Following codes added for check the app platform type is compatible with the chapter platform type
        $chapterPlatformType = NULL;
        if($chapId){
            $themeMetaModel = new Model_ThemeMeta();
            $themeMetaModel->setEntityId($chapId);
            $chapterPlatformType = $themeMetaModel->WHITELABLE_PLATEFORM;
        }
       
        $productModel = new Model_Product();

        $chapProductModel = new Pbo_Model_ChapProducts();
        $errorProdIds = Array();
        $addError = FALSE;
        $addSuccess = FALSE;
        foreach($productIDS as $productID)
        {
            // Added for check and add apps which are applicable for chapter site
            $supportedPlatforms = $productModel->getSupportedPlatforms($productID);
            $appPlatformType = $productModel->verifyPlatformType($supportedPlatforms, $productID);
            
            if($chapterPlatformType == 'ANDROID_PLATFORM_CHAP_ONLY' && $appPlatformType == 'NON_ANDROID_PLATFORM_APP'){
                $errorProdIds[]=$productID;
                $addError = TRUE;
            }
            else{
                $chapProductModel->addProductToChap($chapId, $productID);
                $addSuccess = TRUE;
            }
           
        }
        
        if($addError){
            $this->_helper->flashMessenger->setNamespace('error')->addMessage('The following apps are not compatible with the chapter platform type. ID : '.implode(',', $errorProdIds));
        }
        
        if($addSuccess){
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('Selected Apps successfully added to your store.');
        }

        $cache  = Zend_Registry::get('cache');
        $newestKey    = 'NEWEST_APPS_'.$chapId;
        $freeKey    = 'FREE_APPS_'.$chapId;
        $paidKey    = 'PAID_APPS_'.$chapId;
        $topKey    = 'TOP_APPS_'.$chapId;
        $mostViewedKey    = 'MOSTVIEWED_APPS_'.$chapId;

        //after successful app add to store we remove apps cache, newly added apps will immediately start to appear in the front
        $cache->remove($newestKey);
        $cache->remove($freeKey);
        $cache->remove($paidKey);
        $cache->remove($topKey);
        $cache->remove($mostViewedKey);

        //get the request values and append with redirect
        $urlStr = '';  
        $urlStr .= (isset($this->_request->price_filter)) ? '/price_filter/'.trim($this->_request->price_filter) : 'all';
        $urlStr .= (isset($this->_request->cat_filter)) ? '/cat_filter/'.trim($this->_request->cat_filter) : 'all';
        $urlStr .= (isset($this->_request->search_key)) ? '/search_key/'.trim($this->_request->search_key) : '';
        $urlStr .= (isset($this->_request->platform)) ? '/platform/'.trim($this->_request->platform) : '';
        $urlStr .= (isset($this->_request->language)) ? '/language/'.trim($this->_request->language) : '';
        $urlStr .= (isset($this->_request->page)) ? '/page/'.trim($this->_request->page) : '';
        
        $this->_redirect( '/app/filter-apps'.$urlStr);
    }
    
    public function getAppDetailsAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();    
    
        $userId = trim($this->_getParam('id'));
        $userType = trim($this->_getParam('usertype'));
       
        $apps = array();
        
        if($userType == 'USER')
        {
            $statsModel = new Pbo_Model_StatisticsDownloads();
            $apps = $statsModel->getDownloadedAppsByUser($userId, $userType);
        }
        else
        {
            $productModel = new Pbo_Model_Products();
            $apps = $productModel->getUploadedAppsByUser($userId, $userType);
        }
             
        echo json_encode($apps);
    }
    
    
     public function getAppsByDeviceAction()
    {        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();    
    
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        $deviceId = trim($this->_getParam('id'));
       
        $apps = array();
      
        $statsModel = new Pbo_Model_StatisticsDownloads();
        $apps = $statsModel->getDownloadedAppsByDevice($deviceId, $chapId);

        echo json_encode($apps);
    }
    
    //Loading Nexlinker popup
    public function embededAction() 
    {
            $this->_helper->getHelper('layout')->disableLayout();
            
            $productId = $this->_request->id;
            
            $prodModel = new Model_Product();
            $this->view->content = $prodModel->getProductDetailsById($productId);

            //get the chap id
            $chapId = Zend_Auth::getInstance()->getIdentity()->id;

            if (isset($chapId) && !empty($chapId)) 
            {
                $themeMeta  = new Model_ThemeMeta();
                $themeMeta->setEntityId($chapId);
                $themeData  = $themeMeta->getAll();
                $this->view->themeData  = $themeData;
                $this->view->chapId     = $chapId;    
            }
    }

    public function googlePlayDownloadsAction()
    {
        $id = $this->_request->getParam('id');
        $value = $this->_request->getParam('value');

        $productModel = new Pbo_Model_Products();
        $result =  $productModel->googleDownloads($id,$value);

        echo $result[0]->google_download_count;
        die();
        //return;
    }

    //pass the available platforms for a particular app
    public function getPlatformForAppAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        $appId = trim($this->_getParam('id'));

        $productModel = new Model_Product();
        $platforms = $productModel->getSupportedPlatforms($appId);

        echo json_encode($platforms);
    }

    public function selectedPlatformsForFeaturedAction()
    {        
        $urlStr = '';
        $urlStr .= ($this->_getParam('page'))? '/page/'.$this->_getParam('page') : '' ;
        $urlStr .= ($this->_getParam('chk_filter'))? '/chk_filter/'.$this->_getParam('chk_filter') : 'all' ;
        $urlStr .= ($this->_getParam('search_key'))? '/search_key/'.$this->_getParam('search_key') : '' ;
        $urlStr .= ($this->_getParam('platform_filter'))? '/platform/'.$this->_getParam('platform_filter') : '' ;

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        $platforms = $this->_getParam('platforms');
        $appId = $this->_getParam('appId');
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        $status = $this->_getParam('status');

        $cache  = Zend_Registry::get('cache');
        $key    = 'FEATURED_APPS_'.$chapId;

        $themeMetaModel = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($chapId);

        $featuresProCount = ($themeMetaModel->WHITELABLE_SITE_FETURED_APPS) ? $themeMetaModel->WHITELABLE_SITE_FETURED_APPS : '';

        $chapProductModel = new Pbo_Model_ChapProducts();

        if(is_null($platforms))
        {
            $this->_helper->flashMessenger->setNamespace('error')->addMessage('Select at least one Platform to be featured !');
            $this->_redirect('/app/index'.$urlStr);
        }

        if($status == 1)
        {
            $currentFeauredPlatformCount = $chapProductModel->getFeaturedProductPlatformCountByChap($chapId,$appId,$platforms);
            $currentFeauredPlatformCount = $currentFeauredPlatformCount->toArray();

            if(count($currentFeauredPlatformCount) == 0)
            {
                $chapProductModel->updateFeaturedProduct($chapId, $appId, $status);
                $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully featured.');

                //after successful app feature we remove the featured apps cache, newly featured apps will immediately start to appear in the front
                $cache->remove($key);
                $this->_redirect('/app/index'.$urlStr);
            }
            else
            {

                for($i=0;$i<count($platforms);$i++)
                {
                    if(($currentFeauredPlatformCount[$i]) && ($platforms[$i] == $currentFeauredPlatformCount[$i]->id))
                    {
                        if($currentFeauredPlatformCount[$i]->app_count >= $featuresProCount)
                        {
                            $this->_helper->flashMessenger->setNamespace('error')->addMessage('Featured app limit has reached. Please remove Featured apps from the list and try again');
                            $this->_redirect('/app/index'.$urlStr);
                        }
                    }
                }
            }
        }

        $chapProductModel->updateFeaturedProduct($chapId, $appId, $status);

        if($status == 1)
        {
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully featured.');
        }
        else
        {
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully unfeatured.');
        }

        //after successful app feature we remove the featured apps cache, newly featured apps will immediately start to appear in the front
        $cache->remove($key);
        $this->_redirect('/app/index'.$urlStr);

    }

    public function selectedPlatformsForBannerAction()
    {
        $urlStr = '';
        $urlStr .= ($this->_getParam('page'))? '/page/'.$this->_getParam('page') : '' ;
        $urlStr .= ($this->_getParam('chk_filter'))? '/chk_filter/'.$this->_getParam('chk_filter') : 'all' ;
        $urlStr .= ($this->_getParam('search_key'))? '/search_key/'.$this->_getParam('search_key') : '' ;
        $urlStr .= ($this->_getParam('platform_filter'))? '/platform/'.$this->_getParam('platform_filter') : '' ;
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        $platforms = $this->_getParam('platforms');
        $appId = $this->_getParam('appId');
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        $status = $this->_getParam('status');

        $cache  = Zend_Registry::get('cache');
        $key    = 'BANNERED_APPS_'.$chapId;

        $themeMetaModel = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($chapId);

        $bannerCount = ($themeMetaModel->WHITELABLE_SITE_BANNER_COUNT) ? $themeMetaModel->WHITELABLE_SITE_BANNER_COUNT : '';
        $chapProductModel = new Pbo_Model_ChapProducts();

        if(is_null($platforms))
        {
            $this->_helper->flashMessenger->setNamespace('error')->addMessage('Select at least one Platform to be Banner !');
            $this->_redirect('/app/index'.$urlStr);
        }

        if($status == 1)
        {
            $currentBannerPlatformCount = $chapProductModel->getBannerProductPlatformCountByChap($chapId,$appId,$platforms);
            $currentBannerPlatformCount = $currentBannerPlatformCount->toArray();

            if(count($currentBannerPlatformCount) == 0)
            {
                $chapProductModel->updateBanneredProduct($chapId, $appId, $status);
                $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully added to banner list.');

                //after successful app banner we remove the bannered apps cache, newly bannered apps will immediately start to appear in the front
                $cache->remove($key);
                $this->_redirect('/app/index'.$urlStr);
            }
            else
            {
                for($i=0;$i<count($platforms);$i++)
                {
                    if(($currentBannerPlatformCount[$i]) && ($platforms[$i] == $currentBannerPlatformCount[$i]->id))
                    {
                        if($currentBannerPlatformCount[$i]->app_count >= $bannerCount)
                        {
                            $this->_helper->flashMessenger->setNamespace('error')->addMessage('Banner limit has reached. Please remove banner/s from the list and try again');
                            $this->_redirect('/app/index'.$urlStr);
                        }
                    }
                }
            }
        }

        $chapProductModel->updateBanneredProduct($chapId, $appId, $status);

        if($status == 1)
        {
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully added to banner list.');
        }
        else
        {
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('App successfully removed from the banner list.');
        }

        //after successful app banner we remove the bannered apps cache, newly bannered apps will immediately start to appear in the front
        $cache->remove($key);
        $this->_redirect('/app/index'.$urlStr);

    }
    
    
    //Serialize app names for ajax call in manage app section
    public function getSerialisedAppNamesAction(){
        
        $searchString = $this->_request->getParam('q');
        $platform = $this->_request->getParam('platform');
        $status = $this->_request->getParam('status');
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        
        $chapProductModel = new Pbo_Model_ChapProducts();
        $appNames = $chapProductModel->getChapAllProductNames($chapId, $searchString, $platform, $status);
        
        echo json_encode($appNames);
        die();
    }
 
    //Serialize app names for ajax call in filter app section
    public function getSerialisedAppNamesForFilterAction(){
        
        $searchString = $this->_request->getParam('q');
        $platform = $this->_request->getParam('platform');
        $language = $this->_request->getParam('language');
        $category = $this->_request->getParam('category');
        $price = $this->_request->getParam('price');
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        
        $productModel = new Pbo_Model_Products();
        $appNames = $productModel->getAllNonChapProductNames($chapId, $searchString, $price, $category, $platform, $language);
        
        echo json_encode($appNames);
        die();
    }

    /**
     *
     */
    public function userAppsAction(){
        $this->view->title = 'Apps: User Apps';

        $this->view->showResults = FALSE;

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        if($this->_request->isPost())
        {
            $from =  $this->_request->getPost('from');
            $to =  $this->_request->getPost('to');
            $price =  trim($this->_request->getPost('price'));
            $mobile = $this->_request->getPost('mobile');

        } else {

            $from =  trim($this->_request->from);
            $to =  trim($this->_request->to);
            $price =  trim($this->_request->price);
            $mobile = trim($this->_request->mobile);
        }

        //set filter values back
        $this->view->from = $from;
        $this->view->to = $to;
        $this->view->price = $price;
        $this->view->mobile = $mobile;

        $statisticDownloadModel = new Pbo_Model_StatisticsDownloads();
        $apps = $statisticDownloadModel->getDownloadedAppsByMobile($chapId, $from, $to, $price, $mobile);
        $pagination = Zend_Paginator::factory($apps);

        if(count($pagination) > 0)
        {
            $this->view->showResults = TRUE;
            $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
            $pagination->setItemCountPerPage(10);

            $this->view->apps = $pagination;
            unset($pagination);
        }

    }
    
    function translateAction() {
        $proId  = $this->_getParam('id', null);
        
        if (!$proId) {
            $this->_redirect('/');
        }
        
        if($this->_request->isPost())
        {
            $filterVal =  trim($this->_request->getPost('chkFilter'));
            $searchKey =  trim($this->_request->getPost('txtSearchKey'));
            $platform = $this->_request->getPost('platform');
            $appId = trim($this->_request->getPost('txtSearchId'));
            $languageId = $this->_request->getPost('language');
            $category = $this->_request->getPost('category');
            $grade = $this->_request->getPost('grade',null);
            $page = $this->_request->getPost('page');
        }
        else
        {
            $filterVal =  trim($this->_request->chk_filter);   
            $searchKey =  trim($this->_request->search_key);
            $platform = trim($this->_request->platform);
            $appId = trim($this->_request->txtSearchId);
            $languageId = trim($this->_request->language);
            $category = trim($this->_request->category);
            $grade = trim($this->_request->grade);
            $page = trim($this->_request->page);
        }

        //Set the filter params
        $this->view->filterVal = $filterVal;
        $this->view->searchKey = $searchKey;
        $this->view->platform = $platform;
        $this->view->txtSearchId = $appId;
        $this->view->language = $languageId;
        $this->view->category = $category;
        $this->view->grade = $grade;
        $this->view->page = $page;

        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 

        $productModel   = new Model_Product();
        $langModelUsers      = new Model_LanguageUsers();
        $productLangMeta    = new Model_ProductLanguageMeta();
        
        $languages      = $langModelUsers->getMultiLanguagesByChap($chapId);
        $product        = $productModel->getProductDetailsById($proId, true);

        $translation    = new stdClass();
        $translation->PRODUCT_NAME          = '';
        $translation->PRODUCT_DESCRIPTION   = '';
        $translation->PRODUCT_SUMMARY       = '';
        $translation->language_id           = '';
        
        //$langId     = (int) $this->_getParam('langId', null);  
        //Get chap default lang id. @TO DO this will be change to get default lang id set from the admin
        $langId = $languages[0]->id;     
        
        if ($langId) {
            $productLangMeta    = new Model_ProductLanguageMeta();
            $translation        = $productLangMeta->loadTranslation($proId, $langId);
        }
        
        $this->view->title          = 'Manage Transations';
        $this->view->translations   = $productLangMeta->getAllTranslations($proId);
        $this->view->translation    = $translation;
        $this->view->languages      = $languages;
        $this->view->product        = $product;
        $this->view->proid          = $proId;
        $this->view->chapId         = $chapId;
        $this->view->curLangId      = $langId;
    }
    
    function addtranslationAction() {
        
        $proId  = (int) $this->_getParam('id', false);
        
        if (!$proId) {
            $this->_redirect('/');
        }
        //$this->__checkOwner($proId);
        $langId     = (int) $this->_getParam('langId', null);
        
        $page = '';
        $chkFilter  = '';
        $searchKey = '';
        $urlStr = '';
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;     
        $appId = $this->_getParam('id'); 
        $status = $this->_getParam('status'); 
        $page = $this->_getParam('pageNo'); 
        $chkFilter = $this->_getParam('chk_filter'); 
        $searchKey = $this->_getParam('search_key');
        $platform = $this->_getParam('platform'); 
        $platformFilter = $this->_getParam('platform_filter'); 
        $language = $this->_getParam('language_id'); 

        $urlStr .= ($page)? '/page/'.$page : '' ;
        $urlStr .= ($chkFilter)? '/chk_filter/'.$chkFilter : '' ;
        $urlStr .= ($searchKey)? '/search_key/'.$searchKey : '' ;
        $urlStr .= ($platformFilter)? '/platform/'.$platformFilter : '' ;
        $urlStr .= ($status)? '/status/'.$status : '' ;
        $urlStr .= ($language) ? '/language/'.$language : '';
        
        //echo $urlStr; die();
        
        $proLangModel   = new Model_ProductLanguageMeta();
        $data           = array(
            'name'      => $this->_getParam('name'),
            'summary'   => $this->_getParam('summary'),
            'desc'      => $this->_getParam('desc')
        );
        
        //print_r($data); die();
        
        $proLangModel->saveTranslation($proId, $langId, $data);
        $this->_redirect('/app/translate/id/' . $proId . $urlStr);
    }
    
    function removetranslationAction() {
        $proId  = (int) $this->_getParam('id', false);
        
        if (!$proId) {
            $this->_redirect('/');
        }
        
        if($this->_request->isPost())
        {
            $filterVal =  trim($this->_request->getPost('chkFilter'));
            $searchKey =  trim($this->_request->getPost('txtSearchKey'));
            $platform = $this->_request->getPost('platform');
            $appId = trim($this->_request->getPost('txtSearchId'));
            $languageId = $this->_request->getPost('language');
            $category = $this->_request->getPost('category');
            $grade = $this->_request->getPost('grade',null);
            $page = $this->_request->getPost('page');
        }
        else
        {
            $filterVal =  trim($this->_request->chk_filter);   
            $searchKey =  trim($this->_request->search_key);
            $platform = trim($this->_request->platform);
            $appId = trim($this->_request->txtSearchId);
            $languageId = trim($this->_request->language);
            $category = trim($this->_request->category);
            $grade = trim($this->_request->grade);
            $page = trim($this->_request->page);
        }
        
        
        $page = '';
        $chkFilter  = '';
        $searchKey = '';
        $urlStr = '';
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;     

        $urlStr .= ($page)? '/page/'.$page : '' ;
        $urlStr .= ($filterVal)? '/chk_filter/'.$filterVal : '' ;
        $urlStr .= ($searchKey)? '/search_key/'.$searchKey : '' ;
        $urlStr .= ($platform)? '/platform/'.$platform : '' ;
        $urlStr .= ($status)? '/status/'.$status : '' ;
        $urlStr .= ($languageId) ? '/language/'.$languageId : '';
        $urlStr .= ($category) ? '/category/'.$category : '';
        
        //$this->__checkOwner($proId);
        $langId     = (int) $this->_getParam('langId', null);
        
        $langModelUsers      = new Model_LanguageUsers();
        $languageChap      = $langModelUsers->getMultiLanguagesByChap($chapId);
        //Get chap default lang id. @TO DO this will be change to get default lang id set from the admin
        $chapLangId = $languageChap[0]->id;     
        
        if($chapLangId != $langId){
            $this->_redirect('/app/index');
        }
        
        $proModel   = new Model_ProductLanguageMeta();
        $proModel->delete("product_id = {$proId} AND language_id = {$langId}");
        
        $this->_redirect('/app/translate/id/' . $proId . $urlStr);
    }
}