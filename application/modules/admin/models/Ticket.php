<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 11/27/13
 * Time: 4:00 PM
 * To change this template use File | Settings | File Templates.
 */

class Admin_Model_Ticket extends Zend_Db_Table_Abstract {

    protected $_id = 'id';
    protected $_name = 'tickets';

    function __construct()
    {
        parent::__construct ();
    }

    function getAllOpenTickets(){
        $sql = $this->select();
        $sql->from(array('t'=>$this->_name))
            ->where('t.status =?','Open')
            ;
        //Zend_Debug::dump($sql->assemble());die();
        return $this->fetchAll($sql);
    }

    /**
     * @param $chapId
     * @param $priority
     * @param $status
     * @param $source
     * @return tickets for a particular chap
     */
    function getTicketsByChap($chapId,$priority,$status,$source)
    {
        $sql   = $this->select();
        $sql->from(array('t' => $this->_name),array('t.*'))
            ->setIntegrityCheck(false)
            ->join(array('u' => 'users'), 'u.id = t.user_id', array('u.email'));
            
            if($chapId != 'all'){
                $sql->where('u.chap_id = ?', $chapId);
            }
            if($priority)
            {
                $sql->where('t.priority = ?', $priority);
            }
            if($status)
            {
                $sql->where('t.status = ?', $status);
            }
            if($source)
            {
                $sql->where('t.source = ?', $source);
            }
        $sql->order('t.id DESC');
        //Zend_Debug::dump($sql->assemble());die();
        return $sql;
    }

    function deleteTicket($id)
    {
        $this->delete('id ='. $id);
    }

    function closeTicket($id)
    {
        $data = array (
            'status' => 'Closed',
            'action_date' => date('Y-m-d H:i:s')
        );
        $this->update ( $data, array ('id = ?' => $id ) );
    }

    function getTicketById($ticketId)
    {
        $sql =  $this->select()
            ->from(array('t'=>$this->_name))
            ->where('t.id = ?',$ticketId)
        ;
        return $this->fetchRow($sql);
    }

    function getActiveChaps()
    {
        $sql =  $this->select()
                ->setIntegrityCheck(false)
                ->from(array('u'=>'users'))
                ->where('u.status = ?',1)
                ->where('u.type = ?','CHAP')
                ;
        //return $sql->assemble();
        return $this->fetchAll($sql);
    }

    function updateTicket($ticketId,$data)
    {
        $this->update($data,array('id = ?' => $ticketId));
    }

    function getResponsesForTicket($ticketId)
    {
        $sql =  $this->select()
            ->from(array('t' => $this->_name), array('t.id as ticketId'))
            ->setIntegrityCheck(false)
            ->join(array('u' => 'users'), 't.user_id = u.id', array())
            ->join(array('tr' => 'ticket_responses'), 't.id = tr.ticket_id', array('tr.id', 'tr.description', 'tr.attachment_name', 'tr.response_date', 'tr.user_id'))
            //->where('t.user_id = ?',$userId)
            ->where('t.id = ?',$ticketId)
            ->order('tr.id ASC')
        ;
        return $this->fetchAll($sql);
    }

    /**
     * @return open ticket count for all CHAPs
     */
    function getOpenTicketCount()
    {
        $sql   = $this->select();
        $sql->from(array('t' => $this->_name),array('t.*'))
            ->setIntegrityCheck(false)
            ->join(array('u' => 'users'), 'u.id = t.user_id', array('u.email'))
            ->where('t.status = ?','Open')
            ->order('t.id DESC')
            ;
        $result = $this->fetchAll($sql)->toArray();
        return count($result);
    }

    function getClosedTodayTickets(){
        $toDay = date("Y-m-d");
        //echo $toDay;die();
        $sql   = $this->select();
        $sql->from(array('t' => $this->_name))
            ->where('DATE(t.action_date) = ?',$toDay)
            ;
        $result = $this->fetchAll($sql)->toArray();
        return count($result);
    }
}
