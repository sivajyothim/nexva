<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 11/22/13
 * Time: 12:03 PM
 * To change this template use File | Settings | File Templates.
 */
class Cpbo_Model_TicketResponse extends Zend_Db_Table_Abstract {
    protected $_name    = 'ticket_responses';
    protected $_primary = 'id';

    function  __construct() {
        parent::__construct();
    }

    function createResponse($values)
    {
        $this->insert($values);
        return $this->getAdapter()->lastInsertId();
    }

    function getResponseRow($responseId){
        $rowset = $this->find($responseId);
        $responseRow = $rowset->current();
        return $responseRow;
    }

    function deleteResponse($id)
    {
        $this->delete('ticket_id ='. $id);
    }

}