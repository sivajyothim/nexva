<?php

class Pbo_StatisticController extends Zend_Controller_Action {

    protected $_flashMessenger;

    public function init() {
        // Flash Messanger
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->flashMessenger = $this->_flashMessenger;
    }

    public function preDispatch() {


        if (!Zend_Auth::getInstance()->hasIdentity()) {

            $skip_action_names = array('login', 'register', 'forgotpassword', 'resetpassword', 'claim', 'impersonate');

            if (!in_array($this->getRequest()->getActionName(), $skip_action_names)) {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();
                $this->_redirect('/user/login');
            }
        }

        $this->_helper->layout->setLayout('pbo/pbo');

        $usersNs = new Zend_Session_NameSpace('members');
        $this->view->currencyRate = $usersNs->currencyRate;
        $this->view->currencyCode = $usersNs->currencyCode;

        //$this->view->headScript()->appendFile('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js');
        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/datepicker/css/ui.daterangepicker.css');
    }

    public function indexAction() {

        $chapId = 0;
        $userType = '';
        $totalViews = 0;
        $totalDownloads = 0;
        $totalUsers = 0;
        $totalCps = 0;
        $downloadsByApp = 0;
        $viewsByApp = 0;
        $totalFree = 0;
        $totalPremium = 0;
        $totalFreeViews = 0;
        $totalPremiumViews = 0;
        $totalFreeDownloads = 0;
        $totalPremiumDownloads = 0;
        $appDownloads = 0;
        $appView = 0;
        $newTelcos = array();
        $this->view->telcoDropdown = false;
        $this->view->chapId = 'all';


        $adminId = Zend_Auth::getInstance()->getIdentity()->id;

        $usersNs = new Zend_Session_NameSpace('members');
        $userType = $usersNs->userType;


        if ($this->_request->isPost()) {

            $startDate = $this->_request->from;
            $endDate = $this->_request->to;
        } else {

            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        }


        $themeMetaModel = new Model_ThemeMeta();
        $statisticDownloadModel = new Model_StatisticDownload();
        $statisticViewsModel = new Model_StatisticProduct();
        $chapProductsModel = new Pbo_Model_ChapProducts();

        $downloads = new Nexva_Analytics_ProductDownload();
        $views = new Nexva_Analytics_ProductView();
        $statisticProductModel = new Pbo_Model_StatisticsProducts();
        $statisticDownloadsModel = new Pbo_Model_StatisticsDownloads();
        $userAccount = new Model_UserAccount();

        $newTelcoDetailsArray = array();
        $i = 0;

        $userModel = new Model_User();

        $chapId = $this->_request->chap;

        $userModelPbo = new Pbo_Model_User();

        if ($userType == 'superAdmin') {

            $this->view->telcoDropdown = true;

            if ($chapId == 'all' or empty($chapId)) {
                $telcos = $userModelPbo->getAllTelcos($adminId);
            } else {

                $telcos = $userModelPbo->getTelco($chapId);
                $this->view->topAppSection = true;
                $downloadsByApp = $downloads->getMostDownloadedAppsByDateRange($startDate, $endDate, 10, $chapId);
                $viewsByApp = $views->getMostViewedAppsByDateRange($startDate, $endDate, 10, $chapId);
            }

            $telcosDropDown = $userModelPbo->getAllTelcos($adminId);

            $telcoDetailsArray = array();
            $k = 0;

            foreach ($telcosDropDown as $telcoDetailsNew) {

                $themeMetaValues = $themeMetaModel->getThemeMeta($telcoDetailsNew->id);
                $telcoDetailsArray[$k]['id'] = $telcoDetailsNew->id;
                $telcoDetailsArray[$k]['siteName'] = $themeMetaValues['WHITELABLE_SITE_NAME'];
                $k++;
            }

            $this->view->telcosDropDown = $telcoDetailsArray;
            $this->view->chapId = $chapId;
        } else {

            $telcos = $userModelPbo->getTelco($adminId);

            $this->view->topAppSection = true;

            if ($this->_request->isPost()) {

                if ($userType == 'superAdmin') {

                    $downloadsByApp = $downloads->getMostDownloadedAppsByDateRange($startDate, $endDate, 10, $chapId);
                    $viewsByApp = $views->getMostViewedAppsByDateRange($startDate, $endDate, 10, $chapId);
                } else {

                    $downloadsByApp = $downloads->getMostDownloadedAppsByDateRange($startDate, $endDate, 10, $adminId);
                    $viewsByApp = $views->getMostViewedAppsByDateRange($startDate, $endDate, 10, $adminId);
                }
            } else {

                $downloadsByApp = $downloads->getMostDownloadedAppsByDateRange($startDate, $endDate, 10, $adminId);
                $viewsByApp = $views->getMostViewedAppsByDateRange($startDate, $endDate, 10, $adminId);
            }
        }



        if (count($telcos) > 0) {

            $this->view->noTelcos = false;

            foreach ($telcos as $telcoDetails) {

                $themeMetaValues = $themeMetaModel->getThemeMeta($telcoDetails->id);
                $revenueAmount = '';


                $newTelcoDetailsArray[$i]['id'] = $telcoDetails->id;
                $newTelcoDetailsArray[$i]['siteName'] = $themeMetaValues['WHITELABLE_SITE_NAME'];
                $newTelcoDetailsArray[$i]['telcoAppId'] = $themeMetaValues['APPSTORE_APP_ID'];

                $newTelcoDetailsArray[$i]['appCount'] = $chapProductsModel->getAllProductsCountForChap($telcoDetails->id, $startDate, $endDate, 0);
                $newTelcoDetailsArray[$i]['free'] = $chapProductsModel->getProductsCountForChap($telcoDetails->id, $startDate, $endDate, 'free');
                $newTelcoDetailsArray[$i]['premium'] = $chapProductsModel->getProductsCountForChap($telcoDetails->id, $startDate, $endDate, 'premium');
                $newTelcoDetailsArray[$i]['appCountFeatured'] = $chapProductsModel->getAllProductsCountForChap($telcoDetails->id, $startDate, $endDate, 1);
                $newTelcoDetailsArray[$i]['telcoAppDownloadedCount'] = $statisticDownloadModel->totalDownloadAppCount($themeMetaValues['APPSTORE_APP_ID'], $startDate, $endDate);
                $newTelcoDetailsArray[$i]['views'] = $statisticViewsModel->getAllViewsForChap($telcoDetails->id, $startDate, $endDate);
                $newTelcoDetailsArray[$i]['freeAappViews'] = $statisticViewsModel->getAllViewsChapByType($telcoDetails->id, $startDate, $endDate, 'free');
                $newTelcoDetailsArray[$i]['premiumAappViews'] = $statisticViewsModel->getAllViewsChapByType($telcoDetails->id, $startDate, $endDate, 'premium');
                $newTelcoDetailsArray[$i]['freeAppDownloads'] = $statisticDownloadModel->getDownloadsTypeForChap($telcoDetails->id, $startDate, $endDate, 'free');
                $newTelcoDetailsArray[$i]['premiumAppDownloads'] = $statisticDownloadModel->getDownloadsTypeForChap($telcoDetails->id, $startDate, $endDate, 'premium');
                $newTelcoDetailsArray[$i]['downloads'] = $statisticDownloadModel->getDownloadsForChap($telcoDetails->id, $startDate, $endDate);
                $newTelcoDetailsArray[$i]['users'] = $userModel->getTotalNumberOfUsersChap($telcoDetails->id, 'USER', $startDate, $endDate);
                $newTelcoDetailsArray[$i]['cp'] = $userModel->getTotalNumberOfUsersChap($telcoDetails->id, 'CP', $startDate, $endDate);
                $revenueAmount = $userAccount->getTotalRevenueForChap($telcoDetails->id, $startDate, $endDate);
                $newTelcoDetailsArray[$i]['revenue'] = (isset($revenueAmount)) ? $revenueAmount : '0';

                $totalViews += $newTelcoDetailsArray[$i]['views'];
                $totalDownloads += $newTelcoDetailsArray[$i]['downloads'];
                $totalUsers += $newTelcoDetailsArray[$i]['users'];
                $totalCps += $newTelcoDetailsArray[$i]['cp'];
                $totalFree += $newTelcoDetailsArray[$i]['free'];
                $totalPremium += $newTelcoDetailsArray[$i]['premium'];
                $newTelcos[] = $telcoDetails->id;
                $totalFreeViews += $newTelcoDetailsArray[$i]['freeAappViews'];
                $totalPremiumViews += $newTelcoDetailsArray[$i]['premiumAappViews'];
                $totalFreeDownloads += $newTelcoDetailsArray[$i]['freeAppDownloads'];
                $totalPremiumDownloads += $newTelcoDetailsArray[$i]['premiumAppDownloads'];
                //  $total += $newTelcoDetailsArray[$i]['revenue'];
                $i++;
            }


            $freePremiumViewsRatio = array();
            $freePremiumDownloadRatio = array();

            $freePremiumViewsRatio['free'] = $totalFreeViews;
            $freePremiumViewsRatio['premium'] = $totalPremiumViews;

            $freePremiumDownloadRatio['free'] = $totalFreeDownloads;
            $freePremiumDownloadRatio['premium'] = $totalPremiumDownloads;


            if ($freePremiumViewsRatio['free'] != 0 or $freePremiumViewsRatio['premium'] != 0)
                $this->view->freePremiumViewsRatio = json_encode($freePremiumViewsRatio);
            else
                $this->view->freePremiumViewsRatio = 0;

            if ($freePremiumDownloadRatio['free'] != 0 or $freePremiumDownloadRatio['premium'] != 0)
                $this->view->freePremiumDownloadRatio = json_encode($freePremiumDownloadRatio);
            else
                $this->view->freePremiumDownloadRatio = 0;

            $appDownloads = $downloads->getAppDownloadsByDateForChap($newTelcos, $startDate, $endDate);
            $appView = $views->getAppViewsByDateForChap($newTelcos, $startDate, $endDate);

            $downloadsByAndroidApp = $statisticDownloadsModel->getAppDownloadsBySource($newTelcos, $startDate, $endDate, 'API');
            $downloadsByMobileWeb = $statisticDownloadsModel->getAppDownloadsBySource($newTelcos, $startDate, $endDate, 'MOBILE');

            $viewsByAndroidApp = $statisticProductModel->getAppViewsBySource($newTelcos, $startDate, $endDate, 'API');
            $viewsByWeb = $statisticProductModel->getAppViewsBySource($newTelcos, $startDate, $endDate, 'WEB');
            $viewsByMobileWeb = $statisticProductModel->getAppViewsBySource($newTelcos, $startDate, $endDate, 'MOBILE');
        } else {

            $this->view->noTelcos = true;
        }

        $this->view->startDate = $startDate;
        $this->view->endDate = $endDate;

        $this->view->appDownloadDateJson = json_encode($appDownloads);
        $this->view->appViewDateJson = json_encode($appView);

        $this->view->appDownloadsByAndroidAppJson = json_encode($downloadsByAndroidApp);
        $this->view->appDownloadsByMobileWebJson = json_encode($downloadsByMobileWeb);

        $this->view->appViewByAndroidAppJson = json_encode($viewsByAndroidApp);
        $this->view->appViewByWebJson = json_encode($viewsByWeb);
        $this->view->appViewByMobileWebJson = json_encode($viewsByMobileWeb);

        $this->view->viewsByApp = $viewsByApp;
        $this->view->downloadsByApp = $downloadsByApp;

        $this->view->chapCount = count($telcos);
        $this->view->telcoDetails = $newTelcoDetailsArray;

        $this->view->totalFree = $totalFree;
        $this->view->totalPremium = $totalPremium;

        $this->view->totalViews = $totalViews;
        $this->view->totalDownloads = $totalDownloads;
        $this->view->totalUsers = $totalUsers;
        $this->view->totalCps = $totalCps;
        $revenue = 0;

        $revenue = $userAccount->getTotalRevenueForChap($adminId, $startDate, $endDate);

        $userAccount = new Model_UserAccount();
        $this->view->paymentReceivedList = $userAccount->partnerPaymentReceivedList($startDate, $endDate, $adminId, $paymentType = 'WEB');

        if ($revenue) {
            $this->view->revenue = $revenue;
            $this->view->totalRevenue = '$ ' . number_format($revenue, 2);
        } else {
            $this->view->revenue = $revenue;
            $this->view->totalRevenue = '$ 0.00';
        }


        $this->view->totalApps = 0;
    }

