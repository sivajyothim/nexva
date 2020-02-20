<?php

class Partner_Model_Queue extends Zend_Db_Table_Abstract
{
    protected $_name = 'web_to_mobile_queue';
    protected $_id = 'id';
            
    public function addProducttoDownloadQueue($appId, $userId, $chapId)
    {
   	  	   
        $values = array(
            "product_id"	=> $appId,
            "user_id"   	=> $userId,
            "chap_id"       => $chapId
        );
    	
        
        $check_queue = $this->fetchRow($this->select()->where("product_id=?", $appId)->where("user_id=?", $userId)->where("chap_id=?", $chapId));
    	
        if(is_null($check_queue))
                $this->insert($values);
        
    }
    
    public function listDownloadQueue($userId, $chapId, $limit, $offset) {
        
         $select = $this->select()
                        ->from('web_to_mobile_queue')
                        ->setIntegrityCheck(false)  
                        ->join(array('p' => 'products'), 'web_to_mobile_queue.product_id = p.id', array('p.name','p.id as product_id','p.price','p.user_id','p.thumbnail'))  
     					->where('web_to_mobile_queue.user_id ='. $userId)
                        ->where('web_to_mobile_queue.chap_id ='. $chapId)
                        ->limit($limit, $offset);
                        
               
        $buildInfo = $this->fetchall($select)->toArray();

        
        if(count($buildInfo) > 0)
            return $buildInfo;
        else 
            return false;
    }
    
    public function removeDownlaodedItem($userId, $chapId)
    {
        $rowsAffected = $this->delete('user_id = '. $userId . ' AND chap_id =' . $chapId);
        
        if ($rowsAffected > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    
    
}