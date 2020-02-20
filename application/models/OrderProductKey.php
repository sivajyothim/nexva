<?php

class Model_OrderProductKey extends Zend_Db_Table_Abstract {

    protected $_name    =   "order_product_keys";


    /**
     *
     * @param int $orderId
     * @param string $key
     * @return int The id of the newly created key.
     */
    public function saveProductKey($orderId, $key) {
        return $this->insert(array(
                'order_id' => $orderId,
                'key' => $key
            )
        );

    }

    /**
     * Returns the product key associated with an order
     *
     * @param int $orderId 
     * @return Zend_Db_Table_Row_Abstract || null
     */
    public function getProductKey($orderId) {
        return $this->fetchRow('order_id = '.$orderId);
    }



}

?>
