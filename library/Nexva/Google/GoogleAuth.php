<?php

//include google api files
require_once  APPLICATION_PATH.'/../public/vendors/openid/google_auth/src/Google_Client.php';
require_once  APPLICATION_PATH.'/../public/vendors/openid/google_auth/src/contrib/Google_Oauth2Service.php';

/**
 *
 */
class Nexva_Google_GoogleAuth {

    private $_gClient;
    private $_googleGauthV2;

    public function __construct() {
        
        $google_client_id 		= '284226356142-s2bi39h2kgfvbl2ef8pb21eh8742fpck.apps.googleusercontent.com';
        $google_client_secret 	= 'IRz7CzTuq22em6gJq3-PoOEx';
        $this->google_redirect_url 	= 'http://unitelapps.nexva.com/user/google-login/'; //path to your script
        $google_developer_key 	= '284226356142-s2bi39h2kgfvbl2ef8pb21eh8742fpck@developer.gserviceaccount.com';
        
        $this->_gClient = new Google_Client();
        $this->_gClient->setAccessType('online');
        $this->_gClient->setApplicationName('Unitel Appstore');
        $this->_gClient->setClientId($google_client_id);
        $this->_gClient->setClientSecret($google_client_secret);
        $this->_gClient->setRedirectUri($this->google_redirect_url);
        $this->_gClient->setState('online');
        //$this->_gClient->setScopes(array('https://www.googleapis.com/auth/plus.login', 'https://www.googleapis.com/auth/plus.profile.emails.read', 'https://www.googleapis.com/auth/plus.me'));
        // Deprecated 
        //$this->_gClient->setScopes(array('https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/userinfo.profile'));
        // $this->_gClient->setScopes(array('https://www.googleapis.com/auth/plus.login','https://www.googleapis.com/auth/plus.profile.emails.read'));
        $this->_gClient->setDeveloperKey($google_developer_key);
        $this->_googleGauthV2 = new Google_Oauth2Service($this->_gClient);
        
    }
    
    public function revokeToken() {
        return $this->_gClient->revokeToken();
    }
    
    public function getAccessToken() {
    	return $this->_gClient->getAccessToken();
    }
    
    public function setAccessToken() {
    	return $this->_gClient->setAccessToken($_SESSION['token']);
    }

    public function createAuthUrl() {
    	return $this->_gClient->createAuthUrl();
    }

    public function authenticate($token) {
    	return $this->_gClient->authenticate($token);
    }
    
    public function getUserDetails() {
        if($this->_gClient->getAccessToken())
    	    return  $this->_googleGauthV2->userinfo->get();
    }
    
    public function authenticateGoogle() {
        $this->authenticate($_GET['code']);
        $_SESSION['token'] =  $this->getAccessToken();
        header('Location: ' . filter_var($this->google_redirect_url, FILTER_SANITIZE_URL));
    }
    
 

}

?>