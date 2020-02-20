<?php

class Partnermobile_AppController extends Nexva_Controller_Action_Partnermobile_MasterController {
    
    public function init() 
    {
        parent::init(); 
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
            
    }
   
    //Top newest applications
    public function newestAction()
    {
        $isLowEndDevice = $this->_isLowEndDevice;

        $chapProducts = new Partner_Model_ChapProducts();
        
        $newestApps = $chapProducts->getNewestProductIdsByDevice($this->_chapId, $this->_deviceId, 15, $this->_grade);
        
        //get Product details
        if (count($newestApps) > 0) 
        {   $this->view->showResults = TRUE;
            $newestApps = $this->getProductDetails($newestApps, $this->_deviceId);
        }
        
        $pagination = Zend_Paginator::factory($newestApps);
        $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
        $pagination->setItemCountPerPage(10);
                
        $this->view->newestApps = $pagination;

        //currency details by chap
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
        $this->view->currencyDetails = $currencyDetails;
        
        if($isLowEndDevice):
            $this->_helper->viewRenderer('newest-low-end');
        endif;
        
    }
    
    //Top downloaded applications
    public function topDownloadsAction()
    {
        $isLowEndDevice = $this->_isLowEndDevice; 
        $chapProducts = new Partner_Model_ChapProducts();   

        $sessionDevice = new Zend_Session_Namespace("devices_partner_web");
        if($sessionDevice->platfrom != 'iOS') 
            $topDownloadsApps = $chapProducts->getTopProductIdsByDevice($this->_chapId, $this->_deviceId, 15, $this->_grade);
        else 
            $topDownloadsApps = $chapProducts->getTopProductIdsByApple($this->_chapId, $this->_deviceId, 15, $this->_grade);

        //get Product details
        if (count($topDownloadsApps) > 0) 
        {   $this->view->showResults = TRUE;
            $topDownloadsApps = $this->getProductDetails($topDownloadsApps, $this->_deviceId);
        }

        $pagination = Zend_Paginator::factory($topDownloadsApps);
        $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
        $pagination->setItemCountPerPage(10);
        
        $this->view->topDownloadedApps = $pagination;

        //currency details by chap
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
        $this->view->currencyDetails = $currencyDetails;
        
         if($isLowEndDevice):
            $this->_helper->viewRenderer('top-downloads-low-end');
        endif;
    }
    
    //Free applications 
    public function topFreeAction()
    {
        $isLowEndDevice = $this->_isLowEndDevice; 
       
        $chapProducts = new Partner_Model_ChapProducts();
        $freeApps = $chapProducts->getFreeProductIdsByDevice($this->_chapId, $this->_deviceId, 100, $this->_grade);
        
        //get Product details
        if (count($freeApps) > 0) 
        {
    	    $this->view->showResults = TRUE;
            $freeApps = $this->getProductDetails($freeApps, $this->_deviceId);
        }
        
        
        $pagination = Zend_Paginator::factory($freeApps);
        $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
        $pagination->setItemCountPerPage(10);
        
        $this->view->freeApps = $pagination;

        //currency details by chap
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
        $this->view->currencyDetails = $currencyDetails;
        
        if($isLowEndDevice):
            $this->_helper->viewRenderer('top-free-low-end');
        endif;
    }
    
    //Premium applications
    public function topPremiumAction()
    {
        $isLowEndDevice = $this->_isLowEndDevice;
        
        $chapProducts = new Partner_Model_ChapProducts();
        $paidApps = $chapProducts->getPaidProductIdsByDevice($this->_chapId, $this->_deviceId, 15, $this->_grade);
        
        //get Product details
        if (count($paidApps) > 0) 
        {
    	    $this->view->showResults = TRUE;
            $paidApps = $this->getProductDetails($paidApps, $this->_deviceId);
        }
        
        $pagination = Zend_Paginator::factory($paidApps);
        $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
        $pagination->setItemCountPerPage(10);
        
        
        $this->view->paidApps = $pagination;

        //currency details by chap
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
        $this->view->currencyDetails = $currencyDetails;
        if($isLowEndDevice):
            $this->_helper->viewRenderer('top-premium-low-end');
        endif;
        
    }

    //Returns details of an application
    public function detailAction() 
    {


        $isLowEndDevice = $this->_isLowEndDevice; 
        
        $appId = $this->getRequest()->getParam('id', 1);
        $this->view->testing = $this->getRequest()->getParam('t',0);
        
        $this->view->buildUrlAvailable = false;

        if($this->_chapId == 81449){
            if($this->getRequest()->getParam('buildUrl')){

                $transactionId = $this->getRequest()->getParam('transactionId');

                $this->view->buildUrlAvailable = true;
                $this->view->transactionId = ($transactionId) ? $transactionId : null ;

                $this->view->buildUrl =  $this->getRequest()->getParam('buildUrl', 1);
                $msgSuccess = "Payment was suceessful";
                $this->view->transactionId = $msgSuccess;
                //$message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
                //$this->view->errorMsgApp = $message;

                //$msgSuccess = "Payment was suceessful";
                //$message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
                //$this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
            }
        }
        else {
            //This condition used for redirected build url from payAction and payByRedirectionAction. Used to download the build by JS redirection in details view
            if($this->_request->getPost()){
                $postVarArr = $this->_request->getPost();
                if($postVarArr['buildUrl']){
                    $this->view->buildUrlAvailable = ($postVarArr['buildUrl']) ? true : false;
                    $this->view->transactionId = ($postVarArr['transactionId']) ? $postVarArr['transactionId'] : null ;
                    $this->view->buildUrl =  $postVarArr['buildUrl'];

                    if($postVarArr['transactionId']){
                        $msgSuccess = "Transaction ID";
                        $message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
                        $this->view->transactionId = $message.' - '.$postVarArr['transactionId'];
                    }
                    else{
                        $msgSuccess = "Payment was suceessful";
                        $message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
                        $this->view->errorMsgApp = $message;
                    }
                }
            }
        }
        /*if($this->_chapId == 23045 && $_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            $postVarArr = $this->_request->getPost();
            if($postVarArr['build_url']){
       
                $this->view->buildUrlAvailable = ($postVarArr['build_url']) ? true : false;
                $this->view->transactionId = ($postVarArr['trans_id']) ? $postVarArr['trans_id'] : null ;
                $this->view->buildUrl =  $postVarArr['build_url'];
                
                $msgSuccess = "Payment was suceessful";
                $message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
                $this->view->errorMsgApp = $message;
            }
        }
        
        if($this->getRequest()->getParam('buildUrl')){
            
            $transactionId = $this->getRequest()->getParam('transactionId');
            
            $this->view->buildUrlAvailable = true;
            $this->view->transactionId = ($transactionId) ? $transactionId : null ;
            
            $this->view->buildUrl =  $this->getRequest()->getParam('buildUrl', 1);
            $msgSuccess = "Payment was suceessful";
            $message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
            $this->view->errorMsgApp = $message;
            
            //$msgSuccess = "Payment was suceessful";
            //$message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
            //$this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
        }*/

    	$nexApi = new Nexva_Api_NexApi();
        $appDetails = $nexApi->detailsAppAction($appId, $this->_deviceId, $this->_chapId, $this->_chapLanguageId);
 

        /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            echo $this->_deviceId; die();
        }*/

        /* Add translation. 
         * Get the translations for product name,description,brief_description and overite if the translations are available.
         * If not let it be with english language.
         */
        $productLanguageMeta = new Model_ProductLanguageMeta();
        
        $productTranslation = $productLanguageMeta->loadTranslation($appId, $this->_chapLanguageId);

        //this is temporary for MTN Iran cell
        /* @todo: needs to be removed once we bags all translations
         */
        if(23045 != $this->_chapId){
            if($productTranslation->PRODUCT_NAME):
                $appDetails["name"] = $productTranslation->PRODUCT_NAME;
            endif;
        }

        if($productTranslation->PRODUCT_DESCRIPTION):
            $appDetails["desc"] = $productTranslation->PRODUCT_DESCRIPTION;
        endif;
        
         //Check the premium app already downloaded by the same user
        /*$modelDownloadStats = new Api_Model_StatisticsDownloads();
        $downloadedCount = $modelDownloadStats->checkThisDownloadExist($appId,$this->_chapId,$this->_userId,$appDetails['build_id']);
        $appDetails["exist_download_count"] = $downloadedCount['exist_count'];*/
        
        $this->view->product = $appDetails;
        
        $themeMeta  = new Model_ThemeMeta();
        $themeMeta->setEntityId($this->_chapId);
        $chapInfo  = $themeMeta->getAll();
        $this->view->chapInfo = $chapInfo;
//        $chap = new Zend_Session_Namespace('partner');
//        $this->view->chap_id = $chap->id;
        
        //Set the messages if exists
        $this->view->errorMsgApp = $this->_helper->flashMessenger->setNamespace('errorMsgApp')->getMessages();

    

        
        /************* Add Statistics - View ************************ */
        $source = "MOBILE";
        $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');

        $modelViewStats = new Api_Model_StatisticsProducts();
        /*
         * same ip, same product, same device, same chap stats are not allowed to insert
         */
        $rowView = $modelViewStats->checkThisStatExist($appId, $this->_chapId, $source, $ipAddress, $this->_deviceId);

        //If the record not exist only stat will be inserted
        if($rowView['exist_count'] == 0){
            $modelViewStats->addViewStat($appId, $this->_chapId, $source, $ipAddress, $this->_deviceId, $this->_userId);
        }
        

    
        
        /********************End Statistics ***************************** */
        
        //currency details by chap
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
        $this->view->currencyDetails = $currencyDetails;
        
        //Check if the product is approved by AVG
        $productBuildModel = new Model_ProductBuild();
        $avgApproved = $productBuildModel->getAvgApproved($appId);
        $this->view->avgApproved = $avgApproved;
        
        //Get the device OS
        $deviceModel = new Model_Device();
        $devicePlatform = $deviceModel->getDevicePlatformById($this->_deviceId);
        $this->view->devicePlatform = $devicePlatform;
        
        //Get the Appstore app details
        $appstoreAppId = $chapInfo->APPSTORE_APP_ID;
        $appStoreAppDetails = $nexApi->detailsAppAction($appstoreAppId);
        
        //Zend_Debug::dump($appStoreAppDetails['supported_platforms']->build_id); die();
        $this->view->appStoreAppDetails = $appStoreAppDetails;
        
        //Get default payment gateway
        $paymentGatewayUserModel = new Model_PaymentGatewayUser();
        $defaultGateways = $paymentGatewayUserModel->getPaymentGatewayByChapId($this->_chapId);
        $this->view->defaultGateways = $defaultGateways;

        /*if($this->_chapId == '80184'){
            echo count($defaultGateways);
            foreach($defaultGateways as $defaultGateway){
                Zend_Debug::dump($defaultGateway);
            }
            die();
        }*/
        
        


        $modelProductBuild = new Partnermobile_Model_ProductBuilds();
        $buildType = $modelProductBuild->getFileTypeByBuildId($appDetails['build_id']);

        $this->view->buildType = $buildType;

        //------------------get Download Counts--------------------------
        $statisticsDownloadsModel = new Api_Model_StatisticsDownloads();
        $downloads = $statisticsDownloadsModel->getDownloadCountByAppChap($appId, $this->_chapId);
        $this->view->downloads = $downloads;

        //------------------get View Counts------------------------------
        $statisticsProductsModel = new Api_Model_StatisticsProducts();
        $views = $statisticsProductsModel->getViewCountByAppChap($appId, $this->_chapId);
        $this->view->views = $views;
        
