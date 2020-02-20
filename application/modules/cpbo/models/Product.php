<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 10/31/13
 * Time: 5:10 PM
 * To change this template use File | Settings | File Templates.
 */
class Cpbo_Model_Product extends Zend_Db_Table_Abstract {
    protected $_name    = 'products';
    protected $_primary = 'id';

    function  __construct() {
        parent::__construct();
    }

    /**
     * @param $productId
     * @return null|Zend_Db_Table_Row_Abstract
     */
    function getProductDetails($productId){
        $sql = $this->select()
                    ->from(array('p' => $this->_name))
                    ->where('p.id =?',$productId)
                    ;
        return $this->fetchRow($sql);
    }

}

