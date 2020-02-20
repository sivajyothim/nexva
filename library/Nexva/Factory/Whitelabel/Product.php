<?php
class Nexva_Factory_Whitelabel_Product {

    /**
     * Returns a constructed product model with all the chap specific data 
     * injected into it
     * @param Array $opts
     * @return Whitelabel_Model_Product
     */
    public static function getProductModel($opts = array()) {
        $productModel   = new Whitelabel_Model_Product();
        $productModel->chapId   = empty($opts['chapId']) ? null : $opts['chapId'];
        //get chap rules
        
        return $productModel;                
    }
} 