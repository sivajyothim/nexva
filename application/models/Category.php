<?php

/**
 *
 * @copyright   neXva.com
 * @author      Cheran@nexva.com
 * @package     cp
 *
 */
class Model_Category extends Zend_Db_Table_Abstract {

    protected $_name = "categories";
    protected $_id = "id";

    function __construct() {
        parent::__construct();
    }

    /**
     * Get all the categories
     * @return <type>
     */
    public function getCategorylist() {
        $resultSet = $this->fetchAll(
                    $this->select()
                    ->where('status = ?', 1)
                    ->order('id ASC')
                    ->order('parent_id ASC')
        );
        $entries = array();
        foreach ($resultSet as $row) {
            $entries[$row->parent_id][$row->id] = $row->name;
        }
        return $entries;
    }
    
    public function getCategorylistByLanguage($languageId) {
        
            $catSql=$this->select();
            $catSql->from(array('c'=>$this->_name))
                    ->setIntegrityCheck(false)
                    ->join(array('tc'=>'translations_categories'),'tc.category_id = c.id ',array('tc.translation_title'))
                    ->where('c.status = ?', 1)
                    ->where('tc.language_id = ?', $languageId)
                    ->order('c.id ASC')
                    ->order('c.parent_id ASC');
        $resultSet = $this->fetchAll($catSql);
        $entries = array();
        foreach ($resultSet as $row) {
            $entries[$row->parent_id][$row->id] = $row->translation_title;
        }
        return $entries;
    }

    //Get all parent categories
    public function getParentCategories($chapId, $grade = null)
    {
        $cache  = Zend_Registry::get('cache');
        $key    = 'GET_PARENT_CATEGORIES_'.$chapId.$grade;
        if (($categories = $cache->get($key)) !== false)
        {
        		return $categories;
        }
         
        
        $catSql   = $this->select(); 
        $catSql ->from(array('c'=>$this->_name), array('c.id as main_cat','c.name'))
                ->setIntegrityCheck(false)
                ->join(array('cc'=>'chap_categories'),'cc.category_id = c.id ')
                ->where('cc.chap_id = ?',$chapId)
                ->where('cc.status = ?',1);
                ;
        if($grade != null && !empty($grade)){
            $catSql ->joinRight(array('qgc'=>'qelasy_grade_categories'),'cc.category_id = qgc.category_id')
                    ->join(array('qg'=>'qelasy_grades'),'qgc.grade_id = qg.id',array())
                    ->where('qgc.status = ?',1)
                    ->where('qgc.grade_id = ?',$grade)
                    ;
        }
        $catSql     ->where('c.parent_id = ?',0)
                    ->where('c.status = ?',1)
                    ->group('main_cat')
                    ;
                    
        
         
        
            $catSql->order("FIELD(c.id, 199)");
     
        $categories = $this->fetchAll($catSql)->toArray();
                    
        $cache->set($categories, $key, 3600);
                    
        return	$categories;
        //echo $catSql->assemble();die();

    }
    
    //Get all parent categories
    public function getParentCategoriesForMts($chapId, $grade = null)
    {
        $cache  = Zend_Registry::get('cache');
        $key    = 'GET_PARENT_CATEGORIES_'.$chapId.$grade;
        if (($categories = $cache->get($key)) !== false)
        {
        		return $categories;
        }
         
        
        $catSql   = $this->select(); 
        $catSql ->from(array('c'=>$this->_name), array('c.id as main_cat','c.name'))
                ->setIntegrityCheck(false)
                ->join(array('cc'=>'chap_categories'),'cc.category_id = c.id ')
                ->where('cc.chap_id = ?',$chapId)
                ->where('cc.status = ?',1);
                ;
        if($grade != null && !empty($grade)){
            $catSql ->joinRight(array('qgc'=>'qelasy_grade_categories'),'cc.category_id = qgc.category_id')
                    ->join(array('qg'=>'qelasy_grades'),'qgc.grade_id = qg.id',array())
                    ->where('qgc.status = ?',1)
                    ->where('qgc.grade_id = ?',$grade)
                    ;
        }
        $catSql     ->where('c.parent_id = ?',0)
                    ->where('c.status = ?',1)
                    ->group('main_cat');
                    
            $catSql->order("c.name desc");
     
        $categories = $this->fetchAll($catSql)->toArray();
                    
        $cache->set($categories, $key, 3600);
        for( $i=0; $i< count($categories); $i++ ){
            $categories[$i]['name'] =ucfirst(strtolower($categories[$i]['name']));
        }     
        return	$categories;
        //echo $catSql->assemble();die();

    }
    
