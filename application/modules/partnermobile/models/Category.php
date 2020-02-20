<?php

class Partnermobile_Model_Category extends Zend_Db_Table_Abstract
{
    protected $_name = 'categories';
    protected $_id = 'id';
    
    public function getDetailsById($categoryId)
    {        
        $sql = $this->select();
        $sql->from($this->_name,array('name','parent_id'))
            ->where('id = ?',$categoryId);        
         
        return $this->fetchRow($sql);
        
    }
    
    public function getNameId($categoryId)
    {        
        $sql = $this->select();
        $sql->from($this->_name,array('name'))
            ->where('id = ?',$categoryId);        
         
        return $this->fetchRow($sql)->name;
        
    }
    
}