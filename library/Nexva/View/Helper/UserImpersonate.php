<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 1/17/14
 * Time: 2:56 PM
 * To change this template use File | Settings | File Templates.
 */

/*
 * Checks whether a user have uploaded the apps
 */
class Nexva_View_Helper_UserImpersonate extends Zend_View_Helper_Abstract
{
    function UserImpersonate($userId)
    {
        //$userId = '31007';
        $productModel = new Admin_Model_Product();
        $products = $productModel->userHaveApps($userId);
        if(count($products) > 0){
            return true;
        }else{
            return false;
        }
    }
}