<?php 

class Partner_InfoController extends Nexva_Controller_Action_Partner_MasterController
{

    public function init()
    {
    	parent::init();
        $this->view->headLink()->appendStylesheet('/partner/default/assets/css/register.css');
        //$this->view->headLink()->appendStylesheet('/partner/default/assets/css/ticketing_styles.css');
    }
    
	public function aboutAction()
    {
		$this->view->pageName = 'About Us';
    
    }
    
    public function privacyAction()
    {
    	$this->view->pageName = 'Privacy Statement';
    	
    	
    }
    
    
    public function developerAction()
    {
    	$this->view->pageName = 'Developer';
    	
    	
    }
    
    public function corpAction()
    {
    	$this->view->pageName = 'Corp';
    	
    }
    
    public function termsAction()
    {
    	$this->view->pageName = 'Terms of Use';
    	
    	
    }
    
    public function loginAction()
    {

        //Added for Qelasy
        $this->view->loginType = 1;
        
        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');  //Zend_Debug::dump($translate);die();

    	$this->view->chapId = $this->_chapId;
        $this->view->flashError = $this->_helper->flashMessenger->setNamespace('error')->getMessages();

        if ($this->getRequest ()->isPost ()) {
            //Two authentication methods are available (by username / by mobile no)
            $userName = $this->getRequest()->getParam('username');
            $mobileNo = $this->getRequest()->getParam('mobile');
            $password = $this->getRequest()->getParam('password');
            
            if($userName){
                $this->__doLogin($userName,$password);
            }
            if($mobileNo){
                $this->__doLoginByMobile($mobileNo,$password);
            }
    	}
        
        //$message =  ($translate != null) ? $translate->translate($msgNotFound) : $msgNotFound ;
    	if($this->_request->reset)	{
    		
    		$this->view->message = $translate->translate("Password Reset Successfully. Please login using your email and password..");
    		//$this->view->message = "Password Reset Successfully. Please login using your email and password..";
    	}
    	
    	//$message =  ($translate != null) ? $translate->translate($msgNotFound) : $msgNotFound ;
    	if($this->_request->error)	{
    	
    		$this->view->message =  "An error occurred, please try again.";
    		//$this->view->message = "Password Reset Successfully. Please login using your email and password..";
    	}
    	
    	$this->view->pageName = $translate->translate('Login here');
    	//$this->view->pageName = 'Login here';
    	
    	// for caboapps
    	if($this->_chapId == 136079) {
    	
    		$googleAuth = new Nexva_Google_GoogleAuth();
    		$this->view->gooleUrl = $googleAuth->createAuthUrl();
    		
    		
    		if (!is_null($this->_request->facebook)) {
    		
    			$fb = new Nexva_Facebook_FacebookConnect('web', '840862629279829', '617e486288a6718bf8554c8813120109');

    			//check of the user cancelled FB auth attempt or error in auth attempt
    			if( !is_null($this->_request->error_reason)) $this->_redirect('/info/login'); //somehow FB's PHP SDK never sets this :( See next line
    			if( !$fb->hasUserAuthorizedApp() ) {
    			    $this->_helper->flashMessenger->setNamespace('error')->addMessage('An error occurred, please try again.');
    			    $this->_redirect('/info/login'); //hopefully, a foolproof way to check for authorization even if line above fails
    			    
    			}
    			
    			$fbUser = $fb->getLoggedFbUser();

    			$userModel = new Model_User();
    			$url = $userModel->doLoginFb($fbUser['email'], 'web', '840862629279829', '617e486288a6718bf8554c8813120109', $this->_chapId);
    			$this->_redirect('/user/fb-logincv');
    		}
    		
    		
    		
    		
    	}
    	
    }


