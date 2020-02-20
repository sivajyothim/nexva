<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Nexva_PaymentGateway_PayThru {
    protected $api_endpoint = 'https://api.paythru.com';
    protected $paythru_vars = array();

    public function  __construct($api_key, $api_password) {
        $this->AddVar("api_key", $api_key);
        $this->AddVar("api_password", $api_password);
    }
    
    protected function SetServiceEndPoint($url) {
        $this->api_endpoint = $url;

    }

    protected function AddVar($name, $value) {
        $this->paythru_vars[$name] = $value;
    }

    public function Prepare($vars = array()) {
        foreach($vars as $key => $value) {
            $this->AddVar($key, $value);
        }
    }

    public function Execute() {
        $curl_connection = curl_init($this->api_endpoint.'/gettoken');
        //die($this->api_endpoint.'/gettoken');

        curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_connection, CURLOPT_POST, 1);
        curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $this->paythru_vars);

        $result = curl_exec($curl_connection);
//        die($result);

        curl_close($curl_connection);
        preg_match("/<redemption_url>(.*)<\/redemption_url>/", $result, $matches);
        $redemptionurl = $matches[1];
//        die("RURL: ". $redemptionurl);
        header("location:" . $redemptionurl);
        exit;


    }
}

?>
