<?php

include_once("facebook-php-sdk-668c61a/src/facebook.php");

/**
 *
 */
class Nexva_Facebook_FacebookConnect extends Facebook {

  //Nexva_Facebook_FacebookConnect is primarily a proxy into facebook.php with some custom helper functions

    public function __construct($interface = 'web', $appId = null, $secret = null) {
        $config     = Zend_Registry::get('config');
        $appId      = ($appId == null) ? $config->$interface->facebook->connect->appid : $appId;
        $secret     = ($secret == null) ? $config->$interface->facebook->connect->secret : $secret;
        parent::__construct(
                array(
                  'appId' => $appId,
                  'secret' => $secret,
                  'cookie' => true
                )
        );
    }

  /**
   * Tries it's best to figure out if the user authorized the app or not. AFAICS, FB's PHP SDK doesn't give us a way to check this.
   *
   * The problem with calling api('/me') is that it would always return the public profile info of the user regardless of authorization state.
   * Since we are totally reliant on 'email' to do session management, absense of this element would indicate the user declined to authorize us.
   *
   *
   * @return boolean
   */
  public function hasUserAuthorizedApp() {
    //if( !$this->getSession() ) return false;
    $user = $this->getUser();

    

    return $user != null;



    
  }

  /**
   * Returns information about the currently logged in user
   * 
   * @return mixed Returns FALSE if the user is not logged into FB else returns an array with user details
   */
  public function getLoggedFbUser() {
    if (!$this->hasUserAuthorizedApp())
      return false;
    return $this->api('/me');
  }

}

?>