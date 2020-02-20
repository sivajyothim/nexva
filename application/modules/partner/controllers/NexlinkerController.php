<?php

/**
 * Controller for all things nexlinker
 *
 * @copyright neXva.com
 * @author John Pereira
 * @date Jan 14, 2011
 */
class Partner_NexlinkerController extends Zend_Controller_Action {

    function sendmailAction() {
        
        $translate = Zend_Registry::get('Zend_Translate');
        $appId = (int) $this->_request->getParam('appId', 0);
        $email = $this->_request->getParam('email', false);
        $chapId = (int) $this->_request->getParam('chapId', 0);
        $config = Zend_Registry::get("config");
        $promoId = $this->_request->getParam('promo', false);
        $regType = $this->_request->getParam('regType', false);
        $capValue = $this->_request->getParam('capValue', false);
        $regType11 = $this->_request->getParams();
 
        if (!($appId && $email)) {
            $this->__echoError('The email could not be sent. Please refresh your page and try again.');
        }

        $appModel = new Model_Product();
        $product = $appModel->getProductDetailsById($appId);

        if (!$product) {
            $this->__echoError('This product does not exist. Please check the URL and try again.');
        }

        $promocode = null;
        if ($promoId) {
            $promocodeModel = new Model_PromotionCode();
            $promocode = $promocodeModel->getPromotionCode($promoId, true, false);
        }

        $chapData = null;
        if (isset($promocode['chap_id'])) {
            $themeMeta = new Model_ThemeMeta();
            $themeMeta->setEntityId($promocode['chap_id']);
            $chapData = $themeMeta->getAll();
        }
        //modified - 13/03/2012 
        else if ($chapId > 0) {
            $themeMeta = new Model_ThemeMeta();
            $themeMeta->setEntityId($chapId);
            $chapData = $themeMeta->getAll();

            //set whitle label settings
            $whitelLabelSettings = (isset($chapData->WHITELABLE_SETTINGS)) ? json_decode($chapData->WHITELABLE_SETTINGS) : new stdClass();
        }
        
        //Zend_Debug::dump( $chapData->WHITELABLE_URL);
        //Zend_Debug::dump( $chapData);
        //di$_SESSION['code']e();
        
        /* Capture validation*/
        if(($chapId == 283006)){
        //    $captcha = new Zend_Service_ReCaptcha(Zend_Registry::get('config')->recaptcha->public_key, Zend_Registry::get('config')->recaptcha->private_key);
            if ($capValue==$_SESSION['code']) {
                //$resp = $captcha->verify($this->_request->recaptchaChallengeField, $this->_request->recaptchaResponseField);
                
                
                if ($capValue != $_SESSION['code']) {

                    $data['error'] = "The security key wasn't entered correctly. Please try again.";
                    $this->__echoResponse($data);
                }else{
                    /* send mail sectioin */
                            $data = array();
                    $data['isShare'] = false;
                    $data['message'] = 'An email has been sent with instructions on how to download this application.';
                    $template = 'nexlinker_sendapp.phtml';
                    $friend = '';

                    //Retrieve translate object
                    $translate = Zend_Registry::get('Zend_Translate');
                    $downloadStr = "Download options for";
                    $downloadStr = ($translate != null) ? $translate->translate($downloadStr) : $downloadStr;    

                    $subject = $downloadStr.' '. $product['name'];
                    if ($this->_request->getParam('share', 'false') != 'false') {
                        $data['isShare'] = true;
                        $friend = $this->_request->getParam('sender');
                        $template = 'nexlinker_sendapp_share.phtml';
                        $data['message'] = 'An email has been sent to your friend with instructions on how to download this application.';
                        $subject = $friend . ' shared ' . $product['name'] . ' with you';
                    }

                    $mailer = new Nexva_Util_Mailer_Mailer();
                    $mailer->setSubject($subject);
                    $mailer->addTo($email)
                            ->setMailVar("prod", $product)
                            ->setMailVar("chap_id", $chapId)
                            ->setMailVar("email", $email)
                            ->setMailVar('friend', $friend)
                            ->setMailVar('promotion', $promocode)
                            ->setMailVar('company_name', 'neXva')
                            ->setMailVar("baseurl", Zend_Registry::get('config')->nexva->application->base->url)
                            ->setMailVar("regType", $regType);
                            //->setMailVar("mobileurl", Zend_Registry::get('config')->nexva->application->mobile->url)
                            /*if($chapId == 21134)
                            {
                                $mailer->setMailVar("mobileurl", 'app-etite.mobi');
                            }
                            else
                            {
                                $mailer->setMailVar("mobileurl", Zend_Registry::get('config')->nexva->application->mobile->url);
                            }*/
                            $buildId = (int) $this->_request->getParam('buildId', 0);


                            //Get build type for the build id. If the types are URL load the mobile web nexlinker.
                            $buildType = NULL;
                            $platformId = NULL;
                            if($buildId):
                                $modelProductBuild = new Partner_Model_ProductBuilds();
                                $buildType = $modelProductBuild->getFileTypesByBuildId($buildId);
                                $platformId = $modelProductBuild->getPlatformIdByBuildId($buildId);
                            endif;

                            $themeMeta = new Model_ThemeMeta();
                            $themeMeta->setEntityId($chapId);
                            $themeData = $themeMeta->getAll();
                            $chapterPlatformType = NULL;

                            if(isset($themeData->WHITELABLE_PLATEFORM)):
                                $chapterPlatformType = $themeData->WHITELABLE_PLATEFORM;
                            endif;

                            //Check the chap platforms and app platforms and change the app url according to
                            $appUrl = NULL;
                            if($chapterPlatformType == 'MULTIPLE_PLATFORM_CHAP_ONLY')://apetitle
                                    $appUrl = $themeData->WHITELABLE_URL_WEB . "/" . $appId;

                            elseif($chapterPlatformType == 'ANDROID_PLATFORM_CHAP_ONLY')://carolina

                                if( $chapId=='81604'){ //For Qelasy do not show the app store app link. So we will send the mobile site link
                                    $appUrl = $themeData->WHITELABLE_URL_WEB . "/" . $appId;
                                }
                                else{
                                    $appUrl = Zend_Registry::get('config')->nexva->application->mobile->url. "/nt/" . $appId;
                                }

                            elseif($chapterPlatformType == 'ANDROID_AND_MULTIPLE_PLATFORM_CHAP')://MTN
                                if(($platformId != 12 && !empty($platformId)) || $buildType=='url'):
                                    $appUrl = $themeData->WHITELABLE_URL_WEB . "/" . $appId;
                                else:
                                    if (isset($themeData->APPSTORE_APP_URL))
                                    {
                                        if( $chapId=='283006' ){/*for MTS */
                                            $appUrl = $themeData->WHITELABLE_URL_WEB . "/" . $appId;
                                        }else{
                                            $appUrl = $themeData->APPSTORE_APP_URL . "/nt/" . $appId;
                                        }
                                    }
                                    else
                                    {
                                        if( $chapId=='283006' ){/*for MTS */
                                            $appUrl = $themeData->WHITELABLE_URL_WEB . "/" . $appId;
                                        }else{
                                            $appUrl = Zend_Registry::get('config')->nexva->application->mobile->url. "/nt/" . $appId;

                                        }    
                                    }
                                endif;
                            else:

                            endif;

                            $mailer->setMailVar("mobileurl", $appUrl);

                            $mailer->setMailVar("isChap", FALSE);

                            $mailer->setMailVar("webUrl", $themeData->WHITELABLE_URL_WEB);
                    //echo $appUrl;

                    if ($chapData) {
                        //$mailer->setMailVar("mobileurl", $chapData->WHITELABLE_URL) //assigned above
                         $mailer->setMailVar("isChap", TRUE)
                                ->setMailVar('company_name', 'Momaco');

                        if (!empty($themeData->WHITELABLE_SITE_CONTACT_US_EMAIL)) {
                            $mailer->setFrom($themeData->WHITELABLE_SITE_CONTACT_US_EMAIL);
                        } 
                        elseif (!empty($whitelLabelSettings->from_email)) {
                            $mailer->setFrom($whitelLabelSettings->from_email);
                        }
                    }

                    $mailer->setLayout("generic_mail_template");
                    $mailer->sendHTMLMail($template);

                    /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                    echo $mailer->getHTMLMail($template); die();
                    }*/

                    $this->__echoResponse($data);
                            /* end */
                }
            } else {
                $data['error']  = "The security key wasn't entered correctly. Please try again.";
                $this->__echoResponse($data);
            }
        }else{
              $data = array();
                    $data['isShare'] = false;
                    $data['message'] = 'An email has been sent with instructions on how to download this application.';
                    $template = 'nexlinker_sendapp.phtml';
                    $friend = '';

                    //Retrieve translate object
                    $translate = Zend_Registry::get('Zend_Translate');
                    $downloadStr = "Download options for";
                    $downloadStr = ($translate != null) ? $translate->translate($downloadStr) : $downloadStr;    

                    $subject = $downloadStr.' '. $product['name'];
                    if ($this->_request->getParam('share', 'false') != 'false') {
                        $data['isShare'] = true;
                        $friend = $this->_request->getParam('sender');
                        $template = 'nexlinker_sendapp_share.phtml';
                        $data['message'] = 'An email has been sent to your friend with instructions on how to download this application.';
                        $subject = $friend . ' shared ' . $product['name'] . ' with you';
                    }

                    $mailer = new Nexva_Util_Mailer_Mailer();
                    $mailer->setSubject($subject);
                    $mailer->addTo($email)
                            ->setMailVar("prod", $product)
                            ->setMailVar("chap_id", $chapId)
                            ->setMailVar("email", $email)
                            ->setMailVar('friend', $friend)
                            ->setMailVar('promotion', $promocode)
                            ->setMailVar('company_name', 'neXva')
                            ->setMailVar("baseurl", Zend_Registry::get('config')->nexva->application->base->url)
                            ->setMailVar("regType", $regType);
                            //->setMailVar("mobileurl", Zend_Registry::get('config')->nexva->application->mobile->url)
                            /*if($chapId == 21134)
                            {
                                $mailer->setMailVar("mobileurl", 'app-etite.mobi');
                            }
                            else
                            {
                                $mailer->setMailVar("mobileurl", Zend_Registry::get('config')->nexva->application->mobile->url);
                            }*/
                            $buildId = (int) $this->_request->getParam('buildId', 0);


                            //Get build type for the build id. If the types are URL load the mobile web nexlinker.
                            $buildType = NULL;
                            $platformId = NULL;
                            if($buildId):
                                $modelProductBuild = new Partner_Model_ProductBuilds();
                                $buildType = $modelProductBuild->getFileTypesByBuildId($buildId);
                                $platformId = $modelProductBuild->getPlatformIdByBuildId($buildId);
                            endif;

                            $themeMeta = new Model_ThemeMeta();
                            $themeMeta->setEntityId($chapId);
                            $themeData = $themeMeta->getAll();
                            $chapterPlatformType = NULL;

                            if(isset($themeData->WHITELABLE_PLATEFORM)):
                                $chapterPlatformType = $themeData->WHITELABLE_PLATEFORM;
                            endif;

                            //Check the chap platforms and app platforms and change the app url according to
                            $appUrl = NULL;
                            if($chapterPlatformType == 'MULTIPLE_PLATFORM_CHAP_ONLY')://apetitle
                                    $appUrl = $themeData->WHITELABLE_URL_WEB . "/" . $appId;

                            elseif($chapterPlatformType == 'ANDROID_PLATFORM_CHAP_ONLY')://carolina

                                if( $chapId=='81604'){ //For Qelasy do not show the app store app link. So we will send the mobile site link
                                    $appUrl = $themeData->WHITELABLE_URL_WEB . "/" . $appId;
                                }
                                else{
                                    $appUrl = Zend_Registry::get('config')->nexva->application->mobile->url. "/nt/" . $appId;
                                }

                            elseif($chapterPlatformType == 'ANDROID_AND_MULTIPLE_PLATFORM_CHAP')://MTN
                                if(($platformId != 12 && !empty($platformId)) || $buildType=='url'):
                                    $appUrl = $themeData->WHITELABLE_URL_WEB . "/" . $appId;
                                else:
                                    if (isset($themeData->APPSTORE_APP_URL))
                                    {
                                        if( $chapId=='283006' ){/*for MTS */
                                            $appUrl = $themeData->WHITELABLE_URL_WEB . "/" . $appId;
                                        }else{
                                            $appUrl = $themeData->APPSTORE_APP_URL . "/nt/" . $appId;
                                        }
                                    }
                                    else
                                    {
                                        if( $chapId=='283006' ){/*for MTS */
                                            $appUrl = $themeData->WHITELABLE_URL_WEB . "/" . $appId;
                                        }else{
                                            $appUrl = Zend_Registry::get('config')->nexva->application->mobile->url. "/nt/" . $appId;

                                        }    
                                    }
                                endif;
                            else:

                            endif;

                            $mailer->setMailVar("mobileurl", $appUrl);

                            $mailer->setMailVar("isChap", FALSE);

                            $mailer->setMailVar("webUrl", $themeData->WHITELABLE_URL_WEB);
                    //echo $appUrl;

                    if ($chapData) {
                        //$mailer->setMailVar("mobileurl", $chapData->WHITELABLE_URL) //assigned above
                         $mailer->setMailVar("isChap", TRUE)
                                ->setMailVar('company_name', 'Momaco');

                        if (!empty($themeData->WHITELABLE_SITE_CONTACT_US_EMAIL)) {
                            $mailer->setFrom($themeData->WHITELABLE_SITE_CONTACT_US_EMAIL);
                        } 
                        elseif (!empty($whitelLabelSettings->from_email)) {
                            $mailer->setFrom($whitelLabelSettings->from_email);
                        }
                    }

                    $mailer->setLayout("generic_mail_template");
                    $mailer->sendHTMLMail($template);

                    /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                    echo $mailer->getHTMLMail($template); die();
                    }*/

