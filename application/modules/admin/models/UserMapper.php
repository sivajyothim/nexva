<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */

class Admin_Model_UserMapper {

    /**
     *
     * @var <type>
     */
    protected $_dbTable;

    /**
     *
     * @param <type> $dbTable
     * @return <type>
     */
    public function setDbTable($dbTable) {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    /**
     *
     * @return <type>
     */
    public function getDbTable() {
        if (null === $this->_dbTable) {
            $this->setDbTable('Admin_Model_DbTable_User');
        }
        return $this->_dbTable;
    }

    /**
     *
     * @param Admin_Model_User $user
     */
    public function save(Admin_Model_User $user) {
        $data = array(
                'id'        => $user->getId(),
                'username'  => $user->getUsername(),
                'email'     => $user->getEmail(),
                'password'  => $user->getPassword(true),
                'type'      => $user->getType(),
                'payout_id'=> $user->getPayout(),
                'login_type'=> 'NEXVA',
                'status'    => $user->getStatus(),
        );

//        print_r($data);
//        echo $user->getId();
//        exit;
        if (0 === ($id = $user->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    /**
     *
     * @param <type> $id
     * @param Application_Model_Guestbook $guestbook
     * @return <type>
     */
    public function find($id, Admin_Model_User $user) {
     
        $result = $this->getDbTable()->find($id);
        
        if (0 == count($result)) {
           
            return false;
        }
        $row = $result->current();
        $user   ->setId($row->id)
                ->setUsername($row->username)
                ->setEmail($row->email)
                ->settype($row->type)
                ->setPayout($row->payout_id)
                ->setStatus($row->status);
        
    }

    /**
     *
     * @return <type>
     */
    public function fetchAll() {

        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Admin_Model_User();
            $entry  ->setId($row->id)
                    ->setUsername($row->username)
                    ->setEmail($row->email)
                    ->settype($row->type)
                    ->setStatus($row->status);
            $entries[] = $entry;
        }
        return $entries;
    }



    /**
     *
     */
    public function fetchRows($where, $sort, $start = 0 , $offset = 10) {

        if('' == $offset) {
            
            $resultSet = $this->getDbTable()->fetchAll(
                    $where,
                    $sort,
                    $start
            );
        }else {
            $resultSet = $this->getDbTable()->fetchAll(
                    $where,
                    $sort,
                    $offset,
                    $start
            );
        }
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Admin_Model_User();
            $entry  ->setId($row->id)
                    ->setUsername($row->username)
                    ->setEmail($row->email)
                    ->settype($row->type)
                    ->setStatus($row->status);
            $entries[] = $entry;
        }
        return $entries;
    }

    /**
     *
     * @param <type> $id
     * @return <type>
     */
    public function delete($id) {
        $result = $this->getDbTable()->delete('id = ' . $id);
        return $result;
    }
}

