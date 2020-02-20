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
        
        $headersParams = apache_request_headers();
        $chapId = $headersParams['Chap-Id'];
        $langCode = $headersParams['langCode'];
        
        $skipActionNames = array('openmobile', 'pay-appp', 'buymts-responce', 'buymts-request');
        
        if($chapId == 326728 )
            die();
        

        if(!in_array($this->getRequest()->getActionName(), $skipActionNames)) 
        {
            //$this->loggerInstance = new Zend_Log();
           // $pathToLogFile = APPLICATION_PATH . "/../logs/api_request." . APPLICATION_ENV . ".log";
           // $writer = new Zend_Log_Writer_Stream($pathToLogFile);
           // //$this->loggerInstance->addWriter($writer);

            
      

            $header = $this->validateHeaderParams();
            
            //$this->uniqueVisits();
            //echo $_SERVER['REMOTE_ADDR'];
            //Zend_Debug::dump($header);die();

            //$headersParams = apache_request_headers();
           // $userAgent = $headersParams['User-Agent'];
          //  $chapId = $headersParams['Chap-Id'];
          //  //$this->loggerInstance->log('Request ::' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . ' :: - Request IP ' . 
                                        //    $_SERVER['REMOTE_ADDR'] . ' :: - User-Agent - ' . $userAgent . ' :: -Chap-Id-' . $chapId, Zend_Log::INFO);

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
    
  /** Returns relevant error message based on the language and the error code 
    * @param $languageCode language code
    * @param $errorCode error code
    * returns relevant error message
    */
    protected function getTranslatedText($languageCode, $errorCode, $paramOptional = null)
    {
        $translationArr;
        
        //require_once APPLICATION_PATH . '/../library/Nexva/ControllerTranslations/TranslationsArray.php';

        //French translations
        $translationArr['fr'] = array(
                                '0000' => 'User Agent not provided - French',
                                '1000' => 'User Agent not provided - French',
                                '1001' => 'Identifiant de l\'application non trouvÃ©',
                                '1002' => 'Build Id not found - French',
                                '1003' => 'App does not belong to this partner - French',
                                '1004' => 'Limite maximum de tÃ©lÃ©chargements autorisÃ©s atteinte',

                                '2000' => 'Mobile non trouvÃ©',
                                '2001' => 'Adresse e-mail invalide',
                                '2002' => 'Votre mot de passe doit avoir au moins 6 caractÃ¨res',
                                '2003' => 'Le champ PrÃ©nom est vide',
                                '2004' => 'Le champ Nom est vide',
                                '2005' => 'NumÃ©ro de tÃ©lÃ©phone invalide',
                                '2006' => 'Cette adresse e-mail existe dÃ©jÃ ',
                                '2007' => 'Ce numÃ©ro de tÃ©lÃ©phone existe dÃ©jÃ . Veuillez entrer un autre',
                                '2222' => 'Utilisateur vÃ©rifiÃ© avec succÃ¨s',

                                '3000' => 'Informations non trouvÃ©es',
                                '3001' => 'Cette application a Ã©tÃ© supprimÃ©e ou n\'existe pas',

                                '4000' => 'Payment was unsuccessfull - French',

                                '5000' => 'NumÃ©ro de mobile non trouvÃ©',

                                '6000' => 'CatÃ©gorie non trouvÃ©e',

                                '7000' => 'Le prix est invalide',
                                '7001' => 'Demande de tÃ©lÃ©chargement invalide',

                                '8000' => 'La validation des informations a Ã©chouÃ©',
                                '8001' => 'Identifiant utilisateur non trouvÃ©',
                                '8002' => 'VÃ©rification du code non trouvÃ©e',
                                '8003' => 'Les champs ne concordent pas',
                                '8004' => 'La vÃ©rification a dÃ©jÃ  Ã©tÃ© faite',
                                '8005' => 'Le champ Nom d\'utilisateur est vide',
                                '8006' => 'Le champ mot de passe est vide',
                                '8007' => 'Nom d\'utilisateur ou mot de passe invalide',
                                '8008' => 'L\'utilisateur n\'a pas vÃ©rifiÃ© le compte',
                                '8009' => 'Nom d\'utilisateur invalide',
                                '8010' => 'NumÃ©ro de tÃ©lÃ©phone invalide',
                                '8011' => 'NumÃ©ro de tÃ©lÃ©phone non enregistrÃ©',
                                '8012' => 'Mot de passe invalide',

                                '9000' => 'Demande non authentifiÃ©e',

                                '10000' => 'Veuillez utiliser le code de verification '.$paramOptional.' pour terminer votre enregistrement.',
                                '10001' => 'Veuillez utiliser le code de verification '.$paramOptional.' pour verifier votre numero de telephone.',
                                '10002' => 'Veuillez utiliser le code de verification '.$paramOptional.' pour terminer la rÃ©initialisation de votre mot de passe.',
                                '10003' => 'Vous vous etes enregistre avec succes.',
                             );

//Farsi(Persian language) translations
$translationArr['fa'] = array(
                                '0000' => 'Ù…Ø±ÙˆØ±Ú¯Ø± ÙˆØ§Ø±Ø¯ Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª',
                                '1000' => 'Ø´Ù†Ø§Ø³Ù‡ Ù¾Ø±ÙˆØªÚ©Ù„ ØªØ§ÛŒÛŒØ¯ ÛŒØ§Ù�Øª Ù†Ø´Ø¯',
                                '1001' => 'Ø´Ù†Ø§Ø³Ù‡ Ø¨Ø±Ù†Ø§Ù…Ù‡ Ú©Ø§Ø±Ø¨Ø±Ø¯ÛŒ ÛŒØ§Ù�Øª Ù†Ø´Ø¯',
                                '1002' => 'Ø´Ù†Ø§Ø³Ù‡ Ø³Ø§Ø®Øª ÛŒØ§Ù�Øª Ù†Ø´Ø¯',
                                '1003' => 'Ø¨Ø±Ù†Ø§Ù…Ù‡ Ø¨Ù‡ Ø§ÛŒÙ† Ø´Ø±ÛŒÚ© ØªØ¹Ù„Ù‚ Ù†Ø¯Ø§Ø±Ø¯',
                                '1004' => 'Ø­Ø¯Ø§ÙƒØ«Ø± Ø¯Ø§Ù†Ù„ÙˆØ¯ Ù…Ø¬Ø§Ø² Ø§Ù†Ø¬Ø§Ù… Ø´Ø¯Ù‡ Ø§Ø³Øª',

                                '2000' => 'Ø¯Ø³ØªÚ¯Ø§Ù‡ Ù…ÙˆØ±Ø¯ Ù†Ø¸Ø± ÛŒØ§Ù�Øª Ù†Ø´Ø¯',
                                '2001' => 'Ø§ÛŒÙ…ÛŒÙ„ Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø³Øª',
                                '2002' => 'Ø±Ù…Ø² Ø¹Ø¨ÙˆØ±ØªØ§Ù† Ø¨Ø§ÛŒØ¯ Ø­Ø¯Ø§Ù‚Ù„ 6 Ú©Ø§Ø±Ø§Ú©ØªØ± Ø¯Ø§Ø´ØªÙ‡ Ø¨Ø§Ø´Ø¯',
                                '2003' => 'Ù†Ø§Ù… ÙˆØ§Ø±Ø¯ Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª',
                                '2004' => 'Ù†Ø§Ù… Ø®Ø§Ù†ÙˆØ§Ø¯Ú¯ÛŒ ÙˆØ§Ø±Ø¯ Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª',
                                '2005' => 'Ø´Ù…Ø§Ø±Ù‡ ØªÙ„Ù�Ù† Ù‡Ù…Ø±Ø§Ù‡ Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø³Øª',
                                '2006' => 'Ø§ÛŒÙ…ÛŒÙ„ Ø§Ø² Ù‚Ø¨Ù„ ÙˆØ¬ÙˆØ¯ Ø¯Ø§Ø±Ø¯',
                                '2007' => 'Ø§ÛŒÙ† Ø´Ù…Ø§Ø±Ù‡ ØªÙ„Ù�Ù† Ø§Ø² Ù‚Ø¨Ù„ ÙˆØ§Ø±Ø¯ Ø´Ø¯Ù‡ Ø§Ø³Øª. Ù„Ø·Ù�Ø§Ù‹ Ø´Ù…Ø§Ø±Ù‡ Ø¯ÛŒÚ¯Ø±ÛŒ Ø±Ø§ ÙˆØ§Ø±Ø¯ Ù†Ù…Ø§ÛŒÛŒØ¯',
                                '2222' => 'Ú©Ø§Ø±Ø¨Ø± Ø¨Ø§Ù…ÙˆÙ�Ù‚ÛŒØª ØªØ£ÛŒÛŒØ¯ Ø´Ø¯',

                                '3000' => 'Ø¯Ø§Ø¯Ù‡ ÛŒØ§Ù�Øª Ù†Ø´Ø¯',
                                '3001' => 'Ø§ÛŒÙ† Ø¨Ø±Ù†Ø§Ù…Ù‡ Ø­Ø°Ù� Ø´Ø¯Ù‡ ÛŒØ§ ÙˆØ¬ÙˆØ¯ Ù†Ø¯Ø§Ø±Ø¯',

                                '4000' => 'Ù¾Ø±Ø¯Ø§Ø®Øª Ø¨Ø§ Ù…ÙˆÙ�Ù‚ÛŒØª Ø§Ù†Ø¬Ø§Ù… Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª',

                                '5000' => 'Ø´Ù…Ø§Ø±Ù‡ ØªÙ„Ù�Ù† Ù‡Ù…Ø±Ø§Ù‡ ÛŒØ§Ù�Øª Ù†Ø´Ø¯',

                                '6000' => 'Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒ ÛŒØ§Ù�Øª Ù†Ø´Ø¯',

                                '7000' => 'Ù…Ø¨Ù„Øº Ù…Ø¹ØªØ¨Ø± Ù†ÛŒØ³Øª',
                                '7001' => 'Ø¯Ø±Ø®ÙˆØ§Ø³Øª Ø¯Ø§Ù†Ù„ÙˆØ¯ Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø³Øª',

                                '8000' => 'Ø¹Ø¯Ù… Ù…ÙˆÙ�Ù‚ÛŒØª ØªØ£ÛŒÛŒØ¯ Ø§Ø¹ØªØ¨Ø§Ø± Ø¯Ø§Ø¯Ù‡â€ŒÙ‡Ø§',
                                '8001' => 'Ø´Ù†Ø§Ø³Ù‡ Ú©Ø§Ø±Ø¨Ø± ÛŒØ§Ù�Øª Ù†Ø´Ø¯',
                                '8002' => 'Ú©Ø¯ ØªØ§ÛŒÛŒØ¯ Ø§Ø¹ØªØ¨Ø§Ø± ÛŒØ§Ù�Øª Ù†Ø´Ø¯',
                                '8003' => 'ØªØ±Ú©ÛŒØ¨ Ù…Ø·Ø§Ø¨Ù‚Øª Ù†Ø¯Ø§Ø±Ø¯',
                                '8004' => 'ØªØ§ÛŒÛŒØ¯ Ø§Ø¹ØªØ¨Ø§Ø± Ù‚Ø¨Ù„Ø§ Ø§Ù†Ø¬Ø§Ù… Ø´Ø¯Ù‡ Ø§Ø³Øª',
                                '8005' => 'Ù†Ø§Ù… Ú©Ø§Ø±Ø¨Ø±ÛŒ ÙˆØ§Ø±Ø¯ Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª',
                                '8006' => 'Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± ÙˆØ§Ø±Ø¯ Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª',
                                '8007' => 'Ù†Ø§Ù… Ú©Ø§Ø±Ø¨Ø±ÛŒ ÛŒØ§ Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø³Øª',
                                '8008' => 'Ú©Ø§Ø±Ø¨Ø± Ø­Ø³Ø§Ø¨ Ø±Ø§ ØªØ§ÛŒÛŒØ¯ Ù†Ú©Ø±Ø¯Ù‡ Ø§Ø³Øª',
                                '8009' => 'Ù†Ø§Ù… Ú©Ø§Ø±Ø¨Ø±ÛŒ Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø³Øª',
                                '8010' => 'Ø´Ù…Ø§Ø±Ù‡ ØªÙ„Ù�Ù† Ù‡Ù…Ø±Ø§Ù‡ Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø³Øª',
                                '8011' => 'Ø´Ù…Ø§Ø±Ù‡ ØªÙ„Ù�Ù† Ù‡Ù…Ø±Ø§Ù‡ Ø«Ø¨Øª Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª',
                                '8012' => 'Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø³Øª',

                                '9000' => 'ØµØ­Øª Ø¯Ø±Ø®ÙˆØ§Ø³Øª ØªØ§ÛŒÛŒØ¯ Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª',

                                '10000' => 'Ù„Ø·Ù�Ø§Ù‹ Ø¨Ø±Ø§ÛŒ ØªÚ©Ù…ÛŒÙ„ Ø«Ø¨Øª Ù†Ø§Ù…ØŒ Ø§Ø² Ú©Ø¯ ØªØ§ÛŒÛŒØ¯ '.$paramOptional.' Ø§Ø³ØªÙ�Ø§Ø¯Ù‡ Ú©Ù†ÛŒØ¯',
                                '10001' => 'Ù„Ø·Ù�Ø§Ù‹ Ø¨Ø±Ø§ÛŒ ØªØ§ÛŒÛŒØ¯ Ø´Ù…Ø§Ø±Ù‡ ØªÙ„Ù�Ù† Ù‡Ù…Ø±Ø§Ù‡ØŒ Ø§Ø² Ú©Ø¯ ØªØ§ÛŒÛŒØ¯ '.$paramOptional.' Ø§Ø³ØªÙ�Ø§Ø¯Ù‡ Ú©Ù†ÛŒØ¯',
                                '10002' => 'Ù„Ø·Ù�Ø§Ù‹ Ø¨Ø±Ø§ÛŒ ØªÚ©Ù…ÛŒÙ„ Ù�Ø±Ø§ÛŒÙ†Ø¯ ØªÙ†Ø¸ÛŒÙ… Ù…Ø¬Ø¯Ø¯ Ø±Ù…Ø² Ø¹Ø¨ÙˆØ±ØŒ Ø§Ø² Ú©Ø¯ ØªØ£ÛŒÛŒØ¯ '.$paramOptional.' Ø§Ø³ØªÙ�Ø§Ø¯Ù‡ Ú©Ù†ÛŒØ¯',
                                '10003' => 'Ø«Ø¨Øªâ€ŒÙ†Ø§Ù… Ø¨Ø§ Ù…ÙˆÙ�Ù‚ÛŒØª Ø§Ù†Ø¬Ø§Ù… Ø´Ø¯',
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

  /** This is left for old users who is not got their app updated.. 
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
        $langCode = $headersParams['langCode'];
        
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
     
        //$this->loggerInstance->log('Response ::' . json_encode($currencyDetails),Zend_Log::INFO);
        echo str_replace('\/','/',json_encode($currencyDetails));
        
    }
    
    public function appConfigurationsAction()
    {
    	//Validate Heder params

    	$headersParams = $this->validateHeaderParams();
    	
    
    	$userAgent = $headersParams['userAgent'];
    	$chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
    
    	//Get currency details
    	$currencyUserModel = new Api_Model_CurrencyUsers();
    	$currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
    
    	//Load them meta model
    	$themeMeta   = new Model_ThemeMeta();
    	$themeMeta->setEntityId($chapId);
    	

    
    	$allConstants['keywords'] = $themeMeta->WHITELABLE_SITE_ADVERTISING;
    	$allConstants['appstore_version'] =  $themeMeta->WHITELABLE_SITE_APPSTORE_VERSION;
    	$allConstants['add_interval'] =  $themeMeta->WHITELABLE_SITE_INTERVAL;
    	$allConstants['appstore_latest_version'] = $themeMeta->WHITELABLE_SITE_APPSTORE_VERSION;

        //Get the S3 URL of the Relevant build
        $productDownloadCls = new Nexva_Api_ProductDownload();
        $buildUrl = null;
        
        

        if(($themeMeta->WHITELABLE_SITE_APPSTORE_APP_ID) && ($themeMeta->WHITELABLE_SITE_APPSTORE_BUILD_ID)){
            

            
            $buildUrl = $productDownloadCls->getBuildFileUrl($themeMeta->WHITELABLE_SITE_APPSTORE_APP_ID, $themeMeta->WHITELABLE_SITE_APPSTORE_BUILD_ID);
        }
        

        $allConstants['latest_build_url'] = $buildUrl;
        
     
       
    	$this->getResponse()
    	->setHeader('Content-type', 'application/json');

    	
    	 
    	//$this->loggerInstance->log('Response ::' . json_encode($currencyDetails),Zend_Log::INFO);
    	echo str_replace('\/','/',json_encode($currencyDetails +  $allConstants));
    
    }
    
    
    public function mtnipheaderUserAction()
    {
        
    	//Validate Heder params
    	$headersParams = $this->validateHeaderParams();
    	
    
    	$userAgent = $headersParams['userAgent'];
    	$chapId = $headersParams['chapId'];
    	$langCode = $headersParams['langCode'];
        
    	$msisdn = @$_SERVER['HTTP_MSISDN'];
        //$msisdn = '234998885548566';
    
    	if($msisdn == null) {
    	   // $msisdn = '08103114401'; 
     $msisdn = '0123456789';//Don't change this since this is the common for web and app
    	}
    	

    	if($msisdn)    {

            $themeMeta   = new Model_ThemeMeta();
            $themeMeta->setEntityId($chapId);

            $countryCode = $themeMeta->COUNTRY_CODE_TELECOMMUNICATION;

            $ptn = "/^$countryCode/";  // Regex
            $str = $msisdn; //Your input, perhaps $_POST['textbox'] or whatever
            $rpltxt = "0";  // Replacement string
            $msisdn = preg_replace($ptn, $rpltxt, $str);
	
			$user = new Api_Model_Users();
			$userInfo = $user->getUserByMobileNo($msisdn);
    	
    	
    	If($userInfo) {
    	    
    	    $sessionUser = new Zend_Session_Namespace('partner_user');
    	    $sessionUser->id = $userInfo->id;
    	    $sessionId = Zend_Session::getId();
    	   
            $isCommonPwd = ($userInfo->password == md5('mtnpassword')) ? TRUE : FALSE;
    	    $response = array(
    	    		'user' => $userInfo->id,
    	    		'token' => $sessionId,
    	    		'mobile_no' => $userInfo->mobile_no,
                        'common_password' => $isCommonPwd
    	    );
    	    	
    	    //$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
    	    echo json_encode($response);
    	    
    	} else {
    	    
    	    $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
    	    $email = $activationCode.'iphaderuser@mtn.com';
    	    
    	    
    
    	     
    	    $password = 'mtnpassword'; //Don't change this since this is the common for web and app
    	    $firstName = 'mtnfisrtname';
    	    $lastName = 'mtnlastname';
    	    $firstName = 'mtnfisrtname';
    	    
    	    $userData = array(
    	    	    'username' => $email,
    	    		'email' => $email,
    	    	    'password' => $password,
    	    		'type' => "USER",
    	    		'login_type' => "NEXVA",
    	    		'chap_id' => $chapId,
    	    		'mobile_no' => $msisdn,
    	    		'activation_code' => $activationCode
    	    );
    	    

    	    $userId = $user->createUser($userData);
    	    
  
    	    
    	    $userMeta = new Model_UserMeta();
    	    $userMeta->setEntityId($userId);
    	    $userMeta->FIRST_NAME = $firstName;
    	    $userMeta->LAST_NAME = $lastName;
    	    $userMeta->TELEPHONE = $msisdn;
    	    $userMeta->UNCLAIMED_ACCOUNT = '1';
    	    
    	    $sessionUser = new Zend_Session_Namespace('partner_user');
    	    $sessionUser->id = $userId;
    	    $sessionId = Zend_Session::getId();
    	      
    	    $response = array(
    	    		'user' => $userId,
    	    		'token' => $sessionId,
    	    		'mobile_no' => $msisdn,
                        'common_password' => TRUE
    	    );
    	    
    	    //$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
    	    echo json_encode($response);
    	    
    	    
    	}
    	
    	}  else {
    	    
    	    $this->__echoError("8020", "Please use MTN Nigeria data connection. ", self::BAD_REQUEST_CODE);
    	    
    	}
    
    }
    
    
    public function airtelipheaderUserAction()
    {
    
    	//Validate Heder params
    	$headersParams = $this->validateHeaderParams();
    	 
    
    	$userAgent = $headersParams['userAgent'];
    	$chapId = $headersParams['chapId'];
    	$langCode = $headersParams['langCode'];
    	
    	$headers = apache_request_headers();
    	
    	
    	$themeMeta   = new Model_ThemeMeta();
    	$themeMeta->setEntityId($chapId);
    	
    	$headerIdentifierCode = $themeMeta->WHITELABLE_IP_HDR_IDENTIFIER;
    	 
    	$msisdn = $headers[$headerIdentifierCode];
    	//$msisdn = '234998885548566';
    
    	if($msisdn == null) {
    		// $msisdn = '08103114401';
    		$msisdn = '0123456789';//Don't change this since this is the common for web and app
    	}
    	 
    	
    	//if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
    	//	$msisdn = '2348022220469';//Don
    	//}
    	
    	
    	
    
    	if($msisdn)    {
    
    		$user = new Api_Model_Users();
    		$userInfo = $user->getUserByMobileNo($msisdn);
    		 
    		If($userInfo) {
    				
    			$sessionUser = new Zend_Session_Namespace('partner_user');
    			$sessionUser->id = $userInfo->id;
    			$sessionId = Zend_Session::getId();
    
    			$isCommonPwd = ($userInfo->password == md5('airtelpassword')) ? TRUE : FALSE;
    			$response = array(
    					'user' => $userInfo->id,
    					'token' => $sessionId,
    					'mobile_no' => $userInfo->mobile_no,
    					'common_password' => $isCommonPwd
    			);
    
    			//$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
    			echo json_encode($response);
    				
    		} else {
    				
    			$activationCode = substr(md5(uniqid(rand(), true)), 5,8);
    			$email = $activationCode.'iphaderuser@airtel.com';
    				
    			$password = 'airtelpassword'; //Don't change this since this is the common for web and app
    			$firstName = 'airtelfisrtname';
    			$lastName = 'airtellastname';
    			$firstName = 'airtelfisrtname';
    				
    			$userData = array(
    					'username' => $email,
    					'email' => $email,
    					'password' => $password,
    					'type' => "USER",
    					'login_type' => "NEXVA",
    					'chap_id' => $chapId,
    					'mobile_no' => $msisdn,
    					'activation_code' => $activationCode
    			);
    				
    
    			$userId = $user->createUser($userData);
    				
    			$userMeta = new Model_UserMeta();
    			$userMeta->setEntityId($userId);
    			$userMeta->FIRST_NAME = $firstName;
    			$userMeta->LAST_NAME = $lastName;
    			$userMeta->TELEPHONE = $msisdn;
    			$userMeta->UNCLAIMED_ACCOUNT = '1';
    				
    			$sessionUser = new Zend_Session_Namespace('partner_user');
    			$sessionUser->id = $userId;
    			$sessionId = Zend_Session::getId();
    			 
    			$response = array(
    					'user' => $userId,
    					'token' => $sessionId,
    					'mobile_no' => $msisdn,
    					'common_password' => TRUE
    			);
    				
    			//$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
    			echo json_encode($response);
    				
    				
    		}
    		 
    	}  else {
    			
    		$this->__echoError("8020", "Please use Airtel data connection. ", self::BAD_REQUEST_CODE);
    			
    	}
    
    }
    
    
    public function orangeipheaderUserAction()
    {
    
    	//Validate Heder params
    	$headersParams = $this->validateHeaderParams();
    
    
    	$userAgent = $headersParams['userAgent'];
    	$chapId = $headersParams['chapId'];
    	$langCode = $headersParams['langCode'];
    	 
    	$headers = apache_request_headers();
    	 
    	 
    	$themeMeta   = new Model_ThemeMeta();
    	$themeMeta->setEntityId($chapId);
    	 
    	$headerIdentifierCode = $themeMeta->WHITELABLE_IP_HDR_IDENTIFIER;
    
    	$msisdn = $headers[$headerIdentifierCode];
    	//$msisdn = '234998885548566';
    
    	if($msisdn == null) {
    		// $msisdn = '08103114401';
    		$msisdn = '0123456789';//Don't change this since this is the common for web and app
    	}
    
    	 
    	//if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
    	//	$msisdn = '2348022220469';//Don
    	//}
    	 
    	 
    	 
    
    	if($msisdn)    {
    
    		$user = new Api_Model_Users();
    		$userInfo = $user->getUserByMobileNo($msisdn);
    		 
    		If($userInfo) {
    
    			$sessionUser = new Zend_Session_Namespace('partner_user');
    			$sessionUser->id = $userInfo->id;
    			$sessionId = Zend_Session::getId();
    
    			$isCommonPwd = ($userInfo->password == md5('orangepassword')) ? TRUE : FALSE;
    			$response = array(
    					'user' => $userInfo->id,
    					'token' => $sessionId,
    					'mobile_no' => $userInfo->mobile_no,
    					'common_password' => $isCommonPwd
    			);
    
    			//$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
    			echo json_encode($response);
    
    		} else {
    
    			$activationCode = substr(md5(uniqid(rand(), true)), 5,8);
    			$email = $activationCode.'iphaderuser@orange.com';
    
    			$password = 'orangepassword'; //Don't change this since this is the common for web and app
    			$firstName = 'orangefisrtname';
    			$lastName = 'orangelastname';
    
    			$userData = array(
    					'username' => $email,
    					'email' => $email,
    					'password' => $password,
    					'type' => "USER",
    					'login_type' => "NEXVA",
    					'chap_id' => $chapId,
    					'mobile_no' => $msisdn,
    					'activation_code' => $activationCode
    			);
    
    
    			$userId = $user->createUser($userData);
    
    			$userMeta = new Model_UserMeta();
    			$userMeta->setEntityId($userId);
    			$userMeta->FIRST_NAME = $firstName;
    			$userMeta->LAST_NAME = $lastName;
    			$userMeta->TELEPHONE = $msisdn;
    			$userMeta->UNCLAIMED_ACCOUNT = '1';
    
    			$sessionUser = new Zend_Session_Namespace('partner_user');
    			$sessionUser->id = $userId;
    			$sessionId = Zend_Session::getId();
    
    			$response = array(
    					'user' => $userId,
    					'token' => $sessionId,
    					'mobile_no' => $msisdn,
    					'common_password' => TRUE
    			);
    
    			//$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
    			echo json_encode($response);
    
    
    		}
    		 
    	}  else {
    		 
    		$this->__echoError("8020", "Please use Airtel data connection. ", self::BAD_REQUEST_CODE);
    		 
    	}
    
    }
    
    
    
    public function mtsipheaderUserAction()
    {
    
    	//Validate Heder params
    	$headersParams = $this->validateHeaderParams();
    
    
    	$userAgent = $headersParams['userAgent'];
    	$chapId = $headersParams['chapId'];
    	$langCode = $headersParams['langCode'];
    
    	$headers = apache_request_headers();
    
    
    	$themeMeta   = new Model_ThemeMeta();
    	$themeMeta->setEntityId($chapId);
    
    	$headerIdentifierCode = $themeMeta->WHITELABLE_IP_HDR_IDENTIFIER;
    
    	$msisdn = $headers[$headerIdentifierCode];
    	//$msisdn = '381645552117';
    
    	if($msisdn == null) {
    		// $msisdn = '08103114401';
    		$msisdn = '0123456789';//Don't change this since this is the common for web and app
    	}
    
    
    	//if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
    	///	$msisdn = '2348022220469';//Don
    	//}
    
    
    
    
    	if($msisdn)    {
    
    		$user = new Api_Model_Users();
    		$userInfo = $user->getUserByMobileNo($msisdn);
    		 
    		If($userInfo) {
    
    			$sessionUser = new Zend_Session_Namespace('partner_user');
    			$sessionUser->id = $userInfo->id;
    			$sessionId = Zend_Session::getId();
    
    			$isCommonPwd = ($userInfo->password == md5('orangepassword')) ? TRUE : FALSE;
    			$response = array(
    					'user' => $userInfo->id,
    					'token' => $sessionId,
    					'mobile_no' => $userInfo->mobile_no,
    					'common_password' => $isCommonPwd
    			);
    
    			//$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
    			echo json_encode($response);
    
    		} else {
    
    			$activationCode = substr(md5(uniqid(rand(), true)), 5,8);
    			$email = $activationCode.'iphaderuser@mts.com';
    
    			$password = 'mtspassword'; //Don't change this since this is the common for web and app
    			$firstName = 'mtsfisrtname';
    			$lastName = 'mtslastname';
    
    			$userData = array(
    					'username' => $email,
    					'email' => $email,
    					'password' => $password,
    					'type' => "USER",
    					'login_type' => "NEXVA",
    					'chap_id' => $chapId,
    					'mobile_no' => $msisdn,
    					'activation_code' => $activationCode
    			);
    
    
    			$userId = $user->createUser($userData);
    
    			$userMeta = new Model_UserMeta();
    			$userMeta->setEntityId($userId);
    			$userMeta->FIRST_NAME = $firstName;
    			$userMeta->LAST_NAME = $lastName;
    			$userMeta->TELEPHONE = $msisdn;
    			$userMeta->UNCLAIMED_ACCOUNT = '1';
    
    			$sessionUser = new Zend_Session_Namespace('partner_user');
    			$sessionUser->id = $userId;
    			$sessionId = Zend_Session::getId();
    
    			$response = array(
    					'user' => $userId,
    					'token' => $sessionId,
    					'mobile_no' => $msisdn,
    					'common_password' => TRUE
    			);
    
    			//$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
    			echo json_encode($response);
    
    
    		}
    		 
    	}  else {
    		 
    		$this->__echoError("8020", "Please use MTS data connection. ", self::BAD_REQUEST_CODE);
    		 
    	}
    
    }
    
    
    public function airtelrwipheaderUserAction()
    {
    
    	//Validate Heder params
    	$headersParams = $this->validateHeaderParams();
    
    
    	$userAgent = $headersParams['userAgent'];
    	$chapId = $headersParams['chapId'];
    	$langCode = $headersParams['langCode'];
    	 
    	$headers = apache_request_headers();
    
    	$msisdn = $headers['MSISDN'];
    	//$msisdn = '234998885548566';
    
    	if($msisdn == null) {
    		// $msisdn = '08103114401';
    		$msisdn = '0123456789';//Don't change this since this is the common for web and app
    	}
    
    
    	if($msisdn)    {
    
    		$user = new Api_Model_Users();
    		$userInfo = $user->getUserByMobileNo($msisdn);
    		 
    		If($userInfo) {
    
    			$sessionUser = new Zend_Session_Namespace('partner_user');
    			$sessionUser->id = $userInfo->id;
    			$sessionId = Zend_Session::getId();
    
    			$isCommonPwd = ($userInfo->password == md5('mtnpassword')) ? TRUE : FALSE;
    			$response = array(
    					'user' => $userInfo->id,
    					'token' => $sessionId,
    					'mobile_no' => $userInfo->mobile_no,
    					'common_password' => $isCommonPwd
    			);
    
    			//$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
    			echo json_encode($response);
    
    		} else {
    
    			$activationCode = substr(md5(uniqid(rand(), true)), 5,8);
    			$email = $activationCode.'iphaderuser@airtelrw.com';
    
    			$password = 'airtelrwpassword'; //Don't change this since this is the common for web and app
    			$firstName = 'airtelrwfisrtname';
    			$lastName = 'airtelrwlastname';
    			$firstName = 'airtelrwfisrtname';
    
    			$userData = array(
    					'username' => $email,
    					'email' => $email,
    					'password' => $password,
    					'type' => "USER",
    					'login_type' => "NEXVA",
    					'chap_id' => $chapId,
    					'mobile_no' => $msisdn,
    					'activation_code' => $activationCode
    			);
    
    
    			$userId = $user->createUser($userData);
    
    			$userMeta = new Model_UserMeta();
    			$userMeta->setEntityId($userId);
    			$userMeta->FIRST_NAME = $firstName;
    			$userMeta->LAST_NAME = $lastName;
    			$userMeta->TELEPHONE = $msisdn;
    			$userMeta->UNCLAIMED_ACCOUNT = '1';
    
    			$sessionUser = new Zend_Session_Namespace('partner_user');
    			$sessionUser->id = $userId;
    			$sessionId = Zend_Session::getId();
    
    			$response = array(
    					'user' => $userId,
    					'token' => $sessionId,
    					'mobile_no' => $msisdn,
    					'common_password' => TRUE
    			);
    
    			//$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
    			echo json_encode($response);
    
    
    		}
    		 
    	}  else {
    		 
    		$this->__echoError("8020", "Please use Airtel data connection. ", self::BAD_REQUEST_CODE);
    		 
    	}
    
    }

    public function mtnugipheaderUserAction(){

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        /*$commonUserInfo = $this->_helper->IpHeader->checkUser($chapId);

        $allConstants['user_id'] = $commonUserInfo['user_id'];
        $allConstants['mobile_no'] = $commonUserInfo['mobile_no'];
        $allConstants['token'] = Zend_Session::getId();

        $this->getResponse()->setHeader('Content-type', 'application/json');

        //$this->loggerInstance->log('Response ::' . json_encode($allConstants),Zend_Log::INFO);
        echo str_replace('\/','/',json_encode($allConstants));

        die();*/
        //

        $headers = apache_request_headers();

        $msisdn = $headers['MSISDN'];

        if($msisdn == null){
            $msisdn = '0123456789';//Don't change this since this is the common for web and app
        }

        if($msisdn){

            $user = new Api_Model_Users();
            $userInfo = $user->getUserByMobileNo($msisdn);

            if($userInfo){

                $sessionUser = new Zend_Session_Namespace('partner_user');
                $sessionUser->id = $userInfo->id;
                $sessionId = Zend_Session::getId();

                $isCommonPwd = ($userInfo->password == md5('mtnpassword')) ? TRUE : FALSE;
                $response = array(
                    'user' => $userInfo->id,
                    'token' => $sessionId,
                    'mobile_no' => $userInfo->mobile_no,
                    'common_password' => $isCommonPwd
                );

                //$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
                echo json_encode($response);

            } else {
                $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
                $email = $activationCode.'iphaderuser@mtnug.com';

                $password = 'mtnugpassword'; //Don't change this since this is the common for web and app
                $firstName = 'mtnugfisrtname';
                $lastName = 'mtnuglastname';

                $userData = array(
                    'username' => $email,
                    'email' => $email,
                    'password' => $password,
                    'type' => "USER",
                    'login_type' => "NEXVA",
                    'chap_id' => $chapId,
                    'mobile_no' => $msisdn,
                    'activation_code' => $activationCode
                );

                $userId = $user->createUser($userData);

                $userMeta = new Model_UserMeta();
                $userMeta->setEntityId($userId);
                $userMeta->FIRST_NAME = $firstName;
                $userMeta->LAST_NAME = $lastName;
                $userMeta->TELEPHONE = $msisdn;
                $userMeta->UNCLAIMED_ACCOUNT = '1';

                $sessionUser = new Zend_Session_Namespace('partner_user');
                $sessionUser->id = $userId;
                $sessionId = Zend_Session::getId();

                $response = array(
                    'user' => $userId,
                    'token' => $sessionId,
                    'mobile_no' => $msisdn,
                    'common_password' => TRUE
                );

                //$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
                echo json_encode($response);
            }
        } else {

            $this->__echoError("8020", "Please use MTN data connection. ", self::BAD_REQUEST_CODE);

        }
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
        $langCode = $headersParams['langCode'];

        //Get the parameters               
        $category = trim($this->_getParam('category'));
        $offset = trim($this->_getParam('offset', 0));
        $limit = trim($this->_getParam('limit', 10));


        //Check if Category has been provided
        if ($category === null || empty($category)) {
        
            $this->__echoError("6000", $this->getTranslatedText($langCode, '6000') ? $this->getTranslatedText($langCode, '6000') : "Category not found", self::BAD_REQUEST_CODE);
        }

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);
$deviceId=1;
        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) {

            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
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
                //$this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                
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

        //Thumbnail Dimension
        $thumbWidth = $this->_getParam('twidth', 80);
        $thumbHeight = $this->_getParam('theight', 80);

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);
$deviceId=1;
        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) {
           
           $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
            
        } else { //Get Featured Apps based on Chap and the Device
            $ApiModel = new Nexva_Api_NexApi();

            $apiCall = true;

            //Featured apps for banners
            $featuredApps = $ApiModel->featuredAppsAction($chapId, 15, $deviceId, $apiCall, $category, $thumbWidth, $thumbHeight);

            //change the thumbnail path
            if (count($featuredApps) > 0) {

                $apps = str_replace('\/', '/', json_encode($featuredApps));
                //$this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
                echo $apps;

            } 
            else{
                
               $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);   
            }
        }
    }
    
    
    
    
    public function flaggedAppsAction() {
    
    	//Validate Heder params
    	$headersParams = $this->validateHeaderParams();
    
    	$userAgent = $headersParams['userAgent'];
    	$chapId = $headersParams['chapId'];
    	$langCode = $headersParams['langCode'];
    
    	//Get the parameters
    	$category = trim($this->_getParam('category', null));
    
    	//Thumbnail Dimension
    	$thumbWidth = $this->_getParam('twidth', 80);
    	$thumbHeight = $this->_getParam('theight', 80);
    
    	//Detect the device id from thd db according to the given user agent
    	$deviceId = $this->deviceAction($userAgent);
    
    	//Check if the device was detected or not, if not retrun a message as below
    	if ($deviceId === null || empty($deviceId)) {
    		 
    		$this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
    
    	} else { //Get Featured Apps based on Chap and the Device
    		$ApiModel = new Nexva_Api_NexApi();
    
    		$apiCall = true;
    
    		//Featured apps for banners
    		$featuredApps = $ApiModel->flaggedAppsAction($chapId, 15, $deviceId, $apiCall, $category, $thumbWidth, $thumbHeight);
    
    		//change the thumbnail path
    		if (count($featuredApps) > 0) {
    
    			$apps = str_replace('\/', '/', json_encode($featuredApps));
    			//$this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
    			echo $apps;
    
    		}
    		else{
    
    			$this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
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
        $langCode = $headersParams['langCode'];
        
        //App Id
        $appId = $this->_getParam('appId', null);

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
$deviceId=1;
        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) {

            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
            
        } 
        else             
        {

            $ApiModel = new Nexva_Api_NexApi();
            //Get the details of the app
            $appDetails = $ApiModel->appDetailsById($appId, $deviceId, $screenWidth, $screenHeight, $chapLangId, $thumbWidth, $thumbHeight, $chapId);
//Nexva_Api_NexApi->appDetailsById('61413', 1, 320, 480, 1, '120', '120', '283006')
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

                /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){

                    //Zend_Debug::dump($appDetails['desc']);


                    //echo json_encode(utf8_decode($appDetails));
                    //die();
                    //$encoded_rows = array_map('utf8_encode', $appDetails);

                    //echo $apps;
                    if(1 == $chapLangId){
                        $appDetails['desc'] = preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '',$appDetails['desc']);
                    }
                    $apps = str_replace('\/', '/', json_encode($appDetails));
                    echo $apps;
                    die();
                }*/


                if(115189==$chapId){
                    if(1 == $chapLangId){
                        $appDetails['desc'] = preg_replace('/[^a-z0-9 -]+/', '',$appDetails['desc']);
                    }
                }


                $apps = str_replace('\/', '/', json_encode($appDetails));
                //$apps = str_replace('\/', '/', json_encode($appDetails, JSON_HEX_QUOT | JSON_HEX_TAG));
                //json_encode(ARRAY, JSON_HEX_QUOT | JSON_HEX_TAG);
                //json_encode($a, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

                /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){

                    echo json_last_error();

                    die();
                }*/

                //$this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);

                echo $apps;
                
            } 
            else 
            {
                $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
            }
        }
    }
    
    function my_json_encode($in){
    	$_escape = function ($str) {
    		return addcslashes($str, "\v\t\n\r\f\"\\/");
    	};
    	$out = "";
    	if (is_object($in)){
    		$class_vars = get_object_vars(($in));
    		$arr = array();
    		foreach ($class_vars as $key => $val){
    			$arr[$key] = "\"{$_escape($key)}\":\"{$val}\"";
    		}
    		$val = implode(',', $arr);
    		$out .= "{{$val}}";
    	}elseif (is_array($in)){
    		$obj = false;
    		$arr = array();
    		foreach($in as $key => $val){
    			if(!is_numeric($key)){
    				$obj = true;
    			}
    			$arr[$key] = my_json_encode($val);
    		}
    		if($obj){
    			foreach($arr AS $key => $val){
    				$arr[$key] = "\"{$_escape($key)}\":{$val}";
    			}
    			$val = implode(',', $arr);
    			$out .= "{{$val}}";
    		}else {
    			$val = implode(',', $arr);
    			$out .= "[{$val}]";
    		}
    	}elseif (is_bool($in)){
    		$out .= $in ? 'true' : 'false';
    	}elseif (is_null($in)){
    		$out .= 'null';
    	}elseif (is_string($in)){
    		$out .= "\"{$_escape($in)}\"";debug('in='.$in.', $_escape($in)='.$_escape($in).', out='.$out);
    	}else{
    		$out .= $in;
    	}
    	return "{$out}";
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
        


        //Validate Header params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
        
        if($sessionId) {
        	$userId = $this->__validateToken($sessionId);
        	if($chapId == 23045 and empty($userId)) 
        	    die();
        } else {
        	$userId = 22272;
        	if($chapId == 23045)
        		die();
        }
        

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
$deviceId=1;
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
            
            //************* Add codengo *************************
            $condengo = new Nexva_Util_Http_SendRequestCodengo;
            $condengo->send($appId);
            

            //************* Add Statistics - Download *************************
            $source = "API";
            $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');


            $model_ProductBuild = new Model_ProductBuild();
            $buildInfo = $model_ProductBuild->getBuildDetails($buildId);

            $modelQueue = new Partner_Model_Queue();

            $modelQueue->removeDownlaodedItem($userId, $chapId);
            $modelDownloadStats = new Api_Model_StatisticsDownloads();
            
            $sessionId = Zend_Session::getId();

            $rowBuild = $modelDownloadStats->checkThisStatExistWithSession($userId, $chapId, $buildId, $deviceId, $sessionId);
              
              //If the record not exist only stat will be inserted
            if($rowBuild['exist_count'] == 'no'){
                $modelDownloadStats->addDownloadStat($appId, $chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $deviceId, $sessionId);
            }
              
            
           

            /**             * **************End Statistics ******************************* */
            $downloadLink = array();
            $downloadLink['download_app'] = $buildUrl;

            /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                //echo $appId.'-'.$buildId;
                Zend_Debug::dump($downloadLink);
                die();
            }*/

            $this->getResponse()->setHeader('Content-type', 'application/json');

            //Here str_replace has been used to prevent of adding '/' when ecnoded by JSON
            //thre is a solution but ,JSON_UNESCAPED_UNICODE is only works in PHP 5.4+, http://php.net/manual/en/function.json-encode.php
            $encodedDownloadLink = str_replace('\/', '/', json_encode($downloadLink));
            //$this->loggerInstance->log('Response ::' . $encodedDownloadLink,Zend_Log::INFO);
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

        //$userId = 22272;

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $appId = $this->_getParam('appId');
        $buildId = $this->_getParam('build_Id');
        $mobileNo = $this->_getParam('mdn');
        $paymentGateway = $this->_getParam('paymentGateway');
        
        //    
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
           
            $pgType = $pgDetails->gateway_id; 
            $paymentGatewayId = $pgDetails->payment_gateway_id;

        // when there is an option to select 2 payment geteways
            if($chapId == '801844' or $chapId == '276531'){
                //Call Nexva_MobileBilling_Factory and create relevant instance
                $pgClass = Nexva_MobileBilling_Factory::createFactory($paymentGateway);
            } else {
                //Call Nexva_MobileBilling_Factory and create relevant instance
                $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
            }

            //Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
            $pgClass->addMobilePayment($chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId);

            //If MTN Iran the factory is sending the build url in an array
            if($chapId == 23045)
            {
                //Do the transaction and get the build url      
                $buildUrlArr = $pgClass->doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price);   
                $transactionId = $buildUrlArr['trans_id'];
                $buildUrl = $buildUrlArr['build_url'];
                $faultString = $buildUrlArr['message'];
                $faultCode = $buildUrlArr['fault_code'];
            }
            else{
                //Do the transaction and get the build url      
                $buildUrl = $pgClass->doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price);   
            }

        /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){                
            $downloadLink = array();
            $downloadLink['buildUrl'] = $buildUrl;
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $encodedDownloadLink = str_replace('\/', '/', json_encode($downloadLink));
            echo $encodedDownloadLink;
            die();
         }*/
         
            //Check if payment was made success, Provide the download link
            if(!empty($buildUrl) && !is_null($buildUrl)) 
            {

        //////////////////////////////////////////////////
        //
        //
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
                
                //If MTN Iran the factory is sending the build url in an array
                if($chapId == 23045)
                {
                    $downloadLink['transaction_id'] = $transactionId;
                }

                $this->getResponse()
                        ->setHeader('Content-type', 'application/json');

                //Here str_replace has been used to prevent of adding '/' when ecnoded by JSON
                //thre is a solution but ,JSON_UNESCAPED_UNICODE is only works in PHP 5.4+, http://php.net/manual/en/function.json-encode.php
                $encodedDownloadLink = str_replace('\/', '/', json_encode($downloadLink));
                //$this->loggerInstance->log('Response :: Pay app response not logged' ,Zend_Log::INFO);
                echo $encodedDownloadLink;
            } 
            else 
            {  
                $errorMsg = "Payment was unsuccessfull";
                if($chapId == 23045)
                {
                    $msg = json_encode(array("message" =>$faultString,"error_code" => $faultCode));
        
                    $this->getResponse()->setHeader('Content-type', 'application/json');
                    //$this->loggerInstance->log('Response ::' . $msg, Zend_Log::INFO);

                    echo $msg;
                    exit;
        
                }
                else{
                    $this->__echoError("4000", $this->getTranslatedText($langCode, '4000') ? $this->getTranslatedText($langCode, '4000') : $errorMsg, self::BAD_REQUEST_CODE);
                }
     
                
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

        //Mobile number no needed for YCoins/caboapps
        if($chapId != 115189 && $chapId != 136079){
            $mobileNo = $this->_getParam('mdn');
        }

        //Testing Mobile No
        //$mobileNo = '5155328687';
        //$userAgent = "Mozilla/5.0 (Linux; U; Android 1.5; fr-fr; Galaxy Build/CUPCAKE) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2";
        //$chapId = 8056;
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
        
        //Mobile number no needed for YCoins/caboapps
        if($chapId != 115189 && $chapId != 136079){
            //Check if Chap Id has been provided
            if ($mobileNo === null || empty($mobileNo)) 
            {
                $this->__echoError("5000", $this->getTranslatedText($langCode, '5000') ? $this->getTranslatedText($langCode, '5000') : "Mobile Number not found", self::BAD_REQUEST_CODE);
            }
        }


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
            
         //   $capable = true;

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
                //$this->loggerInstance->log('Response :: Pay app response not logged' ,Zend_Log::INFO);
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
        $langCode = $headersParams['langCode'];

        //Get chapter site language id
        $userModel = new Model_User();
        $chapLanguageId = $userModel->getUserLanguage($chapId);
        
        $languageIdGet = $this->_getParam('language_id');
        
        if(isset($languageIdGet)){
            $chapLanguageId = $languageIdGet;
        }
        //Get all categories
        $ApiModel = new Nexva_Api_NexApi();
        $allCategories = $ApiModel->categoryAction($chapId, $chapLanguageId);
        //$cat_list = array();
        //$cat_list["category"]=$allCategories;
        if (count($allCategories) > 0) 
        {
            $this->getResponse()
                    ->setHeader('Content-type', 'application/json');
     
            echo str_replace('\/','/',json_encode($allCategories));
            //$this->loggerInstance->log('Response ::' . json_encode($allCategories),Zend_Log::INFO);         
	    
        } 
        else 
        {
            $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
        }
    }
    
    public function categoryMtsAction() 
    {
        
        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
        
        //Get chapter site language id
        $userModel = new Model_User();
        $chapLanguageId = $userModel->getUserLanguage($chapId);
        
        $languageIdGet = $this->_request->getParam("language_id");
       
        if(isset($languageIdGet)){
            $chapLanguageId = $languageIdGet;
        }
        //Get all categories
        $ApiModel = new Nexva_Api_NexApi();
        $allCategories = $ApiModel->categoryMtsAction($chapId, $chapLanguageId);
        //$cat_list = array();
        //$cat_list["category"]=$allCategories;
        if (count($allCategories) > 0) 
        {
            $this->getResponse()
                    ->setHeader('Content-type', 'application/json');
     
            echo str_replace('\/','/',json_encode($allCategories));
            //$this->loggerInstance->log('Response ::' . json_encode($allCategories),Zend_Log::INFO);         
	    
        } 
        else 
        {
            $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
        }
    }

    /**
     * 
     * Returns the deviced id from the DB based on the User Agent.
     * @param $userAgent User Agent
     * returns $deviceId
     */
    protected function deviceAction($userAgent) {

        /*
        //Iniate device detection using Device detection adapter
        $deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();

        //Detect the device
        $exactMatch = $deviceDetector->detectDeviceByUserAgent($userAgent);

        //Device barand name
        $brandName = $deviceDetector->getDeviceAttribute('brand_name', 'product_info');

        //Get the Device ID of nexva db
        $deviceId = $deviceDetector->getNexvaDeviceId();
        */
        
        $session = new Zend_Session_Namespace("devices_new");
        $deviceId = $session->deviceId;
        
        if(!isset($deviceId)) {
        
        
        $deviceDetection =  Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
        $deviceInfo = $deviceDetection->getNexvaDeviceId($_SERVER['HTTP_USER_AGENT']);
        //If this is not a wireless device redirect to the main site
        
        $deviceId = $deviceInfo->id;
        $session->deviceId = $deviceId;
        }
        
        
/*         if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
        	Zend_Debug::dump($deviceId,'ddd');die();
        } */

        return $deviceId;
    }

    protected function echoJson($json, $halt=1) 
    {
        $this->getResponse()
                ->setHeader('Content-type', 'application/json');
        //$this->loggerInstance->log('Response ::' . json_encode($json),Zend_Log::INFO);
        
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
//print_r($headersParams);exit;

        //We need only User-Agent, Chap-Id
        
        if(!empty($headersParams['User-Agent'])) {
        $userAgent = !empty($headersParams['User-Agent']) ? $headersParams['User-Agent'] : '';
        $chapId = !empty($headersParams['Chap-Id']) ? $headersParams['Chap-Id'] : '';
        
        }
        
        if( !empty($headersParams['USER-AGENT'])) {
        
        $userAgent = !empty($headersParams['USER-AGENT']) ? $headersParams['USER-AGENT'] : '';
        $chapId = !empty($headersParams['CHAP-ID']) ? $headersParams['CHAP-ID'] : '';
        
        }
        if(!empty($headersParams['user-agent'])) {
        $userAgent = !empty($headersParams['user-agent']) ? $headersParams['user-agent'] : '';
        $chapId = !empty($headersParams['chap-id']) ? $headersParams['chap-id'] : '';
        
        }
        //Get user language ocde
        $modelUserLanguages = new Model_LanguageUsers;
        $langCode = $modelUserLanguages->getLanguageCodeByChap($chapId); 
        

      
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
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId); //Jyothi : getting serviceprovider class as per chap id 
         
        $pgType = $pgDetails->gateway_id; //Jyothi : getting service provider id
         
        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
         //echo($pgClass);die();
        //$message = 'Please use this verification code '.$activationCode.' to complete your registration.';
        $message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.' to complete your registration.';

        
        if($chapId == 80184)
        	$message = "Y'ello. Please use this verification code $activationCode to complete your registration on the MTN AppStore. Thank you.";

        
        
     	if($chapId == 274515)
            $message =  "Veuillez utiliser ce code de vÃ©rification $activationCode pour terminer votre enregistrement.";
     	

     	if($chapId == 283006)
     	    $message =  "Koristi ovu verifikacionu Ã…Â¡ifru $activationCode da zavrÃ…Â¡iÃ…Â¡ proces registracije.";
        
        
        
        $pgClass->sendSms($mobileNumber, $message, $chapId); //Jyothi : sending sms with service provider matched with the chapid

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
        
        //$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
        
        echo json_encode($response);
    }

     /**
     * User Registration without verification
     * @param firstName
     * @param lastName
     * @param mobileNumber
     * @param email
     * @param password
     * returns activation code,user id, success_code as a JSON response
     */
    public function registerNonVerifiedAction()
    {
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
        
        $userData = array(
            'username' => $email,
            'email' => $email,
            'password' => $password,
            'type' => "USER",
            'login_type' => "NEXVA",
            'chap_id' => $chapId,
            'mobile_no' => $mobileNumber,
            'status' => 1,
            'activation_code' => ''
        );

        $userId = $user->createUser($userData);

        $userMeta = new Model_UserMeta();
        $userMeta->setEntityId($userId);
        $userMeta->FIRST_NAME = $firstName;
        $userMeta->LAST_NAME = $lastName;
        $userMeta->TELEPHONE = $mobileNumber;
        $userMeta->UNCLAIMED_ACCOUNT = '1';
        
        $response = array(
                            'user' => $userId,
                            'success_code' => '2222'
                        );
        
        //$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
        
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
        $langCode = $headersParams['langCode'];
        
        //Get the parameters               
        $userId = trim($this->_getParam('userId'));
        $activationCode = trim($this->_getParam('activationCode'));
       
        //Check if User Id has been provided
        if ($userId === null || empty($userId)) 
        {   
            echo $userId.'hh'; print_r($this->_getParam('activationCode'));
            $this->__echoError("8001", $this->getTranslatedText($langCode, '8001') ? $this->getTranslatedText($langCode, '8001') : "User Id not found", self::BAD_REQUEST_CODE);
        }
        //Check if Activaion Code has been provided
        if ($activationCode === null || empty($activationCode)) 
        {
            $this->__echoError("8002", $this->getTranslatedText($langCode, '8002') ? $this->getTranslatedText($langCode, '8002') : "Verification Code not found", self::BAD_REQUEST_CODE);
        }        
        
        $userModel = new Api_Model_Users();
        //Check if combination exists
        $recordCount = $userModel->getUserCountById($chapId, $userId, $activationCode);
        
        if(empty($recordCount) || is_null($recordCount) || $recordCount <= 0)
        {
            $this->__echoError("8003", $this->getTranslatedText($langCode, '8003') ? $this->getTranslatedText($langCode, '8003') : "Combination does not match", self::BAD_REQUEST_CODE);
        } 
        
        $status = 1;
        
        //update the status
        if($userModel->updateVerificationStatus($userId, $status))
        {
            $userInfo = $userModel->getUserById($userId);
            // send sms start
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
             
            $pgType = $pgDetails->payment_gateway_id;
             
            $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
             
            $message = $this->getTranslatedText($langCode, '10003') ? $this->getTranslatedText($langCode, '10003') : 'You have completed your registration successfully.';
            
            //$message =  'You have completed your registration successfully.';
            if($chapId == 80184)
                $message = "Y'ello. You have completed your registration to the MTN Appstore successfully. Thank you.";

            
            
            if($chapId == 274515)
            	$message =  "Vous avez terminÃ© votre enregistrement avec succÃ¨s.";
           

            if($chapId == 283006)
                $message =  "Proces registracije je uspeÅ¡no zavrÅ¡en.";
            
            
            if($chapId == 585474)
                $message =  "Vous avez change votre mot de passe avec succes.";
             
                         
            $pgClass->sendSms($userInfo->mobile_no, $message, $chapId);
            // send sms end
            
            $response = array(
                                'user' => $userId,
                                'message' => $this->getTranslatedText($langCode, '2222') ? $this->getTranslatedText($langCode, '2222') : 'User Verified Successfully',
                                'success_code' => '2222'
                            );
            
            //$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);        
            echo json_encode($response);            

        }
        else
        {
            $this->__echoError("8004", $this->getTranslatedText($langCode, '8004') ? $this->getTranslatedText($langCode, '8004') : "Verification has been done already", self::BAD_REQUEST_CODE);
        }
    }
    
    public function verifyUserPassAction()
    {
    	$headersParams = $this->validateHeaderParams();
    	$chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
    
    	//Get the parameters
    	$userId = trim($this->_getParam('userId'));
    	$activationCode = trim($this->_getParam('activationCode'));
    
    	//Check if User Id has been provided
    	if ($userId === null || empty($userId))
    	{
    		$this->__echoError("8001", $this->getTranslatedText($langCode, '8001') ? $this->getTranslatedText($langCode, '8001') : "User Id not found", self::BAD_REQUEST_CODE);
    	}
    	//Check if Activaion Code has been provided
    	if ($activationCode === null || empty($activationCode))
    	{
    		$this->__echoError("8002", $this->getTranslatedText($langCode, '8002') ? $this->getTranslatedText($langCode, '8002') : "Verification Code not found", self::BAD_REQUEST_CODE);
    	}
    
    	$userModel = new Api_Model_Users();
    	//Check if combination exists
    	$recordCount = $userModel->getUserCountById($chapId, $userId, $activationCode);
    
    	if(empty($recordCount) || is_null($recordCount) || $recordCount <= 0)
    	{
    		$this->__echoError("8003", $this->getTranslatedText($langCode, '8003') ? $this->getTranslatedText($langCode, '8003') : "Combination does not match", self::BAD_REQUEST_CODE);
    	}
    
    	$status = 1;
    	
    	$response = array(
    			'user' => $userId,
    			'message' => $this->getTranslatedText($langCode, '2222') ? $this->getTranslatedText($langCode, '2222') : 'User Verified Successfully',
    			'success_code' => '2222'
    	);
    	
    	//$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
    	echo json_encode($response);
   	
    }
    
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
                // $this->__echoError("8008", "User has not verified the account", self::BAD_REQUEST_CODE); 
                
                 $msg = json_encode(
                                        array(
                                                "message" => $this->getTranslatedText($langCode, '8008') ? $this->getTranslatedText($langCode, '8008') : "User has not verified the account",
                                                "error_code" => "8008",
                                                "user" => $tmpUser->id
                                             )
                                    );
        
                 $this->getResponse()->setHeader('Content-type', 'application/json');
                 //$this->loggerInstance->log('Response ::' . $msg, Zend_Log::INFO);

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
        
        //$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
        echo json_encode($response);
    }

    public function resetAction() 
    {
        $this->validateHeaderParams();
        $langCode = $headersParams['langCode'];

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
        if (!empty($errors)) 
        {
            $temp = array("message" => $this->getTranslatedText($langCode, '8000') ? $this->getTranslatedText($langCode, '8000') : "Data validation failed",
                                "error_details" => $errors);
            
            $this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
        }

        $user->resetPassword($userRow->id, $password);
        //$this->loggerInstance->log('Response ::' . json_encode(array("user" => $userRow->id)),Zend_Log::INFO);
        echo json_encode(array("user" => $userRow->id));
    }
    
    
    public function resetPasswordAction()
    {
    	$this->validateHeaderParams();
    	$langCode = $headersParams['langCode'];
    
    	$password = $this->_request->password;
    	$mobileNumber = $this->_request->mobileNumber;
    
    	// Store Mobile number from db.
    	$usrMDN = '';
    
    	// Get user object for given email.
    	$user = new Model_User();
    	//$userRow = $user->getUserByEmail($username);
    
    	$userObj = new Api_Model_Users();
    	$userRow = $userObj->getUserByphone($mobileNumber);
    	

    
    	// META row for Mobile number verification
    	//$userMeta = new Model_UserMeta();
    
    
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
    	if (!empty($errors))
    	{
    		$temp = array("message" => $this->getTranslatedText($langCode, '8000') ? $this->getTranslatedText($langCode, '8000') : "Data validation failed",
    				"error_details" => $errors);
    
    		$this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
    	}
    
    	$user->resetPassword($userRow->id, $password);
    	//$this->loggerInstance->log('Response ::' . json_encode(array("user" => $userRow->id)),Zend_Log::INFO);
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
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
        
        //Get the parameters               
        $userId = trim($this->_getParam('userId'));
        $mobileNumber = trim($this->_getParam('mobileNumber'));
        
        //Check if User Id has been provided
        if ($userId === null || empty($userId)) 
        {
            $this->__echoError("8001", $this->getTranslatedText($langCode, '8001') ? $this->getTranslatedText($langCode, '8001') : "User Id not found", self::BAD_REQUEST_CODE);        
        }
        //Check if Activaion Code has been provided
        if ($mobileNumber === null || empty($mobileNumber) || (strlen($mobileNumber) < 10)) 
        {
            $this->__echoError("8010", $this->getTranslatedText($langCode, '8010') ? $this->getTranslatedText($langCode, '8010') : "Invalid Mobile Number", self::BAD_REQUEST_CODE);
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
             
            //$message = 'Please use this verification code '.$activationCode.' to verify your mobile number.';
            $message = $this->getTranslatedText($langCode, '10001', $activationCode) ? $this->getTranslatedText($langCode, '10001', $activationCode) : 'Please use this verification code '.$activationCode.' to verify your mobile number.';

            
            if($chapId == 80184) 
            	$message = "Y'ello. Please use this verification code $activationCode to verify your mobile number on the MTN AppStore. Thank you.";

            
            
            if($chapId == 274515)
           	    $message =  "Veuillez utiliser ce code $activationCode pour vÃ©rifier votre numÃ©ro de tÃ©lÃ©phone.";
           
            
            $pgClass->sendSms($mobileNumber, $message, $chapId);
            
        
            $response = array(
                                'user' => $userId, 
                                'activation_code' => $activationCode, 
                                'success_code' => '1111'
                            );
            
            //$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);        
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
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
        
        //Get the parameters               
        $userId = trim($this->_getParam('userId'));
        //$mobileNumber = trim($this->_getParam('mobileNumber'));
        
        //Check if User Id has been provided
        if ($userId === null || empty($userId)) 
        {
            $this->__echoError("8001", $this->getTranslatedText($langCode, '8001') ? $this->getTranslatedText($langCode, '8001') : "User Id not found", self::BAD_REQUEST_CODE);         
        }
              
        
        if($chapId ==  80184 || $chapId ==  276531 ) {
            $activationCode = $this->__random_numbers(4);
        } else {
            $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
        }
        $status = 0;
        
        $userModel = new Api_Model_Users();
        
        $mobileNumber = $userModel->getUserMobileById($userId);
        
        //Check if mobile number is given
        if ($mobileNumber === null || empty($mobileNumber)) 
        {
            $this->__echoError("8011", $this->getTranslatedText($langCode, '8011') ? $this->getTranslatedText($langCode, '8011') : "Mobile Number Not Registered", self::BAD_REQUEST_CODE);
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
            	
            //$message = 'Please use this verification code '.$activationCode.' to complete your registration.';
            if($chapId == 274515) {
                $message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.'  to complete your registration on the MTN AppStore. Thank you.';
            } else {
                $message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.' to complete your registration.';
            }
          
            
            
            if($chapId == 80184) 
            	$message = "Y'ello. Please use this verification code $activationCode. to complete your registration on the MTN AppStore. Thank you.";
            	
            if($chapId == 274515)
            	$message =  "Veuillez utiliser ce code de vÃ©rification $activationCode pour terminer votre enregistrement.";
            
//            $pgClass->sendSms($mobileNumber, $message, $chapId);
            // send sms end
        
            $response = array(
                                'user' => $userId, 
                                'activation_code' => $activationCode, 
                                'success_code' => '1111'
                            );
            
            //$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);        
            echo json_encode($response);            

        }
    }
    
    public function resendActivationOrangeAction()
    {
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
    
        //Get the parameters
        $userId = trim($this->_getParam('userId'));
        //$mobileNumber = trim($this->_getParam('mobileNumber'));
    
        //Check if User Id has been provided
        if ($userId === null || empty($userId))
        {
            $this->__echoError("8001", $this->getTranslatedText($langCode, '8001') ? $this->getTranslatedText($langCode, '8001') : "User Id not found", self::BAD_REQUEST_CODE);
        }
    
        
        $activationCode = $this->__random_numbers(6);
        
        $status = 0;
    
        $userModel = new Api_Model_Users();
    
        $mobileNumber = $userModel->getUserMobileById($userId);
    
        //Check if mobile number is given
        if ($mobileNumber === null || empty($mobileNumber))
        {
            $this->__echoError("8011", $this->getTranslatedText($langCode, '8011') ? $this->getTranslatedText($langCode, '8011') : "Mobile Number Not Registered", self::BAD_REQUEST_CODE);
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
             
            //$message = 'Please use this verification code '.$activationCode.' to complete your registration.';
            if($chapId == 274515) {
                $message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.'  to complete your registration on the MTN AppStore. Thank you.';
            } else {
                $message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.' to complete your registration.';
            }
    
    
    
            if($chapId == 80184)
                $message = "Y'ello. Please use this verification code $activationCode. to complete your registration on the MTN AppStore. Thank you.";
             
            if($chapId == 274515)
                $message =  "Veuillez utiliser ce code de vÃ©rification $activationCode pour terminer votre enregistrement.";
    
            $pgClass->sendSms($mobileNumber, $message, $chapId);
            // send sms end
            

            $user = new Model_User();
            
            $user->resetPassword($userId, $activationCode);
    
            $response = array(
                'user' => $userId,
                'activation_code' => $activationCode,
                'success_code' => '1111'
            );
    
            //$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
            echo json_encode($response);
    
        }
    }
    
    
    
    
    public function resendActivationByMobileNoAction()
    {
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
    
        //Get the parameters
     
        $mobileNumber = trim($this->_getParam('mobileNumber'));
        
        $userObj = new Api_Model_Users();
        $userRow = $userObj->getUserByMobileNo($mobileNumber);
        
        $userId =   $userRow->id;
        
    
        //Check if User Id has been provided
        if ($userId === null || empty($userId))
        {
            $this->__echoError("8001", $this->getTranslatedText($langCode, '8001') ? $this->getTranslatedText($langCode, '8001') : "User Id not found", self::BAD_REQUEST_CODE);
        }
    
    
        if($chapId ==  80184 || $chapId ==  276531 || $chapId ==  585474 ) {
           $activationCode = $this->__random_numbers(6);
        } else {
            $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
        }
        
        
        if( $chapId ==  585474 ) {
            $activationCode = $this->__random_numbers(4);
        } 
        
        $status = 0;
    
        $userModel = new Api_Model_Users();
    
        $mobileNumber = $userModel->getUserMobileById($userId);
    
        //Check if mobile number is given
        if ($mobileNumber === null || empty($mobileNumber))
        {
            $this->__echoError("8011", $this->getTranslatedText($langCode, '8011') ? $this->getTranslatedText($langCode, '8011') : "Mobile Number Not Registered", self::BAD_REQUEST_CODE);
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
             
            //$message = 'Please use this verification code '.$activationCode.' to complete your registration.';
            if($chapId == 274515) {
                $message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.'  to complete your registration on the MTN AppStore. Thank you.';
            } else {
                $message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.' to complete your registration.';
            }
    
            if($chapId == 80184)
                $message = "Y'ello. Please use this verification code $activationCode. to complete your registration on the MTN AppStore. Thank you.";
             
            if($chapId == 274515)
                $message =  "Veuillez utiliser ce code de vÃ©rification $activationCode pour terminer votre enregistrement.";
            

			if($chapId == 585474)
			    $message =  "Veuillez utiliser ce code de verification $activationCode pour confirmer votre inscription.";
             
    
            $pgClass->sendSms($mobileNumber, $message, $chapId);
            // send sms end
    
            $response = array(
                'user' => $userId,
                'activation_code' => $activationCode,
                'success_code' => '1111'
            );
    
            //$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
            echo json_encode($response);
    
        }
    }
    
    
    public function resendActivationByMobileNoOrangeAction()
    {
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
    
        //Get the parameters
         
        $mobileNumber = trim($this->_getParam('mobileNumber'));
    
        $userObj = new Api_Model_Users();
        $userRow = $userObj->getUserByMobileNo($mobileNumber);
    
        $userId =   $userRow->id;
    
    
        //Check if User Id has been provided
        if ($userId === null || empty($userId))
        {
            $this->__echoError("8001", $this->getTranslatedText($langCode, '8001') ? $this->getTranslatedText($langCode, '8001') : "User Id not found", self::BAD_REQUEST_CODE);
        }
    
    
        if($chapId ==  80184 || $chapId ==  276531 || $chapId ==  585474 ) {
            $activationCode = $this->__random_numbers(4);
        } else {
            $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
        }
        $status = 0;
    
        $userModel = new Api_Model_Users();
    
        $mobileNumber = $userModel->getUserMobileById($userId);
    
        //Check if mobile number is given
        if ($mobileNumber === null || empty($mobileNumber))
        {
            $this->__echoError("8011", $this->getTranslatedText($langCode, '8011') ? $this->getTranslatedText($langCode, '8011') : "Mobile Number Not Registered", self::BAD_REQUEST_CODE);
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
             
            //$message = 'Please use this verification code '.$activationCode.' to complete your registration.';
            if($chapId == 274515) {
                $message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.'  to complete your registration on the MTN AppStore. Thank you.';
            } else {
                $message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.' to complete your registration.';
            }
    
            if($chapId == 80184)
                $message = "Y'ello. Please use this verification code $activationCode. to complete your registration on the MTN AppStore. Thank you.";
             
            if($chapId == 274515)
                $message =  "Veuillez utiliser ce code de vÃ©rification $activationCode pour terminer votre enregistrement.";
    
    
            if($chapId == 585474)
                $message =  "Veuillez utiliser ce code de verification $activationCode pour confirmer votre inscription.";
             
    
          //  $pgClass->sendSms($mobileNumber, $message, $chapId);
            // send sms end
    
            $response = array(
                'user' => $userId,
                'activation_code' => $activationCode,
                'success_code' => '1111'
            );
    
            //$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
            echo json_encode($response);
    
        }
    }
    
    
    public function listUpdatesOrnageAction() {
    
        
        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
        
        
        $productId = $this->_getParam('product_id', 0);
        $languageId = $this->_getParam('language_id', 0);
        $date = $this->_getParam('date', 0);
    
        $limit = $this->_getParam('limit', 10);
        $offset = $this->_getParam('offset', 0);
    
        // retrive all the downloaded apps
        $modelDownloadStats = new Api_Model_StatisticsDownloads();
        $model_ProductBuild = new Model_ProductBuild();
        $nexApi = new Nexva_Api_NexApi();
    
        $deviceId = $this->deviceAction($userAgent);
    
        $downloadedProducts = $model_ProductBuild->listProductBuildUpdated($productId, $languageId, $date);
    
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
    
    public function listUpdatesAction() {

        $userId = $this->__validateToken($this->_getParam('token', 0));

        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

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
        $langCode = $headersParams['langCode'];

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
        $langCode = $headersParams['langCode'];

        // retrive all the downloaded apps 
        $modelDownloadStats = new Api_Model_StatisticsDownloads();
        $model_ProductBuild = new Model_ProductBuild();

        $downloadedProducts = $modelDownloadStats->getDownloadedBuilds($userId, $limit, $offset);

        $deviceId = $this->deviceAction($userAgent);
$deviceId=1;
        $nexApi = new Nexva_Api_NexApi();
        $downloadedProductInfo = $nexApi->getProductDetails($downloadedProducts, $deviceId, true);

        if ($downloadedProductInfo) {

            if (count($downloadedProductInfo) > 0) {
                
                $this->__printArrayOfDataJson($downloadedProductInfo);
            }
        } else {

            $this->__dataNotFound();
        }
    }


public function myAppsQelasyAction() {

        //$userId = $this->__validateToken($this->_getParam('token', 0));
		$userId = 111531;
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

        $nexApi = new Nexva_Api_NexApi();
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

        if ($categoryId == 0) {

            $this->__dataNotFound();
            
        }

        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $deviceId = $this->deviceAction($userAgent);

        $chapProduct = new Api_Model_ChapProducts();
        $topProducts = $chapProduct->getTopCategoryProducts($chapId, $deviceId, $categoryId, $limit, $offset);

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
        $langCode = $headersParams['langCode'];

        $deviceId = $this->deviceAction($userAgent);
$deviceId=1;
        $chapProduct = new Api_Model_ChapProducts();
        $topProducts = $chapProduct->getTopProductsByDevice($chapId, $deviceId, true, $limit, $offset);

        if ($topProducts) {

            $nexApi = new Nexva_Api_NexApi();
            $productInfo = $nexApi->getProductDetails($topProducts, $deviceId, true,'', '', '', $chapId);

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

        $simpleSearch = trim($this->_getParam('simple', true));
        
        $headersParams = $this->validateHeaderParams();
        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];//echo $chapId;die();
        $langCode = $headersParams['langCode'];

        $deviceId = $this->deviceAction($userAgent);
$deviceId=1;
        //Get chapter site language id
        $userModel = new Model_User();
        $chapLanguageId = $userModel->getUserLanguage($chapId);

        $productsModel = new Model_Product();

        //If the chap language is not english first search the keyword in PRODUCT_META table.
        $countPaginator = NULL;
        if($chapLanguageId != 1){

            $searchQry = $productsModel->getSearchQueryChapByLangId($keyword, $deviceId, $simpleSearch, $chapId, $chapLanguageId);

            /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                Zend_Debug::dump($searchQry);die();
            }*/

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

            $nexApi = new Nexva_Api_NexApi();

            $productsDisplay = $nexApi->getProductDetails($products, $deviceId, true, $s3file = false, $thumbWidth = NULL , $thumbHeight = NULL, $chapId);

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
          // $string = strtr($productMeta->FULL_DESCRIPTION, "ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½", "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn'");

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

        $headersParams = $this->validateHeaderParams();
        $langCode = $headersParams['langCode'];
       
        if (!$token) {
            
            $this->__echoError("9000", $this->getTranslatedText($langCode, '9000') ? $this->getTranslatedText($langCode, '9000') : "Request not authenticated", self::BAD_REQUEST_CODE);
        }
        
//        Zend_Session::setId($token);
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

    /**
     * 
     * print the apps data in json format
     * @param $apps array 
     */
    private function __printArrayOfDataJson($apps) 
    {
        $apps = str_replace('\/', '/', json_encode($apps));
        //$this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
        echo $apps;
        exit;
    }
    
    private function __echoError($errNo, $errMsg, $httpResponseCode)
    {
        $msg = json_encode(array("message" =>$errMsg,"error_code" => $errNo));
        
        $this->getResponse()->setHeader('Content-type', 'application/json');
        //$this->loggerInstance->log('Response ::' . $msg, Zend_Log::INFO);
        
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
        $langCode = $headersParams['langCode'];

        //Get the parameters               
        $category = trim($this->_getParam('category', null));

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);
$deviceId=1;
        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) 
        {            
            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
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
                //$this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
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
        $langCode = $headersParams['langCode'];

        //Get the parameters               
        $category = trim($this->_getParam('category', null));

        //Thumbnail Dimension
        $thumbWidth = $this->_getParam('twidth', 80);
        $thumbHeight = $this->_getParam('theight', 80);

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);
$deviceId=1;
        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) 
        {            
            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
        } 
        else 
        { //Get Featured Apps based on Chap and the Device
            
            $ApiModel = new Nexva_Api_NexApi();

            $apiCall = true;

            //Featured apps for banners
            $freeApps = $ApiModel->freeAppsAction($chapId, 15, $deviceId, $apiCall, $category,$thumbWidth, $thumbHeight);

            //change the thumbnail path
            if (count($freeApps) > 0) 
            {
                $apps = str_replace('\/', '/', json_encode($freeApps));
                //$this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
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
        $thumbWidth = $this->_getParam('twidth', 80);
        $thumbHeight = $this->_getParam('theight', 80);

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);
$deviceId=1;

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) 
        {            
            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
        } 
        else 
        { //Get Most rated Apps based on Chap and the Device
            
            $ApiModel = new Nexva_Api_NexApi();

            $apiCall = true;

            //Featured apps for banners
            $freeApps = $ApiModel->topRatedAppsAction($chapId, 15, $deviceId, $apiCall, $category,$thumbWidth,$thumbHeight);

            //change the thumbnail path
            if (count($freeApps) > 0) 
            {
                $apps = str_replace('\/', '/', json_encode($freeApps));
                //$this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
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

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        //Get the parameters               
        $category = trim($this->_getParam('category', null));
        $thumbWidth = $this->_getParam('twidth', 80);
        $thumbHeight = $this->_getParam('theight', 80);

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);
$deviceId=1;
        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) 
        {            
            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
        } 
        else 
        { //Get Featured Apps based on Chap and the Device
            
            $ApiModel = new Nexva_Api_NexApi();

            $apiCall = true;

            //Featured apps for banners
            $premiumApps = $ApiModel->paidAppsAction($chapId, 15, $deviceId, $apiCall, $category, $thumbWidth, $thumbHeight);
            //print_r($premiumApps);die();
            //change the thumbnail path
            if (count($premiumApps) > 0) 
            {
                $apps = str_replace('\/', '/', json_encode($premiumApps));
                //$this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
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

        //Thumbnail Dimension
        $thumbWidth = $this->_getParam('twidth', 80);
        $thumbHeight = $this->_getParam('theight', 80);

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);
        $deviceId=1;
        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) 
        {               
            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
        } 
        else 
        { //Get Featured Apps based on Chap and the Device
            
            $ApiModel = new Nexva_Api_NexApi();

            $apiCall = true;

            //Featured apps for banners
            $newestApps = $ApiModel->newestAppsAction($chapId, 15, $deviceId, $apiCall, $category, $thumbWidth, $thumbHeight);

            //change the thumbnail path
            if (count($newestApps) > 0) 
            {
                $apps = str_replace('\/', '/', json_encode($newestApps));
                //$this->loggerInstance->log('Response ::' . $apps,Zend_Log::INFO);
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
        $langCode = $headersParams['langCode'];
       
        $ApiModel = new Nexva_Api_NexApi();

        //Featured apps for banners
        $pages = $ApiModel->getTextPagesAction($chapId);

        //change the thumbnail path
        if (count($pages) > 0) 
        {
            $pages = str_replace('\/', '/', json_encode($pages));
            //$this->loggerInstance->log('Response ::' . $pages, Zend_Log::INFO);
            echo $pages;
        } 
        else 
        {
            $this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
        }
       
    }
    
    
    public function validatePasswordAction()
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
    		$this->__echoError("8006", $this->getTranslatedText($langCode, '8006') ? $this->getTranslatedText($langCode, '8006') : "Emptry password", self::BAD_REQUEST_CODE);
    	}

    	$userObj = new Api_Model_Users();
    	$tmpUser = $userObj->getUserByEmail($username);
    
    	if(is_null($tmpUser))
    	{
                $this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid username", self::BAD_REQUEST_CODE);
    	}
    	if ($tmpUser->password != md5($password))
    	{
                $this->__echoError("8012", $this->getTranslatedText($langCode, '8012') ? $this->getTranslatedText($langCode, '8012') : "Invalid password", self::BAD_REQUEST_CODE);
    			
    	} else {
    	    
    	    $response = array(
    	    		'status' => 1,
    	    		'mobile_no' => $tmpUser->mobile_no);
    	     
    	    //$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
    	    echo json_encode($response);
    	    
    	    
    	}
   
    	
    }
    
    
    public function validatePasswordByPhoneAction()
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
    		$this->__echoError("8006", $this->getTranslatedText($langCode, '8006') ? $this->getTranslatedText($langCode, '8006') : "Emptry password", self::BAD_REQUEST_CODE);
    	}
    
    	$userObj = new Api_Model_Users();
    	$tmpUser = $userObj->getUserByphone($username);
    
    	if(is_null($tmpUser))
    	{
    		$this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid username", self::BAD_REQUEST_CODE);
    	}
    	if ($tmpUser->password != md5($password))
    	{
    		$this->__echoError("8012", $this->getTranslatedText($langCode, '8012') ? $this->getTranslatedText($langCode, '8012') : "Invalid password", self::BAD_REQUEST_CODE);
    		 
    	} else {
    			
    		$response = array(
    				'status' => 1,
    				'mobile_no' => $tmpUser->mobile_no);
    
    		//$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
    		echo json_encode($response);
    			
    			
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
        $langCode = $headersParams['langCode'];

        $appId = $this->_getParam('appId');
        $buildId = $this->_getParam('build_Id');
        $mobileNo = $this->_getParam('mdn');

        if ($appId === null || empty($appId)) 
        {            
            $this->__echoError("1001", $this->getTranslatedText($langCode, '1001') ? $this->getTranslatedText($langCode, '1001') : "App Id not found", self::BAD_REQUEST_CODE);
        }
        //Check if Chap Id has been provided
        if ($buildId === null || empty($buildId)) {

            $this->__echoError("1002", $this->getTranslatedText($langCode, '1002') ? $this->getTranslatedText($langCode, '1002') : "Build Id not found", self::BAD_REQUEST_CODE);
        }
        //Check if Chap Id has been provided
        if ($mobileNo === null || empty($mobileNo)) {

            $this->__echoError("5000", $this->getTranslatedText($langCode, '5000') ? $this->getTranslatedText($langCode, '5000') : "Mobile Number not found", self::BAD_REQUEST_CODE);
        }


        //Check if the app belongs to the CHAP
        $chapProductModel = new Api_Model_ChapProducts();
        $appCount = $chapProductModel->getProductCountByChap($chapId, $appId);

        if ($appCount == 0) {
            
                $this->__echoError("1003", $this->getTranslatedText($langCode, '1003') ? $this->getTranslatedText($langCode, '1003') : "App does not belong to this partner", self::BAD_REQUEST_CODE);
        }

        /******************************************************************* */

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId)) {

           $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
           
        } else {

            //get the app details
            $productModel = new Api_Model_Products();
            $appDetails = $productModel->getProductDetailsbyId($appId);

            //Check if app details available
            if (is_null($appDetails)) {
                
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
                //$this->loggerInstance->log('Response :: Pay app response not logged' ,Zend_Log::INFO);
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

    
    public function fogotpasswordrequestAction()
    {    	
    	$headersParams = $this->validateHeaderParams();
    	$chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
            	
    	$email = $this->_request->email;
    	
    	// Input field validation.
    	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
        {
            $errors[] = 'Invalid email';
            $this->__echoError("2001", $this->getTranslatedText($langCode, '2001') ? $this->getTranslatedText($langCode, '2001') : "Invalid email.", self::BAD_REQUEST_CODE);
    	}
    
    	$userModel = new Api_Model_Users();
    	$tmpUser = $userModel->getUserByEmail($email);
    	
    	if(is_null($tmpUser))
    	{
            $this->__echoError("8001", $this->getTranslatedText($langCode, '8001') ? $this->getTranslatedText($langCode, '8001') : "User Id Not Found", self::BAD_REQUEST_CODE);
    	}
    
       if($chapId ==  80184 || $chapId ==  276531) {
            $activationCode = $this->__random_numbers(4);
        } else {
            $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
        }
    	$status = 1;
    
    	$mobileNumber = $tmpUser->mobile_no;
    
    	//Check if mobile number is given
    	if ($mobileNumber === null || empty($mobileNumber))
    	{
                $this->__echoError("8011", $this->getTranslatedText($langCode, '8011') ? $this->getTranslatedText($langCode, '8011') : "Mobile Number Not Registered", self::BAD_REQUEST_CODE);
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
    	    
    	    //$message = 'Please use this verification code '.$activationCode.' to complete the password resetting process.';
            $message = $this->getTranslatedText($langCode, '10002', $activationCode) ? $this->getTranslatedText($langCode, '10002', $activationCode) : 'Please use this verification code '.$activationCode.' to complete the password resetting process.';
    	    
            
            
            if($chapId == 80184) 
            	$message = "Y'ello. Please use this verification code $activationCode to  complete the password resetting process on the MTN AppStore. Thank you.";
     
            
            if($chapId == 274515)
            	$message =  "Veuillez utiliser ce code de vÃ©rification $activationCode pour terminer le processus de rÃ©initialisation du mot de passe.";
            
            
    	    $pgClass->sendSms($mobileNumber, $message, $chapId);

    		$response = array(
                                    'user' => $tmpUser->id,
                                    'mobile_no' => $mobileNumber,
                                    'activation_code' => $activationCode,
                                    'success_code' => '1111'
                                );
    
    		//$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
    		echo json_encode($response);
    	}
    }
    
    
    public function fogotpasswordrequestByPhoneAction()
    {
    	$headersParams = $this->validateHeaderParams();
    	$chapId = $headersParams['chapId'];
    	$langCode = $headersParams['langCode'];
    	 

    	
    	$mdn = $this->_request->mdn;
    	 

    	$userModel = new Api_Model_Users();
    	$tmpUser = $userModel->getUserByphone($mdn);
    	 
    	if(is_null($tmpUser))
    	{
    		$this->__echoError("8001", $this->getTranslatedText($langCode, '8001') ? $this->getTranslatedText($langCode, '8001') : "User Id Not Found", self::BAD_REQUEST_CODE);
    	}
    
    	$activationCode = substr(md5(uniqid(rand(), true)), 5,8);
    	
    	
    	if($chapId ==  80184 || $chapId ==  276531  || $chapId ==  283006) {
    		$activationCode = $this->__random_numbers(4);
    	} else {
    		$activationCode = substr(md5(uniqid(rand(), true)), 5,8);
    	}
    	
    	$status = 1;
    
    	$mobileNumber = $tmpUser->mobile_no;
    	
    
    	//Check if mobile number is given
    	if ($mobileNumber === null || empty($mobileNumber))
    	{
    		$this->__echoError("8011", $this->getTranslatedText($langCode, '8011') ? $this->getTranslatedText($langCode, '8011') : "Mobile Number Not Registered", self::BAD_REQUEST_CODE);
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
    			
    		//$message = 'Please use this verification code '.$activationCode.' to complete the password resetting process.';
    		$message = $this->getTranslatedText($langCode, '10002', $activationCode) ? $this->getTranslatedText($langCode, '10002', $activationCode) : 'Please use this verification code '.$activationCode.' to complete the password resetting process.';
    			
    		
    		
    		if($chapId == 80184) 
    			$message = "Y'ello. Please use this verification code $activationCode to complete the password resetting process on the MTN AppStore. Thank you.";
    		
    		
    		if($chapId == 274515)
    			$message =  "Veuillez utiliser ce code de vÃ©rification $activationCode pour terminer le processus de rÃ©initialisation du mot de passe.";
    		
    		
//    		$pgClass->sendSms($mobileNumber, $message, $chapId);
    
    		$response = array(
    				'user' => $tmpUser->id,
    				'mobile_no' => $mobileNumber,
    				'activation_code' => $activationCode,
    				'success_code' => '1111'
    		);
    
    		//$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
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
        echo $responseStr;
    }
    


    /*public function loginiranAction() {
        
       include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
        
       
       //Check if user already exists,
       
       //if not add the record to the DB
       //else just returns the token if successfully authenticated through the Auth API
       
       $client = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/clientRequest');
       $client->soap_defencoding = 'UTF-8';
       $client->decode_utf8 = false;

       $username = '';
       $apiPassword = '';
       
       $timeStamp = date("Y-m-d").'T'.date("H:i:s");
       
       $mobileNo = '989362034232';        
       $password = '';
        
       $header = array(
                        'authentication' => array ( 
                                                        'user' => $username, 
                                                        'password' => $apiPassword
                                                    )
                        );
        
       $body = array(
                        'EaiEnvelope' => array( 
                                            'Domain' => 'Portal', 
                                            'Service' => 'Ecare',
                                            'Language' => 'en', 
                                            'UserId' => 'USER_ID',
                                            'Sender' => 'SENDER_ID', 
                                            'MessageId' => '504016000001401131554427972005',
                                            'CorrelationId' => '504016000001401131554427972005', 
                                            'GenTimeStamp' => $timeStamp,
                                            'Payload' => array( 
                                                            'EcareData' => array( 
                                                                            'Request' => array( 
                                                                                            'Operation_Name' => 'authenticateCustomer',
                                                                                            'CustDetails_InputData' => array( 
                                                                                                                    'MSISDN' => $mobileNo, 
                                                                                                                    'language' => 'en',
                                                                                                                    'newPassword' => $password
                                                                                                                    )
                                                                                              )
                                                                            )
                                                            )
                                            )
                       );
                       
       $result = $client->call('clientRequest', $body, '', '', $header);
       
       // trun on this for debuging
       Zend_Debug::dump( $result);
      echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
     echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
       die();
    }*/
    
    public function loginiranAction() {
        
       include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
        
       
       //Check if user already exists,
       
       //if not add the record to the DB
       //else just returns the token if successfully authenticated through the Auth API
       
       $client = new nusoap_client('http://10.132.58.112:7001/MTNIranCell_Proxy/services/clientRequest');
       $client->soap_defencoding = 'UTF-8';
       $client->decode_utf8 = false;

       $username = 'mw_irancellapp';
       $apiPassword = 'MW_ir029Mtn';
       
       $timeStamp = date("Y-m-d").'T'.date("H:i:s");
       
       $mobileNo = '989362034232';        
       $password = '';
        
       $header = array(
                        'authentication' => array ( 
                                                        'user' => $username, 
                                                        'password' => $apiPassword
                                                    )
                        );
        
       $body = array(
                        'EaiEnvelope' => array( 
                                            'Domain' => 'Portal', 
                                            'Service' => 'Ecare',
                                            'Language' => 'en', 
                                            'UserId' => 'abl_care',
                                            'Sender' => 'abl_care', 
                                            'MessageId' => '504016000001401131554427972005',
                                            'CorrelationId' => '504016000001401131554427972005', 
                                            'GenTimeStamp' => $timeStamp,
                                            'Payload' => array( 
                                                            'EcareData' => array( 
                                                                            'Request' => array( 
                                                                                            'Operation_Name' => 'authenticateCustomer',
                                                                                            'CustDetails_InputData' => array( 
                                                                                                                    'MSISDN' => $mobileNo, 
                                                                                                                    'language' => 'en',
                                                                                                                    'newPassword' => $password
                                                                                                                    )
                                                                                              )
                                                                            )
                                                            )
                                            )
                       );
                       
       $result = $client->call('clientRequest', $body, '', '', $header);
       
       // trun on this for debuging
       Zend_Debug::dump( $result);
      echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
     echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
       die();
    }
    
    public function loginirantestAction() {
        
       include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
       
       $client = new nusoap_client('http://92.42.51.113:7001/MTNIranCell_Proxy'); 
       $client->soap_defencoding = 'UTF-8';
       $client->decode_utf8 = false;

       $username = 'mw_pn';
       $apiPassword = 'MW_pn030Mtn';
       
       $timeStamp = date("Y-m-d").'T'.date("H:i:s");
       
       $mobileNo = '989360659829';        
       $password = '2084930';
      
       $header = array(
                        'ns1:authentication xmlns:ns1="http://webservices.irancell.com/ProxyService"' => array ( 
                                                        'ns1:user' => $username, 
                                                        'ns1:password' => $apiPassword
                                                    )
                        );
        
       $body = array(
                        'ns3:EaiEnvelope xmlns:ns3="http://eai.mtnn.iran/Envelope"' => array(
                            'ns3:Domain' => 'Portal',
                            'ns3:Service' => 'Ecare',
                            'ns3:Language' => 'en',
                            'ns3:UserId' => 'abl_care',
                            'ns3:Sender' => 'abl_care',
                            'ns3:MessageId' => '504016000001401131554427972005',
                            'ns3:CorrelationId' => '504016000001401131554427972005',
                            'ns3:GenTimeStamp' => $timeStamp,
                            'ns3:Payload' => array(
                                'ns3:EcareData' => array(
                                    'ns4:Request xmlns:ns4="http://eai.mtn.iran/Ecare"' => array(
                                        'ns4:Operation_Name' => 'authenticateCustomer',
                                        'ns4:CustDetails_InputData' => array(
                                            'ns4:MSISDN' => $mobileNo,
                                            'ns4:language' => 'en',
                                            'ns4:newPassword' => $password
                                        )
                                    )
                                )
                            )
                        )
                   );

         $result = $client->call('ns2:clientRequest xmlns:ns2="http://www.openuri.org/"', $body, '', '', $header);
       
		 // trun on this for debuging
		 //Zend_Debug::dump( $result);
		 echo $client->request.'<br/><br/>';
		 
		 echo $client->response;
		 //echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
		//echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
		 die();
    }
	
	
    public function sendxmlAction($password,$mobileNumber)
    {  
		include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );

		$client = new nusoap_client('http://92.42.51.113:7001/MTNIranCell_Proxy', false);
		$client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
		
		/*$msg = $client->serializeEnvelope('
  <SOAP-ENV:Header>
    <ns1:authentication xmlns:ns1="http://webservices.irancell.com/ProxyService">
      <ns1:user xsi:type="xsd:string">mw_pn</ns1:user>
      <ns1:password xsi:type="xsd:string">MW_pn030Mtn</ns1:password>
    </ns1:authentication>
  </SOAP-ENV:Header>
  <SOAP-ENV:Body>
    <ns2:clientRequest xmlns:ns2="http://www.openuri.org/">
      <ns3:EaiEnvelope xmlns:ns3="http://eai.mtnn.iran/Envelope">
        <ns3:Domain xsi:type="xsd:string">Portal</ns3:Domain>
        <ns3:Service xsi:type="xsd:string">Ecare</ns3:Service>
        <ns3:Language xsi:type="xsd:string">en</ns3:Language>
        <ns3:UserId xsi:type="xsd:string">abl_care</ns3:UserId>
        <ns3:Sender xsi:type="xsd:string">abl_care</ns3:Sender>
        <ns3:MessageId xsi:type="xsd:string">504016000001401131554427972005</ns3:MessageId>
        <ns3:CorrelationId xsi:type="xsd:string">504016000001401131554427972005</ns3:CorrelationId>
        <ns3:GenTimeStamp xsi:type="xsd:string">2014-02-26T09:39:51</ns3:GenTimeStamp>
        <ns3:Payload>
          <ns3:EcareData>
            <ns4:Request xmlns:ns4="http://eai.mtn.iran/Ecare">
              <ns4:Operation_Name xsi:type="xsd:string">authenticateCustomer</ns4:Operation_Name>
              <ns4:CustDetails_InputData>
                <ns4:MSISDN xsi:type="xsd:string">989360659829</ns4:MSISDN>
                <ns4:language xsi:type="xsd:string">en</ns4:language>
                <ns4:newPassword xsi:type="xsd:string">2084930</ns4:newPassword>
              </ns4:CustDetails_InputData>
            </ns4:Request>
          </ns3:EcareData>
        </ns3:Payload>
      </ns3:EaiEnvelope>
    </ns2:clientRequest>
		');*/
		
		$msg = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/">
 <SOAP-ENV:Header>
    <ns1:authentication xmlns:ns1="http://webservices.irancell.com/ProxyService">
      <ns1:user xsi:type="xsd:string">mw_pn</ns1:user>              <!--$password,$mobileNumber-->
      <ns1:password xsi:type="xsd:string">MW_pn030Mtn</ns1:password>
    </ns1:authentication>
  </SOAP-ENV:Header>
  <SOAP-ENV:Body>
    <ns2:clientRequest xmlns:ns2="http://www.openuri.org/">
      <ns3:EaiEnvelope xmlns:ns3="http://eai.mtnn.iran/Envelope">
        <ns3:Domain xsi:type="xsd:string">Portal</ns3:Domain>
        <ns3:Service xsi:type="xsd:string">Ecare</ns3:Service>
        <ns3:Language xsi:type="xsd:string">en</ns3:Language>
        <ns3:UserId xsi:type="xsd:string">abl_care</ns3:UserId>
        <ns3:Sender xsi:type="xsd:string">abl_care</ns3:Sender>
        <ns3:MessageId xsi:type="xsd:string">504016000001401131554427972005</ns3:MessageId>
        <ns3:CorrelationId xsi:type="xsd:string">504016000001401131554427972005</ns3:CorrelationId>
        <ns3:GenTimeStamp xsi:type="xsd:string">2014-02-26T09:39:51</ns3:GenTimeStamp>
        <ns3:Payload>
          <ns3:EcareData>
            <ns4:Request xmlns:ns4="http://eai.mtn.iran/Ecare">
              <ns4:Operation_Name xsi:type="xsd:string">authenticateCustomer</ns4:Operation_Name>
              <ns4:CustDetails_InputData>
                <ns4:MSISDN xsi:type="xsd:string">09362034232</ns4:MSISDN>
                <ns4:language xsi:type="xsd:string">en</ns4:language>
                <ns4:newPassword xsi:type="xsd:string">147852</ns4:newPassword>
              </ns4:CustDetails_InputData>
            </ns4:Request>
          </ns3:EcareData>
        </ns3:Payload>
      </ns3:EaiEnvelope>
    </ns2:clientRequest>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
		
		$result=$client->send($msg, 'http://92.42.51.113:7001/MTNIranCell_Proxy');
		print_r($result);
		echo '<br/><br/>'.$client->request.'<br/><br/>';
		 
		 echo $client->response;
		//Zend_Debug::dump($result);
		//echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
		//echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';

		die();
	}
	
  /* Following functions has been added by Rooban after introduced the header enricment for the mobile web and app */
    
  /** Returns the response array if the user authenticated 
    * @param mobileNumber
    * @param password
    * returns relevant user data
    */
    
    public function signInMtnAction() 
    {  

        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
        
        $password = $this->_request->password;
        $mobileNumber = $this->_request->mobileNumber;
        if ($mobileNumber == '' || (strlen($mobileNumber) < 10)) 
        { 
            $this->__echoError("2005", $this->getTranslatedText($langCode, '2005') ? $this->getTranslatedText($langCode, '2005') : "Invalid mobile number.", self::BAD_REQUEST_CODE);
        }
        if ($password == '') 
        {
            $this->__echoError("8006", $this->getTranslatedText($langCode, '8006') ? $this->getTranslatedText($langCode, '8006') : "Empty password", self::BAD_REQUEST_CODE);
        }
        
        if (empty($errors)) 
        {
            $userObj = new Api_Model_Users();
            $tmpUser = $userObj->getUserByMobileNo($mobileNumber);
            
            if (is_null($tmpUser)) 
            {
                $this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid mobile number or password", self::BAD_REQUEST_CODE);
            }
            if (((empty($errors))) && ($tmpUser->password != md5($password))) 
            {
                $this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid mobile number or password", self::BAD_REQUEST_CODE);
            }
            if($tmpUser->status != 1)
            {
                // $this->__echoError("8008", "User has not verified the account", self::BAD_REQUEST_CODE); 
                
                 $msg = json_encode(
                                        array(
                                                "message" => $this->getTranslatedText($langCode, '8008') ? $this->getTranslatedText($langCode, '8008') : "User has not verified the account",
                                                "error_code" => "8008",
                                                "user" => $tmpUser->id
                                             )
                                    );
        
                 $this->getResponse()->setHeader('Content-type', 'application/json');
                 //$this->loggerInstance->log('Response ::' . $msg, Zend_Log::INFO);

                 echo $msg;
                 exit;
                //$errors[] = 'Invalid password';
            }                
            if($tmpUser->chap_id != $chapId) //Check whether user belongs to the particular CHAP
            {
                $this->__echoError("8009", $this->getTranslatedText($langCode, '8009') ? $this->getTranslatedText($langCode, '8009') : "Invalid mobile number", self::BAD_REQUEST_CODE);
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
        
        $response = array(
                            'user' => $tmpUser->id,
                            'token' => $sessionId, 
                            'mobile_no' => $tmpUser->mobile_no
                         );
        
        //$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
        echo json_encode($response);
    }
    
    
    
    public function signInMtsAction()
    {
    
    	$headersParams = $this->validateHeaderParams();
    	$chapId = $headersParams['chapId'];
    	$langCode = $headersParams['langCode'];
    
    	$password = $this->_request->password;
    	$mobileNumber = $this->_request->mobileNumber;

    	if ($mobileNumber == '' || (strlen($mobileNumber)  < 12))
    	{
    		$this->__echoError("2005", $this->getTranslatedText($langCode, '2005') ? $this->getTranslatedText($langCode, '2005') : "Invalid mobile number.", self::BAD_REQUEST_CODE);
    	}
    	if ($password == '')
    	{
    		$this->__echoError("8006", $this->getTranslatedText($langCode, '8006') ? $this->getTranslatedText($langCode, '8006') : "Empty password", self::BAD_REQUEST_CODE);
    	}
    
    	if (empty($errors))
    	{
    		$userObj = new Api_Model_Users();
    		$tmpUser = $userObj->getUserByMobileNo($mobileNumber);
    
    		if (is_null($tmpUser))
    		{
    			$this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid mobile number or password", self::BAD_REQUEST_CODE);
    		}
    		if (((empty($errors))) && ($tmpUser->password != md5($password)))
    		{
    			$this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid mobile number or password", self::BAD_REQUEST_CODE);
    		}
    		if($tmpUser->status != 1)
    		{
    			// $this->__echoError("8008", "User has not verified the account", self::BAD_REQUEST_CODE);
    
    			$msg = json_encode(
    					array(
    							"message" => $this->getTranslatedText($langCode, '8008') ? $this->getTranslatedText($langCode, '8008') : "User has not verified the account",
    							"error_code" => "8008",
    							"user" => $tmpUser->id
    					)
    			);
    
    			$this->getResponse()->setHeader('Content-type', 'application/json');
    			//$this->loggerInstance->log('Response ::' . $msg, Zend_Log::INFO);
    
    			echo $msg;
    			exit;
    			//$errors[] = 'Invalid password';
    		}
    		if($tmpUser->chap_id != $chapId) //Check whether user belongs to the particular CHAP
    		{
    			$this->__echoError("8009", $this->getTranslatedText($langCode, '8009') ? $this->getTranslatedText($langCode, '8009') : "Invalid mobile number", self::BAD_REQUEST_CODE);
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
    
    	$response = array(
    			'user' => $tmpUser->id,
    			'token' => $sessionId,
    			'mobile_no' => $tmpUser->mobile_no
    	);
    
    	//$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
    	echo json_encode($response);
    }
    
    
    
    public function signInOrangeGuineaAction()
    {
    
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
    
        $password = $this->_request->password;
        $mobileNumber = $this->_request->mobileNumber;
    
        if ($mobileNumber == '' || (strlen($mobileNumber)  < 12))
        {
            $this->__echoError("2005", $this->getTranslatedText($langCode, '2005') ? $this->getTranslatedText($langCode, '2005') : "Invalid mobile number.", self::BAD_REQUEST_CODE);
        }
        if ($password == '')
        {
            $this->__echoError("8006", $this->getTranslatedText($langCode, '8006') ? $this->getTranslatedText($langCode, '8006') : "Empty password", self::BAD_REQUEST_CODE);
        }
    
        if (empty($errors))
        {
            $userObj = new Api_Model_Users();
            $tmpUser = $userObj->getUserByMobileNo($mobileNumber);
    
            if (is_null($tmpUser))
            {
                $this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid mobile number or password", self::BAD_REQUEST_CODE);
            }
            if (((empty($errors))) && ($tmpUser->password != md5($password)))
            {
                $this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid mobile number or password", self::BAD_REQUEST_CODE);
            }
            if($tmpUser->status != 1)
            {
                // $this->__echoError("8008", "User has not verified the account", self::BAD_REQUEST_CODE);
    
                $msg = json_encode(
                    array(
                        "message" => $this->getTranslatedText($langCode, '8008') ? $this->getTranslatedText($langCode, '8008') : "User has not verified the account",
                        "error_code" => "8008",
                        "user" => $tmpUser->id
                    )
                );
    
                $this->getResponse()->setHeader('Content-type', 'application/json');
                //$this->loggerInstance->log('Response ::' . $msg, Zend_Log::INFO);
    
                echo $msg;
                exit;
                //$errors[] = 'Invalid password';
            }
            if($tmpUser->chap_id != $chapId) //Check whether user belongs to the particular CHAP
            {
                $this->__echoError("8009", $this->getTranslatedText($langCode, '8009') ? $this->getTranslatedText($langCode, '8009') : "Invalid mobile number", self::BAD_REQUEST_CODE);
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
    
        $response = array(
            'user' => $tmpUser->id,
            'token' => $sessionId,
            'mobile_no' => $tmpUser->mobile_no
        );
    
        //$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
        echo json_encode($response);
    }
    
    
    public function signInMtsNewAction()
    {
    
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
    
        $password = $this->_request->password;
        $mobileNumber = $this->_request->mobileNumber;
    
        if ($mobileNumber == '' || (strlen($mobileNumber)  < 12))
        {
            $this->__echoError("2005", $this->getTranslatedText($langCode, '2005') ? $this->getTranslatedText($langCode, '2005') : "Invalid mobile number.", self::BAD_REQUEST_CODE);
        }
        if ($password == '')
        {
            $this->__echoError("8006", $this->getTranslatedText($langCode, '8006') ? $this->getTranslatedText($langCode, '8006') : "Empty password", self::BAD_REQUEST_CODE);
        }
    
        if (empty($errors))
        {
            $userObj = new Api_Model_Users();
            $tmpUser = $userObj->getUserByMobileNo($mobileNumber);
    
            if (is_null($tmpUser))
            {
                $this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid mobile number or password", self::BAD_REQUEST_CODE);
            }
            if (((empty($errors))) && ($tmpUser->password != md5($password)))
            {
                $this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid mobile number or password", self::BAD_REQUEST_CODE);
            }
            if($tmpUser->status != 1)
            {
                // $this->__echoError("8008", "User has not verified the account", self::BAD_REQUEST_CODE);
    
                $msg = json_encode(
                    array(
                        "message" => $this->getTranslatedText($langCode, '8008') ? $this->getTranslatedText($langCode, '8008') : "User has not verified the account",
                        "error_code" => "8008",
                        "user" => $tmpUser->id
                    )
                );
    
                $this->getResponse()->setHeader('Content-type', 'application/json');
                //$this->loggerInstance->log('Response ::' . $msg, Zend_Log::INFO);
    
                echo $msg;
                exit;
                //$errors[] = 'Invalid password';
            }
            if($tmpUser->chap_id != $chapId) //Check whether user belongs to the particular CHAP
            {
                $this->__echoError("8009", $this->getTranslatedText($langCode, '8009') ? $this->getTranslatedText($langCode, '8009') : "Invalid mobile number", self::BAD_REQUEST_CODE);
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
    
        $response = array(
            'user' => $tmpUser->id,
            'token' => $sessionId,
            'mobile_no' => $tmpUser->mobile_no
        );
    
        //$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
        echo json_encode($response);
    }
    
    
    /*public function appConfigurationsMtnAction()
    {
    	//Validate Heder params
    	$headersParams = $this->validateHeaderParams();
    
    	$userAgent = $headersParams['userAgent'];
    	$chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
    
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
    
        //Get the common user infor for MTN 
        $commonUserInfo = $this->_helper->IpHeader->checkUser($chapId);
        $allConstants['user_id'] = $commonUserInfo['user_id'];
    	$allConstants['mobile_no'] = $commonUserInfo['mobile_no'];
        $allConstants['token'] = Zend_Session::getId();
	//print_r($userInfo);
        
    	$this->getResponse()
    	->setHeader('Content-type', 'application/json');
    	 
    	//$this->loggerInstance->log('Response ::' . json_encode($currencyDetails),Zend_Log::INFO);
    	echo str_replace('\/','/',json_encode($currencyDetails +  $allConstants));
    }*/
	
	/**
     * User Registration for MTN users
     * @param mobileNumber
     * @param password
     * returns activation code,user id, success_code as a JSON response
     */
    public function registerMtnAction() {

        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $mobileNumber = $this->_request->mobileNumber;
        $password = $this->_request->password;

        //Get the parameters               
        $userId = trim($this->_getParam('userId',NULL));
        
        $mobileExist = FALSE;
        
        // Input field validation.
        
        if (strlen($password) < 6) {
            $errors[] = 'Your password must be at least 6 characters long';
            
            $this->__echoError("2002", $this->getTranslatedText($langCode, '2002') ? $this->getTranslatedText($langCode, '2002') : "Your password must be at least 6 characters long.", self::BAD_REQUEST_CODE);
            
        }
        
        if ($mobileNumber == '' || (strlen($mobileNumber) < 10)) 
        {
            $errors[] = 'Invalid mobile number';
            
            $this->__echoError("2005", $this->getTranslatedText($langCode, '2005') ? $this->getTranslatedText($langCode, '2005') : "Invalid mobile number.", self::BAD_REQUEST_CODE);
        }
        
        $user = new Api_Model_Users();
        
        if($userId){
            if(!$user->validateUserMdn($mobileNumber)){
                $mobileExist = TRUE;
            }
        }
        else{
            if (!$user->validateUserMdn($mobileNumber))
            {
                    $errors[] = "This mobile number already exists. Please use another number.";

                    $this->__echoError("2007", $this->getTranslatedText($langCode, '2007') ? $this->getTranslatedText($langCode, '2007') : "This mobile number already exists. Please use another number.", self::BAD_REQUEST_CODE);
            }
        }
        

        // When errors generated send out error response and terminate.
        if (!empty($errors)) 
        {
            $temp = array("message" => $this->getTranslatedText($langCode, '8000') ? $this->getTranslatedText($langCode, '8000') : "Data validation failed",
                                "error_details" => $errors);
            
            $this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
        }

        //Generate activation code
        
        if($chapId ==  80184 || $chapId ==  276531 || $chapId ==  585474) {
            $activationCode = $this->__random_numbers(4);
        } else {
            $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
        }
        
        
		$msisdn = @$_SERVER['HTTP_MSISDN'];
        //$msisdn = '234998885548566';

        $themeMeta   = new Model_ThemeMeta();
        $themeMeta->setEntityId($chapId);

        $countryCode = $themeMeta->COUNTRY_CODE_TELECOMMUNICATION;

        $ptn = "/^$countryCode/";  // Regex
		$str = $msisdn; //Your input, perhaps $_POST['textbox'] or whatever
		$rpltxt = "0";  // Replacement string
		$msisdn = preg_replace($ptn, $rpltxt, $str);

		//No need to send verification to header enrichment users
		if(($mobileNumber != $msisdn) && !$userId){        
			// send sms start
			$pgUsersModel = new Api_Model_PaymentGatewayUsers();
			$pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
			 
			$pgType = $pgDetails->gateway_id;
			 
			$pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
			 //echo($pgClass);die();
			//$message = 'Please use this verification code '.$activationCode.' to complete your registration.';
			$message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.' to complete your registration.';
			 
			if($chapId == 274515)
				$message =  "Veuillez utiliser ce code de vÃ©rification $activationCode pour terminer votre enregistrement.";
			
			
			
			if($chapId == 80184) 
				$message = "Y'ello. Please use this verification code $activationCode to complete your registration on the MTN AppStore. Thank you.";
			
			if($chapId == 585474)
			    $message =  "Veuillez utiliser ce code de verification $activationCode pour terminer votre enregistrement.";
			
			
			
			$pgClass->sendSms($mobileNumber, $message, $chapId);
			
		}
		//  send sms end

		$userData = array(
			'username' => $activationCode.'@nexva.com',
			'email' => $activationCode.'@nexva.com',
			'password' => $password,
			'type' => "USER",
			'login_type' => "NEXVA",
			'chap_id' => $chapId,
			'mobile_no' => $mobileNumber,
			'status' => ($headerMsisdn) ? 1 : 0,
			'activation_code' => $activationCode
		);

		if($userId && $mobileExist){
			$userModel = new Model_User();
			$res = $userModel->resetPassword($userId, $password);
			if($res){
				$userModel->changeUserStatus($userId, 1);
			}
		}
		else{
			$userId = $user->createUser($userData);
		}


        $userMeta = new Model_UserMeta();
        $userMeta->setEntityId($userId);
        $userMeta->FIRST_NAME = NULL;
        $userMeta->LAST_NAME = NULL;
        $userMeta->TELEPHONE = $mobileNumber;
        $userMeta->UNCLAIMED_ACCOUNT = '0';
        
        $response = array(
                            'user' => $userId, 
                            'activation_code' => $activationCode, 
                            'success_code' => '1111'
                        );
        
        //$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
        
        echo json_encode($response);
    }
    
    
    public function registerOrangeAction() {
    
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
        
        $activationCodePass = $this->__random_numbers(4);
    
        $mobileNumber = $this->_request->mobileNumber;
        $password = $activationCodePass;
    
        //Get the parameters
        $userId = trim($this->_getParam('userId',NULL));
    
        $mobileExist = FALSE;
    
        // Input field validation.
    
        if (strlen($password) < 4) {
            $errors[] = 'Your password must be at least 6 characters long';
    
            $this->__echoError("2002", $this->getTranslatedText($langCode, '2002') ? $this->getTranslatedText($langCode, '2002') : "Your password must be at least 6 characters long.", self::BAD_REQUEST_CODE);
    
        }
    
        if ($mobileNumber == '' || (strlen($mobileNumber) < 10))
        {
            $errors[] = 'Invalid mobile number';
    
            $this->__echoError("2005", $this->getTranslatedText($langCode, '2005') ? $this->getTranslatedText($langCode, '2005') : "Invalid mobile number.", self::BAD_REQUEST_CODE);
        }
    
        $user = new Api_Model_Users();
    
        if($userId){
            if(!$user->validateUserMdn($mobileNumber)){
                $mobileExist = TRUE;
            }
        }
        else{
            if (!$user->validateUserMdn($mobileNumber))
            {
                $errors[] = "This mobile number already exists. Please use another number.";
    
                $this->__echoError("2007", $this->getTranslatedText($langCode, '2007') ? $this->getTranslatedText($langCode, '2007') : "This mobile number already exists. Please use another number.", self::BAD_REQUEST_CODE);
            }
        }
    
    
        // When errors generated send out error response and terminate.
        if (!empty($errors))
        {
            $temp = array("message" => $this->getTranslatedText($langCode, '8000') ? $this->getTranslatedText($langCode, '8000') : "Data validation failed",
                "error_details" => $errors);
    
            $this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
        }
    
        //Generate activation code
    
        if($chapId ==  80184 || $chapId ==  276531 || $chapId ==  585474) {
            $activationCode = $activationCodePass;
        } else {
            $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
        }
    
    
        $msisdn = @$_SERVER['HTTP_MSISDN'];
        //$msisdn = '234998885548566';
    
        $themeMeta   = new Model_ThemeMeta();
        $themeMeta->setEntityId($chapId);
    
        $countryCode = $themeMeta->COUNTRY_CODE_TELECOMMUNICATION;
    
        $ptn = "/^$countryCode/";  // Regex
        $str = $msisdn; //Your input, perhaps $_POST['textbox'] or whatever
        $rpltxt = "0";  // Replacement string
        $msisdn = preg_replace($ptn, $rpltxt, $str);
    
        //No need to send verification to header enrichment users
        if(($mobileNumber != $msisdn) && !$userId){
            // send sms start
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
    
            $pgType = $pgDetails->gateway_id;
    
            $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
            //echo($pgClass);die();
            //$message = 'Please use this verification code '.$activationCode.' to complete your registration.';
            $message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.' to complete your registration.';
    
            if($chapId == 274515)
                $message =  "Veuillez utiliser ce code de vÃ©rification $activationCode pour terminer votre enregistrement.";
            	
            	
            	
            if($chapId == 80184)
                $message = "Y'ello. Please use this verification code $activationCode to complete your registration on the MTN AppStore. Thank you.";
            	
            if($chapId == 585474)
                $message =  "Veuillez utiliser ce code de verification $activationCode pour terminer votre enregistrement.";
            	
            	
            	
            $pgClass->sendSms($mobileNumber, $message, $chapId);
            	
        }
        //  send sms end
    
        $userData = array(
            'username' => $activationCode.'@nexva.com',
            'email' => $activationCode.'@nexva.com',
            'password' => $password,
            'type' => "USER",
            'login_type' => "NEXVA",
            'chap_id' => $chapId,
            'mobile_no' => $mobileNumber,
            'status' => ($headerMsisdn) ? 1 : 0,
            'activation_code' => $activationCode
        );
    
        if($userId && $mobileExist){
            $userModel = new Model_User();
            $res = $userModel->resetPassword($userId, $password);
            if($res){
                $userModel->changeUserStatus($userId, 1);
            }
        }
        else{
            $userId = $user->createUser($userData);
        }
    
    
        $userMeta = new Model_UserMeta();
        $userMeta->setEntityId($userId);
        $userMeta->FIRST_NAME = NULL;
        $userMeta->LAST_NAME = NULL;
        $userMeta->TELEPHONE = $mobileNumber;
        $userMeta->UNCLAIMED_ACCOUNT = '0';
    
        $response = array(
            'user' => $userId,
            'activation_code' => $activationCode,
            'success_code' => '1111'
        );
    
        //$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
    
        echo json_encode($response);
    }
    
    
    
    public function registerMtsNewAction() {
    
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
    
        $activationCodePass = $this->__random_numbers(4);
        
   //     $activationCodePass =  trim($this->_getParam('password',NULL));
    
        $mobileNumber = $this->_request->mobileNumber;
        //  $password = $activationCodePass;

        $password = trim($this->_getParam('password',NULL));
        
    
    
        //Get the parameters
        $userId = trim($this->_getParam('userId',NULL));
    
        $mobileExist = FALSE;
    
        // Input field validation.
    
        if (strlen($password) < 4) {
            $errors[] = 'Your password must be at least 4 characters long';
    
            $this->__echoError("2002", $this->getTranslatedText($langCode, '2002') ? $this->getTranslatedText($langCode, '2002') : "Your password must be at least 6 characters long.", self::BAD_REQUEST_CODE);
    
        }
        

    
        if ($mobileNumber == '' || (strlen($mobileNumber) < 10))
        {
            $errors[] = 'Invalid mobile number';
    
            $this->__echoError("2005", $this->getTranslatedText($langCode, '2005') ? $this->getTranslatedText($langCode, '2005') : "Invalid mobile number.", self::BAD_REQUEST_CODE);
        }
    
        $user = new Api_Model_Users();
    
        if($userId){
            if(!$user->validateUserMdn($mobileNumber)){
                $mobileExist = TRUE;
            }
        }
        else{
            if (!$user->validateUserMdn($mobileNumber))
            {
                $errors[] = "This mobile number already exists. Please use another number.";
    
                $this->__echoError("2007", $this->getTranslatedText($langCode, '2007') ? $this->getTranslatedText($langCode, '2007') : "This mobile number already exists. Please use another number.", self::BAD_REQUEST_CODE);
            }
        }
    
    
        // When errors generated send out error response and terminate.
        if (!empty($errors))
        {
            $temp = array("message" => $this->getTranslatedText($langCode, '8000') ? $this->getTranslatedText($langCode, '8000') : "Data validation failed",
                "error_details" => $errors);
    
            $this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
        }
    
        //Generate activation code
    
        $activationCode = $activationCodePass;
    
    
        $msisdn = @$_SERVER['HTTP_MSISDN'];
        //$msisdn = '234998885548566';
    
        $themeMeta   = new Model_ThemeMeta();
        $themeMeta->setEntityId($chapId);
    
        $countryCode = $themeMeta->COUNTRY_CODE_TELECOMMUNICATION;
    
        $ptn = "/^$countryCode/";  // Regex
        $str = $msisdn; //Your input, perhaps $_POST['textbox'] or whatever
        $rpltxt = "0";  // Replacement string
        $msisdn = preg_replace($ptn, $rpltxt, $str);
    
        //No need to send verification to header enrichment users
        if(($mobileNumber != $msisdn) && !$userId){
            // send sms start
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
//    print_r($pgDetails);exit;
            $pgType = $pgDetails->gateway_id;
    
            $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
//            print_r($pgClass);die();
            //$message = 'Please use this verification code '.$activationCode.' to complete your registration.';
            $message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.' to complete your registration.';
   
             
            if($chapId == 283006)
                $message =  " Koristi ovu verifikacionu Å¡ifru $activationCode da zavrÅ¡iÅ¡ proces registracije.";

//            $test=$pgClass->sendSms($mobileNumber, $message, $chapId);
//            echo "jyothi";
//            print_r($test);exit;
             
        }
//        exit;
        //  send sms end
    
        $userData = array(
            'username' => $activationCode.'@nexva.com',
            'email' => $activationCode.'@nexva.com',
            'password' => $password,
            'type' => "USER",
            'login_type' => "NEXVA",
            'chap_id' => $chapId,
            'mobile_no' => $mobileNumber,
            'status' => isset($headerMsisdn) ? 1 : 0,
            'activation_code' => $activationCode
        );
    
        if($userId && $mobileExist){
            $userModel = new Model_User();
            $res = $userModel->resetPassword($userId, $password);
            if($res){
                $userModel->changeUserStatus($userId, 1);
            }
        }
        else{
            $userId = $user->createUser($userData);
        }
    
    
        $userMeta = new Model_UserMeta();
        $userMeta->setEntityId($userId);
        $userMeta->FIRST_NAME = NULL;
        $userMeta->LAST_NAME = NULL;
        $userMeta->TELEPHONE = $mobileNumber;
        $userMeta->UNCLAIMED_ACCOUNT = '0';
    
        $response = array(
            'user' => $userId,
            'activation_code' => $activationCode,
            'success_code' => '1111'
        );
    
        //$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
    
        echo json_encode($response);
    }
    

    
    
    
    public function registerAirtelAction() {
        
    	$headersParams = $this->validateHeaderParams();
    	$chapId = $headersParams['chapId'];
    	$langCode = $headersParams['langCode'];
    	

    
    	$mobileNumber = $this->_request->mobileNumber;
    	$password = $this->_request->password;
    
    	//Get the parameters
    	$userId = trim($this->_getParam('userId',NULL));

        //If the user id is 101543(common user) then the user is viewing the app store on wifi. This id needs to change as null
        if($userId == 101543){
            $userId = null;
        }
        
    	$mobileExist = FALSE;

    	// Input field validation.
    
    	if (strlen($password) < 6) {
    		$errors[] = 'Your password must be at least 6 characters long';
    
    		$this->__echoError("2002", $this->getTranslatedText($langCode, '2002') ? $this->getTranslatedText($langCode, '2002') : "Your password must be at least 6 characters long.", self::BAD_REQUEST_CODE);
    
    	}
    
    	if ($mobileNumber == '' || (strlen($mobileNumber) < 10))
    	{
    		$errors[] = 'Invalid mobile number';
    
    		$this->__echoError("2005", $this->getTranslatedText($langCode, '2005') ? $this->getTranslatedText($langCode, '2005') : "Invalid mobile number.", self::BAD_REQUEST_CODE);
    	}
    
    	$user = new Api_Model_Users();
        $userInfo = $user->getUserByMobileNo($mobileNumber);

        
        //Set common password for chap
        $airtelPassword = 'airtelpassword';
        $headerEnrichedUserId = null;
        
        //The user id is common user id when viewing on wifi (mo no - 0123456789)
    	if($userId){
                if(!$user->validateUserMdn($mobileNumber)){
                    $mobileExist = true;
                        
                   //Check the user's password if common password or not
                   $existPassword = $userInfo->password;
                   if($existPassword == md5($airtelPassword)){
                       $headerEnrichedUserId = $userInfo->id;
                   }
               
                }

    	}
    	else{
            //Check the user's password if common password or not
               $existPassword = $userInfo->password;
               if($existPassword == md5($airtelPassword)){
                   $mobileExist = true;
                   $headerEnrichedUserId = $userInfo->id;
               }else {            
          		if (!$user->validateUserMdn($mobileNumber))
                        {
                                $errors[] = "This mobile number already exists. Please use another number.";
                                $this->__echoError("2007", $this->getTranslatedText($langCode, '2007') ? $this->getTranslatedText($langCode, '2007') : "This mobile number already exists. Please use another number.", self::BAD_REQUEST_CODE);
                        }
               }
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
    
    	$headers = apache_request_headers();

    	$themeMeta   = new Model_ThemeMeta();
    	$themeMeta->setEntityId($chapId);
    
    	$headerIdentifierCode = $themeMeta->WHITELABLE_IP_HDR_IDENTIFIER;

    	$msisdn = $headers[$headerIdentifierCode];

        //echo $msisdn,' - ',$userId;die();

    	//No need to send verification to header enrichment users
    	if(($mobileNumber != $msisdn) || !is_null($headerEnrichedUserId)){

    		// send sms start
    		$pgUsersModel = new Api_Model_PaymentGatewayUsers();
    		$pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
    
    		$pgType = $pgDetails->gateway_id;
    
    		$pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
    		//echo($pgClass);die();
    		//$message = 'Please use this verification code '.$activationCode.' to complete your registration.';
    		$message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.' to complete your registration.';
            //Zend_Debug::dump($pgClass);die();
            
    		if($chapId == 80184) 
    		    $message = "Y'ello. Please use this verification code $activationCode to complete your registration on the MTN AppStore. Thank you.";
    		
    		
    		if($chapId == 274515)
    			$message =  "Veuillez utiliser ce code de vÃ©rification $activationCode pour terminer votre enregistrement.";
    		
    		
                $pgClass->sendSms($mobileNumber, $message, $chapId);
  
    	}

    	$userData = array(
    			'username' => $activationCode.'@nexva.com',
    			'email' => $activationCode.'@nexva.com',
    			'password' => $password,
    			'type' => "USER",
    			'login_type' => "NEXVA",
    			'chap_id' => $chapId,
    			'mobile_no' => $mobileNumber,
    			'status' => ($headerMsisdn) ? 1 : 0,
    			'activation_code' => $activationCode
    	);
    
        //Only for header enrichment active user registering
        $userModel = new Model_User();
        
        //If the user already registered by header enrichment reset the user or create the user
    	if($userId && $mobileExist){
    		$res = $userModel->resetPassword($userId, $password);
    		if($res){
    			$userModel->changeUserStatus($userId, 1);
    		}
    	}
    	else{
            //The header enriched user viewing on wifi
            if(!is_null($headerEnrichedUserId)){
                $res = $userModel->resetPassword($headerEnrichedUserId, $password);
    		if($res){
    			$userModel->changeUserStatus($headerEnrichedUserId, 0);
    		}
            } else {
                $userId = $user->createUser($userData);
            }
            
            //$userId = $user->createUser($userData);
    	}
    
        //Overite the header enriched user id to user id
        $userId = (is_null($headerEnrichedUserId)) ? $userId : $headerEnrichedUserId ;
    
    	$userMeta = new Model_UserMeta();
    	$userMeta->setEntityId($userId);
    	$userMeta->FIRST_NAME = NULL;
    	$userMeta->LAST_NAME = NULL;
    	$userMeta->TELEPHONE = $mobileNumber;
    	$userMeta->UNCLAIMED_ACCOUNT = '0';
    
    	$response = array(
    			'user' => $userId,
    			'activation_code' => $activationCode,
    			'success_code' => '1111'
    	);
    
    	//$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
    
    	echo json_encode($response);
    	die();
    }
    
    
    
    public function registerMtsAction() {
    
        

        
    	$headersParams = $this->validateHeaderParams();
    	$chapId = $headersParams['chapId'];
    	$langCode = $headersParams['langCode'];
    	 
    
    
    	$mobileNumber = $this->_request->mobileNumber;
    	$password = $this->_request->password;
    	
    	

    	
    	//Get the parameters
    	$userId = trim($this->_getParam('userId',NULL));
    
    	//If the user id is 101543(common user) then the user is viewing the app store on wifi. This id needs to change as null
    	if($userId == 101543){
    		$userId = null;
    	}
    
    	$mobileExist = FALSE;
    
    	// Input field validation.
    
    	if (strlen($password) < 6) {
    		$errors[] = 'Your password must be at least 6 characters long';
    
    		$this->__echoError("2002", $this->getTranslatedText($langCode, '2002') ? $this->getTranslatedText($langCode, '2002') : "Your password must be at least 6 characters long.", self::BAD_REQUEST_CODE);
    
    	}
    
    	if ($mobileNumber == '' || (strlen($mobileNumber) < 10))
    	{
    		$errors[] = 'Invalid mobile number';
    
    		$this->__echoError("2005", $this->getTranslatedText($langCode, '2005') ? $this->getTranslatedText($langCode, '2005') : "Invalid mobile number.", self::BAD_REQUEST_CODE);
    	}
    
    	$user = new Api_Model_Users();
    	$userInfo = $user->getUserByMobileNo($mobileNumber);
    
    
    	//Set common password for chap
    	$airtelPassword = 'mtspassword';
    	$headerEnrichedUserId = null;
    
    	//The user id is common user id when viewing on wifi (mo no - 0123456789)
    	if($userId){
    		if(!$user->validateUserMdn($mobileNumber)){
    			$mobileExist = true;
    
    			//Check the user's password if common password or not
    			$existPassword = $userInfo->password;
    			if($existPassword == md5($airtelPassword)){
    				$headerEnrichedUserId = $userInfo->id;
    			}
    			 
    		}
    
    	}
    	else{
    		//Check the user's password if common password or not
    		$existPassword = $userInfo->password;
    		if($existPassword == md5($airtelPassword)){
    			$mobileExist = true;
    			$headerEnrichedUserId = $userInfo->id;
    		}else {
    			if (!$user->validateUserMdn($mobileNumber))
    			{
    				$errors[] = "This mobile number already exists. Please use another number.";
    				$this->__echoError("2007", $this->getTranslatedText($langCode, '2007') ? $this->getTranslatedText($langCode, '2007') : "This mobile number already exists. Please use another number.", self::BAD_REQUEST_CODE);
    			}
    		}
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
    
    	$headers = apache_request_headers();
    
    	$themeMeta   = new Model_ThemeMeta();
    	$themeMeta->setEntityId($chapId);
    
    	$headerIdentifierCode = $themeMeta->WHITELABLE_IP_HDR_IDENTIFIER;
    
    	$msisdn = $headers[$headerIdentifierCode];
    
    	//echo $msisdn,' - ',$userId;die();
    
    	//No need to send verification to header enrichment users
    	if(($mobileNumber != $msisdn) || !is_null($headerEnrichedUserId)){
    
    		// send sms start
    		$pgUsersModel = new Api_Model_PaymentGatewayUsers();
    		$pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
    
    		$pgType = $pgDetails->gateway_id;
    
    		$pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
    		//echo($pgClass);die();
    		//$message = 'Please use this verification code '.$activationCode.' to complete your registration.';
    		$message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.' to complete your registration.';
    		//Zend_Debug::dump($pgClass);die();
    
    
   
     	if($chapId == 283006)
     	    
     
     	    $message =  " Koristi ovu verifikacionu Å¡ifru $activationCode da zavrÅ¡iÅ¡ proces registracije.";
        
    
    
    		$pgClass->sendSms($mobileNumber, $message, $chapId);
    
    	}
    
    	$userData = array(
    			'username' => $activationCode.'@nexva.com',
    			'email' => $activationCode.'@nexva.com',
    			'password' => $password,
    			'type' => "USER",
    			'login_type' => "NEXVA",
    			'chap_id' => $chapId,
    			'mobile_no' => $mobileNumber,
    			'status' => ($headerMsisdn) ? 1 : 0,
    			'activation_code' => $activationCode
    	);
    
    	//Only for header enrichment active user registering
    	$userModel = new Model_User();
    
    	//If the user already registered by header enrichment reset the user or create the user
    	if($userId && $mobileExist){
    		$res = $userModel->resetPassword($userId, $password);
    		if($res){
    			$userModel->changeUserStatus($userId, 1);
    		}
    	}
    	else{
    		//The header enriched user viewing on wifi
    		if(!is_null($headerEnrichedUserId)){
    			$res = $userModel->resetPassword($headerEnrichedUserId, $password);
    			if($res){
    				$userModel->changeUserStatus($headerEnrichedUserId, 0);
    			}
    		} else {
    			$userId = $user->createUser($userData);
    		}
    
    		//$userId = $user->createUser($userData);
    	}
    
    	//Overite the header enriched user id to user id
    	$userId = (is_null($headerEnrichedUserId)) ? $userId : $headerEnrichedUserId ;
    
    	$userMeta = new Model_UserMeta();
    	$userMeta->setEntityId($userId);
    	$userMeta->FIRST_NAME = NULL;
    	$userMeta->LAST_NAME = NULL;
    	$userMeta->TELEPHONE = $mobileNumber;
    	$userMeta->UNCLAIMED_ACCOUNT = '0';
    
    	$response = array(
    			'user' => $userId,
    			'activation_code' => $activationCode,
    			'success_code' => '1111'
    	);
    
    	//$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
    
    	echo json_encode($response);
    	die();
    }
    
    
    
    public function validatePasswordMtnAction(){

        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $password = $this->_request->password;
        $mobile = $this->_request->mobile;

        if ($mobile == '')
        {
            $this->__echoError("8013", $this->getTranslatedText($langCode, '8013') ? $this->getTranslatedText($langCode, '8013') : "Empty Mobile Number", self::BAD_REQUEST_CODE);
        }
        if ($password == '')
        {
            $this->__echoError("8006", $this->getTranslatedText($langCode, '8006') ? $this->getTranslatedText($langCode, '8006') : "Empty password", self::BAD_REQUEST_CODE);
        }

        $userObj = new Api_Model_Users();
        $tmpUser = $userObj->getUserByMobileNo($mobile);

        if(is_null($tmpUser))
        {
            $this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid Mobile Number", self::BAD_REQUEST_CODE);
        }
        if ($tmpUser->password != md5($password))
        {
            $this->__echoError("8012", $this->getTranslatedText($langCode, '8012') ? $this->getTranslatedText($langCode, '8012') : "Invalid password", self::BAD_REQUEST_CODE);

        } else {

            $response = array(
                'status' => 1,
                'mobile_no' => $tmpUser->mobile_no
            );

            //$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
            echo json_encode($response);
        }
        die();
    }

    /**
     * Reset password by mobile number
     * @param mobileNumber
     * @param password
     * @param userId - Only when update the registration
     * returns activation code,user id, success_code as a JSON response
     */
    public function resetMtnAction()
    {

        $headersParams = $this->validateHeaderParams();
        $langCode = $headersParams['langCode'];

        $password = $this->_request->password;
        //$username = $this->_request->username;
        $mobileNumber = $this->_request->mobileNumber;
  
        // Store Mobile number from db.
        $usrMDN = '';

        // Get user object for given email.
        $user = new Model_User();
        //$userRow = $user->getUserByEmail($username);

        $userObj = new Api_Model_Users();
        $userRow = $userObj->getUserByMobileNo($mobileNumber);

        // META row for Mobile number verification
        //$userMeta = new Model_UserMeta();

        // Input field validation.
        /*if (!filter_var($username, FILTER_VALIDATE_EMAIL) || (is_null($userRow) )) {
            $errors[] = 'invalid user name';
        }*/

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
        if (!empty($errors))
        {
            $temp = array("message" => $this->getTranslatedText($langCode, '8000') ? $this->getTranslatedText($langCode, '8000') : "Data validation failed",
                "error_details" => $errors);

            $this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
        }

        $user->resetPassword($userRow->id, $password);
        //$this->loggerInstance->log('Response ::' . json_encode(array("user" => $userRow->id)),Zend_Log::INFO);
        echo json_encode(array("user" => $userRow->id));
        die();
    }
    
    
    public function resetMtzAction()
    {
    
        $headersParams = $this->validateHeaderParams();
        $langCode = $headersParams['langCode'];
    
        $password = $this->_request->password;
        //$username = $this->_request->username;
        $mobileNumber = $this->_request->mobileNumber;
    
        // Store Mobile number from db.
        $usrMDN = '';
    
        // Get user object for given email.
        $user = new Model_User();
        //$userRow = $user->getUserByEmail($username);
    
        $userObj = new Api_Model_Users();
        $userRow = $userObj->getUserByMobileNo($mobileNumber);
    
        // META row for Mobile number verification
        //$userMeta = new Model_UserMeta();
    
        // Input field validation.
        /*if (!filter_var($username, FILTER_VALIDATE_EMAIL) || (is_null($userRow) )) {
         $errors[] = 'invalid user name';
        }*/
    
        if ((empty($errors)) && (strlen($password) < 4)) {
            $errors[] = 'Your password must be at least 4 characters long';
        }
    
        // Check mobile number from user meta, is the user object is available.
        if (!(is_null($userRow))) {
            $usrMDN = $userRow->mobile_no;
        }
         
        if ((empty($errors)) && (($mobileNumber == '' || (strlen($mobileNumber) < 10) || ($usrMDN != $mobileNumber)))) {
            $errors[] = 'Invalid mobile number';
        }
        // When errors generated send out error response and terminate.
        if (!empty($errors))
        {
            $temp = array("message" => $this->getTranslatedText($langCode, '8000') ? $this->getTranslatedText($langCode, '8000') : "Data validation failed",
                "error_details" => $errors);
    
            $this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
        }
    
        $user->resetPassword($userRow->id, $password);
        //$this->loggerInstance->log('Response ::' . json_encode(array("user" => $userRow->id)),Zend_Log::INFO);
        echo json_encode(array("user" => $userRow->id));
        die();
    }
    
    
    public function resetOrangeAction()
    {
    
        $headersParams = $this->validateHeaderParams();
        $langCode = $headersParams['langCode'];
    
        $password = $password = $this->_request->password;

                //$username = $this->_request->username;
        $mobileNumber = $this->_request->mobileNumber;
    
        // Store Mobile number from db.
        $usrMDN = '';
    
        // Get user object for given email.
        $user = new Model_User();
        //$userRow = $user->getUserByEmail($username);
    
        $userObj = new Api_Model_Users();
        $userRow = $userObj->getUserByMobileNo($mobileNumber);
    
        // META row for Mobile number verification
        //$userMeta = new Model_UserMeta();
    
        // Input field validation.
        /*if (!filter_var($username, FILTER_VALIDATE_EMAIL) || (is_null($userRow) )) {
         $errors[] = 'invalid user name';
        }*/
    
        if ((empty($errors)) && (strlen($password) < 4)) {
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
        if (!empty($errors))
        {
            $temp = array("message" => $this->getTranslatedText($langCode, '8000') ? $this->getTranslatedText($langCode, '8000') : "Data validation failed",
                "error_details" => $errors);
    
            $this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
        }
    
        $user->resetPassword($userRow->id, $password);
        //$this->loggerInstance->log('Response ::' . json_encode(array("user" => $userRow->id)),Zend_Log::INFO);
        echo json_encode(array("user" => $userRow->id, 'password' => $password));
        die();
    }
    
    
    
    
    public function paymentwifiVerificationAction()
    {
    	$headersParams = $this->validateHeaderParams();
    	$chapId = $headersParams['chapId'];
    	$langCode = $headersParams['langCode'];
    
    	//Get the parameters
    	$userId = trim($this->_getParam('userId'));
    	$mobileNumber = trim($this->_getParam('mobileNumber'));
    
    	/*
    	//Check if User Id has been provided
    	if ($userId === null || empty($userId))
    	{
    		$this->__echoError("8001", $this->getTranslatedText($langCode, '8001') ? $this->getTranslatedText($langCode, '8001') : "User Id not found", self::BAD_REQUEST_CODE);
    	}
    */
    
    	$activationCode = substr(md5(uniqid(rand(), true)), 5,8);
    	$status = 0;
    
    	$userModel = new Api_Model_Users();
    
    	//$mobileNumber = $userModel->getUserMobileById($userId);
    
    	//Check if mobile number is given
    	if ($mobileNumber === null || empty($mobileNumber))
    	{
    		$this->__echoError("8011", $this->getTranslatedText($langCode, '8011') ? $this->getTranslatedText($langCode, '8011') : "Mobile Number Not Registered", self::BAD_REQUEST_CODE);
    	}
    
    	// send sms start
    	$pgUsersModel = new Api_Model_PaymentGatewayUsers();
    	$pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
    
    	 
    	$pgType = $pgDetails->gateway_id;
    
    	//Call Nexva_MobileBilling_Factory and create relevant instance
    	$pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
    
    	//$message = 'Please use this verification code '.$activationCode.' to complete your registration.';
    	$message = $this->getTranslatedText($langCode, '10000', $activationCode) ? $this->getTranslatedText($langCode, '10000', $activationCode) : 'Please use this verification code '.$activationCode.' to complete your payment.';
    
    	
    	
    	if($chapId == 80184) 
    		$message = "Y'ello. Please use this verification code $activationCode to complete your payment on the MTN AppStore. Thank you.";
    	
    	
    	if($chapId == 274515)
    		$message =  "Veuillez utiliser ce code de vÃ©rification $activationCode pour terminer votre paiement.";
    	
    	
    	$pgClass->sendSms($mobileNumber, $message, $chapId);
    	// send sms end
    	 
    	$response = array(
    			'user' => $userId,
    			'activation_code' => $activationCode,
    			'success_code' => '1111'
    	);
    	 
    	//$this->loggerInstance->log('Response ::' . json_encode($response),Zend_Log::INFO);
    	echo json_encode($response);
    	 
    	 
    }


    /**
     *
     */
    public function userLoginIranOldAction(){

        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $mobileNumber = $this->_request->mobile;
        $password = $this->_request->password;

        $user = new Api_Model_Users();
        $userInfo = $user->getUserByMobileNo($mobileNumber);

        if($userInfo == null){

            $userModel = new Model_User();
            $result = $userModel->sendXml($password,$mobileNumber);

            if( $result['EaiEnvelope']['Payload']['EcareData']['Response']['Result_OutputData']['resultCode'] == 0 ){

                $username = $result['EaiEnvelope']['Payload']['EcareData']['Response']['CustDetails_OutputData']['firstName'].'_'.$result['EaiEnvelope']['Payload']['EcareData']['Response']['CustDetails_OutputData']['lastName'];

                $this->registerIran($password,$mobileNumber,$username);
                $this->signInIran($password,$mobileNumber);
            }

        } else {

            $this->signInIran($password,$mobileNumber);

        }
        
        die();
            //$this->signInIran($password,$mobileNumber);
    }

    /**
     *
     */

    public function userLoginIranAction(){

        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $mobileNumber = $this->_request->mobile;
        $password = $this->_request->password;

        /*$response = array('mobile' => $mobileNumber, 'password' => $password);
        //$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
        echo json_encode($response);
        die();*/
        
        if ($mobileNumber == '' || (strlen($mobileNumber) < 10))
        {
            $this->__echoError("2005", $this->getTranslatedText($langCode, '2005') ? $this->getTranslatedText($langCode, '2005') : "Invalid mobile number.", self::BAD_REQUEST_CODE);
        }

        if ($password == '')
        {
            $this->__echoError("8006", $this->getTranslatedText($langCode, '8006') ? $this->getTranslatedText($langCode, '8006') : "Empty password", self::BAD_REQUEST_CODE);
        }

        $userObj = new Api_Model_Users();
        $tmpUser = $userObj->getUserByMobileNo($mobileNumber);
        
        if((!is_null($tmpUser)) && ($tmpUser->chap_id != $chapId)) //Check whether user belongs to the particular CHAP
        {
            $this->__echoError("8009", $this->getTranslatedText($langCode, '8009') ? $this->getTranslatedText($langCode, '8009') : "Invalid mobile number", self::BAD_REQUEST_CODE);
        }

        //Create login factory
        $loginClass = Nexva_RemoteLogin_Factory::createFactory('MtnIran');

        $response = false;

        if(empty($tmpUser)){
            $response = $loginClass->registerAndSignIn($password, $mobileNumber, $chapId, $source = 'API');

            if($response){
                $tmpUser = $userObj->getUserByMobileNo($mobileNumber);
            }
        } 

        if (is_null($tmpUser)) 
        {
            $this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid username or password", self::BAD_REQUEST_CODE);
        }
        
        $sessionUser = new Zend_Session_Namespace('partner_user');
        $sessionUser->id = $tmpUser->id;
        $sessionId = Zend_Session::getId();
        
        $response = array(
                            'user' => $tmpUser->id,
                            'token' => $sessionId, 
                            'mobile_no' => $mobileNumber 
                         );
        
        //$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
        echo json_encode($response);

    }

    public function userLoginYcoinsAction(){

        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $username = $this->_request->username;
        $password = $this->_request->password;

        if ($username == '')
        {
            $this->__echoError("8005", $this->getTranslatedText($langCode, '8005') ? $this->getTranslatedText($langCode, '8005') : "Emptry username", self::BAD_REQUEST_CODE);
        }
        if ($password == '')
        {
            $this->__echoError("8006", $this->getTranslatedText($langCode, '8006') ? $this->getTranslatedText($langCode, '8006') : "Emptry password", self::BAD_REQUEST_CODE);
        }

        $user = new Api_Model_Users();
        $userInfo = $user->getUserByEmail($username);
        //Zend_Debug::dump($userInfo);die();

        if((!is_null($userInfo)) && ($userInfo->chap_id != $chapId)) //Check whether user belongs to the particular CHAP
        {
            $this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid Username", self::BAD_REQUEST_CODE);
        }

        $loginClass = Nexva_RemoteLogin_Factory::createFactory('Ycoins');

        if($userInfo == null){
            //echo 'null - ',$username;die();
            $loginClass->registerAndSignIn($password, $username, $chapId);
            $response = $loginClass->signIn($password, $username);
        } else {
            //echo 'not null - ',$username;die();
            $response = $loginClass->signIn($password, $username);
        }

        //$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
        echo json_encode($response);
    }


    /**
     * @param $password
     * @param $mobileNumber
     */
    public function signInIran($password,$mobileNumber)
    {

        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        /*if ($mobileNumber == '' || (strlen($mobileNumber) < 10))
        {
            $this->__echoError("2005", $this->getTranslatedText($langCode, '2005') ? $this->getTranslatedText($langCode, '2005') : "Invalid mobile number.", self::BAD_REQUEST_CODE);
        }
        if ($password == '')
        {
            $this->__echoError("8006", $this->getTranslatedText($langCode, '8006') ? $this->getTranslatedText($langCode, '8006') : "Empty password", self::BAD_REQUEST_CODE);
        }*/

        if (empty($errors))
        {
            $userObj = new Api_Model_Users();
            $tmpUser = $userObj->getUserByMobileNo($mobileNumber);

            if (is_null($tmpUser))
            {
                $this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid mobile number or password", self::BAD_REQUEST_CODE);
            }
            if (((empty($errors))) && ($tmpUser->password != md5($password)))
            {
                $this->__echoError("8007", $this->getTranslatedText($langCode, '8007') ? $this->getTranslatedText($langCode, '8007') : "Invalid mobile number or password", self::BAD_REQUEST_CODE);
            }
            if($tmpUser->status != 1)
            {
                // $this->__echoError("8008", "User has not verified the account", self::BAD_REQUEST_CODE);

                $msg = json_encode(
                    array(
                        "message" => $this->getTranslatedText($langCode, '8008') ? $this->getTranslatedText($langCode, '8008') : "User has not verified the account",
                        "error_code" => "8008",
                        "user" => $tmpUser->id
                    )
                );

                $this->getResponse()->setHeader('Content-type', 'application/json');
                //$this->loggerInstance->log('Response ::' . $msg, Zend_Log::INFO);

                echo $msg;
                exit;
                //$errors[] = 'Invalid password';
            }
            if($tmpUser->chap_id != $chapId) //Check whether user belongs to the particular CHAP
            {
                $this->__echoError("8009", $this->getTranslatedText($langCode, '8009') ? $this->getTranslatedText($langCode, '8009') : "Invalid mobile number", self::BAD_REQUEST_CODE);
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

        $response = array(
            'user' => $tmpUser->id,
            'token' => $sessionId,
            'mobile_no' => $tmpUser->mobile_no
        );

        //$this->loggerInstance->log(json_encode($response), Zend_Log::INFO);
        echo json_encode($response);
    }

    /**
     * @param $password
     * @param $mobileNumber
     * @param $username
     */
    public function registerIran($password,$mobileNumber,$username) {

        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $user = new Api_Model_Users();

        //Generate activation code
        $activationCode = substr(md5(uniqid(rand(), true)), 5,8);

        //  send sms end
        $userData = array(
            'username' => $username,
            'email' => $activationCode.'@nexva.com',
            'password' => $password,
            'type' => "USER",
            'login_type' => "NEXVA",
            'chap_id' => $chapId,
            'mobile_no' => $mobileNumber,
            'status' => 1,
            'activation_code' => $activationCode
        );

        $user->createUser($userData);
    }
	
	/*
	Testing function for MTN Uganda MoMO
	*/
	public function mobileMoneyUgandaAction() {
       include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
       
       $client = new nusoap_client('http://172.25.48.43:8310/hirdPartyServiceUMMImpl/UMMServiceService/RequestPayment/v17');
       $client->soap_defencoding = 'UTF-8';
       $client->decode_utf8 = false;

       $timeStamp = date("Ymd").date("His");        
       $spId =  '2560110000692';
       $spServiceId = '';
       $spPass = 'Huawei2014';
       $bundleID = '256000039';
       
       $header = array(
                        'RequestSOAPHeader' => array ( 
                                                        'spId' => $spId, 
                                                        'spPassword' => $spPass, 
                                                        'bundleID' => $bundleID,
                                                        'serviceId' => $spServiceId, 
                                                        'timeStamp' => $timeStamp
                                                    )
                        );
        
       $body = array(
                            'serviceId'     =>  '1231232645',
                            'parameter'     => array(
                                'name'    =>  'DueAmount',
                                'value'   =>  '10'
                            ),
                            'parameter'     => array(
                                'name'    =>  'MSISDNNum',
                                'value'   =>  '13132132000'
                            ),
                            'parameter'     => array(
                                'name'    =>  'ProcessingNumber',
                                'value'   =>  '555'
                            ),
                            'parameter'     => array(
                                'name'    =>  'serviceId',
                                'value'   =>  '101'
                            ),
                            'parameter'     => array(
                                'name'    =>  'MinDueAmount',
                                'value'   =>  '100'
                            ),
                            'parameter'     => array(
                                'name'    =>  'OpCoID',
                                'value'   =>  '0'
                            )
                      );

       $result = $client->call('b2b:processRequest', $body, '', '', $header);
       
        //Zend_Debug::dump( $result);
        echo '<pre>' . $client->request . '</pre>';
		echo '<pre>' . $client->response . '</pre>';
        die();
    }
	
	/*
	Testing function XML for MTN Uganda MoMO
	*/
	public function mobileMoneyXmlUgandaAction() {
		
	    include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );

		$client = new nusoap_client('http://172.25.48.43:8310/ThirdPartyServiceUMMImpl/UMMServiceService/RequestPaymenttest/v15', false);
		$client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
	
		//$timeStamp = date("Ymd").date("His"); 
		
		$timeStamp = '20140401112201';
		$spId =  '2560110000692';
		$spPass = 'Huawei2014';
		
		$password = strtoupper(MD5($spId.$spPass.$timeStamp));
		
		$xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
			xmlns:b2b="http://b2b.mobilemoney.mtn.zm_v1.0">
			   <soapenv:Header>
				  <RequestSOAPHeader xmlns="http://www.huawei.com.cn/schema/common/v2_1">
					 <spId>2560110000692</spId>
					 <spPassword>ECF325ECD1342743992165611BBE4A96</spPassword>
					 <serviceId></serviceId>
					 <timeStamp>20140401112201</timeStamp>
					</RequestSOAPHeader>
			   </soapenv:Header>
			   <soapenv:Body>
				  <b2b:processRequest>
					 <serviceId>200</serviceId>
					 <parameter>
						<name>DueAmount</name>
						<value>10</value>
					 </parameter>
					 <parameter>
						<name>MSISDNNum</name>
						<value>256789999550</value>
					 </parameter>
					 <parameter>
						<name>ProcessingNumber</name>
						<value>556</value>
					 </parameter>
					 <parameter> 
						<name>serviceId</name> 
						<value>Appstore</value> 
					 </parameter>
					 <parameter>
						<name>AcctRef</name>
						<value>1234</value>
					 </parameter>
					 <parameter>
						<name>AcctBalance</name>
						<value>0</value>
					 </parameter>
					 <parameter>
						<name>MinDueAmount</name>
						<value>1</value>
					 </parameter>
					 <parameter> 
						<name>Narration</name> 
						<value>121212</value> 
					 </parameter>
					 <parameter>
						<name>PrefLang</name>
						<value>en</value>
					 </parameter>
					 <parameter>
						<name>OpCoID</name>
						<value>25601</value>
					 </parameter>
				  </b2b:processRequest>
			   </soapenv:Body>
			</soapenv:Envelope>';
			
			$result=$client->send($xmlMsg, 'http://172.25.48.43:8310/ThirdPartyServiceUMMImpl/UMMServiceService/RequestPayment/v15');
			
			echo $client->request.'<br/><br/>';
			echo $client->response.'<br/><br/>';
			print_r($result);
			die();
	}
	
	public function smsXmlUgandaAction($mobileNo, $message, $chapId)
    {
       	//todo, get chap SMS gateway details dynamically242068661314
      
       	include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
		$client = new nusoap_client('http://212.88.118.228:5001/SmsNotificationManagerService/services/SmsNotificationManager',false);
		 
		$client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
	
		$timeStamp = date("Ymd").date("His"); 
		$spId =  '2560110000692';
		$spPass = 'Huawei2014';
		
		$password = MD5($spId + $spPass + $timeStamp);
		
		$xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v2="http://www.huawei.com.cn/schema/common/v2_1" xmlns:loc="http://www.csapi.org/schema/parlayx/sms/notification_manager/v2_3/local">
				  <soapenv:Header>
					<RequestSOAPHeader xmlns="http://www.huawei.com.cn/schema/common/v2_1">
					  <spId>'.$spId.'</spId>
					  <spPassword>'.$password.'</spPassword>
					  <serviceId></serviceId>
					  <timeStamp>'.$timeStamp.'</timeStamp>
					</RequestSOAPHeader>
				  </soapenv:Header>
				  <soapenv:Body>
					<loc:startSmsNotification>
					  <loc:reference>
						<endpoint>http://10.138.38.139:9080/notify</endpoint>
						<interfaceName>notifySmsReception</interfaceName>
						<correlator>00001</correlator>
					  </loc:reference>
					  <loc:smsServiceActivationNumber>1234501</loc:smsServiceActivationNumber>
					  <loc:criteria>demand</loc:criteria>
					</loc:startSmsNotification>
				  </soapenv:Body>
				</soapenv:Envelope>';
			
			$result=$client->send($xmlMsg, 'http://212.88.118.228:5001/SmsNotificationManagerService/services/SmsNotificationManager');
			
			echo $client->request.'<br/><br/>';
			echo $client->response.'<br/><br/>';
			print_r($result);
			die();
       
    }


    /**
     * mobile money uganada confirm third party payment request (this is under testing 2014/04/24)
     */
    public function confirmThirdPartyPaymentRequestAction()
    {
        // initialize server and set URI
        // $server = new Zend_Soap_Server(null, array('uri' => 'http://api.nexva.com/test/confirm-third-party-payment-request'));

        $server = new MobilemoneySoapServer(null, array('uri' => 'http://api.nexva.com/nexapi/confirm-third-party-payment-request'));

        // set SOAP service class
        $server->setClass('Uganda_Soap_Functions');

        $server->setEncoding('UTF-8');
        //$server->setReturnResponse(false);

        // handle request
        $server->handle();

    }

    public function updateAndGetBuildUrlAction(){

        $token = $this->_getParam('token', 0);
        $userId = $this->__validateToken($token);

        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
        $userAgent = $headersParams['userAgent'];

        //Get the parameters
        $appId = trim($this->_getParam('appId'));
        $buildId = trim($this->_getParam('build_Id'));
        $price = trim($this->_getParam('price'));

        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);

        //Check if the device was detected or not, if not retrun a message as below
        if ($deviceId === null || empty($deviceId))
        {
            $this->__echoError("2000", $this->getTranslatedText($langCode, '2000') ? $this->getTranslatedText($langCode, '2000') : "Device not found", self::BAD_REQUEST_CODE);
        }

        //Get the S3 URL of the Relevant build
        $productDownloadCls = new Nexva_Api_ProductDownload();
        $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

        //adds record to the interop_payments table
        switch ($chapId) {
            case 115189:
                $paymentGatewayId = 19;
                break;
            default:
                break;
        }

        $interopPaymentsModel = new Api_Model_InteropPayments();
        $data = array(
            'chap_id' => $chapId,
            'app_id' => $appId,
            'build_id' => $buildId,
            'date_transaction' => date("Y-m-d H:i:s"),
            'price' => $price,
            'trans_id' => 0,
            'status' => 'Success',
            'downlaod_link' => $buildUrl,
            'payment_gateway_id' => $paymentGatewayId,
            'token'=>$token
        );

        $interopPaymentsModel->insert($data);

        //************* Add Royalties *************************
        $userAccount = new Model_UserAccount();
        $userAccount->saveRoyalitiesForApi($appId, $price, $paymentMethod='CHAP', $chapId, $userId);

        //************* Add Statistics - Download *************************
        $source = "API";
        $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');

        $model_ProductBuild = new Model_ProductBuild();
        $buildInfo = $model_ProductBuild->getBuildDetails($buildId);

        $modelDownloadStats = new Api_Model_StatisticsDownloads();
        $modelDownloadStats->addDownloadStat($appId, $chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $deviceId, $token);

        /******************End Statistics ******************************* */

        //json encode the build url to send to the App
        $buildUrl = array('build_url' => $buildUrl);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $buildUrlJson = str_replace('\/', '/', json_encode($buildUrl));
        //$this->loggerInstance->log('Response ::' . $buildUrlJson,Zend_Log::INFO);
        echo $buildUrlJson;

    }
    
    /*
     * This function will be called only for remote login functionalities (API login)
     */
    public function remoteLoginAction() 
    {  
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];
        
        $userObj = new Api_Model_Users();
        $keyType = '';
        $factoryType = '';
        
        switch($chapId){

                case 23045 :
                    $factoryType = 'MtnIran';
                    $mobileNo = $this->_request->mobilenumber;
                    $keyType = $mobileNo;
                    break;

                case 115189:
                    $factoryType = 'Ycoins';
                    $username = $this->_request->username;
                    $keyType = $username;
                    break;

                case 136079:
                    $factoryType = 'RegisterAndLogin';
                    $data['first_name'] = $this->_request->first_name;
                    $data['last_name'] = $this->_request->last_name;
                    $username = $this->_request->username;
                    $keyType = $username;
                    break;

            }
                
        $password = $this->_request->password;
        $usernameOrMobile = $keyType;

        /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            echo $usernameOrMobile.'###';
            echo $password;
            die();
        }*/
        
        $data = Array();
        
        if ($usernameOrMobile == '') 
        { 
            $this->__echoError("8005", $this->getTranslatedText($langCode, '8005') ? $this->getTranslatedText($langCode, '8005') : "Emptry ".$usernameOrMobile, self::BAD_REQUEST_CODE);
        }
        
        if ($password == '') 
        {
            $this->__echoError("8006", $this->getTranslatedText($langCode, '8006') ? $this->getTranslatedText($langCode, '8006') : "Empty password", self::BAD_REQUEST_CODE);
        }

        if (empty($errors)) 
        {
            //Create login factory
            $loginClass = Nexva_RemoteLogin_Factory::createFactory($factoryType);
            
            $response = array();
            
            $response = $loginClass->registerAndSignIn($password, $keyType, $chapId, $source = 'API', $data);
            
            if ($response['status'] == 'fail'){
                $this->__echoError("8007", $response['fault_string'] , self::BAD_REQUEST_CODE);
            }
            else{
                $sessionUser = new Zend_Session_Namespace('partner_user');
                $sessionUser->id = $response['user_id'];
                $sessionId = Zend_Session::getId();

                $response = array(
                    'user' => $response['user_id'],
                    'token' => $sessionId,
                    //'email' => $keyType
                    //'mobile_no' => $userDetails->mobile_no

                );

                if(23045 == $chapId){
                    $response['mobile_no'] = $keyType;
                } else {
                    $response['email'] = $keyType;
                }


                $msg = json_encode($response);
                $this->getResponse()->setHeader('Content-type', 'application/json');
                echo $msg;
                exit;
            }

        }

        // When errors generated, send out error response and terminate.
        if (!empty($errors)) 
        {            
            $temp = array("message" => $this->getTranslatedText($langCode, '8000') ? $this->getTranslatedText($langCode, '8000') : "Data validation failed",
                                "error_details" => $errors);
            
            $this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
        }
    }

    /*
     * This function will return the MSISDN info to the APP (prepaid/postpa)
     */
    public function getIranMsisdnInfoAction(){
        
        $headersParams = $this->validateHeaderParams();
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        $factoryType = 'MtnIran';
        $mobileNo = $this->_request->mobilenumber;
                
        if ($mobileNo == '') 
        { 
            $this->__echoError("8005", $this->getTranslatedText($langCode, '8005') ? $this->getTranslatedText($langCode, '8005') : "Empty mobile number", self::BAD_REQUEST_CODE);
        }
        
        if (empty($errors)) 
        {
            //Create login factory
            $loginClass = Nexva_RemoteLogin_Factory::createFactory($factoryType);
            $response = array();
            $response = $loginClass->getMsisdnInfo($mobileNo);

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $responseStr = str_replace('\/', '/', json_encode($response));
            echo $responseStr;
            exit;
        }

        // When errors generated, send out error response and terminate.
        if (!empty($errors)) 
        {            
            $temp = array("message" => $this->getTranslatedText($langCode, '8000') ? $this->getTranslatedText($langCode, '8000') : "Data validation failed",
                                "error_details" => $errors);
            $this->__echoError("8000",$temp, self::BAD_REQUEST_CODE);
        }
    }
    
    function pagaliAction(){

        //$token = $this->_getParam('token', 0);
        //$userId = $this->__validateToken($token);

        //Validate Heder params
        $headersParams = $this->validateHeaderParams();

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

        //echo $userAgent, '-', $chapId, ' - ', $langCode;die();

        $appId = $this->_getParam('appId');
        $buildId = $this->_getParam('build_Id');
        $price = $this->_getParam('price');

        //Check if App Id has been provided
        if ($appId === null || empty($appId)) {

            $this->__echoError("1001", $this->getTranslatedText($langCode, '1001') ? $this->getTranslatedText($langCode, '1001') : "App Id not found", self::BAD_REQUEST_CODE);
        }

        //Check if build Id has been provided
        if ($buildId === null || empty($buildId))
        {
            $this->__echoError("1002", $this->getTranslatedText($langCode, '1002') ? $this->getTranslatedText($langCode, '1002') : "Build Id not found", self::BAD_REQUEST_CODE);
        }

        //Check if the app belongs to the CHAP
        $chapProductModel = new Api_Model_ChapProducts();
        $appCount = $chapProductModel->getProductCountByChap($chapId, $appId);

        if ($appCount == 0)
        {
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

        //header("location: qelasyapp://view?status=".$status."&ompay_token=".$token."&amount=".$amount."&payment_id=".$interopPaymentId."&app_token=".$appToken);
        //header("location: qelasyapp://view?status=".$status."&ompay_token=".$token."&amount=".$amount."&payment_id=".$interopPaymentId."&app_token=".$appToken);

        $url = 'https://www.pagali.cv/pagali_app/index.php?r=pgPaymentInterface/ecommercePayment';
        $data = array(
            'id_ent'    =>  'B4107C95-16AD-7927-A5B6-471C6EF02A8A',
            'id_temp'   =>  '7AF7D692-BBEE-5522-6961-4F8775C743CB',
            'order_id'  =>  '12345',
            'currency_code' => 'CVE',
            'return'    =>  'http://api.nexva.com/nexapi/pagali',
            'notify'    =>  'http://api.nexva.com/nexapi/pagali',
            'total'     =>  '1.50',
            'item_name' =>  'nexva_purchase',
            'quantity'  =>  '2',
            'item_number'   => '234',              //optional parameter
            'amount'    =>  '0.75',
            'total_item'    =>  '1.50'
        );

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

        $userAgent = $headersParams['userAgent'];
        $chapId = $headersParams['chapId'];
        $langCode = $headersParams['langCode'];

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
        //$this->loggerInstance->log('Response ::' . $response,Zend_Log::INFO);

        echo $responseStr;
    }
    
    
    public function buymtsRequestAction() {
    
        
  

        
       $deviceDetection =  Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
       $deviceInfo = $deviceDetection->getNexvaDeviceId($_SERVER['HTTP_USER_AGENT']);
                //If this is not a wireless device redirect to the main site
        $deviceId = $deviceInfo->id;
        
    	$appId = $this->_request->appId;
    	$buildId = $this->_request->buildId;
    	$price = $this->getRequest()->getParam('price');
    	$chapId = $this->getRequest()->getParam('chapId');
    	$mobileNo = $this->getRequest()->getParam('mobileNo');
    	$userId  = $this->getRequest()->getParam('userId');
    	
    	/* user validation */
    	if (!isset($appId)) {
    		die(json_encode(array('status' => 113, 'response' => 'Please enter data.')));
    	} else {
    		if (!is_numeric($appId) || ($appId <= 0) || is_float($appId + 0)) {
    			die(json_encode(array('status' => 114, 'response' => 'Please enter valid data.')));
    		}
    	}
    	if (!isset($buildId)) {
    		die(json_encode(array('status' => 113, 'response' => 'Please enter data.')));
    	} else {
    		if (!is_numeric($buildId) || ($buildId <= 0) || is_float($buildId + 0)) {
    			die(json_encode(array('status' => 114, 'response' => 'Please enter valid data.')));
    		}
    	}
    	if (!isset($price)) {
    		die(json_encode(array('status' => 113, 'response' => 'Please enter data.')));
    	} else {
    		if (!is_numeric($price) || ($price <= 0)) {
    			die(json_encode(array('status' => 114, 'response' => 'Please enter valid data.')));
    		}
    	}
    	if (!isset($chapId)) {
    		die(json_encode(array('status' => 113, 'response' => 'Please enter data.')));
    	} else {
    		if (!is_numeric($chapId) || ($chapId <= 0) || is_float($chapId + 0)) {
    			die(json_encode(array('status' => 114, 'response' => 'Please enter valid data.')));
    		}
    	}
    	/**/
    	$session = new Zend_Session_Namespace('payment_reference');
    	$session->payment_reference = null;
    	$session->app_Id = $appId;
    	$session->build_Id = $buildId;
    	$session->price = $price;
    	$session->userId = $userId;
    	$session->chapId = $chapId;
    	$session->deviceId =  $deviceId;


    	/* end */
    	/* payment gate way details */
    	$pgUsersModel = new Api_Model_PaymentGatewayUsers();
    	$pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
    	$paymentGatewayId = $pgDetails->payment_gateway_id;

    	//Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
    	$telekomSrbijaObj = new Nexva_MobileBilling_Type_TelekomSrbija();
    	$session->payment_reference = $telekomSrbijaObj->addMobilePayment($chapId, $appId, $buildId, $mobileNo, $session->price, $paymentGatewayId, '222');
    
    	$url = $telekomSrbijaObj->doPaymentApp($chapId, $session->build_Id, $session->app_Id, $mobileNo, '123456', $session->price);
    	//echo $url;

    	$this->_redirect($url);
    }
    
    public function buymtsResponceAction() {
        
        

        
    	$auth = Zend_Auth::getInstance();
    	$status = $this->getRequest()->getParam('status');


    	if ($status == 'success') {
    
    		$productDownloadCls = new Nexva_Api_ProductDownload();
    		$session = new Zend_Session_Namespace('payment_reference');
    		$buildUrl = $productDownloadCls->getBuildFileUrl($session->app_Id, $session->build_Id);
    		$userId = $session->userId;
    		

    		
    		$paymentResult = 'Success';
    		$paymentTimeStamp = date('Y-m-d H:i:s');
    		$paymentTransId = strtotime($paymentTimeStamp);
    		$telekomSrbijaObj = new Nexva_MobileBilling_Type_TelekomSrbija();
    		$telekomSrbijaObj->_paymentId = $session->payment_reference;
    		
    
    
    		//var_dump($buildUrl);die();
    		if ($buildUrl != null && !empty($buildUrl)) {
    
    			/*                 * *********** Add Royalties ************************ */
    			$userAccount = new Model_UserAccount();
    			$userAccount->saveRoyalitiesForApi($session->app_Id, $session->price, $paymentMethod = 'CHAP', $session->chapId, $userId);
 
    			$telekomSrbijaObj->UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
   
    	
    			/*                 * *********** Add codengo ************************ */
    			$condengo = new Nexva_Util_Http_SendRequestCodengo;
    			$condengo->send($session->app_Id);

    
    			//************* Add Statistics - Download *************************
    			$source = "API";
    			$ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');
    
    			$modelProductBuild = new Model_ProductBuild();
    			$buildInfo = $modelProductBuild->getBuildDetails($session->build_Id);
    
    			$sessionId = Zend_Session::getId();
    			
 
    
    			/* same user, same product, same device, same chap stats are not allowed to insert */
    			$modelDownloadStats = new Api_Model_StatisticsDownloads();
    			
    		    $modelDownloadStats->addDownloadStat($session->app_Id, $session->chapId, $source, $ipAddress, $userId, $session->build_Id, $buildInfo->platform_id, $buildInfo->language_id, $session->deviceId, $sessionId);
   
    
    			$this->_redirect($buildUrl);
    
    			Zend_Session::namespaceUnset('payment_reference');
    		}
    	}
    }
    
    
    
    
    public function requestOrangeOtpAction() {
    

        
        $mobileNo = $this->getRequest()->getParam('mobile_no');

    
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
            $challengeId = $arrayb[4] ;
    
    
        }
                
            if($challengeId) {
            $response = array('challenge_id' => $challengeId);
            
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $responseStr = str_replace('\/', '/', json_encode($response));
            echo $responseStr;
            
            } else {
            
            $response = array('status' => 'error');
            
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $responseStr = str_replace('\/', '/', json_encode($response));
            echo $responseStr;
            
            
        }
            
        
        die();
            
        
        
    
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
            
            
               /// -- 
            
            
/*             $user = new Api_Model_Users();
            $userInfo = $user->getUserByMobileNo($mobileNo);
            $userId = $userInfo->id;
            

            $productDownloadCls = new Nexva_Api_ProductDownload();
             
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
            
            $message == 'sss';
            
            $response = array('status' => 'success','message' => $message, 'url' => $buildUrl );
            
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $responseStr = str_replace('\/', '/', json_encode($response));
            echo $responseStr;
            die();
             */
            
            /// --
         
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
    
    
                $dd =   substr($output, strpos($output, '{')+strlen( '{'));
    
                $aa = '{'.$dd;
                $ss=    json_decode($aa);
                 
                //   Zend_Debug::dump($ss, 'json');
                //finally there you aew
                //  Zend_Debug::dump($ss->challenge->result[0]->value, 'json');
    
                $token = $ss->challenge->result[0]->value;
    

     
 
               if(is_null($token)) {
                    // $flashMessege =  ($translate != null) ? $translate->translate($msgErrVerify) : $msgErrVerify ;
                    $message = "Invalid token. Please check your messages and type new auth code received.";
                    $message = "Le Token est invalide. Merci de vÃ©rifier vos messages et rentrer le nouveau code d'authentification reÃ§u.";
                    
                    $response = array('status' => 'error','message' => $message);
                    
                    $this->getResponse()->setHeader('Content-type', 'application/json');
                    $responseStr = str_replace('\/', '/', json_encode($response));
                    echo $responseStr;
                    die();
    
    
                }  
                 
       
            }

    
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
    
               // Zend_Debug::dump($headers);
    
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
                //    echo 'ddd';
                    $info = curl_getinfo($ch);
               //     Zend_Debug::dump($info['request_header'][5]);
               //     Zend_Debug::dump($info['request_header']);
               //     Zend_Debug::dump($output);
    
    
                    $dd =   substr($output, strpos($output, '{')+strlen( '{'));
    
                    $aa = '{'.$dd;
                    $response =    json_decode($aa);
    
                    //die();
       
    
    
                    // Error
    
                    /*           $bb =  json_decode('{"requestError":{"policyException":{"messageId":"POL1000","text":"User has insufficient credit for transaction.","variable":"NotEnoughCredit"}}}');
    
                    Zend_Debug::dump($bb);
                     
                    Zend_Debug::dump($bb->requestError->policyException->messageId);
                    Zend_Debug::dump($bb->requestError->policyException->text); */
    

                  
                    if($response->requestError->policyException->messageId) {
    

                      $message =  "Error.. insufficient credit for transaction." ;
                      $message =  "Erreur... vous n'avez pas assez de crÃ©dit pour effectuer la transaction" ;
                        
                        $response = array('status' => 'error','message' => $message);
                        
                        $this->getResponse()->setHeader('Content-type', 'application/json');
                        $responseStr = str_replace('\/', '/', json_encode($response));
                        echo $responseStr;
                        die();
                        
    
    
                    }
     
    
                    /*
                     *                  $bb =  json_decode('{"amountTransaction":{"endUserId":"acr:OrangeAPIToken","paymentAmount":{"chargingInformation":{"description":"Test Rania","currency":"GNF","amount":93.0},"totalAmountCharged":93.0,"chargingMetaData":{"onBehalfOf":"test123456789","purchaseCategoryCode":"Game","channel":"5","serviceId":"ORANGESTORE"}},"transactionOperationStatus":"Charged","referenceCode":"REF-22462424591144217393593773","serverReferenceCode":"e0ecc467-5031-4caa-b897-e11ce7703e66","clientCorrelator":"44217393593773","resourceURL":"http://10.117.20.61:83//acr%3AOrangeAPIToken/transactions/amount/e0ecc467-5031-4caa-b897-e11ce7703e66"}}');
    
                     Zend_Debug::dump($bb);
    
                     Zend_Debug::dump($bb->amountTransaction->transactionOperationStatus); // "Charged"
                     Zend_Debug::dump($bb->amountTransaction->referenceCode); // "REF-22462424591144217393593773"
                     Zend_Debug::dump($bb->amountTransaction->serverReferenceCode);  //"e0ecc467-5031-4caa-b897-e11ce7703e66" */
                    
       
    
                    if($response->amountTransaction->transactionOperationStatus == "Charged") {
                        
                        /// payment success 

                        $user = new Api_Model_Users();
                        $userInfo = $user->getUserByMobileNo($mobileNo);
                        $userId = $userInfo->id;
                        
                        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
                        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
                        $paymentGatewayId = $pgDetails->payment_gateway_id;
                        
                        $productDownloadCls = new Nexva_Api_ProductDownload();
                         
                        $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
                        
                        $paymentResult = 'Success';
                        $paymentTimeStamp = date('d/m/Y- H:i:s');
                        $paymentTransId = $ref;
                         
                        $orangeGuineaObj = new Nexva_MobileBilling_Type_OrangeGuinea();
                        $paymentRefernce =   $orangeGuineaObj->addMobilePayment($chapId, $appId, $buildId, $mobileNo, $amount, $paymentGatewayId, '222');
                        
                        
                        $orangeGuineaObj->_paymentId=$paymentRefernce;
                        $orangeGuineaObj->UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
                        
                         
                        if($buildUrl != null && !empty($buildUrl))
                        {
                        
                            /************* Add Royalties *************************/
                            $userAccount = new Model_UserAccount();
                            $userAccount->saveRoyalitiesForApi($appId, $amount, $paymentMethod='CHAP',  $chapId, $userId);
                        
                            /************* Add codengo *************************/
                            $condengo = new Nexva_Util_Http_SendRequestCodengo;
                            $condengo->send($appId);
                        
                            //************* Add Statistics - Download *************************
                            $source = "MOBILE";
                            $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');
                        
                            $modelProductBuild = new Model_ProductBuild();
                            $buildInfo = $modelProductBuild->getBuildDetails($buildId);
                        
                            $sessionId = Zend_Session::getId();
                            
                            $this->_deviceId = 1111;
                        
                            /* same user, same product, same device, same chap stats are not allowed to insert */
                            $modelDownloadStats = new Api_Model_StatisticsDownloads();
                            $rowBuild = $modelDownloadStats->checkThisStatExistWithSession($userId, $chapId, $buildId, $this->_deviceId, $sessionId);
                            
                            $headersParams = $this->validateHeaderParams();
                            
                            $userAgent = $headersParams['userAgent'];
                            
                            $deviceId = $this->deviceAction($userAgent);
                        
                            /*If the record not exist only stat will be inserted*/
                            if($rowBuild['exist_count'] == 'no'){
                                $modelDownloadStats->addDownloadStat($appId, $chapId, $source, $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, $deviceId, $sessionId);
                            }
                             
                             
                            $modelUSer = new Model_User();
                             
                            $userDetails = $modelUSer->getUserById($userId);
                             
                            $currencyUserModel = new Api_Model_CurrencyUsers();
                            $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
                            $currencyRate = $currencyDetails['rate'];
                            $currencyCode = $currencyDetails['code'];
                            $sessionId = Zend_Session::getId();
                            $amount = ceil($currencyRate * $amount);
                             
                            $message = 'Vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour votre achat dâ€™application depuis Orange.';
                            $orangeGuineaObj->sendsms($userDetails->mobile_no, $message, $this->_chapId);
                        
                             
                            $message =  "Thank you! Your payment is successful, Please click on download button to start download." ;
                            $message =  "Merci! Votre paiement a Ã©tÃ© effectuÃ© avec succÃ¨s. Merci de cliquer sur le bouton 'tÃ©lÃ©charger' pour dÃ©marrer le tÃ©lÃ©chargement." ;
                            
                        ///
                        $response = array('status' => 'success','message' => $message, 'url' => $buildUrl );
                        
                        $this->getResponse()->setHeader('Content-type', 'application/json');
                        $responseStr = str_replace('\/', '/', json_encode($response));
                        echo $responseStr;
                        die();
                        ////
                        
                        
                        } else {
                        
                 
                        $message =  "Paiement Ã©chouÃ©" ;
                        
                        $response = array('status' => 'error','message' => $message);
                        
                        $this->getResponse()->setHeader('Content-type', 'application/json');
                        $responseStr = str_replace('\/', '/', json_encode($response));
                        echo $responseStr;
                        die();
                        
                        }
                        
                        
                        
                        
                        
                        
                        
                        $message =  "Paiement Ã©chouÃ©" ;
                        
                        $response = array('status' => 'error','message' => $message);
                        
                        $this->getResponse()->setHeader('Content-type', 'application/json');
                        $responseStr = str_replace('\/', '/', json_encode($response));
                        echo $responseStr;
                        die();
                        
                        
                        
                        
                        
                        
                        
                        
                        /// payment finished 
                        
                    }
                    
                    
                    $message =  "Paiement Ã©chouÃ©" ;
                    
                    $response = array('status' => 'error','message' => $message);
                   
                    
                    $this->getResponse()->setHeader('Content-type', 'application/json');
                    $responseStr = str_replace('\/', '/', json_encode($response));
                    echo $responseStr;
                    die();
                    

                     
                     
                }
    
            } else {
                 
                $message =  "Paiement Ã©chouÃ©" ;
                
                $response = array('status' => 'error','message' => $message);
                
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $responseStr = str_replace('\/', '/', json_encode($response));
                die();
                 
    
            }
    
    
        }
        
        die();
    
    
    }
    
    
    public function successOrnageAction(){
        
        
    }
    
    

    private function __random_numbers($digits) {
    	$min = pow(10, $digits - 1);
    	$max = pow(10, $digits) - 1;
    	return mt_rand($min, $max);
    }
    
    public function deleteMyDownloadsAction() {
        
        $appId = $this->_getParam('app_id', NULL);
        $userId = $this->_getParam('user_id', NULL);
        
        $staticDownload = new Model_StatisticDownload();
        $result=$staticDownload->deleteMyDownlod($appId,$userId);
        $response=array();
        if($result){
            $response=array('status' => 'success','message' =>'Deleted successfully.');
        }else{
            $response=array('status' => 'error','message' =>"Can't delete successfully.");           
        }
       die(json_encode($response));
    }
    
    public function deleteMyAccountAction() {
      
        $phoneNumber = $this->_getParam('phone_number', NULL);
        
        $user = new Model_User();
        $result=$user->deleteUserByPhoneNumber($phoneNumber);
        $response=array();
        if($result){
            $response=array('status' => 'success','message' =>'Deleted successfully.');
        }else{
            $response=array('status' => 'error','message' =>"Can't delete successfully.");           
        }
       die(json_encode($response));
        
    }

}