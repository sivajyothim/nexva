<?php

class Admin_Model_Announcement extends Zend_Db_Table_Abstract {

    protected  $_id     = 'id';
    protected  $_name   = 'announcements';

    public function  __construct() {
        parent::__construct();
    }

}

?>