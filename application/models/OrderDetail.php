<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_OrderDetail extends Zend_Db_Table_Abstract {

    protected $_name    =   "order_details";
    protected $_referenceMap    =   array(


            "product" => array(
                            "columns"       =>  array("product_id"),
                            "refTableClass" =>  "Model_Product",
                            "refColumns"    =>  array("id")
            )

    );

    public function isProductExists($productId) {
        // get the user id and check in order table
        $row = $this->fetchRow(
                $this->select()
                ->where('product_id = ?', $productId)
        );
        if(isset ($row->id))
            return $row;
        else
            return false;
    }

    /**
     * Finds order details for an $orderId
     *
     * @param int $orderId
     * @return Zend_Db_Table_Row_Abstract
     */
    public function findOrderDetails($orderId) {
        return $this->fetchRow('order_id = '. $orderId);
    }
}

?>