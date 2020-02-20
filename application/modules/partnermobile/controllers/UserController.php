<?php

class Partnermobile_UserController extends Nexva_Controller_Action_Partnermobile_MasterController {
	
    public function init() 
    {
        parent::init();
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->initView();
        
        $this->view->chapId = $this->_chapId;
        
    }

    /**
     * register new users, After the process of server side validation SMS being sent to the user with the activation code for the account verification. After
     * @param : firstName
     * @param : lastName
     * @param : email
     * @param : phoneNumber
     * @param : password
     * @param : password2 ; user confirmation of password
     *
     */

    public function registerAction()
    {
        $isLowEndDevice = $this->_isLowEndDevice;

       //Assign Chp Id
     
        
        
        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');
               
        //When form is submitted
        if ($this->_request->isPost()) 
        {
            //Server side validation
            $filters = array
            (
                'first_name'    => array('StringTrim'),
                'last_name'     => array('StringTrim'),
                'email'         => array('StringTrim'),
                'phone_number'  => array('StringTrim'),
                'password'      => array('StringTrim'),
                'password2'     => array('StringTrim')
            );

            $firstName     = trim($this->getRequest()->getPost('first_name',null));
            $lastName      = trim($this->getRequest()->getPost('last_name',null));
            $email          = trim($this->getRequest()->getPost('email',null));
            $phoneNumber   = trim($this->getRequest()->getPost('phone_number',null));
            $password       = trim($this->getRequest()->getPost('password',null));
            $password2      = trim($this->getRequest()->getPost('password2',null));

            $validators = array(
                'first_name' => array(
                    new Zend_Validate_NotEmpty(),
                    Zend_Filter_Input::MESSAGES => array(
                        array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Please enter First Name',
                        )
                    )
                ),
                'last_name' => array(
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
                'phone_number' => array(
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
                'password' => array(
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
            
            if($this->_chapId==283006){
                unset($validators['password']);
                unset($validators['password2']);
            }
            
            if($this->_chapId== 935529){// 935529
                unset($validators['phone_number']);
            }
            
            //Validate and filter inputs
            $inputValidation = new Zend_Filter_Input($filters, $validators, $_POST);

            $chapId = $this->_chapId;
            $errorsMessages = array();
            if($inputValidation->isValid())
            {
                //Generate activation code
                
                if($this->_chapId==283006) {                    
                    $activationCode = $this->__random_numbers(5);
                    $password = $activationCode;                   
                
                } else {
                    
                    if($this->_chapId ==  80184 || $this->_chapId ==  276531) {
                	    $activationCode = $this->__random_numbers(4);
                    } else {
                            $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
                    }
                    
                }
                 $user = new Api_Model_Users();
                /* E-Caret User registration */
               
                if ($this->_chapId == 935529) { // ecaret chap
                    $partnerUser = new Partnermobile_Model_Users();
                    $userDetails = $partnerUser->getUserByEmail($email);
                    
                    if ($userDetails) {
                        /* Already registered users. */
                         $errors[] =  ($translate != null) ? $translate->translate("This Email already exists") : 'This Email already exists';
                         $this->view->ErrorMessages=$errors;
                    } else {
                       
                        /* For new register user. */
                        $userData = array(
                            'username' => $email,
                            'email' => $email,
                            'password' => $password,
                            'type' => "USER",
                            'login_type' => "NEXVA",
                            'chap_id' => $chapId,
                            'mobile_no' => $phoneNumber,
                            'activation_code' => $activationCode,
                            'status' => 0
                        );

                        $userId = $user->createUser($userData);
                        $userMeta = new Model_UserMeta();
                        $userMeta->setEntityId($userId);
                        $userMeta->FIRST_NAME = $firstName;
                        $userMeta->LAST_NAME = $lastName;
                        $userMeta->TELEPHONE = $phoneNumber;
                        $userMeta->UNCLAIMED_ACCOUNT = '0';
                        
                        $userDetails = new Zend_Session_Namespace('userDetails');
                        $userDetails->userId = $userId;
                    
                        $this->sendEmailValidationMessage($activationCode,$firstName." ".$lastName,$email);
                        $flashMessage = 'A verification email has been sent to your email. Please use the given verification code to complete your registration';
                        $flashMessageTrans = ($translate != null) ? $translate->translate($flashMessage) : $flashMessage;

                        $this->_flashMessenger->addMessage($flashMessageTrans);
                        $this->_redirect("/user/authentication");
                    }
                }else{
                /* End */
               
                if ((!$this->_headerEnrichment) && (!$user->validateUserEmail($email)))     //if(!$user->validateUserEmail($email))
                {
                    $errors[] =  ($translate != null) ? $translate->translate("This Email already exists") : 'This Email already exists';
                }

                
                //Check the user already registered
                $userInfo = $user->getUserByMobileNo($phoneNumber);
                $headerEnrichedUser = ($userInfo && $userInfo->mobile_no == $phoneNumber && ($userInfo->password == md5('mtnpassword') || $userInfo->password == md5('airtelpassword'))) ? true : false ;
                        
                /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                    print_r($userInfo);
                    echo '#####<br>#####';
                    echo $userInfo->mobile_no.'###'.$phoneNumber;
                    echo '#####<br>#####';
                    echo $userInfo->password.'###'.md5('airtelpassword');
                     echo '#####<br>#####';
                    echo $headerEnrichedUser;
                    echo '#####<br>#####';
                    echo $this->_headerEnrichment;
                    die();
                }*/
                
                //Check the user's password is common(temp) password
                if($this->_headerEnrichment && $headerEnrichedUser){
                     //The above conditions checks if the user has has been registered with the common password. If yes the user has been registered through the HEADER ENRICHMENT
                }
                else{
                    if (!$user->validateUserMdn($phoneNumber))
                    {
                         $errors[] =  ($translate != null) ? $translate->translate("This Phone Number already exists. Please try using forgot password") : 'This Phone Number already exists. Please try using forgot password';
                    }
                }

                // When errors generated send out error response and terminate.
                if (!empty($errors))
                {
                    $this->view->ErrorMessages = $errors;
                    $this->view->success = false;

                    //setting the parameters again to display on the view
                    $this->view->first_name = $firstName;
                    $this->view->last_name = $lastName;
                    $this->view->email = $email;
                    $this->view->phone_number = $phoneNumber;
                    
                    // check and load the low-end view if the device is low end
                     if($isLowEndDevice):
                        $this->_helper->viewRenderer('register-low-end');
                     endif;
                     
                    return;
                }
                else
                {
                    //If the chap is MTN Nigeria then insert the user datails
                    //_headerEnrichment
                    if($this->_chapId == 283006){
                        $userData = array(
                                            'username' => $activationCode.'@nexva.com',
                                            'email' => $activationCode.'@nexva.com',
                                            'password' => $password,
                                            'type' => "USER",
                                            'login_type' => "NEXVA",
                                            'chap_id' => $chapId,
                                            'mobile_no' => $phoneNumber,
                                            'activation_code' => $activationCode,
                                            'status' => 1
                                        );
                    }else{
                        if($this->_headerEnrichment){
                            //Registered user's username and password will be changed as below when the chap has header enrichment
                            $userData = array(
                                            'username' => $activationCode.'@nexva.com',
                                            'email' => $activationCode.'@nexva.com',
                                            'password' => $password,
                                            'type' => "USER",
                                            'login_type' => "NEXVA",
                                            'chap_id' => $chapId,
                                            'mobile_no' => $phoneNumber,
                                            'activation_code' => $activationCode,
                                            'status' => 0
                                        );
                        }
                        else{
                            $userData = array(
                                            'username' => $email,
                                            'email' => $email,
                                            'password' => $password,
                                            'type' => "USER",
                                            'login_type' => "NEXVA",
                                            'chap_id' => $chapId,
                                            'mobile_no' => $phoneNumber,
                                            'activation_code' => $activationCode,
                                            'status' => 0
                                        );
                        }
                    }

                    //Check the user contain the temp password by same mobile no
                    if($this->_headerEnrichment && $headerEnrichedUser){
                        $userId = $userInfo->id;
                        $userModel = new Model_User();
                        
                        //resetPassword() function used for update the existing user created when the header enrichment was active
                        $res = $userModel->resetPassword($userId, $password);
                        /*if($res){
                            $userModel->changeUserStatus($this->_userId, 0);
                        }*/
                        $userMeta = new Model_UserMeta();
                        $userMeta->setEntityId($userId);
                        $userMeta->FIRST_NAME = $firstName;
                        $userMeta->LAST_NAME = $lastName;
                        $userMeta->TELEPHONE = $phoneNumber;
                        $userMeta->UNCLAIMED_ACCOUNT = '0';
                    }
                    else{

                        $userId = $user->createUser($userData);
                        $userMeta = new Model_UserMeta();
                        $userMeta->setEntityId($userId);
                        $userMeta->FIRST_NAME = $firstName;
                        $userMeta->LAST_NAME = $lastName;
                        $userMeta->TELEPHONE = $phoneNumber;
                        $userMeta->UNCLAIMED_ACCOUNT = '0';
                    }

                    $userDetails = new Zend_Session_Namespace('userDetails');
                    $userDetails->userId = $userId;
		
            
		   //No need to send sms verification for header enriched users
                    if($this->_headerEnrichment && $headerEnrichedUser){
                        if($userId > 0)
                        {
                            $flashMessage = 'You have completed your registration successfully';
                            $this->_flashMessenger->addMessage($flashMessage);
                            $this->_redirect("/user/login");
                        }
                    }
                    else{
						//after creating the user sending a SMS for the device with success message
						if($userId > 0)
						{
							//Send verification SMS
							$pgUsersModel = new Api_Model_PaymentGatewayUsers();
							$pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
	
							$pgType = $pgDetails->gateway_id;
							
			
							
							$pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
	
							if($this->_chapId == 80184) {
							
							    $message = "Y'ello. Please use this verification code $activationCode to complete your registration on the MTN AppStore. Thank you.";
							  
							
							} else {
							    
							    $message = "Please use this verification code $activationCode to complete your registration.";
							    $message = ($translate != null) ? $translate->translate('Please use this verification code').' '.$activationCode.' '.$translate->translate('to complete your registration.') : $message;
							}
						   
							if($this->_chapId == 274515)
                                                            $message =  "Veuillez utiliser ce code de v�rification $activationCode pour terminer votre enregistrement.";
							

							if($this->_chapId == 585474)
							    $message =  "Veuillez utiliser ce code de verification $activationCode pour terminer votre enregistrement.";
                                                        
                                                        
							$result = $pgClass->sendSms($phoneNumber, $message, $chapId);
							
                                                        
                                                        if($this->_chapId == 283006) {
                                                            $flashMessage = $translate->translate('A verification SMS has been sent to your mobile. Please use the given password to complete your registration');
                                                            $flashMessageTrans = ($translate != null) ? $translate->translate($flashMessage) : $flashMessage;
                                                            $this->_flashMessenger->setNamespace('error')->addMessage($flashMessageTrans);
                              
                                                            $this->_redirect("/user/login");
                                                        
                                                        } else {
                                                            
                                                            $flashMessage = 'A verification SMS has been sent to your mobile. Please use the given verification code to complete your registration';
                                                            $flashMessageTrans = ($translate != null) ? $translate->translate($flashMessage) : $flashMessage;

                                                            $this->_flashMessenger->addMessage($flashMessageTrans);
                                                            $this->_redirect("/user/authentication");
                                                        
                                                            
                                                        }
						}
					}
                }
               }
            }
            else
            {
                $this->view->first_name = $firstName;
                $this->view->last_name = $lastName;
                $this->view->email = $email;
                $this->view->phone_number = $phoneNumber;

                $messages = $inputValidation->getMessages();
                
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

        //Assign the global mobile number to login view
        //$this->view->phone_number = ($this->_userId == 101543 || $this->_tempPassword != md5('mtnpassword') ) ? '' : $this->_mobileNo;
        
        if($this->_headerEnrichment){
            $this->view->phone_number = ($this->_userId == 101543 || $this->_userId == 132405) ? '' : $this->_mobileNo; //No need to set the mobile number to common user
        }
        
         if($isLowEndDevice):
            $this->_helper->viewRenderer('register-low-end');
         endif;
    }

    /**
     * verify the user with the verification code that is sending to the user device, with the db
     * @param : verification code
     * return : redirecting to the login page in successful authentication or gives the error message
     */

    public function authenticationAction()
    {
        $isLowEndDevice = $this->_isLowEndDevice;
        
        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');
        
        $userId = '';
        if(Zend_Session::namespaceIsset('userDetails'))
        {
            $userDetails = Zend_Session::namespaceGet('userDetails');
            $userId = $userDetails['userId'];
        }

        $chapId = $this->_chapId;
        $messages = array();
        
        if ($this->_request->isPost())
        {
            //Get the parameters
            $activationCode = trim($this->_getParam('activation_code'));

            //Check if User Id has been provided
            if ($userId === null || empty($userId))
            {
                //$messages[] =  "Error Verification";
                $messages[] =  ($translate != null) ? $translate->translate("Error Verification") : 'Error Verification';
            }

            //Check if Activation Code has been provided
            if ($activationCode === null || empty($activationCode))
            {
                //$messages[] =  "Verification code not given";
                $messages[] =  ($translate != null) ? $translate->translate("Verification code not given") : 'Verification code not given';
            }

            $userModel = new Api_Model_Users();
            
            //Check if combination exists
            $recordCount = $userModel->getUserCountById($chapId, $userId, $activationCode);
            
            if(empty($recordCount) || is_null($recordCount) || $recordCount <= 0)
            {
                //$messages[] = "Verification failed. verification code is incorrect";
                $messages[] =  ($translate != null) ? $translate->translate("Verification failed. verification code is incorrect") : 'Verification failed. verification code is incorrect';
            }

            $status = 1;

            if (empty($messages))
            {
                //update the status
                if($userModel->updateVerificationStatus($userId, $status))
                {
                    if($this->_chapId != 935529){
                    $userDetails = $userModel->getUserById($userId);
                    $phoneNumber =  $userDetails->mobile_no;

                    //Sending SMS after Authentication
                    $pgUsersModel = new Api_Model_PaymentGatewayUsers();
                    $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);

                    $pgType = $pgDetails->gateway_id;

                    $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

                    //$message = 'You have completed your registration successfully. "MTN NIGERIA APP STORE"';
                    if($this->_chapId == 80184){
                        $message = "Y'ello. You have completed your registration to the MTN Appstore successfully. Thank you.";
                    } else {
                        $message = 'You have completed your registration successfully';
                    }
      
                    
                    $message =  ($translate != null) ? $translate->translate($message) : $message;
                    
                    if($this->_chapId == 274515)
                    	$message =  "Vous avez termin� votre enregistrement avec succ�s.";

                    $result = $pgClass->sendSms($phoneNumber, $message, $chapId);      
                    }
                    //$this->_flashMessenger->addMessage('You successfully completed the registration process');
                    $flashMessage = ($translate != null) ? $translate->translate("You successfully completed the registration process") : "You successfully completed the registration process";
                    $this->_flashMessenger->addMessage($flashMessage);
                    $this->_redirect('/user/login');
                }
                else
                {
                    //$messages[] = 'Already Verified User';
                    $messages[] = ($translate != null) ? $translate->translate("Already Verified User") : 'Already Verified User';
                }
            }

            $this->view->Messages = $messages;
        }
        
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
        
        if($isLowEndDevice):
            $this->_helper->viewRenderer('authentication-low-end');
        endif;
    }

    /**
     * logs the user by checking the username & password
     * @param : username
     * @param : password
     * return : redirect to the last URI after successful login or gives the error message
     */

    //Condition ($this->_chapId == 21134) has been added for check if MTN nigeria chap then user authenticated by mobile or username
    public function loginAction()
    {
        
     ///   error_reporting(E_ALL);
     //   ini_set('display_errors', 1);

        //Zend_Debug::dump($this->_headerEnrichment);die();
        //Assign Chp Id
        $this->view->chapId = $this->_chapId;
        $this->view->flashMessages = $this->_helper->flashMessenger->setNamespace('error')->getMessages();
        $error = '';
        $translate = Zend_Registry::get('Zend_Translate');
        
        //Check mobile_no or username
        $partError = null;
        if(($this->_headerEnrichment)){
            $partError = 'mobile number';
        }
        else{
            $partError = 'username';
        }
        
        $error = $this->_request->getParam('error', '');
        
        if($error)  {
            //$this->view->error = "The username or password you entered is incorrect";
            $msgErrUnPwd = "An error occurred, please try again.";
            $this->view->error =  ($translate != null) ? $translate->translate($msgErrUnPwd) : $msgErrUnPwd ;
        }
               

        
        $sessionChapDetails = new Zend_Session_Namespace('partnermobile');
        $chapId = $sessionChapDetails->id;
        
        $isLowEndDevice = $this->_isLowEndDevice;
        
        //When form is submitted
        if ($this->_request->isPost()) 
        {
            $username = trim($this->getRequest()->getPost('username', ''));
            $mobileNo = trim($this->getRequest()->getPost('mobile_no', ''));
            $password = trim($this->getRequest()->getPost('password', ''));

            if (($username != '' || $mobileNo != '') && $password != '') 
            {
                
                //Check if email exists
                $user = new Partnermobile_Model_Users();
                
                if(($this->_headerEnrichment)){
                    $userRow = $user->fetchRow($user->select()->where('mobile_no = ?', $mobileNo));
                }
                else{
                    $userRow = $user->fetchRow($user->select()->where('email = ?', $username));
                }
                
                if ($userRow) 
                {
                    $user = new Partnermobile_Model_Users();
                    
                    if(($this->_headerEnrichment)){
                        $verified = $user->getUserStatusByMobileNo($mobileNo);
                        //gets the userId by email and set it to the session, this is needed when redirecting to the authentication as userId has to be there for resend verification
                        $userData = $user->getUserByMobileNo($mobileNo);
                    }
                    else{
                        $verified = $user->getUserStatusByEmail($username);
                        //gets the userId by email and set it to the session, this is needed when redirecting to the authentication as userId has to be there for resend verification
                        $userData = $user->getUserByEmail($username);
                    }

                    
                    $userDetails = new Zend_Session_Namespace('userDetails');
                    $userDetails->userId = $userData['id'];

                    //Check if account has been verified
               
                    if ($verified != '0') 
                    {

                        $db = Zend_Registry::get('db');
                       
                        if(($this->_headerEnrichment)){
                            $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'mobile_no', 'password', "MD5(?) AND status=1 AND type='USER' AND mobile_no != '' AND chap_id = $chapId");
                            $authDbAdapter->setIdentity($mobileNo);
                            $authDbAdapter->setCredential($password);
                        }
                        else{
                            if($this->_chapId== 935529){
                                $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'email', 'password', "MD5(?) AND status=1 AND type='USER'  AND chap_id = $chapId");
                            }else{
                               $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'email', 'password', "MD5(?) AND status=1 AND type='USER' AND mobile_no != '' AND chap_id = $chapId"); 
                            }                                
                                $authDbAdapter->setIdentity($username);
                                $authDbAdapter->setCredential($password);
                        }

                        $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);
                       
                        //If validated successfully
                        if($result->isValid()) 
                        {
                            Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));
                                  
                            //Redirect to requested page
                            $session = new Zend_Session_Namespace ( 'lastRequest' );
                                        
                            //Redirect to last requested URL if it was
                            if (isset($session->lastRequestUri)) 
                            {
                                $newpage = $session->lastRequestUri;
                                $session->lastRequestUri = NULL;
                                $this->_redirect($newpage);
                            }
                                        
                            $this->_redirect("/");
                        } 
                        else 
                        {        
                            //$this->view->error = "The username or password you entered is incorrect";
                            $msgErrUnPwd = "The $partError or password you entered is incorrect";
                            $this->view->error =  ($translate != null) ? $translate->translate($msgErrUnPwd) : $msgErrUnPwd ;
                        }
                    } 
                    else 
                    {
                        //$this->_flashMessenger->addMessage('You have not verified your account. A verification SMS has been sent to your mobile . Please use the given verification code or get it resent by clicking below "Resend verification code" to verify your account');
                        $msgErrVerify = 'You have not verified your account. A verification SMS has been sent to your mobile . Please use the given verification code or get it resent by clicking below "Resend verification code" to verify your account';
                        $flashMessege =  ($translate != null) ? $translate->translate($msgErrVerify) : $msgErrVerify ;
                        $this->_flashMessenger->addMessage($flashMessege);
                        
                        $this->_redirect("/user/authentication");
                        //$this->view->error = "This account has not been verified.";
                    }
                } 
                else 
                {
                   
                    //$this->view->error = "Please enter a valid username";
                    $msgErrInvalid = "Please enter a valid $partError";
                    $this->view->error =  ($translate != null) ? $translate->translate($msgErrInvalid) : $msgErrInvalid ;
                }
            } 
            else 
            {           
                //$this->view->error = "Please enter your username and password";
                $msgErrUnPwd = "Please enter your $partError and password";
                $this->view->error =  ($translate != null) ? $translate->translate($msgErrUnPwd) : $msgErrUnPwd ;
            }
        }
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
        
        //Assign the global mobile number to login view
        //$setMobileNo = ($this->_userId == 101543 || $this->_tempPassword != md5('mtnpassword') ) ? '' : $this->_mobileNo;
        $setMobileNo = '';
        if($this->_headerEnrichment){
            $setMobileNo = ($this->_userId == 101543 || $this->_userId == 132405) ? '' : $this->_mobileNo; //No need to set the mobile number to common user
        }
        
        /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99' || $_SERVER['REMOTE_ADDR'] == '203.153.223.109'){
            echo $this->_tempPassword.' ### '.md5('mtnpassword').' ### '.$this->_userId;
            echo $setMobileNo; die();
        }*/
        
        $this->view->mobileNo = $setMobileNo;
                
        if($isLowEndDevice):
            $this->_helper->viewRenderer('login-low-end');
        endif;
        
 
            $googleAuth = new Nexva_Google_GoogleAuth();
            $this->view->gooleUrl = $googleAuth->createAuthUrl();
            
     
            if (!is_null($this->_request->facebook)) {
            
            	$fb = new Nexva_Facebook_FacebookConnect('web', '840862629279829', '617e486288a6718bf8554c8813120109');
            
            	//check of the user cancelled FB auth attempt or error in auth attempt
            	if( !is_null($this->_request->error_reason)) $this->_redirect('/info/login'); //somehow FB's PHP SDK never sets this :( See next line
            	if( !$fb->hasUserAuthorizedApp() ) {
            		$this->_helper->flashMessenger->setNamespace('error')->addMessage('An error occurred, please try again.');
            		$this->_redirect('/user/login'); //hopefully, a foolproof way to check for authorization even if line above fails
            			
            	}
            	 
            	$fbUser = $fb->getLoggedFbUser();
            
            	$userModel = new Model_User();
            	$url = $userModel->doLoginFb($fbUser['email'], 'web', '840862629279829', '617e486288a6718bf8554c8813120109', $this->_chapId);
            	$this->_redirect($url);
            }
            

    }
    
    
    public function accountAction()
    {
        $isLowEndDevice = $this->_isLowEndDevice;

        // retrive all the downloaded apps 
        $modelDownloadStats = new Api_Model_StatisticsDownloads();
        $model_ProductBuild = new Model_ProductBuild();

        $auth = Zend_Auth::getInstance();        
        $userId = $auth->getIdentity()->id;
        
        $downloadedProducts = $modelDownloadStats->getDownloadedBuilds($userId,100,'','MOBILE');

        $nexApi = new Nexva_Api_NexApi();
        $downloadedProductInfo = $nexApi->getProductDetails($downloadedProducts, $this->_deviceId, true);
        
        $this->view->downloadedApps = $downloadedProductInfo;

        //currency details by chap
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
        $this->view->currencyDetails = $currencyDetails;

        $this->view->flashMessages = $this->_flashMessenger->getMessages();
        if($isLowEndDevice):
            $this->_helper->viewRenderer('account-low-end');
         endif;

    }

    //Log out action
    public function logoutAction() 
    {
        $this->_helper->viewRenderer->setNoRender(true);
        Zend_Auth::getInstance()->clearIdentity();
        $session_logout = new Zend_Session_Namespace('logoutaction');
        $session_logout->logout = true;
        Zend_Session::destroy();
        $this->_redirect('/');
    }

    /**
     * change the mobile number, regenerate the activation code & change the status to zero
     * @param : mobile number, mobile number confirmation
     * return user messages
     */
    public function changeMobileAction()
    {
        $translate = Zend_Registry::get('Zend_Translate');
        
        $isLowEndDevice = $this->_isLowEndDevice;
       
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity())
        {
            //When form is submitted
            if ($this->_request->isPost())
            {
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

                $auth = Zend_Auth::getInstance();
                $userId = $auth->getIdentity()->id;

                if($inputValidation->isValid())
                {
                    //Generate activation code
                    
                    if($this->_chapId ==  80184 || $this->_chapId ==  276531) {
                    	$activationCode = $this->__random_numbers(4);
                    } else {
                    	$activationCode = substr(md5(uniqid(rand(), true)), 5,8);
                    }
                    
                    $status = 0;

                    $user = new Api_Model_Users();
                    if (!$user->validateUserMdn($mobileNumber))
                    {
                        //$errors[] =  "This number has already been used. Please try another";
                        
                        $msgErrExist = "This number has already been used. Please try another";
                        $errors[] =  ($translate != null) ? $translate->translate($msgErrExist) : $msgErrExist ;
                    }

                    // When errors generated send out error response and terminate.
                    if (!empty($errors))
                    {
                        $this->view->ErrorMessages = $errors;
                        return;
                    }
                    else
                    {
                        $user->updateMobileNumebr($userId, $mobileNumber, $activationCode, $status);

                        //Send SMS with verfication code                   
                        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
                        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);

                        $pgType = $pgDetails->gateway_id;

                        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

                        //$message = 'Please use this verification code '.$activationCode.' to verify your mobile number. "MTN NIGERIA APP STORE"';
                        
                        if($this->_chapId == 80184) {
                            $message = "Y'ello. Please use this verification code ".$activationCode." to verify your mobile number on the MTN AppStore. Thank you.";
                        } else {
                            $message = 'Please use this verification code '.$activationCode.' to verify your mobile number.';
                        }
                      
                        
                        $msgErrVerifyP1 = "Please use this verification code";
                        $msgErrVerifyP2 = "to verify your mobile number.";
                        $message = ($translate != null) ? $translate->translate($msgErrVerifyP1).' '.$activationCode.' '.$translate->translate($msgErrVerifyP2) : $message ;
                        
                        if($this->_chapId == 274515)
            	             $message =  "Veuillez utiliser ce code de v�rification $activationCode pour terminer votre enregistrement";
                        
                        $result = $pgClass->sendSms($mobileNumber, $message, $chapId);      
                       
                        $userDetails = new Zend_Session_Namespace('userDetails');
                        $userDetails->userId = $userId;

                        $msgSucMobileNo = "Mobile number changed successfully";
                        $flashMessage =  ($translate != null) ? $translate->translate($msgSucMobileNo) : $msgSucMobileNo ;
                        
                        //$this->_flashMessenger->addMessage('Mobile number changed successfully');
                        $this->_flashMessenger->addMessage($flashMessage);
                        $this->_redirect("/user/authentication");
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
        
        if($isLowEndDevice):
            $this->_helper->viewRenderer('change-mobile-low-end');
         endif;
    }

    /**
     * reset password as the user request
     * @param : userId gets through the session if it comes from the forgot password or gets from the Zend_Auth if the user logged in
     * @param : email,mobile no gets through the form input; email & mobile no sets on the view by passing the data to the view and user cannot edit the values
     * return : redirect to the login page in successful reset password or gives the error message
     */

    public function resetPasswordAction()
    {
        $translate = Zend_Registry::get('Zend_Translate');
        
            $isLowEndDevice = $this->_isLowEndDevice;
            $userId = '';
            if(Zend_Session::namespaceIsset('userDetails'))
            {
                $userDetails = Zend_Session::namespaceGet('userDetails');
                $userId = $userDetails['userId'];
            }

            //if user is logged in userId gets through Zend auth
            $auth = Zend_Auth::getInstance();
            if($auth->hasIdentity())
            {
                $userId = $auth->getIdentity()->id;
            }

            $user = new Api_Model_Users();
            $userDetails = $user->getUserById($userId);

//            $this->view->email =  $userDetails->email;
            $this->view->mobile_no =  $userDetails->mobile_no;
            $this->view->email =  $userDetails->email;

            //When form is submitted
            if ($this->_request->isPost())
            {
                $filters = array
                (
                    //'email'    => array('StringTrim'),
                    'mobile_number'     => array('StringTrim'),
                    'password'     => array('StringTrim'),
                    'password2'     => array('StringTrim')
                );

                //$email   = trim($this->getRequest()->getPost('email',null));
                $mobileNumber    = trim($this->getRequest()->getPost('mobile_number',null));
                $password    = trim($this->getRequest()->getPost('password',null));
                $password2    = trim($this->getRequest()->getPost('password2',null));

                $validators = array(
                   /* 'email' => array(
                        new Zend_Validate_NotEmpty(),new Zend_Validate_EmailAddress(),
                        Zend_Filter_Input::MESSAGES => array(
                            array(
                                Zend_Validate_NotEmpty::IS_EMPTY => 'Please enter your mobile number'
                            ),
                            array(
                                Zend_Validate_EmailAddress::INVALID_FORMAT => 'Please enter a valid email address for your email'
                            )
                        )
                    ),*/
                    'mobile_number' => array(
                        new Zend_Validate_NotEmpty(),new Zend_Validate_Digits(),
                        Zend_Filter_Input::MESSAGES => array(
                            array(
                                Zend_Validate_NotEmpty::IS_EMPTY => 'Please confirm your Mobile Number'
                            ),
                            array(
                                Zend_Validate_Digits::INVALID => ''
                            )
                        )
                    ),
                    'password' => array(
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
                                Zend_Validate_Identical::NOT_SAME => 'Password not matching'
                            )
                        )
                    )
                );
                if($this->_chapId==283006){
                    unset($validators['password']);
                    unset($validators['password2']);
                }
                if($this->_chapId==935529){
                    unset($validators['mobile_number']);
                    
                }
                //Validate and filter inputs
                $inputValidation = new Zend_Filter_Input($filters, $validators, $_POST);

                if($inputValidation->isValid())
                {
                    $userModel = new Model_User();
                    if($this->_chapId == 283006){
                        $password= $this->__random_numbers(5);
                        $userModel->resetPassword($userId,  $password);
                        $msgSucPwd = 'Please use the given password to log in.'.$password;
                    }else{
                        $userModel->resetPassword($userId, $password);
                        $msgSucPwd = "Password reset successfully";
                    }
                    $flashMessage =  ($translate != null) ? $translate->translate($msgSucPwd) : $msgSucPwd ;
                    //$this->_flashMessenger->addMessage('Password reset successfully.');
                    $this->_flashMessenger->setNamespace('error')->addMessage($flashMessage);
                    $this->_redirect("/user/login");
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
            
            if($isLowEndDevice):
                $this->_helper->viewRenderer('reset-password-low-end');
            endif;
    }

    /**
     * reset the activation code and send it to the user, then redirect to forgotVerification function
     * @param : e-mail
     */

    public function forgotPasswordAction()
    {
        $translate = Zend_Registry::get('Zend_Translate');
        $this->view->chapId = $this->_chapId;
        $isLowEndDevice = $this->_isLowEndDevice;
        
        if ($this->_request->isPost())
        {
            $filters = array
            (
                'phone_number'  => array('StringTrim')
            );
            $chapId = $this->_chapId;
            $mobileNo  = trim($this->getRequest()->getPost('mobile_no',null));
            $email  = trim($this->getRequest()->getPost('email',null));

            $validators = array(
                'phone_number' => array(
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
            );
            if($this->_chapId== 935529){// 935529
                unset($validators['phone_number']);
            }else{
                unset($validators['email']);
            }
            //Validate and filter inputs
            $inputValidation = new Zend_Filter_Input($filters, $validators, $_POST);

            if($inputValidation->isValid())
            {
                $userModel = new Api_Model_Users();
                if($this->_chapId != 935529){
                        
                        $tmpUser = $userModel->getUserByMobileNo($mobileNo);

                        $mobileNumber = '';

                        if(is_null($tmpUser))
                        {
                            //$errors[] = "User not found registered with this email";
                            $errUsrNotFound = "User not found registered with this phone number";
                            $errors[] =  ($translate != null) ? $translate->translate($errUsrNotFound) : $errUsrNotFound ;

                            $this->view->ErrorMessages = $errors;

                            // check and load the low-end view if the device is low end
                             if($isLowEndDevice):
                                $this->_helper->viewRenderer('forgot-password-low-end');
                             endif;

                            return;

                        }
                        else
                        {
                            $mobileNumber = $tmpUser->mobile_no;
                        }


                        if($this->_chapId ==  80184 || $this->_chapId ==  276531) {
                                $activationCode = $this->__random_numbers(4);
                        } else {
                                $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
                        }

                        $status = 1;

                        //Check if mobile number is given
                        if (($mobileNumber === null || empty($mobileNumber)) && ($this->_chapId != 935529))
                        {
                            //$errors[] = "Mobile number not registered";

                            $errMobNotReg = "Mobile number not registered";
                            $errors[] =  ($translate != null) ? $translate->translate($errMobNotReg) : $errMobNotReg ;
                        }
                }else{
                    $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
                }
                // When errors generated send out error response and terminate.
                if (!empty($errors))
                {
                    $this->view->ErrorMessages = $errors;
                    return;
                }
                else
                {
                    $userModel->updateActivationCode($tmpUser->id, $activationCode, $status);
                    
                    if($this->_chapId != 935529){
                        $userModel->updateActivationCode($tmpUser->id, $activationCode, $status);
                        //Send verification SMS
                        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
                        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);

                        $pgType = $pgDetails->gateway_id;

                        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

                        //$message = 'Please use this verification code '.$activationCode.' to complete the password resetting process. "MTN NIGERIA APP STORE"';

                        if($this->_chapId == 80184) {

                            $message = "Y'ello. Please use this verification code  $activationCode to to complete the password resetting process on the MTN AppStore. Thank you.";


                        } else {
                            $message = 'Please use this verification code '.$activationCode.' to complete the password resetting process.';
                        }

                        if($this->_chapId == 274515)
                        $message = 'Please use this verification code '.$activationCode.' to complete the password resetting process.';

                        $errEmptyCodeP1 = "Please use this verification code";
                        $errEmptyCodeP2 = "to complete the password resetting process.";
                        $message = ($translate != null) ? $translate->translate($errEmptyCodeP1).' '.$activationCode.' '.$translate->translate($errEmptyCodeP2) : $message ;

                        if($this->_chapId == 274515)
                            $message =  "Veuillez utiliser ce code  $activationCode pour terminer le processus de r�initialisation du mot de passe";

                        $result = $pgClass->sendSms($mobileNumber, $message, $chapId); 

                        $userDetails = new Zend_Session_Namespace('userDetails');
                        $userDetails->userId = $tmpUser->id;



                        if($this->_chapId == 80184) {
                            $msgInfo = 'A verification SMS has been sent to your mobile. Please use the given verification code to verify';
                        } else {
                            $msgInfo = 'You have requested to change password. A verification SMS has been sent to your mobile. Please use the given verification code to verify';
                        }
                    }else{
                        
                        
                        $user= new Cpbo_Model_User();
                        $userObj = $user->getUserDetailsByEmail($email);
                        $userModel->updateActivationCode($userObj->id, $activationCode, $status);
                        $userMeta =new Model_UserMeta();
                        $firstname=$userMeta->getFirstName($userObj->id);
                        $lastname=$userMeta->getLastName($userObj->id);
                        $userDetails = new Zend_Session_Namespace('userDetails');
                        $userDetails->userId = $userObj->id;
                        
                        $msgInfo = 'You have requested to change password. A verification email has been sent to '.$email.'. Please use the given verification code to verify';
                        $this->sendEmailValidationMessage($activationCode,$firstname." ".$lastname,$email);
                        
                    }
                    $flashMessage =  ($translate != null) ? $translate->translate($msgInfo) : $msgInfo;
                    $this->_flashMessenger->addMessage($flashMessage);
                    $this->_redirect("/user/forgot-verification");
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
        
        // check and load the low-end view if the device is low end
         if($isLowEndDevice):
            $this->_helper->viewRenderer('forgot-password-low-end');
         endif;
    }

    /**
     *verify the verification code with the database.
     *@param : verification code
     *return : redirect to the reset password after successful verification or gives the error message in failure of verification
     */

    public function forgotVerificationAction()
    {
        $translate = Zend_Registry::get('Zend_Translate');
        
          $isLowEndDevice = $this->_isLowEndDevice;
        //include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );
        if ($this->_request->isPost())
        {
            $filters = array
            (
                'verificationCode'    => array('StringTrim')

            );

            $chapId = $this->_chapId;
            $verificationCode   = trim($this->getRequest()->getPost('verificationCode',null));

            $userDetails = Zend_Session::namespaceGet('userDetails');

            $userId = $userDetails['userId'];

            $validators = array(
                'verificationCode' => array(
                    new Zend_Validate_NotEmpty(),
                    Zend_Filter_Input::MESSAGES => array(
                        array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Please enter your Verification Code'
                        )
                    )
                )
            );

            //Validate and filter inputs
            $inputValidation = new Zend_Filter_Input($filters, $validators, $_POST);

            if($inputValidation->isValid())
            {
                $userModel = new Api_Model_Users();
                $validate = $userModel->validateActivationcode($verificationCode);
                if($validate)
                {
                    $status = 1;
                    $userModel->updateVerificationStatus($userId,$status);
                    $this->_redirect("/user/reset-password");
                }
                else
                {
                    //$errors[] = "Verification code is incorrect.";
                    $errors[] =  ($translate != null) ? $translate->translate("Verification code is incorrect.") : 'Verification code is incorrect.';
                }

                if (!empty($errors))
                {
                    $this->view->ErrorMessages = $errors;
                    return;
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
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
         if($isLowEndDevice):
            $this->_helper->viewRenderer('forgot-verification-low-end');
         endif;
    }

    /**
     *resend the verification code for the user device
     *@param : gets the user id from the session
     *checks whether user have the mobile number registered with before sending a SMS
     *return : send a SMS with verification code to the user device
     */

    public function resendVerificationAction()
    {
        $translate = Zend_Registry::get('Zend_Translate');
        
        $this->_helper->viewRenderer->setNoRender(true);
        
        if ($this->_request->isPost())
        {
            $resend   = trim($this->getRequest()->getPost('resend',null));
            if($resend)
            {
                $userId = '';
                if(Zend_Session::namespaceIsset('userDetails'))
                {
                    $userDetails = Zend_Session::namespaceGet('userDetails');
                    $userId = $userDetails['userId'];
                }

                $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
                
                if($this->_chapId ==  80184 || $this->_chapId ==  276531) {
                	$activationCode = $this->__random_numbers(4);
                } else {
                	$activationCode = substr(md5(uniqid(rand(), true)), 5,8);
                }
                
                $status = 0;

                $user = new Api_Model_Users();
                $userDetails = $user->getUserById($userId);

                $mobileNumber = $userDetails->mobile_no;
                $email = $userDetails->email;
 
                if(is_null($mobileNumber) OR $mobileNumber == '')
                {                    
                    if($this->_chapId != 935529){
                        $msgNotFound =  ($translate != null) ? $translate->translate("Mobile Number not found for this user") : 'Mobile Number not found for this user';
                        //$this->_flashMessenger->addMessage('Mobile Number not found for this user');
                        $this->_flashMessenger->addMessage($msgNotFound);
                        return ;
                    }
                }
                elseif(is_null($email) OR $email == '')
                {
                    //$this->_flashMessenger->addMessage('Email not found for this user');
                    $msgNotFound =  ($translate != null) ? $translate->translate("Email not found for this user") : 'Email not found for this user';
                    $this->_flashMessenger->addMessage($msgNotFound);
                    return;
                }
                else
                {
                    //$this->_flashMessenger->addMessage('You have requested to resend verification code. A verification SMS has been sent to your mobile. Please use the given verification code to verify your account');
                    
                    
                    if($this->_chapId == 80184) {
                        $msgInfo = 'A verification SMS has been sent to your mobile. Please use the given verification code to verify your account';
                    } else {
                        if($this->_chapId == 935529){
                            $msgInfo = 'You have requested to resend verification code. A verification email has been sent to your email. Please use the given verification code to verify your account';
                            
                        }else{
                           $msgInfo = 'You have requested to resend verification code. A verification SMS has been sent to your mobile. Please use the given verification code to verify your account'; 
                        }
                    }
                    
                    $flashMessage =  ($translate != null) ? $translate->translate($msgInfo) : $msgInfo;
                    $this->_flashMessenger->addMessage($flashMessage);
                }

                $user->updateActivationCode($userId, $activationCode, $status);
                $chapId = $this->_chapId;
 
                //Send verification SMS
                if($this->_chapId != 935529){
                    $pgUsersModel = new Api_Model_PaymentGatewayUsers();
                    $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);

                    $pgType = $pgDetails->gateway_id;

                    $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

                    //$message = 'Please use this verification code '.$activationCode.' as you requested. "MTN NIGERIA APP STORE"';

                    if($this->_chapId == 80184) {
                            $message = "Y'ello. Please use this verification code ".$activationCode." to complete your registration on the MTN AppStore. Thank you.";

                    } else {
                        $message = 'Please use this verification code '.$activationCode.' as you requested.';
                    }



                    $errVerifyCodeP1 = "Please use this verification code";
                    $errVerifyCodeP2 = "as you requested.";
                    $message = ($translate != null) ? $translate->translate($errVerifyCodeP1).' '.$activationCode.' '.$translate->translate($errVerifyCodeP2) : $message ;

                    if($this->_chapId == 274515)
                            $message =  "Veuillez utiliser ce code de v�rification   $activationCode.";

                    $result = $pgClass->sendSms($mobileNumber, $message, $chapId);
                    }else{
                        
                         $userMeta = new Model_UserMeta();                         
                         $firstName = $userMeta->getFirstName($userId);
                         $lastName = $userMeta->getLastName($userId);
                         
                         $this->sendEmailValidationMessage($activationCode,$firstName." ".$lastName,$email);
                    }
                }
            
        }
        else
        {
            $this->_redirect('/');
        }
        $this->view->flashMessages = $this->_flashMessenger->getMessages();
    }
    
    public function infoMtnNetworkAction()
    {
        
    }
    
    
    public function fakeUserAction()
    {
    
    }
    
    public function updateProfileAction(){
        $isLowEndDevice = $this->_isLowEndDevice;       

        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');
        
        //if user is logged in userId gets through Zend auth
        $auth = Zend_Auth::getInstance();
        
        $extEmail = NULL;
        
        if($auth->hasIdentity())
        {
            $userId = $auth->getIdentity()->id;
            
            //Get the userd details
            $user = new Api_Model_Users();
            $userDetails = $user->getUserById($userId);
            $extEmail = $userDetails['email'];
            
            //Get the userd details
            $userMeta = new Model_User();
            $extFirstName = $userMeta->getMetaValue($userId, 'FIRST_NAME');
            $extLastName = $userMeta->getMetaValue($userId, 'LAST_NAME');
            
            //setting the parameters to view
            $this->view->first_name = $extFirstName;
            $this->view->last_name = $extLastName;
            $this->view->email = $extEmail;
        }

        //When form is submitted
        if ($this->_request->isPost()) 
        {
            //Server side validation
            $filters = array
            (
                'first_name'    => array('StringTrim'),
                'last_name'     => array('StringTrim'),
                'email'         => array('StringTrim')
            );

            $firstName     = trim($this->getRequest()->getPost('first_name',null));
            $lastName      = trim($this->getRequest()->getPost('last_name',null));
            $email          = trim($this->getRequest()->getPost('email',null));

            $validators = array(
                'first_name' => array(
                    new Zend_Validate_NotEmpty(),
                    Zend_Filter_Input::MESSAGES => array(
                        array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Please enter First Name',
                        )
                    )
                ),
                'last_name' => array(
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
                )
                
            );

            //Validate and filter inputs
            $inputValidation = new Zend_Filter_Input($filters, $validators, $_POST);

            if($inputValidation->isValid())
            {

                $user = new Api_Model_Users();
                
                if($extEmail != $email){
                    if (!$user->validateUserEmail($email))
                    {
                        $errors[] =  ($translate != null) ? $translate->translate("This Email already exists") : 'This Email already exists';
                    }
                }

                // When errors generated send out error response and terminate.
                if (!empty($errors))
                {
                    $this->view->ErrorMessages = $errors;
                    $this->view->success = false;

                    //setting the parameters again to display on the view
                    $this->view->first_name = $firstName;
                    $this->view->last_name = $lastName;
                    $this->view->email = $email;
                    
                    // check and load the low-end view if the device is low end
                     if($isLowEndDevice):
                        $this->_helper->viewRenderer('update-profile-low-end');
                     endif;
                     
                    return;
                }
                else
                {
                   $userData = array(
                                        'username' => $email,
                                        'email' => $email
                                    );
                    
                   //Update username and email to users table
                    if($user->updateUserProfile($userData, $userId))
                    {
                        //Updating the meta value
                        $userMeta = new Model_UserMeta();
                        $userMeta->setEntityId($userId);
                        $userMeta->FIRST_NAME = $firstName;
                        $userMeta->LAST_NAME = $lastName;
                    }
                    
                    $flashMessage = 'Profile successfully updated.';
                    $flashMessage = ($translate != null) ? $translate->translate($flashMessage) : $flashMessage;

                    $this->_flashMessenger->addMessage($flashMessage);

                    //$this->_helper->viewRenderer('account');
                    $this->_redirect('/user/account');
                   
                }
            }
            else
            {
                $this->view->first_name = $firstName;
                $this->view->last_name = $lastName;
                $this->view->email = $email;

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

        if($isLowEndDevice):
            $this->_helper->viewRenderer('update-profile-low-end');
        endif;
    }

    public function userLoginIranAction(){

        $this->_helper->viewRenderer->setRender('login');

        //Assign Chp Id
        $this->view->chapId = $this->_chapId;

        $translate = Zend_Registry::get('Zend_Translate');

        $sessionChapDetails = new Zend_Session_Namespace('partnermobile');
        $chapId = $sessionChapDetails->id;

        $isLowEndDevice = $this->_isLowEndDevice;

        //When form is submitted
        if ($this->_request->isPost())
        {
            $username = trim($this->getRequest()->getPost('username', ''));
            $mobileNo = trim($this->getRequest()->getPost('mobile_no', ''));
            $password = trim($this->getRequest()->getPost('password', ''));

            if ( $mobileNo != '' && $password != '')
            {
                //Check if user exists by mobile number
                $user = new Partnermobile_Model_Users();
                $userRow = $user->fetchRow($user->select()->where('mobile_no = ?', $mobileNo));

                if ($userRow)
                {
                    //we do have a user in db so directly we logged him/her in
                    $user = new Partnermobile_Model_Users();
                    $verified = $user->getUserStatusByMobileNo($mobileNo);
                    $userData = $user->getUserByMobileNo($mobileNo);

                    $userDetails = new Zend_Session_Namespace('userDetails');
                    $userDetails->userId = $userData['id'];

                    //Check if account has been verified
                    if ($verified != '0')
                    {
                        $db = Zend_Registry::get('db');

                        $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'mobile_no', 'password', "MD5(?) AND status=1 AND type='USER' AND mobile_no != '' AND chap_id = $chapId");
                        $authDbAdapter->setIdentityColumn('mobile_no');
                        $authDbAdapter->setIdentity($mobileNo);
                        $authDbAdapter->setCredentialColumn('password');
                        $authDbAdapter->setCredential($password);

                        $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);
                        /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                            Zend_Debug::dump($result); die();
                        }*/

                        //If validated successfully
                        if($result->isValid())
                        {
                            Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));

                            //Redirect to requested page
                            $session = new Zend_Session_Namespace ( 'lastRequest' );

                            //Redirect to last requested URL if it was
                            if (isset($session->lastRequestUri))
                            {
                                $newpage = $session->lastRequestUri;
                                $session->lastRequestUri = NULL;

                                if($newpage == 'user/user-login-iran'){ $newpage = 'user/login';}
                                $this->_redirect($newpage);
                            }

                            $this->_redirect("/");
                        }
                        else
                        {

                            $msgErrUnPwd = "The mobile number or password you entered is incorrect";
                            $this->_helper->flashMessenger->setNamespace('error')->addMessage(($translate != null) ? $translate->translate($msgErrUnPwd) : $msgErrUnPwd);

                            $this->_redirect("/user/login");
                        }
                    }
                }
                else
                {
                    //no user so we import data from through SOAP from iran
                    $userModel = new Model_User();
                    $result = $userModel->sendXml($password,$mobileNo);

                    if( $result['EaiEnvelope']['Payload']['EcareData']['Response']['Result_OutputData']['resultCode'] == 0 ){

                        $username = $result['EaiEnvelope']['Payload']['EcareData']['Response']['CustDetails_OutputData']['firstName'].'_'.$result['EaiEnvelope']['Payload']['EcareData']['Response']['CustDetails_OutputData']['lastName'];

                        //then we insert the imported record to our db
                        $this->registerIran($password,$mobileNo,$username);

                        //here after adding a record to the db we make user to sign in
                        $user = new Partnermobile_Model_Users();
                        $verified = $user->getUserStatusByMobileNo($mobileNo);
                        $userData = $user->getUserByMobileNo($mobileNo);

                        $userDetails = new Zend_Session_Namespace('userDetails');
                        $userDetails->userId = $userData['id'];

                        //Check if account has been verified
                        if ($verified != '0')
                        {
                            $db = Zend_Registry::get('db');

                            $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'mobile_no', 'password', "MD5(?) AND status=1 AND type='USER' AND mobile_no != '' AND chap_id = $chapId");
                            $authDbAdapter->setIdentityColumn('mobile_no');
                            $authDbAdapter->setIdentity($mobileNo);
                            $authDbAdapter->setCredentialColumn('password');
                            $authDbAdapter->setCredential($password);

                            $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);

                        }
                    } else {
                        $msgErrUnPwd = "The mobile number or password you entered is incorrect";
                        $this->_helper->flashMessenger->setNamespace('error')->addMessage(($translate != null) ? $translate->translate($msgErrUnPwd) : $msgErrUnPwd);

                        $this->_redirect("/user/login");
                    }

                    //Redirect to requested page
                    $session = new Zend_Session_Namespace ( 'lastRequest' );

                    //Redirect to last requested URL if it was
                    if (isset($session->lastRequestUri))
                    {
                        $newpage = $session->lastRequestUri;
                        $session->lastRequestUri = NULL;

                        if($newpage == 'user/user-login-iran'){ $newpage = 'user/login';}
                        $this->_redirect($newpage);
                        //$this->_redirect('/user/login');

                    }
                    $this->_redirect("/");
                }
            }
            else
            {
                //$this->view->error = "Please enter your username and password";
                $msgErrUnPwd = "Please enter your Mobile Number and password";
                $this->view->error =  ($translate != null) ? $translate->translate($msgErrUnPwd) : $msgErrUnPwd ;
            }
        }
        //$this->_redirect('/user/account');
        $this->view->flashMessages = $this->_flashMessenger->getMessages();

        //Assign the global mobile number to login view
        //$this->view->mobileNo = ($this->_userId == 101543 || $this->_tempPassword != md5('mtnpassword') ) ? '' : $this->_mobileNo; //No need to set the mobile number to common user

        if($isLowEndDevice):
            $this->_helper->viewRenderer('login-low-end');
        endif;
    }

    public function registerIran($password,$mobileNo,$username)
    {
        $isLowEndDevice = $this->_isLowEndDevice;

        //Assign Chp Id
        $this->view->chapId = $this->_chapId;

        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');

            $firstName     = trim($this->getRequest()->getPost('first_name',null));
            $lastName      = trim($this->getRequest()->getPost('last_name',null));
            $email          = trim($this->getRequest()->getPost('email',null));
            $phoneNumber   = trim($this->getRequest()->getPost('phone_number',null));
            $password       = trim($this->getRequest()->getPost('password',null));
            $password2      = trim($this->getRequest()->getPost('password2',null));

        //Generate activation code
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
            'status' => 1,
            'activation_code' => $activationCode
        );

        $user = new Api_Model_Users();
        $user->createUser($userData);


        //Assign the global mobile number to login view
        //$this->view->phone_number = ($this->_userId == 101543 || $this->_tempPassword != md5('mtnpassword') ) ? '' : $this->_mobileNo; //No need to set the mobile number to common user

        if($isLowEndDevice):
            $this->_helper->viewRenderer('register-low-end');
        endif;
    }

    public function remoteLoginAction(){

        //error_reporting(E_ALL);
        //ini_set('display_errors', 1);

        $type = $this->_getParam('type', null);
        $this->view->loginType = $type;

        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');

        $this->_helper->viewRenderer->setRender('login');

        $this->view->chapId = $this->_chapId;
        $this->view->flashError = $this->_helper->flashMessenger->setNamespace('error')->getMessages();

        if ($this->getRequest()->isPost()) {
            $username = trim($this->getRequest()->getPost('username', ''));
            $mobileNo = trim($this->getRequest()->getPost('mobile_no', ''));
            $password = trim($this->getRequest()->getPost('password', ''));
            $type = trim($this->getRequest()->getPost('login_type', ''));

            $this->view->loginType = $type;

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
                        $partError = 'email';
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
                //Zend_Debug::dump($response);die();
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
                else
                {

                    //$this->view->error = "The username or password you entered is incorrect";
                    //$msgErrUnPwd = "The $partError or password you entered is incorrect";
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

        $this->view->flashMessages = $this->_flashMessenger->getMessages();

        if($this->_isLowEndDevice):
            $this->_helper->viewRenderer('login-low-end');
        endif;
    }
    
    private function __random_numbers($digits) {
    	$min = pow(10, $digits - 1);
    	$max = pow(10, $digits) - 1;
    	return mt_rand($min, $max);
    }


    public function googleLoginAction() {
        
    $googleAuth =  new Nexva_Google_GoogleAuth();
    
    //if code is empty then there is error 
    if(isset($_GET['code']))
        $googleAuth->authenticateGoogle();
    else 
       $this->_redirect('/user/login/error/true');
    
    // Once autheticated success then set the token and get the email 
    if(isset($_SESSION['token']))
        $googleAuth->setAccessToken();


    if ($googleAuth->getAccessToken())
    {
        $user = $googleAuth->getUserDetails();
        $userModel = new Partnermobile_Model_Users();
    	$userData['email'] = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
    	$userData['first_name'] = filter_var($user['given_name'], FILTER_SANITIZE_SPECIAL_CHARS);
    	$userData['last_name'] = filter_var($user['family_name'], FILTER_SANITIZE_SPECIAL_CHARS);
    	$url = $userModel->doLoginOpenId($userData, $this->_chapId);
  			        
    	$this->_redirect($url);
     }
    			        
		
    }

    function sendEmailValidationMessage($activationCode,$name,$userEmail) {

        $template = 'registration_ecarrot_user.phtml';
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject('Wellcome to eCarrot appstore.');
        $mailer->addTo($userEmail);
        $mailer->addCc(Zend_Registry::get('config')->nexva->application->content_admin->contact)        
                ->setMailVar("name", $name)
                ->setMailVar("activationCode", $activationCode);
        
        $mailer->setLayout("generic_mail_template");
        $mailer->sendHTMLMail($template);
    }

}