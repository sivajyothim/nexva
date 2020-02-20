<?php
/**
 * A view helper to print the CDN URL 
 *
 * @author chathura
 */

class Nexva_View_Helper_Cdn extends Zend_View_Helper_Abstract {
    
    public function cdn($contentType) {
//		echo $contentType;exit;
		if ($contentType == 'product_images') {

			$config = Zend_Registry::get ( "config" )->nexva->application->product_images_domain;
		}
		
		if ($contentType == 'site_assets') {
                   // echo $contentType;exit;
                   //print_r(Zend_Registry::get ( "config" ));
			$config = Zend_Registry::get ( "config" )->nexva->application->site_assets_domain;
                           
		}
		
        if ($contentType == 'jquiry') {

            $config = Zend_Registry::get ( "config" )->nexva->application->googleapis_domain->jquiry;

        }
        
        if ($contentType == 'jquiryui') {

            $config = Zend_Registry::get ( "config" )->nexva->application->googleapis_domain->jquiryUi;

        }
		
		if ($contentType == 'jquirymin') {
			
			$config = Zend_Registry::get ( "config" )->nexva->application->googleapis_domain->jquiry_min;
		
		}
    //$protocol = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'off' || $_SERVER['HTTP_X_FORWARDED_PORT'] == 443) ? "https://" : "http://";
$protocol = "http://";
        $url = $protocol.$config;
        return $url;
    }
}

