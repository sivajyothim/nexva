<?php

class Pbo_Model_WlPages extends Zend_Db_Table_Abstract
{
    protected $_name = 'wl_pages';
    protected $_id = 'id';
    
     /**
     * Add Web Page     
     * @param - $title
     * @param - $pageText description,
     * @param - $category category(this will be used in future to categorize pages),
     * @param - $status,
     * @param - $chapId,    
     */
    public function addPage($title,$pageText,$category,$status,$chapId,$language)
    {
        
        $data = array
                (
                    'title' => $title,
                    'page_text' => $pageText,
                    'category' => $category,
                    'status' => $status,
                    'chap_id' => $chapId,           
                    'date_added' => new Zend_Db_Expr('NOW()'),
                    'language_id' => $language
                );
        
       $id =  $this->insert($data);
       return $id;
    }
    
    
    /**
     * Returns all the pages peratains to a CHAP
     * @param - $chapId    
     */
    public function getPages($chapId)
    {        
        $selectSql   = $this->select(); 
        $selectSql  ->from(array('wlp' => $this->_name), array('wlp.id','wlp.title'))
                    ->setIntegrityCheck(false)
                    ->join(array('wlmi' => 'wl_menu_items'),'wlp.id = wlmi.page_id',array('wlp.language_id'))
                    ->where('wlp.chap_id = ?',$chapId)
                    ->where('wlp.status = ?',1)
                    ->order('wlp.date_added DESC');
        
        return $this->fetchAll($selectSql);
    }
    
    
    /**
     * Returns details of a page
     * @param - $chapId 
     * $parma - $pageId   
     */
    public function getPageDetailsbyId($chapId, $pageId)
    {        
        $selectSql   = $this->select(); 
        $selectSql->from($this->_name, array('id','title', 'page_text', 'category', 'status','language_id'))
                    ->where('chap_id = ?',$chapId)
                    ->where('id = ?',$pageId);

        return $this->fetchRow($selectSql);
        
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
    public function updatePage($title, $pageText, $category, $status, $chapId, $pageId, $language)
    {   
        $data = array(                        
                        'title' => $title,
                        'page_text' => $pageText,
                        'category' => $category,
                        'status' => $status,
                        'language_id' => $language
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
    
    
    public function getAllPages($chap_id)
    {
        $pageUserSql = $this->select();
        $pageUserSql->from(array('wlp' => $this->_name), array('wlp.id', 'wlp.title', 'wlp.category', 'wlp.date_added', 'wlp.status'))
                    ->setIntegrityCheck(false)
                    ->join(array('l' => 'languages'),'wlp.language_id = l.id',array('l.name AS language'))
                    ->where('wlp.chap_id = ?', $chap_id)
                    ->order('wlp.date_added DESC')
                    //->group('title')
                    ;
            //->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
        return $pageUserSql;
        
    }


    public function deletePage($chap_id, $chapPageId)
    {
        $rowsAffected = $this->delete( array('id = ?' => $chapPageId, 'chap_id = ?' => $chap_id));
        
        if($rowsAffected > 0)
        {
            return  TRUE;

        }
        else
        {
            return FALSE;
        }

    }

    public function updatePublishedState($chapId, $pageId, $status)
    {
        $data = array('status' => $status);
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

    function getPagesByChapID($chap_id)
    {
        $sql        = $this->select();
        $sql        ->from(array('w' => $this->_name))
                    ->where('w.chap_id = ?',$chap_id)
                    ->where('w.title = ?','Terms & Conditions');
        //return $sql->assemble();
        return $this->fetchAll($sql);
    }


    function getPagesByLanguageId($chap, $language){

        $sql =  $this->select();
        $sql    ->from(array('w' => $this->_name),array('w.title','w.id'))
                ->where('w.chap_id = ?', $chap )
                ->where('w.language_id = ?', $language)
                ->where('w.status = ?',1)
                ;
        return $this->fetchAll($sql);

    }

}
