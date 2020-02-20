<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 7/19/13
 * Time: 2:21 PM
 * To change this template use File | Settings | File Templates.
 */

class Nexva_View_Helper_SupportedPlatforms extends Zend_View_Helper_Abstract {

    public function supportedPlatforms($productID){

        $productModel = new Model_Product();
        $platforms = $productModel->getSupportedPlatforms($productID);
        return $platforms;
    }
}