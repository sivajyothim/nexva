<?php

class Nexva_View_Helper_ApplicationId extends Zend_View_Helper_Abstract {
	
	public function ApplicationId($productId) {
		
		$config = Zend_Registry::get ( 'config' );
		$key = $config->nexva->application->salt;
		
		$idString = strtoupper( md5 ( $key . $productId ) );
		
		$one = substr($idString, 0, 4);
		$two = substr($idString, 4, 4);
		$three = substr($idString, 8, 4);
		$four = substr($idString, 12, 4);
		$five = substr($idString, 16, 4);
	  	$six = substr($idString, 20, 4);
		$seven = substr($idString, 24, 4);
		$eight = substr($idString, 28, 4);
	
		return $one.'-'.$two.'-'.$three.'-'.$four.'-'.$five.'-'.$six.'-'.$seven.'-'.$eight;
		
	}

}