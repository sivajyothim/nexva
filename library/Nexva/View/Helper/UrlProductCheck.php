<?php


class Nexva_View_Helper_UrlProductCheck extends Zend_View_Helper_Abstract {


    public function UrlProductCheck($productId = null) {
    	if (!$productId) {
    	    return false;
    	}
        
    	$productBuild = new Model_ProductBuild();
		$isUrlProduct = $productBuild->isUrlProduct($productId);
		
		return $isUrlProduct;
		
    }

}

?>