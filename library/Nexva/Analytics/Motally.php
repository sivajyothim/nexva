<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */

/**
 * Instructions
 *
 * Insert the code on each page you wish to track.
 *
 * For ease of installation, add the code below to a common
 * file that is included on all pages - such as a header or footer.
 *
 * Remember to set the motally_params "m" value to live when ready to track stats
 *
 * Explanation of Optional fields
 * "e" - the environment - options: "prod", "dev"
 *       Default is "prod" if left blank
 *       Currently only production values will be displayed in reports.
 *       Subsquent versions of the reporting will allow viewing of stats marked on the development site
 *
 * "mu" - markup language - options: "xhtml", "wml", "chtml"
 *       Default is blank
 *       Markup doesn't affect the stats tracked
 *
 *
 * "u" - unique user id - options: free-form, site defined id
 *       Default is blank
 *       Site publisher can pass any String that uniquely identifies the user to aid in stats reporting
 *
 * "t" - track id - options: free-form, site defined id
 *       Default is blank
 *       Site publisher can define an id to further identify this particular hit
 *       For example a particular event occured on this page vs other versions of the same page
 *
 * Copyright 2009 Motally
 */

class Nexva_Analytics_Motally {
    private $_motally_params = array();

    public static $userAgentHeaders = array(
            'HTTP_X_DEVICE_USER_AGENT',
            'HTTP_X_ORIGINAL_USER_AGENT',
            'HTTP_X_OPERAMINI_PHONE_UA',
            'HTTP_X_SKYFIRE_PHONE',
            'HTTP_X_BOLT_PHONE_UA',
            'HTTP_USER_AGENT'
    );

    public function __construct() {
        $motally_params = array();
        $motally_params["s"] = "ni9qj7lw";
        $motally_params["v"] = "phpc.20090506";
        //required parameters
        $motally_params["m"] = "live"; //change to "live" when ready to track stats; set to "test" to disable reporting
        //optional parameters
        $motally_params["e"] = ""; //Optional environment: "prod", "dev"
        $motally_params["mu"] = ""; //Optional page markup: "xhtml", "wml"] = "chtml"
        $motally_params["t"] = ""; //Optional track id
        $motally_params["u"] = "";
        $this->_motally_params = $motally_params;
    }

    public function log() {
        return $this->motallyTrack();
    }

    private function motallyAppend(&$motally_post, $key, $value) {
        if($value != null && $value != "") {
            $motally_post .= sprintf("&%s=%s", urlencode($key),urlencode($value));
        }
    }

    private function motallyTrack() {
        $motally_params = $this->_motally_params;
        $motally_url = "http://track.motally.com/m/t";
        $motally_timeout = 1;

        //param string
        $motally_post = "";

        //add motally params
        $motally_post .= sprintf("s=%s", $motally_params["s"]);
        $this->motallyAppend($motally_post,"v",$motally_params["v"]);
        $this->motallyAppend($motally_post,"m",$motally_params["m"]);
        $this->motallyAppend($motally_post,"e",$motally_params["e"]);
        $this->motallyAppend($motally_post,"mu",$motally_params["mu"]);
        $this->motallyAppend($motally_post,"t",$motally_params["t"]);
        $this->motallyAppend($motally_post,"u",$motally_params["u"]);
        //add page variables
        $this->motallyAppend($motally_post, "a", $this->getUserAgent());
        $this->motallyAppend($motally_post,"p",$_SERVER["REQUEST_URI"]);
        $this->motallyAppend($motally_post,"q",$_SERVER["QUERY_STRING"]);
        $this->motallyAppend($motally_post,"r",isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "");
        $this->motallyAppend($motally_post,"i",$_SERVER["REMOTE_ADDR"]);
        $this->motallyAppend($motally_post, "si", session_id() ? md5(session_id()) : "");

        //header vars
        $keys = array_keys($_SERVER);
        $motally_headers = "";
        foreach($keys as $key) {
            $xin = stripos($key, "HTTP_");
            if($xin !== false && $xin == 0) {
                $motally_headers .= sprintf("%s=%s&", $key, trim($_SERVER[$key]));
            }
        }
        $this->motallyAppend($motally_post,"c", $motally_headers);


        $motally_request = curl_init();
        curl_setopt( $motally_request, CURLOPT_URL, $motally_url);
        curl_setopt( $motally_request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $motally_request, CURLOPT_TIMEOUT, $motally_timeout);
        curl_setopt( $motally_request, CURLOPT_CONNECTTIMEOUT, $motally_timeout);
        curl_setopt( $motally_request, CURLOPT_HEADER, false);
        //curl_setopt( $motally_request, CURLOPT_FOLLOWLOCATION  ,1);
        //curl_setopt( $motally_request, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Connection: Close"));
        curl_setopt($motally_request, CURLOPT_POST, 1);
        curl_setopt( $motally_request, CURLOPT_POSTFIELDS, $motally_post);
        $motally_results = curl_exec( $motally_request );

        if(!isset($motally_results) || ($motally_results === FALSE)) {
//          exit;
//          return $motally_results;
            $motally_results = "Motally request failed";
        }
        else {
          $motally_results = "Motally request success";
        }
        curl_close( $motally_request );
//exit;
        return $motally_results;

    }

    // Public Methods
    public function getUserAgent($source=null) {
        if(is_null($source) || !is_array($source))$source = $_SERVER;
        $userAgent = '';
        if(isset($_GET['UA'])) {
            $userAgent = $_GET['UA'];
        }else {
            foreach(self::$userAgentHeaders as $header) {
                if(array_key_exists($header,$source) && $source[$header]) {
                    $userAgent = $source[$header];
                    break;
                }
            }
        }
        return $userAgent;
    }
}
?>
