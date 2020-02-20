<?php

class Partner_Model_WlPage extends Zend_Db_Table_Abstract
{
    protected $_name = 'wl_pages';
    protected $_id = 'id';
    
    public function getPageDetailsById($chapId, $pageId)
    {
        $selectSql   = $this->select(); 
        $selectSql->from($this->_name, array('id','page_text','title'))
                    ->where('chap_id = ?',$chapId)
                    ->where('status =?', 1)
                    ->where('id = ?',$pageId);
        
        return $this->fetchRow($selectSql);
    }
    
    public function getPageDetailsByTitle($chapId, $title, $language = 1)
    {
    	$selectSql   = $this->select();
    	$selectSql->from($this->_name, array('id','page_text','title'))
    	->where('chap_id = ?',$chapId)
    	->where('status =?', 1)
    	->where('language_id =?', $language)
    	->where('title = ?',$title);
    
    	return $this->fetchRow($selectSql);
    }
    
}