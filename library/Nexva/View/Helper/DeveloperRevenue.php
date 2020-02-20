<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 6/25/13
 * Time: 4:15 PM
 * To change this template use File | Settings | File Templates.
 */
class Nexva_View_Helper_DeveloperRevenue extends Zend_View_Helper_Abstract {

    /**
     * Returns DeveloperRevenue
     * @param Product ID $productIDs
     * @return revenue
     */
    public function developerRevenue($productIDs){
        //return 'Inside view Helper';
        //return $productIDs;
        $IDs = explode(',', $productIDs);
        //$IDs = array('14103','13375','14103');
        $productModel = new Admin_Model_Product();
        $revenue = 0;
        foreach($IDs as $ID)
        {
            //echo $ID;
            $price = $productModel->getProductPrice($ID);
            $revenue += $price[0]['price'];
        }
        //die();
        return $revenue;
    }
}