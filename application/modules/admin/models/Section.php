<?php
class Admin_Model_Section extends Nexva_Db_Model_MasterModel {
        
    protected  $_id     = 'id';
    protected  $_name   = 'sections';
    
    function __construct() {
        parent::__construct();
        $db     = Zend_Registry::get('db');
        $db->query('SET NAMES "utf8"')->execute();
    }
    
    public function getSectionByCode($code) {
        $select = $this->select(true);
        $select->where('sections.code = ?', $code);
        $select->limit(1);
        $row    = $this->fetchAll($select);
        if (!empty($row)) {
            $row    = $row->toArray();
            if (!isset($row[0])) {
                return false;    
            } else {
                return (object) $row[0];    
            }
        }
        return false;
    }
}