    //get sub categories by Parent Id
    public function getSubCatsByID($parentId , $chapId, $grade = null)
    {
        $cache  = Zend_Registry::get('cache');
        $key    = 'GET_SUB_CATS_BY_ID_'.$parentId.$chapId.$grade;
        if (($categories = $cache->get($key)) !== false)
        {
        	return $categories;
        }
         
        
        $catSql   = $this->select(); 
        $catSql->from(array('c'=>$this->_name), array('c.id as cat_id','c.name','c.parent_id as main_cat'))
                    ->setIntegrityCheck(false)
                    ->join(array('cc'=>'chap_categories'),'cc.category_id = c.id')
                    ->where('cc.chap_id = ?',$chapId)
                    ->where('cc.status = ?',1)
                    ;
        if($grade != null && !empty($grade)){
            $catSql ->joinRight(array('qgc'=>'qelasy_grade_categories'),'cc.category_id = qgc.category_id')
                    ->join(array('qg'=>'qelasy_grades'),'qgc.grade_id = qg.id',array())
                    ->where('qgc.status = ?',1)
                    ->where('qgc.grade_id = ?',$grade)
                    ;
        }
        $catSql ->where('c.parent_id = ?',$parentId)
                    ->where('c.status = ?',1)
                    //->group('main_cat')
                    ;
        //echo $catSql->assemble();die();
        
   
        
            $catSql->order("FIELD(c.id, 199) DESC");
 

        $categories = $this->fetchAll($catSql)->toArray(); 
        
        $cache->set($categories, $key, 3600);
        
        return	$categories;
    }

    //Get all parent categories by chapter default language
    public function getParentCategoriesByLangId($langId, $chapId, $grade = null)
    {
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'GET_PARENT_CATEGORIES_BY_LANG_ID_'.$langId.$chapId.$grade;
        if (($categories = $cache->get($key)) !== false)
        {
        	return $categories;
        }
         
        $catSql = $this->select()
                 ->setIntegrityCheck(false)
                 ->from(array('c' => 'categories'),array('id as main_cat'))
                 ->join(array('tc' => 'translations_categories'),'c.id = tc.category_id',array('translation_title as name') )
                ->join(array('cc'=>'chap_categories'),'cc.category_id = c.id ')
                ->where('cc.chap_id = ?',$chapId)
                ->where('cc.status = 1');
        if($grade != null && !empty($grade)){
            $catSql ->joinRight(array('qgc'=>'qelasy_grade_categories'),'cc.category_id = qgc.category_id')
                    ->join(array('qg'=>'qelasy_grades'),'qgc.grade_id = qg.id',array())
                    ->where('qgc.status = ?',1)
                    ->where('qgc.grade_id = ?',$grade)
                    ;
        }
        $catSql ->where('c.parent_id = ?',0)
                 ->where('tc.language_id = ?',$langId)
                 ->where('c.status = ?',1)
                 ->where('tc.status = ?',1)
                ->group('main_cat')
                ;

       
        
     //   $catSql->order("FIELD(c.id, 199) DESC");
        
        // $catSql->order("FIELD(c.id, 199) DESC");
        
        $catSql->order("tc.translation_title ASC");
        

       
       $categories = $this->fetchAll($catSql)->toArray(); 
       
       $cache->set($categories, $key, 3600);
       
       return	$categories;
    }

