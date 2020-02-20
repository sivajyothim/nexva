<?php

class Pbo_UserController extends Zend_Controller_Action {

    /*public function init()
    {
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->initView();
    }*/

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
                $this->_redirect ( PBO_PROJECT_BASEPATH.'user/login' );
            } 
        }    
    
        $this->_helper->layout->setLayout('pbo/pbo_login');        
        
    }
    
    //Show the registered user under particular CHAP
    public function indexAction()
    {
        $this->_helper->layout->setLayout('pbo/pbo');
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        $modelUser = new Pbo_Model_User();
        $regUsers=0;
        $this->view->title = "Users : Registered Users / Developers"; 

        //Checks if the request is post and the passed parameter passed parameter from the view is regusers
        if($this->_request->isPost())
        {   
            $userSearchKey = trim($this->_getParam('regusers'));            
            $userFilter =  trim($this->_request->getPost('chkUserFilter'));
            $fromDate = trim($this->_request->getPost('fromDate'));
            $toDate = trim($this->_request->getPost('toDate'));
            if(!empty($fromDate) && empty($toDate)){ 
                $this->_helper->flashMessenger->setNamespace('error')->addMessage("Please select 'To' date.");
                $this->_redirect ('/user/index/from_date/'.$fromDate);
            }
            if(!empty($toDate) && empty($fromDate)){   
                $this->_helper->flashMessenger->setNamespace('error')->addMessage("Please select 'From' date.");
                 $this->_redirect ('/user/index/to_date/'.$toDate);
            }
        }
        else//display all user details    
        {            
            $userFilter =  trim($this->_request->user_filter);   
            $userSearchKey =  trim($this->_request->search_key);
            $fromDate = trim($this->_request->from_date);
            $toDate = trim($this->_request->to_date);            
        }
        
         //assigining the sql statement return value to a variable
        $regUsers = $modelUser->getSerachedrRegUsers($chapId, $userSearchKey, strtoupper($userFilter), $fromDate, $toDate);

        //print_r($regUsers['VERIFIED_ACCOUNT']);die();
        //Zend_Debug::dump($regUsers);die();

        $this->view->userFilterVal = $userFilter;
        $this->view->searchKey = $userSearchKey;   
        $this->view->fromDate = $fromDate;
        $this->view->toDate = $toDate;             
            
        //pagination       
        $this->view->showResults = FALSE;      
        
        $paginator = Zend_Paginator::factory($regUsers);            
        $count = count($paginator);
        
        if($count > 0)
        {   
            $this->view->showResults = TRUE;
            
            $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));
            $paginator->setItemCountPerPage(10);
            
            $this->view->regUsers = $paginator;
            unset($paginator);
        }
        

        //$this->view->headScript()->appendFile('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js');
        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'common/datepicker/css/ui.daterangepicker.css');

        //$this->view->flashMessages = $this->_flashMessenger->getMessages();
        $this->view->flashMessagesSuccess = $this->_helper->flashMessenger->setNamespace('success')->getMessages();
        $this->view->flashMessagesError = $this->_helper->flashMessenger->setNamespace('error')->getMessages();
    }
 
    //Login action
    public function loginAction() 
    {        
        //When form submitted
        if ($this->_request->isPost()) 
        {
            $username = trim($this->getRequest()->getPost('username', ''));
            $password = trim($this->getRequest()->getPost('password', ''));

            if ($username != '' && $password != '') 
            {
                //Check if email exists
                $user = new Model_User();
                $userRow = $user->fetchRow($user->select()->where('email = ?', $username));

                if ($userRow) 
                {
                    $userMeta = new Model_UserMeta();
                    $verified = $userMeta->getAttributeValue($userRow->id, 'VERIFIED_ACCOUNT');

                    //Check if account has verified
                    if ($verified != '0') 
                    {
                        $db = Zend_Registry::get('db');
                        $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'email', 'password', "MD5(?) AND status=1 AND (type='CHAP' OR type = 'SUPERCHAP'  OR type = 'SPECHAP')");
                        $authDbAdapter->setIdentity($username);
                        $authDbAdapter->setCredential($password);

                        $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);

                        //If validated successfully
                        if ($result->isValid()) 
                        {
                            Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));

                            //If remember me is clicked
                            if ($this->getRequest()->getPost('remember') == 1) 
                            {

                                //$cookie_username = new Zend_Http_Cookie('username',$this->getRequest()->getParam('username'), 'cp.nexva-v2-dev.com', time()+72000,'/');
                                unset($_COOKIE ['username']);
                                unset($_COOKIE ['password']);
                                $session_logout = new Zend_Session_Namespace('logoutaction');

                                $session_logout->logout = false;
                                setcookie('username', $this->getRequest()->getParam('username'), time() + (60 * 60 * 24 * 30));
                                //$cookie_password = new Zend_Http_Cookie('password',$this->getRequest()->getParam('password'), 'cp.nexva-v2-dev.com', time()+72000,'/');
                                setcookie('password', $this->getRequest()->getParam('password'), time() + (60 * 60 * 24 * 30));
                            }

                            //Save last login details
                            $auth = Zend_Auth::getInstance();
                            $user = new Cpbo_Model_UserMeta ( );

                            $user->setEntityId($auth->getIdentity()->id);

                            $user->LAST_LOGIN = date('Y-m-d h:i:s');

                            //Redirect to requested page
                            $session = new Zend_Session_Namespace('lastRequest');

//                            if (isset($session->lastRequestUri)) 
//                            {
//                                $newpage = $session->lastRequestUri;
//                                $session->lastRequestUri = NULL;
//                                $this->_redirect($newpage);
//                                return;
//                            }
                            
                            
                            //Get User the user Type and set it on the session                            
                            $userType = $auth->getIdentity()->type;
                            
                            
                            if($userType == 'CHAP')
                            {
                                 $userType =  'admin';
                            }
                            else if($userType == 'SUPERCHAP')
                            {                                
                                $userType =  'superAdmin';
                            }    
                            else if($userType == 'SPECHAP')
                            {
                            	$userType =  'speAdmin';
                            }
                            
                            $usersNs = new Zend_Session_NameSpace('members');
                            $usersNs->userType = $userType;
                                    
                            $this->_redirect(PBO_PROJECT_BASEPATH);
                        } 
                        else 
                        {                           
                            $this->view->error = "Username or password is incorrect.";
                        }
                    } 
                    else 
                    {
                        $this->view->error = "This account has not been verified.";
                    }
                } 
                else 
                {
                   
                    $this->view->error = "Please enter valid username.";
                }
            } 
            else 
            {                
                $this->view->error = "Please enter your username and password.";
            }
        }
    }
    
    
    //Log out action
    public function logoutAction() 
    {
        $this->_helper->viewRenderer->setNoRender(true);
        Zend_Auth::getInstance()->clearIdentity();
        $session_logout = new Zend_Session_Namespace('logoutaction');
        $session_logout->logout = true;
        Zend_Session::destroy();
        $this->_redirect(PBO_PROJECT_BASEPATH.'user/login');
    }

    //Resend verification SMS to USERS
    function resendVerificationAction()
    {    
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $userId = $this->_getParam('user_id');
               
        if($userId)
        {
            $activationCode = substr(md5(uniqid(rand(), true)), 5,8);
            $status = 0;

            $userModel = new Api_Model_Users();
            $userDetails = $userModel->getUserById($userId);

            $mobileNumber = $userDetails->mobile_no;
            $email = $userDetails->email;
            $chapId = Zend_Auth::getInstance()->getIdentity()->id;
            
            //update activation code in the DB
            $userModel->updateActivationCode($userId, $activationCode, $status);            
            
            //Re-send verification SMS
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);

            $pgType = $pgDetails->gateway_id;

            $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

            $message = 'Please use this verification code '.$activationCode.' to complete your registration. "MTN NIGERIA APP STORE".';

            $result = $pgClass->sendSms($mobileNumber, $message, $chapId);
            
            $this->_helper->flashMessenger()->setNamespace('success')->addMessage('Verification Code Successfully Sent to the Mobile Number "'.$mobileNumber.'", registered with "'.$email.'".');
            $this->_redirect('user/index');
            
        }
        else
        {
            $this->_helper->flashMessenger()->setNamespace('error')->addMessage('Error Sending SMS.');
            $this->_redirect('user/index');
        }        
    }
    
    //Serialize user emails for ajax call in filter app section
    public function getSerialisedUserEmailsAction(){
        
        $searchString = $this->_request->getParam('q');
        $fromDate = $this->_request->getParam('fromDate');
        $toDate = $this->_request->getParam('toDate');
        $userFilter = $this->_request->getParam('chkUserFilter');
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        
        $modelUser = new Pbo_Model_User();
        $userMobile = $modelUser->getRegUserMobile($chapId, $searchString, strtoupper($userFilter), $fromDate, $toDate);
        
        echo json_encode($userMobile);
        die();
    }
    
    public function deleteUserAction()
    {
        $userId = $this->_request->getParam('id');
        
        $userModel = new Model_User();
        $userRow = $userModel->getUserById($userId);
        
        if($userRow['type'] != 'USER'){
            $this->_helper->flashMessenger()->setNamespace('error')->addMessage('Sorry, You are not authorized to delete developer accounts.');
        } 
        else {
            $userModel->deleteUser($userId);
            $this->_helper->flashMessenger()->setNamespace('success')->addMessage('User "'.$userRow['email'].'" successfully deleted.');
        }
        
        $this->_redirect('/user/');  
    }


    /* generates the same result set as the indexAction for generate excel-report
     * @param - email
     * @param - type
     * @param - from
     * @param - to
     * @return excel file generate through html header parameters
     */

    public function excelReportAction(){

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $userSearchKey = trim($this->_getParam('email'));
        $userFilter =  $this->_getParam('type');
        $fromDate = $this->_getParam('from');
        $toDate = $this->_getParam('to');

        $modelUser = new Pbo_Model_User();
        $regUsers = $modelUser->getSerachedrRegUsersForReport($chapId, $userSearchKey, strtoupper($userFilter), $fromDate, $toDate);

        $data = "";
        $line = "ID"."\t"."E-Mail"."\t"."Mobile"."\t"."Type"."\t"."Status"."\t"."\n";

        foreach($regUsers as $regUser){
            $line.= $regUser->id."\t";
            $line.= $regUser->email."\t";
            $line.= $regUser->mobile_no."\t";
            $line.= $regUser->type."\t";
            if($regUser->status){
                $line.= 'Verified'."\t";
            }else{
                $line.= 'Not-Verified'."\t";
            }
            $line .= "\n";
        }

        $data .= trim($line) . "\n";
        $data = str_replace("\r", "", $data);

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename = registered-users.xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        print $data;
        exit;
    }
    
}