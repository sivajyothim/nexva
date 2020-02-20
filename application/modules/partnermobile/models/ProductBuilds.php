<?php

class Partnermobile_Model_ProductBuilds extends Zend_Db_Table_Abstract
{
    protected $_name = 'product_builds';
    protected $_id = 'id';
    
    public function getFileTypeByBuildId($buildId)
    {      
        $sql = $this->select();
        $sql->from($this->_name,array('build_type'))
            ->where('id = ?',$buildId);        
        //echo $sql->assemble();die();
        return $this->fetchRow($sql)->build_type;
        
    }
}