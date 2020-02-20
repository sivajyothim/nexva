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
        $this->insert($values);
    }
    
    public function getLanguageCodeByChap($chapId)
    {        
        $sql = $this->select();
        $sql->from(array('lu' => 'language_users'), array())
            ->setIntegrityCheck(false)
            ->join(array('l' => 'languages'), 'lu.language_id = l.id', array('l.code'))
            ->where('lu.user_id = ?',$chapId)
            ->where('lu.status = ?', 1);        
         
        $langCode = $this->fetchRow($sql);
        
        if($langCode)
        {
            return $langCode->code;
        }
        else
        {
            return 'en';
        }
    }
}