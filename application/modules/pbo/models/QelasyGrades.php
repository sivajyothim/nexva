<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 5/12/14
 * Time: 7:00 PM
 * To change this template use File | Settings | File Templates.
 */

class Pbo_Model_QelasyGrades extends Zend_Db_Table_Abstract
{
    protected $_name = 'qelasy_grades';
    protected $_id = 'id';

    public function getQelasyGrades($appId){
        $sql    = $this->select();
        $sql    ->from(array('qg'=>$this->_name))
                ->setIntegrityCheck(false)
                ->joinRight(array('qga'=>'qelasy_grade_apps'),'qg.id = qga.grade_id AND qga.product_id = '.$appId,array())
                ->where('qg.status = 1')
                ;
        //echo $sql->assemble();
        return $this->fetchAll($sql);
    }

    public function getQelasyGradesAndInstitutes($instituteId = null){

        $join = 'qg.instituteId = qi.id';
        if($instituteId && !('standard'==$instituteId)){
            $join = $join.' AND qg.countryId = qi.countryId AND qg.type = qi.type';
        }

        $sql    = $this->select();
        $sql    ->from(array('qg'=>$this->_name))
                ->setIntegrityCheck(false)
                ->joinLeft(array('qi'=>'qelasy_institutes'),$join,array('qi.name AS institiute_name'));
        if($instituteId){
            if('standard'==$instituteId){
                $sql->where('qi.id IS NULL');
            } else {
                //$sql->where('qi.id = ?',$instituteId);
                //$sql->where();
            }
        }
            $sql ->where('qg.status =?',1)
                ;
        //echo $sql->assemble();die();
        return $this->fetchAll($sql);
    }

    /**
     * get all qelasy grades
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getAllQelasyGrades(){
        $sql = $this->select();
        $sql    ->from(array('qg'=>$this->_name))
                ->setIntegrityCheck(false)
                ->joinLeft(array('qi'=>'qelasy_institutes'),'qg.instituteId = qi.id',array('qi.name AS institiute_name'))
                ;
        return $this->fetchAll($sql);
    }
}