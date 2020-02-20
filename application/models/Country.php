<?php
class Model_Country extends Zend_Db_Table_Abstract {


    protected $_id = 'id';
    protected $_name = 'countries';

    function __construct() {
        parent::__construct();
    }


    function getAllCountries() {
        $countries  =   $this->fetchAll();
        foreach($countries as $country) {
            $allCountries[$country['id']] = $country['name'];
        }
        return $allCountries;
    }
    
    function getCountry($id)    {

    	$rowset = $this->find($id);
        $countyRow = $rowset->current();
        return $countyRow->name;
    	
    }
    
    /**
     * Returns the full country information as well as the currency object of a country given a code
     * @param string $code 
     */
    public function getCountryByCode($code) {
        $country    = $this->fetchRow("iso = '{$code}'");
        if (!$country) {
            return false;
        }
        
        $country    = (object) $country->toArray(); //getting a clean object
        
        if ($country->currency_id) {
            $currencyModel  = new Model_Currency();
            $currency       = $currencyModel->find($country->currency_id)->current()->toArray();//won't be null because of ref integrity
            $country->currency  = (object) $currency;
        }
        return $country;
    }    
}
?>
