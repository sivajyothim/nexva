<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 5/12/14
 * Time: 2:34 PM
 * To change this template use File | Settings | File Templates.
 */
class Pbo_Model_ChapCategories extends Zend_Db_Table_Abstract
{
    protected $_name = 'chap_categories';
    protected $_id = 'id';

    function __construct() {
        parent::__construct();
    }

    /**
     * @param $chapId
     * @return parent categories for particular CHAP
     */
    function getCategories($chapId){
        $sql = $this->select();
        $sql    ->from(array('cc'=>$this->_name),array('cc.id AS chap_category_id','cc.chap_id','cc.status AS chap_category_status'))
                ->setIntegrityCheck(false)
                ->joinRight(array('c'=>'categories'),'cc.category_id = c.id AND cc.chap_id = '.$chapId)
                ->where('c.parent_id = 0')
                ;
        //echo $sql->assemble();die();
        return $this->fetchAll($sql);
    }

    function getSubCategoriesForParentCategory($parentId,$chapId){
        $sql =  $this->select()
                ->from(array('cc' => $this->_name),array('cc.id AS chap_category_id','cc.chap_id','cc.status AS chap_category_status'))
                ->setIntegrityCheck(false)
                ->joinRight(array('c'=>'categories'),'cc.category_id = c.id AND cc.chap_id = '.$chapId)
                ->where('c.parent_id =?',$parentId)
                ;
        //echo $sql->assemble();die();
        return $this->fetchAll($sql);
    }

    function addChapCategory($chapId,$categoryId){

        $sql = $this->select();
        $sql    ->from(array('cc' => $this->_name))
                ->where('cc.chap_id = ?',$chapId)
                ->where('cc.category_id = ?',$categoryId)
                //->where('cc.status = 1')
                ;
        $result = $this->fetchAll($sql);
        if(count($result) == 0){
            $data = array
            (
                'chap_id' => $chapId,
                'category_id' => $categoryId,
                'status' => 1
            );
            $this->insert($data);
        }else{
            $data = array
            (
                'status' => 1
            );
            $this->update($data,'chap_id ='.$chapId.' AND category_id = '.$categoryId);
        }
        return 'success';
    }

    function removeChapCategory($chapId,$categoryId){

        $sql = $this->select();
        $sql    ->from(array('cc' => $this->_name))
                ->where('cc.chap_id = ?',$chapId)
                ->where('cc.category_id = ?',$categoryId)
                ;
        $result = $this->fetchAll($sql);
        if(count($result) > 0){
            $data = array
            (
                'chap_id' => $chapId,
                'category_id' => $categoryId,
                'status' => 0
            );
            $this->update($data,'chap_id ='.$chapId.' AND category_id = '.$categoryId);
        }
    }

    /*function checkCategoryAssigned($chapId,$categoryId){
        $sql = $this->select();
        $sql    ->from(array('cc' => $this->_name))
                ->where('cc.chap_id = ?',$chapId)
                ->where('cc.category_id = ?',$categoryId)
                ->where('cc.status = 1')
        ;
        $result = $this->fetchAll($sql);
        if(count($result) > 0){
            return true;
        }
    }*/

    public function getAllChapCategories($chapId){
        $sql = $this->select()
                    ->from(array('cc'=>$this->_name))
                    ->setIntegrityCheck(false)
                    ->join(array('c'=>'categories'),'cc.category_id = c.id')
                    ->where('cc.chap_id = ?',$chapId)
                    ->where('cc.status = ?',1)
                    ->order('c.name')
                    ;
        return $this->fetchAll($sql);
    }
}