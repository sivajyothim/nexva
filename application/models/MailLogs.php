<?php

//Class for insert out going mail to the DB
class Model_MailLogs extends Zend_Db_Table_Abstract {

    protected $_name = 'mail_logs';
    protected $_id = 'id';

    function __construct() {
        parent::__construct();
    }

    public function insertRecords($data) {
        //error_reporting(E_ALL);
        //ini_set('display_errors',1);
         
        return $this->insert($data);
        //print_r($data); die();   
    }

}

?>
