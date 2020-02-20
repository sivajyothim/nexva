<?php

class Admin_Model_Payout extends Zend_Db_Table_Abstract {
	
	protected $_id = 'id';
	protected $_name = 'payouts';
	
	function __construct() {
		parent::__construct ();
	
	}
	
	function getPayoutRoyalties() {
		
		$payout = $this->select () ->from('payouts', array('id','name'))->order('payouts.id')->query ()->fetchAll ();
		
	
		foreach ($payout as $value){
			$payoutArray[$value->id] = $value->name;
			
		}
				
		return $payoutArray;
	
	}
	
   
    function createPayoutRoyalties($payout) {
              
                $values = array(
                "name"         =>   $payout['name'],
                "description"   =>  $payout['description'],
                "payout_cp"      => $payout['payout_cp'],
                "payout_nexva"   => $payout['payout_nexva'],
                "payout_chap"   =>  $payout['payout_chap'],
                "payout_super_chap"   =>  $payout['payout_super_chap'],
                "cp_payout_description"   =>  $payout['cp_payout_description']
        );
        $res  =   $this->insert($values);
                
        return $payoutArray;
    
    }
    
    function getPayoutRoyaltiesById($id) {
    
              $select = $this->select()
              ->setIntegrityCheck(false)
              ->from('payouts')
              ->where('id =  ' . $id);
               
              return $this->fetchRow($select);
        
    }
    
    function updatePayoutRoyalties($id, $payout)    
    {
    	    
              $this->update(array(
                "name"         =>   $payout['name'],
                "description"   =>  $payout['description'],
                "payout_cp"      => $payout['payout_cp'],
                "payout_nexva"   => $payout['payout_nexva'],
                "payout_chap"   =>  $payout['payout_chap'],
                "payout_super_chap"   =>  $payout['payout_super_chap'],
                "cp_payout_description"   =>  $payout['cp_payout_description']

            ),"id = ".$id);
    	
        
    }
    
	public function getPayoutDetails()
    {
        $payout_sql = $this ->select()
                            ->setIntegrityCheck()
                            ->from('payouts')
                            ->query()
                            ->fetchAll();
        return $payout_sql;
    }

}

