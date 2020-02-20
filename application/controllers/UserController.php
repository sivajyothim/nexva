<?php

class Default_UserController extends Nexva_Controller_Action_Web_MasterController {

  public function init() {
    /* Initialize action controller here */
    // include Ketchup libs
    parent::init();
  }

  public function indexAction() {
    // action body
  }

  public function registerAction() {

      //error_reporting(E_ALL);

    $this->_helper->layout->setLayout('web/web_innerpage');
    if (isset(Zend_Auth::getInstance()->getIdentity()->id)) {
      $this->_redirect('/');
    }

    $config = Zend_Registry::get('config');
    $publickey = $config->recaptcha->public_key;

    $captcha = new Zend_Service_ReCaptcha(Zend_Registry::get('config')->recaptcha->public_key, Zend_Registry::get('config')->recaptcha->private_key);

    $captcha->setOption('theme', "clean");
    $this->view->recaptcha = $captcha->getHTML();
    
    if ($this->getRequest()->isPost()) {
        
        
        $formValues = new stdClass();
        $fields     = array('first_name', 'last_name', 'email', 'password');
        foreach ($fields as $field) {
            $formValues->$field = trim($this->_getParam($field, ''));
        };

        $errors = array();
//        if (isset($this->getRequest()->recaptcha_challenge_field) and ($this->getRequest()->recaptcha_response_field)) {
//
//            $resp = $captcha->verify($this->getRequest()->recaptcha_challenge_field, $this->getRequest()->recaptcha_response_field);
//
//            if (!$resp->isValid()) {
//                $errors[]   = "The security key wasn't entered correctly. Try it again.";
//            }
//        } else {
//            $errors[]   = "The security key wasn't entered correctly. Try it again.";
//        }

        $referer = $_SERVER['HTTP_REFERER'];
//        if(!('http://nexva.com/user/register/' == $referer)){
//            die();
//        }

        //let's do the validations
        $user   = new Model_User();
        //$errors = array();
        if(!filter_var($formValues->email, FILTER_VALIDATE_EMAIL)) {
            $errors[]   = 'Please type in a valid email.';
        } else {
            if(!$user->validateUserEmail($formValues->email)) {
                $loginLink  = "<a href='/user/login/'>login</a>";
                $recover    = "<a href='/user/forgot-password/'>recover your password</a>";
                $errors[]   = "This email already exists. Do you want to {$loginLink} or {$recover}?";
            }
        }

        if ($formValues->first_name == '' || $formValues->last_name == '' ) {
            $errors[]   = 'Please type in your full name';
        }

        if (strlen($formValues->password) < 6) {
            $errors[]   = 'Your password must be at least 6 characters long';
        }

        if (!empty($errors)) {
            $this->view->formValues = $formValues;
            $this->view->errors     = $errors;
            return;
            //errors, bailing out
        }


        $userData = array(
          'username' => $formValues->email,
          'email' => $formValues->email,
          'password' => $formValues->password,
          'type' => "USER",
          'login_type' => "NEXVA"
        );
        $user_id = $user->createUser($userData);

        $userMeta = new Model_UserMeta();
        $userMeta->setEntityId($user_id);
        $userMeta->FIRST_NAME   = $formValues->first_name;
        $userMeta->LAST_NAME    = $formValues->last_name;

        $name = '';
        $name = $userMeta->FIRST_NAME . ' ' . $userMeta->LAST_NAME;
//        $mailer = new Nexva_Util_Mailer_Mailer();
//        $mailer->setSubject('neXva - Your Account Details');
//        $mailer->addTo($formValues->email, $this->_request->firstname)
//            ->setLayout("generic_mail_template")
//            ->setMailVar("email", $formValues->email)
//            ->setMailVar("name", $name)
//            ->sendHTMLMail('registration_user.phtml');
        $this->doLogin($formValues->email, $formValues->password);



    } else { //Not a POST
        $formValues = new stdClass();
        $fields     = array('first_name', 'last_name', 'email', 'password');
        foreach ($fields as $field) {$formValues->$field = '';};
    }
    $this->view->formValues = $formValues;
  }

