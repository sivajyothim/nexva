<?php

/**
 *
 * @copyright   neXva.com
 * @author      Chathura 
 * @package     api inaap
 * @version     1
 */
class Nexva_PaymentGateway_Adapter_Paythru_PayThru implements Nexva_PaymentGateway_Interface_Interface {
	
	private static $__instance;
	protected static $_payThru = null;
	
	private function __clone() {
	
	}
	
	public static function getInstance($sandBox = 0) {
		
		if (! isset ( self::$__instance )) {
			$class = __CLASS__;
			self::$__instance = new $class ( $sandBox );
		}
		
		return self::$__instance;
	}
	
	public function getAdapterName() {
		return "PayThru";
	}
	
	protected $api_endpoint = 'https://api.paythru.com';
	protected $paythru_vars = array ();
	
	public function __construct($sandBox) {
		
		$config = Zend_Registry::get ( 'config' );
		
		if ($sandBox == 0) {
			
			$api_key = $config->paythru->api->key;
			$api_password = $config->paythru->api->password;
		
		} else {
			
			$api_key = $config->paythru->sandbox->api->key;
			$api_password = $config->paythru->sandbox->api->password;
		}
		
		$this->addVar ( "api_key", $api_key );
		$this->addVar ( "api_password", $api_password );
	
	}
	
	public function setServiceEndPoint($url) {
		$this->api_endpoint = $url;
	}
	
	public function addVar($name, $value) {
		$this->paythru_vars [$name] = $value;
	}
	
	public function prepare($vars = array()) {
		foreach ( $vars as $key => $value ) {
			$this->addVar ( $key, $value );
		}
	}
	
	public function execute() {
		$curl_connection = curl_init ( $this->api_endpoint . '/gettoken' );
		
		// for debugging die($this->api_endpoint.'/gettoken');
		

		curl_setopt ( $curl_connection, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl_connection, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $curl_connection, CURLOPT_POST, 1 );
		curl_setopt ( $curl_connection, CURLOPT_POSTFIELDS, $this->paythru_vars );
		
		$result = curl_exec ( $curl_connection );
		
		// for debugging    die($result);
		

		curl_close ( $curl_connection );
		preg_match ( "/<redemption_url>(.*)<\/redemption_url>/", $result, $matches );
		$redemptionurl = $matches [1];
		// for debugging  die("RURL: ". $redemptionurl);
		header ( "location:" . $redemptionurl );
		exit ();
	}

}

?>
