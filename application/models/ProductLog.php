<?php

class Model_ProductLog extends Zend_Db_Table_Abstract{

    protected $_id      =   "id";
    protected $_name    =   "product_log";


    function  __construct() {
        parent::__construct();
    }
}


?>
