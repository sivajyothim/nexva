<?php
/* 
 * A library to send get dynamic registation codes via HTTP Post
 *
 * @author jahufar
 * @todo: Shouldn't this be a larger part of a webhooks framework?
 *
 */

class Nexva_DynamicKeyGenerator_PostToScript {

    protected $_options = null;
    protected $_lastHttpResponse = null;
    protected $_lastHttpResponseCode = null;

    /**
     * Constructor
     *
     * @param array $options
     *
     * Currently supported options:
     *  'post_fields': An array of post fields to submit
     *  'endpoint': The location where to submit to
     *
     */
    public function  __construct($options = array()) {

        if( !isset($options['post_fields']['test'])) $options['post_fields']['test']=0;
        if( isset($options['post_fields']['imei'])) $options['post_fields']['pin']=$options['post_fields']['imei']; //IMEI and PIN are synonymous
        if( !isset($options['endpoint'])) throw new Zend_Exception('No endpoint set');

        $this->_options = $options;

    }

    public function getKey() {

        $fields= "";
        foreach($this->_options['post_fields'] as $key=>$value) { $fields .= $key.'='.$value.'&'; }        
        rtrim($fields,'&');

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$this->_options['endpoint']);
        curl_setopt($ch,CURLOPT_POST,count($this->_options['post_fields']));
        curl_setopt($ch,CURLOPT_POSTFIELDS,$fields);
        //curl_setopt($ch,CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        

        $this->_lastHttpResponse = curl_exec($ch);

        $this->_lastHttpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        switch ($this->_lastHttpResponseCode) {
            case "200": //all OK, the body should contain the reg code                
            case "202": //request was accepted, but no reg code sent (perhaps sent later). the body should contain more information
                return $this->_lastHttpResponse;
                break;


            case "403": //request rejected
                return false;
                
        }

    }

    public function getLastHttpResponseCode() {
        return $this->_lastHttpResponseCode;
    }

    public function getLastHttpResponse() {
      return $this->_lastHttpResponse;
    }
}
?>
