<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 11/14/13
 * Time: 5:33 PM
 * To change this template use File | Settings | File Templates.
 */
class Pbo_Model_ThemeMeta extends Zend_Db_Table_Abstract
{
    protected $_name = 'theme_meta';
    protected $_id = 'id';

    function getMetaValueByMetaName($chapId,$metaName)
    {
        $sql =  $this->select();
        $sql    ->from(array('tm'=>$this->_name),array('tm.meta_value'))
                ->where('tm.user_id = ?',$chapId)
                ->where('tm.meta_name = ?',$metaName)
                ;
        return $result = $this->fetchAll($sql);
        //Zend_Debug::dump($result);die();
    }
}