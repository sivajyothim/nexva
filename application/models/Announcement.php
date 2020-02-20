<?php

class Model_Announcement extends Nexva_Db_Model_MasterModel {

    protected  $_id     = 'id';
    protected  $_name   = 'announcements';

    public function  __construct() {
        parent::__construct();
    }

    
    public function addUsers($announcementId,$chap_id) {
        
        //see whether it's already added, if so ignore
        $db     = Zend_Registry::get('db');
        $stmnt  = $db->query('SELECT id FROM user_announcements WHERE announcement_id = ? LIMIT 1', array($announcementId));
        $row    = $stmnt->fetch();
        if ($row === false) {
            $sql    = " INSERT INTO `user_announcements` (announcement_id, user_id)
                        SELECT $announcementId, id
                        FROM users";
            if($chap_id == -1)
            {
                $sql = $sql." WHERE users.type = 'CP'";
            }
            else
            {
                $sql = $sql." WHERE users.chap_id = $chap_id";
            }

            //return $sql;
            $db->query($sql);
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Returns the number of unread messages. 
     * Filters by user if userID given
     * @param $userId
     */
    public function getUnreadMessageCount($userId = null) {
        $db     = Zend_Registry::get('db');
        if ($userId) {
            $stmnt  = $db->query('SELECT COUNT(id) AS unread FROM user_announcements WHERE user_id = ?', array($userId));    
        } else {
            $stmnt  = $db->query('SELECT COUNT(id) AS unread FROM user_announcements');
        }
        
        $row    = $stmnt->fetch();
        return $row->unread;
    }
    
    public function getUnreadMessagesForUser($userId, $limit = 10) {
        $select = $this->select(false)->setIntegrityCheck(false);
        $select
            ->from('announcements', array('title', 'message', 'created'))
            ->joinLeft('user_announcements', 'user_announcements.announcement_id = announcements.id', array('user_id'))
            ->where('user_id = ? ', $userId)->limit(10);
        return $this->fetchAll($select);
    }
    
    /**
     * 
     * Used in the front end. Hence the limiting an the conditions
     */
    public function getMessages($CHAP,$limit = 10) {
        $select = $this->select(true)->setIntegrityCheck(false);
        if($CHAP == null)
        {
            $select->where("cp_chap = '-1'");
        }
        else
        {
            $select->where("cp_chap IN ($CHAP,'-1')");
        }
        $select->where('status = 1')->order('created DESC')->limit(10);
        return $this->fetchAll($select);
    }
    
    public function deleteUnreadMessages($userId) {
        $sql    = "DELETE FROM user_announcements WHERE user_id = ?";
        $db     = Zend_Registry::get('db');
        $db->query($sql, array($userId));
    }
}

?>