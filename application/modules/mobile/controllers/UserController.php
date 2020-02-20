<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Mobile
 * @version     $Id$
 */
class Mobile_UserController extends Nexva_Controller_Action_Mobile_MasterController {

  public function init() {
    parent::init ();
  }

  function preDispatch() {
    parent::preDispatch ();
    if (!Zend_Auth::getInstance ()->hasIdentity()) {
      $skip_action_names = array("login", "register", "register-unclaim", 'login', 'external-login', 'openid');
      if (!in_array($this->getRequest()->getActionName(), $skip_action_names)) {
        $requestUri = Zend_Controller_Front::getInstance ()->getRequest()->getRequestUri();
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $requestUri;
        $session->lock();
      }
      if (!in_array($this->getRequest()->getActionName(), $skip_action_names)) {
          $this->_redirect('/user/login/#content');
      }
    }
  }

  /**
   * Remove this action after the new Login system is in place 
   */
/*  
    public function loginOldAction() {
        $this->view->login = TRUE;
        // if user already loggedin then redirect to main page
        if (isset(Zend_Auth::getInstance()->getIdentity()->id)) {
            $this->_redirect('/app/purchased');
        }
        
        // handle the facebook login
        if (!is_null($this->_request->facebook)) {
            if (!is_null($this->_request->error_reason)){
            $this->_redirect('/user/login');
            }
            ////user cancelled FB auth attempt or error in auth attempt
            
            $appId    = (isset($this->themeMeta->WHITELABLE_FB_APPID) && !empty($this->themeMeta->WHITELABLE_FB_APPID)) ? $this->themeMeta->WHITELABLE_FB_APPID : null;
            $secret   = (isset($this->themeMeta->WHITELABLE_FB_SECRET) && !empty($this->themeMeta->WHITELABLE_FB_SECRET)) ? $this->themeMeta->WHITELABLE_FB_SECRET : null;
            $chapId   = (isset($this->themeMeta->USER_ID) && !empty($this->themeMeta->USER_ID)) ? $this->themeMeta->USER_ID : null;
            
            $fb = new Nexva_Facebook_FacebookConnect('mobile', $appId, $secret);
            $fbUser = $fb->getLoggedFbUser();
            $userModel = new Model_User();
            $url = $userModel->doLoginFb($fbUser['email'], 'mobile', $appId, $secret, $chapId);
            $this->_redirect($url);
        }
        $this->view->pageTitle = 'Login';
        if ($this->getRequest()->isPost()) {
          $this->doLogin($this->getRequest()->getParam('username'), $this->getRequest()->getParam('password'));
        }
        $this->view->formValues = $this->_request;
        $this->view->showToplinks = TRUE;
        
        if ($this->_request->getParam('alt', false)) {
            $this->render('login-alt');
        }
    }
*/

    function loginAction() {
        if ($this->_request->getParam('next', false)) {
            $session = new Zend_Session_Namespace('login');
            if($url = $this->_request->getParam('url', false)) {
                $session->lastRequestUri     = base64_decode($url);
            } else {
                $session->lastRequestUri    = $this->_request->getHeader('referer');    
            }
        }
        $this->view->login = TRUE;
        // if user already loggedin then redirect to main page
        if (isset(Zend_Auth::getInstance()->getIdentity()->id)) {
          $this->_redirect('/app/purchased');
        }
        
        // handle the facebook login
        if (!is_null($this->_request->facebook)) {
            if (!is_null($this->_request->error_reason)){
                $this->_redirect('/user/login');
            }
            ////user cancelled FB auth attempt or error in auth attempt

            $appId = null;
            $secret = null;

            if( APPLICATION_ENV != 'development') {
                $appId    = (isset($this->themeMeta->WHITELABLE_FB_APPID) && !empty($this->themeMeta->WHITELABLE_FB_APPID)) ? $this->themeMeta->WHITELABLE_FB_APPID : null;
                $secret   = (isset($this->themeMeta->WHITELABLE_FB_SECRET) && !empty($this->themeMeta->WHITELABLE_FB_SECRET)) ? $this->themeMeta->WHITELABLE_FB_SECRET : null;
            }
            
            $chapId   = (isset($this->themeMeta->USER_ID) && !empty($this->themeMeta->USER_ID)) ? $this->themeMeta->USER_ID : null;
            
            $fb         = new Nexva_Facebook_FacebookConnect('mobile', $appId, $secret);
            $fbUser     = $fb->getLoggedFbUser();
            $userModel  = new Model_User();
            $url        = $userModel->doLoginFb($fbUser['email'], 'mobile', $appId, $secret, $chapId);
            $this->_redirect($url);
        }

        if ($this->getRequest()->isPost()) {
            $this->doLogin($this->getRequest()->getParam('username'), $this->getRequest()->getParam('password'));
        }
        $this->view->formValues = $this->_request;
        $this->view->showToplinks = TRUE;
          
    }
  
