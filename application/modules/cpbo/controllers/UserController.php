<?php

class Cpbo_UserController extends Nexva_Controller_Action_Cp_MasterController {

  protected $_flashMessenger;

  function preDispatch() {
  	

 	
    if (!Zend_Auth::getInstance()->hasIdentity()) {

      $skip_action_names =
          array(
            "login",
            "register",
            "forgotpassword",
            "resetpassword",
            "a",
            "agreement",
            "verify",
            'impersonate',
            'impersonate-an',
            'google-login'
           
      );

//      if (!in_array($this->getRequest()->getActionName(), $skip_action_names)) {
//        $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
//        $session = new Zend_Session_Namespace('lastRequest');
//        $session->lastRequestUri = $requestUri;
//        $session->lock();
//      }
      
      
      if (!in_array($this->getRequest()->getActionName(), $skip_action_names)) {
        $this->_redirect(CP_PROJECT_BASEPATH.'/user/login');
      }
    }
  }

  public function init() {
  	
	$session = new Zend_Session_Namespace('chap');  
	
	if(isset($session->chap->id))	{
		$chapId = $session->chap->id;   
	}

	if(!empty($chapId) && isset($chapId))
	{
	  $themeMetaModel = new Model_ThemeMeta();
	  $themeMetaModel->setEntityId($chapId);
	  $folderPath = ($themeMetaModel->WHITELABLE_THEME_NAME) ? $themeMetaModel->WHITELABLE_THEME_NAME : 'assets';
	  $cpStyles = ($themeMetaModel->CP_CUSTOM_CSS) ? $themeMetaModel->CP_CUSTOM_CSS : 'cp.css';
	  $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/cp/'.$folderPath.'/css/'.$cpStyles);
	}


    $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css');
    //$this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/ketchup/js/jquery.min.js');
    $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js');
    $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages_cp_register.js');
    $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js');

    $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/facebox/facebox.css');
   // $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/facebox/facebox.js');

    // adding admin JS file
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'/admin/assets/js/admin.js');
    $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/cp/assets/js/cp.js');
    $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/cp/assets/css/cp.css');
    //$this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/js/jquery.min.js');
//    $this->view->headScript()->appendFile('/cp/assets/js/cp.js');

