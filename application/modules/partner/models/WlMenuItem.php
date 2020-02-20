<?php

class Partner_Model_WlMenuItem extends Zend_Db_Table_Abstract
{
    protected $_name = 'wl_menu_items';
    protected $_id = 'id';
    
    //Get all menu Items
    public function getAllMenuItems($chap_id, $language = null)
    {
        $menuUserSql   = $this->select();
        $menuUserSql->from(array('wlmi' => $this->_name),array('wlmi.id','wlmi.title', 'wlmi.target_window', 'wlmi.url', 'wlmi.type','wlmi.page_id', 'wlmi.status', 'wlmi.date_added'))
                    //->setIntegrityCheck(false)
                    //->join(array('wlp' => 'wl_pages'),'wlp.id = wlmi.page_id',array('wlp.language_id'))
                    ->where('wlmi.chap_id = ?', $chap_id)
                    ->where('wlmi.status =?', 1)
                    ->where('wlmi.language_id = ?',$language)
                    ->order('order ASC');

        //echo $menuUserSql->assemble();die();
        return $this->fetchAll($menuUserSql);
    }
}