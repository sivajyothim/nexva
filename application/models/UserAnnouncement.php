<?php
class Model_UserAnnouncement extends Zend_Db_Table_Abstract {

    protected  $_id     = 'id';
    protected  $_name   = 'user_announcements';

    public function  __construct() {
        parent::__construct();
    }

    /**
     * Marks an announcment Id as 'read' by user identfied by $userId
     *  
     * @param int $annoucementId
     * @param int $userId
     * @return mixed Last insert ID if the operation was a success || FALSE if the operations failed
     */
    public function markAnnoucementAsUnRead($announcementId, $userId) {
        
            return $this->insert(array (
                    "user_id" => $userId,
                    "announcement_id" => $announcementId
            ));

       

     
    }
    

}

?>
