<?php
class Admin_AnalyticsController extends Nexva_Controller_Action_Admin_MasterController {
    
    private $__validParameters    = array('from_view', 'to_view', 'pro_id', 'mode');
    
    public function preDispatch() {
    	
        parent::preDispatch();
        $this->view->queryString            = $this->__getQueryString();
        
    }
    
    
    public function init()
    {
        $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/datepicker/css/ui.daterangepicker.css');
        $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/datepicker/css/redmond/jquery-ui-1.7.1.custom.css');
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/datepicker/js/daterangepicker.jQuery.js');

    }
    

    function disabledAction() {
        //shows disabled notice
    }
    
    function summaryAction() {

    	
        if ($this->_request->isPost()) {
            $from  = $this->_getParam('from', date('Y-m-d'));
            $to    = $this->_getParam('to', date('Y-m-d'));
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'analytics/summary/from/' . $from . '/to/' . $to);
        }
        
         $firstDayThisMonth = date('Y-m-01'); 
		 $lastDayThisMonth = date('Y-m-d');
        
         $from  = $this->_getParam('from', $firstDayThisMonth );
         $to    = $this->_getParam('to', $lastDayThisMonth); 
         if ($from && $to) {
           
            //$this->__getValidDates($fromTs, $toTs);
            
            $downloads              = new Nexva_Analytics_ProductDownload();
            $views                  = new Nexva_Analytics_ProductView(); 
            $downloadCounts         = array();
            
            $widgetId               = Zend_Registry::get('config')->nexva->application->widget->id;     //
            $totalDownloadsWidget   = $downloads->getTotalDownloadsForApp($widgetId, $from, $to);       //Zend_Debug::dump($totalDownloadsWidget);die();
            $totalDownloads         = $downloads->getTotalDownloadsForApp(null, $from, $to) - $totalDownloadsWidget;    //
            $totalViews        		= $views->getTotalViews($from, $to, null);
            
            $downloadsByApp         = $downloads->getMostDownloadedAppsByDateRange($from, $to, 10); //Zend_Debug::dump($downloadsByApp);die();
            $viewsByApp             = $views->getMostViewedAppsByDateRange($from, $to, 10); //needs to be paged    
            
            $this->view->downloadsByApp         = $downloadsByApp;
            $this->view->viewsByApp             = $viewsByApp;
            $this->view->totalDownloadsWidget   = $totalDownloadsWidget;
            $this->view->totalDownloads         = $totalDownloads;
            $this->view->downloadCounts         = $downloadCounts;
            $this->view->totalViews			    = $totalViews;

            $productModel           	= new Admin_Model_Product();
            $totalApps              	= $productModel->getTotalApprovedApps($from, $to);
            $appsForPlatformDateRnage	= $productModel->getApprovedAppsByPlatformByRange($from, $to);
			$overallApps            	= $productModel->getTotalApprovedApps();
            $appsForPlatform			= $productModel->getApprovedAppsByPlatform();

            $this->view->overallApps = $overallApps;
            $this->view->totalApps	= $totalApps;
            $this->view->appsForPlatformDateRnage  = $appsForPlatformDateRnage;
            $this->view->appsForPlatform  = $appsForPlatform;

            $userModel              = new Model_User();
            $totalUsers             = $userModel->getTotalNumberOfUsersByRegisterDate('CP', $from, $to);
            $this->view->totalUsers  = $totalUsers;
            
            $this->view->from  = $from;
            $this->view->to    = $to;
        }
    }
    
    function dashboardAction() {
    
       
        $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));

        $productId  = $this->_request->getParam('pro_id', false);
        $product    = $this->__getProduct($productId);
        if ($product) {
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'analytics/downloads/' . $this->__getQueryString());
        }
        
        $views          = new Nexva_Analytics_ProductView(); 
        $downloads      = new Nexva_Analytics_ProductDownload();
    
        /* Developer Sing up  */
        $user =new Model_User();
        $this->view->sing_up=$user->developerSingUp($startDate, $endDate);
        /* End */
    
        /* Developer visits  */
        $DeveloperVisits =new Cpbo_Model_DeveloperVisits();
        $this->view->developer_visits=$DeveloperVisits->developerVisits($startDate, $endDate);
        /* End */
        //$gap    = $views->getChartGroupGap($endDateView - $startDateView);
        
        $viewByDate             = $views->getAppViewsByDate(null, $startDate, $endDate);  
        $downloadsByDate        = $downloads->getAppDownloadsByDate(null, $startDate, $endDate);
        
       // $viewsByMultipleApps = $views->getAllAppViewsByDateAndApp($startDate, $endDate, 10);
        
        $viewTrends             = $views->getTotalViewTrends(null, $startDate, $endDate, $productId);
        $downloadTrends         = $downloads->getTotalDownloadsTrends(null, $startDate, $endDate, $productId);

        $this->view->viewTrend  = $viewTrends;
        $this->view->downloadTrends = $downloadTrends;
        
        $this->view->startTimeView  = $startDate;
        $this->view->endTimeView    = $endDate;
        
        $this->view->appView            = is_array($viewByDate) ? $viewByDate : array();
        $this->view->appDownloads       = is_array($downloadsByDate) ? $downloadsByDate : array();
        
        $this->view->appViewDateJson        = json_encode($viewByDate);
        $this->view->appDownloadDateJson    = json_encode($downloadsByDate);
       // $this->view->appViewComparisonJson  = json_encode($viewsByMultipleApps);  
    }
    
    
    /**
     * 
     * Currently, this action is identical to views except for the data it examines
     * We'll be adding more features later to differentiate them so a duplicate 
     * method is ok for now
     */
    public function downloadsAction() {
    	
        $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));       
        
        $views          = new Nexva_Analytics_ProductView(); 
        $downloads      = new Nexva_Analytics_ProductDownload();
        $gap = 0;
        
        $productId  = $this->_request->getParam('pro_id', false);
        $product    = $this->__getProduct($productId);
        $this->view->product = $product;
        
        $viewByDate         = $views->getAppViewsByDate(null, $startDate, $endDate, $gap, $productId);
        $downloadsByDate    = $downloads->getAppDownloadsByDate(null, $startDate, $endDate, $gap, $productId);
        $chapbreakdown      = $downloads->getAppDownloadsByChap(null, $startDate, $endDate, $productId);
        
        $downloadsByApp     = array();
        $viewCountsByApp    = array();       
        if (!$product) {
            $downloadsByApp     = $downloads->getAppDownloadsByApp(null, $startDate, $endDate, 20, $viewCountsByApp); //needs to be paged
            $this->view->viewCounts         = $viewCountsByApp;
        }
        
        $this->view->startTimeView  = $startDate ;
        $this->view->endTimeView    = $endDate;
        
        $this->view->appView            = is_array($viewByDate) ? $viewByDate : array();
        $this->view->appDownloads       = is_array($downloadsByDate) ? $downloadsByDate : array();
        $this->view->downloadsByApp     = $downloadsByApp;
        
        $this->view->appViewDateJson        = json_encode($this->view->appView);
        $this->view->appDownloadDateJson    = json_encode($this->view->appDownloads);
        $this->view->appDownloadJson        = json_encode(array_slice($this->view->downloadsByApp, 0, 10));
        $this->view->appDownloadsByChapJson     = is_array($chapbreakdown) ? json_encode(array_slice($chapbreakdown,0,10)) : array();
    }
     
    public function mostDownloadsAction() {
    	
        $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));          
        
        $statisticDownload = new Model_StatisticDownload();
        $productDownloads = array_slice($statisticDownload->getDownloadCountWithApp($startDate, $endDate),0,10);
        
        $downloadsByApp=array();
        foreach ($productDownloads as $row){
            $downloadsByApp[$row->name]=$row->count;
        }
        $this->view->startTimeView  = $startDate ;
        $this->view->endTimeView    = $endDate;


        $this->view->appDownloadJson        = json_encode(array_slice($downloadsByApp, 0, 10));
    }
    
    public function chapDownloadsAction() {
    	
        $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));       
        
        $views          = new Nexva_Analytics_ProductView(); 
        $downloads      = new Nexva_Analytics_ProductDownload();
        
        $chapbreakdown      = $downloads->getAppDownloadsByChap(null, $startDate, $endDate);
        
        
        $this->view->startTimeView  = $startDate ;
        $this->view->endTimeView    = $endDate;
        
        
        $this->view->appDownloadsByChapJson     = is_array($chapbreakdown) ? json_encode(array_slice($chapbreakdown,0,10)) : array();
    }
    
    
    
    public function appAction() {
    	 
    	$startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
    	$endDate  = $this->_request->getParam('to_view', date('Y-m-t'));
    
    	$views          = new Nexva_Analytics_ProductView();
    	$downloads      = new Nexva_Analytics_ProductDownload();
    	$gap = 0;
    
    	$productId  = $this->_request->getParam('pro_id', false);
    	$product    = $this->__getProduct($productId);
    	
    	
    	$this->view->product = $product;
    	
    	$this->view->productId  = ($productId) ? $productId : 'Product Id';
    
    	$viewByDate         = $views->getAppViewsByDate(null, $startDate, $endDate, $gap, $productId);
    	$downloadsByDate    = $downloads->getAppDownloadsByDate(null, $startDate, $endDate, $gap, $productId);
    	$chapbreakdown      = $downloads->getAppDownloadsByChap(null, $startDate, $endDate, $productId);
    	

    
    	//$downloadsByApp     = array(); removed from 20-04-2017
    	$viewCountsByApp    = array();
    	if (!$product) {
    		//$downloadsByApp     = $downloads->getAppDownloadsByApp(null, $startDate, $endDate, 20, $viewCountsByApp); //needs to be paged removed from 20-04-2017
    		$this->view->viewCounts         = $viewCountsByApp;
    	}
    
    	$this->view->startTimeView  = $startDate ;
    	$this->view->endTimeView    = $endDate;
    
    	$this->view->appView            = is_array($viewByDate) ? $viewByDate : array();
    	$this->view->appDownloads       = is_array($downloadsByDate) ? $downloadsByDate : array();
    	//$this->view->downloadsByApp     = $downloadsByApp;

    	$this->view->appViewDateJson        = json_encode($this->view->appView);
    	$this->view->appDownloadDateJson    = json_encode($this->view->appDownloads);
    	//$this->view->appDownloadJson        = json_encode(array_slice($this->view->downloadsByApp, 0, 5));
    	$this->view->appDownloadsByChapJson     = is_array($chapbreakdown) ? json_encode(array_slice($chapbreakdown,0,10)) : array();
    	
    	$this->view->queryString = '';
    }
    
    
    /**
     * 
     * Currently, this action is identical to downloads except for the data it examines
     * We'll be adding more features later to differentiate them so a duplicate 
     * method is ok for now
     */
    public function viewsAction() {
        
    	$startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));    
        $gap = 0;   
        
        $views          = new Nexva_Analytics_ProductView(); 
        $downloads      = new Nexva_Analytics_ProductDownload();
        
        $productId  = $this->_request->getParam('pro_id', false);
        $product    = $this->__getProduct($productId);
        $this->view->product = $product;
        
        $viewByDate         = $views->getAppViewsByDate(null, $startDate, $endDate, $gap, $productId);
        $downloadsByDate    = $downloads->getAppDownloadsByDate(null, $startDate, $endDate, $gap, $productId);
        $chapbreakdown      = $views->getAppViewsByChap(null, $startDate, $endDate, $productId);
           
        $viewsByApp         = array();
        $downloadCounts     = array();
        if (!$product) {
            $viewsByApp         = $views->getMostViewedAppsCountByDateRangeForCp(null, $startDate,  $endDate, 20, $downloadCounts); //needs to be paged
            $this->view->downloadCounts         = $downloadCounts;
        }

        
        $this->view->startTimeView  = $startDate;
        $this->view->endTimeView    = $endDate;
        
        
        $this->view->appView            = is_array($viewByDate) ? $viewByDate : array();
        $this->view->appDownloads       = is_array($downloadsByDate) ? $downloadsByDate : array();
        $this->view->viewsByApp         = $viewsByApp;
        
        $this->view->appViewDateJson        = json_encode($this->view->appView);
        $this->view->appDownloadDateJson    = json_encode($this->view->appDownloads);
        $this->view->appViewJson            = json_encode(array_slice($this->view->viewsByApp, 0, 5));
        $this->view->appViewsByChapJson     = is_array($chapbreakdown) ? json_encode(array_slice($chapbreakdown,0,13)) : array(); 
        
    }
    
    public function allViewsAction() {
        
    	$page=$this->_request->getParam('page', 1);  
        if ($this->_request->isPost()) {
            $startDate  =  $this->getRequest()->getPost('from_view'); 
            $endDate  = $this->getRequest()->getPost('to_view');
            $page=1;
        }else{
            $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
            $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));  
        }  
        
        /* Get all product downloads counts*/
        $statisticProduct = new Model_StatisticProduct();
        $statisticProductsPaginater = Zend_Paginator::factory($statisticProduct->getViewsCountWithApp($startDate, $endDate));
        $totalViews=0;
        foreach ($statisticProductsPaginater as $product){
            $totalViews += $product->count;
        }
        $statisticProductsPaginater->setCurrentPageNumber($page);
        $statisticProductsPaginater->setItemCountPerPage(15);       
        /* end */

        $downloads = new Nexva_Analytics_ProductDownload();    
       
        /* Get all product view counts*/
        $productDownloadsCounts=$downloads->getAllAppDownloadsByDate($startDate, $endDate);        
        /* end */
        
        $this->view->startTimeView  = $startDate ;
        $this->view->endTimeView    = $endDate;
        
        $this->view->productDownloadCounts  = $productDownloadsCounts ;
        $this->view->productViewCounts    = $statisticProductsPaginater;
        $this->view->sum    = $totalViews;
       
        
        
    }
    
         
    public function mostViewAction() {
    	
        $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));        
        
        $statisticProduct = new Model_StatisticProduct();
        $statisticProductsDetails = Zend_Paginator::factory($statisticProduct->getViewsCountWithApp($startDate, $endDate));
        $viewsByApp=array();
        
        foreach ($statisticProductsDetails as $row){
            $viewsByApp[$row->name]=$row->count;
        }
        
        $this->view->startTimeView  = $startDate ;
        $this->view->endTimeView    = $endDate;

        $this->view->appViewJson        = json_encode(array_slice($viewsByApp, 0, 10));
    }
    
    public function chapViewsAction() {
    	
        $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));       
        
        $views          = new Nexva_Analytics_ProductView(); 
        $chapbreakdown      = $views->getAppViewsByChap(null, $startDate, $endDate);
        
        
        $this->view->startTimeView  = $startDate ;
        $this->view->endTimeView    = $endDate;
        
        
         $this->view->appViewsByChapJson     = is_array($chapbreakdown) ? json_encode(array_slice($chapbreakdown,0,10)) : array();
    }
    
    /**
     * Gives geographical data regarding app views/app downloads, used only with AJAX.
     * Delay loaded as it will slow things down otherwise 
     */
    public function summaryRegionJsonAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        
	    $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));
        $chap = $this->_request->getParam('chap');
        //echo $chap;die();
        
        $productId  = $this->_request->getParam('pro_id', false); 
        $product    = $this->__getProduct($productId);
        $this->view->product = $product;
        
        $mode   = $this->_getParam('mode', 'view');
        
        switch ($mode) {
            case 'downloads':
                $downloads  = new Nexva_Analytics_ProductDownload();
                $data   = ($productId) ? $downloads->getTotalDownloadByRegion($startDate, $endDate, $productId) : $downloads->getTotalDownloadByRegion($startDate, $endDate);
                break;
                
            case 'views':
            default:
                $views  = new Nexva_Analytics_ProductView();
                $data   = $views->getTotalViewsByRegion($startDate, $endDate); 
                break;
        }

        $data   = array(
            'REGIONS'       => $data,
            'REGION_COUNT'  => count($data)
        );
        echo json_encode($data);
    }
    
    
    public function devicesAction() {
        
    	$startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));
        
        $productId  = $this->_request->getParam('pro_id', false);
        $product    = $this->__getProduct($productId);

        $this->view->product = $product;
        
        $views          = new Nexva_Analytics_ProductView(); 
        $viewByDevice   = $views->getAppViewsByDevice($startDate, $endDate);
       
         $productViewCountPerDevice = array();
        
        foreach($viewByDevice as $viewsPerDevice ) 	{
        	 $key = 'Brand-'.$viewsPerDevice->brand .' - '.$viewsPerDevice->model;
        	 $productViewCountPerDevice[$key] = $viewsPerDevice->count;
        }
        
        

        
        $topDevices = array_slice($productViewCountPerDevice, 0, 6);
        
        $otherResults   = array('Other' => array_sum($productViewCountPerDevice) - array_sum($topDevices));
        $topDevices = array_merge($topDevices, $otherResults); 
            
        
        $this->view->startTimeView  = $startDate;
        $this->view->endTimeView    = $endDate;
        
  
        $this->view->appVisitsDevice        = $productViewCountPerDevice;
        $this->view->appVisitsDeviceJson    = json_encode($topDevices);       
    }
    
    public function regionsAction() {

        
        $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));

        $chapSession = new Zend_Session_Namespace('chapAnalytics');
        $chapSession->id = $this->_request->getParam('chap');

        $userModel = new Admin_Model_UserDetails();
        $this->view->chaps  = $userModel->getActiveChaps();

        $productId  = $this->_request->getParam('pro_id', false); 
        $product    = $this->__getProduct($productId);
        $this->view->product = $product;
        
        $mode   = $this->_getParam('mode', 'view');
        
        switch ($mode) {
            case 'downloads':
                $downloads  = new Nexva_Analytics_ProductDownload();
                $downloads->validateDates($startDateView, $endDateView);
                $data   = $downloads->getTotalDownloadByRegion($startDate, $endDate);
                $this->view->metric    = 'Downloads';
                break;

            case 'views-mobile':
                $views  = new Nexva_Analytics_ProductView();
                $views->validateDates($startDateView, $endDateView);
                $data   = $views->getTotalViewsByRegion($startDateView, $endDateView, $productId, 'MOBILE');
                $this->view->metric    = 'Views on Mobile';
                break;

            case 'views-web':
                $views  = new Nexva_Analytics_ProductView();
                $views->validateDates($startDateView, $endDateView);
                $data   = $views->getTotalViewsByRegion($startDateView, $endDateView, $productId, 'WEB');
                $this->view->metric    = 'Views on Web';
                break;    
                
            case 'views':
                /*$views  = new Nexva_Analytics_ProductView();
                //$views->validateDates($startDateView, $endDateView);
                $data   = $views->getTotalViewsByRegion($startDate, $endDate, $chap, $productId);
                $this->view->metric    = 'Views';
                break;*/
            default:
                $views  = new Nexva_Analytics_ProductView();
                $data   = $views->getTotalViewsByRegion($startDate, $endDate);
                $this->view->metric    = 'Views';
                //$this
                break;
        } 

        $this->view->startTimeView  = $startDate;
        $this->view->endTimeView    = $endDate;
        $this->view->appRegions     = $data;
        $this->view->chap = $chapSession->id;
    }
    
    public function buildsAction() {
        
        
        $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));
        
        $downloads = new Nexva_Analytics_ProductDownload();

        //$gap    = $downloads->getChartGroupGap($endDateView - $startDateView);
        
        $productId  = $this->_request->getParam('pro_id', false); 
        $product    = $this->__getProduct($productId);
        $this->view->product = $product;

        $downloadsByBuild   = $downloads->getAppDownloadsByBuild(null, $startDate, $endDate, 200, $productId); 
        $productDownloadsByPlatform=array();
        
          foreach ($downloadsByBuild as $platform)	{
        	
        	$productDownloadsByPlatform[$platform->name] = $platform->count;
        	
        }

        
        
        
        $topFiveBuilds      = array_slice($productDownloadsByPlatform, 0, 5);
        
        $this->view->startTimeView  = $startDate;
        $this->view->endTimeView    = $endDate;

        $this->view->appBuilds        = $productDownloadsByPlatform;
        $this->view->appBuildsJson    = json_encode($topFiveBuilds);  
    }
    
    
    /**
     * 
     * Simply looks through the POST array and makes it to a GET url for bookmarking
     * This uses a whitelist, so remember to add your parameters to the $params
     * variable 
     */
    private function __postToGet() {
        if ($this->getRequest()->isPost()) {
            $params = $this->__validParameters;
            $requestParams  = array_merge($this->_getAllParams(), $this->_request->getPost()); 
            $url    = "/{$requestParams['controller']}/{$requestParams['action']}/";
            
            foreach ($params as $param) {
                $value      = $this->_request->getPost($param, $this->_getParam($param, null)); //getting from POST, else GET
                if ($value) {
                    $url        .= "{$param}/{$value}/";    
                }
            }
            $this->_redirect($url);
        }
    }
    
    /**
     * Convenience function to generate a query string with all the params 
     */
    private function __getQueryString() {
            $params         = $this->__validParameters;
            $requestParams  = $this->_getAllParams(); 
            $qs         = '';
            foreach ($params as $param) {
                $value      = $this->_getParam($param, null);
                if ($value) {
                    $qs        .= "{$param}/{$value}/";    
                }
            }
            return $qs;
    }
    
    /**
     * 
     * Commonly used code. Sets defaults and checks for existing date range
     * Not to be replaced with the date validation that is done in the model. 
     * THAT IS DIFFERENT. DON'T REMOVE THIS!
     * @param unknown_type $startDate
     * @param unknown_type $endDate
     */
    private function __getValidDates(&$startDate, &$endDate) { //for the weekend, you know ;)
        $startDate  = strtotime("-1 month");
        $endDate    = strtotime("+2 hour");
        
        
        if ($from   = $this->_request->getParam('from_view', null)) {
            $from       = strtotime($from);
            $startDate  = ($from) ? $from : null; 
        }
        
        if ($to   = $this->_request->getParam('to_view', null)) {
            $to         = strtotime($to);
            $endDate    = ($to) ? $to : null;
        }
    }
    
    /**
     * 
     * Gets product information. Also redirects to analytics index if the 
     * product is not owned by the current user.
     * Returns false if a product ID is not supplied
     * 
     * @param int $proId
     * @param boolean $redirect
     */
    private function __getProduct($proId = false, $redirect = true) {
        if (!$proId) {
            return false;
        }
        
        $productModel   = new Model_Product();
        $product        = $productModel->getProductDetailsById($proId);
        return $product;
    }
    
    
    
        public function updatecountryAction() {
        	
         $statisticDownloadModel  = new Model_StatisticDownload();
    $geoData = Nexva_GeoData_Ip2Country_Factory::getProvider();
            
    $records = $statisticDownloadModel->getAllStatRecords();
    

            
    foreach($records as $record){
    	
    
    	
        $ip = trim($record->ip);

        if(!empty($ip))	{
           	 $country  =   $geoData->getCountry($ip);
           	 $statisticDownloadModel->updateCountyCode($record->id, $country['code']);
        }
        Zend_Debug::dump($ip. ' -  '.$country['code'].'\n');
        $ip = '';
           	
    }

        	
        }

    public function chapanalyticsAction()
    {
        if ($this->_request->isPost()) {
            $from  = $this->_getParam('from', date('Y-m-d'));
            $to    = $this->_getParam('to', date('Y-m-d'));
            $chap = $this->_getParam('chaps'); // $id = $this->_getParam("id");
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'analytics/chapanalytics/from/' . $from . '/to/' . $to.'/chaps/'.$chap);
        }
        $firstDayThisMonth = date('Y-m-01');
        $lastDayThisMonth = date('Y-m-d');

        $from  = $this->_getParam('from', $firstDayThisMonth );
        $to    = $this->_getParam('to', $lastDayThisMonth);

        $this->view->from  = $from;
        $this->view->to    = $to;

        $user = new Model_User();
        $cps_list = $user ->select()
                          ->from('users')
                          ->where('users.type = "CHAP"')
                          ->order('users.id desc');
        //echo $cps_list->assemble();die();

        $cps_list->query();

        $cps_list = $user->fetchAll($cps_list);
        $this->view->cps_list = $cps_list;

        $chap = $this->_getParam('chaps');
        $this->view->chap = $chap;

        //$chapProductModel = new Admin_Model_ChapProducts();
        //$chapProducts = $chapProductModel->chapAnalytics($chap,$from,$to);

        $statisticsDownloadsModel = new Admin_Model_StatisticsDownloads();
        $chapSales = $statisticsDownloadsModel->getSalesValue($from, $to);

        $paginator = Zend_Paginator::factory($chapSales);
        $paginator->setCurrentPageNumber($this->_getParam('page'),1);
        $paginator->setItemCountPerPage(10);

        $this->view->chapSales = $paginator;

    }
    
        /*get all inapp payments*/
    public function inappPaymentsAction(){
        
        $inappPaymentModel = new Admin_Model_InappPayments();
        $allInappPayment=$inappPaymentModel->getAllInappPaymentWithSkipPhoneNumber(array('081235356533','242066782417'));      
        
        
        $pagination = Zend_Paginator::factory($allInappPayment);

        if (count($allInappPayment) > 0) {
            $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
            $pagination->setItemCountPerPage(10);

            $this->view->inappDetails = $pagination;
            unset($pagination);
        } else {
             $this->view->inappDetails = null;             
        }
    }
    
    public function allDownloadsAction(){        
        $page=$this->_request->getParam('page', 1);  
        if ($this->_request->isPost()) {
            $startDate  =  $this->getRequest()->getPost('from_view', date('Y-m-01')); 
            $endDate  = $this->getRequest()->getPost('to_view', date('Y-m-t'));
            $page=1;
        }else{
            $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
            $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));  
        }
        
        $views          = new Nexva_Analytics_ProductView(); 
        
        /* Get all product downloads counts*/
        $statisticDownload = new Model_StatisticDownload();
        $productDownloadsPaginater = Zend_Paginator::factory($statisticDownload->getDownloadCountWithApp($startDate, $endDate));
        $totalDownloads=0;
        foreach ($productDownloadsPaginater as $download){
            $totalDownloads += $download->count;
        }
        $productDownloadsPaginater->setCurrentPageNumber($page);
        $productDownloadsPaginater->setItemCountPerPage(100);       
        /* end */
      
        /* Get all product view counts*/
        $productViewCounts=$views->getAllAppViewsByDate($startDate, $endDate);        
        /* end */
        
        $this->view->startTimeView  = $startDate ;
        $this->view->endTimeView    = $endDate;
        
        $this->view->productViewCounts  = $productViewCounts ;
        $this->view->productDownloadCounts    = $productDownloadsPaginater;
        $this->view->sum    = $totalDownloads;
        
    
    }
        
     public function inappRevenueAction() {
       
        if ($this->_request->isPost()) {
            $startDate  =  $this->getRequest()->getPost('from_view', date('Y-m-01')); 
            $endDate  = $this->getRequest()->getPost('to_view', date('Y-m-t'));
            $page=1;
        }else{
            $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
            $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));  
        }
        
        $page=$this->_request->getParam('page', 1);  
        
        $inappPayments  = new Admin_Model_InappPayments();
        
        
        $revenueValues = $inappPayments->getRevenue($startDate, $endDate);        
        $revenueFormattedArray = array();        
        foreach ($revenueValues as $revenue)	{        	
        	$revenueFormattedArray[$revenue->channel] = floatval($revenue->sum);        	
        }
      
        $paymentReceivedListPaginater = Zend_Paginator::factory($inappPayments->getPaymentReceivedList($startDate, $endDate));
      
        $paymentReceivedListPaginater->setCurrentPageNumber($page);
        $paymentReceivedListPaginater->setItemCountPerPage(15);       
       
        $this->view->paymentReceivedList = $paymentReceivedListPaginater;
        
        $this->view->startTimeView  =   $startDate;
        $this->view->endTimeView    =   $endDate;
        


        $this->view->appBuilds        = $revenueFormattedArray;
        $this->view->appBuildsJson    = json_encode($revenueFormattedArray);  
      
     }
     
    public function premiumRevenueAction() {
         
        if ($this->_request->isPost()) {
            $startDate  =  $this->getRequest()->getPost('from_view', date('Y-m-01')); 
            $endDate  = $this->getRequest()->getPost('to_view', date('Y-m-t'));
            $page=1;
        }else{
            $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
            $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));  
        }
        
        $page=$this->_request->getParam('page', 1);  
        
        $interopPayments  = new Admin_Model_InteropPayments();
        
        
        $revenueValues = $interopPayments->getRevenue($startDate, $endDate);        
        $revenueFormattedArray = array();        
        foreach ($revenueValues as $revenue)	{        	
        	$revenueFormattedArray[$revenue->channel] = floatval($revenue->sum);        	
        }
      
        $paymentReceivedListPaginater = Zend_Paginator::factory($interopPayments->getPaymentReceivedList($startDate, $endDate));
      
        $paymentReceivedListPaginater->setCurrentPageNumber($page);
        $paymentReceivedListPaginater->setItemCountPerPage(15);       
       
        $this->view->paymentReceivedList = $paymentReceivedListPaginater;
        
        $this->view->startTimeView  =   $startDate;
        $this->view->endTimeView    =   $endDate;
        


        $this->view->appBuilds        = $revenueFormattedArray;
        $this->view->appBuildsJson    = json_encode(array_slice($revenueFormattedArray,0,10)); 
    }
    
      public function appWallAction() {
        
        $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));

        $appWall          = new Admin_Model_AppWall(); 
        
        $appWallChaps   = $appWall->getAppWallChaps($startDate, $endDate);
        $appWallData="";
        foreach ($appWallChaps as $row){
            $appWallData[$row->meta_value]   = $appWall->getAppWall($row->chap_id,$startDate, $endDate);
        }
        $appWallSummaryData=$appWall->getAppWallSummary($startDate, $endDate);
        
        $appWallDetails = array();
        
        foreach($appWallSummaryData as $raw ){
        	 $appWallDetails[$raw->meta_value] = $raw->count;
        }

        $this->view->startTimeView  =  $startDate;
        $this->view->endTimeView    =  $endDate;
        
        $this->view->getAppWallSummary    = json_encode($appWallDetails);     
        $this->view->appWallChaps    = json_encode($appWallChaps);     
        $this->view->appWallJson    = json_encode($appWallData);     
 
    }
    
    public function appCountAction(){        
        
        
        $categoryParent  = $this->_request->getParam('category_parent',NULL);
        $categorySub=null;
        if($this->_request->getParam('category_sub') !='null'){
            $categorySub=$this->_request->getParam('category_sub');
        }
        
        $categoryObj = new Model_Category();
        $this->view->categories = $categoryObj->getAllPerantCategory();
        $this->view->subCategories = $categoryObj->getAllSubCategory();
        $this->view->subCategoryId = $categorySub;
        $this->view->categoryParent = $categoryParent;
        //var_dump($this->view->subCategories);die();
        if($this->getRequest()->isPost()){
            if(!is_null($categoryParent)&& !empty($categoryParent)){
                $this->view->subCategories = $categoryObj->getAllSubCategoryPerantId($categoryParent);
            }
            $productObj =new Model_ProductCategories();
            $this->view->categoryCount = $productObj->getAppCountByCategory($categoryParent,$categorySub);
        }
    }
        
}


