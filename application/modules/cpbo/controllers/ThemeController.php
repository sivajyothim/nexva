<?php

class Cpbo_ThemeController extends Nexva_Controller_Action_Cp_MasterController {

    protected $_flashMessenger;

    function preDispatch() {
        $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/facebox/facebox.css');
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/facebox/facebox.js');
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $skip_action_names =
                    array(
                    "login",
                    "register",
                    "forgotpassword",
                    "resetpassword",
                    "a",
                    "agreement"

            );
            if (!in_array($this->getRequest()->getActionName(), $skip_action_names)) {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();
            }
            if (!in_array($this->getRequest()->getActionName(), $skip_action_names)) {

                $this->_redirect('/user/login');
            }
        }
    }

    public function init() {
        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/admin/assets/ketchup/js/jquery.min.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages_cp_register.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js');
        // $this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/js/jquery.min.js');
    }

    public function indexAction() {
        $this->_redirect("/");
        // action body
    }

    public function picklayoutAction() {

    }

    public function customizeAction() {

        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/js/jquery/plugins/colorpicker/css/colorpicker.css');

        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/colorpicker/js/colorpicker.js');

        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/colorpicker/js/colorpicker_start.js');


        $auth = Zend_Auth::getInstance();
        $theme = new Model_ThemeMeta();
        $theme->setEntityId($auth->getIdentity()->id);

        $cssPath = 'custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/css/default.css";
        $logoPath = '/custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/logos/";
        $this->view->logoPath = $logoPath;
        
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');

        if (file_exists($cssPath)) {
            $this->view->css = file_get_contents($cssPath);
            $this->view->disableCssUpload = true;
        }
        // We dont the custom css now-can remove this comment and below line
        //$this->view->disableCssUpload = true;

        $this->view->theme = $theme;


        if ($this->_request->isPost() and isset($this->_request->whitelabel_enabled)) {
            $this->view->activeTab = 'onwhitelabel';
            //$this->view->message    =   "";

            $userId =   $theme->getEntityId();
            $userModel  =   new Model_User();
            $userDetails =    $userModel->find($userId)->current()->toArray();

            if(isset($userDetails['username']) and $userDetails['username'] != '') {
                $theme->WHITELABEL_ENABLED = $this->_request->whitelabel_enabled;

            }else{
                $this->view->error = $translate->translate("You dont have any white label name.")." <a href='/user/profile'>".$translate->translate("Click here to add")."</a>";
            }
        }
            if ($this->_request->isGet()) {
                if (isset($this->_request->pickedlayout)) {
                    $this->view->activeTab = $translate->translate("template");
                    $this->view->message = $translate->translate("You have successfully selected ") . $this->_request->pickedlayout .' '. $translate->translate("template");
                    $theme->LAYOUT = $this->_request->pickedlayout;
                }
            }

            if ($this->_request->isPost()) {


                $filters = array
                        (
                        '*' => array('StripTags', 'StringTrim')
                );

                $validaters = array
                        (
                        'title' => array(
                                'allowEmpty' => false,
                        ),
                        'titleDescription' => array
                        (
                                'allowEmpty' => false,
                        ),
                        'windowTitle' => array
                        (
                                'allowEmpty' => false,
                        ),

                        'feed' => array
                        (
                                'allowEmpty' =>false
                        ),


                        'facebook' => array
                        (
                                'allowEmpty' =>false
                        ),


                        'linkedin' => array
                        (
                                'allowEmpty' =>false
                        ),

                        'twitter' => array
                        (
                                'allowEmpty' =>false
                        ),


                        'about_business' => array
                        (
                                'allowEmpty' => false
                        ),

                        'footer'  => array(
                                'allowEmpty' => false
                        ),

                        'footer_html' => array(
                                'allowEmpty'  => false
                        )

                );



                $checkData = new Zend_Filter_Input($filters, $validaters, $this->getRequest()->getParams());
                $esc = $checkData->getEscaped();

                if ($checkData->isValid()) {
                    if (isset($this->_request->cat) and (0 == strcmp($this->_request->cat, 'header'))) {
                        $this->view->message = $translate->translate("Header details have been saved.");
                        $this->view->activeTab = $translate->translate("header");
                    }
                    if (isset($this->_request->cat) and (0 == strcmp($this->_request->cat, 'footer'))) {
                        $this->view->message = $translate->translate("Footer details have been saved.");
                        $this->view->activeTab = $translate->translate("footer");
                    }

                    if (isset($this->_request->cat) and (0 == strcmp($this->_request->cat, 'social'))) {
                        $this->view->message = $translate->translate("Socialnetwork details have been saved.");
                        $this->view->activeTab = $translate->translate("social");
                    }

                    foreach ($this->getRequest()->getParams() as $key => $param) {

                        if (in_array($key, array('controller', 'action', 'module', 'cat')))
                            continue;


                        $key = strtoupper($key);

                        if(strcasecmp('FOOTER_HTML', $key) == 0) {
                            $param  = htmlentities(strip_tags($param,"<a>"));
                        }else {

                            // $param;//
                        }

                        if(strcasecmp('MANU', $key) == 0){
                            $param  = htmlentities(strip_tags($param,"<a><ul><li>"));
                        }

                        $theme->$key = $param;
                    }




                    if (isset($_FILES['logo']['name'])) {
                        $this->view->activeTab = 'logo';
                        $adapter = new Zend_File_Transfer_Adapter_Http();

                        $config =   Zend_Registry::get('config');
                        $user   =   $config->nexva->application->user;

                        if ($adapter->isUploaded()) {

                            $adapter->addValidator('FilesSize', false, array('min' => '5KB', 'max' => '200KB'));
                            $adapter->addValidator('Extension', false, array('jpg', 'gif', 'png'));

                            if ($adapter->isValid()) {

//                          $logoPath =   'custom/support_files/'.Zend_Auth::getInstance()->getIdentity()->id."/logos/";

                                if (!file_exists('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id)) {
                                    mkdir('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id,0777);
                                    chown('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id,$user);

                                    mkdir('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/css/",0777);
                                    chown('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id."/css/",$user);


                                    mkdir('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/logos/",0777);
                                    chown('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/logos/",$user);
                                }

                                if(!file_exists('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/css/")) {
                                    mkdir('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/css/",0777);
                                    chown('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id."/css/",$user);

                                }

                                if(!file_exists('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/logos/")) {
                                    mkdir('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/logos/",0777);
                                    chown('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id."/logos/",$user);

                                }

                                $adapter->setDestination('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/logos/");
//                            $adapter->addFilter('Rename',
//                            array('target' => $logoPath,
//                            'overwrite' => true));

                                if ($adapter->receive()) {

                                    if (0 != strcmp($theme->LOGO, $adapter->getFileName(NULL, false))) {
                                        $this->view->message = $translate->translate("You have successfully changed your Logo");

                                        if(isset ($theme->LOGO) and $theme->LOGO != '') {

                                            if(file_exists('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/logos/" . $theme->LOGO))
                                                unlink('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/logos/" . $theme->LOGO);

                                        }

                                        $theme->LOGO = $adapter->getFileName(NULL, false);
                                    }
                                }
                            } else {

                                $errorMessage = NULL;
                                foreach ($adapter->getMessages() as $error) {

                                    $errorMessage .= $error . "<br />";
                                }

                                $this->view->error = $errorMessage;
                            }
                        }
                    }
                }
            }
        }

        public function editcssAction() {

            /*get translater*/
            $translate = Zend_Registry::get('Zend_Translate');
            
            $cssPath = 'custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/css/default.css";

            if (file_exists($cssPath)) {
                $fp = fopen($cssPath, "w");
                fwrite($fp, $this->_request->css);
                $this->view->message = $translate->translate("Css Saved");
            }

            $this->_request->setActionName('customize');
            $this->customizeAction();
        }

        function deletecssAction() {
            /*get translater*/
            $translate = Zend_Registry::get('Zend_Translate');
            
            
            $cssPath = 'custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/css/default.css";
            if (file_exists($cssPath)) {

                if (unlink($cssPath)) {
                    $this->view->message = $translate->translate("Css file has been deleted.Please upload a new one");
                } else {
                    $this->view->error = $translate->translate("Couldn't delete the CSS file.Please try again later");
                }
            }
            $this->_request->setActionName('customize');
            $this->customizeAction();
        }

        function uploadcssAction() {
            $theme = new Model_ThemeMeta();
            $theme->setEntityId(Zend_Auth::getInstance()->getIdentity()->id);
            $adapter = new Zend_File_Transfer_Adapter_Http();

            $config =   Zend_Registry::get('config');
            $user   =   $config->nexva->application->user;

            if ($adapter->isUploaded()) {

                $adapter->addValidator('FilesSize', false, array('min' => '1KB', 'max' => '50KB'));
                $adapter->addValidator('Extension', false, array('css'));

                if ($adapter->isValid()) {

                    $cssPath = 'custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/css/default.css";
                    if (!file_exists('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id)) {
                        mkdir('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id,0777);
                        chown('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id,$user);

                        mkdir('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/css/",0777);
                        chown('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/css/",$user);
                    }

                    if(!file_exists('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/css/")) {
                        mkdir('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/css/",0777);
                        chown('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/css/",$user);
                    }


                    $adapter->setDestination('custom/support_files/' . Zend_Auth::getInstance()->getIdentity()->id . "/css/");
                    $adapter->addFilter('Rename',
                            array('target' => $cssPath,
                            'overwrite' => true));

                    if ($adapter->receive()) {
                        $theme->LAYOUT = '';
                    }
                } else {

                    $errorMessage = NULL;
                    foreach ($adapter->getMessages() as $error) {

                        $errorMessage .= $error . "<br />";
                    }

                    $this->view->error = $errorMessage;
                }
            }

            $this->_request->setActionName('customize');
            $this->customizeAction();
        }
        function customizeselectedAction() {

            $userId =   Zend_Auth::getInstance()->getIdentity()->id;

            $theme  =   new Model_ThemeMeta();
            $theme->setEntityId($userId);
            $themeName  =   $theme->LAYOUT;

            /*get translater*/
            $translate = Zend_Registry::get('Zend_Translate');

            if($theme->LAYOUT != '') {

                $selectedThemeCss  =    "custom/".$themeName."/css/style.css";
                $themeCssFilePath  =     'custom/support_files/'.$userId."/css/".$themeName."/css/layout.css";

                $config =   Zend_Registry::get('config');
                $user   =   $config->nexva->application->user;

                if(file_exists($themeCssFilePath)) {
                    $this->view->css   =   file_get_contents($themeCssFilePath);
                }else {



                    if(!file_exists('custom/support_files/'.$userId)) {

                        mkdir('custom/support_files/'.$userId,0777);
                        chgrp('custom/support_files/'.$userId, $user);
                    }

                    if(!file_exists('custom/support_files/'.$userId."/css")) {
                        mkdir('custom/support_files/'.$userId."/css/",0777);
                        chgrp('custom/support_files/'.$userId."/css/", $user);
                    }

                    if(!file_exists('custom/support_files/'.$userId."/css/".$themeName)) {
                        mkdir('custom/support_files/'.$userId."/css/".$themeName,0777);
                        chgrp('custom/support_files/'.$userId."/css/".$themeName,$user);
                    }

                    if(!file_exists('custom/support_files/'.$userId."/css/".$themeName."/css")) {
                        mkdir('custom/support_files/'.$userId."/css/".$themeName."/css",0777);
                        chgrp('custom/support_files/'.$userId."/css/".$themeName."/css",$user);
                    }


                    copy($selectedThemeCss,$themeCssFilePath);
                    $this->view->css   =   file_get_contents($themeCssFilePath);




                }


                if($this->_request->isPost() and isset($this->_request->css)) {
                    $fp    =   fopen($themeCssFilePath, "w");
                    fwrite($fp, $this->_request->css);
                    $this->view->message   =   $translate->translate("Css file has been saved");
                }
            }


        }
        function printCss(array $style) {

            echo "<style>";

            echo "</style>";
        }

    }
