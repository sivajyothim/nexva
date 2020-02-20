<?php

class Api_Model_EcarrotUser extends Zend_Db_Table_Abstract {

    protected $_name = 'ecarrot_user';
    protected $_id = 'id';

    function __construct() {
        parent::__construct();
    }

    public function insertResponce($data) {
        try {
            $id = $this->insert($data);
        } catch (Exception $e) {
            var_dump($e->getMessage());
            die();
        }
        return $id;
    }

    function updateEcarrotUser($userId) {

        $data = array(
            'verification_status' => 1,
            'started_date' => date('Y-m-d')
        );
        try {
            $this->update($data, 'ecarrot_user_id = ' . $userId);
        } catch (Exception $e) {
            die(array('error' => $e->getMessage()));
        }
    }

    public function getUserstatus($ecarrotUserId) {
        $sql = $this->select();
        $sql->from($this->_name, array('id', 'email', 'mobile_number', 'ecarrot_user_id', 'verification_status', 'mobile_number', 'varification_date'))
                ->where('ecarrot_user_id = ?', $ecarrotUserId);

        $userRow = $this->fetchRow($sql);

        if (is_null($userRow)) {
            return null;
        }
        return $userRow->toArray();
    }

    public function getUnpaidVarifiedUsers() {
      
        $sql = $this->select();
        $sql->from(array('eu' => $this->_name), array('eu.ecarrot_user_id','eu.varification_date','eu.email','eu.varification_date'))
                ->setIntegrityCheck(false)
                ->joinleft(array('erp' => 'ecarrot_recurring_payments'), 'eu.ecarrot_user_id = erp.user_id',array())                
                ->where('erp.user_id is null')     
                ->where('eu.verification_status = ?', 1);
        
        return $this->fetchAll($sql);
//        Zend_Debug::dump($sql->assemble());die();
    }


}
