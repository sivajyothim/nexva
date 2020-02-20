<?php
/**
 * 
 * This class overwrites some of the functionality of the base Product Model
 * and tries to standardize the product API
 * This class should only be used with the whitelabel module
 * @author John
 *
 */
class Whitelabel_Model_Product extends Nexva_Db_Model_MasterModel  {
    
    protected $_name    = 'products';
    protected $_id      = 'id';
    
    /**
     * @var Model_Product
     */
    private $_productModel    = null;
    
    /**
     * Start of injected variables. 
     * Use the Nexva_Factory_Whitelabel_Product factory if you want  
     * a populated product model
     */
    
    /**
     * Contains the chap ID for which data must tailored to 
     * @var Int
     */
    public $chapId     = null;
    
    
    /**
     * Used to filter results by this platform 
     * @var int
     */
    public $platformId  = null;
    
       
    /**
     * Constructs a product object given the ID and optins
     * @param $id ID of the product 
     *  
     */
    public function getProduct($id, $opts = array()) {
        
        
        //for now just pull the product row
        $product    = $this->find($id)->current();
        //Zend_Debug::dump($product);die();
        if ($product) {
            $product    = $product->toArray();
            $product    = (object) $product; //this is the clear the object of the adapter data
            
            //load the meta data
            $productMeta    = new Model_ProductMeta();
            $productMeta->setEntityId($product->id);
            $product->meta  = $productMeta->getAll();
            
            //load the images
            $productImage       = new Model_ProductImages();
            $images             = $productImage->getAllImages($product->id);
            $product->images    = array();
            if ($images) {
                $product->images    = $images->toArray();
            }
            
            //load the CP
            $userMetaModel      = new Model_UserMeta();
            $userMetaModel->setEntityId($product->user_id);
            $product->userMeta  = $userMetaModel->getAll();
        } else {
            return false;
        }
               
        return $product;
    }
    
    
    /**
     * A generic method  that will return an array of products given a query
     * that retruieves product IDs
     */
    public function getProducts($select) {
        $productIds = $select->query()->fetchAll();
        $products   = array();
        foreach ($productIds as $id) {
            $products[]     = $this->getProduct($id->id);
        } 
        
        return $products;
    }
    
    
    /**
     * 
     * Returns chap specific Featured products.
     * @param $platformId if a platform is passed, filters by platform
     * @param $limit
     */
    public function getFeaturedProducts($limit = 4) {
              
        $productFeaturedModel = new Chap_Model_ProductChapFeatured();
        $select = $productFeaturedModel->getFeatureAppsQueryByChap($this->chapId, $this->platformId);
        $select->limit($limit);
        
        $select         = $this->applyModelConditionals($select);
         
        $appRules       = new Model_ProductUserRule();
        $select         = $appRules->applyRules($this->chapId, $select);
       
        $products       = $this->getProducts($select);
   
        return $products;  
    }
    
    
    /**
     * Returns the all products ordered by product ID
     * @param $page
     * @param $limit
     */
    public function getAllProducts($page = 1, $limit = 20) {
        $offset     = max(0, $page - 1) * $limit;
        
        
        
        $select     = $this->select(false)->from('products', array('id'))
                        ->where('products.status = ?', 'APPROVED')
                        //->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                        ->where('deleted <> ?', 1)
                        ->order(array('products.id DESC'))
                        ->limit($limit, $offset);
        
        $exludedChaps = array();
        $exludedChaps[] = 6691;    
        
        //Filter apps by chap, check if the chap_id is exists with in excluded chaps
        if(!in_array($this->chapId, $exludedChaps))
        {            
           $select->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)');
        }
        
        //Zend_Debug::dump($select->__toString());die();
        
        $select         = $this->applyModelConditionals($select);
        $select->distinct();
       
        $appRules       = new Model_ProductUserRule();
        $select         = $appRules->applyRules($this->chapId, $select);
        