  public function loginAction() {
      $chapId = Zend_Auth::getInstance()->getIdentity()->id;      

//echo $chapId.'jyothi';exit;
    Zend_Controller_Action_HelperBroker::addHelper(new Nexva_Controller_Action_Helper_CheckTheme());

    $this->_helper->layout->setLayout('web/web_innerpage');

    // if user already loggedin then redirect to main page
    if (isset(Zend_Auth::getInstance ()->getIdentity()->id)) $this->_redirect('/');
      

    if (!is_null($this->_request->facebook)) { 

        $fb = new Nexva_Facebook_FacebookConnect();
        //check of the user cancelled FB auth attempt or error in auth attempt
        if( !is_null($this->_request->error_reason)) $this->_redirect(PROJECT_BASEPATH.'user/login'); //somehow FB's PHP SDK never sets this :( See next line
        if( !$fb->hasUserAuthorizedApp() ) $this->_redirect(PROJECT_BASEPATH.'user/login'); //hopefully, a foolproof way to check for authorization even if line above fails
        
      
      $fbUser = $fb->getLoggedFbUser();
            
      $userModel = new Model_User();
      $url = $userModel->doLoginFb($fbUser['email']);
      $this->_redirect($url);
    }


    $this->view->formValues = $this->_request;

    if ($this->getRequest()->isPost()) {

      $userModel = new Model_User ( );

      if ($this->_request->unclaim == 1) {

        $userModel = new Model_User ( );

        $validator = new Zend_Validate_EmailAddress ( );
        if ($validator->isValid($this->_request->emailunclaim)) {

          // check email in the database as username field
          $username_check = $userModel->validateUsername($this->_request->emailunclaim);
          if (true !== $username_check) {
            $this->view->messagesUnclaim = array('email' => $username_check);
            return;
          }

          // check email in email field
          $email_check = $userModel->validateEmail($this->_request->emailunclaim);
          if (true !== $email_check) {
            $this->view->messagesUnclaim = $email_check;
            return;
          }

          $inappModel = new Model_Inapp ( );

          $createdPassword = $inappModel->registerUser($this->_request->emailunclaim, 'web');

          if ($createdPassword) {

            $this->doLoginEmail($this->_request->emailunclaim, $createdPassword);
          }
        } else {
          $messages = array('Please enter valid email ');

          $this->view->messagesUnclaim = $messages;
        }
      } else {

        //Check the UNCLAIMED_ACCOUNT in user_meta


        $this->doLogin($this->getRequest()->getParam('email'), $this->getRequest()->getParam('password'));

        $auth = Zend_Auth::getInstance ();

        if ($auth->hasIdentity()) {

          $user = $userModel->fetchAll("email = '" . $this->_request->email . "'");
          if (true == $user) {
            $user = $user->current();
            $userMetaModel = new Cpbo_Model_UserMeta ( );
            $userMetaModel->setEntityId($user->id);

            if ($userMetaModel->UNCLAIMED_ACCOUNT == '1') {
              //jahufar has cheanged his mind :).leave this with this.no harm.we can implement later
            }
          }
        }
      }
    }
    
    if ($this->_request->getParam('alt', false)) {
        $this->render('login-alt');
    }
  }

  
   /**
     * 
     * Triggered for logins that are not nexva and FB Connect
     * @author JP
     */
    public function externalLoginAction() {
        $this->_helper->layout->disableLayout();
        $type   = $this->_request->getParam('type', false);
        if (!$type) {
            $this->_redirect(PROJECT_BASEPATH.'user/login');
        }
        
        $openIdUrl  = $this->_request->getParam('url', '');
        $openIdUrl  = base64_decode($openIdUrl);
        
        $opts       = array(
            'openIdUrl' => $openIdUrl //we only support openid for now, so this is fine
        );
        
        $auth   = Nexva_Auth_AuthenticateFactory::getAuth($type, $opts);
        if (!$auth) {
            $this->_redirect('/user/login');
        }
        
        try { 
            $response   = $auth->doLogin();
            //check out what type of response it is
            if ($response['TYPE'] == 'REDIRECT') {
                $this->_redirect($response['VALUE']);
            } else if ($response['TYPE'] == 'FORM') {
                $this->view->openIdForm =  $response['VALUE'];
            } else {
                throw new Exception('Could not find handler for response type. Response = ' . implode(' <|SEPERATOR|> ', $response));
            }
        } catch (Exception $ex) {
            $line       = "\n\n -------------------MOBILE MODULE---------------------------------- \n\n ";
            $message    = "$line Authentication Error \n";
            $message    .= $ex . "\n";
            $message    .= $ex->getTraceAsString() . $line;
            
            Zend_Registry::get('logger')->err($message);
            
            $this->__addErrorMessage('An error occured while trying to login using this method. Please try an alternative login method.');
            $this->_redirect('/user/login');
        }
        
    }
  
