<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_ProductMeta extends Nexva_Db_EAV_EAVModel {
    protected $_name    = 'product_meta';
    protected $_primary = 'id';

    protected $_metaModel = "Model_ProductMeta";
    protected $_entityIdColumn = "product_id";
    protected $_referenceMap    = array(
            'Model_Product' => array(
                            'columns'           => array('product_id'),
                            'refTableClass'     => 'Model_Product',
                            'refColumns'        => array('id')
            ),
    );

}
?>
