<?php

class Admin_Model_InteropPayments extends Zend_Db_Table_Abstract
{
    protected $_name = 'interop_payments';
    protected $_id = 'id';

  
    
      
    public function getRevenue($startDate, $endDate){
        $select= $this->select()
                      ->setIntegrityCheck(false)
                      ->from(array('itp'=>$this->_name),array('round(sum(itp.price),2) as sum'))
                      ->join(array('tm' => 'theme_meta'), 'itp.chap_id = tm.user_id', array('channel'=>'tm.meta_value'))
                      ->where("status='success'")
                      ->where("tm.meta_name='WHITELABLE_SITE_NAME'")
                      ->where("itp.price < 5 ")
                      ->where("itp.mobile_no not in('081235356533','81235356533')")
                      ->where("itp.date_transaction between '".$startDate."' and '".$endDate."'")
                      ->group("itp.chap_id");
        return $this->fetchAll($select);              
                      

    }
    
    public function getPaymentReceivedList($startDate, $endDate){
        $select= $this->select()
                      ->setIntegrityCheck(false)
                      ->from(array('itp'=>$this->_name),array('date'=>'date_transaction','credit'=>'price','nexva_earn'=>'round(( (price/100)*p.payout_nexva),2)','cp_earn'=>'round(( (price/100)*p.payout_cp),2)','chap_earn'=>'round(( (price/100)*p.payout_chap),2)','mobile_no'=>'mobile_no','trans_id'=>'trans_id'))
                      ->join(array('tm' => 'theme_meta'), 'itp.chap_id = tm.user_id', array('meta_value as channel'))
                      ->join(array('u' => 'users'), 'itp.chap_id = u.id', array())
                      ->join(array('p' => 'payouts'), 'p.id = u.payout_id')  
                      ->where("itp.status='success'")
                      ->where("tm.meta_name='WHITELABLE_SITE_NAME'")
                      ->where("itp.price < 5 ")
                      ->where("itp.mobile_no not in('081235356533','81235356533')")
                      ->where("itp.date_transaction between '".$startDate."' and '".$endDate."'")
                      ->order("itp.date_transaction desc");
        return $this->fetchAll($select);              
                      

    }

}
?>