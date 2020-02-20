<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_ProductCategories extends Zend_Db_Table_Abstract {

    protected $_name = 'product_categories';
    protected $_id = 'id';
    protected $_referenceMap = array(
      'Model_Product' => array(
        'columns' => array('product_id'),
        'refTableClass' => 'Model_Product',
        'refColumns' => array('id')
      ),
    );

    function __construct() {
        parent::__construct();
    }

    public function updateCategoriesByProdId($prodId, $data) {
        $categoryIds = $this->fetchAll('product_id = ' . $prodId);
        $rows = $categoryIds->count();
        if ($rows > 0) {
            foreach ($categoryIds as $categoryId) {
                $id = array_pop($data);
                $this->update(array('category_id' => $id), 'id = ' . $categoryId->id);
            }
        } else {
            foreach ($data as $record) {
                $this->insert(array('category_id' => $record, 'product_id' => $prodId));
            }
        }
    }

    public function selectedParentCategory($prodId) {
        $categoryId = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false)
                ->where('product_categories.product_id = ?', $prodId)
                ->join('categories', 'categories.id = product_categories.category_id')
                ->where("categories.parent_id = ? ", 0)
                ->query()
                ->fetchColumn(2);
//        $rows = $categoryIds->count();
        if (!empty($categoryId))
            return $categoryId;
        else
            return;
    }

    public function selectedSubCategory($prodId = null) {
        if ($prodId == null) {
            return;
        }
        
        $categoryId = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false)
                ->where('product_categories.product_id = ?', $prodId)
                ->join('categories', 'categories.id = product_categories.category_id')
                ->where("categories.parent_id <> ? ", 0)
                ->query()
                ->fetchColumn(2);
//        $rows = $categoryIds->count();
        if (!empty($categoryId))
            return $categoryId;
        else
            return;
    }
    
    public function productCountByCategory($categoryId, $chapId)	{

        
        $cache  = Zend_Registry::get('cache');
        $key    = 'PRODUCT_COUNT_BY_CATEGORY_'.$categoryId.$chapId;
        if (($results = $cache->get($key)) !== false)
        {
        
            if($results == 'none')    {
             	return    0;
             } else {
             	return $results;
             }

        }
        
        
    	    $productSql   = $this->select(); 
            $productSql->from(array('pc' => $this->_name), array('count(*) as app_count'))                   
                    ->setIntegrityCheck(false)  
                    ->join(array('cp' => 'chap_products'), 'cp.product_id = pc.product_id', array())
                    ->where('cp.chap_id = ?',  $chapId)
                    ->where('pc.category_id = ?',$categoryId)
                    ->group('category_id');

             $results = $this->fetchRow($productSql); 
             
             
             if($results == null)
             	$results = 'none';
             
             $cache->set($results, $key, 3600);

             if($results == 'none')    {
             	return    0;
             } else {
             	return $results;
             }
    	
    }
    
    public function selectedParentCategoryName($prodId) {
    	
        $category = $this->select()
    	        ->from(array('pc' => $this->_name), array())      
                ->setIntegrityCheck(false)
                ->where('pc.product_id = ?', $prodId)
                ->join('categories', 'categories.id = pc.category_id')
                ->where("categories.parent_id = ? ", 0);
                
        $category = $this->fetchRow($category); 

        if (!empty($category))
            return $category;
        else
            return;
    }

    public function getParentCategoryByProductId($productId){
        $sql =  $this->select()
                ->from(array('pc' => $this->_name),array())
                ->setIntegrityCheck(false)
                ->join(array('c' => 'categories'),'pc.category_id = c.id')
                ->where('c.parent_id = ?',0)
                ->where('pc.product_id = ?',$productId)
                ;
        return $this->fetchAll($sql);
    }
    
    
    public function getCategotyByProductId($productId){
        $sql =  $this->select()
                ->from(array('pc' => $this->_name),array())
                ->setIntegrityCheck(false)
                ->join(array('c' => 'categories'),'pc.category_id = c.id')
        ->where('pc.product_id = ?',$productId);
        return $this->fetchRow($sql);
    }
    
   function getAppCountByCategory($perantId,$subCatId){
       
        $sql =  $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('pc'=>$this->_name))
                    ->join(array('c' => 'categories'),'pc.category_id = c.id');
        
        $categoryId=NULL;
        if(isset($perantId) && !is_null($perantId)){
             $categoryId = $perantId;  
        }
        if(isset($subCatId) && !is_null($subCatId)){
             $categoryId = $subCatId;  
        }
            
        if((isset($perantId) || isset($subCatId)) && (!is_null($categoryId))){
           
            $sql->where('pc.category_id = ?',$categoryId);
        }
        
        //Zend_Debug::dump($sql->assemble());die();          
        $appDetails= $this->fetchAll($sql);
        return $appDetails->count($appDetails);

    }
}

?>