       /**
     * This is where the openID response comes back to
     */
    public function openidAction() {
        $params = $this->_getAllParams();
        
        //what we really need is the email. But we would also like the names if possible
        try {
            $data   = Nexva_Auth_Provider_OpenId::parse($params, array('email'));
            $user   = new Model_User();
            
            if ($user->doLoginOpenId($data)) {
                $session = new Zend_Session_Namespace('login');
                if (isset($session->lastRequestUri)) {
                    $url    = $session->lastRequestUri;
                    unset($session->lastRequestUri);
                    $this->_redirect($url);
                } else {
                    $this->_redirect('/app/purchased');
                }                
            } else {
                $this->__addErrorMessage('An error occured while trying to login using this method. Please try an alternative login method.');
                $this->_redirect('/user/login');
            }
        } catch (Exception $ex) {
            $line       = "\n\n -------------------MOBILE MODULE---------------------------------- \n\n ";
            $message    = "$line Authentication Error \n";
            $message    .= $ex . "\n";
            $message    .= $ex->getTraceAsString() . $line;
            
            Zend_Registry::get('logger')->err($message);
            
            $this->__addErrorMessage('An error occured while trying to login using this method. Please try an alternative login method.');
            $this->_redirect('/user/login');
        }
    }
  
  
  protected function doLogin($username, $password) {
    if ('' != $username && '' != $password) {
      $db = Zend_Registry::get('db');
      $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'email', 'password', "MD5(?)");
      $authDbAdapter->setIdentity($username);
      $authDbAdapter->setCredential($password);
      $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);
//      print_r($result);exit;
      if ($result->isValid()) {
        Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));
        //Save last login details
        $auth = Zend_Auth::getInstance();
        $user = new Model_UserMeta();
        $user->setEntityId($auth->getIdentity()->id);
        $user->LAST_LOGIN = date('Y-m-d h:i:s');
        
        /**
         * 
         * I'm changing how login redirection works, 
         * now we pickup the refere in the loginAction() method 
         * and we simply redirect there. Revert this behavior if anything breaks.
         * Just switch the namespace to `lastRequest` from `login`
         */
        $session = new Zend_Session_Namespace('lastRequest');
