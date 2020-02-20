<?php
/**
 * Created by PhpStorm.
 * User: viraj
 * Date: 7/30/14
 * Time: 2:33 PM
 */

//include google api files
require_once  APPLICATION_PATH.'/../public/vendors/openid/google_auth/src/Google_Client.php';
require_once  APPLICATION_PATH.'/../public/vendors/openid/google_auth/src/contrib/Google_Oauth2Service.php';

class Nexva_Google_GoogleAuthQelasy {

    private $_gClient;
    private $_googleGauthV2;

    public function __construct() {

        $google_client_id 		= '463292511766-afql0aaq5fps91tlekminhgiuigc206q.apps.googleusercontent.com';
        $google_client_secret 	= 'y21pMlBfDaxln6QnmgvE6uKu';
        $this->google_redirect_url 	= 'http://dev.qelasy.com/user/google-login'; //path to your script
        $google_developer_key 	= '463292511766-afql0aaq5fps91tlekminhgiuigc206q@developer.gserviceaccount.com';

        $this->_gClient = new Google_Client();
        $this->_gClient->setAccessType('online');
        $this->_gClient->setApplicationName('Qelasy Store');
        $this->_gClient->setClientId($google_client_id);
        $this->_gClient->setClientSecret($google_client_secret);
        $this->_gClient->setRedirectUri($this->google_redirect_url);
        $this->_gClient->setState('online');
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