<?php
/**
 * Factory class to get providers and resolve dependencies
 * @author John
 *
 */
class Nexva_Auth_AuthenticateFactory {
    
    /**
     * Gets the proper provider
     * @param unknown_type $type
     * @throws Exception
     * @return Nexva_Auth_Provider_Google
     */
    public static function getAuth($type = null, $opts = array()) {
        switch ($type) {
            case 'google' :
                $consumer   = self::getOpenIdDependencies();
                $openIdUrl  = empty($opts['openIdUrl']) ? '' : $opts['openIdUrl'];
                return new Nexva_Auth_Provider_Google($consumer, $openIdUrl);
                break;
        }
        
        //no handler, throw an error
        throw new Exception("Authenitcation handler not found");
    }
    
    /**
     * 
     * Resolve dependencies for OpenID
     */
    static function getOpenIdDependencies() {
        $path   = dirname(__FILE__ );
        set_include_path(get_include_path() . PATH_SEPARATOR .  $path);
        /**
         * Require the OpenID consumer code.
         */
        require_once "Auth/OpenID/Consumer.php";
    
        /**
         * Require the "file store" module, which we'll need to store
         * OpenID information.
         */
        require_once "Auth/OpenID/FileStore.php";
    
        /**
         * Require the Simple Registration extension API.
         */
        require_once "Auth/OpenID/SReg.php";
        
        /**
         * Require the Attribute extension API
         */
        require_once "Auth/OpenID/AX.php";
        
    
        /**
         * Require the PAPE extension module.
         */
        require_once "Auth/OpenID/PAPE.php";

        $tmp    = Zend_Registry::get('config')->nexva->application->tempDirectory;
        $store      = new Auth_OpenID_FileStore($tmp);
        $consumer   = new Auth_OpenID_Consumer($store);
        return $consumer;
    }
}