<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 5/13/14
 * Time: 12:32 PM
 * To change this template use File | Settings | File Templates.
 */

class Pbo_Model_QelasyGradeApps extends Zend_Db_Table_Abstract
{
    protected $_name = 'qelasy_grade_apps';
    protected $_id = 'id';

    function __construct() {
        parent::__construct();
    }

    public function getQelasyGrades($appId, $chapId){
        $sql    = $this->select();
        $sql    ->from(array('qga'=>$this->_name),array('qga.id AS grade_app_id'))
                ->setIntegrityCheck(false)
                ->joinRight(array('qg'=>'qelasy_grades'),'qg.id = qga.grade_id AND qga.product_id = '.$appId)
                ->where('qg.status = 1')
                //->where('qg.chap_id = ?',$chapId)
                ;
        //echo $sql;die();
        return $this->fetchAll($sql);
    }

    /**
     * @param $productId
     * @param $gradeId
     * checks whether there is a record in the db with above parameters, if there is no record adds the record
     */
    public function assignQelasyGrade($productId,$gradeId){
        $sql    = $this->select();
        $sql    ->from(array('qga'=>$this->_name))
                ->where('qga.grade_id = ?',$gradeId)
                ->where('qga.product_id = ?',$productId)
                ;
        $result = $this->fetchAll($sql);

        $data = array(
            'product_id' => $productId,
            'grade_id'  => $gradeId
        );

        if(count($result) == 0){
            $this->insert($data);
        }
    }

    public function unAssignQelasyGrade($productId,$gradeId){
        /*$data = array
        (
            'chap_id' => $chapId,
            'category_id' => $categoryId,
            'status' => 0
        );
        $this->update($data,'chap_id ='.$chapId.' AND category_id = '.$categoryId);*/
        $this->delete('grade_id = '.$gradeId.' AND product_id = '.$productId);
    }

}