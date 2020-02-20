<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 12/10/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */
class Cpbo_Model_ProductKey extends Zend_Db_Table_Abstract{

    protected $_id   =   'id';
    protected $_name =   'product_keys';

    function  __construct(){
        parent::__construct();
    }
}