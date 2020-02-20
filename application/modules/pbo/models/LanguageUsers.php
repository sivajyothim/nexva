<?php
/**
 * Created by PhpStorm.
 * User: viraj
 * Date: 9/1/14
 * Time: 6:08 PM
 */

class Pbo_Model_LanguageUsers extends Zend_Db_Table_Abstract
{
    protected $_name = 'language_users';
    protected $_id = 'id';

    public function getChapLanguages($chapId){

        $sql = $this->select();
        $sql    ->from(array('lu' => $this->_name))
                ->setIntegrityCheck(false)
                ->join(array('l' => 'languages'), 'lu.language_id = l.id', array('l.name'))
                ->where('lu.status = ?',1)
                ->where('lu.user_id =?',$chapId)
                ;
        return $this->fetchAll($sql);
    }
}