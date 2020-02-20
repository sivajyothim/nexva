<?php
class Cpbo_Model_UserAccount extends Zend_Db_Table_Abstract{

    protected $_id   =   'id';
    protected $_name =   'user_accounts';

    public $isNewGuy = false;
    function  __construct(){
      parent::__construct();
    }

    function getPaymentDetails($from_view, $to_view, $id, $paymentType = 'WEB'){
        
        if(is_null($from_view) or is_null($to_view) or is_null($id))return;

        $payment    = $this->fetchAll(" `date` between '".$from_view."' and  '".$to_view."' and user_id = $id and payment_type = '".$paymentType."'  ", 'date desc'); 
       
        return $payment;
    }
    
    function getPaymentDetailsForCpCreditLeft($id, $paymentType = 'CP'){

        $payment    = $this->fetchAll("user_id = $id and payment_type = '".$paymentType."'  ", 'date desc'); 
       
        return $payment;
    }
    
   
    
}

?>