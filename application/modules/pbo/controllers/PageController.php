<?php

class Pbo_PageController extends Zend_Controller_Action
{
    public function preDispatch() 
    {        
         if( !Zend_Auth::getInstance()->hasIdentity() ) 
         {

            $skip_action_names = array ('login', 'register', 'forgotpassword', 'resetpassword', 'claim', 'impersonate');
            
            if (! in_array ( $this->getRequest()->getActionName (), $skip_action_names)) 
            {            
                $requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
                $session = new Zend_Session_Namespace ( 'lastRequest' );
                $session->lastRequestUri = $requestUri;
                $session->lock ();
                $this->_redirect ( '/user/login' );
            } 
        }    
    
        $this->_helper->layout->setLayout('pbo/pbo');
    }
    
    
    

    public function indexAction()
    {
        $this->_helper->layout->setLayout('pbo/pbo');
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        
        $pageModel = new Pbo_Model_WlPages();
        $pages = 0;
        
        $pages = $pageModel->getAllPages($chapId);

        $this->view->title = "Page : Manage Pages";  
        
        $pagination = Zend_Paginator::factory($pages);        
       
        if(count($pagination))
        {
            
            $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
            $pagination->setItemCountPerPage(20);
            
            $this->view->pages = $pagination;
            
            unset($pagination);
        }
                
        //Set the messages if exists
        $this->view->pageMessages = $this->_helper->flashMessenger->getMessages();
    }

    public function doDeleteAction()
    {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        
        $chapPageId = trim($this->_request->id);
        
        $pageDeleteModel = new Pbo_Model_WlPages();
        $deleted = $pageDeleteModel->deletePage($chapId, $chapPageId);
        
        if($deleted)
        {
            $this->_helper->flashMessenger->addMessage('Page successfully deleted.');
        }
        
        $this->_redirect( '/page' );   
    }

    public function doPublishAction()
    {        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        
        $pageId = trim($this->_request->id);
        $status = trim($this->_request->status);

        $pagePublishModel = new Pbo_Model_WlPages();
        $modified = $pagePublishModel->updatePublishedState($chapId, $pageId, $status);

        if($modified)
        {
             $message = ($status == 1) ? '1 page published' : '1 page unpublished';
             $this->_helper->flashMessenger->addMessage($message);
        }            
        
        $this->_redirect('/page');  
    }

    /* Add web page for CHAP white label
     * @param - title $title,
     * @param - category $category,
     * @param - page text $pageText,
     * @param - status $status
     */
    public function addPageAction() 
    {

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $languageUsersModel = new Pbo_Model_LanguageUsers();
        $languages = $languageUsersModel->getChapLanguages($chapId);
        $this->view->languages = $languages;

        if($this->_request->isPost())
        {
            $title = trim(utf8_encode($this->_getParam('txtTitle')));
            $category = trim($this->_getParam('chkCategory'));
            $status = trim($this->_getParam('chkStatus'));
            $pageText = trim($this->_getParam('txtPageText'));
            $language = trim($this->_getParam('language'));

            $status = ($status == 'publish') ? '1' : '0';

            $pageModel = new Pbo_Model_WlPages();

            /*foreach($languages as $language){
                //$param = 'pageText'.$language->language_id;
                //$pageText = $$param;
                //$pageText = trim($this->_getParam('txtPageText_language'.$language->language_id));

                //$lastInsertId = $pageModel->addPage($title, $pageText, $category, $status, $chapId, $language->language_id);
            }*/

            $lastInsertId = $pageModel->addPage($title, $pageText, $category, $status, $chapId, $language);

            if(!empty($lastInsertId) && $lastInsertId > 0)
            {                
                $this->_helper->flashMessenger->addMessage('Page successfully created.');
            }            
            
            $this->_redirect('/page');  
        }
                
        $this->view->title = "Page : New Page";
    }
    
    
    /* Edit web page for CHAP white label
     * @param - title $title,
     * @param - category $category,
     * @param - page text $pageText,
     * @param - status $status
     */
    public function editPageAction() 
    {    
       $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
      
       //Change the default view and load add-page view
       $this->_helper->viewRenderer->setRender('add-page') ;

        $languageUsersModel = new Pbo_Model_LanguageUsers();
        $languages = $languageUsersModel->getChapLanguages($chapId);
        $this->view->languages = $languages;

       if($this->_request->isPost())
        {
            $title = trim($this->_getParam('txtTitle'));
            $category = trim($this->_getParam('chkCategory'));
            $pageText = trim($this->_getParam('txtPageText'));    
            $status = trim($this->_getParam('chkStatus'));
            $pageId = trim($this->_getParam('pageId'));
            $language = trim($this->_getParam('language'));

            $status = ($status == 'publish') ? '1' : '0';                 
           
            $pageModel = new Pbo_Model_WlPages();
            
            $modified = $pageModel->updatePage($title, $pageText, $category, $status, $chapId, $pageId, $language);

            if($modified)
            {           
                $this->_helper->flashMessenger->addMessage('Page successfully edited.');
                
            }            
            
            $this->_redirect('/page/edit-page/id/'.$pageId);  
        }
        else
        {  
           $pageId = trim($this->_request->id);

           $pageModel = new Pbo_Model_WlPages();
           $pageDetails = $pageModel->getPageDetailsbyId($chapId, $pageId);

           $this->view->pageDetails = $pageDetails;

           $this->view->action = 'edit-page';
           $this->view->pageId = $pageId;
           $this->view->title = "Page : Edit Page";
           
           //Set the messages if exists
           $this->view->pageMessages = $this->_helper->flashMessenger->getMessages();
           
        }
    }
    
    
}