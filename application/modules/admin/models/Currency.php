<?php
class Admin_Model_Currency extends Model_Currency {

    protected  $_id     = 'id';
    protected  $_name   = 'currencies';

    public function  __construct() {
        parent::__construct();
    }

    /**
     *  @return available currencies
     */

    function getAvailableCurrencies()
    {
        $sql = $this->select()
                    ->setIntegrityCheck(false)
                    ->from( 'currencies AS c' ,array('c.*'))
                    ->where('c.enabled = ?', '1');
        $currencies = $this->fetchAll($sql)->toArray();
        return $currencies;
    }
}