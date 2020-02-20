<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 6/12/13
 * Time: 10:27 AM
 * To change this template use File | Settings | File Templates.
 */

class Nexva_View_Helper_CurrencyConvertPricePoints extends Zend_View_Helper_Abstract {

    /**
     * Returns Revenue
     * @param Product ID $productIDs
     * @return revenue;
     */
    public $currencyRate;
    public $currencyCode;
    
    public function CurrencyConvertPricePoints($amount){
        
        $mCurrencyUser = new Model_CurrencyUser();
        $mCurrencyDetails = $mCurrencyUser->getCurrencyUserRow(Zend_Auth::getInstance()->getIdentity()->id);
        $this->currencyRate = ($mCurrencyDetails['rate'])?$mCurrencyDetails['rate']:0;
        $this->currencyCode = ($mCurrencyDetails['symbol'])?$mCurrencyDetails['symbol']:0;
        
        if(Zend_Auth::getInstance()->getIdentity()->id == 81449) {
            $airtelNigeria = new Nexva_MobileBilling_Type_AirtelNigeria();
            $price = $airtelNigeria->getAirtelPricePoints( $this->currencyRate);
            
            $revenue = $this->currencyCode. ' '. number_format($amount* $price, 2);
            
        } else {
            $revenue = $this->currencyCode. ' '. number_format($amount* $this->currencyRate, 2);
        }
        
       
        
        return $revenue;
    }
}