    //Get all parent categories by chapter default language
    public function getParentCategoriesByLangIdForMts($langId, $chapId, $grade = null)
    {
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'GET_PARENT_CATEGORIES_BY_LANG_ID_'.$langId.$chapId.$grade;
        if (($categories = $cache->get($key)) !== false)
        {
        	return $categories;
        }
         
        $catSql = $this->select()
                 ->setIntegrityCheck(false)
                 ->from(array('c' => 'categories'),array('id as main_cat'))
                 ->join(array('tc' => 'translations_categories'),'c.id = tc.category_id',array('translation_title as name') )
                ->join(array('cc'=>'chap_categories'),'cc.category_id = c.id ')
                ->where('cc.chap_id = ?',$chapId)
                ->where('cc.status = 1');
        if($grade != null && !empty($grade)){
            $catSql ->joinRight(array('qgc'=>'qelasy_grade_categories'),'cc.category_id = qgc.category_id')
                    ->join(array('qg'=>'qelasy_grades'),'qgc.grade_id = qg.id',array())
                    ->where('qgc.status = ?',1)
                    ->where('qgc.grade_id = ?',$grade)
                    ;
        }
        $catSql ->where('c.parent_id = ?',0)
                 ->where('tc.language_id = ?',$langId)
                 ->where('c.status = ?',1)
                 ->where('tc.status = ?',1)
                ->group('main_cat')
                ;

        $catSql->order("c.name desc");
        

       
       $categories = $this->fetchAll($catSql)->toArray(); 
       
       $cache->set($categories, $key, 3600);
       for( $i=0; $i< count($categories); $i++ ){
            $categories[$i]['name'] =ucfirst(strtolower($categories[$i]['name']));
       } 
       return	$categories;
    }
    
    //get sub categories by Parent Id and chapter default language
    public function getSubCatsByIDAndLangId($parentId,$langId, $chapId, $grade = null)
    {
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'GET_SUB_CATS_BY_ID_AND_LANGID_'.$parentId.$langId.$chapId.$grade;
        if (($categories = $cache->get($key)) !== false)
        {
        	return $categories;
        }
        
        $subCatSql = $this->select()
                 ->setIntegrityCheck(false)
                 ->from(array('c' => 'categories'),array('c.id as cat_id','c.parent_id as main_cat'))
                 ->join(array('tc' => 'translations_categories'),'c.id = tc.category_id',array('tc.translation_title as name') )
                ->join(array('cc'=>'chap_categories'),'cc.category_id = c.id')
                ->where('cc.chap_id = ?',$chapId)
                ->where('cc.status = ?',1)
                ;
        if($grade != null && !empty($grade)){
            $subCatSql  ->joinRight(array('qgc'=>'qelasy_grade_categories'),'cc.category_id = qgc.category_id')
                        ->join(array('qg'=>'qelasy_grades'),'qgc.grade_id = qg.id',array())
                        ->where('qgc.status = ?',1)
                        ->where('qgc.grade_id = ?',$grade)
                        ;
        }
        $subCatSql  ->where('parent_id = ?',$parentId)
                    ->where('tc.language_id = ?',$langId)
                    ->where('c.status = ?',1)
                    ->where('tc.status = ?',1)
                    //->group('main_cat')
                    ;
 
           //Zend_Debug::dump($subCatSql->assemble($subCatSql));die();

        
        $categories = $this->fetchAll($subCatSql)->toArray(); 
         
        $cache->set($categories, $key, 3600);
         
        return	$categories;
    }


    //get category name by catId and chapter default language(langId)
    public function getCatNameByIDAndLangId($catId,$langId)
    {
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'GET_CAT_NAME_BY_ID_AND_LANG_ID_'.$catId.$langId;
        if (($categories = $cache->get($key)) !== false)
        {
        	return $categories;
        }
        
        if($langId != 1):
        $subCatSql = $this->select()
                 ->setIntegrityCheck(false)
                 ->from(array('c' => 'categories'),
                        array())
                 ->join(array('tc' => 'translations_categories'),
                        'c.id = tc.category_id',
                        array('translation_title as name') )
                 ->where('tc.status = ?',1)
                 ->where('tc.language_id = ?',$langId)
                 ->where('c.status = ?',1)
                 ->where('c.id = ?',$catId)
                 ;
        else:
        $subCatSql = $this->select()
                 ->setIntegrityCheck(false)
                 ->from(array('c' => 'categories'),
                        array())
                 ->where('c.status = ?',1)
                 ->where('c.id = ?',$catId);
        endif;
 
        //Zend_Debug::dump($subCatSql->assemble($subCatSql));die();

        $categories = $this->fetchRow($subCatSql)->name; 
         
        $cache->set($categories, $key, 3600);
         
        return	$categories;
        
    }
    
