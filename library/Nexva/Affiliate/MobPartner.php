<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
// MobPartner Tracking Code
// Language: PHP
// Version: 20100201
// Contact us at : support@mobpartner.com
// This webservice has to be call when the "action" is validated on your side (registration, subscription, deposit...)

class Nexva_Affiliate_MobPartner {

// When MobPartner will finish all tests, please remove that line
  protected $mp_testprod = '';
// Type of call ("http" or "pixel")
  protected $mp_call_type = "http";
//////////////////
// Note: The traffic will be send to your mobile site like this : http://www.yoursite.mobi/.../?mobtag=[RANDOM_PARAMETER]
// You have to keep this "mobtag" parameter until the payment or validation page, where you will call this code
// The "mobtag" parameter can be another name, just let us know
//////////////////
// Remplace by the "mobtag" parameter from your cookie or url
  protected $mp_mobtag;
// Remplace by your country variable - Format: 2 letters. Example: US
  protected $mp_country = 'FR';
// Remplace by the right action id
// Action 244 : Each new download
  protected $mp_campaign_action_id = 244;
// Remplace by the product name (optional)
  protected $mp_product_name = 'neXva App Store';
// Remplace by the product ID (optional)
  protected $mp_product_id;
// Remplace by your unique order id
  protected $mp_order_id;
  // get the real ip if not there
  protected $mp_ip;
  // get the user agent
  protected $mp_useragent;


  public function __construct($mp_mobtag, $mp_product_id) {
    $this->mp_product_id = $mp_product_id;
    $this->mp_mobtag = $mp_mobtag;
    // add order ID
    $unique = md5(uniqid());  // 32 characters long
    $this->mp_order_id = $unique;
    
    /*
     Since Mobpartner says they would detect the country, we will not be sending this anymore.
    // get the location
    $geoLocation = new Nexva_LocationServices_Factory();
    $countryCode = $geoLocation->getCountryCode();
    $this->mp_country = $countryCode;
     */
    $this->mp_country = '';
    $this->mp_ip = $_SERVER['REMOTE_ADDR'];
    $this->mp_useragent = $_SERVER["HTTP_USER_AGENT"];
    $test = Zend_Registry::get("config")->nexva->affiliate->mobopen->test;
    $this->mp_testprod = ($test) ? '_test' : '';
  }

  public function track() {
    //////////////////////////////
    // DO NOT EDIT BELOW THIS LINE
    $mp_country = urlencode($this->mp_country);
    $mp_product_id = urlencode($this->mp_product_id);
    $mp_product_name = urlencode($this->mp_product_name);
    $mp_order_id = urlencode($this->mp_order_id);
    // get the real IP and UA if UA is Opera
    $this->_getRealIpUa();
    $mp_useragent = urlencode($this->mp_useragent);
    $mp_ip = urlencode($this->mp_ip);
    $mp_headers = addslashes(urlencode(serialize(apache_request_headers())));
    $mp_page = urlencode(sprintf('http%s://%s%s', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == TRUE ? 's' : ''), $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']));
    $mp_session_id = session_id();

    $mp_url = "http://ws.mobpartner.com/v2/ws" . $this->mp_testprod . ".php";
    $mp_url .= "?mobtag=" . $this->mp_mobtag;
    $mp_url .= "&campaign_action_id=" . $this->mp_campaign_action_id;
    $mp_url .= "&useragent=" . $mp_useragent;
    $mp_url .= "&ip=" . $mp_ip;
    $mp_url .= "&page=" . $mp_page;
    $mp_url .= "&call_type=" . $this->mp_call_type;
    if ($mp_country != "")
      $mp_url .= "&country=" . $mp_country;
    if ($mp_product_id != "")
      $mp_url .= "&product_id=" . $mp_product_id;
    if ($mp_product_name != "")
      $mp_url .= "&product_name=" . $mp_product_name;
    if ($mp_order_id != "")
      $mp_url .= "&order_id=" . $mp_order_id;
    if ($mp_campaign_price_value != "")
      $mp_url .= "&campaign_price_value=" . $mp_campaign_price_value;
    if ($mp_session_id != "")
      $mp_url .= "&session_id=" . $mp_session_id;
    if ($mp_data != "")
      $mp_url .= "&data=" . $mp_data;
    if ($mp_timestamp != "")
      $mp_url .= "&timestamp=" . $mp_timestamp;
    if ($mp_headers != "")
      $mp_url .= "&headers=" . $mp_headers;
// log the constructed url
    if (!isset($logger)) {
      $logger = new Zend_Log();
      $path_to_log_file = APPLICATION_PATH . '/modules/mobile/logs/affiliate.log';
      $writer = new Zend_Log_Writer_Stream($path_to_log_file);
      $logger->addWriter($writer);
    }
    $logger->log($mp_url, Zend_Log::INFO);
    $logger = null;

    if ($this->mp_call_type == "http") {
      @$mp_ws_serve = fopen($mp_url, 'r');
      if ($mp_ws_serve) {
        $mp_contents = '';
        while (!feof($mp_ws_serve))
          $mp_contents .= fread($mp_ws_serve, 1024);
        fclose($mp_ws_serve);
      }
    } elseif ($this->mp_call_type == "pixel") {
      echo "<img src=\"$mp_url\" width=\"1\" height=\"1\" />";
    }

    $mp_param = explode("><", $mp_contents);
    if ($mp_param[0] == "ko") {
      $mp_code_error = $mp_param[1];
      $mp_description_error = $mp_param[2];
    }
  }

  private function _getRealIpUa() {
    $debug = 0;
    $useragent = $this->mp_useragent;
    $headers = apache_request_headers();
//Si Opera Mini et si on a le header detaillé:
    if (eregi("Opera Mini", $useragent) && $headers != "") {
      if (is_array($headers)) {
        //Real IP detection
        if (array_key_exists('x-forwarded-for', $headers)) {
          $mp_real_client_ip = $headers['x-forwarded-for'];
          $mp_real_client_ip_tab = explode(",", $mp_real_client_ip);
          $this->mp_ip = $mp_real_client_ip_tab[0];
        } else if (array_key_exists('X-Forwarded-For', $headers)) {
          $mp_real_client_ip = $headers['X-Forwarded-For'];
          $mp_real_client_ip_tab = explode(",", $mp_real_client_ip);
          $this->mp_ip = $mp_real_client_ip_tab[0];
        }

        //Real UserAgent detection
        if (array_key_exists('X-OperaMini-Phone-UA', $headers)) {
          $this->mp_useragent = $headers['X-OperaMini-Phone-UA'];
        } elseif (array_key_exists('X-OperaMini-Phone', $headers)) {
          $this->mp_useragent = $headers['X-OperaMini-Phone'];
        }
      }
    }


//Si Google Wireless Transcoder et si on a le header detaillé:
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
