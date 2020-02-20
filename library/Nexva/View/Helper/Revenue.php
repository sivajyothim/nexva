<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 6/12/13
 * Time: 10:27 AM
 * To change this template use File | Settings | File Templates.
 */

class Nexva_View_Helper_Revenue extends Zend_View_Helper_Abstract {

    /**
     * Returns Revenue
     * @param Product ID $productIDs
     * @return revenue;
     */
    public function revenue($productIDs){

        $ids = join(',',explode(',', $productIDs));     //return $ids;
        $productModel = new Admin_Model_Product();
        $prices = $productModel->getProductPrices($ids);
        //return count($prices);
        $revenue = 0;
        foreach($prices as $price){
            if($price['price'] > '0')
            {
                $revenue += $price['price'];
            }
        }
        return $revenue;
    }
}