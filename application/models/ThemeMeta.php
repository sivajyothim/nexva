<?php
/**
 *
 * @copyright   neXva.com
 * @author      Cheran@nexva.com
 * @package     cp
 *
 */
class Model_ThemeMeta extends Nexva_Db_EAV_EAVModel {
    protected  $_name = "theme_meta";
    protected  $_id  = "id";

    protected $_metaModel = "Model_ThemeMeta";
    protected $_entityIdColumn = "user_id";

    function  __construct() {
        parent::__construct();
    }

    function getThemeMeta($userid){
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'THEME_META_GET_THEME_META'.$userid;
        if (($theme_meta = $cache->get($key)) !== false)
        {
        	return $theme_meta;
        }

        $meta   =   $this->fetchAll('user_id ='.$userid,'id')->toArray();

        foreach($meta as $key=>$value){
               $theme_meta[$value['meta_name']] =   $value['meta_value'];
        }
        
        $theme_meta['WHITELABLE_USER_ID'] = $userid;
        
        $cache->set($theme_meta, $key, 3600);
        
        return $theme_meta;
    }
    
    public function getThemeByHostName ($host)
    {
        
        
     $cache  = Zend_Registry::get('cache');
        $key    = 'THEME_META_GET_THEME_BY_HOST_NAME'.strtoupper(strtr($host, '.', '_'));
        if (($userid = $cache->get($key)) !== false)
        {
               if ($userid) {
                   return $this->getThemeMeta($userid);    
                } else {
                   return false;
                }
        }
        
        $select = $this->select(false)
            ->from('theme_meta', array('user_id'))
            ->where('meta_name = "WHITELABLE_URL_WEB"')
            ->where('is_partner IS NULL')
            ->where('meta_value = ?', $host)
            ->limit(1);
        $userid = $select->query()->fetchColumn('user_id');
        
        $cache->set($userid, $key, 3600);
        
        if ($userid) {
            return $this->getThemeMeta($userid);    
        } else {
            return false;
        }
        
    }    
    
    
     public function getUserIdByMobileHost($host)
    {

       	
       	$cache  = Zend_Registry::get('cache');
       	$key    = 'THEME_META_MOBILE_HOST'.strtoupper(strtr($host, '.', '_'));
       	if (($userid = $cache->get($key)) !== false)
       	{
       		return $userid;
       	}
       	
       	$select = $this->select()
       	->setIntegrityCheck(false)
       	->from('theme_meta', array('user_id'))
       	->where('meta_name = "WHITELABLE_URL"')
       	->where('is_partner = 1')
       	->where('meta_value = ?',$host )
       	->limit(1);
       	
       	$userid   = $select->query()->fetchColumn('user_id');
       	
       	$cache->set($userid, $key, 3600);
       	
       	return	$userid;
       	
    }  
    
    public function getThemeByHostNameForPartner($host) {
        
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'THEME_META_'.$host;
        if (($userid = $cache->get($key)) !== false)
        {
        	return $userid;
        }
        
        $select = $this->select()
        	->setIntegrityCheck(false)
            ->from('theme_meta', array('user_id'))
            ->where('meta_name = "WHITELABLE_URL_WEB"')
            ->where('is_partner = 1')
            ->where('meta_value = ?',$host )
            ->limit(1);
            
        $userid   = $select->query()->fetchColumn('user_id');
        
        $cache->set($userid, $key, 3600);
        
       	return	$userid; 
    }
    
     public function getUrlByHostNameForDeveloper($host) {
         
 
       	$cache  = Zend_Registry::get('cache');
       	$key    = 'THEME_META_'.strtoupper(strtr($host, '.', '_'));
       	if (($userid = $cache->get($key)) !== false)
       	{
       		if($userid == 'none')
       			return '';
       		else 
       		    return $userid;
       	}
       	 
       	$select = $this->select()
       	->setIntegrityCheck(false)
       	->from('theme_meta', array('user_id'))
       	->where('meta_name = "WHITELABLE_URL_DEV_PORTAL"')
       	->where('meta_value = ?',$host )
       	->limit(1);
       	
       	$userid   = $select->query()->fetchColumn('user_id');
       	
       	if($userid == '')
       		$userid = 'none';
       	
       	$cache->set($userid, $key, 3600);
       	
       if($userid == 'none')
       			return '';
       		else 
       		    return $userid;
       	
       	
    }
    
    public function getThemeMetaForPartner($userid){
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'THEME_META_GET_THEME_META_FOR_PARTNER'.$userid;
        if (($theme_meta = $cache->get($key)) !== false)
        {
        	return $theme_meta;
        }
        

        $meta   =   $this->fetchAll('user_id ='.$userid,'id')->toArray();

        foreach($meta as $key=>$value){
        	
            $theme_meta[$value['meta_name']] =   $value['meta_value'];
               
        }
        
        $theme_meta['WHITELABLE_USER_ID'] = $userid;
        
        $cache->set($theme_meta, $key, 3600);
        
        return $theme_meta;
    }
    
    
    //This function is used get if the CHAP is a new one)for new template_
    public function checkIsPartner($host , $chapId)
    {   
          $cache  = Zend_Registry::get('cache');
        $key    = 'THEME_META_CHECK_IS_PARTNER'.$chapId.strtoupper(strtr($host, '.', '_'));
        
        if (($row = $cache->get($key)) !== false)
        {
        	if($row != 'none')
        	{
        		if($row->is_partner == 1)
        		{
        			$isPartner = true;
        		}
        		else
        		{
        			$isPartner = false;
        		}
        	}
        	
        }
        
        $selectSql   = $this->select(); 
        $selectSql->from($this->_name, array('user_id','is_partner'))
                  ->where('meta_name = ?','WHITELABLE_URL_WEB')
                  ->where('user_id = ?',$chapId)
                  ->where('meta_value = ?',$host);
        
        $row = $this->fetchRow($selectSql);
        
        $isPartner = false;
        
        if($row)
        {
            if($row->is_partner == 1)
            {
                $isPartner = true;
            }
            else
            {
                $isPartner = false;
            }
        } else {
            $row = 'none';
        }
        
        $cache->set($row, $key, 3600);
        
        return $isPartner;
      
        
    }

    function setIsPartner($chapId,$metaValue,$host)
    {
        $data = array(
            'is_partner'      => '1'
        );
        $this->update($data,array('user_id = ?' => $chapId,'meta_name = ?' =>$metaValue,'meta_value = ?' =>$host));
    }

   function getMetaValue($userId,$metaName)
    {
        $sql = $this->select();
        $sql->from(array('tm'=>$this->_name),array('tm.meta_value'))
            ->where('meta_name = ?',$metaName)
            ->where('user_id = ?',$userId)
            ;
        return $this->fetchAll($sql);
    }
}
?>
