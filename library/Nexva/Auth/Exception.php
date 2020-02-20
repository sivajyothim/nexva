<?php
class Nexva_Auth_Exception extends Zend_Exception {
    public function __construct($msg = '', $code = 0, Exception $previous = null)  {
        parent::__construct('Authentication Exception : ' . $msg, $code, $previous);
    }
}