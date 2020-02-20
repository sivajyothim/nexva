<?php

class Cpbo_Model_Annoucement extends Zend_Db_Table_Abstract {

    protected  $_id     = 'id';
    protected  $_name   = 'announcements';

    public function  __construct() {
        parent::__construct();
    }

    /**
     * Returns all announcements that are unread by $userId
     *
     * @param int $userId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getUnreadAnnoucements($userId, $limit = 0) {

        $db = Zend_Registry::get('db');

        $subSelect = Zend_Registry::get('db')->select()
        ->from('user_announcements', array('announcement_id') )
        ->where("user_id = $userId");

        $select = Zend_Registry::get('db')->select()
            ->from( 'announcements', array('id', 'title', 'message') )
            ->where("announcements.id NOT IN ($subSelect)")
            ->where("announcements.for = 'CP'")
            ->where("announcements.status = 1")
            ->order('id DESC');

        if( $limit > 0 ) $select->limit($limit);

        return $db->fetchAll($select);
        
    }
}


?>




