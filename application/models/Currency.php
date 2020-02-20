<?php
class Model_Currency extends Nexva_Db_Model_MasterModel {

    protected  $_id     ='id';
    protected  $_name   = 'currencies';

    function  __construct() {
        parent::__construct();
    }

}