<?php

/**
 * Helper to get correct price points for airtel.
 * @author Rooban
 */
 
class Nexva_View_Helper_AirtelPricePoints extends Zend_View_Helper_Abstract {

  /**
   * Find the input price and return most highest price points
   * @param float $price
   */
  public function AirtelPricePoints($price) {

    $pricePoints = 0;
	$itemKey = 0;
	//Airtel price points array
    $array = array(5,10,15,20,30,40,50,80,100,130,150,180,200,250,300,350,400,600,800,1000);
	
	$minPricePoint = min($array);
	$maxPricePoint = max($array);
	

	if ($price >= $maxPricePoint) {
	  	$pricePoints = $maxPricePoint;
	} 
	elseif($price <= $minPricePoint){
		$pricePoints = $minPricePoint;
	}
	else { 
	  foreach ($array as $key => $value) {
		
		if ($minPricePoint >= $price) {
		  break;
		}

		$minPricePoint = $value;
		$itemKey = $key;
	  }
	  
	  $pricePoints = $array[$itemKey];
	}  

    return $pricePoints;

  }
}

?>
