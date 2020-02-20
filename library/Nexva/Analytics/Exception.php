<?php
class Nexva_Analytics_Exception extends Zend_Exception {
    
    public function __construct($msg = '', $code = 0, Exception $previous = null)  {
        parent::__construct('Analytics Exception : ' . $msg, $code, $previous);
    }
}
