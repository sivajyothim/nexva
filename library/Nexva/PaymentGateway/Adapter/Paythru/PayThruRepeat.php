<?php
/**
 *
 * @copyright   neXva.com
 * @author      chathura 
 * @package     Admin
 * @version     $Id$
 */
class Nexva_PaymentGateway_Adapter_Paythru_PayThruRepeat implements Nexva_PaymentGateway_Interface_Interface {
	
    private static $__instance;
    protected static $_payThru = null;
    
    private function __clone() {
    }
    
    public static function getInstance() {
        if (! isset ( self::$__instance )) {
            $class = __CLASS__;
            self::$__instance = new $class ( );
        }
        
        return self::$__instance;
    }
    
    public function getAdapterName() {
        return "PayThruRepeat";
    }
	
    protected $api_endpoint = 'https://api.paythru.com';
    protected $paythru_vars = array();

    public function  __construct() {
        
    	$config = Zend_Registry::get ( 'config' );
        $api_key = $config->paythru->api->key;
        $api_password = $config->paythru->api->password;

        $this->addVar ( "api_key", $api_key );
        $this->addVar ( "api_password", $api_password );
    }
    
    public function setServiceEndPoint($url) {
        $this->api_endpoint = $url;

    }

    public function addVar($name, $value) {
        $this->paythru_vars[$name] = $value;
    }

    public function prepare($vars = array()) {
        foreach($vars as $key => $value) {
            $this->addVar($key, $value);
        }
    }

    public function execute() {
        $curl_connection = curl_init($this->api_endpoint.'/repeat');

        curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_connection, CURLOPT_POST, 1);
        curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $this->paythru_vars);

        $result = curl_exec($curl_connection);
        curl_close($curl_connection);
        
        //Zend_Debug::dump($result);
       
        $xml = simplexml_load_string($result);
        
        $status['type'] = $xml->transaction->type;
        $status['amount'] = $xml->transaction->amount;
        $status['prev_trans_key'] = $xml->transaction->prev_trans_key;
        $status['class'] = $xml->transaction->class;
        $status['system_action'] = $xml->transaction->system_action;
        $status['status'] = $xml->transaction->status;
        $status['trans_key'] = $xml->transaction->trans_key;
        $status['auth_code'] = $xml->transaction->auth_code;
        $status['errorcode'] = $xml->errorcode;
        
        return $status;

  


    }
}

?>