    /**
     * 
     * Triggered for logins that are not nexva and FB Connect
     */
    public function externalLoginAction() {
        $type   = $this->_request->getParam('type', false);
        if (!$type) {
            $this->_redirect('/user/login');
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
            $chapId = isset ( $this->themeMeta->USER_ID ) ? $this->themeMeta->USER_ID : null;
            
            if ($user->doLoginOpenId($data, $chapId)) {
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
            $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'active_users', 'username', 'password', "MD5(?)");
            $authDbAdapter->setIdentity($username);
            $authDbAdapter->setCredential($password);
            $result = Zend_Auth::getInstance ()->authenticate($authDbAdapter);
            if ($result->isValid()) {
                Zend_Auth::getInstance ()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));
                //Save last login details
                $auth = Zend_Auth::getInstance ();
                $user = new Model_UserMeta ( );
                $user->setEntityId($auth->getIdentity()->id);
                $user->LAST_LOGIN = date('Y-m-d h:i:s');
                //Redirect to requested page
                $session = new Zend_Session_Namespace('login');
                if (isset($session->lastRequestUri)) {
                    $this->_redirect($session->lastRequestUri);
                    unset($session->lastRequestUri);
                    return;
                } else {
                    $this->_redirect('/app/purchased');
                    return;
                }   
            } else {
                $this->view->messages = array('Username or password is incorrect.');
            }
        } else {
            $this->view->messages = array('Please enter your username and password.');
        }
    }

    public function logoutAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        Zend_Auth::getInstance ()->clearIdentity();
        $session = new Zend_Session_Namespace('lastRequest');
        unset($session->lastRequestUri);
        unset($session->productId);
        $this->_redirect('/');
    }

    function registerAction() {

        error_reporting(-1);
        ini_set('display_errors', 'On');

        $this->view->showToplinks = true;
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

            $referer = $_SERVER['HTTP_REFERER'];

            Zend_Debug::dump($referer);
            if('http://nexva.mobi/user/register' != $referer){
                die();
            }

            $errors = array();
            if (isset($this->getRequest()->recaptcha_challenge_field) and ($this->getRequest()->recaptcha_response_field)) {

                $resp = $captcha->verify($this->getRequest()->recaptcha_challenge_field, $this->getRequest()->recaptcha_response_field);

                if (!$resp->isValid()) {
                    $errors[]   = "The security key wasn't entered correctly. Try it again.";
                }
            } else {
                $errors[]   = "The security key wasn't entered correctly. Try it again.";
            }

            //let's do the validations
            $user   = new Model_User();

            if(!filter_var($formValues->email, FILTER_VALIDATE_EMAIL)) {
                $errors[]   = 'Please type in a valid email.';
            } else {
                if(!$user->validateUserEmail($formValues->email)) {
                    $loginLink  = "<a href='/user/login/'>login</a>";
                    //$recover    = "<a href='/user/forgot-password/'>recover your password</a>";
                    $errors[]   = "This email already exists. Do you want to {$loginLink}?";
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
            $mailer = new Nexva_Util_Mailer_Mailer();
            $mailer->setSubject('neXva - Your Account Details');
            $mailer->addTo($formValues->email, $this->_request->firstname)
                ->setLayout("generic_mail_template")         
                ->setMailVar("email", $formValues->email)
                ->setMailVar("name", $name)
                ->sendHTMLMail('registration_user.phtml');  
            $this->doLogin($formValues->email, $formValues->password);
        } else { //Not a POST
            $formValues = new stdClass();
            $fields     = array('first_name', 'last_name', 'email', 'password');
            foreach ($fields as $field) {$formValues->$field = '';};
        }
        $this->view->formValues = $formValues;
    }
  
  
  function register2Action() {
    // if user already loggedin then redirect to main page
    if (isset(Zend_Auth::getInstance ()->getIdentity()->id))
      $this->_redirect('/app/');
    // @TODO : clean up the code
    $this->view->pageTitle = 'Register';
    $user ['username'] = $this->_request->email;
    $user ['email'] = $this->_request->email;
    $this->view->user = $user;
    if ('' != $this->_request->submit and '' != $this->_request->email) {
      $user = new Model_User ( );
      $common_check = $user->registrationCommonCheck($this->getRequest()->getParams());
      if ($common_check->isValid()) {
        $username_check = $user->validateUsername($common_check->email);
        if (true !== $username_check) {
          $this->view->messages = $username_check;
          return;
        }
        $password_check = $user->checkPassword($common_check->password, $common_check->retypepassword);
        if (true !== $password_check) {
          $this->view->messages = $password_check;
          return;
        }
        $email_check = $user->validateEmail($common_check->email);

        if (true !== $email_check) {
          $this->view->messages = $email_check;
          return;
        }
        $userData = array('username' => $common_check->email, 'email' => $common_check->email, 'password' => $common_check->password, 'type' => "USER", 'login_type' => "NEXVA");
        $user_id = $user->createUser($userData);
        // Add ful name to Meta
        $userMeta = new Model_UserMeta ( );
        $userMeta->setEntityId($user_id);
        //Save User Details to Meta
        if (isset($this->_request->title))
          $userMeta->TITLE = $this->_request->title;
        if (isset($this->_request->firstname))
          $userMeta->FIRST_NAME = $this->_request->firstname;
        if (isset($this->_request->lastname))
          $userMeta->LAST_NAME = $this->_request->lastname;
        if (isset($this->_request->mobile))
          $userMeta->MOBILE = $this->_request->mobile;
        if (isset($this->_request->address))
          $userMeta->ADDRESS = $this->_request->address;
        if (isset($this->_request->city))
          $userMeta->CITY = $this->_request->city;
        if (isset($this->_request->zipcode))
          $userMeta->ZIPCODE = $this->_request->zipcode;
        // sending the email to the user
        $name = '';
        $name = $userMeta->FIRST_NAME . ' ' . $userMeta->LAST_NAME;

        $mailer = new Nexva_Util_Mailer_Mailer ( );
        $mailer->setSubject('neXva - Your Account Details');
        $mailer->addTo($common_check->email, $this->_request->firstname)
            ->setLayout("generic_mail_template")
            ->setMailVar("email", $common_check->email)
            ->setMailVar("name", isset($name) ? $name : $common_check->email)
            ->setMailVar("address", $userMeta->ADDRESS)
            ->setMailVar("city", $userMeta->CITY)
            ->setMailVar("country", $userMeta->COUNTRY)
            ->setMailVar("mobile", $userMeta->MOBILE)
            ->sendHTMLMail('registration_user.phtml');

        $this->doLogin($common_check->email, $common_check->password);
        $this->view->messages = array("Registration is success.  <a href='/user/login/#content'>Login</a>");
        $this->view->user = NULL;
      } else {
        $messages = $common_check->getMessages();
        //                print_r($messages);
        //                exit;
        $this->view->messages = array(array_pop(array_pop($messages)));
      }
    } else {
      if ($this->_request->submit != '') {
        $this->view->messages = array('Please fill all the fields.');
      }
    }
    //        $this->view->formValues = $this->_request;
  }

  public function registerUnclaimAction() {


    if (isset(Zend_Auth::getInstance ()->getIdentity()->id))
      $this->_redirect('/app/#content');

    //	$this->_redirect ( '/app/#content' );

    $this->view->pageTitle = 'Register';
    $user ['username'] = $this->_request->email;
    $user ['email'] = $this->_request->email;
    $this->view->user = $user;

    $email = trim($this->_request->email);


    if ('' != $this->_request->submit and '' != $this->_request->email) {

      $user = new Model_User ( );


      // check email in email field
      $email_check = $user->validateEmail($this->_request->email);

      if (true !== $email_check) {
        $this->view->messages = $email_check;
        return;
      }

      $chapId   = (isset($this->themeMeta->USER_ID) && !empty($this->themeMeta->USER_ID)) ? $this->themeMeta->USER_ID : null;
      $user = new Model_Inapp ( );
      $created = $user->registerUser($this->_request->email, 'mobile', $chapId);

      if ($created) {

        $db = Zend_Registry::get('db');

        $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'active_users', 'username', 'password', "MD5(?)");
        $authDbAdapter->setIdentity($this->_request->email);
        $authDbAdapter->setCredential($created);

        $result = Zend_Auth::getInstance ()->authenticate($authDbAdapter);

        Zend_Auth::getInstance ()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));

        $auth = Zend_Auth::getInstance ();
        $user = new Model_UserMeta ( );
        $user->setEntityId($auth->getIdentity()->id);
        $user->LAST_LOGIN = date('Y-m-d h:i:s');

        $session = new Zend_Session_Namespace('lastRequest');
        if (isset($session->lastRequestUri)) {
          // then this should be cause of clickin buy button
          // so need to go to checkout once login is completed
          $newpage = $session->lastRequestUri . '/#content';
          $session->lastRequestUri = NULL;
          // if next action is checkout redirect to checkout
          $next = null;
          $next = $this->getRequest()->getParam('next');
          if (isset($next))
            $newpage = '/app/checkout/#content';
          $this->_redirect($newpage);
          return;
        }
        $this->_redirect("/#content");
      }
    }

    if (empty($email) and $this->_request->submit) {
      $this->view->messages[] = "Email is empty.";
    }
  }

}

?>
