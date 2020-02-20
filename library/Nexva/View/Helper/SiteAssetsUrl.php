<?php
/**
 * A view helper to print the site Asserts URL 
 *
 * @author chathura
 */

class Nexva_View_Helper_SiteAssetsUrl extends Zend_View_Helper_Abstract {
	
	public function siteAssetsUrl() {
		$config = Zend_Registry::get ( "config" )->nexva->application->site_assets_domain;
		$url = 'http://'.$config;
		return $url;
	}
}

