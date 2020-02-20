<?php

class Partner_Model_Platforms extends Zend_Db_Table_Abstract
{
    protected $_name = 'platforms';
    protected $_id = 'id';

    public function getAllPlatforms()
    {
        $sql = $this->select();
        $sql ->from(array('p' => $this->_name),array('p.id'));

        return $this->fetchAll($sql);
    }
}