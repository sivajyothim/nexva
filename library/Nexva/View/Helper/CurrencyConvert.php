<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 6/12/13
 * Time: 10:27 AM
 * To change this template use File | Settings | File Templates.
 */

class Nexva_View_Helper_CurrencyConvert extends Zend_View_Helper_Abstract {

    /**
     * Returns Revenue
     * @param Product ID $productIDs
     * @return revenue;
     */
    public function CurrencyConvert($amount){
        
        $mCurrencyUser = new Model_CurrencyUser();
        $mCurrencyDetails = $mCurrencyUser->getCurrencyUserRow(Zend_Auth::getInstance()->getIdentity()->id);
        $currencyRate = $mCurrencyDetails['rate'];
        $currencyCode = $mCurrencyDetails['symbol'];
        
        $revenue = $currencyCode. ' '. number_format($amount* $currencyRate, 2);
        
        return $revenue;
    }
}