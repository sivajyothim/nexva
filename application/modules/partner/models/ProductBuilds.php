<?php

class Partner_Model_ProductBuilds extends Zend_Db_Table_Abstract
{
    protected $_name = 'product_builds';
    protected $_id = 'id';
    
    public function getFileTypesByBuildId($buildId)
    {      
        $sql = $this->select();
        $sql->from($this->_name,array('build_type'))
            ->where('id = ?',$buildId);        
       //echo $sql->assemble(); die(); 
        return $this->fetchRow($sql)->build_type;
        
    }
    
    public function getPlatformIdByBuildId($buildId)
    {      
        $sql = $this->select();
        $sql->from($this->_name,array('platform_id'))
            ->where('id = ?',$buildId);        
       //echo $sql->assemble(); die(); 
        return $this->fetchRow($sql)->platform_id;
        
    }
    
    public function getPlatformsByAppIdAndLangId($appId)
    {
        $sql = $this->select();
        $sql    ->from($this->_name, array('platform_id'))
                ->where('product_id = ?',$appId);
        return $this->fetchAll($sql);
    }
    
    function getAvgApproved($productId)
    {
        $select =   $this->select()
                ->from(array('pb'=>$this->_name),array())
                ->where('pb.product_id = ?',$productId)
                ->where('pb.avg_approved = ?',1)
                ;
        $result = $this->fetchAll($select);
        $rowCount = count($result);
        if($rowCount > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}