    $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
  }

  public function indexAction() {
    $this->_redirect("/");
    // action body
  }

  /**
   * allow cps to authanticate to enter in cp back office
   * @return none
   * @access public
   * @author cheran cheran@nexva.com
   */
  public function loginAction() {
      


    /*Developer visits*/
        
        $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');
        $referer = $this->getRequest()->getServer('HTTP _REFERER');
        $developerVisites=new Cpbo_Model_DeveloperVisits();        
        $developerVisites->saveStatics($ipAddress, $referer);            
       
    /*End*/
            
    /*get translater*/
    $translate = Zend_Registry::get('Zend_Translate');

    if($this->_request->ref == 'appclaim' )   {
    	
        $this->view->tab = "tab-login";
        $this->view->message = $translate->translate("In order to claim your application, you must first login to your Content Provider (CP) account. 
                            If you don't have an account, please <a href=".CP_PROJECT_BASEPATH."'/user/register' target='_blank'> click here </a>  to register (once you have registered and verified your email 
                            address, you can try to claim your application again).");
    }
    
    
        $url = $this->view->url ( array ('action' => 'verify', 'resend' => 1, 'email' => $this->_request->email ) );
		$this->view->email = rawurldecode ( urlencode ( $this->_request->email ) );
		$this->view->url = $url;
				
		$captcha = new Zend_Service_ReCaptcha(Zend_Registry::get('config')->recaptcha->public_key, Zend_Registry::get('config')->recaptcha->private_key);

        $captcha->setOption('theme', "white");
        $this->view->recaptcha = $captcha->getHTML();
		
		// if the account is not verified after creating the account 
		if ($this->_request->verify === '0') {
			
			$this->view->tab = "tab-login";
			$this->view->warningUnverifiedAccount = 1;
		
		}
		
		// if the account is not verified after resend the verify email 
		if ($this->_request->unverified)
			$this->view->warningVerifyEmailRequest = 1;
		
		if (isset ( $_COOKIE ['username'] ) and isset ( $_COOKIE ['password'] )) {
			
			$session_logout = new Zend_Session_Namespace ( 'logoutaction' );
			
			if ($session_logout->logout != true) {
				
				$this->doLogin ( $_COOKIE ['username'], stripcslashes ( $_COOKIE ['password'] ) );
			}
		}

		if ($this->getRequest ()->isPost ()) {
			switch ($this->_request->getParam ( 'operation' )) {
				
				case 'login' :
					$this->doLogin (  $this->getRequest ()->getParam ( 'username' ), $this->getRequest ()->getParam ( 'password' ) );
					break;
				
				case 'forgot_password' :
					$this->doForgotPassword ( urldecode ( $this->_request->getParam ( 'email' ) ) );
					break;
				
				default :
					throw new Zend_Exception ( $translate->translate("Unknown operation attempted").": " . $this->_request->getParam ( 'operation' ) );
			}
		} else
			$this->view->tab = "tab-login";
		
				
		if( $this->_request->chap != '') 
			$chapId = $this->_request->chap;
		else  {
			$chapId = 'nexva.mobi'; //nexva's id
                   
                    $theamMeta = new Model_ThemeMeta();
                    $userId = @$theamMeta->getUrlByHostNameForDeveloper($_SERVER['SERVER_NAME']);
                    if($userId){
                    $userModel = new Model_User();
                    $chapId = $userModel->getUserById($userId)->username;
                    }
                }
                $this->view->chapId=$chapId;
                
		
                $userModel = new Model_User();
		$chap = $userModel->getUserByUsername($chapId);
		$chapSessionNs = new Zend_Session_Namespace('chap');
		$chapSessionNs->chap  = $chap;

      //google login
      //if($this->_request->chap == 'caboapps'){
    if(81604 == $chapSessionNs->chap->id){
        $googleAuth = new Nexva_Google_GoogleAuthQelasy();
    } else {
        $googleAuth = new Nexva_Google_GoogleAuthCp();
    }
        $this->view->googleUrl = $googleAuth->createAuthUrl();

      //}
        
      //facebook login
      if (!is_null($this->_request->facebook)) {

          if(!is_null($this->_request->chap_id)){
              $chapId =  $this->_request->chap_id;
              $userModel = new Model_User();
              $chap = $userModel->getUserById($chapId);
          }

          $chapSessionNs = new Zend_Session_Namespace('chap');
          $chapSessionNs->chap  = $chap;

          if(81604 == $chapId){

              $fb = new Nexva_Facebook_FacebookConnect('web', '274873269367132', '4250c2ca22f4fa15ef0b16cb22faec01');

              //check of the user cancelled FB auth attempt or error in auth attempt
              if( !is_null($this->_request->error_reason)) $this->_redirect(CP_PROJECT_BASEPATH.'/user/login'); //somehow FB's PHP SDK never sets this :( See next line
              if( !$fb->hasUserAuthorizedApp() ) {
                  $this->_helper->flashMessenger->setNamespace('error')->addMessage($translate->translate("An error occurred, please try again."));
                  $this->_redirect(CP_PROJECT_BASEPATH.'/user/login'); //hopefully, a foolproof way to check for authorization even if line above fails
              }

              $fbUser = $fb->getLoggedFbUser();
             
              $userModel = new Model_User();
              $url = $userModel->doLoginFb($fbUser['email'], 'web', '274873269367132', '4250c2ca22f4fa15ef0b16cb22faec01', $chapId);

              $this->_redirect('http://dev.qelasy.com/');

          } else {
              $fb = new Nexva_Facebook_FacebookConnect('web', '795172363840019', 'a86be10b6b07ac0787718c25920f2890');

              //check of the user cancelled FB auth attempt or error in auth attempt
              if( !is_null($this->_request->error_reason)) $this->_redirect(CP_PROJECT_BASEPATH.'/user/login'); //somehow FB's PHP SDK never sets this :( See next line
              if( !$fb->hasUserAuthorizedApp() ) {
                  $this->_helper->flashMessenger->setNamespace('error')->addMessage($translate->translate("An error occurred, please try again."));
                  $this->_redirect(CP_PROJECT_BASEPATH.'/user/login'); //hopefully, a foolproof way to check for authorization even if line above fails
              }

              $fbUser = $fb->getLoggedFbUser();
               
              $userModel = new Model_User();
              $url = $userModel->doLoginFb($fbUser['email'], 'web', '795172363840019', 'a86be10b6b07ac0787718c25920f2890', $chapId);

              $this->_redirect('http://cp.nexva.com');
          }

          //$this->_redirect('http://cp.nexva.com/r/'.$chapSessionNs->chap->username);
      }



		$this->_helper->layout->setLayout ( 'cp_login' );
                
                //get the chap id 
                $session = new Zend_Session_Namespace('chap');    
                $chapId = $session->chap->id;

                $themeMetaModel = new Model_ThemeMeta();
                $themeMetaModel->setEntityId($chapId);        
        
                $this->view->cpDescription = ($themeMetaModel->CP_HOME_DESCRIPTION) ? $themeMetaModel->CP_HOME_DESCRIPTION : '';
                
              
	}

  /**
   *
   * Authenticates and logs in a user user by $username and $password
   *
   * @param string $username
   * @param string $password
   */
  protected function doLogin($username, $password) {
  	
  	
  	if ('' != $username && '' != $password) {
  	
  	    $user = new Model_User();
        $userRow = $user->fetchRow( $user->select()->where('email = ?', $username) );
        
        if($userRow)    {
        
         $userMeta = new Model_UserMeta();
         $verified  =  $userMeta->getAttributeValue($userRow->id,'VERIFIED_ACCOUNT');
         
         

        
		if ($verified != '0' ) {
		
				
				$db = Zend_Registry::get ( 'db' );
				$authDbAdapter = new Zend_Auth_Adapter_DbTable ( $db, 'users', 'email', 'password', "MD5(?) and status=1 " );
				$authDbAdapter->setIdentity ( $username );
				$authDbAdapter->setCredential ( $password );
				
				$result = Zend_Auth::getInstance ()->authenticate ( $authDbAdapter );
				
				if ($result->isValid ()) {
					Zend_Auth::getInstance ()->getStorage ()->write ( $authDbAdapter->getResultRowObject ( null, 'password' ) );
					
					if ($this->getRequest ()->getParam ( 'remember' ) == 1) {
						
						//$cookie_username = new Zend_Http_Cookie('username',$this->getRequest()->getParam('username'), 'cp.nexva-v2-dev.com', time()+72000,'/');
						unset ( $_COOKIE ['username'] );
						unset ( $_COOKIE ['password'] );
						$session_logout = new Zend_Session_Namespace ( 'logoutaction' );
						
						$session_logout->logout = false;
						setcookie ( 'username', $this->getRequest ()->getParam ( 'username' ), time () + (60 * 60 * 24 * 30) );
						//$cookie_password = new Zend_Http_Cookie('password',$this->getRequest()->getParam('password'), 'cp.nexva-v2-dev.com', time()+72000,'/');
						setcookie ( 'password', $this->getRequest ()->getParam ( 'password' ), time () + (60 * 60 * 24 * 30) );
					}
					
					//Save last login details
					$auth = Zend_Auth::getInstance ();
					$user = new Cpbo_Model_UserMeta ( );
					
					$user->setEntityId ( $auth->getIdentity ()->id );
					
					$user->LAST_LOGIN = date ( 'Y-m-d h:i:s' );
					
					//Redirect to requested page
					$session = new Zend_Session_Namespace ( 'lastRequest' );
					
					if (isset ( $session->lastRequestUri )) {
						$newpage = $session->lastRequestUri;
						$session->lastRequestUri = NULL;
						$this->_redirect ( $newpage );
						return;
					}
					$this->_redirect (CP_PROJECT_BASEPATH . "/user/login");
				} else {
					$this->view->tab = "tab-login";
					$this->view->error = "Username or password is incorrect.";
				}
			
		
		} else {
			
		$url = $this->view->url(array('action'=>'verify', 'resend' => 1, 'email' => $username ));

        $this->view->tab = "tab-login";
        $this->view->email = $username;
        $this->view->url = $url;
		$this->view->warningNotRegistred = 1;

		
		}
		
        }
        
        else
        {
        	
        	   $this->view->tab = "tab-login";
               $this->view->error = "Please enter valid username.";
        	
        	
        	
        }
		
  	} else {
                $this->view->tab = "tab-login";
                $this->view->error = "Please enter your username and password.";
            }
	
	}
  
