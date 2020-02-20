<?php
class Nexva_Analytics_NexpagerView extends Nexva_Analytics_Base {
    
    public function __construct() {
        parent::__construct();
        $this->tableName    = 'nexpager_views'; 
    }
    
    /**
     * 
     * Logs the data for a nexpager view
     * Required keys are - cp_id, device_id,  device_name, language_id, language_name
     * Optional keys are - ip
     * @todo See if we can filter out admin views. 
     */
    public function log($opts) {
        
        $required   = array(
            'cp_id', 'device_id', 'device_name',
            'language_id', 'language_name'
        );
        
        foreach ($required as $key) {
            if (!isset($opts[$key])) {
                throw new Nexva_Analytics_Exception($key . ' has not been set');
            }
        }
        $request        = new Zend_Controller_Request_Http();
        $opts['ip']     = empty($opts['ip']) ? $request->getClientIp() : $opts['ip'];
        $opts['ua']     = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        
        $opts['cp_id']  = (int) $opts['cp_id'];
        
        $referrer   = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        if (filter_var(trim($referrer), FILTER_VALIDATE_URL) !== false) { 
            $parsedUrl  = parse_url($referrer);
            
            $opts['referrer']    = array(
                'url'   => $referrer,
                'base'  => $parsedUrl['host']
             );
        } else {
            $opts['referrer']    = null;
        }
        
        $opts['timestamp']   = time();
        try {
            $this->_save($opts, $this->tableName);
        } catch (Exception $ex) {
            /**
             * @todo log the error?
             */
        }
    }
}