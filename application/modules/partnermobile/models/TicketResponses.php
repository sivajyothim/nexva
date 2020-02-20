<?php

class Partnermobile_Model_TicketResponses extends Zend_Db_Table_Abstract
{
    protected $_name = 'ticket_responses';
    protected $_id = 'id';

    /*
     * Inserting submitted response details and return the last insert id
     */
    public function insertResponseDetails($values)
    {
         $this->insert($values);
         return $this->getAdapter()->lastInsertId();
    }
    
    /*
     * Get response row by ticket ID
     * @pram $ticketId
     */
    function getResponseRow($responseId){
        $rowset = $this->find($responseId);
        $responseRow = $rowset->current();
        return $responseRow;
    }
   
}