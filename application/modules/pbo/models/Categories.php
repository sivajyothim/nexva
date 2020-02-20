<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 11/14/13
 * Time: 4:12 PM
 * To change this template use File | Settings | File Templates.
 */
class Pbo_Model_Categories extends Zend_Db_Table_Abstract
{
    protected $_name = 'categories';
    protected $_id = 'id';

    function getCategoryById($categoryId)
    {
        $sql = $this->select();
        $sql    ->from(array('c'=>$this->_name),array('c.name'))
                ->where('c.id = ?',$categoryId)
                //->where('c.parent_id != ?',0)
                ;
        return $this->fetchAll($sql);
    }

    function getAllCategories(){
        $sql = $this->select();
        $sql    ->from(array('c'=>$this->_name))
                ;
        return $this->fetchAll($sql);
    }

    function getMainCategories(){
        $sql = $this->select();
        $sql    ->from(array('c'=>$this->_name))
                ->where('c.parent_id = 0')
                ;
        return $this->fetchAll($sql);
    }

    function getSubCategoriesForParentCategory($parentId){
        $sql =  $this->select()
                ->from(array('c' => $this->_name))
                ->where('c.parent_id =?',$parentId)
                ;
        return $this->fetchAll($sql);
    }
}