<?php
class Admin_Model_PhraseLang extends Nexva_Db_Model_MasterModel {
        
    protected  $_id     = 'id';
    protected  $_name   = 'phrase_langs';
    
    function __construct() {
        parent::__construct();
        $db     = Zend_Registry::get('db');
        $db->query('SET NAMES "utf8"')->execute();
    }
}