    public function getCatgoryNameById($catId) {
        $rowset = $this->find($catId);
        return $rowset->current()->name;
    }

    public function getParentCatgoryNameById($catId) {
        $sql = $this->select()
                    ->setIntegrityCheck()
                    ->from(array('c'=>$this->_name))
                    ->where('c.id = ?',$catId)
                    //->where('IF(c.parent_id = 0,c.id = ?,c.parent_id = ?)',$catId)
                    ;
        //echo $sql->assemble();
        return $this->fetchRow($sql);
        //->where('IF(pb.platform_id = 0, 1 = 1, pdsa.value = ?)', $deviceAttrib[1])
    }

    public function getCategoryInfo($id) {
        return $this->fetchRow("id=$id");
    }
 
    // Function added for get the translated categories for non english chap sites
    public function getCategoryInfoByChapLangId($id, $chapLangId) {
        $catSql = $this->select()
                 ->setIntegrityCheck(false)
                 ->from(array('c' => 'categories'),
                        array())
                 ->join(array('tc' => 'translations_categories'),
                        'c.id = tc.category_id',
                        array('translation_title as name') )
                 ->where('c.parent_id = ?',$id)
                 ->where('tc.language_id = ?',$chapLangId)
                 ->where('c.status = ?',1)
                 ->where('tc.status = ?',1);
 
            //Zend_Debug::dump($catSql->assemble($catSql));die();
            
       return $this->fetchRow($catSql);
    }
    
    public function getCategoryBreadcrumb($id) {
        
        $cache      = Zend_Registry::get('cache');
        $key        = 'CATEGORY_BREADCRUMB_' . $id;
        if (($breadcrumbs = $cache->get($key)) !== false) {
            return $breadcrumbs;
        }
        
        $breadcrumbs = array();

        $row = $this->fetchRow("id=$id");

        $array['id'] = $row->id;
        $array['name'] = $row->name;


        array_push($breadcrumbs, $array);

        while ($row->parent_id != 0) {
            $row = $this->fetchRow("id=" . $row->parent_id);

            $array['id'] = $row->id;
            $array['name'] = $row->name;

            array_push($breadcrumbs, $array);
        }

        $breadcrumbs = array_reverse($breadcrumbs);

        $cache->set($breadcrumbs, $key);
        return $breadcrumbs;
    }
    
    public function getAllCategories()
    {
        $catSql   = $this->select(); 
        $catSql->from(array($this->_name), array('id','name'))                    
                    ->where('status = ?',1)
                    ->order('name');
        
        return $this->fetchAll($catSql); 
    }

    public function getCategoryTranslationByCategoryName($categoryName, $chapLangId){
        $catSql = $this->select()
         ->setIntegrityCheck(false)
         ->from(array('c' => 'categories'),
                array())
         ->join(array('tc' => 'translations_categories'),
                'c.id = tc.category_id',
                array('translation_title') )
         ->where('c.name LIKE ?','%'.$categoryName.'%')
         ->where('tc.language_id = ?',$chapLangId)
         ->where('c.status = ?',1)
         ->where('tc.status = ?',1);

        //Zend_Debug::dump($catSql->assemble($catSql));die();
            
        return $this->fetchRow($catSql);
    }
    
    public function getAllPerantCategory(){
        $catSql   = $this->select(); 
        $catSql->from(array($this->_name), array('id','name'))                    
                    ->where('status = ?',1)
                    ->where('parent_id = ?',0)
                    ->order('name');
        
        return $this->fetchAll($catSql); 
    }
    
    public function getAllSubCategory(){
        $catSql   = $this->select(); 
        $catSql->from(array($this->_name), array('id','name'))                    
                    ->where('status = ?',1)
                    ->where('parent_id != ?',0)
                    ->order('name');
        
        return $this->fetchAll($catSql); 
    }
    public function getAllSubCategoryPerantId($parentId){
        $catSql   = $this->select(); 
        $catSql->from(array($this->_name), array('id','name'))                    
                    ->where('status = ?',1)
                    ->where('parent_id = ?',$parentId)
                    ->order('name');
        
        return $this->fetchAll($catSql); 
    }
}

?>
