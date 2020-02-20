<?php

class Partner_UserController extends Nexva_Controller_Action_Partner_MasterController {
    
    public function registerAction(){
        
    }
    
    public function googleLoginAction() {
    
    	$googleAuth =  new Nexva_Google_GoogleAuth();
    
    	//if code is empty then there is error
    	if(isset($_GET['code']))
    		$googleAuth->authenticateGoogle();
    	else {
    		$this->_helper->flashMessenger->setNamespace('error')->addMessage('An error occurred, please try again.');
            $this->_redirect('/info/login/error/true');

    	}
    
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
    
    public function fbLogincvAction() {
    
        $this->_redirect('http://unitelapps.nexva.com');
    
    }
}