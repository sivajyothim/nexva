<?php

class Nexva_View_Helper_Country extends Zend_View_Helper_Abstract {
    
    public function country($id) {
    	
        $county = new Model_Country;
        return ucfirst(strtolower($county->getCountry($id)));
        
    }

}