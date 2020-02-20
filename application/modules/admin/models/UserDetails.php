<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 6/12/13
 * Time: 5:41 PM
 * To change this template use File | Settings | File Templates.
 */
class Admin_Model_UserDetails extends Zend_Db_Table_Abstract {

    protected $_id = 'id';
    protected $_name = 'users';

    function __construct() {
        parent::__construct ();

    }

    public function getUserDetails($userID)
    {
        $user_sql = $this   ->select()
            ->setIntegrityCheck()
            ->from('users')
            ->where('users.id = ' . $userID)
            ->query()
            ->fetchAll();
        return $user_sql;
    }

    public function updatePayout($userID,$payoutID)
    {
        $this->update(array(
            "payout_id" => $payoutID
        ),"id = ".$userID);

        return true;
    }

    function getActiveChaps()
    {
        $sql =  $this   ->select()
                        //->setIntegrityCheck(false)
                        ->from(array('u'=>'users'))
                        ->where('u.status = ?',1)
                        ->where('u.type = ?','CHAP')
                        ;
        //return $sql->assemble();
        return $this->fetchAll($sql);
    }

}

/*$this->update(array(
    "name"         =>   $payout['name'],
    "description"   =>  $payout['description'],
    "payout_cp"      => $payout['payout_cp'],
    "payout_nexva"   => $payout['payout_nexva'],
    "payout_chap"   =>  $payout['payout_chap'],
    "payout_superchap"   =>  $payout['payout_superchap']

),"id = ".$id);*/