<?php

class Api_NexapiController extends Zend_Controller_Action {

    protected $serverPathThumb = "http://thor.nexva.com/vendors/phpThumb/phpThumb.php?src=";
    protected $serverPath = "http://thor.nexva.com/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/";
    protected $loggerInstance;

    const BAD_REQUEST_CODE = "400";
    
    
    public function init() {

        //Disabling the layout and views
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $skip_action_names = array('openmobile', 'pay-appp');
        if(!in_array($this->getRequest()->getActionName(), $skip_action_names)) {

        $this->loggerInstance = new Zend_Log();
        $path_to_log_file = APPLICATION_PATH . "/../logs/api_request." . APPLICATION_ENV . ".log";
        $writer = new Zend_Log_Writer_Stream($path_to_log_file);
        $this->loggerInstance->addWriter($writer);
        
        $this->validateHeaderParams();

        $headersParams = apache_request_headers();

        $userAgent = $headersParams['User-Agent'];
        $chapId = $headersParams['Chap-Id'];
        $this->loggerInstance->log('Request ::' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . ' :: - Request IP ' . $_SERVER['REMOTE_ADDR'] . ' :: - User-Agent - ' . $userAgent . ' :: -Chap-Id-' . $chapId, Zend_Log::INFO);

        }

    }

  /** 
    * Returns the configuration of a particular CHAP 
    * @param User-Agent (HTTP request headers) User Agent of the client device
    * @param Chap-Id Chap ID (HTTP request headers)
    * returns JSON encoded config array (currency rate, currency symbol, etc..)
    */
    public function configurationsAction()
    {
        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        
        //Get currency details
        $currencyUserModel = new Api_Model_CurrencyUsers();        
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        
        //Load them meta model
        $themeMeta   = new Model_ThemeMeta();
        $themeMeta->setEntityId($chapId);

        $allConstants['rates'] = $currencyDetails;
        $allConstants['keywords'] = $themeMeta->WHITELABLE_SITE_ADVERTISING;
        $allConstants['appstore_version'] =  $themeMeta->WHITELABLE_SITE_APPSTORE_VERSION;
        $allConstants['add_interval'] =  $themeMeta->WHITELABLE_SITE_INTERVAL;
        $allConstants['appstore_latest_version'] = 1.2;
        $allConstants['latest_build_url'] = 'https://production.applications.nexva.com.s3.amazonaws.com/productfile/29297/Nexva_-_mtn.apk';
        

        
        $this->getResponse()
                    ->setHeader('Content-type', 'application/json');
     
        $this->loggerInstance->log('Response ::' . json_encode($currencyDetails),Zend_Log::INFO);
        echo str_replace('\/','/',json_encode($currencyDetails));
        
    }
    
    public function appConfigurationsAction()
    {
    	//Validate Heder params
    	$headersParams = $this->validateHeaderParams();
    
    	$userAgent = $headersParams['userAgent'];
    	$chapId = $headersParams['chapId'];
    
    	//Get currency details
    	$currencyUserModel = new Api_Model_CurrencyUsers();
    	$currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
    
    	//Load them meta model
    	$themeMeta   = new Model_ThemeMeta();
    	$themeMeta->setEntityId($chapId);
    
    	$allConstants['keywords'] = $themeMeta->WHITELABLE_SITE_ADVERTISING;
    	$allConstants['appstore_version'] =  $themeMeta->WHITELABLE_SITE_APPSTORE_VERSION;
    	$allConstants['add_interval'] =  $themeMeta->WHITELABLE_SITE_INTERVAL;
    	$allConstants['appstore_latest_version'] = 1.2;
    	$allConstants['latest_build_url'] = 'https://production.applications.nexva.com.s3.amazonaws.com/productfile/29297/Nexva_-_mtn.apk';
    
    	$this->getResponse()
    	->setHeader('Content-type', 'application/json');
    	 
    	$this->loggerInstance->log('Response ::' . json_encode($currencyDetails),Zend_Log::INFO);
    	echo str_replace('\/','/',json_encode($currencyDetails +  $allConstants));
    
    }
    
    /**
     * Returns the set of applications based on Device and Chap with a page limit   
     * 
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * @param page Page number (GET) Optional
     * @param limit App limit (GET) Optional
     * returns JSON encoded apps array
     */
    public function allAppsAction() {

        //$this->__validateToken($this->_getParam('token', 0));

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        //Get the parameters               
        $category = trim($this->_getParam('category'));
        $offset = trim($this->_getParam('offset', 0));
        $limit = trim($this->_getParam('limit', 10));


        //Check if Category has been provided
        if ($category === null || empty($category)) {
            $this->__echoError("6000", "Category not found", self::BAD_REQUEST_CODE);
        }

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) {

            $this->__echoError("2000", "Device not found", self::BAD_REQUEST_CODE);
        } 
        else 
           { 
            ////Get the Apps based on Chap and the Device
            $ApiModel = new Nexva_Api_NexApi();

            //Get category wise applications
            $allApps = $ApiModel->allAppsByChapAction($chapId, $deviceId, $category, $limit, $offset);

            //change the thumbnail path
            if (count($allApps) > 0) 
            {
                unset($allApps["user_id"]);
                $this->getResponse()->setHeader('Content-type', 'application/json');
                
                $apps = str_replace('\/', '/', json_encode($allApps));
                $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                
                echo $apps;
            } 
            else 
            {

                $this->__echoError("3000", "Data Not found", self::BAD_REQUEST_CODE);
            }
        }
    }

    /**
     * 
     * Returns the Featured Applications based on Device and Chap
     * 
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * returns JSON encoded apps array
     */
    public function featuredAppsAction() {


        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        //Get the parameters               
        $category = trim($this->_getParam('category', null));

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) {
            
            $this->__echoError("2000", "Device not found", self::BAD_REQUEST_CODE);
        } else { //Get Featured Apps based on Chap and the Device
            $ApiModel = new Nexva_Api_NexApi();

            $apiCall = true;

            //Featured apps for banners
            $featuredApps = $ApiModel->featuredAppsAction($chapId, 15, $deviceId, $apiCall, $category);

            //change the thumbnail path
            if (count($featuredApps) > 0) {

                $apps = str_replace('\/', '/', json_encode($featuredApps));
                $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                echo $apps;

                
            } else {
               $this->__echoError("3000", "Data Not found", self::BAD_REQUEST_CODE);                
            }
        }
    }

    /**
     * 
     * Returns the details of a particular app     
     * 
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * @param $appId App ID (GET)
     * returns JSON encoded $downloadLink
     */
    public function detailsAppAction() {

        //$userId = $this->__validateToken($this->_getParam('token', 0));
        $userId="";
        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        //App Id
        $appId = $this->_getParam('appId', null);
        $screenWidth = $this->_getParam('width', 320);
        $screenHeight = $this->_getParam('height', 480);

        //Check if App Id has been provided
        if ($appId === null || empty($appId)) {

            $this->__echoError("1001", "App Id not found", self::BAD_REQUEST_CODE);
        }

        /************************************
          //ToDO
          //Check if the app compatible with the device
         **************************************/

        //Instantiate the Api_Model_ChapProducts model
        $chapProductModel = new Api_Model_ChapProducts();

        //check if the app belongs to the CHAP
        $appCount = $chapProductModel->getProductCountByChap($chapId, $appId);

        if ($appCount == 0) {

            $this->__echoError("1003", "App does not belong to this partner", self::BAD_REQUEST_CODE);
        }

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) {

            $this->__echoError("2000", "Device not found", self::BAD_REQUEST_CODE);
            
        } 
        else             
        {
            $ApiModel = new Nexva_Api_NexApi();
            //Get the details of the app
            $appDetails = $ApiModel->appDetailsById($appId, $deviceId, $screenWidth, $screenHeight);
          
            if (!empty($appDetails) || $appDetails !== null) 
            {
                //************* Add Statistics - View *************************
                $source = "API";
                $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');

                $modelViewStats = new Api_Model_StatisticsProducts();
                $modelViewStats->addViewStat($appId, $chapId, $source, $ipAddress, $deviceId, $userId);

                /********************End Statistics ******************************/
        
                
                $this->getResponse()
                        ->setHeader('Content-type', 'application/json');

                $apps = str_replace('\/', '/', json_encode($appDetails));
                $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                echo $apps;
                
            } 
            else 
            {

                $this->__echoError("3000", "Data Not found", self::BAD_REQUEST_CODE);
            }
        }
    }

    /**
     * 
     * Returns the download url of the app. This is a direct link to the app file on S3 server wrapped by the 
     * relevent parameters (AWSAccessKeyId,Expires,Signature).
     * 
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * @param $appId App ID (GET)
     * returns JSON encoded $downloadLink
     */
    public function downloadAppAction() {

    	$sessionId = $this->_getParam('token', 0);
   	
        $userId = $this->__validateToken($sessionId);
        
        //Validate Header params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        $appId = $this->_getParam('appId');
        $buildId = $this->_getParam('build_Id');

        /************************************
          //ToDO
          //Check if the app compatible with the device
         ****************************** */

        //Check if App Id has been provided
        if ($appId === null || empty($appId)) 
        {
            $this->__echoError("1001", "App Id not found", self::BAD_REQUEST_CODE);            
        }
        //Check if Chap Id has been provided
        if ($buildId === null || empty($buildId)) 
        {
            $this->__echoError("1002", "Build Id not found", self::BAD_REQUEST_CODE);
        }


        //Check if the app belongs to the CHAP
        $chapProductModel = new Api_Model_ChapProducts();

        $appCount = $chapProductModel->getProductCountByChap($chapId, $appId);

        if ($appCount == 0) {

            
            $this->__echoError("1003", "App does not belong to this partner", self::BAD_REQUEST_CODE);
        }

        /*         * **************************************************************** */

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) {

            
             $this->__echoError("2000", "Device not found", self::BAD_REQUEST_CODE);
            
        } else {

            //get the app details
            $productModel = new Api_Model_Products();
            $appDetails = $productModel->getProductDetailsbyId($appId);

            if ($appDetails->price > 0) {

                
                $this->__echoError("7001", "Invalid download request", self::BAD_REQUEST_CODE);
            }

            //Get the S3 URL of the Relevant build
            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

            //************* Add Statistics - Download *************************
            $source = "API";
            $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');


            $model_ProductBuild = new Model_ProductBuild();
            $buildInfo = $model_ProductBuild->getBuildDetails($buildId);

            $modelQueue = new Partner_Model_Queue();

            $modelQueue->removeDownlaodedItem($userId, $chapId);
            $modelDownloadStats = new Api_Model_StatisticsDownloads();
            $modelDownloadStats->addDownloadStat($appId, $chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $deviceId, $sessionId);

            /**             * **************End Statistics ******************************* */
            $downloadLink = array();
            $downloadLink['download_app'] = $buildUrl;

            $this->getResponse()->setHeader('Content-type', 'application/json');

            //Here str_replace has been used to prevent of adding '/' when ecnoded by JSON
            //thre is a solution but ,JSON_UNESCAPED_UNICODE is only works in PHP 5.4+, http://php.net/manual/en/function.json-encode.php
            $encodedDownloadLink = str_replace('\/', '/', json_encode($downloadLink));
            $this->loggerInstance->log('Response ::' . $encodedDownloadLink,Zend_Log::INFO);
            echo $encodedDownloadLink;
        }
    }

    /**
     * Pay and download a premium app
     * In this function it will call the Interop Payment gateway to make a mobile payment and if succeeded, 
     * Returns the download url of the app. This is a direct link to the app file on S3 server wrapped by the 
     * relevent parameters (AWSAccessKeyId,Expires,Signature).
     * 
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * @param $appId App ID (GET)
     * @param $buildId Build ID (GET)
     * @param $mobileNo Mobile No (GET)
     * returns JSON encoded $downloadLink
     */