//        if (isset($session->lastRequestUri)) {
//          $newpage = $session->lastRequestUri;
//          unset($session->lastRequestUri);
//          $this->_redirect($newpage);
//        } 
        $this->_redirect(PROJECT_BASEPATH.'user/login');
        return;
      } else {
        $this->view->messages = array(
          'Wrong email and password combination.'
        );
      }
    } else {
      $this->view->messages = array(
        'Wrong email and password combination.'
      );
    }
  }

  public function forgotPasswordAction() {

    if ($this->getRequest()->isPost()) {
      $this->doForgotPassword($this->_request->getParam('email'));
    }
    $this->view->formValues = $this->_request;

    Zend_Controller_Action_HelperBroker::addHelper(new Nexva_Controller_Action_Helper_CheckTheme());

    $theme = $this->_helper->CheckTheme->CheckTheme();

    if (false !== $theme) {
      $layout = $theme->LAYOUT;
      $this->_helper->layout->setLayout('custom/' . $layout . "/" . $layout . "_innerpage");
      $this->view->theme = $theme;
    } else {
      $this->_helper->layout->setLayout('web/web_innerpage');
    }
  }

  protected function doForgotPassword($email) {

    $user = new Model_User();
    try {
      $user->sendPasswordResetMail($email);
      $message[] = "Instructions on how to reset your password have been emailed to " . $email;
    } catch (Zend_Exception $e) {

      $message[] = "The email you supplied was not found or is not valid.";
      $this->view->errors = $message;
    }

    $this->view->messages = $message;
  }

  public function resetPasswordAction() {
    $otp = new Nexva_Util_OTP_OneTimePassword();
    $config = Zend_Registry::get('config');

    $user = new Model_User();
    $userRow = $user->fetchRow($user->select()->where('id = ?', $this->_request->getParam('id')));

    //Zend_Debug::dump($userRow->password);die();

    $otp->setSalt($config->nexva->application->salt);
    $otp->setTimeout($this->_request->getParam('time'));
    $otp->setId($this->_request->getParam('id'));
    $otp->setPassword($userRow->password);

    $result = $otp->verifyOTPHash($this->_request->getParam('otphash'));



    if (!$result) {

      $messages[] = "The password reset request is no longer valid. Please try <a href='/user/forgot-password'>resending</a> the request.";
      $this->view->messages = $messages;
    }

    $this->view->valid_request = $result;

    if ($this->_request->isPost()) { //everything is good - reset the password and redirect
      if ($this->_request->password == $this->_request->confirm) {
        $user->resetPassword($userRow->id, $this->_request->password);
        $this->doLoginEmail($userRow->email, $this->_request->password);


        $userMetaModel = new Cpbo_Model_UserMeta();

        $userMetaModel->setEntityId($userRow->id);

        if ($userMetaModel->UNCLAIMED_ACCOUNT == '1') {
          $userMetaModel->UNCLAIMED_ACCOUNT == '0';
        }
        $this->_redirect("/user/login");
      } else {
        $messages[] = "The passwords you entered did not match.";
        $this->view->messages = $messages;
      }
    }

    $this->_helper->layout->setLayout('web/web_innerpage');
  }

  public function claimAction() {

    $this->view->headTitle('Claim your account');


    $otp = new Nexva_Util_OTP_OneTimePassword();
    $config = Zend_Registry::get('config');

    $user = new Model_User();

    $userRow = $user->fetchRow($user->select()->where('id = ?', $this->_request->getParam('id', '0')));

    //Zend_Debug::dump($userRow->password);die();

    $otp->setSalt($config->nexva->application->salt);
    $otp->setTimeout($this->_request->getParam('time'));
    $otp->setId($this->_request->getParam('id'));
    $otp->setPassword($userRow->password);

    $result = $otp->verifyOTPHash($this->_request->getParam('otphash'));



    if (!$result) {
      $messages[] = "The URL you clicked is no longer valid. ";
      $this->view->messages = $messages;
    }

    $this->view->valid_request = $result;
    $this->view->email = $userRow->email;

    if ($this->_request->isPost()) { //everything is good - reset the password and redirect
      if ($this->_request->password == $this->_request->confirm) {
        $user->resetPassword($userRow->id, $this->_request->password);
        $this->doLoginEmail($userRow->email, $this->_request->password);

        $userMetaModel = new Cpbo_Model_UserMeta();

        $userMetaModel->setEntityId($userRow->id);

        if ($userMetaModel->UNCLAIMED_ACCOUNT == '1') {
          $userMetaModel->UNCLAIMED_ACCOUNT == '0';
        }
        $this->_redirect("/user/login");
      } else {
        $messages[] = "The passwords you entered did not match.";
        $this->view->messages = $messages;
      }
    }

    $this->_helper->layout->setLayout('web/web_innerpage');
  }
  
  
  protected function doLoginEmail($username, $password) {
	        if( '' !=  $username && '' != $password ) {
	            $db = Zend_Registry::get('db');
	            $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'active_users', 'username', 'password', "MD5(?)");
	            $authDbAdapter->setIdentity($username);
	            $authDbAdapter->setCredential($password);
	            $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);
	            if($result ->isValid()) {
	                Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password') );
	                //Save last login details
	                $auth     =  Zend_Auth::getInstance();
	                $user     =   new Model_UserMeta();
	                $user->setEntityId($auth->getIdentity()->id);
	                $user->LAST_LOGIN = date('Y-m-d h:i:s');
	                //Redirect to requested page
	                $session = new Zend_Session_Namespace('lastRequest');
	                if (isset($session->lastRequestUri)) {
	                    // then this should be cause of clickin buy button
	                    // so need to go to checkout once login is completed
	                    $newpage    =   $session->lastRequestUri;
	                    $this->_redirect($newpage);
	                    return;
	                }
	                $this->_redirect("/");
	            }
	            else {
	                $this->view->messages = array(
	                        'Username or password is incorrect.'
	                );
	            }
	        }
	        else {
	            $this->view->messages = array(
	                    'Please enter your username and password.'
	            );
	        }
	    }

  public function logoutAction() {
    $this->_helper->viewRenderer->setNoRender(true);
    Zend_Auth::getInstance()->clearIdentity();
    $this->_redirect($this->_request->getHeader('referer'));
  }

}

