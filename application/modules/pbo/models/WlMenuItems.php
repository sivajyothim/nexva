<?php

class Pbo_Model_WlMenuItems extends Zend_Db_Table_Abstract
{
    protected $_name = 'wl_menu_items';
    protected $_id = 'id';
    
     /**
     * Add Menu item - White Label
     * @param - $title,
     * @param - $targetWindow,
     * @param - $type (EXTERNAL / INTERNAL),
     * @param - $url,
     * @param - $pageId
     * @param - $order
     * @param - $chapId
     * @param - $status
     * @param - $caption
     */
    public function addMenuItem($title, $targetWindow, $menuType, $url, $pageId, $order, $chapId, $status, $language)
    {
        $data = array
                (
                    'title' => $title,
                    'target_window' => $targetWindow,
                    'type' => $menuType,
                    'url' => $url,
                    'page_id' => $pageId,
                    'order' => $order,
                    'chap_id' => $chapId,
                    'status' => $status,          
                    'date_added' => new Zend_Db_Expr('NOW()'),
                    'language_id' => $language
                );
        
       $id =  $this->insert($data);
       return $id;
    }
    
    
    //Get all menu Items
    public function getAllMenus($chap_id)
    {
        $menuUserSql   = $this->select();
        $menuUserSql->from($this->_name,array('id','title', 'target_window', 'order', 'type','chap_id', 'status', 'date_added'))
        ->where('chap_id = ?', $chap_id)
        ->order('date_added DESC');

        return $menuUserSql;
    }
    
    /**
     * Returns a details of a menu item  
     * @param - $chapId
     * @param - $menuId
     */
    public function getMenuDetailsbyId($chapId, $menuId)
    {
        $selectSql   = $this->select(); 
        $selectSql  ->from(array('wlmi' => $this->_name), array('wlmi.id','wlmi.title', 'wlmi.target_window', 'wlmi.url','wlmi.type','wlmi.chap_id', 'wlmi.page_id', 'wlmi.status'))
                    ->setIntegrityCheck(false)
                    ->join(array('wlp' => 'wl_pages'),'wlp.id = wlmi.page_id',array('wlp.language_id'))
                    ->join(array('l' => 'languages'),'wlp.language_id = l.id',array('l.name AS language'))
                    ->where('wlmi.chap_id = ?',$chapId)
                    ->where('wlmi.id = ?',$menuId)
                    ;
        
        return $this->fetchRow($selectSql);
    }
    
    
    /**
     * Update a menu item
     * @param - $title,
     * @param - $targetWindow,
     * @param - $type (EXTERNAL / INTERNAL),
     * @param - $url,
     * @param - $pageId
     * @param - $chapId
     * @param - $status
     * @param - $caption  
     */
    public function updateMenuItem($title, $targetWindow, $menuType, $url, $webPageId, $chapId, $status, $menuId, $language)
    {  
        $data = array(                        
                        'title' => $title,
                        'target_window' => $targetWindow,
                        'type' => $menuType,
                        'url' => $url,
                        'page_id' => $webPageId,
                        'status' => $status,
                        'language_id' => $language
                    );

        $where = array('id = ?' => $menuId, 'chap_id = ?' => $chapId);

        $rowsAffected = $this->update($data,$where);
       
        if($rowsAffected > 0)
        {           
            return  TRUE;

        }
        else
        {
            return FALSE;
        }
    }
        
    //delete menu a menu item
    public function deleteMenu($chap_id, $chapMenuId)
    {
        $rowsAffected = $this->delete( array('id = ?' => $chapMenuId, 'chap_id = ?' => $chap_id));

        if($rowsAffected > 0)
        {
            return  TRUE;

        }
        else
        {
            return FALSE;
        }

    }

    //update the status of a menu item
    public function updatePublishedState($chapId, $menuId, $status)
    {
        $data = array('status' => $status);
        $where = array('id = ?' => $menuId, 'chap_id = ?' => $chapId);

        $rowsAffected = $this->update($data,$where);
        if($rowsAffected > 0)
        {
            return  TRUE;

        }
        else
        {
            return FALSE;
        }
    }
    
    
    
    /**
     * Update a page
     * @param - $title
     * @param - $pageText description,
     * @param - $category category,
     * @param - $status,
     * @param - $chapId,  
     * @param - $pageId,  
     */
    public function updatePage($title, $pageText, $category, $status, $chapId, $pageId)
    {   
        $data = array(                        
                        'title' => $title,
                        'page_text' => $pageText,
                        'category' => $category,
                        'status' => $status
                    );
        $where = array('id = ?' => $pageId, 'chap_id = ?' => $chapId);

        $rowsAffected = $this->update($data,$where);
       
        if($rowsAffected > 0)
        {
            return  TRUE;

        }
        else
        {
            return FALSE;
        }
    }
    
    
}