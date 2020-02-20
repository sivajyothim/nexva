<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_ProductImages extends Zend_Db_Table_Abstract {


    protected $_name = 'product_images';
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
    
    public function getAllImages($productId) {
        return $this->fetchAll('product_id = ' . $productId);
    }
    
    
    public function getImageById($productId)
    {
        $productSql   = $this->select(); 
        $productSql->from(array($this->_name),array('filename'))  
                    ->where('product_id = ?',$productId)
                    ->order('id')
                    ->limit(1);
            
        return $this->fetchRow($productSql); 
//        $x = $this->fetchRow($productSql)->toArray(); 
        
    }
}
?>
