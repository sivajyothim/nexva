<?php
/**
 * A simple helper to return the contents' created date.
 * if the $createdDate is not available in the product table then uset the PRODUCT_CHANGED in the productmeta.
 * 
 * @param int $productId, date $createdDate
 * @return date
 */

class Nexva_View_Helper_GetCreatedDate extends Zend_View_Helper_Abstract {

        public function GetCreatedDate($productId, $createdDate=''){

            if($createdDate == '')	{
            	  $productMeta    = new Model_ProductMeta();
            	  $productMeta->setEntityId($productId);
            	  if($productMeta->PRODUCT_CHANGED)
            	 	 $date = date('Y-m-d', $productMeta->PRODUCT_CHANGED);
            	  else 	
            	  	 $date = 'Not available';
            }
            else 
            	 $date = $createdDate;
            	
        return $date;

        }
        
}
