<?php

class Api_Model_EcarrotRecurringPayment extends Zend_Db_Table_Abstract {

    protected $_name = 'ecarrot_recurring_payments';
    protected $_id = 'id';

    function __construct() {
        parent::__construct();
    }

    public function insertResponce($data) {
        $id = '';
        try {
            $id = $this->insert($data);
        } catch (Exception $e) {
            var_dump($e->getMessage());
            die();
        }
        return $id;
    }

    public function getPaymentDate($userId) {
        $erpSql1 = $this->select();
        $erpSql1->from($this->_name, array('final_payment_date'))
                ->setIntegrityCheck(false)
                ->where('user_id = ?', $userId)
        ->order('id desc');

        $row = $this->fetchRow($erpSql1);   //If no row is found then $row is null .

        if ($row)
            return $row->toArray();
        else
            return null;
    }

    public function getAllPaymentDate() {
        $erpSql1 = $this->select();
        $erpSql1->from($this->_name)
                ->setIntegrityCheck(false)
                ->where('profile_status = ?', 'Active');

        return $this->fetchAll($erpSql1);
    }

    function updateProfileByProfileId($profileId, $finalDate) {

        $data = array(
            'final_payment_date' => $finalDate
        );
        $this->update($data, "profile_id = '" . $profileId . "'");
    }

    public function getPaidUsers() {
       
        $sql = $this->select();
        $sql->from($this->_name)
                ->setIntegrityCheck(false)
                ->where('profile_status = ?', 'Active');
      
          return $this->fetchAll($sql);
      
    }

}
