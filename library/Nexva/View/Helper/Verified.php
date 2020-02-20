<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 8/13/13
 * Time: 1:39 PM
 * To change this template use File | Settings | File Templates.
 */
class Nexva_View_Helper_Verified extends Zend_View_Helper_Abstract {

    public function verified($user_id)
    {
        $userMeta  = new Model_UserMeta();
        return $userMeta->getVerified($user_id);
    }
}