<?php
class Admin_Model_AdminAccount extends Zend_Db_Table_Abstract{

    protected $_name =   'user_accounts';

    public $isNewGuy = false;
    function  __construct(){
      parent::__construct();
    }

    
    function getPaymentDetails($month,$year,$id){

        $payment    = $this->fetchAll("user_id = $id", 'date desc');

        $rowCount =   count($payment);
          if(0 == $rowCount ){
              $this->isNewGuy=true;
          }

         return $payment;
    }
    
}

?>