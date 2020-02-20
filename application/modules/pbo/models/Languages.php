<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 9/16/13
 * Time: 5:39 PM
 * To change this template use File | Settings | File Templates.
 */

class Pbo_Model_Languages extends Zend_Db_Table_Abstract
{
    protected $_name = 'languages';
    protected $_id = 'id';

    public function getAllLanguages()
    {
        $sql = $this->select();
        $sql ->from(array('l' => $this->_name))
             ->where('status = ?',1)
             ->order('common_name ASC');
        return $this->fetchAll($sql);
    }
}