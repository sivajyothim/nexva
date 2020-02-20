<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 11/12/13
 * Time: 4:46 PM
 * To change this template use File | Settings | File Templates.
 */
class Pbo_Model_Devices extends Zend_Db_Table_Abstract
{
    protected $_name = 'devices';
    protected $_id = 'id';

    function getDeviceDetails($deviceId)
    {
        $sql = $this->select();
        $sql    ->from(array('d'=>$this->_name))
                ->where('d.id = ?',$deviceId)
                ;
        return $this->fetchAll($sql);
    }

    function searchDevicesByPhrase($phrase){
        $sql    = $this->select();
        $sql    ->from(array('d'=>$this->_name))
                ->where('d.brand LIKE ? ','%'.$phrase.'%')
                ->orWhere('d.model LIKE ? ','%'.$phrase.'%')
                ;
        return $this->fetchAll($sql);
    }
}
?>