<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 2/18/14
 * Time: 6:02 PM
 * To change this template use File | Settings | File Templates.
 */

class Pbo_Model_Campaigns extends Zend_Db_Table_Abstract {
    protected $_name = 'campaigns';
    protected $_id = 'id';

    public function getChapCampaign($chapId,$type){
        $sql = $this->select();
        $sql    ->from(array('c' => $this->_name))
                ->where('c.type = ?',$type)
                ->where('c.chap_id = ?',$chapId)
                ->order('c.created_date DESC');
        return $sql;
    }
}

