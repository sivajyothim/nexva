<?php

class Partnermobile_CategoryController extends Nexva_Controller_Action_Partnermobile_MasterController {

    public function init() {
        parent::init();
    }

    public function indexAction() 
    {   
        
        $isLowEndDevice = $this->_isLowEndDevice;

        $categoryId = $this->_request->getParam('id', null);

        $chapProduct = new Partnermobile_Model_ChapProducts();
        $catWiseProducts = $chapProduct->getTopCategoryProducts($this->_chapId, $this->_deviceId, $categoryId);
        
        $pagination = Zend_Paginator::factory($catWiseProducts);        
       
        $this->view->showResults = FALSE;
        
        if(count($pagination))
        {           
            $this->view->showResults = TRUE;
            
            $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
            $pagination->setItemCountPerPage(10);
            
            $clsNexApi =  new Nexva_Api_NexApi();
            $test = $clsNexApi->getProductDetails($pagination, $this->_deviceId);
            
            $this->view->catApps = $pagination;
            unset($pagination);
        }
        
        $catModel = new Partnermobile_Model_Category();
        $subCatDetails = $catModel->getDetailsById($categoryId);
        
   
       
        
        $this->view->subCatName = $subCatDetails->name;
        $this->view->catName = $catModel->getNameId($subCatDetails->parent_id);
        
        if($this->_chapId == 585480 || $this->_chapId == 585474) {
            $modelCategory= new Model_Category();
            $this->view->subCatName = $modelCategory->getCatNameByIDAndLangId($categoryId,$this->_chapLanguageId);
        
            $this->view->catName = $modelCategory->getCatNameByIDAndLangId($subCatDetails->parent_id,$this->_chapLanguageId);
            
            
        }

        
        //currency details by chap
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
        $this->view->currencyDetails = $currencyDetails;
        
        //check the device user agent and load the low end view if the user agent is low end
        if($isLowEndDevice):
            $this->_helper->viewRenderer('index-low-end');
        endif;
    }
    
    // function added for load categories menu view for low end devices
    public function menuAction()
    {
        
    }
    
    // function added for get sub categories for low end devices
    public function menuSubAction()
    {
        $chapId = $this->_chapId;
        $grade = $this->_grade;

        //get the language id for chapter site
        $userModel = new Model_User();
        $userLanguageId = $userModel->getUserLanguage($this->_chapId);
        
         $categoryModel = new Model_Category();
         
         $mainCatId = $this->getRequest()->getParam('id', 1); 
         
         //Check the language id and if the language id is not equal to english (1) call the translation function
        if($userLanguageId == 1 || empty($userLanguageId)):  
                $this->view->subCatList = $categoryModel->getSubCatsByID($mainCatId, $chapId ,$grade);
                $this->view->parentCatName = $categoryModel->getCatgoryNameById($mainCatId,$userLanguageId);
        else:
                $this->view->subCatList = $categoryModel->getSubCatsByIDAndLangId($mainCatId, $userLanguageId, $chapId, $grade);
                $this->view->parentCatName = $categoryModel->getCatNameByIDAndLangId($mainCatId,$userLanguageId);
        endif;
         //$this->_helper->viewRenderer('top-premium-low-end');
    }
}