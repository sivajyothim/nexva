<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 11/12/13
 * Time: 4:46 PM
 * To change this template use File | Settings | File Templates.
 */
class Pbo_Model_EcarrotUser extends Zend_Db_Table_Abstract
{
    protected $_name = 'ecarrot_user';
    protected $_id = 'id';


    function getEcarrotUser($from,$to){
        $sql    = $this->select();
        $sql    ->from(array('eu'=>$this->_name))
                ->where('eu.started_date between "'.$from.'" and "'.$to.'"');
        //Zend_Debug::dump($sql->__toString());die();
        return $this->fetchAll($sql);
    }

    function getEcarrotPaidUser($from,$to){
        $sql    = $this->select();
        $sql    ->from(array('eu'=>$this->_name))
                ->setIntegrityCheck(false)
                 ->join(array('erp' => 'ecarrot_recurring_payments'), 'erp.user_id = eu.ecarrot_user_id')
                ->where('erp.created_at between "'.$from.'" and "'.$to.'"');
                //->where('erp.profile_status =?','Active');
        //Zend_Debug::dump($sql->__toString());die();
        return $this->fetchAll($sql);
    }
}
?>