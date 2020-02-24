<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 2/17/14
 * Time: 2:44 PM
 * To change this template use File | Settings | File Templates.
 */
class Pbo_CampaignController extends Zend_Controller_Action {

    public function preDispatch() {

        if( !Zend_Auth::getInstance()->hasIdentity() )
        {
            $skip_action_names = array ('login', 'register', 'forgotpassword', 'resetpassword', 'claim', 'impersonate');

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

    public function indexAction(){

    }

    /**************************************************************sms Campaign Functions ***************************************************************************/

    public function smsCampaignAction(){

    }

    /**
     *
     */
    public function addSmsCampaignAction(){
        $this->view->title = 'Add SMS Campaign';
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        if($this->_request->isPost()) {
            $name = trim($this->_getParam('campaign-name'));
            $description = trim($this->_getParam('campaign-description'));
            $campaignId = trim($this->_getParam('campaign-id'));

            $campaignModel = new Pbo_Model_Campaigns();
            $campaignData = array(
                'type' => 'sms',
                'name' => $name,
                'description' => $description,
                'chap_id' => $chapId,
                'created_date' => date('Y-m-d H:i:s')
            );

            if($campaignId){
                $lastInsertId = $campaignModel->update($campaignData,array ('id = ?' => $campaignId ));
                $message = 'SMS Campaign successfully edited. ';
            } else {
                $lastInsertId = $campaignModel->insert($campaignData);
                $message = ' SMS Campaign successfully Added. ';
            }

            if($lastInsertId){
                $this->_helper->flashMessenger->setNamespace('success')->addMessage($message);
                $this->_redirect (PBO_PROJECT_BASEPATH.'/campaign/list-sms-campaign');
            } else {
                $this->_helper->flashMessenger->setNamespace('error')->addMessage('Something wrong with saving SMS campaign. ');
            }
            $this->view->unsavedCampaignData = $campaignData;
        }

    }

    public function listSmsCampaignAction(){

        $this->view->succesMessages = $this->_helper->flashMessenger->getMessages();

        $this->view->title = 'List SMS Campaign';

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $campaignModel = new Pbo_Model_Campaigns();
        $campaigns = $campaignModel->getChapCampaign($chapId,'sms');

        $paginator = Zend_Paginator::factory($campaigns);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));
        $paginator->setItemCountPerPage(10);

        $this->view->campaigns = $paginator;
        unset($paginator);
    }

    public function editSmsCampaignAction(){

        $this->view->title = 'Edit SMS Campaign';

        $id = trim($this->_getParam('id'));
        $campaignModel = new Pbo_Model_Campaigns();
        $rowset = $campaignModel->find($id);
        $campaignRow = $rowset->current();

        $this->view->campaignRow = $campaignRow;
    }

