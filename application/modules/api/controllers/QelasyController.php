<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 5/23/14
 * Time: 1:38 PM
 * To change this template use File | Settings | File Templates.
 */
class Api_QelasyController extends Zend_Controller_Action {

    protected $serverPathThumb = "http://thor.nexva.com/vendors/phpThumb/phpThumb.php?src=";
    protected $serverPath = "http://thor.nexva.com/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/";
    protected $loggerInstance;

    const BAD_REQUEST_CODE = "400";

    public function init(){
        //Disabling the layout and views
        
        //
        $skipActionNamesForm = array('form');
        
        if(!in_array($this->getRequest()->getActionName(), $skipActionNamesForm))
        {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);   
        }

        //$skipActionNames = array('openmobile', 'pay-appp');
        $skipActionNames = array();

        if(!in_array($this->getRequest()->getActionName(), $skipActionNames))
        {
            $this->loggerInstance = new Zend_Log();
            $pathToLogFile = APPLICATION_PATH . "/../logs/api_request." . APPLICATION_ENV . ".log";
            $writer = new Zend_Log_Writer_Stream($pathToLogFile);
            $this->loggerInstance->addWriter($writer);

            $this->validateHeaderParams();

            $headersParams = apache_request_headers();

            $userAgent = $headersParams['User-Agent'];
            $chapId = $headersParams['Chap-Id'];
            $this->loggerInstance->log('Request ::' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . ' :: - Request IP ' .
                $_SERVER['REMOTE_ADDR'] . ' :: - User-Agent - ' . $userAgent . ' :: -Chap-Id-' . $chapId, Zend_Log::INFO);
        }

    }


    protected function uniqueVisits($userAgent, $chapId, $sessionId, $sessionUser){

        $ip = $_SERVER['REMOTE_ADDR'];
        $now = date("Y-m-d H:i:s");

        $uniqueVisitsModel = new Model_UniqueVisits();
        $isUnique = $uniqueVisitsModel->isUnique($ip, $sessionId);      //check whether this is a Unique visit

        $isExpired = FALSE;

        if($sessionUser->visitTime){     //if already visited, we calculate visited time duration

            //$visitDetails = $uniqueVisitsModel->lastUpdatedTime($ip, $sessionId, $sessionUser->visitTime);

            $visitTime = new DateTime($sessionUser->visitTime);
            $updatedTime = new DateTime($now);
            $interval = $visitTime->diff($updatedTime);

            if($interval->h){       //if visited time duration is more than one hour, we insert a new record
                $isExpired = TRUE;
            }

        }

        if($isUnique || $isExpired){        //if this is a unique visit or current visit expired, we insert a new record

            $sessionUser->visitTime = $now;

            $data = array(
                'ip' => $ip,
                'visit_time' => $now,
                'updated_time' => $now,
                'chap_id' => $chapId,
                'session_id' => $sessionId,
                'source' => 'API',
                'user_agent'=> $userAgent
            );

            $uniqueVisitsModel->insert($data);

        }

        // removed this part, we don't need to run a SQL query for every single click

        /*else {    //if this is not a unique visit we update the current record

            $data = array(
                'updated_time' => $now
            );

            $uniqueVisitsModel->update($data,array('session_id = ?' => $sessionId, 'ip = ?' => $ip, 'visit_time =?' => $sessionUser->visitTime));
        }*/

    }


    private function validateHeaderParams() {

        //Get all HTTP request headers, this is an associative array
        $headersParams = apache_request_headers();

        //We need only User-Agent, Chap-Id
        //$userAgent = !empty($headersParams['User-Agent']) ? $headersParams['User-Agent'] : '';
        //$chapId = !empty($headersParams['Chap-Id']) ? $headersParams['Chap-Id'] : '';
        
        $userAgent = !empty($headersParams['USER-AGENT']) ? $headersParams['USER-AGENT'] : '';
        $chapId = !empty($headersParams['CHAP-ID']) ? $headersParams['CHAP-ID'] : '';
        $langCode = !empty($headersParams['langCode']) ? $headersParams['langCode'] : 'en';

        //Get user language ocde
        //$modelUserLanguages = new Model_LanguageUsers;
        //$langCode = $modelUserLanguages->getLanguageCodeByChap($chapId);

        //Check if User-Agent has been provided
        if ($userAgent === null || empty($userAgent)) {

            $this->__echoError("0000", $this->getTranslatedText($langCode, '0000') ? $this->getTranslatedText($langCode, '0000') : 'User Agent not provided', self::BAD_REQUEST_CODE);
        }

        //Check if Chap Id has been provided
        if ($chapId === null || empty($chapId)) {

            $this->__echoError("1000", $this->getTranslatedText($langCode, '1000') ? $this->getTranslatedText($langCode, '1000') : "Chap Id not found", self::BAD_REQUEST_CODE);
        }

        $rtArray['userAgent'] = $userAgent;
        $rtArray['chapId'] = $chapId;
        $rtArray['langCode'] = $langCode;

        return $rtArray;
    }

    private function __echoError($errNo, $errMsg, $httpResponseCode)
    {
        $msg = json_encode(array("message" =>$errMsg,"error_code" => $errNo));

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->loggerInstance->log('Response ::' . $msg, Zend_Log::INFO);

        echo $msg;
        exit;
    }

    private function __printArrayOfDataJson($apps)
    {
        $apps = str_replace('\/', '/', json_encode($apps));
        $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
        echo $apps;
        exit;
    }

    /**
     *
     * Returns valid user id based on valid token (session id)
     *
     * @param $token session id
     * returns userId or error code
     */
    private function __validateToken($token) {

        $headersParams = $this->validateHeaderParams();
        $langCode = $headersParams['langCode'];

        if (!$token) {

            $this->__echoError("9000", $this->getTranslatedText($langCode, '9000') ? $this->getTranslatedText($langCode, '9000') : "Request not authenticated", self::BAD_REQUEST_CODE);
        }

        Zend_Session::setId($token);
        $sessionUser = new Zend_Session_Namespace('partner_user');

        if (!($sessionUser->id))
        {

            $this->__echoError("9000", $this->getTranslatedText($langCode, '9000') ? $this->getTranslatedText($langCode, '9000') : "Request not authenticated", self::BAD_REQUEST_CODE);
        }

        return $sessionUser->id;
    }

    /**
     *
     * print error code data not found
     *
     */
    private function __dataNotFound() {
        $headersParams = $this->validateHeaderParams();
        $langCode = $headersParams['langCode'];

        $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
        exit;
    }

    protected function getTranslatedText($languageCode, $errorCode, $paramOptional = null)
    {
        $translationArr = array();

        /*if(!empty($languageCode) && !is_null($languageCode) && $languageCode != 'en')
        {
            try {
                if (!file_exists(APPLICATION_PATH.'/../public/api/'.$languageCode.'.php' )) {
                    throw new Exception ('Language file for this Language, Doesn\'t exist');
                } else {
                    require_once(APPLICATION_PATH.'/../public/api/'.$languageCode.'.php');
                    return $translationArr[$errorCode];
                }

            } catch (exception $e){
                return $e->getMessage();
            }
        } else {
            return false;
        }*/

        //French translations
        $translationArr['fr'] = array(
            '0000' => 'User Agent not provided - French',
            '1000' => 'User Agent not provided - French',
            '1001' => 'Identifiant de l\'application non trouvé',
            '1002' => 'Build Id not found - French',
            '1003' => 'App does not belong to this partner - French',
            '1004' => 'Limite maximum de téléchargements autorisés atteinte',

            '2000' => 'Mobile non trouvé',
            '2001' => 'Adresse e-mail invalide',
            '2002' => 'Votre mot de passe doit avoir au moins 6 caractères',
            '2003' => 'Le champ Prénom est vide',
            '2004' => 'Le champ Nom est vide',
            '2005' => 'Numéro de téléphone invalide',
            '2006' => 'Cette adresse e-mail existe déjà',
            '2007' => 'Ce numéro de téléphone existe déjà. Veuillez entrer un autre',
            '2222' => 'Utilisateur vérifié avec succès',

            '3000' => 'Informations non trouvées',
            '3001' => 'Cette application a été supprimée ou n\'existe pas',

            '4000' => 'Payment was unsuccessfull - French',

            '5000' => 'Numéro de mobile non trouvé',

            '6000' => 'Catégorie non trouvée',

            '7000' => 'Le prix est invalide',
            '7001' => 'Demande de téléchargement invalide',

            '8000' => 'La validation des informations a échoué',
            '8001' => 'Identifiant utilisateur non trouvé',
            '8002' => 'Vérification du code non trouvée',
            '8003' => 'Les champs ne concordent pas',
            '8004' => 'La vérification a déjà été faite',
            '8005' => 'Le champ Nom d\'utilisateur est vide',
            '8006' => 'Le champ mot de passe est vide',
            '8007' => 'Nom d\'utilisateur ou mot de passe invalide',
            '8008' => 'L\'utilisateur n\'a pas vérifié le compte',
            '8009' => 'Nom d\'utilisateur invalide',
            '8010' => 'Numéro de téléphone invalide',
            '8011' => 'Numéro de téléphone non enregistré',
            '8012' => 'Mot de passe invalide',

            '9000' => 'Demande non authentifiée',

            '10000' => 'Veuillez utiliser le code de verification '.$paramOptional.' pour terminer votre enregistrement.',
            '10001' => 'Veuillez utiliser le code de verification '.$paramOptional.' pour verifier votre numero de telephone.',
            '10002' => 'Veuillez utiliser le code de verification '.$paramOptional.' pour terminer la réinitialisation de votre mot de passe.',
            '10003' => 'Vous vous etes enregistre avec succes.',

            '11000' => 'Grade Id Empty - French',

        );

//Farsi(Persian language) translations
        $translationArr['fa'] = array(
            '0000' => 'مرورگر وارد نشده است',
            '1000' => 'شناسه پروتکل تایید یافت نشد',
            '1001' => 'شناسه برنامه کاربردی یافت نشد',
            '1002' => 'شناسه ساخت یافت نشد',
            '1003' => 'برنامه به این شریک تعلق ندارد',
            '1004' => 'حداكثر دانلود مجاز انجام شده است',

            '2000' => 'دستگاه مورد نظر یافت نشد',
            '2001' => 'ایمیل نامعتبر است',
            '2002' => 'رمز عبورتان باید حداقل 6 کاراکتر داشته باشد',
            '2003' => 'نام وارد نشده است',
            '2004' => 'نام خانوادگی وارد نشده است',
            '2005' => 'شماره تلفن همراه نامعتبر است',
            '2006' => 'ایمیل از قبل وجود دارد',
            '2007' => 'این شماره تلفن از قبل وارد شده است. لطفاً شماره دیگری را وارد نمایید',
            '2222' => 'کاربر باموفقیت تأیید شد',

            '3000' => 'داده یافت نشد',
            '3001' => 'این برنامه حذف شده یا وجود ندارد',

            '4000' => 'پرداخت با موفقیت انجام نشده است',

            '5000' => 'شماره تلفن همراه یافت نشد',

            '6000' => 'دسته‌بندی یافت نشد',

            '7000' => 'مبلغ معتبر نیست',
            '7001' => 'درخواست دانلود نامعتبر است',

            '8000' => 'عدم موفقیت تأیید اعتبار داده‌ها',
            '8001' => 'شناسه کاربر یافت نشد',
            '8002' => 'کد تایید اعتبار یافت نشد',
            '8003' => 'ترکیب مطابقت ندارد',
            '8004' => 'تایید اعتبار قبلا انجام شده است',
            '8005' => 'نام کاربری وارد نشده است',
            '8006' => 'رمز عبور وارد نشده است',
            '8007' => 'نام کاربری یا رمز عبور نامعتبر است',
            '8008' => 'کاربر حساب را تایید نکرده است',
            '8009' => 'نام کاربری نامعتبر است',
            '8010' => 'شماره تلفن همراه نامعتبر است',
            '8011' => 'شماره تلفن همراه ثبت نشده است',
            '8012' => 'رمز عبور نامعتبر است',

            '9000' => 'صحت درخواست تایید نشده است',

            '10000' => 'لطفاً برای تکمیل ثبت نام، از کد تایید '.$paramOptional.' استفاده کنید',
            '10001' => 'لطفاً برای تایید شماره تلفن همراه، از کد تایید '.$paramOptional.' استفاده کنید',
            '10002' => 'لطفاً برای تکمیل فرایند تنظیم مجدد رمز عبور، از کد تأیید '.$paramOptional.' استفاده کنید',
            '10003' => 'ثبت‌نام با موفقیت انجام شد',

            '11000' => 'Grade Id Empty - Farsi',
        );

        if(!empty($languageCode) && !is_null($languageCode) && $languageCode != 'en')
        {
            return $translationArr[$languageCode][$errorCode];
        }
        else
        {
            return false;
        }
    }

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

    public function indexAction(){
        //$headersParams = $this->validateHeaderParams();
        $languageCode = 'fa';
        $errorCode = '11000';
        $errMsg = 'Hi';
        $this->__echoError($errorCode,$errMsg,self::BAD_REQUEST_CODE);
    }

    public function appConfigurationsAction()
    {
        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        //Get the parameters
        $version = (int)(trim($this->_getParam('version')));
        $firstName = trim($this->_getParam('firstName'));
        $lastName = trim($this->_getParam('lastName'));
        $grade = trim($this->_getParam('grade'));
        $userType = trim($this->_getParam('user_type'));
        $email = trim($this->_getParam('parentEmail'));
        $mobile = trim($this->_getParam('parentMobile'));

        //check if version has been provided
        if($version === null || empty($version)){
            $this->__echoError("1005",$this->getTranslatedText($langCode, '1005') ? $this->getTranslatedText($langCode, '1005') : "App Version Number is Empty.", self::BAD_REQUEST_CODE);
        }

        //check if grade has been provided
        /*if($grade === null || empty($grade)){
            $this->__echoError("11000",$this->getTranslatedText($langCode, '11000') ? $this->getTranslatedText($langCode, '11000') : "Grade Id Empty.", self::BAD_REQUEST_CODE);
        }*/

        $user = new Api_Model_Users();

        if(($email != null) && (!empty($email))){

            $userInfo = $user->getUserByEmail($email);

        } /*else if(($mobile != null) && (!empty($mobile))){

            $userInfo = $user->getUserByMobileNo($mobile);

        }*/

        if(($mobile != null) && (!empty($mobile)) && !$userInfo){
            $userInfo = $user->getUserByMobileNo($mobile);
        }

        if($userInfo){
            $sessionUser = new Zend_Session_Namespace('partner_user');
            $sessionUser->id = $userInfo->id;
            $sessionId = Zend_Session::getId();

            $userId = $userInfo->id;
            $token = $sessionId;

        } else {

            $activationCode = substr(md5(uniqid(rand(), true)), 5, 8);

            $userData = array(
                'password' => 'qelasy123',
                'type' => "USER",
                'login_type' => "NEXVA",
                'chap_id' => $chapId,
                'mobile_no' => $mobile,
                'activation_code' => $activationCode,
                'status' => 1
            );

            if(($email == null) && (empty($email))){
                $userData['username'] = $activationCode.'@qelasy.com';
                $userData['email'] = $activationCode.'@qelasy.com';
            } else {
                $userData['username'] = $email;
                $userData['email'] = $email;
            }

            $userId = $user->createUser($userData);

            $userMeta = new Model_UserMeta();
            $userMeta->setEntityId($userId);
            $userMeta->FIRST_NAME = $firstName;
            $userMeta->LAST_NAME = $lastName;
            $userMeta->TELEPHONE = $mobile;
            $userMeta->UNCLAIMED_ACCOUNT = '1';

            $sessionUser = new Zend_Session_Namespace('partner_user');
            $sessionUser->id = $userId;
            $sessionId = Zend_Session::getId();

            $token = $sessionId;
        }

        //Get currency details
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);

        //Load them meta model
        $themeMeta   = new Model_ThemeMeta();
        $themeMeta->setEntityId($chapId);

        $allConstants['keywords'] = $themeMeta->WHITELABLE_SITE_ADVERTISING;
        $allConstants['add_interval'] =  $themeMeta->WHITELABLE_SITE_INTERVAL;

        if($version < (int)$themeMeta->WHITELABLE_SITE_APPSTORE_VERSION) {
            $allConstants['appstore_latest_version'] = $themeMeta->WHITELABLE_SITE_APPSTORE_VERSION;
            //$allConstants['latest_build_url'] = $themeMeta->WHITELABLE_SITE_APPSTORE_URL;

            //Get the S3 URL of the Relevant build
            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = null;

            if(($themeMeta->WHITELABLE_SITE_APPSTORE_APP_ID) && ($themeMeta->WHITELABLE_SITE_APPSTORE_BUILD_ID)){
                $buildUrl = $productDownloadCls->getBuildFileUrl($themeMeta->WHITELABLE_SITE_APPSTORE_APP_ID, $themeMeta->WHITELABLE_SITE_APPSTORE_BUILD_ID);
            }

            $allConstants['latest_build_url'] = $buildUrl;
        }

        $allConstants['user_id'] = $userId;
        $allConstants['token'] = $token;

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

        //Validate Header params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        //Get the parameters
        $category = trim($this->_getParam('category'));
        $offset = trim($this->_getParam('offset', 0));
        $limit = trim($this->_getParam('limit', 40));
        $grade = trim($this->_getParam('grade'));
        $userType = trim($this->_getParam('user_type'));

        //Check if Category has been provided
        /*if ($category === null || empty($category)) {
            $this->__echoError("6000", $this->getTranslatedText($langCode, '6000') ? $this->getTranslatedText($langCode, '6000') : "Category not found", self::BAD_REQUEST_CODE);
        }*/

        //check if grade has been provided
        /*if($grade === null || empty($grade)){
            $this->__echoError("11000",$this->getTranslatedText($langCode, '11000') ? $this->getTranslatedText($langCode, '11000') : "Grade Id Empty", self::BAD_REQUEST_CODE);
        }*/

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) {

            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
        }
        else
        {
            //Get the Apps based on Chap and the Device
            $ApiModel = new Nexva_Api_QelasyApi();

            //Get category wise applications
            $allApps = $ApiModel->allAppsByChapAction($chapId, $deviceId, $category, $grade, $limit, $offset, $userType);

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
                $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
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
        $langCode = $headersParams['langCode'];

        //Get the parameters
        $category = trim($this->_getParam('category', null));
        $grade = trim($this->_getParam('grade'));
        $userType = trim($this->_getParam('user_type'));

        //Thumbnail Dimension
        $thumbWidth = $this->_getParam('twidth', 80);
        $thumbHeight = $this->_getParam('theight', 80);

        //check if grade has been provided
        /*if($grade === null || empty($grade)){

            $this->__echoError("11000",$this->getTranslatedText($langCode, '11000') ? $this->getTranslatedText($langCode, '11000') : "Grade Id Empty", self::BAD_REQUEST_CODE);
        }*/

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);
        //Zend_Debug::dump($deviceId);die();

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) {

            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);

        } else {
            //Get Featured Apps based on Chap and the Device
            $ApiModel = new Nexva_Api_QelasyApi();

            $apiCall = true;

            //Featured apps for banners
            $featuredApps = $ApiModel->featuredAppsAction($chapId, 15, $deviceId, $apiCall, $category, $grade, $thumbWidth, $thumbHeight, $userType);

            //change the thumbnail path
            if (count($featuredApps) > 0) {

                $apps = str_replace('\/', '/', json_encode($featuredApps));
                $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                echo $apps;

            }
            else{

                $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
            }
        }
    }

    /**
     * Identical to the api functionality
     */
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
        $langCode = $headersParams['langCode'];

        //App Id
        $appId = $this->_getParam('appId', null);
        $userId = $this->_getParam('userId', null);
        //$category = trim($this->_getParam('category', null));
        //$grade = trim($this->_getParam('grade'));

        $screenWidth = $this->_getParam('width', 320);
        $screenHeight = $this->_getParam('height', 480);

        //Thumbnail Dimension
        $thumbWidth = $this->_getParam('twidth', 80);
        $thumbHeight = $this->_getParam('theight', 80);

        //Get language id of chap
        $languageUsersModel = new Partner_Model_LanguageUsers();
        $chapLangId = $languageUsersModel->getLanguageIdByChap($chapId);

        //Check if App Id has been provided
        if ($appId === null || empty($appId)) {

            $this->__echoError("1001", $this->getTranslatedText($langCode, '1001') ? $this->getTranslatedText($langCode, '1001') : "App Id not found", self::BAD_REQUEST_CODE);
        }

        //check if grade has been provided
        /*if($grade === null || empty($grade)){

            $this->__echoError("11000",$this->getTranslatedText($langCode, '11000') ? $this->getTranslatedText($langCode, '11000') : "Grade Id Empty", self::BAD_REQUEST_CODE);
        }*/
        /************************************
        //ToDO
        //Check if the app compatible with the device
         **************************************/

        //Instantiate the Api_Model_ChapProducts model
        $chapProductModel = new Api_Model_ChapProducts();

        //check if the app belongs to the CHAP
        $appCount = $chapProductModel->getProductCountByChap($chapId, $appId);

        if ($appCount == 0) {

            $this->__echoError("1003", $this->getTranslatedText($langCode, '1003') ? $this->getTranslatedText($langCode, '1003') : "App does not belong to this partner", self::BAD_REQUEST_CODE);
        }

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) {

            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);

        }
        else
        {
            $ApiModel = new Nexva_Api_QelasyApi();
            //$ApiModel = new Nexva_Api_NexApi();
            //Get the details of the app
            $appDetails = $ApiModel->appDetailsById($appId, $deviceId, $screenWidth, $screenHeight, $chapLangId, $thumbWidth, $thumbHeight, $chapId, $userId);

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
                $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
            }
        }
    }


    /**
     * Identical to the api functionality
     */
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
        $langCode = $headersParams['langCode'];

        $appId = $this->_getParam('appId');
        $buildId = $this->_getParam('build_Id');

        /************************************
        //ToDO
        //Check if the app compatible with the device
         ****************************** */

        //Check if App Id has been provided
        if ($appId === null || empty($appId))
        {
            $this->__echoError("1001", $this->getTranslatedText($langCode, '1001') ? $this->getTranslatedText($langCode, '1001') : "App Id not found", self::BAD_REQUEST_CODE);
        }
        //Check if Chap Id has been provided
        if ($buildId === null || empty($buildId))
        {
            $this->__echoError("1002", $this->getTranslatedText($langCode, '1002') ? $this->getTranslatedText($langCode, '1002') : "Build Id not found", self::BAD_REQUEST_CODE);
        }

        //Check if the app belongs to the CHAP
        $chapProductModel = new Api_Model_ChapProducts();

        $appCount = $chapProductModel->getProductCountByChap($chapId, $appId);

        if ($appCount == 0) {

            $this->__echoError("1003", $this->getTranslatedText($langCode, '1003') ? $this->getTranslatedText($langCode, '1003') : "App does not belong to this partner", self::BAD_REQUEST_CODE);
        }

        /*         * **************************************************************** */

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) {

            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);

        } else {

            //get the app details
            $productModel = new Api_Model_Products();
            $appDetails = $productModel->getProductDetailsbyId($appId);

            if ($appDetails->price > 0) {

                $this->__echoError("7001", $this->getTranslatedText($langCode, '7001') ? $this->getTranslatedText($langCode, '7001') : "Invalid download request", self::BAD_REQUEST_CODE);
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

    public function payAppAction() {

        $sessionId = $this->_getParam('token', 0);

        $userId = $this->__validateToken($sessionId);

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $appId = $this->_getParam('appId');
        $buildId = $this->_getParam('build_Id');
        $mobileNo = $this->_getParam('mdn');
        $paymentGateway = $this->_getParam('paymentGateway');

        //Check if App Id has been provided
        if ($appId === null || empty($appId)) {

            $this->__echoError("1001", $this->getTranslatedText($langCode, '1001') ? $this->getTranslatedText($langCode, '1001') : "App Id not found", self::BAD_REQUEST_CODE);
        }
        //Check if Chap Id has been provided
        if ($buildId === null || empty($buildId))
        {
            $this->__echoError("1002", $this->getTranslatedText($langCode, '1002') ? $this->getTranslatedText($langCode, '1002') : "Build Id not found", self::BAD_REQUEST_CODE);
        }
        //Check if Chap Id has been provided
        if ($mobileNo === null || empty($mobileNo))
        {
            $this->__echoError("5000", $this->getTranslatedText($langCode, '5000') ? $this->getTranslatedText($langCode, '5000') : "Mobile Number not found", self::BAD_REQUEST_CODE);
        }

        //Check if the app belongs to the CHAP
        $chapProductModel = new Api_Model_ChapProducts();
        $appCount = $chapProductModel->getProductCountByChap($chapId, $appId);

        if ($appCount == 0) {

            $this->__echoError("1003", $this->getTranslatedText($langCode, '1003') ? $this->getTranslatedText($langCode, '1003') : "App does not belong to this partner", self::BAD_REQUEST_CODE);
        }

        /********************************************************************* */

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId))
        {
            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
        }
        else
        {
            $price = 0;
            $appName = "";

            //get the app details
            $productModel = new Api_Model_Products();
            $appDetails = $productModel->getProductDetailsbyId($appId);

            //Check if app details available
            if (is_null($appDetails))
            {
                $this->__echoError("3001", $this->getTranslatedText($langCode, '3001') ? $this->getTranslatedText($langCode, '3001') : "This app has been removed or does not exist", self::BAD_REQUEST_CODE);
            }

            //Check if the app is a premium one
            if ($appDetails->price <= 0)
            {
                $this->__echoError("7000", $this->getTranslatedText($langCode, '7000') ? $this->getTranslatedText($langCode, '7000') : "Price is invalid", self::BAD_REQUEST_CODE);
            }
            else
            {
                $price = $appDetails->price;
                $appName = $appDetails->name;
            }

            /*************************** Begin Payment Process *******************************************/

            //Get payment gateway Id of the CHAP

            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
            //Zend_Debug::dump($pgDetails->gateway_id);die();

            $pgType = $pgDetails->gateway_id;
            $paymentGatewayId = $pgDetails->payment_gateway_id;

            //Call Nexva_MobileBilling_Factory and create relevant instance
            $pgClass = Nexva_MobileBilling_Factory::createFactory($paymentGateway);

            /*if($chapId == '80184'){
                //Call Nexva_MobileBilling_Factory and create relevant instance
                $pgClass = Nexva_MobileBilling_Factory::createFactory($paymentGateway);
            } else {
                //Call Nexva_MobileBilling_Factory and create relevant instance
                $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
            }*/

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
                $this->__echoError("4000", $this->getTranslatedText($langCode, '4000') ? $this->getTranslatedText($langCode, '4000') : "Payment was unsuccessfull", self::BAD_REQUEST_CODE);
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
        $langCode = $headersParams['langCode'];

        $appId = $this->_getParam('appId');
        $buildId = $this->_getParam('build_Id');
        //$mobileNo = $this->_getParam('mdn');

        //Check if App Id has been provided
        if ($appId === null || empty($appId))
        {
            $this->__echoError("1001", $this->getTranslatedText($langCode, '1001') ? $this->getTranslatedText($langCode, '1001') : "App Id not found", self::BAD_REQUEST_CODE);
        }
        //Check if Chap Id has been provided
        if ($buildId === null || empty($buildId))
        {
            $this->__echoError("1002", $this->getTranslatedText($langCode, '1002') ? $this->getTranslatedText($langCode, '1002') : "Build Id not found", self::BAD_REQUEST_CODE);
        }
        //Check if Chap Id has been provided
        /*if ($mobileNo === null || empty($mobileNo))
        {
            $this->__echoError("5000", $this->getTranslatedText($langCode, '5000') ? $this->getTranslatedText($langCode, '5000') : "Mobile Number not found", self::BAD_REQUEST_CODE);
        }*/

        //Check if the app belongs to the CHAP
        $chapProductModel = new Api_Model_ChapProducts();
        $appCount = $chapProductModel->getProductCountByChap($chapId, $appId);

        if ($appCount == 0)
        {
            $this->__echoError("1003", $this->getTranslatedText($langCode, '1003') ? $this->getTranslatedText($langCode, '1003') : "App does not belong to this partner", self::BAD_REQUEST_CODE);
        }

        /******************************************************************* */

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId))
        {
            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
        }
        else
        {
            //get the app details
            $productModel = new Api_Model_Products();
            $appDetails = $productModel->getProductDetailsbyId($appId);

            //Check if app details available
            if (is_null($appDetails))
            {
                $this->__echoError("3001", $this->getTranslatedText($langCode, '3001') ? $this->getTranslatedText($langCode, '3001') : "This app has been removed or does not exist", self::BAD_REQUEST_CODE);
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
                $this->__echoError("1004", $this->getTranslatedText($langCode, '1004') ? $this->getTranslatedText($langCode, '1004') : "Maximum allowed download limit reached", self::BAD_REQUEST_CODE);
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
    public function categoryAction()
    {
        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        //$langCode = $headersParams['langCode'];
        //$chapLanguageId = $headersParams['langCode'];

        $grade = $this->_getParam('grade');
        $userType = $this->_getParam('user_type');
        $langCode = $this->_getParam('language');

        //check if grade has been provided
        if($langCode === null || empty($langCode)){
            $this->__echoError("1006","Language code is Empty", self::BAD_REQUEST_CODE);
        }

        //check if grade has been provided
        /*if($grade === null || empty($grade)){

            $this->__echoError("11000",$this->getTranslatedText($langCode, '11000') ? $this->getTranslatedText($langCode, '11000') : "Grade Id Empty", self::BAD_REQUEST_CODE);
        }*/

        $languageModel = new Api_Model_Languages();
        $langId = $languageModel->fetchRow($languageModel->select()->where('code = ?',$langCode));

        $ApiModel = new Nexva_Api_QelasyApi();
        $allCategories = $ApiModel->categoryAction($chapId, $langId->id, $grade);

        if (count($allCategories) > 0)
        {
            $this->getResponse()
                ->setHeader('Content-type', 'application/json');

            echo str_replace('\/','/',json_encode($allCategories));
            $this->loggerInstance->log('Response ::' . json_encode($allCategories),Zend_Log::INFO);

        }
        else
        {
            $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
        }
    }

    /**
     * User Registration
     * @param firstName
     * @param lastName
     * @param mobileNumber
     * @param email
     * @param password
     * returns activation code,user id, success_code as a JSON response
     * @ToDo : yet discuss about registration process for qelasy, as this is not doing from nexva side, at the moment registration process is doing by qelasy from their side & sends the user details through the API
     */
    public function registerAction() {

        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $firstName = $this->_request->firstName;
        $lastName = $this->_request->lastName;
        $mobileNumber = $this->_request->mobileNumber;
        $email = $this->_request->email;
        $password = $this->_request->password;

        // Input field validation.
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email';

            $this->__echoError("2001", $this->getTranslatedText($langCode, '2001') ? $this->getTranslatedText($langCode, '2001') : "Invalid email.", self::BAD_REQUEST_CODE);
        }

        if (strlen($password) < 6) {
            $errors[] = 'Your password must be at least 6 characters long';

            $this->__echoError("2002", $this->getTranslatedText($langCode, '2002') ? $this->getTranslatedText($langCode, '2002') : "Your password must be at least 6 characters long.", self::BAD_REQUEST_CODE);

        }
        if ($firstName == '') {
            $errors[] = 'Empty first name';

            $this->__echoError("2003", $this->getTranslatedText($langCode, '2003') ? $this->getTranslatedText($langCode, '2003') : "Empty first name.", self::BAD_REQUEST_CODE);
        }
        if ($lastName == '')
        {
            $errors[] = 'Empty last name';

            $this->__echoError("2004", $this->getTranslatedText($langCode, '2004') ? $this->getTranslatedText($langCode, '2004') : "Empty last name.", self::BAD_REQUEST_CODE);
        }
        if ($mobileNumber == '' || (strlen($mobileNumber) < 10))
        {
            $errors[] = 'Invalid mobile number';

            $this->__echoError("2005", $this->getTranslatedText($langCode, '2005') ? $this->getTranslatedText($langCode, '2005') : "Invalid mobile number.", self::BAD_REQUEST_CODE);
        }

        $user = new Api_Model_Users();

        if (!$user->validateUserEmail($email))
        {
            $errors[] = "This email already exists.";

            $this->__echoError("2006", $this->getTranslatedText($langCode, '2006') ? $this->getTranslatedText($langCode, '2006') : "This email already exists.", self::BAD_REQUEST_CODE);
        }


        if (!$user->validateUserMdn($mobileNumber))
        {
            $errors[] = "This mobile number already exists. Please use another number.";

            $this->__echoError("2007", $this->getTranslatedText($langCode, '2007') ? $this->getTranslatedText($langCode, '2007') : "This mobile number already exists. Please use another number.", self::BAD_REQUEST_CODE);
        }


        // When errors generated send out error response and terminate.
        if (!empty($errors))
        {
            $temp = array("message" => $this->getTranslatedText($langCode, '8000') ? $this->getTranslatedText($langCode, '8000') : "Data validation failed",
                "error_details" => $errors);

            $this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
        }

        //Generate activation code
        $activationCode = substr(md5(uniqid(rand(), true)), 5,8);

        // send sms start
        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);

        $pgType = $pgDetails->gateway_id;

        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
        //echo($pgClass);die();
        //$message = 'Please use this verification code '.$activationCode.' to complete your registration.';
        $message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.' to complete your registration.';

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
            'status' => 0,
            'activation_code' => $activationCode
        );

        $userId = $user->createUser($userData);

        $userMeta = new Model_UserMeta();
        $userMeta->setEntityId($userId);
        $userMeta->FIRST_NAME = $firstName;
        $userMeta->LAST_NAME = $lastName;
        $userMeta->TELEPHONE = $mobileNumber;
        $userMeta->UNCLAIMED_ACCOUNT = '0';

        $response = array(
            'user' => $userId,
            'activation_code' => $activationCode,
            'success_code' => '1111'
        );

        $this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);

        echo json_encode($response);
    }

    /**
     * @Todo : similar to registerAction
     */
    public function signinAction()
    {

        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $password = $this->_request->password;
        $username = $this->_request->username;

        if ($username == '')
        {
            $this->__echoError("8005", $this->getTranslatedText($langCode, '8005') ? $this->getTranslatedText($langCode, '8005') : "Emptry username", self::BAD_REQUEST_CODE);
        }
        if ($password == '')
        {
            $this->__echoError("8006", $this->getTranslatedText($langCode, '8006') ? $this->getTranslatedText($langCode, '8006') : "Empty password", self::BAD_REQUEST_CODE);
        }

        if (empty($errors))
        {
            $userObj = new Api_Model_Users();
            $tmpUser = $userObj->getUserByEmail($username);

            if (is_null($tmpUser))
            {
                $this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid username or password", self::BAD_REQUEST_CODE);
            }
            if (((empty($errors))) && ($tmpUser->password != md5($password)))
            {
                $this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid username or password", self::BAD_REQUEST_CODE);
            }
            if($tmpUser->status != 1)
            {

                $msg = json_encode(
                    array(
                        "message" => $this->getTranslatedText($langCode, '8008') ? $this->getTranslatedText($langCode, '8008') : "User has not verified the account",
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
                $this->__echoError("8009", $this->getTranslatedText($langCode, '8009') ? $this->getTranslatedText($langCode, '8009') : "Invalid username", self::BAD_REQUEST_CODE);
            }
        }
        // When errors generated, send out error response and terminate.
        if (!empty($errors))
        {
            $temp = array("message" => $this->getTranslatedText($langCode, '8000') ? $this->getTranslatedText($langCode, '8000') : "Data validation failed",
                "error_details" => $errors);

            $this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
        }

        $sessionUser = new Zend_Session_Namespace('partner_user');
        $sessionUser->id = $tmpUser->id;
        $sessionId = Zend_Session::getId();

        //determine unique visits
        $userAgent = $headersParams['userAgent'];
        //$this->uniqueVisits($userAgent, $chapId, $sessionId, $sessionUser);

        $response = array(
            'user' => $tmpUser->id,
            'token' => $sessionId,
            'mobile_no' => $tmpUser->mobile_no
        );

        $this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
        echo json_encode($response);
    }

    public function listUpdatesAction() {

        $userId = $this->__validateToken($this->_getParam('token', 0));
        //$userId = 105068;

        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $limit = $this->_getParam('limit', 10);
        $offset = $this->_getParam('offset', 0);

        // retrive all the downloaded apps
        $modelDownloadStats = new Api_Model_StatisticsDownloads();
        $model_ProductBuild = new Model_ProductBuild();

        //$nexApi = new Nexva_Api_NexApi();
        $nexApi = new Nexva_Api_QelasyApi();

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

        } else
        {
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
        //$userId = 105068;

        $limit = $this->_getParam('limit', 10);
        $offset = $this->_getParam('offset', 0);

        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $deviceId = $this->deviceAction($userAgent);

        $modelQueue = new Partner_Model_Queue();
        $productsToDownload = $modelQueue->listDownloadQueue($userId, $chapId, $limit, $offset);

        if ($productsToDownload) {

            //$nexApi = new Nexva_Api_NexApi();
            $nexApi = new Nexva_Api_QelasyApi();
            $productInfo = $nexApi->getProductDetails($productsToDownload, $deviceId, true);

            if (count($productInfo) > 0) {

                $this->__printArrayOfDataJson($productInfo);
            }
        } else {

            $this->__dataNotFound();
        }
    }

    public function myAppsAction() {

        $userId = $this->__validateToken($this->_getParam('token', 0));
        //$userId = 105068;

        $limit = $this->_getParam('limit', 10);
        $offset = $this->_getParam('offset', 0);

        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        // retrive all the downloaded apps
        $modelDownloadStats = new Api_Model_StatisticsDownloads();
        $model_ProductBuild = new Model_ProductBuild();

        $downloadedProducts = $modelDownloadStats->getDownloadedBuilds($userId, $limit, $offset);

        $deviceId = $this->deviceAction($userAgent);

        //$nexApi = new Nexva_Api_NexApi();
        $nexApi = new Nexva_Api_QelasyApi();

        $downloadedProductInfo = $nexApi->getProductDetails($downloadedProducts, $deviceId, true);

        if ($downloadedProductInfo) {

            if (count($downloadedProductInfo) > 0) {

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
        $grade = $this->_getParam('grade');
        $userType = $this->_getParam('user_type');

        if ($categoryId == 0) {
            $this->__dataNotFound();
        }

        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        //check if grade has been provided
        /*if($grade === null || empty($grade)){
            $this->__echoError("11000",$this->getTranslatedText($langCode, '11000') ? $this->getTranslatedText($langCode, '11000') : "Grade Id Empty", self::BAD_REQUEST_CODE);
        }*/

        $deviceId = $this->deviceAction($userAgent);

        $chapProduct = new Api_Model_ChapProducts();
        $topProducts = $chapProduct->getQeasyTopCategoryProducts($chapId, $deviceId, $categoryId, $limit, $offset, $grade, $userType);

        if ($topProducts) {
            //$nexApi = new Nexva_Api_NexApi();
            $nexApi = new Nexva_Api_QelasyApi();

            $productInfo = $nexApi->getProductDetails($topProducts, $deviceId, true);

            if (count($productInfo) > 0) {

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
        $grade = $this->_getParam('grade');
        $userType = $this->_getParam('user_type');

        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        //check if grade has been provided
        /*if($grade === null || empty($grade)){
            $this->__echoError("11000",$this->getTranslatedText($langCode, '11000') ? $this->getTranslatedText($langCode, '11000') : "Grade Id Empty", self::BAD_REQUEST_CODE);
        }*/

        $deviceId = $this->deviceAction($userAgent);

        $chapProduct = new Api_Model_ChapProducts();
        $topProducts = $chapProduct->getQelasyTopProductsByDevice($chapId, $deviceId, true, $limit, $offset, $grade, $userType);

        if ($topProducts) {

            //$nexApi = new Nexva_Api_NexApi();
            $nexApi = new Nexva_Api_QelasyApi();

            $productInfo = $nexApi->getProductDetails($topProducts, $deviceId, true);

            if (count($productInfo) > 0) {
                $this->__printArrayOfDataJson($productInfo);
            }
        } else {

            $this->__dataNotFound();
        }
    }

    /**
     *
     * Returns the Random Free Applications based on Device and Chap
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * returns JSON encoded apps array
     */
    public function topFreeAppsAction() {

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        //Get the parameters
        $category = trim($this->_getParam('category', null));
        $grade = trim($this->_getParam('grade', null));
        $userType = $this->_getParam('user_type');

        //Thumbnail Dimension
        $thumbWidth = $this->_getParam('twidth', 80);
        $thumbHeight = $this->_getParam('theight', 80);

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //check if grade has been provided
        /*if($grade === null || empty($grade)){

            $this->__echoError("11000",$this->getTranslatedText($langCode, '11000') ? $this->getTranslatedText($langCode, '11000') : "Grade Id Empty", self::BAD_REQUEST_CODE);
        }*/

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId))
        {
            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
        }
        else
        { //Get Featured Apps based on Chap and the Device

            //$ApiModel = new Nexva_Api_NexApi();
            $ApiModel = new Nexva_Api_QelasyApi();
            $apiCall = true;

            //Featured apps for banners
            $freeApps = $ApiModel->freeAppsAction($chapId, 15, $deviceId, $apiCall, $category, $grade, $thumbWidth, $thumbHeight, $userType);

            //change the thumbnail path
            if (count($freeApps) > 0)
            {
                $apps = str_replace('\/', '/', json_encode($freeApps));
                $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                echo $apps;
            }
            else
            {
                $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
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

        //Validate Header params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        //Get the parameters
        $category = trim($this->_getParam('category', null));
        $grade = trim($this->_getParam('grade'));
        $userType = $this->_getParam('user_type');
        $thumbWidth = trim($this->_getParam('twidth', 80));
        $thumbHeight = trim($this->_getParam('theight', 80));

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId))
        {
            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
        }
        else
        { //Get Featured Apps based on Chap and the Device

            //$ApiModel = new Nexva_Api_NexApi();
            $ApiModel = new Nexva_Api_QelasyApi();
            $apiCall = true;

            //Featured apps for banners
            $premiumApps = $ApiModel->paidAppsAction($chapId, 15, $deviceId, $apiCall, $category, $grade, $thumbWidth, $thumbHeight, $userType);

            //change the thumbnail path
            if (count($premiumApps) > 0)
            {
                $apps = str_replace('\/', '/', json_encode($premiumApps));
                $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                echo $apps;
            }
            else
            {
                $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
            }
        }
    }



    /**
     *
     * Returns the most rated Applications based on Device and Chap
     *
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * returns JSON encoded apps array
     */
    public function topRatedAppsAction() {

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        //Get the parameters
        $category = trim($this->_getParam('category', null));
        $grade = trim($this->_getParam('grade'));
        $userType = $this->_getParam('user_type');
        $thumbWidth = trim($this->_getParam('twidth', 80));
        $thumbHeight = trim($this->_getParam('theight', 80));

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId))
        {
            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
        }
        else
        { //Get Most rated Apps based on Chap and the Device

            //$ApiModel = new Nexva_Api_NexApi();
            $ApiModel = new Nexva_Api_QelasyApi();

            $apiCall = true;

            //Featured apps for banners
            $freeApps = $ApiModel->topRatedAppsAction($chapId, 15, $deviceId, $apiCall, $category, $grade, $thumbWidth, $thumbHeight, $userType);

            //change the thumbnail path
            if (count($freeApps) > 0)
            {
                $apps = str_replace('\/', '/', json_encode($freeApps));
                $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                echo $apps;
            }
            else
            {
                $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
            }
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

        $simpleSearch = trim($this->_getParam('simple', true));
        
        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $deviceId = $this->deviceAction($userAgent);

        //Get chapter site language id
        $userModel = new Model_User();
        $chapLanguageId = $userModel->getUserLanguage($chapId);

        $productsModel = new Model_Product();

        //If the chap language is not english first search the keyword in PRODUCT_META table.
        $countPaginator = NULL;
        if($chapLanguageId != 1){

            $searchQry = $productsModel->getSearchQueryChapByLangId($keyword, $deviceId, $simpleSearch, $chapId, $chapLanguageId);
            //To check the count of fetched results following code added
            $countPaginator = count(Zend_Paginator::factory($searchQry));
            //echo count($searchQry);die();
        }

        if($countPaginator < 1){
            $searchQry = $productsModel->getSearchQueryChap($keyword, $deviceId, $simpleSearch, $chapId);
            $paginator = Zend_Paginator::factory($searchQry);
        }

        if ($searchQry) {

            $paginator = Zend_Paginator::factory($searchQry);
            $paginator->setItemCountPerPage($limit);
            $paginator->setCurrentPageNumber($offset);

            $products = array();

            if (!is_null($paginator)) {
                $i = 0;
                foreach ($paginator as $row) {

                    $id = (isset($row->id)) ? $row->id : $row->product_id; //can't change the device selector code now

                    $chapLanguageId = ($chapLanguageId != 1) ? $chapLanguageId : NULL ;

                    $productinfo = $productsModel->getProductDetailsById($id, true, $chapLanguageId);

                    $products[$i]['product_id'] = $id;
                    $products[$i]['user_id'] = $productinfo['uid'];
                    $products[$i]['thumbnail'] = $productinfo['thumb_name'];
                    $products[$i]['name'] = $productinfo['name'];
                    $products[$i]['price'] = $productinfo['cost'];
                    $i++;
                }
            }

            //$nexApi = new Nexva_Api_NexApi();
            $nexApi = new Nexva_Api_QelasyApi();

            $productsDisplay = $nexApi->getProductDetails($products, $deviceId, true);

            if (count($productsDisplay) > 0) {
                $this->__printArrayOfDataJson($productsDisplay);
            } else {
                $this->__dataNotFound();
            }
        }
    }

    public function checkAppDownloadedAction()
    {
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

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

    /**@param : appId
     * Returns the reviews for a particular app in json format
     */
    public function appReviewsAction(){

        //Validate Header params
        $headersParams = $this->validateHeaderParams();

        //$chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $appId = $this->_getParam('appId');

        if($appId === null || empty($appId)){
            $this->__echoError("1001", $this->getTranslatedText($langCode, '1001') ? $this->getTranslatedText($langCode, '1001') : "App Id not found", self::BAD_REQUEST_CODE);
        }else{
            $reviewModel = new Api_Model_Reviews();
            $reviews = $reviewModel->getReviewsByAppId($appId);
            
            //nl2br(htmlspecialchars(strip_tags()));
   
            //print_r($reviews);die();
            //$reviews['rating'] = '4.5';
            echo json_encode($reviews);

            /*$response = array();
            $i = 0;
            $totalRating = 0;
            foreach($reviews as $review){
                //$response[$review->name] = $review->review;
                $response[$i]['name'] = $review->name;
                $response[$i]['title'] = $review->title;
                $response[$i]['review'] = $review->review;
                $response[$i]['date'] = $review->date;

                //get the total rating
                $totalRating = $totalRating + $review->rating;
                $i++;
            }

            //calculate average rating based on reviews
            $response['rating'] = $totalRating/$i;
            //print_r($response);die();
            echo json_encode($response);*/
        }
    }

    public  function promotedAppsAction(){

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        //Get the parameters
        $category = trim($this->_getParam('category', null));
        $grade = trim($this->_getParam('grade'));
        $userType = trim($this->_getParam('user_type'));

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //check if grade has been provided
        /*if($grade === null || empty($grade)){

            $this->__echoError("11000",$this->getTranslatedText($langCode, '11000') ? $this->getTranslatedText($langCode, '11000') : "Grade Id Empty", self::BAD_REQUEST_CODE);
        }*/

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId))
        {
            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
        }
        else
        { //Get Featured Apps based on Chap and the Device

            //$ApiModel = new Nexva_Api_NexApi();
            $ApiModel = new Nexva_Api_QelasyApi();

            $apiCall = true;

            //get promoted apps (these are the banners in the web site)
            $promotedApps = $ApiModel->banneredAppsAction($chapId, 6, $deviceId, $apiCall, $category, $grade, '', '', $userType);

            //change the thumbnail path
            if (count($promotedApps) > 0)
            {
                $apps = str_replace('\/', '/', json_encode($promotedApps));
                $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                echo $apps;
            }
            else
            {
                $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
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
        $langCode = $headersParams['langCode'];

        //Get the parameters
        $category = trim($this->_getParam('category', null));
        $grade = trim($this->_getParam('grade'));
        $userType = $this->_getParam('user_type');

        //check if grade has been provided
        /*if($grade === null || empty($grade)){

            $this->__echoError("11000",$this->getTranslatedText($langCode, '11000') ? $this->getTranslatedText($langCode, '11000') : "Grade Id Empty", self::BAD_REQUEST_CODE);
        }*/

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId))
        {
            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
        }
        else
        { //Get Featured Apps based on Chap and the Device

            //$ApiModel = new Nexva_Api_NexApi();
            $ApiModel = new Nexva_Api_QelasyApi();

            $apiCall = true;

            //Featured apps for banners
            $newestApps = $ApiModel->newestAppsAction($chapId, 15, $deviceId, $apiCall, $category, $grade,'','', $userType);

            //change the thumbnail path
            if (count($newestApps) > 0)
            {
                $apps = str_replace('\/', '/', json_encode($newestApps));
                $this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                echo $apps;
            }
            else
            {
                $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
            }
        }
    }

    public function getBuildUrlAction(){

        //$status = $this->_request->status;
        //$appId = $this->_request->appId;
        //$buildId = $this->_request->build_Id;

        $paymentId = $this->_request->payment_id;
        $token = $this->_request->token;

        //$token = substr($token, 0, 50);  //This is for temp

        $db = Zend_Registry::get('db');
        $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'interop_payments','id','token');

        $authDbAdapter  ->setIdentity($paymentId)
                        ->setCredential($token);

        $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);

        $buildUrl = null;

        if($result->isValid()){
            $interopPaymentsModel = new Api_Model_InteropPayments();
            $paymentDetails = $interopPaymentsModel->fetchRow($interopPaymentsModel->select()->where('id = ?',$paymentId));

            $paymentDetails = $paymentDetails->toArray();

            $buildUrl = $paymentDetails['downlaod_link'];
        }

        $buildUrl = array('build_url' => $buildUrl);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $buildUrlJson = str_replace('\/', '/', json_encode($buildUrl));
        $this->loggerInstance->log('Response ::' . $buildUrlJson,Zend_Log::INFO);
        echo $buildUrlJson;
    }

    /*
     * This payment function will be using for Qelasy only.
     */
    public function initiateOmPayAction() {

        $sessionId = $this->_getParam('session', 0);
        $userId = $this->__validateToken($sessionId);

        //Get the session for initialize the payment
        //$sessionId = Zend_Session::getId();
        
        //$sessionId =  substr(md5(uniqid(rand(), true)), 5, 8);
        //$userId = 1;
        
        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $appId = $this->_getParam('appId');
        $buildId = $this->_getParam('build_Id');

        //Testing Mobile No
        //$mobileNo = '5155328687';
        //$userAgent = "Mozilla/5.0 (Linux; U; Android 1.5; fr-fr; Galaxy Build/CUPCAKE) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2";         
        //$chapId = 8056;
        
        //Check if App Id has been provided
        if ($appId === null || empty($appId)) {
            
            $this->__echoError("1001", $this->getTranslatedText($langCode, '1001') ? $this->getTranslatedText($langCode, '1001') : "App Id not found", self::BAD_REQUEST_CODE);          
        }
        //Check if Chap Id has been provided
        if ($buildId === null || empty($buildId)) 
        {            
            $this->__echoError("1002", $this->getTranslatedText($langCode, '1002') ? $this->getTranslatedText($langCode, '1002') : "Build Id not found", self::BAD_REQUEST_CODE);      
        }

        //Check if the app belongs to the CHAP
        $chapProductModel = new Api_Model_ChapProducts();
        $appCount = $chapProductModel->getProductCountByChap($chapId, $appId);

        if ($appCount == 0) {
            
            $this->__echoError("1003", $this->getTranslatedText($langCode, '1003') ? $this->getTranslatedText($langCode, '1003') : "App does not belong to this partner", self::BAD_REQUEST_CODE);      
        }

        /********************************************************************* */

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) 
        {
            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
        } 
   
        $price = 0;
        $appName = "";

        //get the app details
        $productModel = new Api_Model_Products();
        $appDetails = $productModel->getProductDetailsbyId($appId);
          
        //Check if app details available
        if (is_null($appDetails)) 
        {
            $this->__echoError("3001", $this->getTranslatedText($langCode, '3001') ? $this->getTranslatedText($langCode, '3001') : "This app has been removed or does not exist", self::BAD_REQUEST_CODE);
        }

        //Check if the app is a premium one
        if ($appDetails->price <= 0) 
        {
            $this->__echoError("7000", $this->getTranslatedText($langCode, '7000') ? $this->getTranslatedText($langCode, '7000') : "Price is invalid", self::BAD_REQUEST_CODE);
        }
        else
        {
            $price = $appDetails->price;
            $appName = $appDetails->name;
        }
            
        /*************************** Begin Payment Process *******************************************/
        
        $mobileNo = '';
        
        //Get payment gateway Id of the CHAP            
        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
        $pgType = $pgDetails->gateway_id; 
        $paymentGatewayId = $pgDetails->payment_gateway_id;
        $pgClassName = $pgType;
        
        //Call Nexva_MobileBilling_Factory and create relevant instance. Since this is a redirection payment, this factory doesn't contain the payment codes
        $pgClass = Nexva_PaymentGateway_Factory::factory($pgType,$pgClassName);

        //Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
        $interopPaymentId = $pgClass->addMobilePayment($chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId);

        $sandBox = 0;
        if ($sandBox == 0) {
            $merchantId = '0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e';
            $url = 'https://ompay.orange.ci/e-commerce/init.php';
            $redirect_url = 'https://ompay.orange.ci/e-commerce/';
        } else {
            $merchantId = '0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e';
            $url = 'https://ompay.orange.ci/e-commerce_test_gw/init.php';
            $redirect_url = 'https://ompay.orange.ci/e-commerce_test_gw/';
        }

        //Convert the price to the local price
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];
        $price = ceil($currencyRate * $price);
        //$price = 3.00;
      
        $client = new Zend_Http_Client($url);

        $client->setHeaders(array(
            'User-Agent: Mozilla/5.0 Firefox/3.6.12',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9',
            'Accept-Language: en-us,en;q=0.5',
            'Accept-Encoding: deflate',
            'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: 109'
        ));

        $purchaseref = $pgClass->getEnc($interopPaymentId.'#APP#'.$sessionId);
        
        $client->setParameterPost(array(
            'merchantid' => $merchantId,
            'amount' => $price,
            'sessionid' => $sessionId,
            'purchaseref' => $purchaseref
        ));

        $response = $client->request(Zend_Http_Client::POST);
        
        $token = array('ompay_token' => $response->getRawBody(), 'payment_id' => $interopPaymentId, 'session_id' => $sessionId);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $tokenStr = str_replace('\/', '/', json_encode($token));
        $this->loggerInstance->log('Response ::' . $token,Zend_Log::INFO);
        echo $tokenStr;
        
        /*token = $response->getRawBody();

        $pgData = array(
            'merchantid' => $merchantId,
            'token' => $token,
            'amount' => $price,
            'sessionid' => $sessionId,
            'purchaseref' => $interopPaymentId.'#WEB'
        );
        
        $this->redirectPost($redirectUrl, $pgData);*/
    }

    function redirectPost($redirectUrl, array $pgData) {
        ?>
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <script type="text/javascript">
                    function closethisasap() {
                        document.forms["redirectpost"].submit();
                    }
                </script>
            </head>
            <body onload="closethisasap();">
                <form name="redirectpost" method="post" action="<?= $redirectUrl; ?>">
        <?
        if (!is_null($pgData)) {
            foreach ($pgData as $k => $v) {
                echo '<input type="hidden" name="' . $k . '" value="' . $v . '"> ';
            }
        }
        ?>
                </form>
            </body>
        </html>
        <?
        exit;
    }
    
    /*public function formAction() {
        ?>
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <script type="text/javascript">
                    function closethisasap() {
                        document.forms["redirectpost"].submit();
                    }
                </script>
            </head>
            <body onload="closethisasap();">
                <form name="redirectpost" method="post" action="https://ompay.orange.ci/e-commerce_test_gw/">
                    <input type="hidden" name="test" value="123"/>
                </form>
            </body>
        </html>
        <?php
    }*/
        
    public function getBannerImageAction(){

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $chapId = $headersParams['chapId'];

        //Load them meta model
        $themeMeta   = new Model_ThemeMeta();
        $themeMeta->setEntityId($chapId);

        $qelasyBanner = $themeMeta->WHITELABLE_SITE_QELASY_BANNER;

        $url =  'http://nexva.com/wl/qelasy/'.$qelasyBanner;

        $bannerUrl = array('banner_url' => $url);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $bannerStr = str_replace('\/', '/', json_encode($bannerUrl));
        $this->loggerInstance->log('Response ::' . $bannerUrl,Zend_Log::INFO);
        echo $bannerStr;
    }
    
     /**
     * Adding reviews to individual apps. This will insert the review and allow to moderation
     *
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * @param $productId App ID (GET)
     * @param $token User Session (GET)
     */

    public function addReviewAction() {
        
        $headersParams = $this->validateHeaderParams();

        $sessionId = $this->_getParam('token', 0);
        $userId = $this->__validateToken($sessionId);

        $productId = $this->_getParam('product_id', null);

        $rating = max(min($this->_getParam('rating', 5), 5), 1); //getting value between 1 and 5 
        //$review->title = $this->_getParam('title', null);
        $reviewer  = $this->_getParam('reviewer');
        $body = preg_replace("/\s+/", ' ', substr($this->_getParam('body', false), 0, 1000));
        
        if($reviewer == ''){
            $errorMsg = "Please enter your name";
            $this->__echoError("3001", $errorMsg, self::BAD_REQUEST_CODE);
        }
        
        if($body == ''){
            $errorMsg = "Please add your review";
            $this->__echoError("3001", $errorMsg, self::BAD_REQUEST_CODE);
        }
        
        $data = array();
        $data['user_id'] = $userId;
        $data['product_id'] = $productId;
        $data['name'] = $reviewer;
        $data['review'] = $body;
        $data['title'] = '';
        $data['type'] = 'USER';
        $data['rating'] = $rating;
        $data['status'] = 'NOT_APPROVED';

        $reviewModel = new Model_Review();
        
        $status = 'fail';
        if($reviewModel->insert($data)){
            $status = 'success';
        }
        else{
            $status = 'fail';
        }
        
        $response = array('status' => $status);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $responseStr = str_replace('\/', '/', json_encode($response));
        $this->loggerInstance->log('Response ::' . $response,Zend_Log::INFO);
        echo $responseStr;
    }
    
    
    /**
     * Add ratings for individual apps
     *
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * @param $proId App ID (GET)
     * @param $token Build ID (GET)
     * @param $rating Rating (GET)
     */

    public function addRatingsAction() {
        
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        //$langCode = $headersParams['langCode'];

        $sessionId = $this->_getParam('token', 0);
        $userId = $this->__validateToken($sessionId);

        $proId  = intval($this->_getParam('product_id', 0));
        $rating = intval($this->_getParam('rating', 0));
         
        //checking session for ratings if present
        $ratingNamespace    = new Zend_Session_Namespace('Ratings');

        $ratedProducts  = array($proId => $rating);

        $statisticDownloadModel = new Model_StatisticDownload();
        $downloaded = $statisticDownloadModel->userHasDownloaded($userId,$proId);

        if(!count($downloaded)){
            $errorMsg = "You haven't downloaded this App.";
            $this->__echoError("3002", $errorMsg, self::BAD_REQUEST_CODE);
            //$this->__echoError("7000", $this->getTranslatedText($langCode, '7000') ? $this->getTranslatedText($langCode, '7000') : "Price is invalid", self::BAD_REQUEST_CODE);
        }

        $ratingModel = new Model_Rating();
        $hasRated = $ratingModel->userHasRated($userId,$proId);

        if($hasRated){
            $errorMsg = "You've already rated this application within last Month.";
            $this->__echoError("3001", $errorMsg, self::BAD_REQUEST_CODE);
        }

        $responseMsg = null;
        $status = null;
            
        $avgRating = '';
        
        if ($proId && $rating) {

            $data   = array(
                'product_id'    => $proId,
                'rating'        => $rating,
                'ip'            => @$_SERVER['REMOTE_ADDR'],
                'chap_id'       => $chapId,
                'user_id'       => $userId
            );
            
            $ratingModel->insert($data);
            $ratingModel->clearCache($proId);
            //put it into the session so user can't rate the same thing
            $ratingNamespace->ratedProducts = $ratedProducts;
            
            $responseMsg = 'Thank you for rating this application';
            $status = 'success';

            //$avgRatingVotes = $ratingModel->getTotalRatingByProduct($appId);
            $avgRating = $ratingModel->getAverageRatingByProduct($proId);
                    
            
        } else {
            $responseMsg = 'Error on adding ratings';
            $status = 'fail';
        }
        
        $response = array('status' => $status, 'response_msg' => $responseMsg, 'avg_ratings' => $avgRating);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $responseStr = str_replace('\/', '/', json_encode($response));
        $this->loggerInstance->log('Response ::' . $response,Zend_Log::INFO);
        echo $responseStr;
    }
}