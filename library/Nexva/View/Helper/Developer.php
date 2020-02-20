<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 8/9/13
 * Time: 1:45 PM
 * To change this template use File | Settings | File Templates.
 */
class Nexva_View_Helper_Developer extends Zend_View_Helper_Abstract {

    public function developer($productIDs)
    {
        $arr =  explode(',', $productIDs);

        $product = new Model_Product();
        $userMeta  = new Model_UserMeta();

        $rowset = $product->find($arr[0]);
        $productRow = $rowset->current();
        $userMeta->setEntityId($productRow->user_id);
        $prod['user_meta']  = $userMeta;
        return $prod['vendor'] = $userMeta->COMPANY_NAME;
    }
}