    public function salesAction() {

        $adminId = Zend_Auth::getInstance()->getIdentity()->id;

        $usersNs = new Zend_Session_NameSpace('members');
        $userType = $usersNs->userType;

        if ($this->_request->isPost()) {

            $startDate = $this->_request->from;
            $endDate = $this->_request->to;
        } else {

            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        }

        $this->view->startDate = $startDate;
        $this->view->endDate = $endDate;

        $userAccount = new Model_UserAccount();

        $revenue = $userAccount->getTotalRevenueForUser($adminId, $startDate, $endDate);

        $this->view->paymentReceivedList = $userAccount->partnerPaymentReceivedList($startDate, $endDate, $adminId, $paymentType = 'WEB');


        if ($revenue) {
            $this->view->totalRevenue = '$ ' . number_format($revenue, 2);
        } else
            $this->view->totalRevenue = '$ 0.00';

        $this->view->totalApps = 0;
    }

    public function inAppAction() {

        $adminId = Zend_Auth::getInstance()->getIdentity()->id;

        $usersNs = new Zend_Session_NameSpace('members');
        $userType = $usersNs->userType;

        if ($this->_request->isPost()) {

            $startDate = $this->_request->from;
            $endDate = $this->_request->to;
        } else {

            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        }

        $this->view->startDate = $startDate;
        $this->view->endDate = $endDate;

        $userAccount = new Model_UserAccount();

        $revenue = $userAccount->getTotalRevenueForUser($adminId, $startDate, $endDate);

        $this->view->paymentReceivedList = $userAccount->partnerPaymentReceivedList($startDate, $endDate, $adminId, $paymentType = 'WEB');


        if ($revenue) {
            $this->view->totalRevenue = '$ ' . number_format($revenue, 2);
        } else
            $this->view->totalRevenue = '$ 0.00';

        $this->view->totalApps = 0;
    }

