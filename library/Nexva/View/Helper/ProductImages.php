<?php
/**
 * A view helper to print the product images URL 
 *
 * @author chathura
 */

class Nexva_View_Helper_ProductImages extends Zend_View_Helper_Abstract {
    
    public function productImages() {
        $config = Zend_Registry::get ( "config" )->nexva->application->product_images_domain;
        $url = 'http://'.$config;
        return $url;
    }
}