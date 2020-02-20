<?php 
/** 
 * Payment Gateway Request Class - paythru.class.php file 
 * Makes requests to the paythru payment gateway and returns the results 
 * MUST HAVE THE CURL EXTENSIONS 
 * 
 * @author paythru Development 
 * @package paythruClientAPI 
 * @copyright 2008 paythru Limited 
 * @version 1.0.0 
 */ 
  
class Nexva_PaymentGateway_PaythruApi
{ 
  /** 
   * The url of the gateway 
   * @access private 
   * @var string 
   */ 
  var $url = 'https://gateway.paythru.com/pg/http/'; 
 
  /** 
   * Constructor 
   * @access public 
   * @return void 
  */ 
  function Paythru()  
  { 
  } 
/** 
   * Makes the request to the gateway 
   * @access public 
   * @param array $data 
   * @return array 
   */ 
  function makeRequest($data)  
  { 
    if (empty($data) || !is_array($data)) {  return array();} 
    $response = $this->webRequest($this->url, 'POST', $data); 
	//print_r($response);
    $response = $this->parseUrlEncoded($response); 
	//print_r($response);
    return $response; 
  } 
 
  /** 
   * Makes an HTTP request using CURL 
   * @access private 
   * @param string $url 
   * @param string $method 
 
   * @param mixed $vars array or string 
   * @param bool $sslIgnore 
   * @return string 
   */ 
  function webRequest($url, $method = '', $vars = array(), $sslIgnore = 
false)  
  { 
    if (!trim($url)) { return '';} 
    if (!trim($method)) { $method = 'GET';} 
 
    $vars = $this->parseVars($vars); 
 
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_HEADER, false); 
 
    if ($sslIgnore)  
    { 
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
    } 
 
    if ($method == 'POST')  
    { 
      curl_setopt($ch, CURLOPT_POST, true); 
      curl_setopt($ch, CURLOPT_POSTFIELDS, $vars); 
    } 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    $return = curl_exec($ch); 
 
    if (curl_errno($ch)) { $return = curl_error($ch);} 
 
    curl_close($ch); 
 
    return $return; 
  } 
 
  /** 
   * Converts an array into a query string 
   * @access private 
   * @param array $vars 
   * @return string 
   */ 
  function parseVars($vars) 
{ 
    if (!is_array($vars)) { return $vars;} 
	$temp = ''; 
    foreach ($vars as $name => $value)  
    { 
      $temp .= urlencode($name).'='.urlencode($value).'&'; 
    } 
    $temp = substr($temp, 0, -1); 
  return $temp; 
  } 
 
  /** 
   * Parses URL encoded data into an associate array 
   * @access private 
   * @param string $data 
   * @return array 
   */ 
  function parseUrlEncoded($data)  
  { 
    $output = array(); 
 
    /** 
     * Explode on a '&' character 
     */ 
    $temp = explode('&', $data); 
    $count = count($temp); 
 
    /** 
     * Go through the array and split on '=' 
     */ 
    for ($i = 0; $i < $count; $i++)  
    { 
      $splitAt = strpos($temp[$i], '='); 
      if ($splitAt)  
      { 
        $name = trim(rawurldecode(substr($temp[$i], 0, 
$splitAt))); 
        $value = rawurldecode(substr($temp[$i], ($splitAt + 1))); 
        $output[$name] = $value; 
      } 
    } 
    return $output; 
  } 
} 
?> 