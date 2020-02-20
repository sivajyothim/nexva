<?php

class Partner_Model_LanguageUsers extends Zend_Db_Table_Abstract
{
    protected $_name = 'language_users';
    protected $_id = 'id';
    
    /**
     * Returns language code of the chap
     * @param $chapId chap id 
     * @return : language code
     */
    public function getLanguageCodeByChap($chapId)
    {        
        $sql = $this->select();
        $sql->from(array('lu' => $this->_name), array())
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
    
    
    /**
     * Returns language id of the chap
     * @param $chapId chap id 
     * @return : language id
     */
    public function getLanguageIdByChap($chapId)
    {        
        $sql = $this->select();
        $sql->from(array('lu' => $this->_name), array())
            ->setIntegrityCheck(false)
            ->join(array('l' => 'languages'), 'lu.language_id = l.id', array('l.id'))
            ->where('lu.user_id = ?',$chapId)
            ->where('lu.status = ?', 1)
            ->where('lu.default_language = ?', 1);
         
        
        $langRow = $this->fetchRow($sql);
        
        
     
        
        if($langRow)
        {
            return $langRow->id;
        }
        else
        {
            return 1;
        }
    }
}