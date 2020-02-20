<?php
class Nexva_Analytics_NoCacheException extends Zend_Exception {
    
    public function __construct($msg = '', $code = 0, Exception $previous = null)  {
        parent::__construct('No Cache Available Exception : ' . $msg, $code, $previous);
    }
}
