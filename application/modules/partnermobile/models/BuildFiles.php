<?php

class Partnermobile_Model_BuildFiles extends Zend_Db_Table_Abstract
{
    protected $_name = 'build_files';
    protected $_id = 'id';
    
    public function getFileByBuildId($buildId)
    {        
        $sql = $this->select();
        $sql->from($this->_name,array('filename'))
            ->where('build_id = ?',$buildId);        
         
        return $this->fetchRow($sql)->filename;
        
    }
}