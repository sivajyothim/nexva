<?php
/**
 * 
 * Utility page to convert messed up product content encoding
 * @author John
 *
 */
class Admin_ConverterController extends Nexva_Controller_Action_Admin_MasterController {
    
    public function indexAction() {
        $productIds   = $this->_getParam('products', null);
        if ($productIds) {
            $product    = new Model_Product();
            $query      = $product->select(false)->setIntegrityCheck(false)->from('products', array('id', 'name'))
                ->join('product_meta', 'product_meta.product_id = products.id', array('meta_name', 'meta_value'))
                ->where('products.id IN (?)', explode(',', $productIds))
                ->where("meta_name IN ('BRIEF_DESCRIPTION', 'FULL_DESCRIPTION')");
            $products   = $product->fetchAll($query);
            if ($products == null) {
                die('no products');
            }
            
            $data   = array();
            foreach ($products as $product) {
                
                 $data[$product->id]['name']    = $product->name;       
                 $data[$product->id][$product->meta_name]    = $product->meta_value;
            } 
            $this->view->products   = $data;
        }
        $this->view->productIds = $productIds;
    } 
    
    public function convertAction() {
        $products   = $this->_getParam('products');
        $productModel    = new Model_Product();
        $db     = Zend_Registry::get('db');
        $db->query('SET NAMES "utf8"')->execute();
        $productIds=    '';
        foreach ($products as $id => $product) {
            $productModel->update(array('name' => $product['name']), 'products.id = ' . $id);
            $productMeta = new Model_ProductMeta();
            $productMeta->setEntityId($id);
            if (trim($product['BRIEF_DESCRIPTION']) != '')  $productMeta->BRIEF_DESCRIPTION = $product['BRIEF_DESCRIPTION'];
            if (trim($product['FULL_DESCRIPTION']) != '') $productMeta->FULL_DESCRIPTION = $product['FULL_DESCRIPTION'];
            
            $productIds .= ", {$id}";
        }
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'converter/index/products/' .$productIds);
    }
    
    public function convertAllAction() {
        
        if (!$this->_getParam('do', false)) {
            die("Add /do/1 to the end of the URL if you really want to invoke this method. 
                This method will convert the content of ALL PRODUCTS. IT IS VERY EXPENSIVE!");
        }
        
        $product    = new Model_Product();
        $query      = $product->select(false)->setIntegrityCheck(false)->from('products', array('id', 'name'))
            ->join('product_meta', 'product_meta.product_id = products.id', array('meta_name', 'meta_value'))
            ->where("meta_name IN ('BRIEF_DESCRIPTION', 'FULL_DESCRIPTION')");
        $products   = $product->fetchAll($query);
        if ($products == null) {
            die('no products');
        }
        
        $data   = array();
        foreach ($products as $product) {
             $data[$product->id]['name']    = $product->name;       
             $data[$product->id][$product->meta_name]    = $product->meta_value;
        } 
        
        $products   = $this->_getParam('products');
        $productModel    = new Model_Product();
        $db     = Zend_Registry::get('db');
        $db->query('SET NAMES "utf8"')->execute();
        $productIds=    '';
        foreach ($data as $id => $product) {
            $productModel->update(array('name' => $product['name']), 'products.id = ' . $id);
            $productMeta = new Model_ProductMeta();
            $productMeta->setEntityId($id);
            if (trim($product['BRIEF_DESCRIPTION']) != '')  $productMeta->BRIEF_DESCRIPTION = $product['BRIEF_DESCRIPTION'];
            if (trim($product['FULL_DESCRIPTION']) != '') $productMeta->FULL_DESCRIPTION = $product['FULL_DESCRIPTION'];
            
            $productIds .= ", {$id}";
        }
        die('All done! Updated ' . count($data) . ' products');
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'converter/index/products/' .$productIds);
    }
    
}