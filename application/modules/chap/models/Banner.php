<?php
class Chap_Model_Banner extends Zend_Db_Table_Abstract {
    
    protected $_name = 'chap_banners';
    protected $_id = 'id';
    
    
    /**
     * Add baner details- Chap
     * @param - $chapId,
     * @param - $image,
     * @param - $type (Web / Moblie),
     * @param - $url,
     * @param - $caption
     */
    public function addBanner($chapId,$image,$type,$url,$caption)
    {
        
        $data = array
                (
                    'chap_id' => $chapId,
                    'image' => $image,
                    'type' => $type,
                    'url' => $url,
                    'caption' => $caption,
                    'enabled' => 1,                    
                    'date_created' => new Zend_Db_Expr('NOW()')
                );
        
       $id =  $this->insert($data);
       return $id;
    }
    
     /**
     * Returns banners of a particular chap by type (web / mobile)
     * @param - $chapId,      
     * @param - $type (Web / Moblie)     
     */
    
    public function getChapBannersbyType($chapId,$type)
    {
        $bannerSql   = $this->select(); 
        $bannerSql->from($this->_name, array('image','caption','url'))
                    ->where('chap_id = ?',$chapId)
                    ->where('type = ?',$type)
                    ->where('enabled != ?',0)  
                    ->order('date_created DESC');         
           
         return $this->fetchAll($bannerSql);
    }
}
?>