    public function sendSmsAction(){
        $this->view->title = 'Send SMS Campaign';

        $campaignId = trim($this->_getParam('campaignId'));

        $this->view->campaignId = $campaignId;

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        //gets all the chap products
        $chapProductModel = new Pbo_Model_ChapProducts();
        $chapProducts = $chapProductModel->getAllChapProducts($chapId);
        $this->view->chapProducts = $chapProducts;

        //gets all platforms
        $platformModel = new Pbo_Model_Platforms();
        $platforms = $platformModel->getAllPlatformsAsc();
        $this->view->platforms = $platforms;

        //get all categories
        $categoryModel = new Pbo_Model_Categories();
        $categories = $categoryModel->getAllCategories();
        $this->view->categories = $categories;

        //models
        $userModel = new Pbo_Model_User();
        $statisticDownloadsModel = new Pbo_Model_StatisticsDownloads();
        $statisticsProductModel = new Pbo_Model_StatisticsProducts();
        $campaignStatisticsModel = new Pbo_Model_CampaignStatistics();

        $campaignStatsData = array(
            'campaign_id' => $campaignId
        );

        if($this->_request->isPost()) {
            $params = $this->_request->getParams();

            $message = $params['message'];
            $downloadedAppId = $params['downloaded-app'];
            $viewedAppId = $params['viewed-app'];
            $signUpDate = $params['signup-date'];
            $deviceId = $params['device-id'];

            //Send SMS
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);

            $pgType = $pgDetails->gateway_id;

            $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

            switch($params['send-option']){
                case 'all':
                    $users = $userModel->getAllUsersForCampaign($chapId);
                    foreach($users as $user){
                        if($user->mobile_no) {
                            $result = $pgClass->sendSms($user->mobile_no, $message, $chapId);
                            $campaignStatsData['phone_email'] = $user->mobile_no;
                            $campaignStatisticsModel->insert($campaignStatsData);
                        }
                    }
                    break;
                case 'single':
                    $phoneNumber = $params['phone-no'];
                        $result = $pgClass->sendSms($phoneNumber, $message, $chapId);
                        $campaignStatsData['phone_email'] = $phoneNumber;
                        $campaignStatisticsModel->insert($campaignStatsData);
                    break;
                case 'multiple';
                    $phoneNumbers = explode(',',$params['phone-no']);
                    foreach($phoneNumbers as $phoneNumber){
                        $result = $pgClass->sendSms($phoneNumber, $message, $chapId);
                        $campaignStatsData['phone_email'] = $phoneNumber;
                        $campaignStatsData['chap_id'] = $chapId;
                        $campaignStatsData['message'] = $message;
                        
                        $campaignStatisticsModel->insert($campaignStatsData);
                    }
                    break;
                case 'app-downloaded':
                    $users = $statisticDownloadsModel->getDownloadedUsersByApp($chapId,$downloadedAppId);
                    if( count($users) > 0 ){
                        foreach($users as $user){
                            if($user->mobile_no) {
                                $result = $pgClass->sendSms($user->mobile_no, $message, $chapId);
                                $campaignStatsData['phone_email'] = $user->mobile_no;
                                $campaignStatisticsModel->insert($campaignStatsData);
                            }
                        }
                    }
                    break;
                case 'app-viewed':
                    $users = $statisticsProductModel->getViewedUsersByApp($chapId,$viewedAppId);
                    if( count($users) > 0 ){
                        foreach($users as $user){
                            if($user->mobile_no) {
                                $result = $pgClass->sendSms($user->mobile_no, $message, $chapId);
                                $campaignStatsData['phone_email'] = $user->mobile_no;
                                $campaignStatisticsModel->insert($campaignStatsData);
                            }
                        }
                    }
                    break;
                case 'by-device':
                    $users = $statisticDownloadsModel->getDownloadedUserByDevice($chapId,$deviceId);
                    if(count($users)>0){
                        foreach($users as $user){
                            if($user->mobile_no){
                                $result = $pgClass->sendSms($user->mobile_no, $message, $chapId);
                                $campaignStatsData['phone_email'] = $user->mobile_no;
                                $campaignStatisticsModel->insert($campaignStatsData);
                            }
                        }
                    }
                    break;
                case 'by-platform':
                    $users = $userModel->getUserByDownloadedPlatform($chapId,$params['platform']);
                    if( count($users) > 0 ){
                        $userPhoneNos = array();
                        foreach($users as $user){
                            $userPhoneNos[] = $user->mobile_no;
                        }
                        $userPhoneNos = array_unique($userPhoneNos);
                        foreach($userPhoneNos as $userPhoneNo){
                            if($userPhoneNo){
                                $result = $pgClass->sendSms($userPhoneNo, $message, $chapId);
                                $campaignStatsData['phone_email'] = $userPhoneNo;
                                $campaignStatisticsModel->insert($campaignStatsData);
                            }
                        }
                    }
                    break;
                case 'by-category':
                    $users = $userModel->getUserByDownloadedCategory($chapId,$params['category']);
                    if( count($users) > 0 ){
                        $userPhoneNos = array();
                        foreach($users as $user){
                            $userPhoneNos[] = $user->mobile_no;
                        }
                        $userPhoneNos = array_unique($userPhoneNos);
                        foreach($userPhoneNos as $userPhoneNo){
                            if($userPhoneNo){
                                $result = $pgClass->sendSms($userPhoneNo, $message, $chapId);
                                $campaignStatsData['phone_email'] = $userPhoneNo;
                                $campaignStatisticsModel->insert($campaignStatsData);
                            }
                        }
                    }
                    break;
                case 'signup-date':
                    $users = $userModel->getUserBySignUpDate($chapId,$signUpDate);
                    if( count($users) > 0 ){
                        foreach($users as $user){
                            if($user->mobile_no) {
                                $result = $pgClass->sendSms($user->mobile_no, $message, $chapId);
                                $campaignStatsData['phone_email'] = $user->mobile_no;
                                $campaignStatisticsModel->insert($campaignStatsData);
                            }
                        }
                    }
                    break;
            }
        }
    }

    /*public function viewSmsStatsAction(){

        $this->_helper->viewRenderer->setRender('view-stats');

        $campaignId = trim($this->_getParam('id'));
        $campaignModel = new Pbo_Model_Campaigns();
        $rowset = $campaignModel->find($campaignId);
        $campaignRow = $rowset->current();

        $this->view->title = 'View Stats for Campaign - '.$campaignRow->name;

        $campaignStatsModel = new Pbo_Model_CampaignStatistics();
        $campaignStats = $campaignStatsModel->getCampaignStats($campaignId);

        $this->view->campaignStats = $campaignStats;
    }*/

    /**************************************************************Email Campaign Functions *********************************************************/

    public function emailCampaignAction(){

    }

    public function addEmailCampaignAction(){
        $this->view->title = 'Add Email Campaign';
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        if($this->_request->isPost()) {

            $name = trim($this->_getParam('campaign-name'));
            $description = trim($this->_getParam('campaign-description'));
            $campaignId = trim($this->_getParam('campaign-id'));

            $campaignModel = new Pbo_Model_Campaigns();
            $campaignData = array(
                'type' => 'email',
                'name' => $name,
                'description' => $description,
                'chap_id' => $chapId,
                'created_date' => date('Y-m-d H:i:s')
            );

            if($campaignId){
                $lastInsertId = $campaignModel->update($campaignData,array ('id = ?' => $campaignId ));
                $message = 'Email Campaign successfully edited. ';
            } else {
                $lastInsertId = $campaignModel->insert($campaignData);
                $message = ' Email Campaign successfully Added. ';
            }

            if($lastInsertId){
                $this->_helper->flashMessenger->setNamespace('success')->addMessage($message);
                $this->_redirect ('/campaign/list-email-campaign');
            } else {
                $this->_helper->flashMessenger->setNamespace('error')->addMessage('Something wrong with saving Email campaign. ');
            }
            $this->view->unsavedCampaignData = $campaignData;
        }
    }

    public function listEmailCampaignAction(){

        $this->view->succesMessages = $this->_helper->flashMessenger->getMessages();

        $this->view->title = 'List Email Campaign';

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $campaignModel = new Pbo_Model_Campaigns();
        $campaigns = $campaignModel->getChapCampaign($chapId,'email');

        $paginator = Zend_Paginator::factory($campaigns);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));
        $paginator->setItemCountPerPage(10);

        $this->view->campaigns = $paginator;
        unset($paginator);
    }

    public function editEmailCampaignAction(){

        $this->view->title = 'Edit Email Campaign';

        $id = trim($this->_getParam('id'));
        $campaignModel = new Pbo_Model_Campaigns();
        $rowset = $campaignModel->find($id);
        $campaignRow = $rowset->current();

        $this->view->campaignRow = $campaignRow;
    }

    public function sendEmailAction(){
        $this->view->title = 'Send Email Campaign';

        $campaignId = trim($this->_getParam('campaignId'));

        $this->view->campaignId = $campaignId;

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        //gets all the chap products
        $chapProductModel = new Pbo_Model_ChapProducts();
        $chapProducts = $chapProductModel->getAllChapProducts($chapId);
        $this->view->chapProducts = $chapProducts;

        //gets all platforms
        $platformModel = new Pbo_Model_Platforms();
        $platforms = $platformModel->getAllPlatformsAsc();
        $this->view->platforms = $platforms;

        //get all categories
        $categoryModel = new Pbo_Model_Categories();
        $categories = $categoryModel->getAllCategories();
        $this->view->categories = $categories;

        //models
        $userModel = new Pbo_Model_User();
        $statisticDownloadsModel = new Pbo_Model_StatisticsDownloads();
        $statisticsProductModel = new Pbo_Model_StatisticsProducts();
        $campaignStatisticsModel = new Pbo_Model_CampaignStatistics();

        $campaignStatsData = array(
            'campaign_id' => $campaignId
        );

        if($this->_request->isPost()) {
            $params = $this->_request->getParams();

            $message = $params['message'];
            $downloadedAppId = $params['downloaded-app'];
            $viewedAppId = $params['viewed-app'];
            $signUpDate = $params['signup-date'];
            $deviceId = $params['device-id'];

            switch($params['send-option']){
                case 'all':
                    $users = $userModel->getAllUsersForCampaign($chapId);
                    foreach($users as $user){
                        if(!$user->email) continue;
                            $this->sendEmail($user->email,$message);
                            $campaignStatsData['phone_email'] = $user->email;
                            $campaignStatisticsModel->insert($campaignStatsData);
                    }
                    break;
                case 'single':
                    $email = $params['email'];
                        $this->sendEmail($email,$message);
                        $campaignStatsData['phone_email'] = $email;
                        $campaignStatisticsModel->insert($campaignStatsData);
                    break;
                case 'multiple';
                    $emails = explode(',',$params['email']);
                    foreach($emails as $email){
                        $this->sendEmail($email,$message);
                        $campaignStatsData['phone_email'] = $email;
                        $campaignStatisticsModel->insert($campaignStatsData);
                    }
                    break;
                case 'app-downloaded':
                    $users = $statisticDownloadsModel->getDownloadedUsersByApp($chapId,$downloadedAppId);
                    if( count($users) > 0 ){
                        foreach($users as $user){
                            if($user->email) {
                                $this->sendEmail($user->email,$message);
                                $campaignStatsData['phone_email'] = $user->email;
                                $campaignStatisticsModel->insert($campaignStatsData);
                            }
                        }
                    }
                    break;
                case 'app-viewed':
                    $users = $statisticsProductModel->getViewedUsersByApp($chapId,$viewedAppId);
                    if( count($users) > 0 ){
                        foreach($users as $user){
                            if($user->email) {
                                $this->sendEmail($user->email,$message);
                                $campaignStatsData['phone_email'] = $user->email;
                                $campaignStatisticsModel->insert($campaignStatsData);
                            }
                        }
                    }
                    break;
                case 'by-device':
                    $users = $statisticDownloadsModel->getDownloadedUserByDevice($chapId,$deviceId);
                    if(count($users)>0){
                        foreach($users as $user){
                            if($user->email){
                                $this->sendEmail($user->email,$message);
                                $campaignStatsData['phone_email'] = $user->email;
                                $campaignStatisticsModel->insert($campaignStatsData);
                            }
                        }
                    }
                    break;
                case 'by-platform':
                    $users = $userModel->getUserByDownloadedPlatform($chapId,$params['platform']);
                    if( count($users) > 0 ){
                        $userMails = array();
                        foreach($users as $user){
                            $userMails[] = $user->email;
                        }
                        $userMails = array_unique($userMails);
                        foreach($userMails as $userMail){
                            $this->sendEmail($userMail,$message);
                            $campaignStatsData['phone_email'] = $userMail;
                            $campaignStatisticsModel->insert($campaignStatsData);
                        }
                    }
                    break;
                case 'by-category':
                    $users = $userModel->getUserByDownloadedCategory($chapId,$params['category']);
                    if( count($users) > 0 ){
                        $userMails = array();
                        foreach($users as $user){
                            $userMails[] = $user->email;
                        }
                        $userMails = array_unique($userMails);
                        foreach($userMails as $userMail){
                                $this->sendEmail($userMail,$message);
                                $campaignStatsData['phone_email'] = $userMail;
                                $campaignStatisticsModel->insert($campaignStatsData);
                        }
                    }
                    break;
                case 'signup-date':
                    $users = $userModel->getUserBySignUpDate($chapId,$signUpDate);
                    if( count($users) > 0 ){
                        foreach($users as $user){
                            if($user->email) {
                                $this->sendEmail($user->email,$message);
                                $campaignStatsData['phone_email'] = $user->email;
                                $campaignStatisticsModel->insert($campaignStatsData);
                            }
                        }
                    }
                    break;
            }
        }
    }

    public function sendEmail($userEmail,$message){
        $template = 'email-campaign.phtml';
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer ->setSubject('New Updates from neXva appstore');
        $mailer ->addTo($userEmail)
                ->setMailVar("message", $message)
                //->setMailVar("ticketUrl", $ticketUrl)
                //->setMailVar("ticketId", $ticketId)
                ;
        $mailer->setLayout("generic_mail_template");
        $mailer->sendHTMLMail($template);
    }

/**************************************************************Common Campaign Functions *********************************************************/

    public function addCampaignRecord(){

    }

    public function suggestAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $q = $this->_getParam('request');

        $deviceModel = new Pbo_Model_Devices();
        $devices = $deviceModel->searchDevicesByPhrase($q);
        //echo count($devices);die();
        $results = array ();
        foreach($devices as $device){
            $results [] = array ('id' => $device->id, 'value' => $device->brand.' '.$device->model );
        }
        echo json_encode ( $results );
    }

    public function viewStatsAction(){

        $this->_helper->viewRenderer->setRender('view-stats');

        $campaignId = trim($this->_getParam('id'));
        $campaignModel = new Pbo_Model_Campaigns();
        $rowset = $campaignModel->find($campaignId);
        $campaignRow = $rowset->current();

        $this->view->title = 'View Stats for Campaign - '.$campaignRow->name;

        $campaignStatsModel = new Pbo_Model_CampaignStatistics();
        $campaignStats = $campaignStatsModel->getCampaignStats($campaignId);

        if(count($campaignStats)>0){
            $this->view->campaignStats = $campaignStats;
            $this->view->haveRecords = true;
        } else {
            $this->view->haveRecords = false;
        }

    }

}