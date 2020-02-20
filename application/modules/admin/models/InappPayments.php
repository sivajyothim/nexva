<?php

class Admin_Model_InappPayments extends Zend_Db_Table_Abstract
{
    protected $_name = 'inapp_payments';
    protected $_id = 'id';

    function getAllInappPaymentByChap($chap_Id)
    {
        $sql = $this->select()
                ->where('chap_id=?',$chap_Id)
                ->order('nexva_app_id desc');
        return $this->fetchAll($sql);
    }
    
    function getAllInappPayment()
    {
        $sql = $this->select()
                ->order('date_transaction desc');
        return $this->fetchAll($sql);
    }
    
    function getAllInappPaymentWithSkipPhoneNumber($phoneNumbers)
    {
        $sql = $this->select();
            foreach ($phoneNumbers as $pNumber){
                $sql->where('mobile_no  <>? ',$pNumber) ;  
            }             
                $sql->where('nexva_app_id !=?','0');
                $sql->order('date_transaction desc');
        return $this->fetchAll($sql);
    }
    
      
    public function getRevenue($startDate, $endDate){
        $select= $this->select()
                      ->setIntegrityCheck(false)
                      ->from(array('ip'=>$this->_name),array('round(sum(ip.price),2) as sum'))
                      ->join(array('tm' => 'theme_meta'), 'ip.chap_id = tm.user_id', array('channel'=>'tm.meta_value'))
                      ->where("ip.mode='live'")
                      ->where("status='success'")
                      ->where("tm.meta_name='WHITELABLE_SITE_NAME'")
                      ->where("ip.price < 5")
                      ->where("ip.mobile_no not in('081235356533','81235356533')")
                      ->where("ip.date_transaction between '".$startDate."' and '".$endDate."'")
                      ->group("ip.chap_id");
        return $this->fetchAll($select);              
                      

    }
    
    public function getPaymentReceivedList($startDate, $endDate){
        $select= $this->select()
                      ->setIntegrityCheck(false)
                      ->from(array('ip'=>$this->_name),array('date'=>'date_transaction','credit'=>'price','nexva_earn'=>'round(( (price/100)*p.payout_nexva),2)','cp_earn'=>'round(( (price/100)*p.payout_cp),2)','chap_earn'=>'round(( (price/100)*p.payout_chap),2)','mobile_no'=>'mobile_no','trans_id'=>'trans_id'))
                      ->join(array('tm' => 'theme_meta'), 'ip.chap_id = tm.user_id', array('meta_value as channel'))
                      ->join(array('u' => 'users'), 'ip.chap_id = u.id', array())
                      ->join(array('p' => 'payouts'), 'p.id = u.payout_id')                
                      ->where("ip.mode='live'")
                      ->where("ip.status='success'")
                      ->where("tm.meta_name='WHITELABLE_SITE_NAME'")
                      ->where("ip.price < 5 ")
                      ->where("ip.mobile_no not in('081235356533','81235356533')")
                      ->where("ip.date_transaction between '".$startDate."' and '".$endDate."'")
                      ->order("ip.date_transaction desc");
        return $this->fetchAll($select);              
                      

    }

}
?>