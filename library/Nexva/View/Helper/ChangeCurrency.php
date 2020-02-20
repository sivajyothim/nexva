<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 7/2/13
 * Time: 11:53 AM
 * To change this template use File | Settings | File Templates.
 */

class Nexva_View_Helper_ChangeCurrency extends Zend_View_Helper_Abstract
{

    /**
     * @return changed currency details regarding
     * */
    function ChangeCurrency($chap_id)
    {
        $currencyUserModel = new Model_CurrencyUser();
        return $currencyUserModel->getCurrencyUser($chap_id);

    }
}