        //Getting the token for google wallet. It's only using for qelasy
        if($this->_chapId == 81604){
            
            //Get payment gateway Id of the CHAP            
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($this->_chapId);
            $pgType = $pgDetails->gateway_id; 
            $pgClassName = $pgType;
        
            $pgClass = Nexva_PaymentGateway_Factory::factory($pgType,$pgClassName);
           // $this->view->jwtToken = $pgClass->generateJwt($appId,'');
        }

        //if the device is a low-end one, change the template and the view
        if($isLowEndDevice):
            $this->_helper->viewRenderer('detail-low-end');
        endif;
        
        

        
        // check alredy downlaoded or nto as per uganda requested 
        $modelDownloadStats = new Api_Model_StatisticsDownloads();
        
        $auth = Zend_Auth::getInstance();
        $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;

        
        if($userId) {
        //same user, same product, same device, same chap stats are not allowed to insert
        $alreadyDownloaded = $modelDownloadStats->checkThisDownloadExist($appId, $this->_chapId, $userId, $appDetails['build_id']);
         


        
	        if($alreadyDownloaded['exist_count'] > 0){
	            
	            
	            
	        	$this->view->showDownlaod = true;
	        
	        }
	        

        }
        


        
        
    }
    
    public function bbAction()
    {
        
        



        $isLowEndDevice = $this->_isLowEndDevice;
        
        $appId = $this->getRequest()->getParam('id', 1);
        
        $this->view->buildUrlAvailable = false;
        
        if($this->_chapId == 81449){
            if($this->getRequest()->getParam('buildUrl')){
        
                $transactionId = $this->getRequest()->getParam('transactionId');
        
                $this->view->buildUrlAvailable = true;
                $this->view->transactionId = ($transactionId) ? $transactionId : null ;
        
                $this->view->buildUrl =  $this->getRequest()->getParam('buildUrl', 1);
                $msgSuccess = "Payment was suceessful";
                $this->view->transactionId = $msgSuccess;
                //$message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
                //$this->view->errorMsgApp = $message;
        
                //$msgSuccess = "Payment was suceessful";
                //$message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
                //$this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
            }
        }
        else {
            //This condition used for redirected build url from payAction and payByRedirectionAction. Used to download the build by JS redirection in details view
            if($this->_request->getPost()){
                $postVarArr = $this->_request->getPost();
                if($postVarArr['buildUrl']){
                    $this->view->buildUrlAvailable = ($postVarArr['buildUrl']) ? true : false;
                    $this->view->transactionId = ($postVarArr['transactionId']) ? $postVarArr['transactionId'] : null ;
                    $this->view->buildUrl =  $postVarArr['buildUrl'];
        
                    if($postVarArr['transactionId']){
                        $msgSuccess = "Transaction ID";
                        $message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
                        $this->view->transactionId = $message.' - '.$postVarArr['transactionId'];
                    }
                    else{
                        $msgSuccess = "Payment was suceessful";
                        $message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
                        $this->view->errorMsgApp = $message;
                    }
                }
            }
        }
        /*if($this->_chapId == 23045 && $_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
         $postVarArr = $this->_request->getPost();
         if($postVarArr['build_url']){
          
         $this->view->buildUrlAvailable = ($postVarArr['build_url']) ? true : false;
         $this->view->transactionId = ($postVarArr['trans_id']) ? $postVarArr['trans_id'] : null ;
         $this->view->buildUrl =  $postVarArr['build_url'];
        
         $msgSuccess = "Payment was suceessful";
         $message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
         $this->view->errorMsgApp = $message;
         }
         }
        
         if($this->getRequest()->getParam('buildUrl')){
        
         $transactionId = $this->getRequest()->getParam('transactionId');
        
         $this->view->buildUrlAvailable = true;
         $this->view->transactionId = ($transactionId) ? $transactionId : null ;
        
         $this->view->buildUrl =  $this->getRequest()->getParam('buildUrl', 1);
         $msgSuccess = "Payment was suceessful";
         $message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
         $this->view->errorMsgApp = $message;
        
         //$msgSuccess = "Payment was suceessful";
         //$message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
         //$this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
         }*/
        
        $nexApi = new Nexva_Api_NexApi();
        $appDetails = $nexApi->detailsAppAction($appId, $this->_deviceId, $this->_chapId, $this->_chapLanguageId);
        
        
        /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
         echo $this->_deviceId; die();
        }*/
        
        /* Add translation.
         * Get the translations for product name,description,brief_description and overite if the translations are available.
         * If not let it be with english language.
         */
         $productLanguageMeta = new Model_ProductLanguageMeta();
        
         $productTranslation = $productLanguageMeta->loadTranslation($appId, $this->_chapLanguageId);
        
         //this is temporary for MTN Iran cell
         /* @todo: needs to be removed once we bags all translations
         */
         if(23045 != $this->_chapId){
         if($productTranslation->PRODUCT_NAME):
         $appDetails["name"] = $productTranslation->PRODUCT_NAME;
         endif;
         }
        
         if($productTranslation->PRODUCT_DESCRIPTION):
             $appDetails["desc"] = $productTranslation->PRODUCT_DESCRIPTION;
         endif;
        
         //Check the premium app already downloaded by the same user
         /*$modelDownloadStats = new Api_Model_StatisticsDownloads();
         $downloadedCount = $modelDownloadStats->checkThisDownloadExist($appId,$this->_chapId,$this->_userId,$appDetails['build_id']);
         $appDetails["exist_download_count"] = $downloadedCount['exist_count'];*/
        
         $this->view->product = $appDetails;
        
         $themeMeta  = new Model_ThemeMeta();
         $themeMeta->setEntityId($this->_chapId);
         $chapInfo  = $themeMeta->getAll();
         $this->view->chapInfo = $chapInfo;
         //        $chap = new Zend_Session_Namespace('partner');
         //        $this->view->chap_id = $chap->id;
        
         //Set the messages if exists
         $this->view->errorMsgApp = $this->_helper->flashMessenger->setNamespace('errorMsgApp')->getMessages();
        
        
        
        
         /************* Add Statistics - View ************************ */
         $source = "MOBILE";
         $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');
        
         $modelViewStats = new Api_Model_StatisticsProducts();
        /*
                * same ip, same product, same device, same chap stats are not allowed to insert
                */
                $rowView = $modelViewStats->checkThisStatExist($appId, $this->_chapId, $source, $ipAddress, $this->_deviceId);
        
                //If the record not exist only stat will be inserted
                if($rowView['exist_count'] == 0){
                $modelViewStats->addViewStat($appId, $this->_chapId, $source, $ipAddress, $this->_deviceId, $this->_userId);
        }
        
        
        
        
        /********************End Statistics ***************************** */
        
                //currency details by chap
                $currencyUserModel = new Api_Model_CurrencyUsers();
                    $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
                    $this->view->currencyDetails = $currencyDetails;
        
         //Check if the product is approved by AVG
          $productBuildModel = new Model_ProductBuild();
         $avgApproved = $productBuildModel->getAvgApproved($appId);
         $this->view->avgApproved = $avgApproved;
        
         //Get the device OS
         $deviceModel = new Model_Device();
         $devicePlatform = $deviceModel->getDevicePlatformById($this->_deviceId);
         $this->view->devicePlatform = $devicePlatform;
        
         //Get the Appstore app details
         $appstoreAppId = $chapInfo->APPSTORE_APP_ID;
         $appStoreAppDetails = $nexApi->detailsAppAction($appstoreAppId);
        
         //Zend_Debug::dump($appStoreAppDetails['supported_platforms']->build_id); die();
         $this->view->appStoreAppDetails = $appStoreAppDetails;
        
         //Get default payment gateway
         $paymentGatewayUserModel = new Model_PaymentGatewayUser();
         $defaultGateways = $paymentGatewayUserModel->getPaymentGatewayByChapId($this->_chapId);
         $this->view->defaultGateways = $defaultGateways;
        
         /*if($this->_chapId == '80184'){
         echo count($defaultGateways);
         foreach($defaultGateways as $defaultGateway){
         Zend_Debug::dump($defaultGateway);
        }
        die();
        }*/
        
        
        
        
        $modelProductBuild = new Partnermobile_Model_ProductBuilds();
        $buildType = $modelProductBuild->getFileTypeByBuildId($appDetails['build_id']);
        
        $this->view->buildType = $buildType;
        
        //------------------get Download Counts--------------------------
        $statisticsDownloadsModel = new Api_Model_StatisticsDownloads();
        $downloads = $statisticsDownloadsModel->getDownloadCountByAppChap($appId, $this->_chapId);
         $this->view->downloads = $downloads;
        
         //------------------get View Counts------------------------------
         $statisticsProductsModel = new Api_Model_StatisticsProducts();
        $views = $statisticsProductsModel->getViewCountByAppChap($appId, $this->_chapId);
        $this->view->views = $views;
        
        //Getting the token for google wallet. It's only using for qelasy
        if($this->_chapId == 81604){
        
        //Get payment gateway Id of the CHAP
        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($this->_chapId);
        $pgType = $pgDetails->gateway_id;
        $pgClassName = $pgType;
        
        $pgClass = Nexva_PaymentGateway_Factory::factory($pgType,$pgClassName);
        // $this->view->jwtToken = $pgClass->generateJwt($appId,'');
        }
        
        //if the device is a low-end one, change the template and the view
        if($isLowEndDevice):
        $this->_helper->viewRenderer('detail-low-end');
        endif;
        
        
        
        
        // check alredy downlaoded or nto as per uganda requested
        $modelDownloadStats = new Api_Model_StatisticsDownloads();
        
        $auth = Zend_Auth::getInstance();
        $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;
        
        
            if($userId) {
            //same user, same product, same device, same chap stats are not allowed to insert
            $alreadyDownloaded = $modelDownloadStats->checkThisDownloadExist($appId, $this->_chapId, $userId, $appDetails['build_id']);
             
        
        
        
            if($alreadyDownloaded['exist_count'] > 0){
             
             
             
            $this->view->showDownlaod = true;
             
        }
         
        
        }
        
        
        $this->_helper->layout->disableLayout();
       // $this->_helper->viewRenderer->setNoRender(true);
        
        
        
    }

    /**
     * Download an application
     * @param id App ID
     * @param build Build ID
     * returns application build or redirect to URL
     */   

    
    public function downloadAction()
    {      
         
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        
        set_time_limit(0);
        
        $auth = Zend_Auth::getInstance();
        
        $appId = $this->getRequest()->getParam('id', null);
        $buildId = $this->getRequest()->getParam('build', null);
        $testing = $this->getRequest()->getParam('t',0);
        $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;
       

        //endofstart
        if($_SERVER['HTTP_REFERER'] == 'http://'.$_SERVER['SERVER_NAME'].'/' or $_SERVER['HTTP_REFERER'] == 'http://'.$_SERVER['SERVER_NAME'].'/app/detail/id/'.$appId or $_SERVER['HTTP_REFERER'] == 'http://'.$_SERVER['SERVER_NAME'].'/'.$appId 
                or 'http://'.$_SERVER['SERVER_NAME'].'app/buy-app-by-otp/id/'.$appId)   {
            

        if(!Zend_Auth::getInstance()->hasIdentity() && $this->_userCanDownloadFree == FALSE && !$this->_userId)
             $this->_redirect ( '/user/login' );
       
        $modelProductBuilds =  new Partnermobile_Model_ProductBuilds();
        $fileType = $modelProductBuilds->getFileTypeByBuildId($buildId);

        
        /*$modelDownloadStats = new Api_Model_StatisticsDownloads();
        $modelDownloadStats->addDownloadStat($appId, $this->_chapId, '', '', $userId, $buildId, '', '', $this->_deviceId, '');
        die();*/
        
        /* Does not allow to download unapproved apps */
        
        $product =new Model_Product();
        $status=$product->getProductStatusById($appId);
        if(!in_array($status, array("APPROVED")) && $testing !=1 ){
           $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage("This app still not approved. So can't  download it.");
           $this->_redirect('app/detail/id/'.$appId);
        }
        
        /* End */
        
        if(!empty($fileType) && $fileType != null)
        {
            if($fileType == 'files')
            {
                 //Get the S3 URL of the Relevant build
                $productDownloadCls = new Nexva_Api_ProductDownload();
                $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
   

                //if(1==1) //testing
                if($buildUrl != null && !empty($buildUrl))
                {
                    
                    //************* Add codengo *************************
                    $condengo = new Nexva_Util_Http_SendRequestCodengo;
                    $condengo->send($appId);
                    
                    //************* Add Statistics - Download *************************
                    $source = "MOBILE";
                    $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');

                    $modelProductBuild = new Model_ProductBuild();
                    $buildInfo = $modelProductBuild->getBuildDetails($buildId);

                    $sessionId = Zend_Session::getId();

                    $modelDownloadStats = new Api_Model_StatisticsDownloads();
         
                    /*
                     * same user, same product, same device, same chap stats are not allowed to insert
                     */
                    $rowBuild = $modelDownloadStats->checkThisStatExistWithSession($userId, $this->_chapId, $buildId, $this->_deviceId, $sessionId);
                    
         
                    //echo $rowBuild['exist_count']; die();

                    //If the record not exist only stat will be inserted
                    if($rowBuild['exist_count'] == 'no'){
                        $modelDownloadStats->addDownloadStat($appId, $this->_chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $this->_deviceId, $sessionId);
                    }
                    
                    /*****************End Statistics ******************************* */        

                    /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                        echo $buildUrl; die();
                    }*/
                    $this->_redirect($buildUrl);
                }           
                else
                {
                     $this->_redirect('app/not-found');
                }
            }
            else
            {
                $modelBuldFile = new Partnermobile_Model_BuildFiles();
                $url = $modelBuldFile->getFileByBuildId($buildId);

                /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                    echo $url; die();
                }*/

                if($url != null && !empty($url))
                {
                    $this->_redirect($url);
                }
                else 
                {
                    $this->_redirect('app/not-found');
                }
            }
            
        }
        else
        {
            $this->_redirect('app/not-found');
        }
        
       
       
        if($buildUrl != null && !empty($buildUrl))
        {
             //************* Add Statistics - Download *************************
            $source = "MOBILE";
            $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');


            $modelProductBuild = new Model_ProductBuild();
            $buildInfo = $modelProductBuild->getBuildDetails($buildId);

            $sessionId = Zend_Session::getId();
            
            $modelDownloadStats = new Api_Model_StatisticsDownloads();
            
            /*
             * same user, same product, same device, same chap stats are not allowed to insert
             */
            $rowBuild = $modelDownloadStats->checkThisStatExist($userId, $this->_chapId, $buildId, $this->_deviceId);

            //If the record not exist only stat will be inserted
            if($rowBuild['exist_count'] == 0){
                $modelDownloadStats->addDownloadStat($appId, $this->_chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $this->_deviceId, $sessionId);
            }
            
            /*****************End Statistics ******************************* */        
        
       
            
        }           

        
         //endofchecking           
        }
        else
        {
        
        	$this->_redirect('http://'.$_SERVER['SERVER_NAME']);
        
        }
        
        
        
    }
    
     /**
     * Buy an application
     * @param id App ID
     * @param build Build ID
     * returns application build or redirect to URL
     */ 
    public function buyAction()
    {
        // Report all errors

        
        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');
      
        
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(true);
        
        if(!Zend_Auth::getInstance()->hasIdentity())
             $this->_redirect ( '/user/login' );
        
        $auth = Zend_Auth::getInstance();    


  
        $appId = $this->getRequest()->getParam('id', null);
        $buildId = $this->getRequest()->getParam('build', null);
        $paymentGateway = $this->getRequest()->getParam('paymentGateway', null);
        $chapId = $this->_chapId;
		
	    $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;
            
        $productModel = new Partnermobile_Model_Products();
        $productDetails = $productModel->getDetailsById($appId);
        /*Ecaret form submit*/
        

            
            
            

        //Get payment gateway Id of the CHAP            
        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
        

        
       
        $pgType = $pgDetails->gateway_id; 
        $paymentGatewayId = $pgDetails->payment_gateway_id;

  
        $mobileNo = $auth->getIdentity()->mobile_no;        
        $price = $productDetails->price;
        $appName = $productDetails->name;
        $deviceId = $this->_deviceId;
        $sessionId = Zend_Session::getId();

        //check whether the app has been downloaded already, if it downloaded already no payment required for the same user
        /*$userDownloadModel = new Api_Model_UserDownloads();
        $alreadyDownloaded = $userDownloadModel->checkDownloadCapability($appId,$chapId,$userId,$buildId);*/
        
        $modelDownloadStats = new Api_Model_StatisticsDownloads();
        
        //same user, same product, same device, same chap stats are not allowed to insert
        $alreadyDownloaded = $modelDownloadStats->checkThisDownloadExist($appId,$chapId,$userId,$buildId);
        
        /*E-carrot*/
                
                if($alreadyDownloaded['exist_count'] > 0){
                
            //Call the free download function
                $this->downloadAction();
                } else {
            if($chapId==935529){    
                $productName=$productDetails->name;
                $productPrice=$productDetails->price;
                 $this->_redirect('/app/paypal-post-back/appId/'.$appId.'/productName/'.$productName.'/productPrice/'.$productPrice.'/currency/USD/buildId/'.$buildId.'/deviceId/'.$deviceId.'/chapId/'.$chapId.'/userId/'.$userId);
                }}
            
        /*End*/
     
        //if already downloaded it's redirecting to free download function
        
        if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            $alreadyDownloaded['exist_count'] = 0;
         
        }
        
        if($alreadyDownloaded['exist_count'] > 0){
                
            //Call the free download function
                $this->downloadAction();
                
                /*$msgSuccess = "You have successfully downloaded '".$appName."'";
                $message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
                $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);            
                $this->_redirect('/app/detail/id/'.$appId);*/
            
                /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                    echo '###'.$alreadyDownloaded['exist_count']; die();
                }*/
            }
        else{
            if($chapId == '80184'){

                //Call Nexva_MobileBilling_Factory and create relevant instance
                //$pgClass = Nexva_MobileBilling_Factory::createFactory($paymentGateway);
                $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
                
            } elseif ($chapId == '276531') {
                //Call Nexva_MobileBilling_Factory and create relevant instance
                $pgClass = Nexva_MobileBilling_Factory::createFactory($paymentGateway);
                
            } elseif ($chapId == '585474') {
                
                // Orange Guinea
                
                $url = $this->_baseUrl.'/app/request-orange-otp/mobile_no/'.$mobileNo.'/app_id/'.$appId.'/build_id/'.$buildId.'/amount/'.$price.'/chap_id/'.$chapId;
                $this->_redirect($url);
                

                
            } elseif ($chapId == '283006') {
                
               
                	//Call Nexva_MobileBilling_Factory and create relevant instance
                	$session = new Zend_Session_Namespace('payment_reference');
                	$session->payment_reference = null;
                	$session->app_Id = $appId;
                	$session->build_Id = $buildId;
                	$session->price = $price;
                
                	/*end*/
                	//Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
                	$telekomSrbijaObj=new Nexva_MobileBilling_Type_TelekomSrbija();
                	
                	                            
                	$session->payment_reference=$telekomSrbijaObj->addMobilePayment($chapId, $appId, $buildId, $mobileNo, $session->price, $paymentGatewayId, '222');
                	
                	$url=$telekomSrbijaObj->doPayment($chapId, $session->build_Id, $session->app_Id, 45678, $mobileNo, $session->price);
             
                	$this->_redirect($url);
                	
           
            } else {
                //Call Nexva_MobileBilling_Factory and create relevant instance
                

                $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
                
                
            }
            /* save in session*/
            $sessionUser = new Zend_Session_Namespace('payment_reference');
    	    $sessionUser->payment_reference = null;
    	    $sessionUser->chap_Id = $appId;
    	    $sessionUser->build_Id = $buildId;
            /*end*/
            //Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
            $sessionUser->payment_reference=$pgClass->addMobilePayment($chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId);
            

            //If MTN Iran the factory is sending the build url in an array
            if($this->_chapId == 23045)
            {
                //Do the transaction and get the build url                        
                $buildUrlArr = $pgClass->doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price);
                $faultString = $buildUrlArr['message'];
                $transactionId = $buildUrlArr['trans_id'];
                $buildUrl = $buildUrlArr['build_url'];
            }
            else{
                //Do the transaction and get the build url                        
                $buildUrl = $pgClass->doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price);
            }

            //Check if payment was made successfully, Provide the download link
            if(!empty($buildUrl) && !is_null($buildUrl)) 
            {

                //************* Add Royalties *************************
                $userAccount = new Model_UserAccount();
                $userAccount->saveRoyalitiesForApi($appId, $price, $paymentMethod='CHAP', $chapId, $userId);

                //************* Add Statistics - Download *************************
                $source = "MOBILE";
                $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');

                $model_ProductBuild = new Model_ProductBuild();
                $buildInfo = $model_ProductBuild->getBuildDetails($buildId);

                $sessionId = Zend_Session::getId();

                //$modelDownloadStats = new Api_Model_StatisticsDownloads();

                /*
                 * same user, same product, same device, same chap stats are not allowed to insert
                 */
                /*$rowBuild = $modelDownloadStats->checkThisStatExist($userId, $this->_chapId, $buildId, $this->_deviceId);*/

                //If the record not exist only stat will be inserted
                /*if($rowBuild['exist_count'] == 0){
                    $modelDownloadStats->addDownloadStat($appId, $this->_chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $this->_deviceId, $sessionId);
                }*/

                $modelDownloadStats->addDownloadStat($appId, $this->_chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $this->_deviceId, $sessionId);

                /******************End Statistics ******************************* */

                //Redirect to detail action to download the app by JS - Only for MTNI
                if($this->_chapId == 23045){
                    
                    $this->_request->setPost(array(
                        'buildUrl' => $buildUrl,
                        'id' => $appId,
                        'transactionId' => $transactionId
                    ));
                    
                    $this->_forward('detail', 'app', 'partnermobile');
                
                    //$this->_forward('detail', 'app', 'partnermobile', array('buildUrl' => $buildUrl, 'id' => $appId, 'transactionId' => $transactionId));
                }
                else{
                    // Uganda wants to redirect to main app detail page 
                    if($this->_chapId == 80184){
                        
                        $message =  'Thank you! Your payment is successful, Please click on download button to start download.' ;
                        
                        $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
                        $this->_redirect('/app/detail/id/'.$appId);
                        
                    } else {
                        $this->_redirect($buildUrl);
                    }
                    
                    
                }

            }
            else 
            {
                $msgSuccess = "Payment was unsuccessful";

                //If MTN Iran the factory is sending the build url in an array
                if($this->_chapId == 23045)
                {
                    $msgSuccess = $faultString;
                }
            
                $message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;

                $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($msgSuccess);            
                $this->_redirect('/app/detail/id/'.$appId);
            }
        }
    }
    
    public function updatesAction()
    {
       /* $apiModel = new Nexva_Api_NexApi();
        $freeApps = $apiModel->freeAppsAction($this->_chapId, 15, $this->_deviceId);
       
        $this->view->freeApps = $freeApps;*/

        // retrive all the downloaded apps 
        $modelDownloadStats = new Model_StatisticDownload();
        $model_ProductBuild = new Model_ProductBuild();

        $downloadedProducts = $modelDownloadStats->getDownloadedBuildsByUserId($this->_userId, 'MOBILE', NULL, TRUE);

        $updatedProduct = array();

        // if the user doesn't have any apps downloaded exit with error code
        if (count($downloadedProducts) < 1) {

            $msgError = "Products not found";
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

        //print_r($updatedProduct); die();
        if (count($updatedProduct) < 1) {

            $msgError = "Products not found";

        } else 
            {
            $updatesAvailableApps = $this->getProductDetails($updatedProduct, $this->_deviceId);
            $this->view->updatesAvailableApps = $updatesAvailableApps;
        }
		
		$isLowEndDevice = $this->_isLowEndDevice; 

        if($isLowEndDevice):
            $this->_helper->viewRenderer('updates-low-end');
        endif;
        
    }
    
    public function notFoundAction()
    {
        $translate = Zend_Registry::get('Zend_Translate');
        $msgNotFound = "Ooops. Application you were trying to Download / Buy was not found. Please try to download / buy some other application/s. Sorry for the inconvenience.";
        $message =  ($translate != null) ? $translate->translate($msgNotFound) : $msgNotFound ;
        $this->view->message = "<p>".$message."</p>";
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
        $modelDownloadStats = new Model_StatisticDownload();
        $model_ProductBuild = new Model_ProductBuild();
        
        foreach ($products as &$product) 
        {             
            //Add translation. get the translation for product name and overite if the translation available
            /*$product_name = $productLanguageMeta->getTranslationValue($product['product_id'], $this->_chapLanguageId, 'PRODUCT_NAME');
            if($product_name):
                $product["name"] = $product_name;
            endif;*/
            
            //Get build updates if available
            $product["updates_available"] = 0;
            $downloadedProduct = array();
            if($this->_userId){
                $downloadedProduct = $modelDownloadStats->getDownloadedBuildsByUserId($this->_userId, 'MOBILE', $product['product_id']);
            }
            if(count($downloadedProduct)>0){
                $product["updates_available"] = $model_ProductBuild->checkProductBuildUpdated($downloadedProduct[0]['product_id'], $downloadedProduct[0]['platform_id'], $downloadedProduct[0]['language_id'], $downloadedProduct[0]['date']);
            }
            
            $product["thumbnail"] = $visualPath . '/' . $product["thumbnail"];
            
            $productMeta->setEntityId($product['product_id']);
            
            /* Add translation. 
             * Get the translations for product name,description,brief_description and overite if the translations are available.
             * If not let it be with english language.
             */
            
            
            $productTranslation = $productLanguageMeta->loadTranslation($product['product_id'], $this->_chapLanguageId);
            /*$appArr = array('id' => $product['product_id']); //Temp
            $productTranslation = $productLanguageMeta->translateProduct($appArr, $this->_chapLanguageId, $this->_chapId);*/

            //this is temporary for MTN Iran cell
            /* @todo: needs to be removed once we bags all translations
             */
            
           /*if($_SERVER['REMOTE_ADDR'] == '106.186.127.174'){
               echo $this->_chapLanguageId.'###'.$this->_chapId; 
                print_r($productTranslation);
            }*/
            
            $product["name"] = ($productTranslation->PRODUCT_NAME)? stripslashes(strip_tags($productTranslation->PRODUCT_NAME)) : $product["name"];
           
            $product["description"] = ($productTranslation->PRODUCT_DESCRIPTION)? stripslashes(strip_tags($productTranslation->PRODUCT_DESCRIPTION)) : stripslashes(strip_tags($productMeta->FULL_DESCRIPTION));
            $product["brief_description"] = ($productTranslation->PRODUCT_SUMMARY)? stripslashes(strip_tags($productTranslation->PRODUCT_SUMMARY)) : stripslashes(strip_tags($productMeta->BRIEF_DESCRIPTION));
            
            //below codes commented
            /*$product['description'] = stripslashes(nl2br(strip_tags($productMeta->FULL_DESCRIPTION, "<br>")));
            $product['brief_description'] = stripslashes(strip_tags($productMeta->BRIEF_DESCRIPTION));*/

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
    
    /**
     * This is a function for showing MTN developer challenge apps
     */
    
    public function mtnDeveloperChallengeAction()
    {   
        $isLowEndDevice = $this->_isLowEndDevice; 
        
        $chapProducts = new Partnermobile_Model_ChapProducts();
        
        $developerChallengeApps = $chapProducts->getFlaggedAppsByDevice($this->_chapId, $this->_deviceId);
      
        //get Product details
        if (count($developerChallengeApps) > 0) 
        {   $this->view->showResults = TRUE;
            $developerChallengeApps = $this->getProductDetails($developerChallengeApps, $this->_deviceId);
        }
                
        
        $pagination = Zend_Paginator::factory($developerChallengeApps);
        $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
        $pagination->setItemCountPerPage(10);
        
        $this->view->developerChallengeApps = $pagination;

        //currency details by chap
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
        $this->view->currencyDetails = $currencyDetails;
        
        if($isLowEndDevice):
            $this->_helper->viewRenderer('mtn-developer-challenge-low-end');
        endif;
        
    }
    
    public function appstitudeAction()
    {
    	$isLowEndDevice = $this->_isLowEndDevice;
    
    	$chapProducts = new Partnermobile_Model_ChapProducts();
    
    	$developerChallengeApps = $chapProducts->getAppstitudeAppsByDevice($this->_chapId, $this->_deviceId);
    
    	
    	$this->view->showResults = FALSE;
    	
   
    	//get Product details
    	if (count($developerChallengeApps) > 0)
    	{
    	    $this->view->showResults = TRUE;
    		$developerChallengeApps = $this->getProductDetails($developerChallengeApps, $this->_deviceId);
    	}
    
    	$pagination = Zend_Paginator::factory($developerChallengeApps);
    	$pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
    	$pagination->setItemCountPerPage(10);
    	 
    	
    	$this->view->developerChallengeApps = $pagination;
    
    	//currency details by chap
    	$currencyUserModel = new Api_Model_CurrencyUsers();
    	$currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
    	$this->view->currencyDetails = $currencyDetails;
    
    	if($isLowEndDevice):
    	$this->_helper->viewRenderer('appstitude-low-end');
    	endif;
    
    }
    
    
    public function islamicAction()
    {
    	$isLowEndDevice = $this->_isLowEndDevice;
    
    	$chapProducts = new Partnermobile_Model_ChapProducts();
    
    	$developerChallengeApps = $chapProducts->getIslamicAppsByDevice($this->_chapId, $this->_deviceId);
    
    	 
    	$this->view->showResults = FALSE;
    	 
    	 
    	//get Product details
    	if (count($developerChallengeApps) > 0)
    	{
    		$this->view->showResults = TRUE;
    		$developerChallengeApps = $this->getProductDetails($developerChallengeApps, $this->_deviceId);
    	}
    
    	$pagination = Zend_Paginator::factory($developerChallengeApps);
    	$pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
    	$pagination->setItemCountPerPage(10);
    
    	 
    	$this->view->developerChallengeApps = $pagination;
    
    	//currency details by chap
    	$currencyUserModel = new Api_Model_CurrencyUsers();
    	$currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
    	$this->view->currencyDetails = $currencyDetails;
    
    	if($isLowEndDevice):
    	$this->_helper->viewRenderer('islamic-low-end');
    	endif;
    
    }
    
    /*public function testAction(){
        $secretKey = 'sDhsaf32h9';
        
        $data = json_encode(array('user_id' => (int) 23142));
        $userHash = hash_hmac('sha256', $data, $secretKey);
        
        echo $userHash;  die();
    }*/
    
    
    /**
     * Buy an application for YCoins chap
     * @param id App ID
     * @param build Build ID
     * returns nothing. It's redirecting to YCoins payment login screen
     */ 
    public function buyAppByRedirectionPgAction()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');
        
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(true);
        
        $appSession= $this->getRequest()->getParam('app_session', null);
        
        if($appSession == null){
            if(!Zend_Auth::getInstance()->hasIdentity()){
                 $this->_redirect ( '/user/login' );
            }
            else{
                $auth = Zend_Auth::getInstance();        
                $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;
            }
        }
        else{
            $userId = 1;
        }
        
        
        
        $appId = $this->getRequest()->getParam('id', null);
        $buildId = $this->getRequest()->getParam('build', null);
        $paymentGatewayName = $this->getRequest()->getParam('payment_gateway_name', null);
        $chapId = $this->_chapId;

        //check whether the app has been downloaded already, if it downloaded already no payment required for the same user
        /*$modelDownloadStats = new Api_Model_StatisticsDownloads();
        $alreadyDownloaded = $modelDownloadStats->checkThisDownloadExist($appId,$chapId,$userId,$buildId);
     
        //if already downloaded it's redirecting to free download function
        if($alreadyDownloaded['exist_count'] > 0){
            //Call the free download function
            $this->downloadAction();
            }
        else{
            
        }*/
        
        //Get payment gateway Id of the CHAP            
        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
        
        //If chap has more than 1 payment method the ID will sent from view 
        if($paymentGatewayName){
            $pgType = $paymentGatewayName; 
        }
        else{
            $pgType = $pgDetails->gateway_id; 
        }
        
        $paymentGatewayId = $pgDetails->payment_gateway_id;

        //Get product details by appId
        $productModel = new Partnermobile_Model_Products();
        $productDetails = $productModel->getDetailsById($appId);

        //$mobileNo = $auth->getIdentity()->mobile_no;//Mobile number will be null.
        $mobileNo = '';
        $price = $productDetails->price;
        $appName = $productDetails->name;
        $deviceId = $this->_deviceId;
        $pgClassName = $pgType;
        
        /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99')
        {
            echo $pgClassName; die();
        }*/
        
        //Call Nexva_MobileBilling_Factory and create relevant instance. Since this is a redirection payment, this factory doesn't contain the payment codes
        $pgClass = Nexva_PaymentGateway_Factory::factory($pgType,$pgClassName);
        
        //Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
        $interopPaymentId = $pgClass->addMobilePayment($chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId);
        
        //Set the parameters to redirection and execute
        $data = array('chap_id' => $chapId, 'price' => $price, 'interop_payment_id' => $interopPaymentId, 'app_id' => $appId, 'app_name' => $appName);
        //Zend_Debug::dump($data);die();
        $pgClass->executeRequest($data);
        
    }
    
    public function postBackPgAction(){

        //print_r($_REQUEST); die();
        if(empty($_REQUEST))
             $this->_redirect ( '/' );

       /* if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            Zend_Debug::dump($_REQUEST);
            echo 'post - '.print_r($_POST); 
            echo '<br/>get - '.print_r($_REQUEST); die();
        }
        */
        
        $sessionId = Zend_Session::getId();
        $chapId = $this->_chapId;

        //Get payment gateway Id of the CHAP            
        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
        
        $paymentGatewayId = '';
        
        //If chap has more than 1 payment method the ID will sent from view
        if($paymentGatewayName){
            $pgType = $paymentGatewayName; 
        }
        else{
            $pgType = $pgDetails->gateway_id; 
        }
        
        $paymentGatewayId = $pgDetails->payment_gateway_id;
        $pgClassName = $pgType;
        
        //Call Nexva_MobileBilling_Factory and create relevant instance. Since this is a redirection payment, this factory doesn't contain the payment codes
        $pgClass = Nexva_PaymentGateway_Factory::factory($pgType,$pgClassName);

        //Sample response for YCoins
        $postBackResponse = $_REQUEST;
        //$postBackResponse = Array ( 'clientid' => 'OfkLQf95fqPTwcV9YtpjKPD265/UbLcoB61EBTkH7Sg=', 'transactionid' => 14910, 'status' => 'success', 'price' => 3.00 );
        
        /*if(($this->_chapId == 115189) && ($_SERVER['REMOTE_ADDR'] == '220.247.236.99')){
            $postBackResponse = Array( 'clientid' => '8fb0u2Mf3cGs3enaOIT8KsPQmtWSlM6yow1rB6RuWx4=', 'transactionid' => 14910, 'status' => 'success', 'price' => 3.00 );
        }
        */

        //handleResponse function will return the build URL
        //$buildUrl = $pgClass->handleResponse($postBackResponse, $chapId);
        $returnDetails = $pgClass->handleResponse($postBackResponse, $chapId);

        //Check if the payment has been made successfully
        if($returnDetails['status'] == 'success'){
            
            //echo $appId.'###'.$this->_chapId.'###'.$source.'###'.$ipAddress.'###'.$userId.'###'.$buildId.'###'.$buildInfo->platform_id.'###'.$buildInfo->language_id.'###'.$this->_deviceId.'###'.$sessionId;
            /*$this->view->buildUrl = $buildUrl;
            $msgSuccess = "Payment was suceessful";
            $message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
            $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);  
            $this->_helper->viewRenderer('detail');
            $this->_redirect('/app/detail/id/30628/builid/');*/
            
            $appId = $pgClass->getAppId($postBackResponse);
            
            $paymentTimeStamp = date('Y-m-d H:i:s');
            $paymentTransId = strtotime("now");
            
            $this->_request->setPost(array(
                        'buildUrl' => $returnDetails['build_url'],
                        'id' => $appId,
                        'transactionId' => $paymentTransId
                    ));
                    
            $this->_forward('detail', 'app', 'partnermobile');
                    
            //Redirect to detail action to download the app by JS
            //$this->_forward('detail', 'app', 'partnermobile', array('buildUrl' => $returnDetails['build_url'], 'id' => $appId));
            
            //$this->detailAction(30628,$buildUrl);

        }
        else{
            //$appId = $buildUrl; //If the payment unsuccess it's returning the app id as build url
            $appId = $pgClass->getAppId($postBackResponse);
            //$msgSuccess = "Payment was unsuccessful";
            $errorMessage = 'Payment Gateway Response : '.$returnDetails['status_message'];
            $message =  ($translate != null) ? $translate->translate($errorMessage) : $errorMessage ;
            $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);            
            $this->_redirect('/app/detail/id/'.$appId);
        }
    }
    
    public function postBackCbAction(){
        $this->postBackPgAction();
    }
    
    public function testgooglewalletAction(){
        $pgClass = Nexva_PaymentGateway_Factory::factory('GoogleWallet','GoogleWallet');
        $pgClass->generateJwt(41526);
        die();
    }
    
    
    //Temporary function for insert stats and royalities to apps
    //Please ignore the following function. This needs to be deleted
    public function insertStatAndRoyalitiesAction()
    {

        /*error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        //Static details goes here
        $appId = 43232;
        $buildId = 72930;
        $paymentGateway = 16;
        $chapId = 115189;
	$userId = 175417;
        
        //Get payment gateway Id of the CHAP            
        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
        $pgType = $pgDetails->gateway_id; 
        $paymentGatewayId = $pgDetails->payment_gateway_id;

        $productModel = new Partnermobile_Model_Products();
        $productDetails = $productModel->getDetailsById($appId);
        $mobileNo = '';        
        $price = $productDetails->price;
        $appName = $productDetails->name;
        $deviceId = 5226;
        $sessionId = Zend_Session::getId();
      
        $pgClass = Nexva_PaymentGateway_Factory::factory($pgType,$pgType);
        $interopPaymentId = $pgClass->addMobilePayment($chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId);

        $buildUrl = 'http://s3.amazonaws.com/production.applications.nexva.com/productfile/43232/72930_dtr_android_all_multilang.apk?AWSAccessKeyId=AKIAIB7MH7NAQK55BKOQ&Expires=1408462325&Signature=vk3%2BQ0QRU1gfR1gaQaO47oomPW0%3D';

        if(!empty($buildUrl) && !is_null($buildUrl)) 
        {
            $userAccount = new Model_UserAccount();
            $userAccount->saveRoyalitiesForApi($appId, $price, $paymentMethod='CHAP', $chapId, $userId);
            $source = "MOBILE";
            $ipAddress = $_SERVER['REMOTE_ADDR'];

            $model_ProductBuild = new Model_ProductBuild();
            $buildInfo = $model_ProductBuild->getBuildDetails($buildId);

            $paymentTransId = date("Ymd") . date("His");
            $paymentTimeStamp = date('d-m-Y');
            $paymentTransId = strtotime($paymentTimeStamp);
            $pgClass->updateInteropPayment($paymentTimeStamp, $paymentTransId, $paymentResult = 'success', $buildUrl, $interopPaymentId);

            $modelDownloadStats = new Api_Model_StatisticsDownloads();
            $modelDownloadStats->addDownloadStat($appId, $chapId, $source, $ipAddress, $userId, $buildId, 12, 1, 8650, $sessionId);
        }
        echo $price; die();
        die();*/
    }
    
    /*
     * Following function has been implemented for send SMS verification befor the premium app download. 
     * This is like an OTP send to user's mobile to verify the payment
     */
    public function sendPaymentVerificationSmsAction(){

        //error_reporting(E_ALL);
        //ini_set('display_errors', 1);
        
        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');
        
        //Check if the user logged in
        if(!Zend_Auth::getInstance()->hasIdentity()){
             $this->_redirect ( '/user/login' );
        }
        else{
            $auth = Zend_Auth::getInstance();        
            $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;
        }
        
        $this->view->appId = $this->getRequest()->getParam('id', null);
        $this->view->buildId = $this->getRequest()->getParam('build', null);
        
        $appId = $this->getRequest()->getParam('id', 'null');
        $buildId = $this->getRequest()->getParam('build', 'null');
        $msisdn = $this->getRequest()->getParam('msisdn', '');
        $chapId = $this->_chapId;
        
        
        $modelDownloadStats = new Api_Model_StatisticsDownloads();
        
        //same user, same product, same device, same chap stats are not allowed to insert
        $alreadyDownloaded = $modelDownloadStats->checkThisDownloadExist($appId,$chapId,$userId,$buildId);
         
        //if already downloaded it's redirecting to free download function

        if($alreadyDownloaded['exist_count'] > 0){
        
        	//Call the free download function
        	$this->downloadAction();
        	 
        }
        
            
        if($this->_request->isPost()){
            
            $appId = $this->getRequest()->getPost('appId', 'null');
            $buildId = $this->getRequest()->getPost('buildId', 'null');
            $msisdn = $this->getRequest()->getPost('msisdn', '');
            $chapId = $this->_chapId;

            $filters = array
            (
                'msisdn'         => array('StringTrim')
            );

            $validators = array(
                    'msisdn' => array(
                        new Zend_Validate_NotEmpty(),new Zend_Validate_Digits(),new Zend_Validate_StringLength(array('min' => 10)),
                        Zend_Filter_Input::MESSAGES => array(
                            array(
                                Zend_Validate_NotEmpty::IS_EMPTY => 'Please enter your Phone Number',
                            ),
                            array(
                                Zend_Validate_Digits::INVALID => ''
                            ),
                            array(
                                Zend_Validate_StringLength::TOO_SHORT => 'Mobile Number cannot be less than 10 digits',
                            )
                        )
                    )
                );

            $inputValidation = new Zend_Filter_Input($filters, $validators, array('msisdn' => $msisdn));

            if($inputValidation->isValid())
            {
                //Get payment gateway Id of the CHAP            
                $pgUsersModel = new Api_Model_PaymentGatewayUsers();
                $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
                $pgType = $pgDetails->gateway_id;
                $paymentGatewayId = $pgDetails->payment_gateway_id;

                //Get product details by appId
                $productModel = new Partnermobile_Model_Products();
                $productDetails = $productModel->getDetailsById($appId);

                //$mobileNo = $auth->getIdentity()->mobile_no;
                $mobileNo = $msisdn; //User's HE msisdn will not take into an account
                $price = $productDetails->price;
                $appName = $productDetails->name;
                $deviceId = $this->_deviceId;
                $pgClassName = $pgType;

                //Call Nexva_MobileBilling_Factory and create relevant instance. Since this is a redirection payment, this factory doesn't contain the payment codes
                $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

                //Generate 4 digit random number
                $smsVerificationToken = rand(1000,9999);
                //Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
                $interopPaymentId = $pgClass->addMobilePayment($chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId, $smsVerificationToken);

                $message = 'Please use the following OTP to complete the purchase - '.$smsVerificationToken.'.';
                
                
                // set custermized messages requested by the telco 
                if($this->_chapId == 274515) 
                    $message =  'Veuillez utiliser le code suivant pour terminer votre paiement - '.$smsVerificationToken.'.';
                
                
                $msgWeb = '';
                //Send SMS verification to user
                $smsSent = $pgClass->sendsms($mobileNo, $message, $chapId);

                $this->view->interopPaymentId = $interopPaymentId;
                $this->view->msisdn = $msisdn; 
                    
                if($smsSent){
                    $msgSuccess = "Please enter the 4 digit OTP sent to your mobile number";
                    /*$message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
                    $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);   */  
                    $this->view->success = $msgSuccess;
                    $this->_helper->viewRenderer('send-otp');
                }
                else{

                    $msgSuccess = "Invalid MSISDN";
                    $message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
                    $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);            
                    $this->_redirect('/app/detail/id/'.$appId);
                }

            }
            else{
                $messages = $inputValidation->getMessages();
                $errorsMessages = array();
                foreach ($messages as $key => $value)
                {
                    foreach ($value as $msg)
                    {
                        $errorsMessages[] = $msg;
                    }
                }
                $this->view->ErrorMessages = $errorsMessages;
            }
        }

    }
    
    public function sendOtpAction(){
        
       // error_reporting(E_ALL);
      //  ini_set('display_errors', 1);
        
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(true);
        
        $appId = $this->getRequest()->getParam('id', null);
        $buildId = $this->getRequest()->getParam('build', null);
        $msisdn = $this->getRequest()->getParam('msisdn', null);
        $chapId = $this->_chapId;
            
        $filters = array
        (
            'msisdn'         => array('StringTrim')
        );
        
        $validators = array(
                'msisdn' => array(
                    new Zend_Validate_NotEmpty(),new Zend_Validate_Digits(),new Zend_Validate_StringLength(array('min' => 10)),
                    Zend_Filter_Input::MESSAGES => array(
                        array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Please enter your Phone Number',
                        ),
                        array(
                            Zend_Validate_Digits::INVALID => ''
                        ),
                        array(
                            Zend_Validate_StringLength::TOO_SHORT => 'Mobile Number cannot be less than 13 digits',
                        )
                    )
                )
            );
        
        $inputValidation = new Zend_Filter_Input($filters, $validators, array('msisdn' => $msisdn));

        if($inputValidation->isValid())
        {
            //Get payment gateway Id of the CHAP            
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
            $pgType = $pgDetails->gateway_id;
            $paymentGatewayId = $pgDetails->payment_gateway_id;

            //Get product details by appId
            $productModel = new Partnermobile_Model_Products();
            $productDetails = $productModel->getDetailsById($appId);

            //$mobileNo = $auth->getIdentity()->mobile_no;
            $mobileNo = $msisdn; //User's HE msisdn will not take into an account
            $price = $productDetails->price;
            $appName = $productDetails->name;
            $deviceId = $this->_deviceId;
            $pgClassName = $pgType;

            //Call Nexva_MobileBilling_Factory and create relevant instance. Since this is a redirection payment, this factory doesn't contain the payment codes
            $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

            //Generate 4 digit random number
            $smsVerificationToken = rand(1000,9999);
            //Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
            $interopPaymentId = $pgClass->addMobilePayment($chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId, $smsVerificationToken);

            $message = 'Please use the following OTP to complete the purchase - '.$smsVerificationToken;
            
            if($this->_chapId == 274515)
            	$message =  'Veuillez utiliser le code suivant pour terminer votre paiement - '.$smsVerificationToken.'.';
            
            $msgWeb = '';
            //Send SMS verification to user
            //$pgClass->sendsms($mobileNo, $message, $chapId);
            echo 'success###'.$interopPaymentId.'###'.$msgWeb; 
            //echo $interopPaymentId; die();
        }
        else{
            $messages = $inputValidation->getMessages();
            $errorsMessages = array();
            foreach ($messages as $key => $value)
            {
                foreach ($value as $msg)
                {
                    $msg .= $msg.'<br/>';
                }
            }
            echo 'fail###null###'.$msg; 
        }

        die();
    }
    
    /**
     * Buy an application by entering OTP
     * @param id App ID
     * @param build Build ID
     * @param otp one time password
     * @param msisdn Mobile Number
     * @param paymentId interop payment id
     * returns application build or redirect to URL
     */ 
    public function buyAppByOtpAction()
    {
        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');
        
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(true);
        
        if(!Zend_Auth::getInstance()->hasIdentity())
             $this->_redirect ( '/user/login' );
        
        $auth = Zend_Auth::getInstance();        

        if ($this->_request->isPost()) 
        {
            $appId     = trim($this->getRequest()->getPost('appId',null));
            $buildId     = trim($this->getRequest()->getPost('buildId',null));
            $opt  = trim($this->getRequest()->getPost('otp',null));
            $paymentId  = trim($this->getRequest()->getPost('paymentId',null));
            $msisdn  = trim($this->getRequest()->getPost('msisdn',null));
            $chapId = $this->_chapId;
            
            $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;
        
            //Get payment gateway Id of the CHAP            
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
            $pgType = $pgDetails->gateway_id; 
            $paymentGatewayId = $pgDetails->payment_gateway_id;
            
            $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
            $sessionId = Zend_Session::getId();
            //Select interop pament
            $interopPayment = $pgClass->selectInteropPayment($sessionId, $paymentId);
            
            if($opt == $interopPayment->token && ($interopPayment->status == 'Pending') && ($interopPayment->mobile_no == $msisdn)){

                $productModel = new Partnermobile_Model_Products();
                $productDetails = $productModel->getDetailsById($appId);

                $mobileNo = $msisdn;        
                $price = $productDetails->price;
                $appName = $productDetails->name;
                $deviceId = $this->_deviceId;

                $modelDownloadStats = new Api_Model_StatisticsDownloads();

                //same user, same product, same device, same chap stats are not allowed to insert
                $alreadyDownloaded = $modelDownloadStats->checkThisDownloadExist($appId,$chapId,$userId,$buildId);

                //if already downloaded it's redirecting to free download function
                if($_SERVER['REMOTE_ADDR'] == '61.245.173.81'){
                    $alreadyDownloaded['exist_count'] = 0;
                }
                if($alreadyDownloaded['exist_count'] > 0){
                //if(1 != 1){
                        //Call the free download function
                        $this->downloadAction();

                    }
                else{

                    //Do the transaction and get the build url                        
                    $buildUrl = $pgClass->doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price);
                    
                    //Check if payment was made successfully, Provide the download link
                    if(!empty($buildUrl) && !is_null($buildUrl)) 
                    {
             
                        //************* Add Royalties *************************
                        //$userAccount = new Model_UserAccount();
                        //$userAccount->saveRoyalitiesForApi($appId, $price, $paymentMethod='CHAP', $chapId, $userId);

                        //************* Add Statistics - Download *************************
                        $source = "MOBILE";
                        $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');

                        $model_ProductBuild = new Model_ProductBuild();
                        $buildInfo = $model_ProductBuild->getBuildDetails($buildId);

                        $paymentTimeStamp = date('Y-m-d H:i:s');
                        $paymentTransId = strtotime("now");
        
                        //Update success transaction to the relevant transaction id in the DB
                        $pgClass->updateInteropPayment($paymentTimeStamp, $paymentTransId, $paymentResult = 'Success', $buildUrl, $paymentId);

                        $modelDownloadStats->addDownloadStat($appId, $this->_chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $this->_deviceId, $sessionId);

                        
                        //$transactionId = null;
                        /*$this->_request->setPost(array(
                            'buildUrl' => $buildUrl,
                            'id' => $appId,
                            'transactionId' => $transactionId
                        ));*/

                        $this->_forward('detail', 'app', 'partnermobile',array('id' => $appId, 'buildUrl' => $buildUrl, 'transactionId' => $transactionId));
                        //$this->_redirect($buildUrl);
                        
                       // $this->_helper->viewRenderer('detail');
                    }
                    else 
                    {
                        $paymentTimeStamp = date('Y-m-d H:i:s');
                        $paymentTransId = strtotime("now");
                        $buildUrl = '';
                        //Update success transaction to the relevant transaction id in the DB
                        $pgClass->updateInteropPayment($paymentTimeStamp, $paymentTransId, $paymentResult = 'Fail', $buildUrl, $paymentId);

                        
                        $msgSuccess = "Payment was unsuccessful";
                        $message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
                        $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);            
                        $this->_redirect('/app/detail/id/'.$appId);
                        
                        
                    }
                }
            }
           else{
               $paymentTimeStamp = date('Y-m-d H:i:s');
                $paymentTransId = strtotime("now");
                $buildUrl = '';
                //Update success transaction to the relevant transaction id in the DB
                $pgClass->updateInteropPayment($paymentTimeStamp, $paymentTransId, $paymentResult = 'OTP Fail', $buildUrl, $paymentId);
                        
                $msgSuccess = "Incorrect OTP";
                $message =  ($translate != null) ? $translate->translate($msgSuccess) : $msgSuccess ;
                $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);            
                $this->_redirect('/app/detail/id/'.$appId);
            }
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
    
    
    public function buymtsAction(){
        
        
        $session = new Zend_Session_Namespace('payment_reference');
        
/*  
        
        Zend_Debug::dump($this->getRequest()->getParams());
        
        Zend_Debug::dump($this->getRequest()->getParams());
        Zend_Debug::dump($session->app_Id);
        Zend_Debug::dump($this->_userId);
        Zend_Debug::dump( $session->build_Id);
       
        die();
     */
        
        
    	$auth = Zend_Auth::getInstance();
    	$status=$this->getRequest()->getParam('status');
    	$app_id = $this->getRequest()->getParam('app_id');
    	$userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;
    	if($status=='success'){
    	    

    
    		$productDownloadCls = new Nexva_Api_ProductDownload();
    		
    		if(empty($session->app_Id)) 
    		    $appId = $app_id;
    		else 
    		    $appId = $session->app_Id;
    		
    		if(empty($session->build_Id)) 
    		   $buildId = $this->getRequest()->getParam('build_id');
    		else 
    		    $buildId = $session->build_Id; 

    		$buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
    		

    
    		$paymentResult = 'Success';
    		$paymentTimeStamp = date('Y-m-d H:i:s');
    		$paymentTransId = strtotime($paymentTimeStamp);
    		$telekomSrbijaObj= new Nexva_MobileBilling_Type_TelekomSrbija();
    		$telekomSrbijaObj->_paymentId=$session->payment_reference;
    		$telekomSrbijaObj->UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
    		
    		Zend_Debug::dump($buildUrl);
    		Zend_Debug::dump($appId);
    		Zend_Debug::dump($buildId);
    		 
    	
    		 
    		if($buildUrl != null && !empty($buildUrl))
    		{
    		    
    		
    		    
    			/************* Add Royalties *************************/
    			$userAccount = new Model_UserAccount();
    			$userAccount->saveRoyalitiesForApi($session->app_Id, $session->price, $paymentMethod='CHAP',  $this->_chapId, $userId);
    		
    			/************* Add codengo *************************/
    			$condengo = new Nexva_Util_Http_SendRequestCodengo;
    			$condengo->send($session->app_Id);
    
    			//************* Add Statistics - Download *************************
    			$source = "MOBILE";
    			$ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');
    
    			$modelProductBuild = new Model_ProductBuild();
    			$buildInfo = $modelProductBuild->getBuildDetails($session->build_Id);
    
    			$sessionId = Zend_Session::getId();
    			 
    			/* same user, same product, same device, same chap stats are not allowed to insert */
    			$modelDownloadStats = new Api_Model_StatisticsDownloads();
    			$rowBuild = $modelDownloadStats->checkThisStatExistWithSession($userId, $this->_chapId, $session->build_Id, $this->_deviceId, $sessionId);
    			 
    			/*If the record not exist only stat will be inserted*/
    			if($rowBuild['exist_count'] == 'no'){
    				$modelDownloadStats->addDownloadStat($session->app_Id, $this->_chapId, $source, $ipAddress, $userId, $session->build_Id, $buildInfo->platform_id, $buildInfo->language_id, $this->_deviceId, $sessionId);
    			}
    			
    	
    			$modelUSer = new Model_User(); 
    			
    			$userDetails = $modelUSer->getUserById($userId);
    			
    			$currencyUserModel = new Api_Model_CurrencyUsers();
    			$currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
    			$currencyRate = $currencyDetails['rate'];
    			$currencyCode = $currencyDetails['code'];
    			$sessionId = Zend_Session::getId();
    			$amount = ceil($currencyRate * $session->price);
    			
    		   // $telekomSrbijaObj->sendsms($userDetails->mobile_no, "Uspešno ste platili ".$amount." din za X servis na MTS storu.", $this->_chapId);

    			
    			$message =  "Hvala! Vaše plaćanje je uspešno završeno. Kliknite na dugme ‘Preuzmi’ da bi započeli preuzimanje." ;
    			
    			   $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
    			   
    		
    		    $this->_redirect('/app/detail/id/'.$session->app_Id);
    		    
    		} else {
    		    
    		    $message = "Naplata nije uspela. Molimo Vas pokušajte ponovo.";
    		    
    		    $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
    		    $this->_redirect('/app/detail/id/'.$app_id);
    		    
    		    Zend_Session::namespaceUnset('payment_reference');
    		    
    		}
    	}   else {
    	    
    	    $message =  "Naplata nije uspela. Molimo Vas pokušajte ponovo." ;
    	     
    	    $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
    	    $this->_redirect('/app/detail/id/'.$app_id);
    	     
    	    Zend_Session::namespaceUnset('payment_reference');
    	     
    	    
    	}
    	
    	$message =  "Naplata nije uspela. Molimo Vas pokušajte ponovo." ;
    	
    	$this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
    	$this->_redirect('/app/detail/id/'.$app_id);
    	
    }
    
    
    
    
    
    public function requestOrangeOtpAction() {
        
        $auth = Zend_Auth::getInstance();
        $mobileNo = $auth->getIdentity()->mobile_no;
       
        
        if(empty($mobileNo))        
            $this->view->mobile_no = $this->getRequest()->getParam('mobile_no');
        else 
             $this->view->mobile_no = $mobileNo;
            
            
        $this->view->appId = $this->getRequest()->getParam('app_id');
        $this->view->buildId = $this->getRequest()->getParam('build_id');
        $this->view->amount =  $buildId=$this->getRequest()->getParam('amount');
        $this->view->chapId =  $buildId=$this->getRequest()->getParam('chap_id');
        
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->view->chapId);
        $this->view->currency =  $currencyDetails['code'];
        
        
        $msg = '{
	"challenge": {
		"method": "OTP-SMS-AUTH",
		"country": "GIN",
		"service": "ORANGESTORE",
		"partnerId": "PADOCK",
		"inputs": [{
			"type": "MSISDN",
			"value": "+'.$mobileNo.'"
		},  {
			"type": "message",
			"value": "To confirm your purchase please enter the code %OTP%"
		}, {
			"type": "otpLength",
			"value": "4"
		}, {
			"type": "senderName",
			"value": "ORANGESTORE"
		}]
	}
}';
        
        //Zend_Debug::dump($msg);
        
         
        //   $url = "https://iosw-rest.orange.com/PDK/BE_API-1/challenge/v1/challenges/";
        $url = "https://iosw-rest.orange.com:443/PDK/BE_API-1/challenge/v1/challenges/";
         
        
        //  $url =   "https://iosw3sn-rest.orange.com:8443/PDK/BE_API-1";
        $cert_file = APPLICATION_PATH . '/configs/orangeappstore.pem';
        $cert_password = "abc123";
        
        $ch = curl_init();
        
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
             
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
            CURLOPT_RETURNTRANSFER        => true,
            CURLOPT_HEADER        => true,
            CURLOPT_VERBOSE        => true,
            CURLOPT_URL => $url ,
            CURLOPT_SSLCERT => $cert_file ,
            CURLOPT_SSLCERTPASSWD => $cert_password ,
        );
        
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($msg))
        );
        
        curl_setopt_array($ch , $options);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
        
        
        $output = curl_exec($ch);
        
        if(!$output)
        {
            echo "Curl Error : " . curl_error($ch);
        }
        else
        {
          //  echo 'ddd';
            $info = curl_getinfo($ch);
           // Zend_Debug::dump(preg_split("/\\r\\n|\\r|\\n/", $info['request_header']));
            $arrray = preg_split("/\\r\\n|\\r|\\n/",$output);
            $arrayb = explode('/',$arrray[6]);
            $this->view->challengeId = $arrayb[4] ;
            
 
        }
        
    }
    
    public function processOrangeOtpAction() {
        
     
        
        if ($this->_request->isPost()) {
        
            
      
            $mobileNo = $this->getRequest()->getParam('mobile_no');
            $appId = $this->getRequest()->getParam('app_id');
            $buildId = $this->getRequest()->getParam('build_id');
            $amount =  $this->getRequest()->getParam('amount');
            $currency = $this->getRequest()->getParam('currency');
            $chapId = $this->getRequest()->getParam('chap_id');
        
        
            $challnageId = $this->_request->challnage_id;
            $confirmationCode = $this->_request->confirmation_code;
        
            
/* 
            $url = $this->_baseUrl.'/app/success-ornage/mobile_no/'.$mobileNo.'/app_id/'.$appId.'/build_id/'.$buildId.'/amount/'.$amount.'/chap_id/'.$chapId.'/status/success/chap_id/'.$chapId;
            
            $this->_redirect($url); */
            
        
            $productModel = new Partnermobile_Model_Products();
            $productDetails = $productModel->getDetailsById($appId);
        
        
            $msg = '{
    	"challenge": {
    		"method": "OTP-SMS-AUTH",
    		"country": "GIN",
    		"service": "ORANGESTORE",
    		"partnerId": "PADOCK",
    		"inputs": [{
    			"type": "MSISDN",
    			"value": "+'.$mobileNo.'"
    		}, {
    			"type": "confirmationCode",
    			"value": "'.$confirmationCode.'"
    		}, {
    			"type": "info",
    			"value": "OrangeApiToken,ise2"
    		}]
    	}
    }';
             
            // $url = "https://iosw-rest.orange.com/PDK/BE_API-1/challenge/v1/challenges/588a04777970dc2f67df6cde";
            $url = "https://iosw-rest.orange.com:443/PDK/BE_API-1/challenge/v1/challenges/".$challnageId;
             
        
            //  $url =   "https://iosw3sn-rest.orange.com:8443/PDK/BE_API-1";
            $cert_file = APPLICATION_PATH . '/configs/orangeappstore.pem';
            $cert_password = "abc123";
        
            $ch = curl_init();
        
            $options = array(
                CURLOPT_RETURNTRANSFER => true,
                //CURLOPT_HEADER         => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                 
                CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
                CURLOPT_RETURNTRANSFER        => true,
                CURLOPT_HEADER        => true,
                CURLOPT_VERBOSE        => true,
                CURLOPT_URL => $url ,
                CURLOPT_SSLCERT => $cert_file ,
                CURLOPT_SSLCERTPASSWD => $cert_password ,
            );
        
        
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($msg))
            );
        
            curl_setopt_array($ch , $options);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
        
        
            $output = curl_exec($ch);
        
            if(!$output)
            {
                echo "Curl Error : " . curl_error($ch);
            }
            else
            {
               // echo 'ddd';
                $info = curl_getinfo($ch);
             //   Zend_Debug::dump($info['request_header'][5]);
             //   Zend_Debug::dump($output);
             //   die();
        
        
                $dd =   substr($output, strpos($output, '{')+strlen( '{'));
        
                $aa = '{'.$dd;
                $ss=    json_decode($aa);
                 
             //   Zend_Debug::dump($ss, 'json');
                //finally there you aew
              //  Zend_Debug::dump($ss->challenge->result[0]->value, 'json');
        
                $token = $ss->challenge->result[0]->value;

                if(is_null($token)) {
                // $flashMessege =  ($translate != null) ? $translate->translate($msgErrVerify) : $msgErrVerify ;
                $flashMessege = "Invalid token. Please check your messages and type new Auth code received.4";
                $flashMessege = "Le Token est invalide. Merci de vérifier vos messages et rentrer le nouveau code d'authentification reçu.";
              
                $this->_flashMessenger->addMessage($flashMessege);
                
                $url = $this->_baseUrl.'/app/request-orange-otp/mobile_no/'.$mobileNo.'/app_id/'.$appId.'/build_id/'.$buildId.'/amount/'.$amount.'/chap_id/'.$chapId;
              
                $this->_redirect($url);
                

                }
                 
                 
            }
        
            curl_close($ch);
        
        
            if($token) {
        
                // start payment request
        
        
                $currencyUserModel = new Api_Model_CurrencyUsers();
                $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
                $currencyRate = $currencyDetails['rate'];
                $currencyCode = $currencyDetails['code'];
        
                $price = ceil($currencyRate * $amount);
        
        
                $clientCorrelator = $appId.rand();
        
                $msg =    '{"amountTransaction": {
                   "endUserId": "acr:OrangeAPIToken",
                   "paymentAmount": {
                   "chargingInformation": {
                   "amount": "'.$price.'",
                   "currency": "'.$currency.'",
                   "description": "Test Rania"
                   },
                   "chargingMetaData" : {
                   "onBehalfOf" : "test123456789",
                   "purchaseCategoryCode" : "Game",
                   "channel" : "5",
                   "serviceId" : "ORANGESTORE"
                   }
               },
               "transactionOperationStatus": "Charged",
               "referenceCode": "REF-'.$mobileNo.$clientCorrelator.'",
               "clientCorrelator": "'.$clientCorrelator.'"
               }}';
       
        
                 
                $url = "https://iosw-rest.orange.com:443/PDK/OneAPI-1/payment/v1/acr:OrangeAPIToken/transactions/amount";
        
                //  $url =   "https://iosw3sn-rest.orange.com:8443/PDK/BE_API-1";
                $cert_file = APPLICATION_PATH . '/configs/orangeappstore.pem';
                $cert_password = "abc123";
        
                $ch = curl_init();
        
                $options = array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER         => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_RETURNTRANSFER        => true,
                    CURLOPT_HEADER        => true,
                    CURLOPT_VERBOSE        => true,
                    CURLOPT_URL => $url ,
                    CURLOPT_SSLCERT => $cert_file ,
                    CURLOPT_SSLCERTPASSWD => $cert_password ,
                );
        
                curl_setopt($ch, CURLINFO_HEADER_OUT, 1); //
                 
                // B64TSi6g73jnMJg3ksvGJvyM4Y/amh5Hi3gQmYq0HZnr+cNGlYT7Ao8XBXjx5eZ0qLQXSMDE70aLdqUiSLMZZxVzQ==|MCO=OGC|tcd=1487157168|ted=1487157268|k+E0Z/esPSlBTcmOKgSCM/YfBVk=
                //  B64TSi6g73jnMJg3ksvGJvyM4Y/amh5Hi3gQmYq0HZnr%2BcNGlYT7Ao8XBXjx5eZ0qLQXSMDE70aLdqUiSLMZZxVzQ%3D%3D%257CMCO%3DOGC%257Ctcd%3D1487157168%257Cted%3D1487157268%257Ck%2BE0Z/esPSlBTcmOKgSCM/YfBVk%3D
        
                $headers = array(
                    sprintf('OrangeAPIToken:%s', $token),
                    'Accept: application/json;charset=\'utf-8\'',
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($msg)
                );
        
            //    Zend_Debug::dump($headers);
        
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        
                curl_setopt_array($ch , $options);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
        
        
                $output = curl_exec($ch);
        
                if(!$output)
                {
                    echo "Curl Error : " . curl_error($ch);
                }
                else
                {
                 //   echo 'ddd';
                    $info = curl_getinfo($ch);
              //      Zend_Debug::dump($info['request_header'][5]);
              //      Zend_Debug::dump($info['request_header']);
               //     Zend_Debug::dump($output);
                    
                    
                    $dd =   substr($output, strpos($output, '{')+strlen( '{'));
                    
                    $aa = '{'.$dd;
                    $response =    json_decode($aa);
                    
                    //die();
                    $error = 1;
                    
                    // Error
                    
       /*           $bb =  json_decode('{"requestError":{"policyException":{"messageId":"POL1000","text":"User has insufficient credit for transaction.","variable":"NotEnoughCredit"}}}');
                    
                    Zend_Debug::dump($bb);
                     
                    Zend_Debug::dump($bb->requestError->policyException->messageId);
                    Zend_Debug::dump($bb->requestError->policyException->text); */

                    
                    if($response->requestError->policyException->messageId) {
                    
                    
                    $message =  "Error.. insufficient credit for transaction." ;
                    $message =  "Erreur... vous n'avez pas assez de crédit pour effectuer la transaction" ;
                     
                    $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
                    
                    $this->_redirect('/app/detail/id/'.$appId);

                        
                    }
                    
                    /*                  
 *                  $bb =  json_decode('{"amountTransaction":{"endUserId":"acr:OrangeAPIToken","paymentAmount":{"chargingInformation":{"description":"Test Rania","currency":"GNF","amount":93.0},"totalAmountCharged":93.0,"chargingMetaData":{"onBehalfOf":"test123456789","purchaseCategoryCode":"Game","channel":"5","serviceId":"ORANGESTORE"}},"transactionOperationStatus":"Charged","referenceCode":"REF-22462424591144217393593773","serverReferenceCode":"e0ecc467-5031-4caa-b897-e11ce7703e66","clientCorrelator":"44217393593773","resourceURL":"http://10.117.20.61:83//acr%3AOrangeAPIToken/transactions/amount/e0ecc467-5031-4caa-b897-e11ce7703e66"}}');
                    
                    Zend_Debug::dump($bb);
                    
                    Zend_Debug::dump($bb->amountTransaction->transactionOperationStatus); // "Charged"
                    Zend_Debug::dump($bb->amountTransaction->referenceCode); // "REF-22462424591144217393593773"
                    Zend_Debug::dump($bb->amountTransaction->serverReferenceCode);  //"e0ecc467-5031-4caa-b897-e11ce7703e66" */
                    
                    if($response->amountTransaction->transactionOperationStatus == "Charged") {

                        $url = $this->_baseUrl.'/app/success-ornage/mobile_no/'.$mobileNo.'/app_id/'.$appId.'/build_id/'.$buildId.'/amount/'.$amount.'/chap_id/'.$chapId.'/status/success/chap_id/'.$chapId.'/ref/'.$response->amountTransaction->serverReferenceCode;
                        
                        $this->_redirect($url);
                        
                    }
                     
                     
                }
        
            } else {
                
                $flashMessege = "Invalid token. Please check your messages and type new Auth code received.";
                $flashMessege = "Le Token est invalide. Merci de vérifier vos messages et rentrer le nouveau code d'authentification reçu.2";
                
                $this->_flashMessenger->addMessage($flashMessege);
                
                $url = $this->_baseUrl.'/app/request-orange-otp/mobile_no/'.$mobileNo.'/app_id/'.$appId.'/build_id/'.$buildId.'/amount/'.$amount.'/chap_id/'.$chapId;
                
                $this->_redirect($url);
               
                
             
                
            }
        
        
        }
        
        $flashMessege = "Invalid token. Please check your messages and type new Auth code received.1";
        $flashMessege = "Le Token est invalide. Merci de vérifier vos messages et rentrer le nouveau code d'authentification reçu.";
        
        $this->_flashMessenger->addMessage($flashMessege);
        
        $url = $this->_baseUrl.'/app/request-orange-otp/mobile_no/'.$mobileNo.'/app_id/'.$appId.'/build_id/'.$buildId.'/amount/'.$amount.'/chap_id/'.$chapId;
        
        $this->_redirect($url);
        
    
    }
    
        
        public function successOrnageAction(){
            
            
            
/*             
           $bb =  json_decode('{"requestError":{"policyException":{"messageId":"POL1000","text":"User has insufficient credit for transaction.","variable":"NotEnoughCredit"}}}');
            
           Zend_Debug::dump($bb);
           
           Zend_Debug::dump($bb->requestError->policyException->messageId);
           Zend_Debug::dump($bb->requestError->policyException->text);
           
            */
           
         
            
          
/*           $bb =  json_decode('{"amountTransaction":{"endUserId":"acr:OrangeAPIToken","paymentAmount":{"chargingInformation":{"description":"Test Rania","currency":"GNF","amount":93.0},"totalAmountCharged":93.0,"chargingMetaData":{"onBehalfOf":"test123456789","purchaseCategoryCode":"Game","channel":"5","serviceId":"ORANGESTORE"}},"transactionOperationStatus":"Charged","referenceCode":"REF-22462424591144217393593773","serverReferenceCode":"e0ecc467-5031-4caa-b897-e11ce7703e66","clientCorrelator":"44217393593773","resourceURL":"http://10.117.20.61:83//acr%3AOrangeAPIToken/transactions/amount/e0ecc467-5031-4caa-b897-e11ce7703e66"}}');

          Zend_Debug::dump($bb);
          
          Zend_Debug::dump($bb->amountTransaction->transactionOperationStatus); // "Charged"
          Zend_Debug::dump($bb->amountTransaction->referenceCode); // "REF-22462424591144217393593773"
          Zend_Debug::dump($bb->amountTransaction->serverReferenceCode);  //"e0ecc467-5031-4caa-b897-e11ce7703e66" */
           

        
            $status=$this->getRequest()->getParam('status');
            $appId = $this->getRequest()->getParam('app_id');
            $buildId = $this->getRequest()->getParam('build_id');
            $price = $this->getRequest()->getParam('amount');
            $mobileNo = $this->getRequest()->getParam('mobile_no');
            $chapId = $this->getRequest()->getParam('chap_id');
            $ref = $this->getRequest()->getParam('ref');
             
            $user = new Api_Model_Users();
            $userInfo = $user->getUserByMobileNo($mobileNo);
            $userId = $userInfo->id; 

            
            if($status=='success'){            
                
                $pgUsersModel = new Api_Model_PaymentGatewayUsers();
                $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
                $paymentGatewayId = $pgDetails->payment_gateway_id;
                
                $productDownloadCls = new Nexva_Api_ProductDownload();
                           
                $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
                
                $paymentResult = 'Success';
                $paymentTimeStamp = date('Y-m-d H:i:s');
                $paymentTransId = $ref;
               
                $orangeGuineaObj = new Nexva_MobileBilling_Type_OrangeGuinea();
                $paymentRefernce =   $orangeGuineaObj->addMobilePayment($chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId, '222');
                
                
                $orangeGuineaObj->_paymentId=$paymentRefernce;
                $orangeGuineaObj->UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
        
                 
                if($buildUrl != null && !empty($buildUrl))
                {
        
                    /************* Add Royalties *************************/
                    $userAccount = new Model_UserAccount();
                    $userAccount->saveRoyalitiesForApi($appId, $price, $paymentMethod='CHAP',  $chapId, $userId);
        
                    /************* Add codengo *************************/
                    $condengo = new Nexva_Util_Http_SendRequestCodengo;
                    $condengo->send($appId);
        
                    //************* Add Statistics - Download *************************
                    $source = "MOBILE";
                    $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');
        
                    $modelProductBuild = new Model_ProductBuild();
                    $buildInfo = $modelProductBuild->getBuildDetails($buildId);
        
                    $sessionId = Zend_Session::getId();
        
                    /* same user, same product, same device, same chap stats are not allowed to insert */
                    $modelDownloadStats = new Api_Model_StatisticsDownloads();
                    $rowBuild = $modelDownloadStats->checkThisStatExistWithSession($userId, $chapId, $buildId, $this->_deviceId, $sessionId);
        
                    /*If the record not exist only stat will be inserted*/
                    if($rowBuild['exist_count'] == 'no'){
                        $modelDownloadStats->addDownloadStat($appId, $chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $this->_deviceId, $sessionId);
                    }
                     
                     
                    $modelUSer = new Model_User();
                     
                    $userDetails = $modelUSer->getUserById($userId);
                     
                    $currencyUserModel = new Api_Model_CurrencyUsers();
                    $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
                    $currencyRate = $currencyDetails['rate'];
                    $currencyCode = $currencyDetails['code'];
                    $sessionId = Zend_Session::getId();
                    $amount = ceil($currencyRate * $price);
                     
                    $message = 'Y\'ello, vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour un telechargement depuis  Orange app-store. Merci.';
                    $orangeGuineaObj->sendsms($userDetails->mobile_no, $message, $this->_chapId);
              
                     
                    $message =  "Merci! Votre paiement a été effectué avec succès. Merci de cliquer sur le bouton 'télécharger' pour démarrer le téléchargement." ;
                     
                    $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
        
                    $this->_redirect('/app/detail/id/'.$appId);
        
                } else {
        
                    $message = "Paiement échoué";
        
                    $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
                    $this->_redirect('/app/detail/id/'.$appId);
        
                    Zend_Session::namespaceUnset('payment_reference');
        
                }
            }   else {
                	
                $message =  "Payment Failed." ;
        
                $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
                $this->_redirect('/app/detail/id/'.$appId);
        
                Zend_Session::namespaceUnset('payment_reference');
        
                	
            }
             
            $message =  "Paiement échoué" ;
             
            $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
            $this->_redirect('/app/detail/id/'.$appId);
             
        }

        public function paypalPostBackAction(){
            
            $buildId=$this->getRequest()->getParam('buildId', null);
            /* Get Platform Id*/
             $modelProductBuild = new Model_ProductBuild();
             $buildInfo = $modelProductBuild->getBuildDetails($buildId);
            /* End */
            
           
            /* Put data on session*/
            $paypalSessionData = new Zend_Session_Namespace('paypal_payment');            
            $paypalSessionData->chap_id=$this->getRequest()->getParam('chapId', null);
            $paypalSessionData->app_id=$this->getRequest()->getParam('appId', null);            
            $paypalSessionData->ip_address=$this->getRequest()->getServer('REMOTE_ADDR');
            $paypalSessionData->build_Id=$buildId;
            $paypalSessionData->platform_id=$buildInfo->platform_id;            
            $paypalSessionData->language_id=$buildInfo->language_id;
            $paypalSessionData->device_id=$this->getRequest()->getParam('deviceId', null);            
            $paypalSessionData->userId=$this->getRequest()->getParam('userId', null);            
            /* End */
            
            $this->view->appId=$this->getRequest()->getParam('appId', null);;
            $this->view->productName=$this->getRequest()->getParam('productName', null);;
            $this->view->productPrice=$this->getRequest()->getParam('productPrice', null);;
            $this->view->currency=$this->getRequest()->getParam('currency', null);;
            
        }
        public function paypalResponceAction(){
            
           
            /* Put data on session*/
            $getPaypalSessionData = new Zend_Session_Namespace('paypal_payment');                
            /* End */
            
            /*Save download stacs*/
             $source = "MOBILE";
             $modelDownloadStats = new Api_Model_StatisticsDownloads();
              $modelDownloadStats->addDownloadStat(
                      $getPaypalSessionData->app_id,
                      $getPaypalSessionData->chap_id,
                      $source,
                      $getPaypalSessionData->ip_address,
                      $getPaypalSessionData->userId,
                      $getPaypalSessionData->build_Id,
                      $getPaypalSessionData->platform_id,
                      $getPaypalSessionData->language_id,
                      $getPaypalSessionData->device_id,
                      Zend_Session::getId()
                      );
            /**/
            $appId=$getPaypalSessionData->app_id;
              /* */
            $userAccount = new Model_UserAccount();
            $userAccount->saveRoyalitiesForApi(
                    $appId,
                    $this->getRequest()->getParam('amount'),
                    $paymentMethod='CHAP',
                    $getPaypalSessionData->chap_id,
                    $getPaypalSessionData->userId);
              
              /**/
            /*Paypal payment status*/
            $userModel = new Model_User();
            $user = $userModel->getUserDetailsById($getPaypalSessionData->userId);
            
             $paypalPayment= new Api_Model_PaypalStatus();
             $paypalPayment->insertPaypalStatus(
                     $getPaypalSessionData->chap_id,
                     $appId,
                     $getPaypalSessionData->build_Id,
                     $user->email,
                     $this->getRequest()->getParam('amount'),
                     $this->getRequest()->getParam('seller'),
                     $this->getRequest()->getParam('status'),
                     date("Y-m-d H:i:s"),
                     $this->getRequest()->getParam('currency')
                     );
              /**/
            $message =  'Thank you! Your payment is successful, Please click on download button to start download.' ;
            $this->_helper->flashMessenger->setNamespace('errorMsgApp')->addMessage($message);
            Zend_Session::namespaceUnset('paypal_payment');
            $this->_redirect('/app/detail/id/'.$appId);
            
        }

}