                    $this->__echoResponse($data);
                            /* end */
        }
        /* End */
        
    }

    private function __echoResponse($data = array()) {
        //get callback and attach
        $callBack=$this->_request->getParam('callback');
        if(isset($callBack) && !empty($callBack)){
            $data['callback'] = $this->_request->getParam('callback');
        }
        if(isset($data['message']) && !empty($data['message'])){
           $data['message'] = (isset($data['message'])) ? $data['message'] : '';
        }
        if(isset($data['error']) && !empty($data['error'])){
            $data['error'] = (isset($data['error'])) ? $data['error'] : '';
        }        
        
        echo json_encode($data);
        die();
    }

    private function __echoError($error = '') {
        $data = array();
        $data['callback'] = $this->_request->getParam('callback');
        $data['error'] = $error;
        $data['message'] = '';

        echo json_encode($data);
        die();
    }

    /**
     * Updates language based on URL
     */
    private function __initLanguage() {

        $langCode = $this->_getParam('lang', false);
        if ($langCode) {
            $langModel = new Model_Language();
            $language = $langModel->getLanguageIdByCode($langCode);
            if (isset($language['id'])) {
                $ns = new Zend_Session_Namespace('application');
                $ns->language_id = $language['id'];
            }
        }
    }

    public function oembedAction() {

        //figure out the size - if the 'size' param is not present, the default size would be 'large'
        switch (strtolower($this->_request->size)) {
            case "large":
            case "medium":
            case "small":
            case "qr":
                $size = $this->_request->size;
                break;
            default:
                $size = "large";
        }

        //Assuming that 'url' is always a short url (http://nexva.com/XXXX), this is a hacky-wacky-smacky way of parsing for the id of the product.

        $url = parse_url(urldecode($this->_request->url));

        $pos = stripos($url['path'], '&');

        if (!$pos)
            $id = substr($url['path'], 1, strlen($url['path']));
        else
            $id = substr($url['path'], 1, $pos - 1);

        $html = '
            <!-- neXlinker START -->
	            <span class="nexlinker" id="nexlinker_badge_' . $id . $size . 'nexva"></span>
	            <script type="text/javascript">
	            	var _nxs = _nxs || [];var _nx = new Object();_nx.aid = "' . $id . '";_nx.s = "' . $size . '";_nx.b = "nexva";_nx.h = "nexva.com";
	            	_nxs.push(_nx);</script><script type="text/javascript" src="http://nexva.com/web/nexlinker/nl.js">
            	</script>
            <!-- neXlinker END -->
        ';

        $html = trim($html);
        $html = str_replace('"', '\"', $html);

        $output = '
                {
                    "type":"rich",
                    "html":"' . $html . '"
                }
        ';

        $this->getResponse()->setHeader('Content-type', 'application/json"');

        echo $output;

        die();
    }

    function partnerAction() {
        
        
        

        $id = $this->_request->id;
        $chapId = $this->_request->getParam('p', false);

        $appId = $this->_request->getParam('productid', false);
        $userId = $this->_request->getParam('userid', false);

        $queueModel = new Partner_Model_Queue();

        if ($userId != 'none')
            $queueModel->addProducttoDownloadQueue($appId, $userId, $chapId);

        $this->__initLanguage();

        $isChap = false;
        
        $chapterPlatformType=NULL;
        
        if ($chapId) 
        {
            $themeMeta = new Model_ThemeMeta();
            $themeMeta->setEntityId($chapId);
            $themeData = $themeMeta->getAll();
            
            if (isset($themeData->WHITELABLE_SITE_NAME)) {
                $isChap = true;
                $this->view->chapInfo = $themeData;
                $this->view->chapId = $chapId;
            }
            
            if(isset($themeData->WHITELABLE_PLATEFORM)):
                $chapterPlatformType = $themeData->WHITELABLE_PLATEFORM;
            endif;
        }
        
        /* Capture */
        $captcha = new Zend_Service_ReCaptcha(Zend_Registry::get('config')->recaptcha->public_key, Zend_Registry::get('config')->recaptcha->private_key);

        $captcha->setOption('theme', "white");
        $this->view->recaptcha = $captcha->getHTML();
        /* End */
        
        $this->_helper->layout->setLayout('web/popup');
        //load up product details
        $appModel = new Model_Product();
        $sessionLanguage = new Zend_Session_Namespace('application');

        $buildId = $this->_request->getParam('buildid', false);
        
        if(empty($buildId)):
            $supportedPlatforms = $appModel->getSupportedPlatforms($appId, $chapterPlatformType);
            $countAppPlatforms = count($supportedPlatforms);
            if($countAppPlatforms == 1):
                $buildId = $supportedPlatforms[0]->build_id;
            endif;
        endif;
        
        //Get build type for the build id. If the types are URL load the mobile web nexlinker.
        $buildType = NULL;
        $platformId = NULL;
        if($buildId):
            $modelProductBuild = new Partner_Model_ProductBuilds();
            $buildType = $modelProductBuild->getFileTypesByBuildId($buildId);
            $platformId = $modelProductBuild->getPlatformIdByBuildId($buildId);
        endif;

        $this->view->buildId = $buildId;

        if($chapterPlatformType == 'MULTIPLE_PLATFORM_CHAP_ONLY' || $buildType=='urls')://apetitle
            $this->_helper->viewRenderer('partner-nexlinker-mobile-site');
            $id = $appId; //Replace andriod app id to product id
        
        elseif($chapterPlatformType == 'ANDROID_PLATFORM_CHAP_ONLY' && $buildType=='files')://carolina
            if($chapId == 81604){ //For Qelasy do not show the app store app link. So we will load the mobile site nexlinker template
                $id = $appId;
                $this->_helper->viewRenderer('partner-nexlinker-mobile-site');
            }
            else{
                $this->_helper->viewRenderer('partner');
            }

        
        elseif($chapterPlatformType == 'ANDROID_AND_MULTIPLE_PLATFORM_CHAP')://MTN
            //echo 'xliduhgdlgr',$buildType;die();
            if($platformId != 12 && !empty($platformId) || $buildType=='urls'):
                $this->_helper->viewRenderer('partner-nexlinker-mobile-site');
                $id = $appId; //Replace andriod app id to product id
            else:
                $this->_helper->viewRenderer('partner');
            endif;
        else:

        endif;
        
        //Added for load low end registration
        $regType = $this->_request->getParam('type', false);
        if($regType == 'mobileweb'){
            $this->view->regType = 'mobileweb';
            $this->_helper->viewRenderer('partner-nexlinker-mobile-site');
        }
        else{
            $this->view->regType = 'android_app';
        }
        
        
        $product = $appModel->getProductDetailsById($id, true, $sessionLanguage->language_id);

        if (!$product) {
            $this->_redirect('/');
        }
        
        $this->view->isChap = $isChap;
        $this->view->prod = $product;
        $this->view->userId = $userId;
        $this->view->productId = $appId;
        $this->view->flashMessages = $this->_helper->FlashMessenger->getMessages();
    }

    //pass the available platforms for a particular app
    public function getPlatformForAppAction($appId)
    {

        $productModel = new Model_Product();
        $platforms = $productModel->getSupportedPlatforms($appId);
        return $platforms;
    }
    
    //pass the available platforms for a particular app
    public function getPlatformForAppAjaxAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        $appId = trim($this->_getParam('id'));
        $appStoreAppid = trim($this->_getParam('appStoreAppid'));
        $partnerId = trim($this->_getParam('partnerId'));
        $loggedUser = trim($this->_getParam('loggedUser'));
        
        $productModel = new Model_Product();
        $platforms = $productModel->getSupportedPlatforms($appId);
        //Zend_Debug::dump($platforms);die();
        
        foreach($platforms as $platform):
            echo '<a class="nexlink select_platform" rel="shadowbox;height=530;width=600" href="/nexlinker/partner/id/'.$appStoreAppid.'/p/'.$partnerId.'/userid/'.$loggedUser.'/productid/'.$appId.'/platformid/'.$platform->id.'/buildid/'.$platform->build_id.'" title="Android"><img src="/partner/default/assets/img/platforms/'.$platform->id.'.png" title="'.$platform->name.'" /></a>';
        endforeach;
        //echo json_encode($platforms);
    }
    
     //pass the available platforms for a particular app
    public function getPlatformForAppJsonAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        $appId = trim($this->_getParam('id'));
        
        $productModel = new Model_Product();
        $platforms = $productModel->getSupportedPlatforms($appId);

        echo json_encode($platforms);
    }
    
    function noAppstoreAction()
    {

    }

}
