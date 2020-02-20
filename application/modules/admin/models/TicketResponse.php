<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 11/27/13
 * Time: 6:44 PM
 * To change this template use File | Settings | File Templates.
 */

class Admin_Model_TicketResponse extends Zend_Db_Table_Abstract {
    protected $_id = 'id';
    protected $_name = 'ticket_responses';

    function __construct()
    {
        parent::__construct ();
    }

    function deleteResponse($id)
    {
        $this->delete('ticket_id ='. $id);
    }
}