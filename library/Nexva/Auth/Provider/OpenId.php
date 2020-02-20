<?php
/**
 * 
 * Base class for openID authentication providers
 * @author John
 *
 */
class Nexva_Auth_Provider_OpenId {
    
    protected  function getScheme() {
        $scheme = 'http';
        if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
            $scheme .= 's';
        }
        return $scheme;
    }
    
    protected  function getReturnTo() {
        return sprintf("%s://%s/user/openid",
                       $this->getScheme(), $_SERVER['SERVER_NAME']);
    }
    
    //recently added - 23/04/2012
    protected  function getReturnUrl() {
        return sprintf("%s://%s/download/postback",
                       $this->getScheme(), $_SERVER['SERVER_NAME']);
    }
    
    
    protected function getTrustRoot() {
        return sprintf("%s://%s",
                       $this->getScheme(), $_SERVER['SERVER_NAME']);
    }
    
    /**
     * Throws an auth wrapped exception
     * @param $message
     */
    function throwError($message = "") {
        $ex = new Nexva_Auth_Exception($message);
        throw $ex;
    }
    
    /**
     * Parses the response given by an Open ID supplier and returns the required fields
     * @param $params
     */
    static function parse($params, $required = array('email')) {
        //right now we'll just extract email, firstname and last name
        
        //first see if they've cancelled the access request
        if (isset($params['openid_mode']) && $params['openid_mode'] == 'cancel') {
            //don't throw an exception, just return
            return false;
        }
        
        
        $data   = array(); 
        $data['email']      = empty($params['openid_ext1_value_email']) ? '' : $params['openid_ext1_value_email'];
        $data['first_name'] = empty($params['openid_ext1_value_firstname']) ? '': $params['openid_ext1_value_firstname'];
        $data['last_name']  = empty($params['openid_ext1_value_lastname']) ? '': $params['openid_ext1_value_lastname'];

        $missing    = array();
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $missing[]  = $field;
            }
        }
        
        /**
         * Throwing and exception here because we were expecting data but it never came through
         */
        if (!empty($missing)) { 
            self::throwError('Could not retrieve ' . implode(',', $required) . ' fields . PARAMS = ' . print_r($params, true));
        }
        
        return $data;
    }
}