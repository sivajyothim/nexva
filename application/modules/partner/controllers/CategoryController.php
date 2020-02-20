<?php 


class Partner_CategoryController extends Nexva_Controller_Action_Partner_MasterController
{
    public function init()
    {
    	parent::init();

    }

    public function indexAction()
    {
        $chap = new Zend_Session_Namespace('partner');
        $this->view->chap_id = $chap->id;
    
    	$categoryId = $this->getRequest()->getParam('id', 1);
		
	    $categoryModel = new Model_Category();
    	$productsModel = new Model_Product();
        $chapProducts = new Partner_Model_ChapProducts();

        /*$categoryData = NULL;
        if($this->_chapLanguageId != 1){
            $categoryData = $categoryModel->getCategoryInfoByChapLangId($this->_request->id, $this->_chapLanguageId);
        }
        else{
            $categoryData = $categoryModel->getCategoryInfo($this->_request->id);
        }*/
        
        $categoryData = $categoryModel->getCategoryInfo($this->_request->id);
        
        //Zend_Debug::dump($categoryData); die();
        
        $this->view->categoryInfo   = $categoryData ;
        $this->view->categoryBreadcrumbs = $categoryModel->getCategoryBreadcrumb($this->_request->id);
     
        $this->view->pageName = $categoryData->name;
        
        if($this->_chapId == 585480 || $this->_chapId == 585474) {
            $modelCategory= new Model_Category();
            $this->view->pageName = $modelCategory->getCatNameByIDAndLangId($this->_request->id,$this->_chapLanguageId);
        
          
        }
        
        
        $this->view->parentCategoryId = $categoryData->parent_id;
    	//Get device Id, if choosen
        $deviceId = $this->getDeviceId();
      
        
    	//$products = $categoryProducts->getProductsByCategory($this->_chapId,$categoryId);
        
        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) 
        {
            $products = $chapProducts->getProductsByCategoryAndDevice($this->_chapId, $categoryId, $deviceId);            
        } 
        else 
        {
            $products = $chapProducts->getProductsByCategory($this->_chapId,$categoryId);
        }
        
        $paginator = Zend_Paginator::factory($products);
        $paginator->setItemCountPerPage($this->_productsPerPage);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

        $config = Zend_Registry::get("config");
        
        $controllerName = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
	    $actionName = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
	    $paramArray = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        
        if ($this->_request->device_id) 
        {
          $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/category/' . $this->view->slug($categoryData->name) . '/'.$categoryId. $this->_request->device_id . '/page/';
        } 
        else 
        {
          $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/category/' . $this->view->slug($categoryData->name) . '/'.$categoryId.'/page/';
        }

        $productsDisplay = array();

        if (!is_null($products)) 
        {
            foreach ($paginator as $row) 
            {
                $productsDisplay[] = $productsModel->getProductDetailsById($row->product_id,FALSE,$this->_chapLanguageId,NULL,$this->_chapId);
            }
        }
        
       

    	
	$this->view->products = $productsDisplay;
    	$this->view->paginator = $paginator;
	
    	$this->view->selectedCategory = $categoryId;
    	
    }
    
    protected function getCategories($id=null) {

        $cache = Zend_Registry::get('cache');
        $key = 'SITE_CATEGORIES';
        if (($rows = $cache->get($key)) === false) {
            $categoryModel = new Model_Category();
            $rows = $categoryModel->getCategorylist();
            $cache->set($rows, $key);
        }
        return $rows;
    }
    
    // function added for get sub categories for low end devices
    public function getSubCatMenusAjaxAction()
    {
        $chapId = $this->_chapId;
        $grade = $this->_grade;

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        
        $categoryModel = new Model_Category();
        $parentCatId = $this->getRequest()->getParam('parentMenuId', 1); 

         //Check the language id and if the language id is not equal to english (1) call the translation function
        if($this->_chapLanguageId == 1 || empty($this->_chapLanguageId)):  
                $subCatLists = $categoryModel->getSubCatsByID($parentCatId, $chapId, $grade);
        else:
                $subCatLists = $categoryModel->getSubCatsByIDAndLangId($parentCatId,$this->_chapLanguageId, $chapId ,$grade);
        endif;
        echo '<ul>';
        foreach($subCatLists as $subCatList){
            echo '<li><a href="/category/'.$this->view->slug($subCatList['name']).'/'.$subCatList['cat_id'].'">'.$subCatList["name"].'</a></li>';
        }
        echo '<ul>';
    }	
}