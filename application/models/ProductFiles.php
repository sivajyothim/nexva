<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_ProductFiles extends Zend_Db_Table_Abstract {


    protected $_name = 'product_files';
    protected $_id   = 'id';
    protected $_referenceMap    = array(
            'Model_Product' => array(
                            'columns'           => array('product_id'),
                            'refTableClass'     => 'Model_Product',
                            'refColumns'        => array('id')
            ),
    );


    function  __construct() {
        parent::__construct();
    }

    public function getFileNameById($id){
        $row = $this->find($id);
        return $row->current()->filename;
    }
}
?>
