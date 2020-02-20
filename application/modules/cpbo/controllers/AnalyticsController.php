<?php
class Cpbo_AnalyticsController extends Nexva_Controller_Action_Cp_MasterController {
    
    /**
     * A whitelist of allowed parameters. Used by the methods in this controller 
     * to strip out modified URLs
     * @var array
     */
    private $__validParameters    = array('from_view', 'to_view', 'pro_id');
    
    public function postDispatch() {
        parent::postDispatch();
      //  $this->view->queryString            = $this->__getQueryString();
    }
    
    
    public function indexAction(){ 
            $this->_redirect('/analytics/dashboard');
    }
    
    function dashboardAction() {
    	


        $productId  = $this->_request->getParam('product', false);
        $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));
        
        
        $views          = new Nexva_Analytics_ProductView(); 
        $downloads      = new Nexva_Analytics_ProductDownload();
        
        $viewsNp = new Model_StatisticNexpager();
        
        $viewByDate     = $views->getAppViewsByDate($this->_getCpId(), $startDate, $endDate);
        $downloadsByDate    = $downloads->getAppDownloadsByDate($this->_getCpId(), $startDate, $endDate);
        
        $views->validateDates($startDateView, $endDateView);

        $values = $views->getAllAppViewsByDateAndAppForCp($this->_getCpId(), $startDate, $endDate, 10);
              
        $userAccount  = new Model_UserAccount();
        $revenue = $userAccount->getTotalRevenueForUser($this->_getCpId(), $startDate, $endDate);
        
        
        $viewTrends             = $views->getTotalViewTrends($this->_getCpId(), $startDateView, $endDateView, $productId);
        $downloadTrends         = $downloads->getTotalDownloadsTrends($this->_getCpId(), $startDateView, $endDateView, $productId);

        $this->view->viewTrend  = $viewTrends;
        $this->view->downloadTrends = $downloadTrends;
        
        
        $this->view->totalNpViews = count($viewsNp->getAllViews($this->_getCpId(), $startDate, $endDate));
        
        $this->view->startTimeView  = $startDate ;
        $this->view->endTimeView    = $endDate;
        
        $this->view->revenue = $revenue;
        
        $this->view->appView            = is_array($viewByDate) ? $viewByDate : array();
        $this->view->appDownloads       = is_array($downloadsByDate) ? $downloadsByDate : array();
        
        $this->view->appViewDateJson        = json_encode($viewByDate);
        $this->view->appDownloadDateJson    = json_encode($downloadsByDate);
        $this->view->appViewComparisonJson  = json_encode($values);        
    }
    
    /**
     * 
     * Currently, this action is identical to downloads except for the data it examines
     * We'll be adding more features later to differentiate them so a duplicate 
     * method is ok for now
     */
    public function viewsAction() {
    	
        $productId  = $this->_request->getParam('product', false);
        $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));

        $views          = new Nexva_Analytics_ProductView(); 
        $downloads      = new Nexva_Analytics_ProductDownload();
        
        $viewByDate     = $views->getAppViewsByDate($this->_getCpId(), $startDate, $endDate);
        $downloadsByDate    = $downloads->getAppDownloadsByDate($this->_getCpId(), $startDate, $endDate);

        
        $views->validateDates($startDateView, $endDateView);

        $mostViewsByApp         = $views->getMostViewedAppsCountByDateRangeForCp($this->_getCpId(), $startDate, $endDate); 
        
        
      
        //needs to be paged

        $product =  new Model_Product();      
        $productViews  =  $product->getProductsListByCpWithDownloadAndViewCount($this->_getCpId(),  $startDate, $endDate);
       
        
        $totalViews = 0;
        
        foreach ($productViews as $app )	{
        	$totalViews += $app->views;
        }
 
        $chapbreakdown = $views->getAppViewsByChap($this->_getCpId(),  $startDate,  $endDate);
               
        $this->view->startTimeView  = $startDate;
        $this->view->endTimeView    = $endDate;
        
        $this->view->appView            = is_array($viewByDate) ? $viewByDate : array();
        $this->view->appDownloads       = is_array($downloadsByDate) ? $downloadsByDate : array();
        $this->view->viewsByApp         = $productViews;
        $this->view->totalViews = $totalViews;
        
        
    
        
        
        $this->view->appViewDateJson        = json_encode($this->view->appView);
        $this->view->appDownloadDateJson    = json_encode($this->view->appDownloads);
        $this->view->appViewJson            = json_encode($mostViewsByApp);

        $this->view->appViewsByChapJson     = is_array($chapbreakdown) ? json_encode(array_slice($chapbreakdown,0,6)) : array();              
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
        
        $viewByDate     = $views->getAppViewsByDate($this->_getCpId(), $startDate, $endDate);
        $downloadsByDate    = $downloads->getAppDownloadsByDate($this->_getCpId(), $startDate, $endDate);


        
        $product =  new Model_Product();      
        $productDownloads  =  $product->getProductsListByCpWithDownloadAndViewCount($this->_getCpId(),  $startDate, $endDate);
        $downloadsByApp = $downloads->getAppDownloadsByApp($this->_getCpId(), $startDate, $endDate, 100); //needs to be paged

        
        $totalDownloads = 0;

        foreach ($productDownloads as $app )	{       	
        	$totalDownloads += $app->downloads;
        }
        
        $chapbreakdown = $downloads->getAppDownloadsByChap($this->_getCpId(),  $startDate, $endDate);
        
        $this->view->startTimeView  = $startDate;
        $this->view->endTimeView    = $endDate;
        
        $this->view->appView            = is_array($viewByDate) ? $viewByDate : array();
        $this->view->appDownloads       = is_array($downloadsByDate) ? $downloadsByDate : array();
        $this->view->downloadsByApp     = $downloadsByApp;
        
        $this->view->totalDownloads =  $totalDownloads;
        
        $this->view->productDownloads = $productDownloads;
        
        $this->view->appViewDateJson        = json_encode($this->view->appView);
        $this->view->appDownloadDateJson    = json_encode($this->view->appDownloads);
        $this->view->appDownloadJson        = json_encode(array_slice($downloadsByApp,0,5));
        $this->view->appDownloadsByChapJson     = is_array($chapbreakdown) ? json_encode(array_slice($chapbreakdown,0,5)) : array();
    }
    
    
    
    public function devicesAction() {
        
    	$productId  = $this->_request->getParam('product', false);
        $startDate  = $this->_request->getParam('from_view', date('Y-m-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));

        $views          = new Nexva_Analytics_ProductView(); 
        
        $viewByDevice   = $views->getAppViewsByDevice($startDate, $endDate, $this->_getCpId(), 200, $productId);
        
        $productViewCountPerDevice = array();
        
        foreach($viewByDevice as $viewsPerDevice ) 	{
        	 $key = 'Brand-'.$viewsPerDevice->brand .' - '.$viewsPerDevice->model;
        	 $productViewCountPerDevice[$key] = $viewsPerDevice->count;
        }


        $this->view->startTimeView  =  $startDate;
        $this->view->endTimeView    =  $endDate;
        
        $this->view->appVisitsDevice        = $productViewCountPerDevice;
        $this->view->appVisitsDeviceJson    = json_encode($productViewCountPerDevice);     

   
        
        
    }
    
    
    public function buildsAction() {
        $this->__postToGet();
        
        $productId  = $this->_request->getParam('product', false);
        $startDate  = $this->_request->getParam('from_view', date('Y-08-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));
        $this->__getValidDates($startDateView, $endDateView);
        
        $downloads      = new Nexva_Analytics_ProductDownload();
        $downloadsByBuild   = $downloads->getAppDownloadsByBuild($this->_getCpId(), $startDate, $endDate, 200, $productId); 
        
        $productDownloadsByPlatform = array();
        
        foreach ($downloadsByBuild as $platform)	{
        	
        	$productDownloadsByPlatform[$platform->name] = $platform->count;
        	
        }
        
        $this->view->startTimeView  =   $startDate;
        $this->view->endTimeView    =    $endDate;

        $this->view->appBuilds        = $productDownloadsByPlatform;
        $this->view->appBuildsJson    = json_encode($productDownloadsByPlatform);  
        

    }
    
    
    public function revenueAction() {
    	
        //$this->__postToGet();
        
        $productId  = $this->_request->getParam('product', false);
        $startDate  = $this->_request->getParam('from_view', date('Y-08-01'));
        $endDate  = $this->_request->getParam('to_view', date('Y-m-t'));
         
        $userAccount  = new Model_UserAccount();
        
        $cpid = $this->_getCpId();
        
        $revenueValues = $userAccount->getTotalRevenueForCpPerChannel($cpid, $startDate, $endDate);
        
        $revenueFormattedArray = array();
        
        foreach ($revenueValues as $revenue)	{
        	
        	$revenueFormattedArray[$revenue->channel] = floatval($revenue->sum);
        	
        }
      
        $paymentReceivedList = $userAccount->getPaymentReceivedList($startDate, $endDate, $cpid, 'WEB');
        
        $this->view->paymentReceivedList = $paymentReceivedList;
        
        $this->view->startTimeView  =   $startDate;
        $this->view->endTimeView    =   $endDate;
        


        $this->view->appBuilds        = $revenueFormattedArray;
        $this->view->appBuildsJson    = json_encode($revenueFormattedArray);  
      

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
        
        if ($product['uid'] != $this->_getCpId() && $redirect) {
            $this->_redirect('/analytics');
        } 
        return $product;
    }
    
    /**
     * 
     * Commonly used code. Sets defaults and checks for existing date range
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
    
    function sampleAction() {
        $host       = Zend_Registry::get('config')->analytics->mongodb->host;
        $port       = Zend_Registry::get('config')->analytics->mongodb->port;
        $dbname     = Zend_Registry::get('config')->analytics->mongodb->dbname;
        
        $m      = new Mongo("mongodb://{$host}:{$port}", array("persist" => "nexva_analytics"));
        $db = $m->selectDB($dbname);    
        
        //views
        $collection = $db->app_views;
        
        //$cp         = array(50);
        $cp         = array(43); //-local
        
        //$products   = array(1615, 3108, 3112, 3117, 3121, 7124, 7377, 7383, 7389, 3106);
        $products   = array(1051, 714, 623, 1086, 811, 1051, 1371, 505, 720, 1103, 631, 2533, 1067, 672, 2508, 530, 397); //-local
        
        $minTime    = strtotime("01-06-2011");
        $maxTime    = strtotime("15-07-2011");;
        
        for($i = 0; $i < 4000; $i++) {
            
            $ai         = rand(0, count($products) - 1);
            $proId      = (string) $products[$ai];
            if (empty($proId)) {
                die('died here ' . $ai);
            }
            $cpId       = $cp;
            $date       = rand($minTime, $maxTime);
            
            $deviceNums = rand(1, 3);  
            $selectedDevices    = array();
            for ($j = 0; $j < $deviceNums; $j++) {
                $devices    = rand(1, 100);
                $selectedDevices[$devices]  = 'Device ' . $devices;
            }
            
            $view = array(            
                  'app_id' => $proId, 
                  'device_id' => $selectedDevices, 
                  'cp_id' => $cpId,
                  'chap_id' => 3424,
                  'ip' => '127.0.0.1', 
                  'ua' => 'NokiaE61-1/3.0 (1.0610.04.04) SymbianOS/9.1 Series60/3.0 Profile/MIDP-2.0 Configuration/CLDC-1.1 FirePHP/0.5', 
                  'timestamp' => $date
            );
            $collection->insert($view);
        }
        echo $collection->count() . '<br>'; 
        
        
        
        $collection = $db->app_downloads;
        for($i = 0; $i < 2000; $i++) {
            
            $ai         = rand(0, count($products) - 1);
            $proId      = (string) $products[$ai];
            if (empty($proId)) {
                die('died here ' . $ai);
            }
            $cpId       = $cp;
            $date       = rand($minTime, $maxTime);
            
            $deviceId    = rand(1, 100);
            
            $view   = array(
              "app_id" =>  $proId,
              "build_id" =>  $proId,
              "device_id" =>  $deviceId,
              "device_name" =>  $deviceId . " Device",
              "chap_id" =>  3424,
              "cp_id" =>  $cpId,
              "source" =>  "OPEN",
              "download_id" =>  "27063",
              "ip" =>  "127.0.0.1",
              "ua" =>  "NokiaN73-2\/3.0-630.0.2 Series60\/3.0 Profile\/MIDP-2.0 Configuration\/CLDC-1.1 FirePHP\/0.5",
              "timestamp" =>  $date
            );
            $collection->insert($view);
        }
                     
         
        echo $collection->count();
        $cursor = $collection->find();
        
        foreach ($cursor as $obj) {
            Zend_Debug::dump($obj); die();
        }
        die();
    }
}