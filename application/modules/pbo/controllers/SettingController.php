<?php

class Pbo_SettingController extends Zend_Controller_Action
{
    
    public function preDispatch() 
    {        
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
    
    /* Get all apps of a chap
    */
    public function indexAction()
    {
        $this->view->title = "Settings : Global Settings";
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
              
        //Load them meta model 
        $themeMeta   = new Model_ThemeMeta();
        $themeMeta->setEntityId($chapId);
                
        $this->view->cpMessages = $this->_helper->flashMessenger->getMessages();
        //$this->view->cpMessages = $this->_helper->flashMessenger->setNamespace('success')->getMessages();
        //echo 'Success';
        //Zend_Debug::dump($this->view->cpMessages);

        //Set the messages if exists
        //$this->view->appErrorMessages = $this->_helper->flashMessenger->setNamespace('error')->getMessages();
        //echo 'Error';
        //Zend_Debug::dump($this->view->appErrorMessages);
      
        //Saving or adding settings
        if($this->_request->isPost())
        {
            //Load file uploading adapter
            $fileUploadAdapter = new Zend_File_Transfer_Adapter_Http();
            $fileUploadAdapter->addValidator('Extension',false,'jpg,jpeg,eps,gif,png,ipk,wgz,prc,bar,ico'); //Allowed file types - got from upload.js
            
            //Get setting type
            $settingType = $this->_getParam('settingType');
            
            //Save Web settings
            if(!empty($settingType) && $settingType == 'web')
            {
                $webSettings = array();
                
                $title = utf8_encode(trim($this->_getParam('txtTitle')));
                $metaKey = trim($this->_getParam('txtMetaKey'));
                $metaDescription = trim($this->_getParam('txtMetaDescription'));            
                $googleAnalytic = trim($this->_getParam('txtGoogleAnalytic'));
                $contactUsEmail = trim($this->_getParam('txtContact'));
                $advertising = trim($this->_getParam('advertising'));
                $appstoreVersion = trim($this->_getParam('appstore_version'));
                $appstoreAppId = trim($this->_getParam('appstoreAppId'));
                $appstoreBuildId = trim($this->_getParam('appstoreBuildId'));
                $interval = trim($this->_getParam('interval'));
                $banners = trim($this->_getParam('banners'));
                $feturedApps = trim($this->_getParam('feturedApps'));
                $agreement_text = trim($this->_getParam('agreement_text'));
                $agreement_link = trim($this->_getParam('agreement_link'));
                $copyright_text = trim($this->_getParam('copyright_text'));

                $webSettings['WHITELABLE_SITE_TITLE'] = $title;
                $webSettings['WHITELABLE_SITE_META_KEYS'] = $metaKey;
                $webSettings['WHITELABLE_SITE_META_DES'] = $metaDescription;
                $webSettings['WHITELABLE_SITE_GOOGLE_ANALYTIC'] = $googleAnalytic;
                $webSettings['WHITELABLE_SITE_CONTACT_US_EMAIL'] = $contactUsEmail;
                $webSettings['WHITELABLE_SITE_ADVERTISING'] = $advertising;
                $webSettings['WHITELABLE_SITE_APPSTORE_VERSION'] = $appstoreVersion;
                $webSettings['WHITELABLE_SITE_APPSTORE_APP_ID'] = $appstoreAppId;
                $webSettings['WHITELABLE_SITE_APPSTORE_BUILD_ID'] = $appstoreBuildId;
                $webSettings['WHITELABLE_SITE_INTERVAL'] = $interval;
                $webSettings['WHITELABLE_SITE_BANNER_COUNT'] = $banners;
                $webSettings['WHITELABLE_SITE_FETURED_APPS'] = $feturedApps;
                $webSettings['WHITELABLE_SITE_AGREEMENT_TEXT'] = $agreement_text;
                $webSettings['WHITELABLE_SITE_AGREEMENT_LINK'] = $agreement_link;
                $webSettings['WHITELABLE_SITE_COPYRIGHT_TEXT'] = $copyright_text;

                //Set params back
                $this->view->webTitle = $title;
                $this->view->metaKey = $metaKey;
                $this->view->metaDescription = $metaDescription;
                $this->view->googleAnalytic = $googleAnalytic;
                $this->view->contactUs = $contactUsEmail;
                $this->view->advertising = $advertising;
                $this->view->appstore_version = $appstoreVersion;
                $this->view->appstoreAppId = $appstoreAppId;
                $this->view->appstoreBuildId = $appstoreBuildId;
                $this->view->interval = $interval;
                $this->view->banners = $banners;
                $this->view->feturedApps = $feturedApps;
            
                /******************** Uploading the favicon ****************************/                
            
                $favicon = $fileUploadAdapter->getFileName('siteFavicon',false);
                
                if(!empty($favicon))
                {
                     $iconDate = strtotime(date("Y-m-d H:i:s"));       

                    //Rename the favicon
                    $newIconName = $iconDate.'_'.$favicon;

                    //$fileUploadAdapter->setDestination($_SERVER['DOCUMENT_ROOT'].'/wl/banners/');
                    $fileUploadAdapter->addFilter('Rename', array('target' => $_SERVER['DOCUMENT_ROOT'].'/wl/favicons/'. $newIconName));
                     //$fileUploadAdapter->addFilter('Rename', $_SERVER['DOCUMENT_ROOT'].'/wl/favicons/', $newIconName);

                    $fileUploadAdapter->receive('siteFavicon');
                   
                }
               
                /******************** End uploading favicon ****************************/
                
                /******************** Uploading the Web Site Logo ****************************/
                $logo = $fileUploadAdapter->getFileName('siteLogo',false);
              
                if(!empty($logo))
                {
                    $logoDate = strtotime(date("Y-m-d H:i:s"));       

                    //Rename the favicon
                    $newLogoName = $logoDate.'_'.$logo;

                    //$fileUploadAdapter->setDestination($_SERVER['DOCUMENT_ROOT'].'/wl/banners/');
                    $fileUploadAdapter->addFilter('Rename', array('target' => $_SERVER['DOCUMENT_ROOT'].'/wl/logos/'. $newLogoName));

                     //$fileUploadAdapter->addFilter('Rename', $_SERVER['DOCUMENT_ROOT'].'/wl/logos/' ,$newLogoName);

                    if(!$fileUploadAdapter->receive('siteLogo'))
                    {
                        $errMsg = $fileUploadAdapter->getMessages();
                        
                    }
                }
                /******************** End uploading Web Site Logo ****************************/

                /******************** Uploading Qelasy app Banner ****************************/

                $qelasyBanner = $fileUploadAdapter->getFileName('qelasy-app-banner',false);
  
                if(!empty($qelasyBanner)){
                    $bannerDate = strtotime(date("Y-m-d H:i:s"));

                    //Rename the banner
                    $newBannerName = $bannerDate.'_'.$qelasyBanner;

                    $fileUploadAdapter->addFilter('Rename', array('target' => $_SERVER['DOCUMENT_ROOT'].'/wl/qelasy/'. $newBannerName));

                    if(!$fileUploadAdapter->receive('qelasy-app-banner'))
                    {
                        $errMsg = $fileUploadAdapter->getMessages();
                    }
                }

                /******************** End Uploading Qelasy app Banner ************************/
                
                $webSettings['WHITELABLE_SITE_FAVICON'] = !empty($favicon) ? $newIconName : trim($this->_getParam('webFavicon'));
                $webSettings['WHITELABLE_SITE_LOGO'] = !empty($logo) ? $newLogoName : trim($this->_getParam('webLogo'));
                $webSettings['WHITELABLE_SITE_QELASY_BANNER'] = !empty($qelasyBanner) ? $newBannerName : trim($this->_getParam('qelasyBanner'));

                //Save or add web settings
                foreach($webSettings as $key=>$value)
                {
                    //echo $key,' - ',$value;
                    $themeMeta->$key   = trim($value);
                }

                if(count($fileUploadAdapter->getMessages()) > 0){
                    $this->_helper->flashMessenger->addMessage(implode(', ', $fileUploadAdapter->getMessages()));
                    //$this->_helper->flashMessenger->setNamespace('error')->addMessage(implode(', ', $fileUploadAdapter->getMessages()));
                    $this->_redirect('/setting');
                }

                //$this->_helper->flashMessenger->setNamespace('success')->addMessage('Web Settings successfully saved.');
                $this->_helper->flashMessenger->addMessage('Web Settings successfully saved.');
                
                
            }
            //Save CP settings
            else if(!empty($settingType) && $settingType == 'cp')
            {
                $cpSettings = array();
                
                $title = trim($this->_getParam('txtTitleCp'));
                $homeDes = trim($this->_getParam('txtHomeDes'));
                $headerColour = trim($this->_getParam('txtHeaderColour'));
                //$customCss = trim($this->_getParam('txtCustomCss')); 
                $footer = trim($this->_getParam('txtFooter')); 
                
                $cpSettings['CP_PANEL_TITLE'] = $title;
                $cpSettings['CP_HOME_DESCRIPTION'] = $homeDes;
                $cpSettings['CP_HEADER_COLOUR'] = $headerColour;
                $cpSettings['CP_FOOTER'] = $footer;
                //$cpSettings['CP_CUSTOM_CSS'] = $customCss;
                
                $this->view->cpTitle = $title;
                $this->view->homeDes = $homeDes;
                $this->view->headeColour = $headerColour;
                $this->view->footerDes = $footer;
                //$this->view->customCss = $customCss;
                
                /******************** Uploading the CP Panel Logo ****************************/
                $cpLogo = $fileUploadAdapter->getFileName('cpLogo',false);
                
                if(!empty($cpLogo))
                {                
                    $cpLogoDate = strtotime(date("Y-m-d H:i:s"));       

                    //Rename the favicon
                    $newCpLogoName = $cpLogoDate.'_'.$cpLogo;

                    //$fileUploadAdapter->setDestination($_SERVER['DOCUMENT_ROOT'].'/wl/banners/');
                    $fileUploadAdapter->addFilter('Rename', array('target' => $_SERVER['DOCUMENT_ROOT'].'/cp/assets/logos/'. $newCpLogoName));

                    $fileUploadAdapter->receive('cpLogo');
                }
                /******************** End uploading CP Panel Logo ****************************/
             
                $cpSettings['CP_HEADER_LOGO'] = !empty($cpLogo) ? $newCpLogoName : trim($this->_getParam('cpHeaderLogo'));
                 
                foreach($cpSettings as $key=>$value)
                {
                    $themeMeta->$key   = trim($value);                    
                }

                if(count($fileUploadAdapter->getMessages()) > 0){
                    $this->_helper->flashMessenger->addMessage(implode(', ', $fileUploadAdapter->getMessages()));
                    //$this->_helper->flashMessenger->setNamespace('error')->addMessage(implode(', ', $fileUploadAdapter->getMessages()));
                    $this->_redirect('/setting');
                }

                $this->_helper->flashMessenger->addMessage('Developer Panel Settings successfully saved.');
                //$this->_helper->flashMessenger->setNamespace('success')->addMessage('Developer Panel Settings successfully saved.');
               //$this->_redirect (PBO_PROJECT_BASEPATH.'setting');
            }
            
            $this->_redirect (PBO_PROJECT_BASEPATH.'setting');
        }       
                
        //Theme meta keys
        $settingsArrayKeys   = array(   
                                        'WHITELABLE_SITE_TITLE',
                                        'WHITELABLE_SITE_META_KEYS',
                                        'WHITELABLE_SITE_META_DES',
                                        'WHITELABLE_SITE_GOOGLE_ANALYTIC',
                                        'WHITELABLE_SITE_CONTACT_US_EMAIL',
                                        'WHITELABLE_SITE_ADVERTISING',
                                        'WHITELABLE_SITE_APPSTORE_VERSION',
                                        'WHITELABLE_SITE_APPSTORE_APP_ID',
                                        'WHITELABLE_SITE_APPSTORE_BUILD_ID',
                                        'WHITELABLE_SITE_INTERVAL',
                                        'WHITELABLE_SITE_BANNER_COUNT',
                                        'WHITELABLE_SITE_FETURED_APPS',
                                        'WHITELABLE_SITE_FAVICON',
                                        'WHITELABLE_SITE_LOGO',
                                        'WHITELABLE_SITE_QELASY_BANNER',
                                        'WHITELABLE_SITE_AGREEMENT_TEXT',
                                        'WHITELABLE_SITE_AGREEMENT_LINK',
                                        'WHITELABLE_SITE_COPYRIGHT_TEXT',
                                        'CP_PANEL_TITLE',
                                        'CP_HOME_DESCRIPTION',
                                        'CP_HEADER_COLOUR',
                                        //'CP_CUSTOM_CSS',
                                        'CP_HEADER_LOGO',
                                        'CP_FOOTER',
                                     );
        $settings = array();
        
        //Store settings in the array
        foreach ($settingsArrayKeys as $key) 
        {
             $settings[$key] = $themeMeta->$key;
        }
        
        $this->view->settings   = (object)$settings;
    }
}