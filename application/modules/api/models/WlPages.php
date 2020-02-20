<?php

class Api_Model_WlPages extends Zend_Db_Table_Abstract
{
    protected $_name = 'wl_pages';
    protected $_id = 'id';
    
     /**
     * Returns CHAP specific text pages 
     * @param - $chapId,    
     */
    public function getAllPages($chapId)
    {
        $sql   = $this->select(); 
        $sql->from(array('wp' => $this->_name), array('wp.page_text'))                   
                    ->setIntegrityCheck(false)  
                    ->join(array('wl' => 'wl_menu_items'), 'wp.id = wl.page_id', array('wl.title'))  
                    ->where('wp.chap_id = ?',$chapId)
                    ->where('wp.status = ?',1)  
                    ->where('wl.type = ?', 'INTERNAL')
                    ->order('wl.order');
        
        
        
        return $this->fetchAll($sql)->toArray();
        
    }
}