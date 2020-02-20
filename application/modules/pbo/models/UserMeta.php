<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 10/11/13
 * Time: 12:27 PM
 * To change this template use File | Settings | File Templates.
 */
class Pbo_Model_UserMeta extends Nexva_Db_EAV_EAVModel
{
    protected $_name = 'user_meta';
    protected $_id = 'id';

    function getVenderName($userId)
    {
        $sql    =$this->select()
                ->from(array('um'=>$this->_name),array('um.meta_value'))
                ->where('um.user_id = ?', $userId)
                ->where('um.meta_name = ?', 'COMPANY_NAME')
                ;
        $userDetals =  $this->fetchRow($sql);
        if($userDetals && count($userDetals)>0 && !is_null($userDetals))
        {
            return $userDetals->meta_value;
        }
        else
        {
            return NULL;
        }
    }
}

