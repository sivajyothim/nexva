<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 11/18/13
 * Time: 5:15 PM
 * To change this template use File | Settings | File Templates.
 */
class Cpbo_Model_Ticket extends Zend_Db_Table_Abstract {

    protected $_name    = 'tickets';
    protected $_primary = 'id';

    function  __construct() {
        parent::__construct();
    }

    function createTicket($values)
    {
        $this->insert($values);
        return $this->getAdapter()->lastInsertId();
    }

    function getTicketsForCp($userId)
    {
        $sql =  $this->select()
                ->setIntegrityCheck(false)
                ->from(array('t'=>$this->_name))
                ->join(array('u' => 'users'), 't.user_id = u.id', array())
                ->joinLeft(array('tr'=>'ticket_responses'),'t.id = tr.ticket_id',array('count(tr.id) AS responses'))
                ->where('t.user_id = ?',$userId)
                ->group('t.id')
                ->order('t.id DESC')
                ;
        //Zend_Debug::dump($sql->assemble());die();
        return $sql;
    }

    function deleteTicket($id)
    {
        $this->delete('id ='. $id);
    }

    function getTicketById($ticketId)
    {
        $sql =  $this->select()
                ->from(array('t'=>$this->_name))
                ->where('t.id = ?',$ticketId)
                ;
        return $this->fetchRow($sql);
    }

    function getResponsesForTicket($ticketId, $userId)
    {
        $sql =  $this->select()
                ->from(array('t' => $this->_name), array('t.id as ticketId'))
                ->setIntegrityCheck(false)
                ->join(array('u' => 'users'), 't.user_id = u.id', array())
                ->join(array('tr' => 'ticket_responses'), 't.id = tr.ticket_id', array('tr.id', 'tr.description', 'tr.attachment_name', 'tr.response_date', 'tr.user_id'))
                ->where('t.user_id = ?',$userId)
                ->where('t.id = ?',$ticketId)
                ->order('tr.id ASC')
                ;
        return $this->fetchAll($sql);
    }

    function getTicketRow($ticketId){
        $rowset = $this->find($ticketId);
        $ticketRow = $rowset->current();
        return $ticketRow;
    }
}