    public function loginIranAction(){

        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');

        $this->_helper->viewRenderer->setRender('login');

        $mobile = $this->_getParam('mobile');
        $password = $this->_getParam('password');

        if ($this->getRequest ()->isPost ()) {
            if(!empty($mobile) and !empty($password) ){

                $db = Zend_Registry::get('db');
                $authDbAdapter = new Zend_Auth_Adapter_DbTable ( $db, 'users', 'email', 'password', "MD5(?) and status=1 " );
                $authDbAdapter->setIdentityColumn('mobile_no');
                $authDbAdapter->setCredentialColumn('password');
                $authDbAdapter->setIdentity($mobile);
                $authDbAdapter->setCredential($password);

                $result = Zend_Auth::getInstance ()->authenticate($authDbAdapter);
                if ($result->isValid()) {
                    //our db has user credentials
                    Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject( null, 'password'));

                    setcookie ('mobile', $mobile, time() +(60 * 60 * 24 * 30));
                    setcookie ('password', $password, time() +(60 * 60 * 24 * 30));

                    $this->_redirect('/dashboard');
                } else {
                    //our db has no user credentials, so we import data from through SOAP from iran
                    $userModel = new Model_User();
                    $resultFromIran =  $userModel->sendXml($password,$mobile);

                    if( $resultFromIran['EaiEnvelope']['Payload']['EcareData']['Response']['Result_OutputData']['resultCode'] == 0 ){
                        $username = $resultFromIran['EaiEnvelope']['Payload']['EcareData']['Response']['CustDetails_OutputData']['firstName'].'_'.$resultFromIran['EaiEnvelope']['Payload']['EcareData']['Response']['CustDetails_OutputData']['lastName'];

                        //then we insert the imported record to our db
                        $this->registerIran($password,$mobile,$username);

                        $db = Zend_Registry::get('db');
                        $authDbAdapter = new Zend_Auth_Adapter_DbTable ( $db, 'users', 'email', 'password', "MD5(?) and status=1 " );
                        $authDbAdapter->setIdentityColumn('mobile_no');
                        $authDbAdapter->setCredentialColumn('password');
                        $authDbAdapter->setIdentity($mobile);
                        $authDbAdapter->setCredential($password);

                        $result = Zend_Auth::getInstance ()->authenticate($authDbAdapter);

                        Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject( null, 'password'));

                        setcookie ('mobile', $mobile, time() +(60 * 60 * 24 * 30));
                        setcookie ('password', $password, time() +(60 * 60 * 24 * 30));

                        $this->_redirect('/dashboard');
                    }

                    $this->_helper->flashMessenger->setNamespace('error')->addMessage($translate->translate('Mobile Number or password is incorrect'));
                    $this->_redirect('/info/login');
                }

            } else {
                $this->_helper->flashMessenger->setNamespace('error')->addMessage($translate->translate('Mobile Number or password is incorrect'));
            }
        }

        $this->_redirect('/info/login');
    }

    private function registerIran($password,$mobileNo,$username){

        $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
        $chapId = $this->_chapId;

        $userData = array(
            'username' => $username,
            'email' => $activationCode.'@nexva.com',
            'password' => $password,
            'type' => "USER",
            'login_type' => "NEXVA",
            'chap_id' => $chapId,
            'mobile_no' => $mobileNo,
            "status" => '1',
            'activation_code' => $activationCode
        );

        $user = new Api_Model_Users();
        $user->createUser($userData);
    }

    
    public function logoutAction ()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $session_logout = new Zend_Session_Namespace('logoutaction');
        $session_logout->logout = true;
        $this->_redirect('/');

    }
    
   
    protected function __doLogin($username, $password)
    {
      if(!empty($username) and !empty($password) )	{
    	
	       $db = Zend_Registry::get('db');
	       $authDbAdapter = new Zend_Auth_Adapter_DbTable ( $db, 'users', 'email', 'password', "MD5(?) and status=1 " );
	       $authDbAdapter->setIdentity($username);
	       $authDbAdapter->setCredential($password);
		   
	       $result = Zend_Auth::getInstance ()->authenticate($authDbAdapter);
					
	        if ($result->isValid()) {
	        	
	        	Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject( null, 'password'));
	        	
	            setcookie ('username', $username, time() +(60 * 60 * 24 * 30));
		        setcookie ('password', $password, time() +(60 * 60 * 24 * 30));
		
	        	//print_r($authDbAdapter->getResultRowObject());
		        //print_r(Zend_Auth::getInstance()->getIdentity());
                        
                        //Commented to redirect to dashboard
		        //$this->_redirect ('/' );
                        
                        //Redirect every login to dashboard
                        $this->_redirect('/dashboard');
		      
		        //die();
		        /*
		        		$auth = Zend_Auth::getInstance ();
		        		$auth->getIdentity()->id;
		        */
		        
		        
	        }    else    {
	        	
	            $this->view->error = "Username or password is incorrect!.";
	        	
	        }
        
      } else    {
          $this->view->error = "Username or password is incorrect!.";
      }
    }
    
    //Login by mobile no and password
    protected function __doLoginByMobile($mobileNo, $password)
    {
    	
      if(!empty($mobileNo) and !empty($password) )	{
    	
	       $db = Zend_Registry::get('db');
	       $authDbAdapter = new Zend_Auth_Adapter_DbTable ( $db, 'users', 'mobile_no', 'password', "MD5(?) and status=1 " );
	       $authDbAdapter->setIdentity($mobileNo);
	       $authDbAdapter->setCredential($password);
		   
	       $result = Zend_Auth::getInstance ()->authenticate($authDbAdapter);
					
	        if ($result->isValid()) {
	        	
	        	Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject( null, 'password'));
	        	
                        setcookie ('mobile', $mobileNo, time() +(60 * 60 * 24 * 30));
		        setcookie ('password', $password, time() +(60 * 60 * 24 * 30));
		
	        	//print_r($authDbAdapter->getResultRowObject());
		        //print_r(Zend_Auth::getInstance()->getIdentity());
                        
                        //Commented to redirect to dashboard
		        //$this->_redirect ('/' );
                        
                        //Redirect every login to dashboard
                        $this->_redirect('/dashboard');
		      
		        //die();
		        /*
		        		$auth = Zend_Auth::getInstance ();
		        		$auth->getIdentity()->id;
		        */
		        
		        
	        }    else    {
	        	
	            $this->view->error = "Email or password is incorrect!.";
	        	
	        }
        
      } else    {
          $this->view->error = "Email or password is incorrect!.";
      }
    }
    

    public function resetAction ()
    {

    	
    	$captcha = new Zend_Service_ReCaptcha(Zend_Registry::get('config')->recaptcha->public_key, Zend_Registry::get('config')->recaptcha->private_key);

        $captcha->setOption('theme', "clean");
        $this->view->recaptcha = $captcha->getHTML();
        
	    $this->view->pageName = 'Reset password';
        
        
            if ($this->_request->isPost())   {

            	$this->view->email = $this->_request->email;
            	$this->__doForgotPassword($this->_request->email);
            	
            }
        

    }
    
    
    public function resetpasswordAction() {

    	
    $this->view->pageName = 'Reset password';
	
    $otp = new Nexva_Util_OTP_OneTimePassword();
    $config = Zend_Registry::get('config');

    $user = new Cpbo_Model_User();
    $userRow = $user->fetchRow($user->select()->where('id = ?', $this->_request->getParam('id')));


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
   
      $this->_redirect("/info/login/reset/success");
    }

  }
  
    
	
    protected function __doForgotPassword($email) {
		
		$user = new Cpbo_Model_User ( );


		$captcha = new Zend_Service_ReCaptcha(Zend_Registry::get('config')->recaptcha->public_key, Zend_Registry::get('config')->recaptcha->private_key);

		try {
			
			if (isset ( $this->_request->recaptcha_challenge_field ) and ($this->_request->recaptcha_response_field)) {
				$resp = $captcha->verify ( $this->_request->recaptcha_challenge_field, $this->_request->recaptcha_response_field );
				if (! $resp->isValid ()) {
					
					$this->view->error = "The security key wasn't entered correctly. Please try again.";
					return;
				} else {

					$user->sendPasswordResetMail($email, 'RCA' );
		
					$this->view->message = "Instructions on how to reset your password has been emailed to " . $email;
				}
			} else {
				$this->view->error = "The security key wasn't entered correctly. Please try again.";
				return;
			}
		
		} catch ( Zend_Exception $e ) {
			
			$this->view->error = "The email you supplied was not found.";
		}

	}

	function registerAction(){

        //Retrieve translate object
        //$translate = Zend_Registry::get('Zend_Translate');

        $captcha = new Zend_Service_ReCaptcha(Zend_Registry::get('config')->recaptcha->public_key, Zend_Registry::get('config')->recaptcha->private_key);

        $captcha->setOption('theme', "clean");
        $this->view->recaptcha = $captcha->getHTML();

        //When form is submitted
        if ($this->_request->isPost()){

            //Server side validation
            $filters = array(
                'firstName'    => array('StringTrim'),
                'lastName'     => array('StringTrim'),
                'email'         => array('StringTrim'),
                'phoneNumber'  => array('StringTrim'),
                'password1'      => array('StringTrim'),
                'password2'     => array('StringTrim')
            );

            $firstName     = trim($this->getRequest()->getPost('firstName',null));
            $lastName      = trim($this->getRequest()->getPost('lastName',null));
            $email          = trim($this->getRequest()->getPost('email',null));
            $phoneNumber   = trim($this->getRequest()->getPost('phoneNumber',null));
            $password       = trim($this->getRequest()->getPost('password1',null));
            $password2      = trim($this->getRequest()->getPost('password2',null));
            $captchaResponse =  trim($this->getRequest()->getPost('recaptcha_response_field',null));
            $captchaChallenge = trim($this->getRequest()->getPost('recaptcha_challenge_field',null));

            $validators = array(
                'firstName' => array(
                    new Zend_Validate_NotEmpty(),
                    Zend_Filter_Input::MESSAGES => array(
                        array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Please enter First Name',
                        )
                    )
                ),
                'lastName' => array(
                    new Zend_Validate_NotEmpty(),
                    Zend_Filter_Input::MESSAGES => array(
                        array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Please enter Last Name',
                        )
                    )
                ),
                'email' => array(
                    new Zend_Validate_NotEmpty(),new Zend_Validate_EmailAddress(),
                    Zend_Filter_Input::MESSAGES => array(
                        array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Please enter your Email address',
                        ),
                        array(
                            Zend_Validate_EmailAddress::INVALID_FORMAT => 'Please enter a valid Email address for your Email',
                        )
                    )
                ),
                'phoneNumber' => array(
                    new Zend_Validate_NotEmpty(),new Zend_Validate_Digits(),new Zend_Validate_StringLength(array('min' => 10)),
                    Zend_Filter_Input::MESSAGES => array(
                        array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Please enter your Phone Number',
                        ),
                        array(
                            Zend_Validate_Digits::INVALID => ''
                        ),
                        array(
                            Zend_Validate_StringLength::TOO_SHORT => 'Mobile Number cannot be less than 10 digits',
                        )
                    )
                ),
                'password1' => array(
                    new Zend_Validate_NotEmpty(),
                    Zend_Filter_Input::MESSAGES => array(
                        array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Please enter your Password'
                        )
                    )
                ),
                'password2' => array(
                    new Zend_Validate_NotEmpty(),new Zend_Validate_Identical($password),
                    Zend_Filter_Input::MESSAGES => array(
                        array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Please confirm your Password'
                        ),
                        array(
                            Zend_Validate_Identical::NOT_SAME => 'Password not Matching'
                        )
                    )
                )
            );

            //Validate and filter inputs
            $inputValidation = new Zend_Filter_Input($filters, $validators, $_POST);

            $chapId = $this->_chapId;

            if($inputValidation->isValid()){
                //Generate activation code
                $activationCode = substr(md5(uniqid(rand(), true)), 5,8);

                $user = new Api_Model_Users();

                if (!$user->validateUserEmail($email)){
                    //$errors[] =  ($translate != null) ? $translate->translate("This Email already exists") : 'This Email already exists';
                    $errors[] =  'This Email already exists';
                }

                if (!$user->validateUserMdn($phoneNumber)){
                    //$errors[] =  ($translate != null) ? $translate->translate("This Phone Number already exists") : 'This Phone Number already exists';
                    $errors[] =   'This Phone Number already exists';
                }

                if (isset ($captchaResponse) && ($captchaChallenge)) {
                    $resp = $captcha->verify ( $captchaChallenge, $captchaResponse );

                    if (! $resp->isValid ()) {
                        $errors[] =  "The security key wasn't entered correctly. Please try again.";
                        //return;
                    }
                } else {
                    $errors[] =  "The security key wasn't entered correctly. Please try again.";
                    //return;
                }




                // When errors generated send out error response and terminate.
                if (!empty($errors)){
                    $this->view->ErrorMessages = $errors;
                    $this->view->success = false;

                    //setting the parameters again to display on the view
                    $this->view->firstName = $firstName;
                    $this->view->lastName = $lastName;
                    $this->view->email = $email;
                    $this->view->phoneNumber = $phoneNumber;

                    return;
                } else {
                    $userData = array(
                        'username' => $email,
                        'email' => $email,
                        'password' => $password,
                        'type' => "USER",
                        'login_type' => "NEXVA",
                        'chap_id' => $chapId,
                        'mobile_no' => $phoneNumber,
                        'activation_code' => $activationCode
                    );
                    //Zend_Debug::dump($userData);die();
                    $userId = $user->createUser($userData);

                    $userMeta = new Model_UserMeta();
                    $userMeta->setEntityId($userId);
                    $userMeta->FIRST_NAME = $firstName;
                    $userMeta->LAST_NAME = $lastName;
                    $userMeta->TELEPHONE = $phoneNumber;
                    $userMeta->UNCLAIMED_ACCOUNT = '0';

                    $userDetails = new Zend_Session_Namespace('userDetails');
                    $userDetails->userId = $userId;

                    //after creating the user sending a SMS for the device with success message
                    if($userId > 0){
                        //Send verification SMS
                        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
                        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);

                        $pgType = $pgDetails->gateway_id;

                        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

                        $message = "Please use this verification code $activationCode to complete your registration.";
                        //$message = ($translate != null) ? $translate->translate('Please use this verification code').' '.$activationCode.' '.$translate->translate('to complete your registration.') : $message;

                        $result = $pgClass->sendSms($phoneNumber, $message, $chapId);

                        $flashMessage = 'A verification SMS has been sent to your mobile. Please use the given verification code to complete your registration';
                        //$flashMessage = ($translate != null) ? $translate->translate($flashMessage) : $flashMessage;

                        //$this->_flashMessenger->addMessage($flashMessage);
                        $this->_redirect("/info/authentication");
                    }
                }
            }else{
                $this->view->firstName = $firstName;
                $this->view->lastName = $lastName;
                $this->view->email = $email;
                $this->view->phoneNumber = $phoneNumber;

                $messages = $inputValidation->getMessages();
                $errorsMessages = array();
                foreach ($messages as $key => $value){
                    foreach ($value as $msg){
                        $errorsMessages[] = $msg;
                    }
                }
                $this->view->ErrorMessages = $errorsMessages;
            }
        }
    }

    function authenticationAction(){

        $userId = '';
        if(Zend_Session::namespaceIsset('userDetails')){
            $userDetails = Zend_Session::namespaceGet('userDetails');
            $userId = $userDetails['userId'];
        }

        $chapId = $this->_chapId;
        $messages = array();

        if ($this->_request->isPost()){
            $activationCode = trim($this->_getParam('verificationCode'));
            //Zend_Debug::dump($_POST);die();
            //Check if User Id has been provided
            if ($userId === null || empty($userId)){
                $messages[] =  "Error Verification";
                //$messages[] =  ($translate != null) ? $translate->translate("Error Verification") : 'Error Verification';
            }

            //Check if Activation Code has been provided
            if ($activationCode === null || empty($activationCode))
            {
                $messages[] =  "Verification code not given";
                //$messages[] =  ($translate != null) ? $translate->translate("Verification code not given") : 'Verification code not given';
            }

            $userModel = new Api_Model_Users();

            //Check if combination exists
            $recordCount = $userModel->getUserCountById($chapId, $userId, $activationCode);

            if(empty($recordCount) || is_null($recordCount) || $recordCount <= 0)
            {
                $messages[] = "Verification failed. verification code is incorrect";
                //$messages[] =  ($translate != null) ? $translate->translate("Verification failed. verification code is incorrect") : 'Verification failed. verification code is incorrect';
            }

            $status = 1;

            if (empty($messages)){
                //update the status
                if($userModel->updateVerificationStatus($userId, $status)){
                    $userDetails = $userModel->getUserById($userId);
                    $phoneNumber =  $userDetails->mobile_no;

                    //Sending SMS after Authentication
                    $pgUsersModel = new Api_Model_PaymentGatewayUsers();
                    $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);

                    $pgType = $pgDetails->gateway_id;

                    $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

                    //$message = 'You have completed your registration successfully. "MTN NIGERIA APP STORE"';
                    $message = 'You have completed your registration successfully';

                    //$message =  ($translate != null) ? $translate->translate($message) : $message;

                    $result = $pgClass->sendSms($phoneNumber, $message, $chapId);

                    $flashMessage = 'You successfully completed the registration process';
                    //$flashMessage = ($translate != null) ? $translate->translate("You successfully completed the registration process") : "You successfully completed the registration process";
                    //$this->_flashMessenger->addMessage($flashMessage);
                    $this->_redirect('/info/login');
                } else {
                    $messages[] = 'Already Verified User';
                    //$messages[] = ($translate != null) ? $translate->translate("Already Verified User") : 'Already Verified User';
                }
            }

            $this->view->Messages = $messages;
        }

        //$this->view->flashMessages = $this->_flashMessenger->getMessages();
    }

    public function resendVerificationAction(){
        //$translate = Zend_Registry::get('Zend_Translate');

        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->_request->isPost()){
            $resend   = trim($this->getRequest()->getPost('resend',null));
            if($resend){
                $userId = '';
                if(Zend_Session::namespaceIsset('userDetails'))
                {
                    $userDetails = Zend_Session::namespaceGet('userDetails');
                    $userId = $userDetails['userId'];
                }

                $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
                $status = 0;

                $user = new Api_Model_Users();
                $userDetails = $user->getUserById($userId);

                $mobileNumber = $userDetails->mobile_no;
                $email = $userDetails->email;

                if(is_null($mobileNumber) OR $mobileNumber == '')
                {
                    //$msgNotFound =  ($translate != null) ? $translate->translate("Mobile Number not found for this user") : 'Mobile Number not found for this user';
                    //$this->_flashMessenger->addMessage('Mobile Number not found for this user');
                    //$this->_flashMessenger->addMessage($msgNotFound);
                    return ;
                }
                elseif(is_null($email) OR $email == '')
                {
                    //$this->_flashMessenger->addMessage('Email not found for this user');
                    //$msgNotFound =  ($translate != null) ? $translate->translate("Email not found for this user") : 'Email not found for this user';
                    //$this->_flashMessenger->addMessage($msgNotFound);
                    return;
                }
                else
                {
                    //$this->_flashMessenger->addMessage('You have requested to resend verification code. A verification SMS has been sent to your mobile. Please use the given verification code to verify your account');
                    $msgInfo = 'You have requested to resend verification code. A verification SMS has been sent to your mobile. Please use the given verification code to verify your account';
                    //$flashMessage =  ($translate != null) ? $translate->translate($msgInfo) : $msgInfo;
                    //$this->_flashMessenger->addMessage($flashMessage);
                }

                $user->updateActivationCode($userId, $activationCode, $status);
                $chapId = $this->_chapId;

                //Send verification SMS
                $pgUsersModel = new Api_Model_PaymentGatewayUsers();
                $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);

                $pgType = $pgDetails->gateway_id;

                $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

                //$message = 'Please use this verification code '.$activationCode.' as you requested. "MTN NIGERIA APP STORE"';
                $message = 'Please use this verification code '.$activationCode.' as you requested.';

                $errVerifyCodeP1 = "Please use this verification code";
                $errVerifyCodeP2 = "as you requested.";
                //$message = ($translate != null) ? $translate->translate($errVerifyCodeP1).' '.$activationCode.' '.$translate->translate($errVerifyCodeP2) : $message ;

                //$result = $pgClass->sendSms($mobileNumber, $message, $chapId);
            }
        } else {
            $this->_redirect('/');
        }
        //$this->view->flashMessages = $this->_flashMessenger->getMessages();
    }

    public function changeMobileAction(){
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            //When form is submitted
            if ($this->_request->isPost()){
                $filters = array
                (
                    'mobile_number'    => array('StringTrim'),
                    'mobile_number_confirm'     => array('StringTrim')
                );

                $mobileNumber   = trim($this->getRequest()->getPost('mobile_number',null));
                $mobileNumberConfirm    = trim($this->getRequest()->getPost('mobile_number_confirm',null));

                $validators = array(
                    'mobile_number' => array(
                        new Zend_Validate_NotEmpty(),new Zend_Validate_Digits(),new Zend_Validate_StringLength(array('min' => 10)),
                        Zend_Filter_Input::MESSAGES => array(
                            array(
                                Zend_Validate_NotEmpty::IS_EMPTY => 'Please enter your Mobile Number'
                            ),
                            array(
                                Zend_Validate_Digits::INVALID => ''
                            ),
                            array(
                                Zend_Validate_StringLength::TOO_SHORT => 'Mobile Number cannot be less than 10 digits'
                            )
                        )
                    ),
                    'mobile_number_confirm' => array(
                        new Zend_Validate_NotEmpty(),new Zend_Validate_Digits(),new Zend_Validate_Identical($mobileNumber),
                        Zend_Filter_Input::MESSAGES => array(
                            array(
                                Zend_Validate_NotEmpty::IS_EMPTY => 'Please confirm your Mobile Number'
                            ),
                            array(
                                Zend_Validate_Digits::INVALID => ''
                            ),
                            array(
                                Zend_Validate_Identical::NOT_SAME => 'Mobile Number not Matching'
                            )
                        )
                    )
                );

                //Validate and filter inputs
                $inputValidation = new Zend_Filter_Input($filters, $validators, $_POST);

                $chapId = $this->_chapId;

                $userId = $auth->getIdentity()->id;

                if($inputValidation->isValid()){
                    //Generate activation code
                    $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
                    $status = 0;

                    $user = new Api_Model_Users();
                    if (!$user->validateUserMdn($mobileNumber))
                    {
                        $errors[] =  "This number has already been used. Please try another";
                        $msgErrExist = "This number has already been used. Please try another";
                        //$errors[] =  ($translate != null) ? $translate->translate($msgErrExist) : $msgErrExist ;
                    }

                    // When errors generated send out error response and terminate.
                    if (!empty($errors))
                    {
                        $this->view->ErrorMessages = $errors;
                        return;
                    }else{
                        $user->updateMobileNumebr($userId, $mobileNumber, $activationCode, $status);

                        //Send SMS with verfication code
                        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
                        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);

                        $pgType = $pgDetails->gateway_id;

                        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

                        //$message = 'Please use this verification code '.$activationCode.' to verify your mobile number. "MTN NIGERIA APP STORE"';
                        $message = 'Please use this verification code '.$activationCode.' to verify your mobile number.';

                        $msgErrVerifyP1 = "Please use this verification code";
                        $msgErrVerifyP2 = "to verify your mobile number.";
                        //$message = ($translate != null) ? $translate->translate($msgErrVerifyP1).' '.$activationCode.' '.$translate->translate($msgErrVerifyP2) : $message ;

                        $result = $pgClass->sendSms($mobileNumber, $message, $chapId);

                        $userDetails = new Zend_Session_Namespace('userDetails');
                        $userDetails->userId = $userId;

                        $msgSucMobileNo = "Mobile number changed successfully";
                        //$flashMessage =  ($translate != null) ? $translate->translate($msgSucMobileNo) : $msgSucMobileNo ;

                        //$this->_flashMessenger->addMessage('Mobile number changed successfully');
                        //$this->_flashMessenger->addMessage($flashMessage);
                        $this->_redirect("/info/authentication");
                    }
                }
                else
                {
                    $messages = $inputValidation->getMessages();
                    $errorsMessages = array();
                    foreach ($messages as $key => $value)
                    {
                        foreach ($value as $msg)
                        {
                            $errorsMessages[] = $msg;
                        }
                    }
                    $this->view->ErrorMessages = $errorsMessages;
                }
            }
        }
        else
        {
            $this->_redirect("/");
        }
    }
    
    
    
    
    
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
    
    public function testoAction(){
    
    	echo "<a href='/info/external-login/type/google/url/".base64_encode('https://www.google.com/accounts/o8/id')."'>
    	<img alt='login with Google' src='/web/images/login_options/googleIcon.gif' width='30' height='30'>
    	Login with Google
    	</a>";
    
    	die();
    
    }
    
    public function externalLoginAction() {
        ini_set('display_startup_errors',1);
        ini_set('display_errors',1);
        error_reporting(-1);
        
    	$this->_helper->layout->disableLayout();
    	$type   = $this->_request->getParam('type', false);
    	if (!$type) {
    		$this->_redirect('/info/login');
    	}
    
    	$openIdUrl  = $this->_request->getParam('url', '');
    	$openIdUrl  = base64_decode($openIdUrl);
    	
  
    	$opts       = array(
    			'openIdUrl' => $openIdUrl //we only support openid for now, so this is fine
    	);
    
    	$auth   = Nexva_Auth_AuthenticateFactory::getAuth($type, $opts);
    	if (!$auth) {
    		$this->_redirect('/info/login');
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
    public function remoteLoginAction(){
        
        //error_reporting(E_ALL);
        //ini_set('display_errors', 1);

        $type = trim($this->getRequest()->getPost('login_type', ''));
        //$type = (is_null($type)) ? 1 : $type ;
        
        $this->view->loginType = $type;
        
        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');

        $this->_helper->viewRenderer->setRender('login');

        $this->view->chapId = $this->_chapId;
        $this->view->flashError = $this->_helper->flashMessenger->setNamespace('error')->getMessages();

        if ($this->getRequest()->isPost()) {
            $username = trim($this->getRequest()->getPost('username', ''));
            $mobileNo = trim($this->getRequest()->getPost('mobile_no', ''));
            $mobileNo = (empty($mobileNo)) ? trim($this->getRequest()->getPost('mobile', '')) : $mobileNo ; 
            $password = trim($this->getRequest()->getPost('password', ''));
            $type = trim($this->getRequest()->getPost('login_type', ''));
            
            $user = new Api_Model_Users();

            if (($username != '' || $mobileNo != '') && $password != '') { 

                switch($this->_chapId){
                    case 23045 :
                        $factoryType = 'MtnIran';
                        $userInfo = $user->getUserByMobileNo($mobileNo);
                        $keyType = $mobileNo;
                        $partError = 'mobile number';
                        break;

                    case 115189:
                        $factoryType = 'Ycoins';
                        $userInfo = $user->getUserByEmail($username);
                        $keyType = $username;
                        $partError = 'email';
                        break;

                    case 81604:
                        $factoryType = 'Qelasy';
                        $userInfo = $user->getUserByEmail($username);
                        $keyType = $username;
                        $partError = ($type == 0) ? 'user name' : 'student id';
                        break;

                }
                
                //Create login factory
                $loginClass = Nexva_RemoteLogin_Factory::createFactory($factoryType);

                if($this->_chapId == 81604){
                        $response = $loginClass->registerAndSignIn($password, $keyType, $this->_chapId, $type);
                }
                else{
                    $response = $loginClass->registerAndSignIn($password, $keyType, $this->_chapId);
                    //$response = $loginClass->signIn($password, $keyType, $this->_chapId);
                }

                /*if($userInfo == null){
                    if($this->_chapId == 81604){
                        $response = $loginClass->registerAndSignIn($password, $keyType, $this->_chapId, $type);
                    }
                    else{
                        $response = $loginClass->registerAndSignIn($password, $keyType, $this->_chapId);
                        //$response = $loginClass->signIn($password, $keyType, $this->_chapId);
                    }
                } else {
                    $response = $loginClass->signIn($password, $keyType, $this->_chapId);
                }*/

                //If validated successfully
                 if($response['status'] == 'success') 
                {
                    //Redirect to requested page
                    $session = new Zend_Session_Namespace ( 'lastRequest' );

                    //echo $session->lastRequestUri; die();

                    //Redirect to last requested URL if it was
                    if (isset($session->lastRequestUri)) 
                    {
                        $newpage = $session->lastRequestUri;
                        $session->lastRequestUri = NULL;
                        $this->_redirect($newpage);
                    }

                    $this->_redirect("/");
                } 
                else{
                    $msgErrUnPwd = $response['fault_string'];
                    $this->view->error =  ($translate != null) ? $translate->translate($msgErrUnPwd) : $msgErrUnPwd ;
                }
              
            }
            else 
            {    
                switch($this->_chapId){
                    case 23045 :
                        $partError = 'mobile number';
                        break;

                    case 115189:
                        $partError = 'email';
                        break;

                    case 81604:
                        $partError = ($type == 0) ? 'student id' : 'user name';
                        break;

                }
                
                 $msgErrUnPwd = "The $partError or password you entered is incorrect";
                 $this->view->error =  ($translate != null) ? $translate->translate($msgErrUnPwd) : $msgErrUnPwd ;
            }
        }
    }
}