    public function purchasesAction() {



        $adminId = Zend_Auth::getInstance()->getIdentity()->id;

        $payoutInfo = new Model_Payout();
        $payoutInfo = $payoutInfo->getPayoutForUser($adminId);

        $this->view->payoutInfo = $payoutInfo;

        $usersNs = new Zend_Session_NameSpace('members');
        $userType = $usersNs->userType;
        //	$status = 'Success';
        $transaction_id = null;

        $interopPayments = new Api_Model_InteropPayments();
        if ($this->_request->isPost()) {

            $startDate = $this->_request->from;
            $endDate = $this->_request->to;
            $transaction_id = $this->_request->transaction_id;
            //$status = $this->getRequest()->getParam('status', NULL);

            $this->view->status = $status;


            if ($this->_request->report == 'Excel') {

                $interopPaymentsStatistics = $interopPayments->getInteropPaymentInfo($adminId, $startDate, $endDate, $status, $transaction_id);

                $data = "";
                $line = "App Id" . "\t" . "Build Id" . "\t" . "Mobile No" . "\t" . "Price(USD)" . "\t" . "Date Transaction" . "\t" . "Transaction Id" . "\t" . "Status" . "\t" . "Payment Geteway" . "\n";



                foreach ($interopPaymentsStatistics as $interopPaymentsStatistic) {
                    $line .= $interopPaymentsStatistic['app_id'] . "\t";
                    $line .= $interopPaymentsStatistic['build_id'] . "\t";
                    $line .= $interopPaymentsStatistic['mobile_no'] . "\t";
                    $line .= $interopPaymentsStatistic['price'] . ' [ You get ( ' . $payoutInfo->payout_chap . ')% - ' . $interopPaymentsStatistic['price'] / ($payoutInfo->payout_chap / 100) . "]\t";
                    $line .= $interopPaymentsStatistic['date_transaction'] . "\t";
                    $line .= $interopPaymentsStatistic['trans_id'] . "\t";
                    $line .= $interopPaymentsStatistic['status'] . "\t";
                    $line .= $interopPaymentsStatistic['maketing_name'] . "\t";

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
        } else {

            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        }


        $this->view->startDate = $startDate;
        $this->view->endDate = $endDate;
        $this->view->transaction_id = $transaction_id;




        $this->view->paymentList = $interopPayments->getInteropPaymentInfo($adminId, $startDate, $endDate, $status, $transaction_id);




        $this->view->totalApps = 0;
    }

    /**
     * Device wise apps downloads
     * 
     */
    public function deviceWiseAction() {
        $txtSearchKey = NULL;

        if ($this->_request->isPost()) {
            $txtSearchKey = trim($this->_request->getPost('txtSearchKey'));

            $this->view->txtSearchKey = $txtSearchKey;
        }

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $statsModel = new Pbo_Model_StatisticsDownloads();
        $downloads = $statsModel->countDownloadedAppsByDevice($chapId, $txtSearchKey);

        $showResults = false;
        //set pagination        
        $pagination = Zend_Paginator::factory($downloads);

        if (count($downloads) > 0) {
            $showResults = true;
            $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
            $pagination->setItemCountPerPage(10);

            $this->view->downloads = $pagination;
            unset($pagination);
        }

        $this->view->showResults = $showResults;
        $this->view->title = "Statistics : Device Wise App Downloads";
    }

    public function userWiseAction() {

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(-1);

        $txtSearchKey = NULL;

        if ($this->_request->isPost()) {
            $txtSearchKey = trim($this->_request->getPost('txtSearchKey'));

            $this->view->txtSearchKey = $txtSearchKey;
        }

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $statsModel = new Pbo_Model_StatisticsDownloads();
        $downloads = $statsModel->countDownloadedAppsByUser($chapId, $txtSearchKey);

        $showResults = false;
        //set pagination
        $pagination = Zend_Paginator::factory($downloads);

        if (count($downloads) > 0) {
            $showResults = true;
            $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
            $pagination->setItemCountPerPage(10);

            $this->view->downloads = $pagination;
            unset($pagination);
        }

        $this->view->showResults = $showResults;
        $this->view->title = "Statistics : User Wise App Downloads";
    }

    /**
     * Shows up payout scheme of the particular CHAP
     * 
     */
    public function payoutAction() {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $userModel = new Pbo_Model_User();
        $this->view->payoutDetails = $userModel->getPayoutDetailsByUser($chapId);
        $viewAll = trim($this->_request->view);
        $statDownloadsModel = new Pbo_Model_StatisticsDownloads();

        if ($this->_request->isPost()) {
            $fromDate = trim($this->_request->getPost('fromDate'));
            $toDate = trim($this->_request->getPost('toDate'));

            $fromDate = empty($fromDate) ? null : $fromDate;
            $toDate = empty($toDate) ? null : $toDate;
        } else {
            $toDate = date('Y-m-d');
            $fromDate = date('Y-m-01');
        }

        //if it's grand total
        if ($viewAll == 'all') {
            $fromDate = '';
            $toDate = '';

            $this->view->totalAllSales = $statDownloadsModel->getSalesValue($chapId);
            $this->view->payoutTitle = 'Payouts - Grand Total';
            $this->view->detailsUrl = '/statistic/developers';
        } else {
            $this->view->totalAllSales = $statDownloadsModel->getSalesValue($chapId, $fromDate, $toDate);
            //Zend_Debug::dump($this->view->totalAllSales);die();
            //Zend_Debug::dump($this->view->payoutDetails->payout_cp);
            $payoutTitle = 'Payouts - ';

            if (!is_null($fromDate) && !is_null($toDate) && !empty($fromDate) && !empty($toDate)) {
                $payoutTitle .= "From $fromDate To $toDate";
                $detailsUrl = '/statistic/developers/from/' . strtotime($fromDate) . '/to/' . strtotime($toDate);
            } elseif (!is_null($fromDate) && !empty($fromDate) && (is_null($toDate) || empty($toDate))) {
                $payoutTitle .= "From $fromDate";
                $detailsUrl = '/statistic/developers/from/' . strtotime($fromDate);
            } elseif (!is_null($toDate) && !empty($toDate) && (is_null($fromDate) || empty($fromDate))) {
                $payoutTitle .= "To $toDate";
                $detailsUrl = '/statistic/developers/to/' . strtotime($toDate);
            }

            $this->view->payoutTitle = $payoutTitle;
            $this->view->detailsUrl = $detailsUrl;
        }
        //Zend_Debug::dump($this->view->totalAllSales);die();
        $this->view->fromDate = $fromDate;
        $this->view->toDate = $toDate;
        $this->view->title = "Statistics : Payouts";
    }

    /**
     * Shows up developer wise app breakdown (commercial apps)
     * 
     */
    public function developersAction() {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $toDate = trim($this->_request->to);
        $this->view->to = $toDate;

        $fromDate = trim($this->_request->from);
        $this->view->from = $fromDate;

        $payout = trim($this->_request->payout);
        $this->view->payout = $payout;

        $paramFrom = 0;
        $paramTo = 0;
        if (!empty($fromDate) && !empty($toDate)) {
            $paramFrom = date('Y-m-d', $fromDate);
            $paramTo = date('Y-m-d', $toDate);
        }

        //get list of cp as developers under a particular chap
        $statDownloadsModel = new Pbo_Model_StatisticsDownloads();
        $records = $statDownloadsModel->getDevelopersByChap($chapId, $paramFrom, $paramTo);
        //Zend_Debug::dump($records);die();
        $this->view->title = "Payouts : Developer Wise Breakdown";
        $this->view->records = $records;
    }

    /**
     * Shows up app wise breakdown of a particular developer(commercial apps)
     * 
     */
    public function appWiseAction() {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        $user = $this->_request->user;

        $from = trim($this->_request->from);
        $this->view->from = $from;

        $to = trim($this->_request->to);
        $this->view->to = $to;

        $payout = trim($this->_request->payout);
        $this->view->payout = $payout;

        $paramFrom = 0;
        $paramTo = 0;
        if (!empty($from) && !empty($to)) {
            $paramFrom = date('Y-m-d', $from);
            $paramTo = date('Y-m-d', $to);
        }

        $this->view->title = "Payouts : Apps Wise Breakdown "/* .$userDetails[0]['username'] */;

        $userMeta = new Pbo_Model_UserMeta();
        $vendorName = $userMeta->getVenderName($user);
        $this->view->vendor = $vendorName;

        //get list of apps for a particular cp under particular chap
        $productstatsModel = new Pbo_Model_StatisticsDownloads();
        $productWiseDetails = $productstatsModel->getProductWiseStats($chapId, $user, $paramFrom, $paramTo);

        $this->view->records = $productWiseDetails;
    }

    function importStatisticsAction() {
        
    }

    /*     * @param : startDate, endDate, appType, noOfApps
     * Generates Excel reports for app wise statistics
     * @return : prints the Excel file using html headers
     */

    function appWiseStatisticsAction() {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $startDate = $this->_request->getParam('from_app');
        $endDate = $this->_request->getParam('to_app');
        $appType = $this->_request->getParam('app_type');
        $noOfApps = $this->_request->getParam('app_limit');
        $omitDuplicates = $this->_request->getParam('duplicate');

        $chapProductModel = new Pbo_Model_ChapProducts();
        $appWiseStatistics = $chapProductModel->appWiseStatistics($chapId, $startDate, $endDate, $appType, $noOfApps, $omitDuplicates);

        $productModel = new Model_Product();
        $categoryModel = new Pbo_Model_Categories();
        $themeMetaModel = new Pbo_Model_ThemeMeta();
        $url = $themeMetaModel->getMetaValueByMetaName($chapId, 'WHITELABLE_URL_WEB');

        $data = "";
        $line = "APP Name" . "\t" . "Download Count" . "\t" . "Developer E-Mail" . "\t" . "Platforms" . "\t" . "Date of Submission" . "\t" . "Categories" . "\t" . "Link" . "\n";

        foreach ($appWiseStatistics as $appWiseStatistic) {
            $productInfo = $productModel->getProductDetailsById($appWiseStatistic['productId']);

            $line .= $appWiseStatistic['name'] . "\t";
            $line .= $appWiseStatistic['count'] . "\t";
            $line .= $appWiseStatistic['email'] . "\t";
            foreach ($productInfo['supported_platforms'] as $platform) {
                $line .= $platform->name . ",";
            }
            $line .= "\t";
            $line .= $appWiseStatistic['submit_date'] . "\t";
            foreach ($productInfo['categories'] as $key => $value) {
                $category = $categoryModel->getCategoryById($value);
                //echo count($category);
                //echo $category[0]['name'];
                if (count($category) > 0) {
                    $line .= $category[0]['name'] . ",";
                }
            }
            $line .= "\t";

            $line .= "http://" . $url[0]['meta_value'] . "/" . $appWiseStatistic['productId'] . "\t";
            $line .= "\n";
        }
        //die();
        $data .= trim($line) . "\n";
        $data = str_replace("\r", "", $data);

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename = report.xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        print $data;
        exit;
    }

    function salesStatisticsAction() {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $startDate = $this->_request->getParam('from_app');
        $endDate = $this->_request->getParam('to_app');
        $appType = $this->_request->getParam('app_type');
        $noOfApps = $this->_request->getParam('app_limit');
        $omitDuplicates = $this->_request->getParam('duplicate');

        $chapProductModel = new Pbo_Model_ChapProducts();
        $appWiseStatistics = $chapProductModel->appWiseStatistics($chapId, $startDate, $endDate, $appType, $noOfApps, $omitDuplicates);

        $productModel = new Model_Product();
        $categoryModel = new Pbo_Model_Categories();
        $themeMetaModel = new Pbo_Model_ThemeMeta();
        $url = $themeMetaModel->getMetaValueByMetaName($chapId, 'WHITELABLE_URL_WEB');

        $data = "";
        $line = "APP Name" . "\t" . "Download Count" . "\t" . "Developer E-Mail" . "\t" . "Platforms" . "\t" . "Date of Submission" . "\t" . "Categories" . "\t" . "Link" . "\n";

        foreach ($appWiseStatistics as $appWiseStatistic) {
            $productInfo = $productModel->getProductDetailsById($appWiseStatistic['productId']);

            $line .= $appWiseStatistic['name'] . "\t";
            $line .= $appWiseStatistic['count'] . "\t";
            $line .= $appWiseStatistic['email'] . "\t";
            foreach ($productInfo['supported_platforms'] as $platform) {
                $line .= $platform->name . ",";
            }
            $line .= "\t";
            $line .= $appWiseStatistic['submit_date'] . "\t";
            foreach ($productInfo['categories'] as $key => $value) {
                $category = $categoryModel->getCategoryById($value);
                //echo count($category);
                //echo $category[0]['name'];
                if (count($category) > 0) {
                    $line .= $category[0]['name'] . ",";
                }
            }
            $line .= "\t";

            $line .= "http://" . $url[0]['meta_value'] . "/" . $appWiseStatistic['productId'] . "\t";
            $line .= "\n";
        }
        //die();
        $data .= trim($line) . "\n";
        $data = str_replace("\r", "", $data);

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename = report.xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        print $data;
        exit;
    }

    /*     * @param : startDate, endDate, noOfApps
     * Generates Excel reports for user wise statistics
     * @return : prints the Excel file using html headers
     */

    function userWiseStatisticsAction() {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $startDate = $this->_request->getParam('from_user');
        $endDate = $this->_request->getParam('to_user');
        $noOfUsers = $this->_request->getParam('user_limit');
        $omitDuplicates = $this->_request->getParam('duplicate');

        $statisticDownloadsModel = new Pbo_Model_StatisticsDownloads();
        $userWiseStatistics = $statisticDownloadsModel->userWiseStatistics($chapId, $startDate, $endDate, $noOfUsers, $omitDuplicates);

        $data = "";
        $line = "E-mail" . "\t" . "Mobile Number" . "\t" . "Downloads" . "\n";

        foreach ($userWiseStatistics as $userWiseStatistic) {
            $line .= $userWiseStatistic['email'] . "\t";
            $line .= $userWiseStatistic['mobile_no'] . "\t";
            $line .= $userWiseStatistic['downloads'] . "\t";

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

    function deviceWiseDetailsAction() {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        $deviceId = trim($this->_getParam('id'));

        $deviceModel = new Pbo_Model_Devices();
        $deviceDetails = $deviceModel->getDeviceDetails($deviceId);

        $this->view->deviceDetails = $deviceDetails;

        $statsModel = new Pbo_Model_StatisticsDownloads();
        $apps = $statsModel->getDownloadedAppsByDevice($deviceId, $chapId);

        $pagination = Zend_Paginator::factory($apps);

        if (count($apps) > 0) {
            $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
            $pagination->setItemCountPerPage(10);

            $this->view->downloads = $pagination;
            unset($pagination);
        }
    }

    function userWiseDetailsAction() {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        $userId = trim($this->_getParam('id'));
        $to = $this->_request->getParam('to');
        $from = $this->_request->getParam('from');

        $statsModel = new Pbo_Model_StatisticsDownloads();
        if ($this->_request->isPost()) {

            if ((empty($from) && !empty($to)) || (empty($to) && !empty($from))) {
                $this->_flashMessenger->addMessage(array('error' => 'Please select date range.'));
                $apps = $statsModel->getDownloadedAppsDetailsByUser($userId, $chapId, $from, $to);
            } else {
                $apps = $statsModel->getDownloadedAppsDetailsByUser($userId, $chapId, $from, $to);
            }
        } else {
            $apps = $statsModel->getDownloadedAppsDetailsByUser($userId, $chapId, $from, $to);
        }

        $pagination = Zend_Paginator::factory($apps);

        if (count($apps) > 0) {
            $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
            $pagination->setItemCountPerPage(10);

            $this->view->downloads = $pagination;
            unset($pagination);
        } else {
            $this->view->downloads = null;
        }
        $this->view->from = $from;
        $this->view->to = $to;
        $this->view->id = $userId;
    }

    //Serialize device names for ajax call in filter app section
    public function getDeviceNamesAction() {
        $txtSearchKey = $this->_request->getParam('q');

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        $statsModel = new Pbo_Model_StatisticsDownloads();
        $downloads = $statsModel->getDeviceNamesByKey($chapId, $txtSearchKey);

        echo json_encode($downloads);
        die();
    }

    /**
     * Downloaded Users show in PBO
     */
    public function downloadedUsersAction() {

        $this->view->title = 'Statistics : Downloaded Users';
        $appId = NULL;
        $fromDate = NULL;
        $toDate = NULL;



        if ($this->_request->isPost()) {
            $appId = trim($this->_request->getPost('appId'));
            $fromDate = $this->_request->getPost('fromDate');
            $toDate = $this->_request->getPost('toDate');
        } else {
            $appId = $this->_getParam('appId');
            $fromDate = $this->_getParam('fromDate');
            $toDate = $this->_getParam('toDate');
        }

        $this->view->appId = $appId;
        $this->view->fromDate = $fromDate;
        $this->view->toDate = $toDate;

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $statisticsDownloadsModel = new Pbo_Model_StatisticsDownloads();
        $downnloadedUsers = $statisticsDownloadsModel->getDownloadedUserForApp($chapId, $appId, $fromDate, $toDate);

        $showResults = false;
        if (count($downnloadedUsers) > 0) {
            $showResults = true;

            $pagination = Zend_Paginator::factory($downnloadedUsers);
            $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
            $pagination->setItemCountPerPage(10);

            $this->view->downloadUsers = $pagination;
            unset($pagination);
        }

        $this->view->showResults = $showResults;
    }

    /**
     *
     */
    public function dataUsageAction() {

        $this->view->title = "Statistics : Data Usage";

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $statisticDownloadModel = new Pbo_Model_StatisticsDownloads();

        if ($this->_request->isPost()) {
            $from = trim($this->_request->getPost('from'));
            $to = trim($this->_request->getPost('to'));
            $user = trim($this->_request->getPost('user'));

            $from = empty($from) ? null : $from;
            $to = empty($to) ? null : $to;
            $user = empty($user) ? null : $user;
        } else {
            $from = (trim($this->_request->from)) ? trim($this->_request->from) : date('Y-m-01');
            $to = (trim($this->_request->to)) ? trim($this->_request->to) : date('Y-m-d');
            $user = (trim($this->_request->user)) ? trim($this->_request->user) : null;
        }

        $data = $statisticDownloadModel->dataUsage($chapId, $from, $to, $user);

        $dataTotal = $statisticDownloadModel->dataUsageTotal($chapId, $from, $to, $user);

        $this->view->dataTotal = $dataTotal->data;

        $data = Zend_Paginator::factory($data);

        if (count($data) > 0) {
            $data->setCurrentPageNumber($this->_request->getParam('page', 1));
            $data->setItemCountPerPage(10);
        }

        $this->view->user = $user;
        $this->view->from = $from;
        $this->view->to = $to;
        $this->view->tableTitle = 'Data Usage';
        $this->view->data = $data;
    }

    /* get all inapp payments */

    public function inappPaymentsAction() {

        $this->view->title = "Statistics : Data Usage";
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $inappPaymentModel = new Pbo_Model_InappPayments();
        $allInappPayment = $inappPaymentModel->getAllInappPaymentByChap($chapId);


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

    public function ecarrotUserAction() {

        $authObj = Zend_Auth::getInstance()->getIdentity();
        $this->view->chap_id = $authObj->id;
        $ecarrotUser = new Pbo_Model_EcarrotUser();

        if ($this->_request->isPost()) {

            $startDate = $this->_request->from;
            $endDate = $this->_request->to;
            $this->view->startDate = $startDate;
            $this->view->endDate = $endDate;
            $this->view->earrotUser = $ecarrotUser->getEcarrotUser($startDate, $endDate);
        } else {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
            $this->view->startDate = $startDate;
            $this->view->endDate = $endDate;
            $this->view->earrotUser = $ecarrotUser->getEcarrotUser($startDate, $endDate);
        }
    }

    public function ecarrotPaidUserAction() {

        $authObj = Zend_Auth::getInstance()->getIdentity();
        $this->view->chap_id = $authObj->id;
        $ecarrotUser = new Pbo_Model_EcarrotUser();

        if ($this->_request->isPost()) {

            $startDate = $this->_request->from;
            $endDate = $this->_request->to;
            $this->view->startDate = $startDate;
            $this->view->endDate = $endDate;
            $this->view->earrotUser = $ecarrotUser->getEcarrotPaidUser($startDate, $endDate);
            
        } else {
            
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
            $this->view->startDate = $startDate;
            $this->view->endDate = $endDate;
            $this->view->earrotUser = $ecarrotUser->getEcarrotPaidUser($startDate, $endDate);
        }
    }

}
