<?php

class Nexva_LocationServices_Adapter_MaxMind implements Nexva_LocationServices_Interface_Interface {

  protected $_locationDetils;
  protected $mp_useragent = '';
  protected $mp_ip = '';

  public function __construct($adapter = null) {
    include_once( './vendors/MaxMind/geoip.inc' );
    $gi = geoip_open("./vendors/MaxMind/GeoIP.dat", GEOIP_STANDARD);
    $this->mp_ip = $_SERVER['REMOTE_ADDR'];
    $this->mp_useragent = $_SERVER["HTTP_USER_AGENT"];
//    echo geoip_country_code_by_addr($gi, "24.24.24.24") . "\t" .
//    geoip_country_name_by_addr($gi, "24.24.24.24") . "\n";
//    echo geoip_country_code_by_addr($gi, "80.24.24.24") . "\t" .
//    geoip_country_name_by_addr($gi, "80.24.24.24") . "\n";
//
//
    $this->_getRealIpUa();
    $this->_locationDetils['country'] = geoip_country_name_by_addr($gi, $this->mp_ip);
    $this->_locationDetils['country_code'] = geoip_country_code_by_addr($gi, $this->mp_ip);
    geoip_close($gi);
//    return $locationDetils;
  }

  public function getLocationDetails() {
    return $this->_locationDetils;
  }

  public function getCountry() {
    return $this->_locationDetils['country'];
  }

  public function getCountryCode() {
    return $this->_locationDetils['country_code'];
  }

  private function _getRealIpUa() {
    $useragent = $this->mp_useragent;
    $headers = apache_request_headers();

    if ((eregi("Opera Mini", $useragent) || eregi("OperaMini", $useragent) || eregi("Opera", $useragent)) && $headers != "") {
      //Real IP detection
      if (array_key_exists('x-forwarded-for', $headers)) {
        $mp_real_client_ip = $headers['x-forwarded-for'];
        $mp_real_client_ip_tab = explode(",", $mp_real_client_ip);
        // overload the ip
        $this->mp_ip = $mp_real_client_ip_tab[0];
      }

      //Real UserAgent detection
      if (array_key_exists('X-OperaMini-Phone-UA', $headers)) {
        $useragent = "[Opera] " . $headers['X-OperaMini-Phone-UA'];
      } elseif (array_key_exists('X-OperaMini-Phone', $headers)) {
        // overload the UA
        $this->mp_useragent = "[Opera] " . $headers['X-OperaMini-Phone'];
      }
    }

    if (eregi("Google Wireless Transcoder", $useragent) && $headers != "") {
      //Real IP detection
      if (array_key_exists('X-Forwarded-For', $headers)) {
        $mp_real_client_ip = $headers['X-Forwarded-For'];
        $mp_real_client_ip_tab = explode(",", $mp_real_client_ip);
        // overload the ip
        $this->mp_ip = $mp_real_client_ip_tab[0];
      }
      //Real UserAgent detection
      if (array_key_exists('X-Original-User-Agent', $headers)) {
        // overload the UA
        $this->mp_useragent = "[GWT] " . $headers['X-Original-User-Agent'];
      }
    }
  }

}

?>
