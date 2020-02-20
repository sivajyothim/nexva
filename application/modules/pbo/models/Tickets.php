<?php

class Pbo_Model_Tickets extends Zend_Db_Table_Abstract
{
    protected $_name = 'tickets';
    protected $_id = 'id';
    
    /**
     * Returns a details of a menu item  
     * @param - $chapId
     * @param - $menuId
     */
    public function getTicketsByChap($chapId)
    {
        $selectSql   = $this->select();
        $selectSql->from(array('t' => $this->_name),array('t.*'))
                    ->setIntegrityCheck(false)
                    ->join(array('u' => 'users'), 'u.id = t.user_id', array('u.email'))                    
                    ->where('u.chap_id = ?', $chapId)
                    ->where('t.status != ?', 'Closed')
                    ->order('t.id DESC');

        return $selectSql;
    }
        
    /**
     * Returns a details of a ticket
     * @param - $ticketId ticket id
     */
    public function getTicketDetailsById($ticketId)
    {
        $selectSql   = $this->select(); 
        $selectSql->from($this->_name, array('*'))
                    ->where('id = ?',$ticketId);
        
        return $this->fetchRow($selectSql);
    }
    
    /**
     * Update ticket response date
     * @param - $ticketId  
     * @param - $date  
     */
    public function updateResponseDate($ticketId, $date)
    {   
        $data = array(                       
                        'action_date' => $date
                    );
        
        $where = array('id = ?' => $ticketId);

        $rowsAffected = $this->update($data,$where);
       
        if($rowsAffected > 0)
        {
            return  TRUE;

        }
        else
        {
            return FALSE;
        }
    }
    
    public function updateTicketProperties($ticketId, $type, $priority, $status)
    {
        $data = array(                       
                        'type' => $type,
                        'priority' => $priority,
                        'status' => $status
                    );
        
        $where = array('id = ?' => $ticketId);

        $rowsAffected = $this->update($data,$where);
       
        if($rowsAffected > 0)
        {
            return  TRUE;

        }
        else
        {
            return FALSE;
        }
    }
}