/**
   *
   * Authenticates and logs in a user user by $username and $password
   *
   * @param string $username
   * @param string $password
   */
  protected function doLoginVerify($username) {
  	
  	

  	  $db = Zend_Registry::get('db');
      $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'email', 'email');
      $authDbAdapter->setIdentity($username);
      $authDbAdapter->setCredential($username);

      $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);

      if ($result->isValid()) {
        Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));

        //Save last login details
        $auth = Zend_Auth::getInstance();
        $user = new Cpbo_Model_UserMeta();

        $user->setEntityId($auth->getIdentity()->id);
        $user->LAST_LOGIN = date('Y-m-d h:i:s');

        $this->_redirect("/");
    }
  }

  /**
   * Generates password reset link and emails to user
   *
   * @param string $email
   */
  protected function doForgotPassword($email) {
		/*get translater*/
                $translate = Zend_Registry::get('Zend_Translate');
		$user = new Cpbo_Model_User ( );
		$this->view->tab = "tab-password";
		$this->view->email = $email;
		$captcha = new Zend_Service_ReCaptcha(Zend_Registry::get('config')->recaptcha->public_key, Zend_Registry::get('config')->recaptcha->private_key);
		
		
		try {
			
			if (isset ( $this->_request->recaptcha_challenge_field ) and ($this->_request->recaptcha_response_field)) {
				$resp = $captcha->verify ( $this->_request->recaptcha_challenge_field, $this->_request->recaptcha_response_field );
				if (! $resp->isValid ()) {
					
					$this->view->error = $translate->translate("The security key wasn't entered correctly. Please try again.");
					return;
				} else {
					
					
					$user->sendPasswordResetMail ( $email );
		
					$this->view->message = $translate->translate("Instructions on how to reset your password has been emailed to")." " . $email;
				}
			} else {
				$this->view->error = "The security key wasn't entered correctly. Please try again.";
				return;
			}
		
		} catch ( Zend_Exception $e ) {
			
			$this->view->error = "The email you supplied was not found.";
		}
		
		
	}

  public function resetpasswordAction() {
      
      
      $this->_redirect(PROJECT_BASEPATH."/user/login");
      
    $otp = new Nexva_Util_OTP_OneTimePassword();
    $config = Zend_Registry::get('config');

    $user = new Cpbo_Model_User();
    $userRow = $user->fetchRow($user->select()->where('id = ?', $this->_request->getParam('id')));

    //Zend_Debug::dump($userRow->password);die();


    $otp->setSalt($config->nexva->application->salt);
    $otp->setTimeout($this->_request->getParam('time'));
    $otp->setId($this->_request->getParam('id'));
    $otp->setPassword($userRow->password);

    $result = $otp->verifyOTPHash($this->_request->getParam('otphash'));

    if (!$result)
      $this->view->error = "The password reset request is no longer valid.";

    $this->view->valid_request = $result;

    if ($this->_request->isPost()) { //everything is good - reset the password and redirect
      $user->resetPassword($userRow->id, $this->_request->password);
      $this->_redirect(PROJECT_BASEPATH."/user/login");
    }

    $this->_helper->layout->setLayout('cp_generic');
  }
  
 
  function registerAction() {

    /* Initialize action controller here */
    $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/facebox/facebox.css');

    $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/facebox/facebox_cp_registration.js');
    $config = Zend_Registry::get('config');
    $publickey = $config->recaptcha->public_key;

    $captcha = new Zend_Service_ReCaptcha(Zend_Registry::get('config')->recaptcha->public_key, Zend_Registry::get('config')->recaptcha->private_key);

    $captcha->setOption('theme', "clean");
    $this->view->recaptcha = $captcha->getHTML();

    $this->view->password_field_required = "required(Password field),";
    $this->view->password_retypefield_required = "required(Password retype field),";

    $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.cp.validate.start.js');
    
    $this->_helper->layout->setLayout('cp_register');
    
    $this->view->title = "Register a new account";

    //Get all countries and asign that to view
    $countryModel = new Model_Country();
    $this->view->countries = $countryModel->getAllCountries();

    $this->view->window_title = "Register a new account.";
    $this->view->button_value = "Register";
    
    $this->view->logo = 'logo.png';

    $session = new Zend_Session_Namespace('chap');
    $chapId = $session->chap->id;

    $themeMeta   = new Model_ThemeMeta();
    $themeMeta->setEntityId($chapId);

      $settingsArrayKeys   = array(
          'WHITELABLE_SITE_TITLE',
          'WHITELABLE_SITE_META_KEYS',
          'WHITELABLE_SITE_META_DES',
          'WHITELABLE_SITE_GOOGLE_ANALYTIC',
          'WHITELABLE_SITE_CONTACT_US_EMAIL',
          'WHITELABLE_SITE_ADVERTISING',
          'WHITELABLE_SITE_APPSTORE_VERSION',
          'WHITELABLE_SITE_INTERVAL',
          'WHITELABLE_SITE_BANNER_COUNT',
          'WHITELABLE_SITE_FETURED_APPS',
          'WHITELABLE_SITE_FAVICON',
          'WHITELABLE_SITE_LOGO',
          'WHITELABLE_SITE_AGREEMENT_TEXT',
          'WHITELABLE_SITE_AGREEMENT_LINK',
          'CP_PANEL_TITLE',
          'CP_HOME_DESCRIPTION',
          'CP_HEADER_COLOUR',
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

      //echo $themeMeta->WHITELABLE_SITE_AGREEMENT_LINK;die();
    //$pageModel = new Pbo_Model_WlPages();
    //$pages = $pageModel->getPagesByChapID($chapId);

    $pageModel = new Pbo_Model_WlPages();
    $pageDetails = $pageModel->getPageDetailsbyId($chapId, $themeMeta->WHITELABLE_SITE_AGREEMENT_LINK);

    $this->view->pageDetails = $pageDetails;

    //Zend_Debug::dump($pageDetails);die();

    if ( "" != trim($this->_request->r)) $this->view->r = $this->_request->r;

    foreach ($_POST as $key => $value) {
      $this->view->$key = $value;
    }

    //Save user details
    if ($this->_request->isPost()) {

      $user = new Cpbo_Model_User();
      $captchaNum = $this->_request->captcha;
      $common_check = $user->registrationCommonCheck($this->getRequest()->getParams());

      if ($common_check->isValid()) {

        $username_check = 'NULL';



        $password_check = $user->checkPassword($common_check->password, $common_check->retypepassword);

        if (true !== $password_check) {
          $this->view->user_add_res = $password_check;
          return;
        }


        $email_check = $user->validateEmail($common_check->email);
        if (true !== $email_check) {
          $this->view->user_add_res = $email_check;
          return;
        }

        //validate captcha
//                if( !Nexva_Captcha_CaptchaImage::validate($this->_request->captcha_id, $this->_request->captchaval) ) {
//                    $this->view->user_add_res   = "The security key you entered is not correct. Please try again.";
//                    $this->view->request = $this->_request;
//                    return ;
//                }

        //if (isset($this->_request->recaptcha_challenge_field) and ($this->_request->recaptcha_response_field)) {
        //  $resp = $captcha->verify($this->_request->recaptcha_challenge_field, $this->_request->recaptcha_response_field);
          if ($captchaNum != $_SESSION['code']) {
            $this->view->user_add_res = ("The security key wasn't entered correctly. Please go back and try it again.");
            $this->view->request = $this->_request;
            return;
          }
        //} 
        
        $session = new Zend_Session_Namespace('chap');    
        $chapId = $session->chap->id;

        $userData = array
          (
          'username' => $common_check->username,
          'email' => $common_check->email,
          'password' => $common_check->password,
          'type' => "CP",
          'login_type' => "NEXVA",
          'chap_id' => $chapId,
          'ip' => json_encode($_SERVER)
                             
        );

        
         if (strpos($common_check->email,'.ru') !== false)
             die();

        $user_id = $user->createUser($userData);
        $skip_fields = array('username', 'r', 'email', 'password', 'retypepassword', 'captchaval', 'captcha_id', 'submit');
        
        $userMeta = new Model_UserMeta();
        $userMeta->setEntityId($user_id);
        $userMeta->setAttributeValue($user_id, 'VERIFIED_ACCOUNT', '0');

        if( $this->_request->r != "") {
        	//$userMeta->REFERRER = strtolower($this->_request->r);
        }
        
        
        $userMetaModel = new Cpbo_Model_UserMeta();
        $userMetaModel->setEntityId($user_id);


        foreach ($_POST as $key => $value) {
          if ($value != null) {

            //echo "key =>".$key." value =>".$value."<br />";
            // $this->view->user[$key]=$value;
          }
          if (!in_array($key, $skip_fields)) {

            $keyupper = strtoupper($key);
            $stripped_value = htmlentities(strip_tags($value));
            $userMetaModel->$key = $stripped_value;
          }
        }



        $this->view->user_add_msg = "Registration was success.  <a href='".CP_PROJECT_BASEPATH."/user/login/'>Login</a>";

        #Registration is success ,clear all fields
        $this->view->user = NULL;
        $this->view->first_name = NULL;
        $this->view->last_name = NULL;
        $this->view->email = NULL;
        $this->view->username = NULL;
        $this->view->company_name = NULL;
        $this->view->department = NULL;
        $this->view->designation = NULL;
        $this->view->street = NULL;
        $this->view->city = NULL;
        $this->view->province = NULL;
        $this->view->support_email = NULL;
        $this->view->telephone = NULL;
        $this->view->fax = NULL;
        $this->view->web = NULL;

        #Add details to user meta table

       /// $this->doLogin($common_check->email, $common_check->password);
  
        $userModel = new Model_User();
        $userModel->sendVerifyAccountMail($common_check->email, 'cpbo');
        $this->_redirect(CP_PROJECT_BASEPATH.'user/login/verify/0/email/'.$common_check->email);
        
      }else {
        $messages = $common_check->getMessages();

        $this->view->user_add_res = array_pop($messages);
      }
    } else {
      if ($this->_request->submit != '') {
        $this->view->user_add_res = 'Please fill all the fields.';
      }
    }

  }

  function agreementAction() {
    $this->_helper->layout()->disableLayout();

    $modelPage = new Model_PageLanguages();
    
//    die("->".$this->_request->r);die();

    $nexvaAgreement = current($modelPage->find($this->_request->getParam('id', 1))->toArray());


    if( $this->_request->r != "") {

        $text = $modelPage->getPageBySlug('agreement_'.strtolower($this->_request->r));

        if( !is_null($text) )
            $this->view->agreement = $text;
        else //agreement for this referer not found... revert to nexva's default.
            $this->view->agreement = $nexvaAgreement;

    }
    else
        $this->view->agreement = $nexvaAgreement;



    
   
    
  }

  function logoutAction() {

    $this->_helper->viewRenderer->setNoRender(true);
    Zend_Auth::getInstance()->clearIdentity();
    $session_logout = new Zend_Session_Namespace('logoutaction');
    $session_logout->logout = true;
    
    if($this->_request->r and $this->_request->r != 'nexva.mobi')
        $redirectUrl = '/r/'.$this->_request->r;
    else 
        $redirectUrl = '/';

    $this->_redirect($redirectUrl);
    
  }

  function profileAction() {
      /*get translater*/
    $translate = Zend_Registry::get('Zend_Translate');

    $this->view->username_field_status = "disabled=disabled";

    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.cp.validate.start.js');
    $this->view->window_title = $translate->translate("Update your details");
    $this->view->button_value = $translate->translate("Save");
    $this->view->title = $translate->translate("Update your details");
    if ($this->getRequest()->isPost() and '' != $this->getRequest()->getParam('submit')) {


      $filters = array(
        '*' => array('StripTags', 'StringTrim')
      );


      $validaters = array(
        'email' => array(
          'allowEmpty' => false,
          new Zend_Validate_EmailAddress(Zend_Validate_Hostname::ALLOW_DNS | Zend_Validate_Hostname::ALLOW_LOCAL),
          new Zend_Validate_Db_NoRecordExists('users', 'email', array('field' => 'id', 'value' => Zend_Auth::getInstance()->getIdentity()->id))
        ),
        'password' => array('allowEmpty' => true, array('StringLength', 4, 25)),
        'retypepassword' => array('allowEmpty' => true, array('StringLength', 4, 25))
      );

      $input = new Zend_Filter_Input($filters, $validaters, $this->getRequest()->getParams());

      if ($input->isValid()) {

        $filterd = $input->getEscaped();
        $update = true;

        if ('' == $this->getRequest()->getParam('password') or '' == $this->getRequest()->getParam('retypepassword')) {

          $profile_details = array
            (
            "username" => $this->getRequest()->getParam('username'),
            "email" => $filterd['email'],
          );
        } else {

          if (0 == strcmp($this->getRequest()->getParam('password'), $this->getRequest()->getParam('retypepassword'))) {
            $profile_details = array
              (
              "username"   => $this->getRequest()->getParam('username'),
              "email" => $filterd['email'],
              "password" => md5($this->getRequest()->getParam('password'))
            );
          } else {
            $update = false;
            $this->view->error = $translate->translate("Passwords you entered are not same.");
          }
        }

        if ($update == true) {
          $user = new Cpbo_Model_User();
          $user->update($profile_details, "id=" . Zend_Auth::getInstance()->getIdentity()->id);
          $this->view->message = $translate->translate("Your profile details have been updated.");



          #Send a email
          $subject_text = "Your neXva.com profile has been updated";
          $user = new Cpbo_Model_UserMeta();
          $user->setEntityId(Zend_Auth::getInstance()->getIdentity()->id);
          $mailer = new Nexva_Util_Mailer_Mailer();
          $userModel = new Cpbo_Model_User();
          $userDetails = current($userModel->find(Zend_Auth::getInstance()->getIdentity()->id)->toArray());
          
                    $session=new Zend_Session_Namespace('chap');
                    $config = Zend_Registry::get('config');
                    $chapIds = explode(',', $config->nexva->application->frenchchaps);

                    if (in_array($session->chap->id, $chapIds)) {
                        $template = 'profile_updated_fr.phtml';
                    } else {
                        $template = 'profile_updated.phtml';
                    }

                    $mailer->addTo($userDetails['email'], $user->FIRST_NAME . " " . $user->LAST_NAME)
              ->setSubject($subject_text)
              ->setLayout("generic_mail_template")
              ->setMailVar("name", ucfirst($user->FIRST_NAME . " " . $user->LAST_NAME))
              ->sendHTMLMail($template);
        }
      } else {

        $errors = '';
        foreach ($input->getMessages() as $error) {
          foreach ($error as $er) {
            $errors .= $er . "<br />";
          }
        }
        $this->view->error = $errors;
      }
    }

    $cpauth = Zend_Auth::getInstance();
    //$this->_helper->layout->setLayout('cp_register');
    $user = new Cpbo_Model_User();
    $user_row = $user->fetchRow("id = " . $cpauth->getIdentity()->id);
    $this->view->user = $user_row->toArray();
  }

    public function resetAccountIdAction() {

        $this->_helper->viewRenderer->setNoRender(true);

        $userMeta = new Cpbo_Model_UserMeta();
        $userMeta->setEntityId(Zend_Auth::getInstance()->getIdentity()->id);

        $cp = new Cpbo_Model_User();
        $accountId = $cp->generateAccountId(Zend_Auth::getInstance()->getIdentity()->id);
        
        
        $userMeta->ACCOUNT_ID = $accountId;
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');

        $this->_flashMessenger->addMessage($translate->translate("Your neXva Account ID was reset successfully"));
        $this->_redirect('/user/details/#account-settings');
  }

  /**
   * Display and save user meta data
   * @return none
   * @access public
   *
   */
  function detailsAction() {

  	/**
     * @todo: This whole function is FUCKED UP. The switch statement is epically fucked up cheran style. refactor this shit.
     */
      /*get translater*/
    $translate = Zend_Registry::get('Zend_Translate');
    
    $this->view->headScript()->appendFile('/cp/assets/js/common_ajax_functions.js');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.cp.validate.start.js');
    $message = $this->_flashMessenger->getMessages();
    if($message)    
        $this->view->message =  $message[0];
    
    
    $auth = Zend_Auth::getInstance();
    $user = new Cpbo_Model_UserMeta();
    $user->setEntityId($auth->getIdentity()->id);
    
    
    $url = 'http://' . Zend_Registry::get('config')->nexva->application->base->url . "/np/".$auth->getIdentity()->id.'.';

    $this->view->user_meta = $user;
    $this->view->title = "Update your details";

    if( $user->ACCOUNT_ID == "") //no account id for this user. silenty generate one.
    {
        $userModel = new Cpbo_Model_User();
        $user->ACCOUNT_ID = $userModel->generateAccountId($auth->getIdentity()->id);
        $user->reloadCache($auth->getIdentity()->id);
    }


    //Get all countries and asign that to view
    $countryModel = new Model_Country();
    $this->view->countries = $countryModel->getAllCountries();
    
    $cpauth = Zend_Auth::getInstance();
     //$this->_helper->layout->setLayout('cp_register');
    $user = new Cpbo_Model_User();
    $user_row = $user->fetchRow("id = " . $cpauth->getIdentity()->id);
    $this->view->user = $user_row->toArray();
    
     $user = new Cpbo_Model_UserMeta();
     $user->setEntityId($auth->getIdentity()->id);
    
    $this->view->nexpageUrl = 'http://' . Zend_Registry::get('config')->nexva->application->base->url . "/np/".$auth->getIdentity()->id;
    
    $this->view->nexpageCpUrl = 'http://' . Zend_Registry::get('config')->nexva->application->base->url . "/cp/".$this->view->slug($user->COMPANY_NAME)
                                .".".$auth->getIdentity()->id.".en";
    
    $uploadSuccess = false;
    
    
    if($this->_request->deleted == 1)    
        $this->view->tab_selected = "company";
    
    
    if ($this->getRequest()->isPost()) {
 
      if ('' == $this->getRequest()->getParam('Save')) {
        return;
      }

      switch ($this->getRequest()->getParam('Save')) {
      	    	
     case "Save login details":
     	  	
      $filters = array(
        '*' => array('StripTags', 'StringTrim')
      );

      $validaters = array(
        'email' => array(
          'allowEmpty' => false,
          new Zend_Validate_EmailAddress(Zend_Validate_Hostname::ALLOW_DNS | Zend_Validate_Hostname::ALLOW_LOCAL),
          new Zend_Validate_Db_NoRecordExists('users', 'email', array('field' => 'id', 'value' => Zend_Auth::getInstance()->getIdentity()->id))
        ),
        'password' => array('allowEmpty' => true, array('StringLength', 4, 25)),
        'retypepassword' => array('allowEmpty' => true, array('StringLength', 4, 25))
      );

      $input = new Zend_Filter_Input($filters, $validaters, $this->getRequest()->getParams());

      if ($input->isValid()) {

        $filterd = $input->getEscaped();
        $update = true;

        if ('' == $this->getRequest()->getParam('password') or '' == $this->getRequest()->getParam('retypepassword')) {

          $profile_details = array
            (
            "username" => $this->getRequest()->getParam('username'),
            "email" => $filterd['email'],
          );
        } else {

          if (0 == strcmp($this->getRequest()->getParam('password'), $this->getRequest()->getParam('retypepassword'))) {
            $profile_details = array
              (
              "username"   => $this->getRequest()->getParam('username'),
              "email" => $filterd['email'],
              "password" => md5($this->getRequest()->getParam('password'))
            );
          } else {
            $update = false;
            $this->view->error = "Passwords you entered are not same.";
          }
        }


        

        if ($update == true) {
          $user = new Cpbo_Model_User();
          $user->update($profile_details, "id=" . Zend_Auth::getInstance()->getIdentity()->id);
          $this->view->message = "Your profile details have been updated.";
          $redirectUrl = 'user/details#tabs-1';
        }
      } else {

        $errors = '';
        foreach ($input->getMessages() as $error) {
          foreach ($error as $er) {
            $errors .= $er . "<br />";
          }
        }
        $this->view->error = $errors;
 
        
      }

      break;
				
				case "Save name and company details" :
					
					$this->view->update_res = "Company details have been saved";
					
					
					$max_height = "120"; // This is in pixels
					$max_width = "150"; // This is in pixels
					

					if (is_uploaded_file ( $_FILES ['logo'] ['tmp_name'] )) {
						$extentionValid = '';
						$sizeValid = 1;
						
						// These are the allowed extensions of the files that are uploaded
						$allowed_ext = "jpg, gif, png, jpeg"; 
						

						// Check Entension
						$extension = pathinfo ( $_FILES ['logo'] ['name'] );
						$extension = strtolower($extension ['extension']);
						
						$allowed_paths = explode ( ", ", $allowed_ext );
						for($i = 0; $i < count ( $allowed_paths ); $i ++) {
							if ($allowed_paths [$i] == $extension) {
								$extentionValid = 1;
							
							}
						}
						
						// Check height & width
						list ( $width, $height, $type, $w ) = getimagesize ( $_FILES ['logo'] ['tmp_name'] );
						if ($width > $max_width || $height > $max_height) {
							$sizeValid = 0;
						}

						$user_id = Zend_Auth::getInstance ()->getIdentity ()->id;
						
						// 	if($extentionValid AND $sizeValid)     { 
						

						if ($extentionValid) {
						   
							//	move_uploaded_file ( $_FILES ['logo'] ['tmp_name'], $_SERVER ['DOCUMENT_ROOT'] . '/cp/static/logos' . '/logo_'. $user_id.'.'.$extension);
							
						   	$image = new Nexva_Util_ImageResize_ZebraImage ( );
							
							// indicate a source image (a GIF, PNG or JPEG file)
							$image->source_path = $_FILES ['logo'] ['tmp_name'];
							
							// indicate a target image
							// note that there's no extra property to set in order to specify the target image's type -
							// simply by writing '.jpg' as extension will instruct the script to create a 'jpg' file
							$image->target_path = $_SERVER ['DOCUMENT_ROOT'] . '/cp/static/logos' . '/logo_' . $user_id . '.' . $extension;
							
							// since in this example we're going to have a jpeg file, let's set the output image's quality
							$image->jpeg_quality = 100;
							
							// some additional properties that can be set
							// read about them in the documentation
							$image->preserve_aspect_ratio = true;
							$image->enlarge_smaller_images = true;
							$image->preserve_time = true;
							
							// resize the image to exactly 100x100 pixels by using the "crop from center" method
							// (read more in the overview section or in the documentation)
							//  and if there is an error, check what the error is about
							if (! $image->resize ( 120, 120, ZEBRA_IMAGE_CROP_CENTER )) {
								
								// if there was an error, let's see what the error is about
								switch ($image->error) {
					                /*   this is for debugging  
									case 1 :
										echo 'Source file could not be found!';
										break;
									case 2 :
										echo 'Source file is not readable!';
										break;
									case 3 :
										echo 'Could not write target file!';
										break;
									case 4 :
										echo 'Unsupported source file format!';
										break;
									case 5 :
										echo 'Unsupported target file format!';
										break;
									case 6 :
										echo 'GD library version does not support target file format!';
										break;
									case 7 :
										echo 'GD library is not installed!';
										break;
				                        */
								}
								
							

							} else {
						
							$uploadSuccess = true;
							
							}
		
						}
					
					}
					
					$field_array = array (
					   'FIRST_NAME' => $this->getRequest ()->getParam ( 'first_name' ), 
					   'LAST_NAME' => $this->getRequest ()->getParam ( 'last_name' ), 
					   'COMPANY_NAME' => $this->getRequest ()->getParam ( 'company_name' ), 
					   'DEPARTMENT' => $this->getRequest ()->getParam ( 'department' ), 
					   'DESIGNATION' => $this->getRequest ()->getParam ( 'designation' ), 
					   'COMPANY_DESCRIPTION' => $this->getRequest ()->getParam ( 'company_description' ) 
		            );
		            
         
         $this->_flashMessenger->addMessage($translate->translate("Your profile details have been updated."));
         $redirectUrl = 'user/details#tabs-2';
          break;

        case "Save residence details":

          $field_array = array(
            "ADDRESS" => $this->getRequest()->getParam('address'),
            "CITY" => $this->getRequest()->getParam('city'),
            "PROVINCE" => $this->getRequest()->getParam('province'),
            "COUNTRY" => $this->getRequest()->getParam('country'),
            "ZIP" => $this->getRequest()->getParam('zip'),
            "GOOGLE_MAP_URL" => $this->getRequest()->getParam('google_map_url'),
            "TELEPHONE" => $this->getRequest()->getParam('telephone'),
            "FAX" => $this->getRequest()->getParam('fax'),
            "WEB" => $this->getRequest()->getParam('web'),
            "FACEBOOK" => $this->getRequest()->getParam('facebook'),
            "TWITTER" => $this->getRequest()->getParam('twitter'),
            "LINKEDIN" => $this->getRequest()->getParam('linkedin'),
            "SKYPE" => $this->getRequest()->getParam('skype')
          );

          $this->_flashMessenger->addMessage($translate->translate("Your contact details have been updated."));
          $redirectUrl = 'user/details#tabs-3';
          break;
          
        case "Save support details":

          $field_array = array(
            "SUPPORT_EMAIL" => $this->getRequest()->getParam('support_email'),
            "SUPPORT_TEXT" => $this->getRequest()->getParam('support_text')
          );
          $this->_flashMessenger->addMessage($translate->translate("Support details have been saved."));
          $this->_redirect('user/details#tabs-4');
          break;

        case "Save payout options":
          $field_array = array(
            /* "BANK_NAME"                  => $this->getRequest()->getParam('bank_name'),
              "ACCOUNT_NUMBER"              => $this->getRequest()->getParam('account_number'),
              "ACCOUNT_HOLDER_NAME"         => $this->getRequest()->getParam('account_holder_name'),
              "ACCOUNT_HOLDER_ADDRESS"      => $this->getRequest()->getParam('account_holder_address'),
              "SWIFT_CODE"         => $this->getRequest()->getParam('swift_code'),
              "IBAN"               => $this->getRequest()->getParam('iban'), */
            "PAYPAL_ADDRESS" => $this->getRequest()->getParam('paypal_address')
          );
          $this->_flashMessenger->addMessage($translate->translate("Payout options details have been saved."));
          $redirectUrl = 'user/details#tabs-5';
            
          break;

        case "Save white label details":


          $user_id = Zend_Auth::getInstance()->getIdentity()->id;
          $url = $this->getRequest()->getParam('white_label');
          $modelUserMeta = new Cpbo_Model_UserMeta();
          $urlExists = $modelUserMeta->select();
          $urlExists->from('user_meta')
              ->where("meta_name = 'WHITE_LABEL' and meta_value =  '$url' and user_id != $user_id");

          if (1 != count($modelUserMeta->fetchAll($urlExists))) {


            $field_array = array(
              "WHITE_LABEL" => $this->getRequest()->getParam('white_label')
            );


            $this->view->update_res = "White label details have been saved";
            $this->view->tab_selected = "whitelabel";
            break;
          } else {
            $this->view->update_res = "This whitelabel url is already exists.";
            $this->view->tab_selected = "whitelabel";
            break;
          }
      }
      
      if($this->getRequest()->getParam('Save') != 'Save login details')    {

      $filters = array
        (
        '*' => array('StripTags', 'StringTrim')
      );

      $validaters = array
        (
        'first_name' => array(
          'allowEmpty' => false,
        ),
        'last_name' => array
          (
          'allowEmpty' => false,
        ),
        'company_name' => array
          (
          'allowEmpty' => false,
        ),
        'department' => array
          (
          'allowEmpty' => 'true',
        ),
        'designation' => array
          (
          'allowEmpty' => 'true',
        ),
        'address' => array
          (
          'allowEmpty' => 'true'
        ),
        'city' => array
          (
          'allowEmpty' => 'true'
        ),
        'province' => array
          (
          'allowEmpty' => 'true'
        ),
        'zip' => array
          (
          'allowEmpty' => 'true'
        ),
        'telephone' => array
          (
          'allowEmpty' => 'true'
        ),
        'fax' => array
          (
          'allowEmpty' => 'true'
        ),
        'web' => array
          (
          'allowEmpty' => 'true'
        ),
        'facebook' => array
          (
          'allowEmpty' => 'true'
        ),
        'twitter' => array
          (
          'allowEmpty' => 'true'
        ),
        'linkedin' => array
          (
          'allowEmpty' => 'true'
        ),
        'paypal_address' => array
          (
          'allowEmpty' => 'true'
        ),
        'support_email' => array
          (
          'allowEmpty' => false
        ),
        'support_text' => array
          (
          'allowEmpty' => 'true'
        ),
        'white_label' => array
          (
          'allowEmpty' => 'true'
        ),
        'country' => array
          (
          'allowEmpty' => 'true'
        ),
        'google_map_url' => array
          (
          'allowEmpty' => 'true'
        ),
        'company_description' => array
          (
          'allowEmpty' => 'true'
        ),
          'skype' => array
          (
          'allowEmpty' => 'true'
        ),
        'logo' => array
          (
          'allowEmpty' => 'true'
        )
        
      );
      
      $checkData = new Zend_Filter_Input($filters, $validaters, $this->getRequest()->getParams());
      $esc = $checkData->getEscaped();
				
				if ($checkData->isValid ()) {
					$user = new Cpbo_Model_UserMeta ( );
					$user->setEntityId ( $auth->getIdentity ()->id );
					
					foreach ( $field_array as $key => $field ) {
						$user->$key = $esc [strtolower ( $key )];
					}
					
					if ($uploadSuccess)
						$user->LOGO = 'logo_' . $user_id . '.' . $extension;
					
					$this->view->user_meta = $user;
					$subject_text = "Customer support details have been saved successfully";
					
					if ($this->getRequest ()->getParam ( 'Save' ) == 'Save support details') {
                                            $session=new Zend_Session_Namespace('chap');
                                            $config = Zend_Registry::get('config');
                                            $chapIds= explode(',',$config->nexva->application->frenchchaps);

                                                if (in_array($session->chap->id, $chapIds)) {
                                                    $template = 'support_details_change_fr.phtml';
                                                } else {
                                                    $template = 'support_details_change.phtml';
                                                }

                                                $mailer = new Nexva_Util_Mailer_Mailer ( );
						$userModel = new Cpbo_Model_User ( );
						$userDetails = current ( $userModel->find ( Zend_Auth::getInstance ()->getIdentity ()->id )->toArray () );
						
						$mailer->addTo ( $user->SUPPORT_EMAIL, $user->FIRST_NAME . " " . $user->LAST_NAME )->setSubject ( $subject_text )->setLayout ( "generic_mail_template" )->setMailVar ( "name", ucfirst ( $user->FIRST_NAME . " " . $user->LAST_NAME ) )->sendHTMLMail ($template);
					}
				}
				
				$this->_redirect($redirectUrl);
			}
    }
  }
  
  function logodeleteAction() {
        
  	    $auth = Zend_Auth::getInstance();
  	    $user = new Cpbo_Model_UserMeta();
        $user->setEntityId($auth->getIdentity()->id);
        unlink($_SERVER ['DOCUMENT_ROOT'] . '/cp/static/logos/' .  $user->LOGO);
        $user->LOGO = '';
        $this->_redirect ( '/user/details/deleted/1');
        
}  
  
  
function checkwhitelabelurlAction() {
    $this->_helper->ViewRenderer->setNoRender ( true );
    $this->_helper->layout ()->disableLayout ();
    $user_id = Zend_Auth::getInstance ()->getIdentity ()->id;
    $url = $this->_request->url;
    $modelUserMeta = new Cpbo_Model_UserMeta ( );
    $urlExists = $modelUserMeta->select ();
    $urlExists->from ( 'user_meta' )->where ( "meta_name = 'WHITE_LABEL' and meta_value =  '$url' and user_id != $user_id" );
    
    if (1 == count ( $modelUserMeta->fetchAll ( $urlExists ) )) {
        
        echo '0';
    } else {
        echo '1';
    }
    
}  
  



    function verifyAction() {
		
		$this->_helper->ViewRenderer->setNoRender ( true );
		$this->_helper->layout ()->disableLayout ();
		
		$email =  rawurldecode (urlencode ($this->_request->email));

		
		$userModel = new Model_User ( );
		$otp = new Nexva_Util_OTP_OneTimePassword ( );
		$userCpbo = new Cpbo_Model_User ( );
		$userMeta = new Model_UserMeta ( );
		$config = Zend_Registry::get ( 'config' );
		
		if ($this->_request->resend == 1) {
			
			$userModel->sendVerifyAccountMail( $email, 'cpbo' );
			$this->_redirect ( CP_PROJECT_BASEPATH.'/user/login/verify/0/email/' . $email );
		
		} else {
			
			$userRow = $userModel->fetchRow ( $userModel->select ()->where ( 'id = ?', $this->_request->getParam ( 'id' ) ) );
			
	    	$verified = $userMeta->getAttributeValue ( $userRow->id, 'VERIFIED_ACCOUNT' );
			
			$otp->setSalt ( $config->nexva->application->salt );
			$otp->setTimeout ( $this->_request->getParam ( 'time' ) );
			$otp->setId ( $this->_request->getParam ( 'id' ) );
			$otp->setPassword ( $verified );
			
			$result = $otp->verifyOTPHash ( $this->_request->getParam ( 'otphash' ) );
			
			if (! $result) {
				
				$this->_redirect ( CP_PROJECT_BASEPATH.'/user/login/unverified/1/email/' . urlencode($userRow->email) );
			
			}
			{
				
				$fisrtName = $userMeta->getAttributeValue ( $userRow->id, 'FIRST_NAME' );
				$lastName = $userMeta->getAttributeValue ( $userRow->id, 'LAST_NAME' );
				
				if (empty ( $fisrtName )) {
					$name = $userRow->email;
				
				} else {
					$name = $fisrtName . ' ' . $lastName;
				}
				
				$userCpbo->generateWelcomeMail ( $userRow->email, $name );
				
				if ("production" == APPLICATION_ENV)
					$userCpbo->sendCPSignupAlert ( $userRow->id );
				
				$userMeta->setAttributeValue ( $userRow->id, 'VERIFIED_ACCOUNT', '1' );
				$userModel->updateUserStatus ( $userRow->email );
				$this->doLoginVerify ( $userRow->email );
			
			}
		
		}
	
	}
	

	/**
	 * The handy error report action. Send a mail to the devs with the latest error
	 */
    public function reportAction() {
        $errSession     = new Zend_Session_Namespace("errors");
        if (empty($errSession->lastError)) {
            $this->_redirect('/');
        }
        
        $msg            = $errSession->lastError;
        $errSession->lastError  = null;
        
        $mailer     = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject('CPBO Application Error Report');
        $mailer->addTo(explode(',', Zend_Registry::get('config')->nexva->application->dev->contact))
            ->setLayout("generic_mail_template")     
            ->setMailVar("error", $msg);
        
        $session=new Zend_Session_Namespace('chap');
        $config = Zend_Registry::get('config');
        $chapIds = explode(',', $config->nexva->application->frenchchaps);

        if (in_array($session->chap->id, $chapIds)) {
            $template = 'error_report_fr.phtmll';
        } else {
            $template = 'error_report.phtml';
        }

        if (APPLICATION_ENV == 'production') {
            $mailer->sendHTMLMail($template);  
        } else {
            echo $mailer->getHTMLMail($template);
            die();
        }
    }

    public function impersonateAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $timestamp = $this->_request->timestamp;
        if( strtotime("now") > $timestamp ) die("Invalid Request: Your request is stale. Please try again."); //request timeout - we keep it to <=10

        $cpModel = new Model_User();
        $cp = $cpModel->fetchRow('id='.$this->_request->id);

        if( !$cp ) throw new Zend_Exception('Invalid request: CP not found');

        $hash = md5($cp->id.$cp->password.$this->_request->id.Zend_Registry::get('config')->nexva->application->salt.$timestamp);
        
        if( $hash != $this->_request->hash) die("Invalid request: Access denied."); //couldn't verfiy request sig - most likely someone tried to tamper the URL

        //fix the session and goto dashboard

        $obj = new stdClass();
        $obj->id = $cp->id;
        $obj->email = $cp->email;
        
        Zend_Auth::getInstance ()->getStorage ()->write($obj);
        $this->_redirect('/');
                     
    }
    
    public function impersonateAnAction() {
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    
    	$timestamp = $this->_request->timestamp;
    	if( strtotime("now") > $timestamp ) die("Invalid Request: Your request is stale. Please try again."); //request timeout - we keep it to <=10
    
    	$cpModel = new Model_User();
    	$cp = $cpModel->fetchRow('id='.$this->_request->id);
    
    	if( !$cp ) throw new Zend_Exception('Invalid request: CP not found');
    
    	$hash = md5($cp->id.$cp->password.$this->_request->id.Zend_Registry::get('config')->nexva->application->salt.$timestamp);
    
    	if( $hash != $this->_request->hash) die("Invalid request: Access denied."); //couldn't verfiy request sig - most likely someone tried to tamper the URL
    
    	//fix the session and goto dashboard
    
    	$obj = new stdClass();
    	$obj->id = $cp->id;
    	$obj->email = $cp->email;
    
    	Zend_Auth::getInstance ()->getStorage ()->write($obj);
    	$this->_redirect('/analytics/dashboard');
    	 
    }

    public function googleLoginAction() {

        $chapSessionNs = new Zend_Session_Namespace('chap');

        $userModel = new Model_User();
        $chapDetails = $userModel->getChapDetails($chapSessionNs->chap->id);

        if(81604 == $chapSessionNs->chap->id){
            $googleAuth =  new Nexva_Google_GoogleAuthQelasy();
        } else {
            $googleAuth =  new Nexva_Google_GoogleAuthCp();
        }

        if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            //Zend_Debug::dump($googleAuth);die();
        }

        //if code is empty then there is error
        if(isset($_GET['code'])){
            $googleAuth->authenticateGoogle();
        }
        else {
            //$this->_helper->flashMessenger->setNamespace('error')->addMessage('An error occurred, please try again.');
            $this->_redirect('/r/caboapps');

        }

        // Once autheticated success then set the token and get the email
        if(isset($_SESSION['token']))
            $googleAuth->setAccessToken();


        if ($googleAuth->getAccessToken())
        {
            $user = $googleAuth->getUserDetails();
            $userModel = new Cpbo_Model_User();
            $userData['email'] = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
            $userData['first_name'] = filter_var($user['given_name'], FILTER_SANITIZE_SPECIAL_CHARS);
            $userData['last_name'] = filter_var($user['family_name'], FILTER_SANITIZE_SPECIAL_CHARS);
            //$url = $userModel->doLoginOpenId($userData, $this->_chapId);
            $url = $userModel->doLoginOpenId($userData, $chapSessionNs->chap->id);

            if(81604 == $chapSessionNs->chap->id){
                $this->_redirect('http://dev.qelasy.com');
            } else {
                $this->_redirect('http://cp.nexva.com');
            }
        }
    }
 

}