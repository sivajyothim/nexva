<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 9/11/13
 * Time: 2:29 PM
 * To change this template use File | Settings | File Templates.
 */

class Pbo_Model_Platforms extends Zend_Db_Table_Abstract
{
    protected $_name = 'platforms';
    protected $_id = 'id';

    public function getAllPlatforms()
    {
        $sql = $this->select();
        $sql ->from(array('p' => $this->_name),array('p.id'));

        return $this->fetchAll($sql);
    }

    public function getAllPlatformsAsc(){
        $sql    = $this->select();
        $sql    ->from(array('p' => $this->_name),array('p.id','p.name'))
                ->order('p.name ASC')
                ;
        return $this->fetchAll($sql);
    }
}