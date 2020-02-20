<?php

class Partner_Model_Tickets extends Zend_Db_Table_Abstract
{
    protected $_name = 'tickets';
    protected $_id = 'id';
    
    /*
     * Inserting submitted ticket details
     */
    public function insertTicketingDetails($values)
    {
        $this->insert($values);
        return $this->getAdapter()->lastInsertId();
    }
    
    /*
     * Get all tickets by user ID
     * @pram $userId
     * @return object array
     */
    public function getAllUserTickets($userId){
 
        $sql = $this->select();
        $sql->from(array('t' => $this->_name), array('t.id','t.subject','t.description','t.type','t.attachment_name','t.created_date','t.priority','t.source','t.status'))
            ->setIntegrityCheck(false)
            ->join(array('u' => 'users'), 't.user_id = u.id', array())
            ->joinLeft(array('tr' => 'ticket_responses'), 't.id = tr.ticket_id', array('COUNT(tr.ticket_id) as response_count'))
            ->where('t.user_id = ?',$userId)
            ->group('t.id')
            ->order('t.id DESC')
            ;  
        
        //echo $sql->assemble(); die();
        return $this->fetchAll($sql);
    }
    
    /*
     * Get single ticket by user ID and ticket ID
     * @pram $userId
     * @pram $ticketId
     * @return single row
     */
    public function getSingleTicket($ticketId, $userId){
 
        $sql = $this->select();
        $sql->from(array('t' => $this->_name), array('t.id','t.subject','t.description','t.type','t.attachment_name','t.created_date','t.priority','t.source','t.status'))
            ->setIntegrityCheck(false)
            ->join(array('u' => 'users'), 't.user_id = u.id', array('u.username'))
            ->joinLeft(array('tr' => 'ticket_responses'), 't.id = tr.ticket_id', array('COUNT(tr.ticket_id) as response_count'))
            ->where('t.user_id = ?',$userId)
            ->where('t.id = ?',$ticketId)
            ->group('t.id')
            ;  
        
        //echo $sql->assemble(); die();
        $result = $this->fetchRow($sql);
        if(count($result) > 0){
            return $result->toArray();
        }
        else{
            return array();
        }
    }
    
    /*
     * Get responses by user ID and ticket ID
     * @pram $userId
     * @pram $ticketId
     * @return result array
     */
    public function getResponsesForTicket($ticketId, $userId){
 
        $sql = $this->select();
        $sql->from(array('t' => $this->_name), array('t.id as ticketId'))
            ->setIntegrityCheck(false)
            ->join(array('u' => 'users'), 't.user_id = u.id', array())
            ->join(array('tr' => 'ticket_responses'), 't.id = tr.ticket_id', array('tr.id', 'tr.description', 'tr.attachment_name', 'tr.response_date', 'tr.user_id'))
            ->where('t.user_id = ?',$userId)
            ->where('t.id = ?',$ticketId)
            //->order('tr.id DESC')
            ;  
        
        //echo $sql->assemble(); die();
        $result = $this->fetchAll($sql);
        if(count($result) > 0){
            return $result->toArray();
        }
        else{
            return array();
        }
    }
    
    /*
     * Get ticket row by ticket ID
     * @pram $ticketId
     */
    function getTicketRow($ticketId){
        $rowset = $this->find($ticketId);
        $ticketRow = $rowset->current();
        return $ticketRow;
    }
}