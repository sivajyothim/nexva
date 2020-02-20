<?php

class Model_LanguageUsers extends Zend_Db_Table_Abstract
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
        $cache  = Zend_Registry::get('cache');
        $key    = 'GET_LANGUAGE_CODE_BY_CHAP_'.$chapId;
        if (($langCode = $cache->get($key)) !== false)
        {
            if($langCode == 'none')
            {
                return 'en';
            } 
            else 
            {
                return $langCode->code;
            }
        
        }
        
        
        $sql = $this->select();
        $sql->from(array('lu' => $this->_name), array())
            ->setIntegrityCheck(false)
            ->join(array('l' => 'languages'), 'lu.language_id = l.id', array('l.code'))
            ->where('lu.user_id = ?',$chapId)
            ->where('lu.status = ?', 1);        
         
        $langCode = $this->fetchRow($sql);
        
        if($langCode == '')
        	$langCode = 'none';
        
        $cache->set($langCode, $key, 3600);
        
        
        
           if($langCode == 'none')
            {
                return 'en';
            } 
            else 
            {
                return $langCode->code;
            }
    }
    
     /**
     * Returns all languages by chap
     * @param $chapId chap id 
     * @return : language array
     */
    public function getMultiLanguagesByChap($chapId)
    {        
        $sql = $this->select();
        $sql->from(array('lu' => $this->_name), array())
            ->setIntegrityCheck(false)
            ->join(array('l' => 'languages'), 'lu.language_id = l.id', array('l.id','l.code','l.name','l.common_name','l.icon'))
            ->where('lu.user_id = ?',$chapId)
            ->where('lu.status = ?', 1);        
         
        //echo $sql->assemble();
        return $this->fetchAll($sql);
    }
}