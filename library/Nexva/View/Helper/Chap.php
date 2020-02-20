<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 8/14/13
 * Time: 3:23 PM
 * To change this template use File | Settings | File Templates.
 */
class Nexva_View_Helper_Chap extends Zend_View_Helper_Abstract {

    public function chap($chap)
    {
        $userModel = new Model_User();
        $user = $userModel->getCHAPbyID($chap);
        return $user;
        //return $chap;
    }
}