<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 5/22/14
 * Time: 2:17 PM
 * To change this template use File | Settings | File Templates.
 */
class Pbo_Model_QelasyGradeCategories extends Zend_Db_Table_Abstract
{
    protected $_name = 'qelasy_grade_categories';
    protected $_id = 'id';

    function __construct() {
        parent::__construct();
    }

    /**
     * @param $gradeId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    function getCategories($gradeId, $chapId, $userType = null){
        $sql = $this->select();
        $sql    ->from(array('qgc'=>$this->_name),array('qgc.id AS grade_category_id','qgc.grade_id','qgc.status AS grade_category_status'))
                ->setIntegrityCheck(false)
                ->joinRight(array('c'=>'categories'),'qgc.category_id = c.id AND qgc.grade_id = '.$gradeId.' AND qgc.qelasy_user_type = '.$userType)
                ->join(array('cc'=>'chap_categories'),'cc.category_id = c.id',array())
                ->where('c.parent_id = ?',0)
                ->where('cc.chap_id = ?',$chapId)
                ->where('cc.status = ?',1)
                //->where('qgc.qelasy_user_type = ?',$userType)
                ->order('c.id ASC')
                ;
        /*$sql    ->from(array('cc'=>'chap_categories'))
                ->setIntegrityCheck(false)
                ->joinRight(array('c'=>'categories'),'cc.category_id = c.id')
                ->joinRight(array('qgc'=>'qelasy_grade_categories'),'cc.category_id = qgc.category_id AND qgc.grade_id = '.$gradeId)
                ->where('cc.status = ?',1)
                ->where('cc.chap_id = ?',$chapId)
                ->where('c.parent_id = 0')
                ;*/
        //echo $sql->assemble();die();
        return $this->fetchAll($sql);
    }

    function getSubCategoriesForParentCategory($parentId, $grade, $chapId = null, $userType = null){

        $sql =  $this->select()
                ->from(array('qgc' => $this->_name),array('qgc.id AS grade_category_id','qgc.grade_id','qgc.status AS grade_category_status'))
                ->setIntegrityCheck(false)
                ->joinRight(array('c'=>'categories'),'qgc.category_id = c.id AND qgc.grade_id = '.$grade.' AND qgc.qelasy_user_type = '.$userType)
                ->join(array('cc'=>'chap_categories'),'cc.category_id = c.id',array())
                ->where('c.parent_id =?',$parentId)
                ->where('cc.chap_id = ?',$chapId)
                ->where('cc.status = ?',1)
                //->where('qgc.qelasy_user_type = ?',1)
                ->order('c.id ASC')
                ;
        //echo $sql->assemble();die();
        return $this->fetchAll($sql);
    }

    function addGradeCategory($gradeId,$categoryId){

        $sql = $this->select();
        $sql    ->from(array('qgc' => $this->_name))
                ->where('qgc.grade_id = ?',$gradeId)
                ->where('qgc.category_id = ?',$categoryId)
                ;
        $result = $this->fetchAll($sql);
        if(count($result) == 0){
            $data = array
            (
                'grade_id' => $gradeId,
                'category_id' => $categoryId,
                'status' => 1,
                'qelasy_user_type' => 1
            );
            $this->insert($data);
        }else{
            $data = array
            (
                'status' => 1
            );
            $this->update($data,'grade_id ='.$gradeId.' AND category_id = '.$categoryId);
        }
        return 'success';
    }

    function removeGradeCategory($gradeId,$categoryId){

        $sql = $this->select();
        $sql    ->from(array('qgc' => $this->_name))
                ->where('qgc.grade_id = ?',$gradeId)
                ->where('qgc.category_id = ?',$categoryId)
                ;
        $result = $this->fetchAll($sql);

        //echo $categoryId,' - ',count($result),'<br/>';

        if(count($result) > 0){
            $data = array
            (
                //'grade_id' => $gradeId,
                //'category_id' => $categoryId,
                'status' => 0
                //'qelasy_user_type' => 1
            );

            //Zend_Debug::dump($data);
            $this->update($data,'grade_id ='.$gradeId.' AND category_id = '.$categoryId);
        }
    }

    function getNonGradeCategories($chapId , $userType){
        $sql = $this->select();
        $sql    ->from(array('qgc'=>$this->_name),array('qgc.id AS grade_category_id','qgc.grade_id','qgc.status AS grade_category_status'))
                ->setIntegrityCheck(false)
                ->joinRight(array('c'=>'categories'),'qgc.category_id = c.id AND qgc.qelasy_user_type = '.$userType)
                ->join(array('cc'=>'chap_categories'),'cc.category_id = c.id',array())
                ->where('c.parent_id = ?',0)
                ->where('cc.chap_id = ?',$chapId)
                ->where('cc.status = ?',1)
                ->order('c.id ASC')
                ;
        //echo $sql->assemble();die();
        return $this->fetchAll($sql);
    }

    function getNonGradeSubCategoriesForParentCategory($parentId, $userType, $chapId = null){

        $sql =  $this->select()
                ->from(array('qgc' => $this->_name),array('qgc.id AS grade_category_id','qgc.grade_id','qgc.status AS grade_category_status'))
                ->setIntegrityCheck(false)
                ->joinRight(array('c'=>'categories'),'qgc.category_id = c.id AND qgc.qelasy_user_type = '.$userType)
                ->join(array('cc'=>'chap_categories'),'cc.category_id = c.id',array())
                ->where('c.parent_id =?',$parentId)
                ->where('cc.chap_id = ?',$chapId)
                ->where('cc.status = ?',1)
                ->order('c.id ASC')
                ;
        //echo $sql->assemble();die();
        return $this->fetchAll($sql);
    }

    function addNonGradeCategory($userType, $categoryId){
        $sql = $this->select();
        $sql    ->from(array('qgc' => $this->_name))
                ->where('qgc.qelasy_user_type = ?',$userType)
                ->where('qgc.category_id = ?',$categoryId)
                ;
        $result = $this->fetchAll($sql);
        if(count($result) == 0){
            $data = array
            (
                'grade_id' => '',
                'category_id' => $categoryId,
                'status' => 1,
                'qelasy_user_type' => $userType
            );
            $this->insert($data);
        }else{
            $data = array
            (
                'status' => 1
            );
            $this->update($data,'qelasy_user_type ='.$userType.' AND category_id = '.$categoryId);
        }
        return 'success';
    }

    function removeNonGradeCategory($userType, $categoryId){

        $sql = $this->select();
        $sql    ->from(array('qgc' => $this->_name))
            ->where('qgc.qelasy_user_type = ?',$userType)
            ->where('qgc.category_id = ?',$categoryId)
        ;
        $result = $this->fetchAll($sql);

        if(count($result) > 0){
            $data = array
            (
                //'grade_id' => $gradeId,
                //'category_id' => $categoryId,
                'status' => 0
                //'qelasy_user_type' => 1
            );

            $this->update($data,'qelasy_user_type ='.$userType.' AND category_id = '.$categoryId);
        }
    }
}