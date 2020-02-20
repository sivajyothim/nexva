<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 7/11/13
 * Time: 11:56 AM
 * To change this template use File | Settings | File Templates.
 */

class Nexva_View_Helper_NewPriceCheck extends Zend_View_Helper_Abstract
{
    function NewPriceCheck($price,$id)
    {
        $productModel = new Admin_Model_Product();
        $priceResult = $productModel->checkPrice($price,$id);
        //Zend_Debug::dump($priceResult);die();
        return $priceResult;
    }
}