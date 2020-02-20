<?php

class Pbo_Model_TicketResponses extends Zend_Db_Table_Abstract
{
    protected $_name = 'ticket_responses';
    protected $_id = 'id';
    
    /**
     * Returns a ticket responses by ticket id
     * @param - $chapId
     * @param - $menuId
     */
    public function getResponsesByTicketId($ticketId)
    {
        $selectSql   = $this->select();
        $selectSql->from($this->_name, array('id','description','attachment_name','response_date','user_id','ticket_id'))                    
                    ->where('ticket_id = ?', $ticketId)
                    ->order('id');

        return $this->fetchAll($selectSql);
    }
   
    /**
     * add a ticket response
     * @param - $description response
     * @param - $attachment attachement name
     * @param - $userId user id
     * @param - $ticketId ticket id
     */
    public function addTicketResponse($description, $attachment, $responseDate, $userId, $ticketId)
    {
        $data = array
                (
                    'description' => $description,
                    'attachment_name' => $attachment,
                    'response_date' => $responseDate,
                    'user_id' => $userId,
                    'ticket_id' => $ticketId
                );
        
       $id =  $this->insert($data);
       return $id;
    }   
    
    /**
     * add a ticket response
     * @param - $description response
     * @param - $attachment attachement name
     * @param - $userId user id
     * @param - $ticketId ticket id
     */
    public function getResponseDetailsById($id)
    {
        $selectSql   = $this->select();
        $selectSql->from($this->_name, array('id','description','attachment_name','response_date','user_id','ticket_id'))                    
                    ->where('id = ?', $id);

        return $this->fetchRow($selectSql);
    }
}