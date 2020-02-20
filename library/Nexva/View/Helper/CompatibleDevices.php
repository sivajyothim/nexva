<?php

class Nexva_View_Helper_CompatibleDevices extends Zend_View_Helper_Abstract {
	
	public function CompatibleDevices($productId) {
		
		$productModel = new Model_Product ( );
		$productSupportedPlatforms = $productModel->getSupportedPlatforms( $productId );
		$platform = '';
		
		$config = Zend_Registry::get ( "config" )->nexva->application->site_assets_domain;
		$url = 'http://'.$config;
	
		foreach ( $productSupportedPlatforms as $supportedPlatforms ) {

			
		$platform .=  "<img src='".$url."/web/images/platforms/".$supportedPlatforms->id.".png' title='" . $supportedPlatforms->name . "' 
		  alt='".$supportedPlatforms->name."' />";
		
		}
           
            
        return $platform;

 
	
	}

}