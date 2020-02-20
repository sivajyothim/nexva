<?php

class Nexva_View_Helper_PublishNow extends Zend_View_Helper_Abstract {
	
	public function PublishNow($productId) {
		
		$productBuilds = new Model_ProductBuild ( );
		return $productBuilds->getBuildsByProductId ( $productId );
	
	}

}


?>