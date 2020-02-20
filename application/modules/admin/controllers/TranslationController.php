<?php
class Admin_TranslationController extends Zend_Controller_Action{
    
    // check the user is logged in
    public function preDispatch(){
       if( !Zend_Auth::getInstance()->hasIdentity() ) {

            if($this->_request->getActionName() != "login") {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();

            }
            if( $this->_request->getActionName() != "login" )
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/login');
        }
    }

    function indexAction(){

    }
    
    // get the all languages and display as a dropdown in view and list categories
    function categoriesTranslationsListAction()
    {
        $languageModel = new Admin_Model_Language();
        $languageList = $languageModel->getAvailableLanguages();
        
        //$selectedLanguage = $this->getRequest()->getPost('language', null);
        $selectedLanguage = $this->_request->getParam('language', false);

        if(empty ($selectedLanguage)):
            $selectedLanguage='1';
        endif;   
        
        $page = $this->_request->page;
        
        $categoryTransalationModel  =   new Admin_Model_TranslationCategory();
        $categoryList  =   $categoryTransalationModel->getCategoriesByLanguage($selectedLanguage,$page);
        
        $this->view->languageList = $languageList;
        $this->view->categoryList = $categoryList;
        
        $this->view->selectedLanguage =  $selectedLanguage;
        $this->view->messages = $this->_helper->flashMessenger->getMessages();      
    }

    // get the all categories and list with available translations - AJAX
    function categoriesTranslationsListAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $categoryTransalationModel  =   new Admin_Model_TranslationCategory();
        $categoryList  =   $categoryTransalationModel->getCategorylist();
        $this->view->formattedCategories = $categoryList;
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }
    
    //add new translation or edit existing translation
    function editCatTranslationAction()
    {
        $categoryId = $this->_request->id;
        $langId = $this->_request->language;
        
        $categoryModel  =   new Model_Category();
        $catName  =   $categoryModel->getCatgoryNameById($categoryId);
        
        $languageModel = new Admin_Model_Language();
        $selectedLanguage = $languageModel->getLanguageNameById($langId);
        
        $translationModel = new Admin_Model_TranslationCategory();
        $translationCat = $translationModel->getTranslationByCatIdLangId($categoryId,$langId);
        //print_r($translationCat); die();
        $category = array('id'=>$categoryId,'langId'=>$langId,'translation_title'=>$translationCat['translation_title'],'status'=>$translationCat['status']);
        
        $this->view->catName = $catName;
        $this->view->selectedLanguage = $selectedLanguage;
        $this->view->category = $category;
    }
    
    //save or edit translations for categories 
    function saveCatTranslationAction()
    {
        $data   = array(
                    'category_id' => $this->_request->getParam('catId', false),
                    'language_id' => $this->_request->getParam('langId', false),
                    'translation_title' => $this->_request->getParam('categoryTranslation', false),
                    'status' => $this->_request->getParam('categoryStatus', 0)
                    );
        
        if (empty($data['translation_title'])) {
            $this->__addErrorMessage('Translation is required');
            $this->_redirect('translation/edit-cat-translation/id/'.$data['category_id'].'/language/'.$data['language_id']); 
        }
        
        $translationCatModel  = new Admin_Model_TranslationCategory();
        $message = $translationCatModel->addCatTranslation($data);
 
        $this->__addMessage($message);
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'translation/categories-translations-list/language/'.$data['language_id']);
    }
    
    //delete translation from translation table
    function deleteCatTranslationAction(){
        $catId = $this->_request->getParam('id',false);
        $langId = $this->_request->getParam('language',false);
        
        $translationCatModel = new Admin_Model_TranslationCategory();
        $message = $translationCatModel->deleteCatTranslation($catId,$langId);
        
        $this->__addMessage($message);
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'translation/categories-translations-list/language/'.$langId);
    }
    
    //change the status of translation to 0 or 1
    function statusCatTranslationAction(){
        $catId = $this->_request->getParam('id',false);
        $langId = $this->_request->getParam('language',false);
        $status = $this->_request->getParam('status',false);
        
        $translationCatModel = new Admin_Model_TranslationCategory();
        $message = $translationCatModel->statusCatTranslation($catId,$langId,$status);
        
        $this->__addMessage($message);
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'translation/categories-translations-list/language/'.$langId);
    }
    
    protected function __addMessage($message = '') {
        $this->_helper->FlashMessenger->resetNamespace();
        $this->_helper->FlashMessenger->addMessage($message);
    }

    protected function __addErrorMessage($message = '') {
        $this->_helper->FlashMessenger->setNamespace('ERROR');
        $this->_helper->FlashMessenger->addMessage($message);
        $this->_helper->FlashMessenger->resetNamespace();
    }
}

?>