<?php
class Whitelabel_Model_Category extends Nexva_Db_Model_MasterModel {

    protected $_name = "categories";
    protected $_id = "id";

    /**
     * This is a resource injected version of the product that you 
     * should be populating on the controller level 
     * @var Whitelabel_Model_Product
     */
    private $product     = null;
    
    /**
     * Contains the chap ID for which data must tailored to 
     * @var Int
     */
    public $chapId     = null;
    
    /**
     * Sets the resource injected product model 
     */
    public function setProduct($product) {
        $this->product  = $product;
    }
    
    /**
     * 
     * @return Whitelabel_Model_Product
     */
    public function getProduct() {
        if (!$this->product) {
            $this->product  = new Whitelabel_Model_Product();
        }
        return $this->product;
    }
    
    /**
     * 
     * Returns the available categories for a given chap
     * @param $chapId
     * @return Array of stdClass
     */
    public function getCategories($chapId = 0) {
        $chapCategory   = new Chap_Model_Category();
        $disabledCats   = $chapCategory->getDisabledCategoriesForChap($chapId);
        $select         = $this->select()->where('status = ?', 1);
        if (count($disabledCats)) {
            //remove the disabled cats
            $disabledCatList    = array();
            foreach ($disabledCats as $cat) {
                $disabledCatList[$cat->id]  = $cat->id;
            }
            $select->where('id NOT IN (?)', $disabledCatList);
        } 
        $select->order(array('parent_id', 'name ASC'));
        $cats       = $select->query()->fetchAll();
        return $cats;
    }
 
    /**
     * Returns the products for a category. Takes filters into account
     * @param $catId
     */
    public function getProductsForCategory($catId, $page = 1, $limit = 20) {
        
        //filter
        $offset = max($page - 1, 0) * $limit;
        $query  = $this->select(false)->setIntegrityCheck(false);
        $query->from('product_categories', array('product_id'))
                ->joinLeft('products', 'products.id = product_categories.product_id', array()) //product needs to be joined for filters to work
                ->where('product_categories.category_id = ?', $catId)
                ->where('products.status = ?', 'APPROVED')
                ->where('products.deleted = ?', 0)
                ->limit($limit, $offset)
                ->distinct('product_categories.product_id')
                ->order(array('products.name ASC'));
        //apply request filters like platform. these are stored in the model
        $query  = $this->getProduct()->applyModelConditionals($query);
             
        $appRules       = new Model_ProductUserRule();
        $query          = $appRules->applyRules($this->chapId, $query);
        $productRows    = $this->fetchAll($query);
        if (!$productRows) {
            return array();
        }

        $products       = array();
        $productModel   = new Whitelabel_Model_Product();
        foreach ($productRows as $rows) {
            $products[] = $productModel->getProduct($rows->product_id);
        }
        return $products;
    }
    
    /**
     * Returns the number of products assigned to a category
     * @param int $catId
     */
    public function getProductsCountForCategory($catId) {
        $query  = $this->select(false)->setIntegrityCheck(false);
        $query->from('product_categories', array('COUNT(products.id) AS amount'))
                ->joinLeft('products', 'products.id = product_categories.product_id', array()) //product needs to be joined for filters to work
                ->where('product_categories.category_id = ?', $catId)
                ->where('products.status = ?', 'APPROVED')
                ->where('products.deleted = ?', 0);
        //apply request filters like platform        
        $query  = $this->getProduct()->applyModelConditionals($query);
                
        $appRules       = new Model_ProductUserRule();
        $query          = $appRules->applyRules($this->chapId, $query);
        $result    = $this->fetchAll($query);
        return $result[0]->amount;
    }
}