//    public function payAppAction() {
//
//    	$sessionId = $this->_getParam('token', 0);
//    	
//        $userId = $this->__validateToken($sessionId);
//
//        //Validate Heder params
//        $headersParams = $this->validateHeaderParams();
//
//        $userAgent = $headersParams['userAgent'];
//        $chapId = $headersParams['chapId'];
//
//        $appId = $this->_getParam('appId');
//        $buildId = $this->_getParam('build_Id');
//        $mobileNo = $this->_getParam('mdn');
//
//        //Testing Mobile No
//        //$mobileNo = '5155328687';
//        //$userAgent = "Mozilla/5.0 (Linux; U; Android 1.5; fr-fr; Galaxy Build/CUPCAKE) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2";         
//        //$chapId = 8056;
//        //Check if App Id has been provided
//        if ($appId === null || empty($appId)) {
//
//            
//            $this->__echoError("1001", "App Id not found", self::BAD_REQUEST_CODE);
//            
//        }
//        //Check if Chap Id has been provided
//        if ($buildId === null || empty($buildId)) {
//
//            
//            $this->__echoError("1002", "Build Id not found", self::BAD_REQUEST_CODE);
//            
//        }
//        //Check if Chap Id has been provided
//        if ($mobileNo === null || empty($mobileNo)) {
//            
//            $this->__echoError("5000", "Mobile Number not found", self::BAD_REQUEST_CODE);
//        }
//
//
//        //Check if the app belongs to the CHAP
//        $chapProductModel = new Api_Model_ChapProducts();
//        $appCount = $chapProductModel->getProductCountByChap($chapId, $appId);
//
//        if ($appCount == 0) {
//
//            
//            $this->__echoError("1003", "App does not belong to this partner", self::BAD_REQUEST_CODE);
//            
//        }
//
//        /*         * ***************************************************************** */
//
//        //Detect the device id from thd db according to the given user agent
//        $deviceId = $this->deviceAction($userAgent);
//
//        //Check if the device was detected or not, if not retrun a message as below
//        if ($deviceId === null || empty($deviceId)) {
//
//            
//            $this->__echoError("2000", "Device not found", self::BAD_REQUEST_CODE);
//        } else {
//            $price = 0;
//            $appName = "";
//
//            //get the app details
//            $productModel = new Api_Model_Products();
//            $appDetails = $productModel->getProductDetailsbyId($appId);
//
//            //Check if app details available
//            if (is_null($appDetails)) {
//                $this->__echoError("3001", "This app has been removed or does not exist", self::BAD_REQUEST_CODE);
//            }
//
//            //Check if the app is a premium one
//            if ($appDetails->price <= 0) {
//
//                $this->__echoError("7000", "Price is invalid", self::BAD_REQUEST_CODE);
//                
//            } else {
//                $price = $appDetails->price;
//                $appName = $appDetails->name;
//            }
//
//            //Format the price in the format required by the payment gateway
//            $interopPymtCls = new Nexva_Api_PaymentInterop();
//            $formattedPrice = $interopPymtCls->formatPriceAction($price);
//
//            //Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)   
//            $interopPaymentsModel = new Api_Model_InteropPayments();
//            $paymentId = $interopPaymentsModel->addInteropPayment($chapId, $appId, $buildId, $mobileNo, $price);
//
//            //Do the transaction and get the status                        
//            $payStatus = $interopPymtCls->doPayment($chapId, $appId, $mobileNo, $appName, $formattedPrice);
//
//            //convert the XML string to an Object
//            $payStatus = simplexml_load_string($payStatus);
//
//            $paymentTimeStamp = $payStatus->Timestamp;
//            $paymentResult = $payStatus->Result;
//            $paymentTransId = $payStatus->TransactionID;
//            $paymentStatusCode = $payStatus->StatusCode;
//
//            //Check if payment was made success, Provide the download link
//            if ($paymentStatusCode == '00' && $paymentResult == 'Success') {
//                //Get the S3 URL of the Relevant build
//                $productDownloadCls = new Nexva_Api_ProductDownload();
//                $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
//
//                //Update the relevant Transaction record in the DB
//                $interopPaymentsModel->updateInteropPayment($paymentId, $paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
//                
//                //commented this out as it update live data 
//                
//                //$userAccount = new Model_UserAccount();
//                //$userAccount->saveRoyalitiesForApi($appId, $price, $paymentMethod='CHAP', $chapId);
//                
//
//
//                //************* Add Statistics - Download *************************
//                $source = "API";
//                $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');
//
//
//                $model_ProductBuild = new Model_ProductBuild();
//                $buildInfo = $model_ProductBuild->getBuildDetails($buildId);
//
//
//                $modelQueue = new Partner_Model_Queue();
//                $modelQueue->removeDownlaodedItem($userId, $chapId);
//
//                $modelDownloadStats = new Api_Model_StatisticsDownloads();
//                $modelDownloadStats->addDownloadStat($appId, $chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $deviceId, $sessionId);
//
//       
//                
//
//                /******************End Statistics ******************************* */
//
//                $downloadLink = array();
//                $downloadLink['download_app'] = $buildUrl;
//
//                $this->getResponse()
//                        ->setHeader('Content-type', 'application/json');
//
//                //Here str_replace has been used to prevent of adding '/' when ecnoded by JSON
//                //thre is a solution but ,JSON_UNESCAPED_UNICODE is only works in PHP 5.4+, http://php.net/manual/en/function.json-encode.php
//                $encodedDownloadLink = str_replace('\/', '/', json_encode($downloadLink));
//                $this->loggerInstance->log('Response :: Pay app response not logged' ,Zend_Log::INFO);
//                echo $encodedDownloadLink;
//            } else {
//
//                
//                $this->__echoError("4000", "Payment was unsuccessfull", self::BAD_REQUEST_CODE);
//            }
//        }
//    }

    
    public function payAppAction() {

    	$sessionId = $this->_getParam('token', 0);
    	
        $userId = $this->__validateToken($sessionId);

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        $appId = $this->_getParam('appId');
        $buildId = $this->_getParam('build_Id');
        $mobileNo = $this->_getParam('mdn');

        //Testing Mobile No
        //$mobileNo = '5155328687';
        //$userAgent = "Mozilla/5.0 (Linux; U; Android 1.5; fr-fr; Galaxy Build/CUPCAKE) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2";         
        //$chapId = 8056;
        
        //Check if App Id has been provided
        if ($appId === null || empty($appId)) {

            $this->__echoError("1001", "App Id not found", self::BAD_REQUEST_CODE);            
        }
        //Check if Chap Id has been provided
        if ($buildId === null || empty($buildId)) 
        {            
            $this->__echoError("1002", "Build Id not found", self::BAD_REQUEST_CODE);            
        }
        //Check if Chap Id has been provided
        if ($mobileNo === null || empty($mobileNo)) 
        {            
            $this->__echoError("5000", "Mobile Number not found", self::BAD_REQUEST_CODE);
        }

        //Check if the app belongs to the CHAP
        $chapProductModel = new Api_Model_ChapProducts();
        $appCount = $chapProductModel->getProductCountByChap($chapId, $appId);

        if ($appCount == 0) {
            
            $this->__echoError("1003", "App does not belong to this partner", self::BAD_REQUEST_CODE);            
        }

        /********************************************************************* */

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) 
        {
            $this->__echoError("2000", "Device not found", self::BAD_REQUEST_CODE);
        } 
        else 
        {
            $price = 0;
            $appName = "";

            //get the app details
            $productModel = new Api_Model_Products();
            $appDetails = $productModel->getProductDetailsbyId($appId);

            //Check if app details available
            if (is_null($appDetails)) {
                $this->__echoError("3001", "This app has been removed or does not exist", self::BAD_REQUEST_CODE);
            }

            //Check if the app is a premium one
            if ($appDetails->price <= 0) {

                $this->__echoError("7000", "Price is invalid", self::BAD_REQUEST_CODE);
                
            } else {
                $price = $appDetails->price;
                $appName = $appDetails->name;
            }
            
            

            /*************************** Begin Payment Process *******************************************/
            
            //Get payment gateway Id of the CHAP
            
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
            
           
            $pgType = $pgDetails->gateway_id; 
            $paymentGatewayId = $pgDetails->payment_gateway_id;
            
            //Call Nexva_MobileBilling_Factory and create relevant instance
            $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);            
            
            //Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
            $pgClass->addMobilePayment($chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId);
          
            //Do the transaction and get the build url                        
            $buildUrl = $pgClass->doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price);         

            //Check if payment was made success, Provide the download link
            if(!empty($buildUrl) && !is_null($buildUrl)) 
            {
                //************* Add Royalties *************************
                $userAccount = new Model_UserAccount();
                $userAccount->saveRoyalitiesForApi($appId, $price, $paymentMethod='CHAP', $chapId, $userId);
                
                //************* Add Statistics - Download *************************
                $source = "API";
                $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');

                $model_ProductBuild = new Model_ProductBuild();
                $buildInfo = $model_ProductBuild->getBuildDetails($buildId);

//                $modelQueue = new Partner_Model_Queue();
//                $modelQueue->removeDownlaodedItem($userId, $chapId);
//
                $modelDownloadStats = new Api_Model_StatisticsDownloads();
                $modelDownloadStats->addDownloadStat($appId, $chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $deviceId, $sessionId);

                /******************End Statistics ******************************* */

                $downloadLink = array();
                $downloadLink['download_app'] = $buildUrl;

                $this->getResponse()
                        ->setHeader('Content-type', 'application/json');

                //Here str_replace has been used to prevent of adding '/' when ecnoded by JSON
                //thre is a solution but ,JSON_UNESCAPED_UNICODE is only works in PHP 5.4+, http://php.net/manual/en/function.json-encode.php
                $encodedDownloadLink = str_replace('\/', '/', json_encode($downloadLink));
                $this->loggerInstance->log('Response :: Pay app response not logged' ,Zend_Log::INFO);
                echo $encodedDownloadLink;
            } 
            else 
            {                
                $this->__echoError("4000", "Payment was unsuccessfull", self::BAD_REQUEST_CODE);
            }
        }
    }
    
    
    /**
     * Downaload already downloaded apps
     * In this function it allows to doenload thier downloaded apps for another 2 times.
     * Returns the download url of the app. This is a direct link to the app file on S3 server wrapped by the
     * relevent parameters (AWSAccessKeyId,Expires,Signature).
     *
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * @param $appId App ID (GET)
     * @param $buildId Build ID (GET)
     * @param $mobileNo Mobile No (GET)
     * @return JSON encoded $downloadLink
     */
    public function downloadDownloadedAppsAction() {


        $sessionId = $this->_getParam('token', 0);
        $userId = $this->__validateToken($sessionId);

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        $appId = $this->_getParam('appId');
        $buildId = $this->_getParam('build_Id');
        $mobileNo = $this->_getParam('mdn');

        //Testing Mobile No
        //$mobileNo = '5155328687';
        //$userAgent = "Mozilla/5.0 (Linux; U; Android 1.5; fr-fr; Galaxy Build/CUPCAKE) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2";
        //$chapId = 8056;
        //Check if App Id has been provided
        if ($appId === null || empty($appId)) {


            $this->__echoError("1001", "App Id not found", self::BAD_REQUEST_CODE);

        }
        //Check if Chap Id has been provided
        if ($buildId === null || empty($buildId)) {


            $this->__echoError("1002", "Build Id not found", self::BAD_REQUEST_CODE);

        }
        //Check if Chap Id has been provided
        if ($mobileNo === null || empty($mobileNo)) {


            $this->__echoError("5000", "Mobile Number not found", self::BAD_REQUEST_CODE);
        }


        //Check if the app belongs to the CHAP
        $chapProductModel = new Api_Model_ChapProducts();
        $appCount = $chapProductModel->getProductCountByChap($chapId, $appId);

        if ($appCount == 0) {


            $this->__echoError("1003", "App does not belong to this partner", self::BAD_REQUEST_CODE);

        }

        /******************************************************************* */

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) {

            $this->__echoError("2000", "Device not found", self::BAD_REQUEST_CODE);
        } else {

            //get the app details
            $productModel = new Api_Model_Products();
            $appDetails = $productModel->getProductDetailsbyId($appId);

            //Check if app details available
            if (is_null($appDetails)) {
                $this->__echoError("3001", "This app has been removed or does not exist", self::BAD_REQUEST_CODE);
            }

            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

            //check whether the app has been downloaded 2 times or less, if it downloaded 2 times it wont allowed download anymore
            $userDownloadModel = new Api_Model_UserDownloads();
            $capable = $userDownloadModel->checkDownloadCapability($appId,$chapId,$userId,$buildId);

            if($capable)
            {
                /************* Add Statistics - Download *************************/
                $source = "API";
                $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');


                $model_ProductBuild = new Model_ProductBuild();
                $buildInfo = $model_ProductBuild->getBuildDetails($buildId);


                $modelQueue = new Partner_Model_Queue();
                $modelQueue->removeDownlaodedItem($userId, $chapId);

                $modelDownloadStats = new Api_Model_StatisticsDownloads();
                $modelDownloadStats->addDownloadStat($appId, $chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $deviceId, $sessionId);

                /******************End Statistics ******************************* */

                $downloadLink = array();
                $downloadLink['download_app'] = $buildUrl;

                $this->getResponse()
                    ->setHeader('Content-type', 'application/json');

                //Here str_replace has been used to prevent of adding '/' when ecnoded by JSON
                //thre is a solution but ,JSON_UNESCAPED_UNICODE is only works in PHP 5.4+, http://php.net/manual/en/function.json-encode.php
                $encodedDownloadLink = str_replace('\/', '/', json_encode($downloadLink));
                $this->loggerInstance->log('Response :: Pay app response not logged' ,Zend_Log::INFO);
                echo $encodedDownloadLink;

                //since the download link has already sent adding a download record for UserDownload table
                $userDownloadModel->addDownloadRecord($appId,$chapId,$userId,$buildId);
            }
            else
            {
                $this->__echoError("1004", "Maximum Allowed Download Limit Reached", self::BAD_REQUEST_CODE);
            }
        }
    }
    
    
    /**
     * 
     * Returns the set of app categories (Parent Categories)
     * 
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * returns JSON encoded $downloadLink
     */
    public function categoryAction() {

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        //Get all categories
        $ApiModel = new Nexva_Api_NexApi();
        $allCategories = $ApiModel->categoryAction($chapId);
        //$cat_list = array();
        //$cat_list["category"]=$allCategories;
        if (count($allCategories) > 0) 
         {
            $this->getResponse()
                    ->setHeader('Content-type', 'application/json');
     
            echo str_replace('\/','/',json_encode($allCategories));
            $this->loggerInstance->log('Response ::' . json_encode($allCategories),Zend_Log::INFO);
           
	    
        } 
        else 
        {
            $this->__dataNotFound();
        }
    }

    /**
     * 
     * Returns the deviced id from the DB based on the User Agent.
     * @param $userAgent User Agent
     * returns $deviceId
     */
    protected function deviceAction($userAgent) {

        //Iniate device detection using Device detection adapter
        $deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();

        //Detect the device
        $exactMatch = $deviceDetector->detectDeviceByUserAgent($userAgent);

        //Device barand name
        $brandName = $deviceDetector->getDeviceAttribute('brand_name', 'product_info');

        //Get the Device ID of nexva db
        $deviceId = $deviceDetector->getNexvaDeviceId();

        return $deviceId;
    }

    protected function echoJson($json, $halt=1) 
    {
        $this->getResponse()
                ->setHeader('Content-type', 'application/json');
        $this->loggerInstance->log('Response ::' . json_encode($json),Zend_Log::INFO);
        
        echo json_encode($json);
        if ($halt)
            die();
    }

    /**
     * 
     * Get Header params, validate and Returns Chap Id and the User Agent.
     * @param User-Agent User Agent
     * @param Chap-Id Chap Id
     * returns userAgent,chapId in an array
     */
    private function validateHeaderParams() {

        //Get all HTTP request headers, this is an associative array
        $headersParams = apache_request_headers();

        //We need only User-Agent, Chap-Id
        $userAgent = !empty($headersParams['User-Agent']) ? $headersParams['User-Agent'] : '';
        $chapId = !empty($headersParams['Chap-Id']) ? $headersParams['Chap-Id'] : '';


        //Check if User-Agent has been provided
        if ($userAgent === null || empty($userAgent)) {

            $this->__echoError("0000", "User Agent not provided", self::BAD_REQUEST_CODE);
        }

        //Check if Chap Id has been provided
        if ($chapId === null || empty($chapId)) {

            $this->__echoError("1000", "Chap Id not found", self::BAD_REQUEST_CODE);
        }

        $rtArray['userAgent'] = $userAgent;
        $rtArray['chapId'] = $chapId;

        return $rtArray;
    }

    /**
     * User Registration
     * @param firstName
     * @param lastName
     * @param mobileNumber
     * @param email
     * @param password
     * returns activation code,user id, success_code as a JSON response
     */
    public function registerAction() {

        include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );
        
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];

        $firstName = $this->_request->firstName;
        $lastName = $this->_request->lastName;
        $mobileNumber = $this->_request->mobileNumber;
        $email = $this->_request->email;
        $password = $this->_request->password;

        // Input field validation.
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email';
            $this->__echoError("2001", "Invalid email.", self::BAD_REQUEST_CODE);
        }

        if (strlen($password) < 6) {
            $errors[] = 'Your password must be at least 6 characters long';
            $this->__echoError("2002", "Your password must be at least 6 characters long.", self::BAD_REQUEST_CODE);
        }
        if ($firstName == '') {
            $errors[] = 'Empty first name';
            $this->__echoError("2003", "Empty first name.", self::BAD_REQUEST_CODE);
        }
        if ($lastName == '') 
        {
            $errors[] = 'Empty last name';
            $this->__echoError("2004", "Empty last name.", self::BAD_REQUEST_CODE);
        }
        if ($mobileNumber == '' || (strlen($mobileNumber) < 10)) 
        {
            $errors[] = 'Invalid mobile number';
            $this->__echoError("2005", "Invalid mobile number.", self::BAD_REQUEST_CODE);
        }
        
        $user = new Api_Model_Users();
        
        if (!$user->validateUserEmail($email)) 
        {
            $errors[] = "This email already exists.";
            $this->__echoError("2006", "This email already exists.", self::BAD_REQUEST_CODE);
        }
        
        
        if (!$user->validateUserMdn($mobileNumber))
        {
        	$errors[] = "This mobile number already exists. Please use another number.";
        	$this->__echoError("2007", "This mobile number already exists. Please use another number.", self::BAD_REQUEST_CODE);
        }
        

        // When errors generated send out error response and terminate.
        if (!empty($errors)) 
        {
            $temp = array("message"=>"Data validation failed","error_details"=>$errors);
            $this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
        }

        //Generate activation code
        $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
        
        
        // send sms start
        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
         
        $pgType = $pgDetails->gateway_id;
         
        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
         
        $message = 'Please use this verification code '.$activationCode.' to complete your registration.';
         
        $pgClass->sendSms($mobileNumber, $message, $chapId);
        
        //  send sms end
        
        $userData = array(
            'username' => $email,
            'email' => $email,
            'password' => $password,
            'type' => "USER",
            'login_type' => "NEXVA",
            'chap_id' => $chapId,
            'mobile_no' => $mobileNumber,
            'activation_code' => $activationCode
        );

        $userId = $user->createUser($userData);

        $userMeta = new Model_UserMeta();
        $userMeta->setEntityId($userId);
        $userMeta->FIRST_NAME = $firstName;
        $userMeta->LAST_NAME = $lastName;
        $userMeta->TELEPHONE = $mobileNumber;
        $userMeta->UNCLAIMED_ACCOUNT = '0';
        
        if($userId > 0)
        {
            // send sms start
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
             
            $pgType = $pgDetails->gateway_id;
             
            $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
             
            $message =  'You have completed your registration successfully.';
             
            $pgClass->sendSms($mobileNumber, $message, $chapId);
            // send sms end
        }
        $response = array(
                            'user' => $userId, 
                            'activation_code' => $activationCode, 
                            'success_code' => '1111'
                        );
        
        $this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
        
        echo json_encode($response);
    }

    /**
     * Verify user
     * @param user id
     * @param activation code
     * returns user id, message, success_code as a JSON response
     */
    public function verifyUserAction()
    {
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        
        //Get the parameters               
        $userId = trim($this->_getParam('userId'));
        $activationCode = trim($this->_getParam('activationCode'));
        
        //Check if User Id has been provided
        if ($userId === null || empty($userId)) 
        {
            $this->__echoError("8001", "User Id not found", self::BAD_REQUEST_CODE);            
        }
        //Check if Activaion Code has been provided
        if ($activationCode === null || empty($activationCode)) 
        {
            $this->__echoError("8002", "Verification Code not found", self::BAD_REQUEST_CODE);
        }        
        
        $userModel = new Api_Model_Users();
        //Check if combination exists
        $recordCount = $userModel->getUserCountById($chapId, $userId, $activationCode);
        
        if(empty($recordCount) || is_null($recordCount) || $recordCount <= 0)
        {
            $this->__echoError("8003", "Combination does not match", self::BAD_REQUEST_CODE);
        } 
        
        $status = 1;
        
        //update the status
        if($userModel->updateVerificationStatus($userId, $status))
        {
            $response = array(
                                'user' => $userId,
                                'message' => 'User Verified Successfully',
                                'success_code' => '2222'
                            );
            
            $this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);        
            echo json_encode($response);            

        }
        else
        {
            $this->__echoError("8004", "Verification has been done already", self::BAD_REQUEST_CODE);
        }
    }
    
    public function verifyUserPassAction()
    {
    	$headersParams = $this->validateHeaderParams();
    	$chapId = $headersParams['chapId'];
    
    	//Get the parameters
    	$userId = trim($this->_getParam('userId'));
    	$activationCode = trim($this->_getParam('activationCode'));
    
    	//Check if User Id has been provided
    	if ($userId === null || empty($userId))
    	{
    		$this->__echoError("8001", "User Id not found", self::BAD_REQUEST_CODE);
    	}
    	//Check if Activaion Code has been provided
    	if ($activationCode === null || empty($activationCode))
    	{
    		$this->__echoError("8002", "Verification Code not found", self::BAD_REQUEST_CODE);
    	}
    
    	$userModel = new Api_Model_Users();
    	//Check if combination exists
    	$recordCount = $userModel->getUserCountById($chapId, $userId, $activationCode);
    
    	if(empty($recordCount) || is_null($recordCount) || $recordCount <= 0)
    	{
    		$this->__echoError("8003", "Combination does not match", self::BAD_REQUEST_CODE);
    	}
    
    	$status = 1;
    	
    	$response = array(
    			'user' => $userId,
    			'message' => 'User Verified Successfully',
    			'success_code' => '2222'
    	);
    	
    	$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
    	echo json_encode($response);
   	
    }
    
    public function signinAction() 
    {        
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        
        $password = $this->_request->password;
        $username = $this->_request->username;

        if ($username == '') 
        {
            $this->__echoError("8005", "Emptry username", self::BAD_REQUEST_CODE);  
            //$errors[] = 'Emptry user name';
        }
        if ($password == '') 
        {
            $this->__echoError("8006", "Empty password", self::BAD_REQUEST_CODE);  
            //$errors[] = 'Empty password';
        }
        if (empty($errors)) 
        {
            $userObj = new Api_Model_Users();
            $tmpUser = $userObj->getUserByEmail($username);
            
            if (is_null($tmpUser)) 
            {
                $this->__echoError("8007", "Invalid username or password", self::BAD_REQUEST_CODE); 
                //$errors[] = 'Invalid user';
            }
            if (((empty($errors))) && ($tmpUser->password != md5($password))) 
            {
                $this->__echoError("8007", "Invalid username or password", self::BAD_REQUEST_CODE); 
                //$errors[] = 'Invalid password';
            }
            if($tmpUser->status != 1)
            {
                // $this->__echoError("8008", "User has not verified the account", self::BAD_REQUEST_CODE); 
                
                 $msg = json_encode(
                                        array(
                                                "message" => "User has not verified the account",
                                                "error_code" => "8008",
                                                "user" => $tmpUser->id
                                             )
                                    );
        
                 $this->getResponse()->setHeader('Content-type', 'application/json');
                 $this->loggerInstance->log('Response ::' . $msg, Zend_Log::INFO);

                 echo $msg;
                 exit;
                //$errors[] = 'Invalid password';
            }                
            if($tmpUser->chap_id != $chapId) //Check whether user belongs to the particular CHAP
            {
                $this->__echoError("8009", "User does not belong to the CHAP", self::BAD_REQUEST_CODE); 
                //$errors[] = 'User does not belong to the CHAP';
            }
        }
        // When errors generated, send out error response and terminate.
        if (!empty($errors)) 
        {
            $temp = array("message"=>"Data validation failed","error_details"=>$errors);
            $this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
        }
        
        $sessionUser = new Zend_Session_Namespace('partner_user');
        $sessionUser->id = $tmpUser->id;
        $sessionId = Zend_Session::getId();
        
        $response = array(
                            'user' => $tmpUser->id,
                            'token' => $sessionId, 
                            'mobile_no' => $tmpUser->mobile_no
                         );
        
        $this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
        echo json_encode($response);
    }

    public function resetAction() 
    {
        $this->validateHeaderParams();

        $password = $this->_request->password;
        $username = $this->_request->username;
        $mobileNumber = $this->_request->mobileNumber;

        // Store Mobile number from db.
        $usrMDN = '';

        // Get user object for given email.
        $user = new Model_User();
        //$userRow = $user->getUserByEmail($username);
        
        $userObj = new Api_Model_Users();
        $userRow = $userObj->getUserByEmail($username);

        // META row for Mobile number verification
        //$userMeta = new Model_UserMeta();

        // Input field validation.
        if (!filter_var($username, FILTER_VALIDATE_EMAIL) || (is_null($userRow) )) {
            $errors[] = 'invalid user name';
        }

        if ((empty($errors)) && (strlen($password) < 6)) {
            $errors[] = 'Your password must be at least 6 characters long';
        }
       
        // Check mobile number from user meta, is the user object is available.
        if (!(is_null($userRow))) {
            $usrMDN = $userRow->mobile_no;
        }

        if ((empty($errors)) && (($mobileNumber == '' || (strlen($mobileNumber) < 10) || ($usrMDN != $mobileNumber)))) {
            $errors[] = 'Invalid mobile number';
        }
        // When errors generated send out error response and terminate.
        if (!empty($errors)) {
            $temp = array("message"=>"Data validation failed","error_details"=>$errors);
            $this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
        }

        $user->resetPassword($userRow->id, $password);
        $this->loggerInstance->log('Response ::' . json_encode(array("user" => $userRow->id)),Zend_Log::INFO);
        echo json_encode(array("user" => $userRow->id));
    }
    
    /**
     * Change mobile number
     * @param user id
     * @param activation code
     * returns action code, message, success_code as a JSON response
     */
    public function changeMobileAction()
    {     
        include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );
        
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        
        //Get the parameters               
        $userId = trim($this->_getParam('userId'));
        $mobileNumber = trim($this->_getParam('mobileNumber'));
        
        //Check if User Id has been provided
        if ($userId === null || empty($userId)) 
        {
            $this->__echoError("8001", "User Id Not Found", self::BAD_REQUEST_CODE);            
        }
        //Check if Activaion Code has been provided
        if ($mobileNumber === null || empty($mobileNumber) || (strlen($mobileNumber) < 10)) 
        {
            $this->__echoError("8010", "Invalid Mobile Number", self::BAD_REQUEST_CODE);
        }          
        
         //Generate activation code
        $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
        $status = 0;
        
        $userModel = new Api_Model_Users();
        //update the status
        if($userModel->updateMobileNumebr($userId, $mobileNumber, $activationCode, $status))
        {
           
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
             
            
            $pgType = $pgDetails->gateway_id;
             
            //Call Nexva_MobileBilling_Factory and create relevant instance
            $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
             
            $message = 'Please use this verification code '.$activationCode.' to complete your verification .';
             
            $pgClass->sendSms($mobileNumber, $message, $chapId);
            
        
            $response = array(
                                'user' => $userId, 
                                'activation_code' => $activationCode, 
                                'success_code' => '1111'
                            );
            
            $this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);        
            echo json_encode($response);            

        }
        
        
    }

    /**
     * Resend registration activation code
     * @param user id
     * @param activation code
     * returns action code, message, success_code as a JSON response
     */
    public function resendActivationAction()
    {
        include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );
        
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        
        //Get the parameters               
        $userId = trim($this->_getParam('userId'));
        //$mobileNumber = trim($this->_getParam('mobileNumber'));
        
        //Check if User Id has been provided
        if ($userId === null || empty($userId)) 
        {
            $this->__echoError("8001", "User Id Not Found", self::BAD_REQUEST_CODE);            
        }
              
        
        $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
        $status = 0;
        
        $userModel = new Api_Model_Users();
        
        $mobileNumber = $userModel->getUserMobileById($userId);
        
        //Check if mobile number is given
        if ($mobileNumber === null || empty($mobileNumber)) 
        {
            $this->__echoError("8011", "Mobile Number Not Registered", self::BAD_REQUEST_CODE);            
        }
        
        //update the status
        if($userModel->updateActivationCode($userId, $activationCode, $status))
        {

            // send sms start
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
            	
            
            $pgType = $pgDetails->gateway_id;
            	
            //Call Nexva_MobileBilling_Factory and create relevant instance
            $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
            	
            $message = 'Please use this verification code '.$activationCode.' to complete your registration.';
            	
            $pgClass->sendSms($mobileNumber, $message, $chapId);
            // send sms end
        
            $response = array(
                                'user' => $userId, 
                                'activation_code' => $activationCode, 
                                'success_code' => '1111'
                            );
            
            $this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);        
            echo json_encode($response);            

        }
    }
    
    public function listUpdatesAction() {


        $userId = $this->__validateToken($this->_getParam('token', 0));

        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];


        $limit = $this->_getParam('limit', 10);
        $offset = $this->_getParam('offset', 0);

        // retrive all the downloaded apps 
        $modelDownloadStats = new Api_Model_StatisticsDownloads();
        $model_ProductBuild = new Model_ProductBuild();
        $nexApi = new Nexva_Api_NexApi();

        $deviceId = $this->deviceAction($userAgent);

        $downloadedProducts = $modelDownloadStats->getDownloadedBuilds($userId, 10000);

        $updatedProduct = array();

        // if the user doesn't have any apps downloaded exit with error code
        if (count($downloadedProducts) < 1) {

            $this->__dataNotFound();
        }

        foreach ($downloadedProducts as $downloadedProduct) {

            if ($downloadedProduct['product_id'] and $downloadedProduct['platform_id'] and $downloadedProduct['language_id'] and $downloadedProduct['date']) {

                // check if the products have any updated builds 
                $updated = $model_ProductBuild->checkProductBuildUpdated($downloadedProduct['product_id'], $downloadedProduct['platform_id'], $downloadedProduct['language_id'], $downloadedProduct['date']);

                if ($updated) {

                    $updatedProduct[] = $downloadedProduct;
                }
            }
        }

        if (count($updatedProduct) < 1) {

            $this->__dataNotFound();

        } else {



            // limit the results set   
            $updatedProductPagination = Zend_Paginator::factory($updatedProduct);
            $updatedProductPagination->setCurrentPageNumber($offset);
            $updatedProductPagination->setItemCountPerPage($limit);

            // get the limit product list
            $currentProductList = $updatedProductPagination->getCurrentItems();

            $newFormatedArrayOfProducts = array();
            /* format the array to pass product details fucntion 
             * $updatedProductPagination->getCurrentItems(); return two dimensional array so we need to format this array 
             * as single dimensional array.
             * 
             */
            foreach ($currentProductList as $newTempArr) {
                $newFormatedArrayOfProducts[] = $newTempArr;
            }

            $productsDisplay = array();

            if (!is_null($newFormatedArrayOfProducts)) {

                // get the product details  
                $productsDisplay = $nexApi->getProductDetails($newFormatedArrayOfProducts, $deviceId, true, true);

                if (count($productsDisplay) > 0) {

                   // $this->__printArrayOfDataJson(array("updateList"=>$productsDisplay));
                   $this->__printArrayOfDataJson($productsDisplay);
                } else {

                    $this->__dataNotFound();
                }
            } else {

                $this->__dataNotFound();
            }
        }
    }

    public function downloadQueueAction() {

        $userId = $this->__validateToken($this->_getParam('token', 0));

        $limit = $this->_getParam('limit', 10);
        $offset = $this->_getParam('offset', 0);

        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        $deviceId = $this->deviceAction($userAgent);

        $modelQueue = new Partner_Model_Queue();
        $productsToDownload = $modelQueue->listDownloadQueue($userId, $chapId, $limit, $offset);



        if ($productsToDownload) {

            $nexApi = new Nexva_Api_NexApi();
            $productInfo = $nexApi->getProductDetails($productsToDownload, $deviceId, true);

            if (count($productInfo) > 0) {
                //$this->__printArrayOfDataJson(array("downloadQueue"=>$productInfo));
                $this->__printArrayOfDataJson($productInfo);
            }
        } else {

            $this->__dataNotFound();
        }
    }

    public function myAppsAction() {

        $userId = $this->__validateToken($this->_getParam('token', 0));

        $limit = $this->_getParam('limit', 10);
        $offset = $this->_getParam('offset', 0);

        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        // retrive all the downloaded apps 
        $modelDownloadStats = new Api_Model_StatisticsDownloads();
        $model_ProductBuild = new Model_ProductBuild();

        $downloadedProducts = $modelDownloadStats->getDownloadedBuilds($userId, $limit, $offset);

        $deviceId = $this->deviceAction($userAgent);

        $nexApi = new Nexva_Api_NexApi();
        $downloadedProductInfo = $nexApi->getProductDetails($downloadedProducts, $deviceId, true);

        if ($downloadedProductInfo) {

            if (count($downloadedProductInfo) > 0) {
                //$this->__printArrayOfDataJson(array("myAppList"=>$downloadedProductInfo));
                $this->__printArrayOfDataJson($downloadedProductInfo);
            }
        } else {

            $this->__dataNotFound();
        }
    }

    /**
     * 
     * Top products by category
     * 
     * 
     * returns json product list
     */
    public function topCatAppsAction() {

        //$userId = $this->__validateToken($this->_getParam('token', 0));

        $limit = $this->_getParam('limit', 10);
        $offset = $this->_getParam('offset', 0);
        $categoryId = $this->_getParam('category', 0);

        if ($categoryId == 0) {

            $this->__dataNotFound();
            
        }

        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        $deviceId = $this->deviceAction($userAgent);

        $chapProduct = new Api_Model_ChapProducts();
        $topProducts = $chapProduct->getTopCategoryProducts($chapId, $deviceId, $categoryId, $limit, $offset);

        if ($topProducts) {

            $nexApi = new Nexva_Api_NexApi();
            $productInfo = $nexApi->getProductDetails($topProducts, $deviceId, true);

            if (count($productInfo) > 0) {
                //$this->__printArrayOfDataJson(array("topCatApps"=>$productInfo));
                $this->__printArrayOfDataJson($productInfo);
            }
        } else {

            $this->__dataNotFound();
        }
    }

    /**
     * 
     * Top products
     * 
     * 
     * returns json product list
     */
    public function topAppsAction() {

        //$userId = $this->__validateToken($this->_getParam('token', 0));

        $limit = $this->_getParam('limit', 10);
        $offset = $this->_getParam('offset', 0);

        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        $deviceId = $this->deviceAction($userAgent);

        $chapProduct = new Api_Model_ChapProducts();
        $topProducts = $chapProduct->getTopProductsByDevice($chapId, $deviceId, true, $limit, $offset);

        if ($topProducts) {

            $nexApi = new Nexva_Api_NexApi();
            $productInfo = $nexApi->getProductDetails($topProducts, $deviceId, true);

            if (count($productInfo) > 0) {
                $this->__printArrayOfDataJson($productInfo);
            }
        } else {

            $this->__dataNotFound();
        }
    }

    public function searchAction() {

        //$userId = $this->__validateToken($this->_getParam('token', 0));

        $limit = $this->_getParam('limit', 10);
        $offset = $this->_getParam('offset', 0);

        $keyword = trim($this->_getParam('q', ''));
        if (empty($keyword)) {

            $this->__dataNotFound();
        }

        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        $deviceId = $this->deviceAction($userAgent);

        $productsModel = new Model_Product();
        $searchQry = $productsModel->getSearchQueryChap($keyword, $deviceId, false, $chapId);

        if ($searchQry) {

            $paginator = Zend_Paginator::factory($searchQry);
            $paginator->setItemCountPerPage($limit);
            $paginator->setCurrentPageNumber($offset);

            $products = array();

            if (!is_null($paginator)) {
                $i = 0;
                foreach ($paginator as $row) {

                    $id = (isset($row->id)) ? $row->id : $row->product_id; //can't change the device selector code now 
                    $productinfo = $productsModel->getProductDetailsById($id, true);
                    $products[$i]['product_id'] = $id;
                    $products[$i]['user_id'] = $productinfo['uid'];
                    $products[$i]['thumbnail'] = $productinfo['thumb_name'];
                    $products[$i]['name'] = $productinfo['name'];
                    $products[$i]['price'] = $productinfo['cost'];
                    $i++;
                }
            }


            $nexApi = new Nexva_Api_NexApi();

            $productsDisplay = $nexApi->getProductDetails($products, $deviceId, true);

            if (count($productsDisplay) > 0) {

                //$this->__printArrayOfDataJson(array("searchList"=>$productsDisplay));
                $this->__printArrayOfDataJson($productsDisplay);
            } else {

                $this->__dataNotFound();
            }
        }
    }
    
    
    public function openmobileAction() 
    {

    	
    	$limit = $this->_getParam('limit', 10);
        $offset = $this->_getParam('offset', 0);
        
        if($limit > 20)	{
        	$this->getResponse()->setHeader('Content-type', 'text/xml');
        	echo "<?xml version=\"1.0\"?><error-message> In-valid Request!.  Maximum allowed limit is 10.</error-message>";
        	die();
        }
    	
    	$xmlBody = '';
        
        
        $products = new Api_Model_Products();   
        $productsAll =  $products->getCompatibleAndroidProducts();
        
         
        $productList = Zend_Paginator::factory($productsAll);
        $productList->setItemCountPerPage(20);
        $productList->setCurrentPageNumber($offset);
            
        $statisticDownload = new Model_StatisticDownload();
        $userMeta   = new Model_UserMeta();
        $productMeta = new Model_ProductMeta();
        $productImagesModel = new Model_ProductImages();
        $productDownloadCls = new Nexva_Api_ProductDownload();
        $productCategories = new Model_ProductCategories();
        
        $productImages = new Nexva_View_Helper_ProductImages();
        $serverPathImage = $productImages->productImages() . '/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/';
         
        $imagethumbPostFix = htmlentities("&w=80&h=80&aoe=0&fltr[]=ric|0|0&q=100&f=png");
        
        //Zend_Debug::dump($productList);
        
        $xmlBody = "<?xml version=\"1.0\"?>";
        $xmlBody .=	"<products>";
    	
    	foreach($productList as $list)	{
    		$authorDetails = '';
    		$downloadCount = '';
    		$companyName = '';
    		$keywords = '';
    		$updateDate = '';
    		$catergory = '';
    		$productName = '';
    		
    		$downloadCount = $statisticDownload->getAllDownloadsCount($list->id);

    		$userMeta->setEntityId($list->user_id);


    		
    		
    		$companyName = $userMeta->COMPANY_NAME;
    		if(!isset($companyName))    {
    		    $companyOwnerFname = $userMeta->FIRST_NAME;
    		    $companyOwnerLname = $userMeta->LAST_NAME;
    		    
    		    $companyName = $companyOwnerFname . ' '.$companyOwnerLname;
    		}
    		
    		 $productMeta->setEntityId($list->id);
    		 
    		 $productImagesList = $productImagesModel->getAllImages($list->id);
    	
    		 $catergory = $productCategories->selectedParentCategoryName($list->id);
    		 
    	
    		if(empty($list->build_created_date))
    		    $updateDate = $list->created_date;
            else
                $updateDate = $list->build_created_date;
    	
    		$type = ($list->price > 0) ? 'Commercial' : 'Non-commercial' ;
    		$productName = preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '',$list->name); 
    		
    	$abc = 0;

            $xmlBody .=	"<product id='$list->id' code=''>
                             <cp_product_id>$list->id</cp_product_id>
                             <product_name>".$productName."</product_name>";
            
            if(isset($catergory->id))	{
                $xmlBody .="<category id='$catergory->id' code=''>".$this->_xmlentities($catergory->name)."</category>";
            }
            
                         $xmlBody .="<release_date>$list->created_date</release_date>
                             <downloads_count>$downloadCount->download_count</downloads_count>
    		                 <author>".preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '', $companyName)."</author>
		                     <author_email>$list->email</author_email>
		                     <version>$productMeta->PRODUCT_VERSION</version>
            	             <requirements>Android OS</requirements>";
            	             
            	             $string = '';
            	             $string = preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '', $productMeta->BRIEF_DESCRIPTION );
            	             
            	             $xmlBody .= "<short_description>".$string."</short_description>
            	             <price>$list->price</price>
		                     <currency>USD</currency>";
            	                     	            
            	             $string = '';
            	             
            	             	
    

            	             $string = preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '',$productMeta->FULL_DESCRIPTION);   
		                     $xmlBody .= "<long_description>".$string."</long_description>
		                     <type>$type</type>
		                     <update_date> $updateDate </update_date>
		                     <keywords>";
            				
                             
                             $keywords = explode(',', $list->keywords);
                             
                             if(is_array($keywords))	{
                             foreach($keywords as $keyword) 
                                $xmlBody .= "<keyword>".preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '',$keyword)."</keyword>";
                             }    else    {
                             	
                             	$xmlBody .= $xmlBody;
                             }
                             
                             $xmlBody .= "</keywords>";
                             
                             error_reporting(error_reporting() & ~E_STRICT);
                      
                             
                             $xmlBody .=  "<images> 
                             			       <thumb>".$serverPathImage.$list->thumbnail.$imagethumbPostFix."</thumb>";

                             if(is_object($productImagesList))	{
                             		foreach($productImagesList as $image)	
                                       $xmlBody .=  "<image>".$serverPathImage.$image->filename."</image>";
                             }
                                       
                             $xmlBody .=  "</images>"; 
                             
                             $xmlBody .= "<builds>";
                             $xmlBody .= "<build id='$list->build_id'>";
                             $xmlBody .= "<name>".preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '',$list->build_name)."</name>";
			                 $xmlBody .= "<platform>Android-all</platform>";
			              // $xmlBody .= "<installer_type>ota</installer_type>";
			              // $xmlBody .= "<package_name>com.mobisystems.editor.office_registered</package_name>";
                          // $xmlBody .= "<version_name></version_name>";
			              // $xmlBody .= "<version_code></version_code>";
			              

			             
			              // Get the S3 URL of the Relevant build
			          
                             $buildUrl = $productDownloadCls->getBuildFileUrl( $list->id, $list->build_id);
                              //   Zend_Debug::dump($buildUrl);
    			                 

			                 $xmlBody .= "<file>".htmlentities($buildUrl)."</file>";
			                 $xmlBody .= "<productpage>http://www.nexva.com/app/".$this->view->slug($list->name) . "." . $list->id."</productpage>";
			                 
                             $xmlBody .= "<languages>en</languages>";  
		                     $xmlBody .= "</build>";
                             $xmlBody .= "</builds>";
                       

		$abc++;
            $xmlBody .= "</product>";
		
    	}
    	
    	$xmlBody .=	"</products>";

        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=utf-8');
        
        $productsAllResultsSet  = $products->getCompatibleAndroidProducts('result');
        $productsAllResultsSetCount = count($productsAllResultsSet);
        $productsCount = count($list);
        
        if((($limit * ($offset-1)) - $productsCount) <= $productsAllResultsSetCount) 
            echo $xmlBody;
        else 
          echo "<?xml version=\"1.0\"?><products></products>";
    	

    }
    
    private function _filterInputCharacters($string)
    {
    	
    	return preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '', $string);
    }
    
    
    private function _htmlentities2unicodeentities ($input) {
  	
        $htmlEntities = array_values (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
        $entitiesDecoded = array_keys   (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
        $num = count ($entitiesDecoded);
        for ($u = 0; $u < $num; $u++) {
            $utf8Entities[$u] = '&#'.ord($entitiesDecoded[$u]).';';
        }
        
        return str_replace ($htmlEntities, $utf8Entities, $input);
  
    } 

    private function _xmlentities($string, $quote_style=ENT_QUOTES)
    {
       static $trans;
       if (!isset($trans)) {
           $trans = get_html_translation_table(HTML_ENTITIES, $quote_style);
           foreach ($trans as $key => $value)
           $trans[$key] = '&#'.ord($key).';';
          // dont translate the '&' in case it is part of &xxx;
          $trans[chr(38)] = '&';
       }
       // after the initial translation, _do_ map standalone '&' into '&#38;'
       return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,3};)/","&#38;" , strtr($string, $trans));
       
       
       ///this is just for referance 
          // $string = strtr($productMeta->FULL_DESCRIPTION, "����������������������������������������������������", "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn'");

                            // Remove all remaining other unknown characters
                            // $string = preg_replace('/[^a-zA-Z0-9\-]/', ' ', $string);
                            // $string = preg_replace('/^[\-]+/', '', $string);
                            // $string = preg_replace('/[\-]+$/', '', $string);
                            // $string = preg_replace('/[\-]{2,}/', ' ', $string);
       
    }
    
    

    /**
     * 
     * Returns valid user id based on valid token (session id)
     * 
     * @param $token session id
     * returns userId or error code
     */
    private function __validateToken($token) {

        if (!$token) {

            $this->__echoError("9000", "Request not authenticated", self::BAD_REQUEST_CODE);
        }
        Zend_Session::setId($token);
        $sessionUser = new Zend_Session_Namespace('partner_user');
        if (!($sessionUser->id)) {

            $this->__echoError("9000", "Request not authenticated", self::BAD_REQUEST_CODE);
        }

        return $sessionUser->id;
    }

    /**
     * 
     * print error code data not found
     * 
     */
    private function __dataNotFound() {

        $this->__echoError("3000", "Data Not found", self::BAD_REQUEST_CODE);
        exit;
    }

    /**
     * 
     * print the apps data in json format
     * @param $apps array 
     */
    private function __printArrayOfDataJson($apps) 
    {
        $apps = str_replace('\/', '/', json_encode($apps));
        $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
        echo $apps;
        exit;
    }
    
    private function __echoError($errNo, $errMsg, $httpResponseCode)
    {
        $msg = json_encode(array("message" =>$errMsg,"error_code" => $errNo));
        
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->loggerInstance->log('Response ::' . $msg, Zend_Log::INFO);
        
        echo $msg;
        exit;
    }

    
     /**
     * 
     * Returns the Promoted Applications based on Device and Chap
     * 
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * returns JSON encoded apps array
     */
    public function promotedAppsAction() {

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        //Get the parameters               
        $category = trim($this->_getParam('category', null));

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) 
        {            
            $this->__echoError("2000", "Device not found", self::BAD_REQUEST_CODE);
        } 
        else 
        { //Get Featured Apps based on Chap and the Device
            
            $ApiModel = new Nexva_Api_NexApi();

            $apiCall = true;

            //get promoted apps (these are the banners in the web site)
            $promotedApps = $ApiModel->banneredAppsAction($chapId, 6, $deviceId, $apiCall, $category);

            //change the thumbnail path
            if (count($promotedApps) > 0) 
            {
                $apps = str_replace('\/', '/', json_encode($promotedApps));
                $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                echo $apps;                
            } 
            else 
            {
               $this->__echoError("3000", "Data Not found", self::BAD_REQUEST_CODE);                
            }
        }
    }
    
    /**
     * 
     * Returns the Random Free Applications based on Device and Chap
     * 
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * returns JSON encoded apps array
     */
    public function topFreeAppsAction() {

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        //Get the parameters               
        $category = trim($this->_getParam('category', null));

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) 
        {            
            $this->__echoError("2000", "Device not found", self::BAD_REQUEST_CODE);
        } 
        else 
        { //Get Featured Apps based on Chap and the Device
            
            $ApiModel = new Nexva_Api_NexApi();

            $apiCall = true;

            //Featured apps for banners
            $freeApps = $ApiModel->freeAppsAction($chapId, 15, $deviceId, $apiCall, $category);

            //change the thumbnail path
            if (count($freeApps) > 0) 
            {
                $apps = str_replace('\/', '/', json_encode($freeApps));
                $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                echo $apps;                
            } 
            else 
            {
               $this->__echoError("3000", "Data Not found", self::BAD_REQUEST_CODE);                
            }
        }
    }
    
    
    /**
     * 
     * Returns the Random Premium Applications based on Device and Chap
     * 
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * returns JSON encoded apps array
     */
    public function topPremiumAppsAction() {

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        //Get the parameters               
        $category = trim($this->_getParam('category', null));

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) 
        {            
            $this->__echoError("2000", "Device not found", self::BAD_REQUEST_CODE);
        } 
        else 
        { //Get Featured Apps based on Chap and the Device
            
            $ApiModel = new Nexva_Api_NexApi();

            $apiCall = true;

            //Featured apps for banners
            $premiumApps = $ApiModel->paidAppsAction($chapId, 15, $deviceId, $apiCall, $category);

            //change the thumbnail path
            if (count($premiumApps) > 0) 
            {
                $apps = str_replace('\/', '/', json_encode($premiumApps));
                $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                echo $apps;                
            } 
            else 
            {
               $this->__echoError("3000", "Data Not found", self::BAD_REQUEST_CODE);                
            }
        }
    }
    
    /**
     * 
     * Returns the Latest Applications based on Device and Chap
     * 
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * returns JSON encoded apps array
     */
    public function newestAppsAction() {

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        //Get the parameters               
        $category = trim($this->_getParam('category', null));

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) 
        {            
            $this->__echoError("2000", "Device not found", self::BAD_REQUEST_CODE);
        } 
        else 
        { //Get Featured Apps based on Chap and the Device
            
            $ApiModel = new Nexva_Api_NexApi();

            $apiCall = true;

            //Featured apps for banners
            $newestApps = $ApiModel->newestAppsAction($chapId, 15, $deviceId, $apiCall, $category);

            //change the thumbnail path
            if (count($newestApps) > 0) 
            {
                $apps = str_replace('\/', '/', json_encode($newestApps));
                $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                echo $apps;                
            } 
            else 
            {
               $this->__echoError("3000", "Data Not found", self::BAD_REQUEST_CODE);                
            }
        }
    }
    
    /**
     * 
     * Returns the text pages of an CHAP
     * 
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * returns JSON encoded text pages array
     */
    public function textPagesAction() {

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

       
        $ApiModel = new Nexva_Api_NexApi();

        //Featured apps for banners
        $pages = $ApiModel->getTextPagesAction($chapId);

        //change the thumbnail path
        if (count($pages) > 0) 
        {
            $pages = str_replace('\/', '/', json_encode($pages));
            $this->loggerInstance->log('Response ::' . $pages, Zend_Log::INFO);
            echo $pages;
        } 
        else 
        {
            $this->__echoError("3000", "Data Not found", self::BAD_REQUEST_CODE);
        }
       
    }
    
    
    public function validatePasswordAction()
    {
    	$headersParams = $this->validateHeaderParams();
    	$chapId = $headersParams['chapId'];
    
    	$password = $this->_request->password;
    	$username = $this->_request->username;
    
    	if ($username == '')
    	{
    		$this->__echoError("8005", "Emptry username", self::BAD_REQUEST_CODE);
    		//$errors[] = 'Emptry user name';
    	}
    	if ($password == '')
    	{
    		$this->__echoError("8006", "Empty password", self::BAD_REQUEST_CODE);
    		//$errors[] = 'Empty password';
    	}

    	$userObj = new Api_Model_Users();
    	$tmpUser = $userObj->getUserByEmail($username);
    
    	if(is_null($tmpUser))
    	{
    		$this->__echoError("8007", "Invalid username", self::BAD_REQUEST_CODE);

    	}
    	if ($tmpUser->password != md5($password))
    	{
    		$this->__echoError("8012", "Invalid password", self::BAD_REQUEST_CODE);
    			
    	} else {
    	    
    	    $response = array(
    	    		'status' => 1,
    	    		'mobile_no' => $tmpUser->mobile_no);
    	     
    	    $this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
    	    echo json_encode($response);
    	    
    	    
    	}
   
    	
    }
    
    public function checkAppDownloadedAction()
    {
        
    	$headersParams = $this->validateHeaderParams();
    	$chapId = $headersParams['chapId'];
    
    	$appId = $this->_request->appId;
    	$buildId = $this->_request->buildId;
    	$userId = $this->_request->userId;

    	$modelDownloadStats = new Api_Model_StatisticsDownloads();
   
    	$downloadeds = $modelDownloadStats->checkDownloadedAppByuser($userId, $appId, $buildId);
    	$downloadCount  = $downloadeds->count();

    		
    	if($downloadCount > 0) {
    		$response = array('status' => 1);
    		echo json_encode($response);

    	} else {
    		$response = array('status' => 0);
    		 echo json_encode($response);  
    	}

    }

    /**
     * Pay and download a premium app
     * In this function it will call the Interop Payment gateway to make a mobile payment and if succeeded,
     * Returns the download url of the app. This is a direct link to the app file on S3 server wrapped by the
     * relevent parameters (AWSAccessKeyId,Expires,Signature).
     *
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * @param $appId App ID (GET)
     * @param $buildId Build ID (GET)
     * @param $mobileNo Mobile No (GET)
     * @return JSON encoded $downloadLink
     */
    public function paymyAction() {


        $sessionId = $this->_getParam('token', 0);
        $userId = $this->__validateToken($sessionId);

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];

        $appId = $this->_getParam('appId');
        $buildId = $this->_getParam('build_Id');
        $mobileNo = $this->_getParam('mdn');

        //Testing Mobile No
        //$mobileNo = '5155328687';
        //$userAgent = "Mozilla/5.0 (Linux; U; Android 1.5; fr-fr; Galaxy Build/CUPCAKE) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2";
        //$chapId = 8056;
        //Check if App Id has been provided
        if ($appId === null || empty($appId)) {


            $this->__echoError("1001", "App Id not found", self::BAD_REQUEST_CODE);

        }
        //Check if Chap Id has been provided
        if ($buildId === null || empty($buildId)) {


            $this->__echoError("1002", "Build Id not found", self::BAD_REQUEST_CODE);

        }
        //Check if Chap Id has been provided
        if ($mobileNo === null || empty($mobileNo)) {


            $this->__echoError("5000", "Mobile Number not found", self::BAD_REQUEST_CODE);
        }


        //Check if the app belongs to the CHAP
        $chapProductModel = new Api_Model_ChapProducts();
        $appCount = $chapProductModel->getProductCountByChap($chapId, $appId);

        if ($appCount == 0) {


            $this->__echoError("1003", "App does not belong to this partner", self::BAD_REQUEST_CODE);

        }

        /******************************************************************* */

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) {

            $this->__echoError("2000", "Device not found", self::BAD_REQUEST_CODE);
        } else {

            //get the app details
            $productModel = new Api_Model_Products();
            $appDetails = $productModel->getProductDetailsbyId($appId);

            //Check if app details available
            if (is_null($appDetails)) {
                $this->__echoError("3001", "This app has been removed or does not exist", self::BAD_REQUEST_CODE);
            }

            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

            //check whether the app has been downloaded 2 times or less, if it downloaded 2 times it wont allowed download anymore
            $userDownloadModel = new Api_Model_UserDownloads();
            $capable = $userDownloadModel->checkDownloadCapability($appId,$chapId,$userId,$buildId);

            if($capable)
            {
                /************* Add Statistics - Download *************************/
                $source = "API";
                $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');


                $model_ProductBuild = new Model_ProductBuild();
                $buildInfo = $model_ProductBuild->getBuildDetails($buildId);


                $modelQueue = new Partner_Model_Queue();
                $modelQueue->removeDownlaodedItem($userId, $chapId);

                $modelDownloadStats = new Api_Model_StatisticsDownloads();
                $modelDownloadStats->addDownloadStat($appId, $chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $deviceId, $sessionId);

                /******************End Statistics ******************************* */

                $downloadLink = array();
                $downloadLink['download_app'] = $buildUrl;

                $this->getResponse()
                    ->setHeader('Content-type', 'application/json');

                //Here str_replace has been used to prevent of adding '/' when ecnoded by JSON
                //thre is a solution but ,JSON_UNESCAPED_UNICODE is only works in PHP 5.4+, http://php.net/manual/en/function.json-encode.php
                $encodedDownloadLink = str_replace('\/', '/', json_encode($downloadLink));
                $this->loggerInstance->log('Response :: Pay app response not logged' ,Zend_Log::INFO);
                echo $encodedDownloadLink;

                //since the download link has already sent adding a download record for UserDownload table
                $userDownloadModel->addDownloadRecord($appId,$chapId,$userId,$buildId);
            }
            else
            {
                $this->__echoError("1004", "Maximum Allowed Download Limit Reached", self::BAD_REQUEST_CODE);
            }
        }
    }
    
    public function payApppAction() {
    


    	$appId = $this->_getParam('appId');
    	$buildId = $this->_getParam('build_Id');
    	$mobileNo = $this->_getParam('mdn');
    

    
    		$price = 0;
    		$appName = "";
    

    
    		/*************************** Begin Payment Process *******************************************/
    
    		//Get payment gateway Id of the CHAP
    
    		$pgUsersModel = new Api_Model_PaymentGatewayUsers();
    		$pgDetails = $pgUsersModel->getGatewayDetailsByChap(15267);

    		 
    		$pgType = $pgDetails->gateway_id;
    		$paymentGatewayId = $pgDetails->payment_gateway_id;
    
    		//Call Nexva_MobileBilling_Factory and create relevant instance
    		$pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
    		

    		//Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
    		$pgClass->addMobilePayment(15267, 38652, 16294, '13369570515', '1', $paymentGatewayId);
    		
    		

    
    		//Do the transaction and get the build url
    		$buildUrl = $pgClass->doPayment(15267, $buildId, $appId, '13369570515', $appName, '1.00');
    		
    		Zend_Debug::dump($buildUrl);
    		die();
    		
    
    		//Check if payment was made success, Provide the download link
    		if(!empty($buildUrl) && !is_null($buildUrl))
    		{
    			//************* Add Royalties *************************
    			$userAccount = new Model_UserAccount();
    			$userAccount->saveRoyalitiesForApi($appId, $price, $paymentMethod='CHAP', $chapId, $userId);
    
    			//************* Add Statistics - Download *************************
    			$source = "API";
    			$ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');
    
    			$model_ProductBuild = new Model_ProductBuild();
    			$buildInfo = $model_ProductBuild->getBuildDetails($buildId);
    
    			//                $modelQueue = new Partner_Model_Queue();
    			//                $modelQueue->removeDownlaodedItem($userId, $chapId);
    			//
    			$modelDownloadStats = new Api_Model_StatisticsDownloads();
    			$modelDownloadStats->addDownloadStat($appId, $chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $deviceId, $sessionId);
    
    			/******************End Statistics ******************************* */
    
    			$downloadLink = array();
    			$downloadLink['download_app'] = $buildUrl;
    
    			$this->getResponse()
    			->setHeader('Content-type', 'application/json');
    
    			//Here str_replace has been used to prevent of adding '/' when ecnoded by JSON
    			//thre is a solution but ,JSON_UNESCAPED_UNICODE is only works in PHP 5.4+, http://php.net/manual/en/function.json-encode.php
    			$encodedDownloadLink = str_replace('\/', '/', json_encode($downloadLink));
    			$this->loggerInstance->log('Response :: Pay app response not logged' ,Zend_Log::INFO);
    			echo $encodedDownloadLink;
    		}
    		else
    		{
    			$this->__echoError("4000", "Payment was unsuccessfull", self::BAD_REQUEST_CODE);
    		}
    	
    }
    
    
    
    
    public function fogotpasswordrequestAction()
    {
    	include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );
    	
    	$headersParams = $this->validateHeaderParams();
    	$chapId = $headersParams['chapId'];
    	
    	$email = $this->_request->email;
    	
    	// Input field validation.
    	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    		$errors[] = 'Invalid email';
    		$this->__echoError("2001", "Invalid email.", self::BAD_REQUEST_CODE);
    	}
    
    	$userModel = new Api_Model_Users();
    	$tmpUser = $userModel->getUserByEmail($email);
    	
    	if(is_null($tmpUser))
    	{
    		$this->__echoError("8001", "User Id Not Found", self::BAD_REQUEST_CODE);
    	}
    
    	$activationCode = substr(md5(uniqid(rand(), true)), 5,8);
    	$status = 1;
    
    	$mobileNumber = $tmpUser->mobile_no;
    
    	//Check if mobile number is given
    	if ($mobileNumber === null || empty($mobileNumber))
    	{
    		$this->__echoError("8011", "Mobile Number Not Registered", self::BAD_REQUEST_CODE);
    	}
    
    	//update the status
    	if($userModel->updateActivationCode($tmpUser->id, $activationCode, $status))
    	{
    	    // send sms start
    	    $pgUsersModel = new Api_Model_PaymentGatewayUsers();
    	    $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
    	    
    	    $pgType = $pgDetails->gateway_id;
    	    
    	    //Call Nexva_MobileBilling_Factory and create relevant instance
    	    $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
    	    
    	    $message = 'Please use this verification code '.$activationCode.' to complete password reset.';
    	    
    	    $pgClass->sendSms($mobileNumber, $message, $chapId);

    		$response = array(
    				'user' => $tmpUser->id,
    		        'mobile_no' => $mobileNumber,
    				'activation_code' => $activationCode,
    				'success_code' => '1111'
    		);
    
    		$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
    		echo json_encode($response);
    	}
    }
}
