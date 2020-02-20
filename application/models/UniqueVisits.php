<?php
/**
 * Created by PhpStorm.
 * User: viraj
 * Date: 10/20/14
 * Time: 1:47 PM
 */

class Model_UniqueVisits extends Zend_Db_Table_Abstract {

    protected $_name = 'unique_visits';
    protected $_id = 'id';

    public function isUnique($ip, $sessionId){

        $sql = $this->select()
                    ->from(array('uv'=>$this->_name))
                    ->where('uv.session_id = ?',$sessionId)
                    ->where('uv.ip = ?',$ip)
                    ;
        //echo $sql->assemble();
        $result = $this->fetchAll($sql);

        if(count($result)){
            return false;
        } else {
            return true;
        }

    }

    public function lastUpdatedTime($ip, $sessionId, $visitsTime){
        $sql = $this->select()
                    ->from(array('uv' => $this->_name),array('uv.updated_time'))
                    ->where('uv.session_id = ?',$sessionId)
                    ->where('uv.ip = ?',$ip)
                    ->where('uv.visit_time = ?',$visitsTime)
                    ;
        //echo $sql->assemble();die();
        return $this->fetchRow($sql);

    }

}