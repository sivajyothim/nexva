<?php

class Chap_UserController extends Nexva_Controller_Action_Chap_MasterController {

  protected $_flashMessenger = null;
	
  /* Initialize action controller here */

  public function init() {
    // include Ketchup libs
    $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages.js');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js');
    // adding admin JS file
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/js/admin.js');
    
    $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
    $this->view->flashMessenger = $this->_flashMessenger;
  }

  public function indexAction() {
    // action body
  }

  public function loginAction() {
  	
  		$this->view->tab = 'tab-login';
  		
  	    $username = $this->_request->getParam('username');
        $password = $this->_request->getParam('password');
  		
        
        if (isset($_COOKIE['username']) and isset($_COOKIE['password'])) {
            $session_logout = new Zend_Session_Namespace('logoutaction');
            
            if ($session_logout->logout != true) {
            	
                $this->__doLogin($_COOKIE['username'], stripcslashes($_COOKIE['password']));
            }
        }
  	
        if ($this->_request->isPost()) {
        	
        	switch ($this->_request->operation) {
				
				case 'login' :
					$this->__doLogin($username, $password);
					break;
				
				case 'forgot_password' :
					$this->__doForgotPassword(urldecode($this->_request->email));
					break;
				
				default :
					throw new Zend_Exception ( "Unknown operation attempted: " . $this->_request->operation);
			}
        
    }
    
    			
	$captcha = new Zend_Service_ReCaptcha(Zend_Registry::get('config')->recaptcha->public_key, Zend_Registry::get('config')->recaptcha->private_key);

    $captcha->setOption('theme', "clean");
    $this->view->recaptcha = $captcha->getHTML();

    $this->_helper->layout->setLayout('chap/chap_login');
  }
  
  
  public function accountAction() {
  	
  	 $user = new Cpbo_Model_UserMeta();
     $user->setEntityId(Zend_Auth::getInstance()->getIdentity()->id);
     
     
        if ($this->getRequest()->isPost() and $this->_request->formId == 'technical') {
        
        	$user->CHAP_TECHNICAL_CONTACT_NAME = $this->_request->technical_contact_name;
  	 		$user->CHAP_TECHNICAL_CONTACT_EMAIL = $this->_request->technical_contact_email;
  	 		$user->CHAP_TECHNICAL_CONTACT_PHONE = $this->_request->technical_contact_phone;
  	 		$user->reloadCache(Zend_Auth::getInstance()->getIdentity()->id);
  	 		  //
            $this->_flashMessenger->addMessage(array('info' => 'Successfully saved technical user contacts.'));
        	$this->_redirect('/user/account/#technical');
        }
        
        if ($this->getRequest()->isPost() and $this->_request->formId == 'admin') {
        	
            $user->CHAP_ADMIN_CONTACT_NAME = $this->_request->admin_contact_name;
  	 		$user->CHAP_ADMIN_CONTACT_EMAIL = $this->_request->admin_contact_email;
  	 		$user->CHAP_ADMIN_CONTACT_PHONE = $this->_request->admin_contact_phone;
  	 		$user->reloadCache(Zend_Auth::getInstance()->getIdentity()->id);
  	 		  //
            $this->_flashMessenger->addMessage(array('info' => 'Successfully saved admin user contacts.'));
  	 		$this->_redirect('/user/account/#admin');
        }
        

      
        $technicalUserValue = array(
        'technical_contact_name' => $user->CHAP_TECHNICAL_CONTACT_NAME, 
        'technical_contact_email' => $user->CHAP_TECHNICAL_CONTACT_EMAIL, 
        'technical_contact_phone' => $user->CHAP_TECHNICAL_CONTACT_PHONE
        );
        
        $adminUserValue = array(
        'admin_contact_name' => $user->CHAP_ADMIN_CONTACT_NAME, 
        'admin_contact_email' => $user->CHAP_ADMIN_CONTACT_EMAIL, 
        'admin_contact_phone' => $user->CHAP_ADMIN_CONTACT_PHONE
        );	


  	   // technical user info 
  	    $technicalUser = new Chap_Form_TechnicalUser();
        $technicalUser->setAction($this->getFrontController()->getBaseUrl() . '/user/account');
        $technicalUser->populate($technicalUserValue);
        $this->view->technicalUser = $technicalUser;
       
        $adminUser = new Chap_Form_AdminUser();
        $adminUser->setAction($this->getFrontController()->getBaseUrl() . '/user/account');
        $adminUser->populate($adminUserValue);
        $this->view->adminUser = $adminUser;

  }
  
  
    function logoutAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        Zend_Auth::getInstance()->clearIdentity();
        $session_logout = new Zend_Session_Namespace('logoutaction');
        $session_logout->logout = true;
        $this->_redirect('/user/login');
    }
  
    
 	protected function __doForgotPassword($email) {
		
		$user = new Cpbo_Model_User ( );
		$this->view->tab = "tab-password";
		$this->view->email = $email;
		$captcha = new Zend_Service_ReCaptcha(Zend_Registry::get('config')->recaptcha->public_key, Zend_Registry::get('config')->recaptcha->private_key);

		
		try {
			
			if (isset ( $this->_request->recaptcha_challenge_field ) and ($this->_request->recaptcha_response_field)) {
				$resp = $captcha->verify ( $this->_request->recaptcha_challenge_field, $this->_request->recaptcha_response_field );
				if (! $resp->isValid ()) {
					
					$this->view->error = "The security key wasn't entered correctly. Please try again.";
					return;
				} else {

					$user->sendPasswordResetMail ( $email );
		
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
	
	protected function __doLogin($username, $password)	{
		

            
            try {
            	
                $db = Zend_Registry::get('db');
                $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'email', 'password', "MD5(?) and status=1 and type='CHAP'");
                $authDbAdapter->setIdentity($username)->setCredential($password);
                $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);
                
                if ($result->isValid()) {
                    //auth sucess
                    Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));

                	if ($this->getRequest ()->getParam ( 'remember' ) == 1) {
						
						//$cookie_username = new Zend_Http_Cookie('username',$this->getRequest()->getParam('username'), 'cp.nexva-v2-dev.com', time()+72000,'/');
						unset ( $_COOKIE ['username'] );
						unset ( $_COOKIE ['password'] );
						$session_logout = new Zend_Session_Namespace ( 'logoutaction' );
						
						$session_logout->logout = false;
						setcookie ( 'username', $username, time () + (60 * 60 * 24 * 30) );
						setcookie ( 'password', $password, time () + (60 * 60 * 24 * 30) );
					}

                    //Save last login details
                    
					$user = new Cpbo_Model_UserMeta();
					$user->setEntityId(Zend_Auth::getInstance()->getIdentity()->id);
					$user->LAST_LOGIN = date ( 'Y-m-d h:i:s' );
                    
                    $session = new Zend_Session_Namespace('lastRequest');
                    if (isset($session->lastRequestUri)) {
                        $newpage = $session->lastRequestUri;
                        $session->lastRequestUri = NULL;
                        $this->_redirect($newpage);
                        return;
                    }
                    
                    $this->_redirect('/');
                }
            } catch (Exception $e) { }

			$this->view->error = "The username/password combination you supplied is not valid.";
		
	}
	
	public function resetpasswordAction()
    {

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
        if (! $result)
            $this->view->error = "The password reset request is no longer valid.";
        $this->view->valid_request = $result;
        
        if ($this->_request->isPost()) { //everything is good - reset the password and redirect
          
        	if(empty($this->_request->password) or  empty($this->_request->confirm) or ($this->_request->password != $this->_request->confirm) )
        		$this->view->error = "The passwords you supplied did not match.";
        	else	{	
        		$user->resetPassword($userRow->id, $this->_request->password);
            	$this->_redirect("/user/login");
        	}
          
        	    
          
        	
        	
        }
        $this->_helper->layout->setLayout('chap/chap_login');
    }
	
    
    public function impersonateAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $timestamp = $this->_request->timestamp;
        if( strtotime("now") > $timestamp ) die("Invalid Request: Your request is stale. Please try again."); //request timeout - we keep it to <=10

        $userModel  = new Model_User();
        $user       = $userModel->fetchRow('id='.$this->_request->id);

        if( !$user ) throw new Zend_Exception('Invalid request: CP not found');

        $hash = md5($user->id.$user->password.$this->_request->id.Zend_Registry::get('config')->nexva->application->salt.$timestamp);
        
        if( $hash != $this->_request->hash) die("Invalid request: Access denied."); //couldn't verfiy request sig - most likely someone tried to tamper the URL

        //fix the session and goto dashboard

        $obj = new stdClass();
        $obj->id    = $user->id;
        $obj->email = $user->email;
        
        Zend_Auth::getInstance ()->getStorage ()->write($obj);
        $this->_redirect('/');
                     
    }
    
    
    public function registerdUsersAction()
    {               
        $this->_helper->layout->setLayout('chap/chap');
        $isUsersEmpty = TRUE;
        
        
        $userId = Zend_Auth::getInstance()->getIdentity()->id;    
       
        $userModel = new Model_User();        
        $registerdUsers = $userModel->getRegstrationsForChaps($userId);
         
        
        //Zend_Debug::dump($registerdUsers);die();
       
        
        $pagination = Zend_Paginator::factory($registerdUsers);
        
        if (count($pagination) > 0) 
        {
            $isUsersEmpty = FALSE;
            
            $pagination->setCurrentPageNumber($this->_request->getParam('registered_users', 1));
            $pagination->setItemCountPerPage(30);

            $this->view->isUsersEmpty = $isUsersEmpty;        
            $this->view->registerdUsers = $pagination;
            unset($pagination); 
        }         
        
        
               
        
        //$this->view->registerdUsers = $registerdUsers;
    }
}