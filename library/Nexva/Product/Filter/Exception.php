<?php
class Nexva_Product_Filter_Exception extends Zend_Exception {
    
    public function __construct($msg = '', $code = 0, Exception $previous = null)  {
        parent::__construct('App Filter Exception : ' . $msg, $code, $previous);
    }
}