        $products       = $this->getProducts($select);
        return $products;
    }
    
    /**
     * Returns the count for all the products given the filters
     */
    public function getAllProductsCount() {
        
        $select     = $this->select(false)->from('products', array('COUNT(products.id) AS amount'))
                        ->where('products.status = ?', 'APPROVED')
                        //->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                        ->where('deleted <> ?', 1);
        
        $exludedChaps = array();
        $exludedChaps[] = 6691;    
        
        //Filter apps by chap, check if the chap_id is exists with in excluded chaps
        if(!in_array($this->chapId, $exludedChaps))
        {
           $select->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)');
        }
        
        
        
        $select         = $this->applyModelConditionals($select);
       
        $appRules       = new Model_ProductUserRule();
        $select         = $appRules->applyRules($this->chapId, $select);
        
        $result         = $select->query()->fetchAll();
        
        return $result[0]->amount;
    }
    
    public function getRandomRecentProducts($plaformId = null, $limit = 8) {
        //load up x4 times the amount of products, shuffle it and send it back
        $select     = $this->select(false)->from('products', array('id'))
                        ->where('products.status = ?', 'APPROVED')
                        //->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                        ->where('deleted <> ?', 1)
                        ->order(array('products.id DESC'))
                        ->limit($limit * 4);
        
        $exludedChaps = array();
        $exludedChaps[] = 6691;    
        
        //Filter apps by chap, check if the chap_id is exists with in excluded chaps
        if(!in_array($this->chapId, $exludedChaps))
        {
           $select->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)');
        }
        
        
        $select         = $this->applyModelConditionals($select);        
        $select->distinct();
                      
        $appRules       = new Model_ProductUserRule();
        $select         = $appRules->applyRules($this->chapId, $select);
        
        
        $productIds     = $select->query()->fetchAll();
        shuffle($productIds);
        $productIds     = array_slice($productIds, 0, $limit);
        
        $products   = array();
        foreach ($productIds as $id) {
            $products[]     = $this->getProduct($id->id);
        } 
        return $products;
    }
    

    
    /**
     * Applies the conditionals like (platform, language) to the query.
     * Assumes that product table is already joined
     * @param $query
     */
    public function applyModelConditionals($select) {
        $parts  =   $select->getPart(Zend_Db_Table_Select::FROM);
        
        if (!isset($parts['products'])) {
            throw new Exception('Products table is not present in query');    
        }
        
        if ($this->platformId) {
            $select->joinLeft('product_builds', 'product_builds.product_id = products.id', array());
            $select->where('product_builds.platform_id = ? ', $this->platformId);
        }
        return $select;
    }
    
    public function searchByTerm($keywords, $nameOnlySearch = false, $page = 1, $limit = 20) {
        
        $offset     = max($page - 1, 0) * $limit;
        
        $db         = Zend_Registry::get('db');
        $keysArr    = explode(' ', $keywords);
        
        if ($nameOnlySearch) {
            $keyStr     = 'products.name LIKE ' . $db->quote("%" . $keywords . "%");
        } else {
            $keyStr     = 'products.name LIKE ' . $db->quote("%" . $keywords . "%") . ' OR ' . 
                'products.keywords LIKE ' . $db->quote("%" . $keywords . "%");
            
            foreach ($keysArr as $keyval) {
                $keyStr .= " OR products.name LIKE " . $db->quote("%" . $keyval . "%") . 
                    " OR products.keywords LIKE " . $db->quote("%" . $keyval . "%");
            }    
        }
        
        $select = $this->select()
                ->distinct()
                ->from('products', array('products.id'))
                ->where($keyStr)
                ->where('products.deleted <> 1')
                ->where('products.inapp IS NULL')
                ->where("products.status = 'APPROVED'")
                //->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                ->limit($limit, $offset);
        
        $exludedChaps = array();
        $exludedChaps[] = 6691;    
        
        //Filter apps by chap, check if the chap_id is exists with in excluded chaps
        if(!in_array($this->chapId, $exludedChaps))
        {
           $select->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)');
        }
        

        $select         = $this->applyModelConditionals($select);
        $appRules       = new Model_ProductUserRule();
        $select         = $appRules->applyRules($this->chapId, $select);
              
        $products       = $this->getProducts($select); 
        return $products;        
    }
    
    public function getCountForSearchByTerm($keywords) {
       $db         = Zend_Registry::get('db');
        $keysArr    = explode(' ', $keywords);
        
        $keyStr     = 'products.name LIKE ' . $db->quote("%" . $keywords . "%") . ' OR ' . 
            'products.keywords LIKE ' . $db->quote("%" . $keywords . "%");
        
        foreach ($keysArr as $keyval) {
            $keyStr .= " OR products.name LIKE " . $db->quote("%" . $keyval . "%") . 
                " OR products.keywords LIKE " . $db->quote("%" . $keyval . "%");
        }    
        
        $select = $this->select()
                ->from('products', array('COUNT(products.id) AS amount'))
                ->where($keyStr)
                ->where('products.deleted <> 1')
                ->where('products.inapp IS NULL')
                //->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)')
                ->where("products.status = 'APPROVED'");

        $exludedChaps = array();
        $exludedChaps[] = 6691;    
        
        //Filter apps by chap, check if the chap_id is exists with in excluded chaps
        if(!in_array($this->chapId, $exludedChaps))
        {
           $select->where('products.user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)');
        }
        
        $select         = $this->applyModelConditionals($select);
        $appRules       = new Model_ProductUserRule();
        $select         = $appRules->applyRules($this->chapId, $select);

        $result    = $this->fetchAll($select);
        return $result[0]->amount;
    }
    
    
}