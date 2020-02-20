<?php

class Pbo_MenuController extends Zend_Controller_Action
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
    
    
    /* Get all menu items
     * @param - chap id $chapId,
     * @param - menu query result $menu,
    */
    public function indexAction()
    {
        $this->_helper->layout->setLayout('pbo/pbo');
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        
        $menuModel = new Pbo_Model_WlMenuItems();
        $menu = 0;
        //assign query results to $menu variable
        $menuItems = $menuModel->getAllMenus($chapId);
       
        //pass heading to the view
        $this->view->title = "Pages : Manage Menus";
  
        //set pagination        
        $pagination = Zend_Paginator::factory($menuItems);
        
        if(count($pagination))        
        {            
            $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
            $pagination->setItemCountPerPage(20);
            
            $this->view->menus = $pagination;
            unset($pagination);
        }

        $this->view->menuMessages = $this->_helper->flashMessenger->getMessages();
    }

    //Add menu item
    public function addMenuAction() 
    {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        
        $modelPage = new Pbo_Model_WlPages();
        $pages = $modelPage->getPages($chapId);
        
        $this->view->webPages = $pages;
        $this->view->title = "Menu : New Menu Item";

        $languageUsersModel = new Pbo_Model_LanguageUsers();
        $languages = $languageUsersModel->getChapLanguages($chapId);
        $this->view->languages = $languages;

        if($this->_request->isPost())
        {
            $title = trim(utf8_encode($this->getRequest()->getPost('txtTitle')));
            $menuType = strtoupper(trim($this->_getParam('chkMenuType')));
            $url = trim($this->_getParam('txtUrl'));
            $url = !empty($url) ? $url : '';
            $webPageId = trim($this->_getParam('chkPage'));
            $webPageId = !empty($webPageId) ? $webPageId : '';
            $language = $this->_getParam('language');

            //If menu type is External, then its a external url, hence no need to have a web page id
            //if it's Internal, then no need to have a URL
            if($menuType == 'EXTERNAL')
            {
                $webPageId = null;
            }
            else
            {
                $url = null;
            }
            
            $targetWindow = trim($this->_getParam('chkTarget'));    
            $status = trim($this->_getParam('chkStatus'));
            
            $status = ($status == 'publish') ? '1' : '0';
            $order = 1; 
           
            $menuModel = new Pbo_Model_WlMenuItems();
            $lastInsertId = $menuModel->addMenuItem($title, $targetWindow, $menuType, $url, $webPageId, $order, $chapId, $status, $language);

            if(!empty($lastInsertId) && $lastInsertId > 0)
            {                
                //message when the menu is deleted
                $this->_helper->flashMessenger->addMessage('Menu item successfully created.');
            }            
            $this->_redirect('/menu');
        }
        
        $this->view->title = "Menu : New Menu Item";
    }
    
    
    /* Delete menu details from the DB
     * @param - chap id $chapId,
     * @param - menu to be deleted $chapMenuId,
    */
    public function doDeleteAction()
    {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        //get id of the menu to be deleted from the view
        $chapMenuId = trim($this->_request->id);
        $menuDeleteModel = new Pbo_Model_WlMenuItems();
        //pass the chap ID and the menu id of the menu to be deleted to the model
        $menuDeleteModel->deleteMenu($chapId, $chapMenuId);

        //message when the menu is deleted
        $this->_helper->flashMessenger->addMessage('Menu item successfully deleted.');

        
        $this->_redirect( '/menu' );

    }

    /* Change the status of the menu 1 to 0 and 0 to 1
     * @param - chap id $chapId,
     * @param - menu to be deleted $menuId,
     * @param - status of the menu $status
    */
    public function doPublishAction()
    {

        $menuPublishModel = new Pbo_Model_WlMenuItems();
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        $menuId = trim($this->_request->id);
        $status = trim($this->_request->status);

        //pass chap id, menu id and the status of the menu to the model to do the change status query.
        $menuPublishModel->updatePublishedState($chapId, $menuId, $status);

        //messages when status changed
        if($status == 1)
        {
            $this->_helper->flashMessenger->addMessage('1 menu published');
        }else{
            $this->_helper->flashMessenger->addMessage('1 menu unpublished');
        }

        $this->_redirect('/menu');
    }
    
    
    /* Edit web page for CHAP white label
     * @param - title $title,
     * @param - category $category,
     * @param - page text $pageText,
     * @param - status $status
     */
    public function editMenuAction() 
    {    
       $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
      
       //Change the default view and load add-page view
       $this->_helper->viewRenderer->setRender('add-menu') ;
       
       //Get web pages
       $menuId = trim($this->_request->id);

       $modelPage = new Pbo_Model_WlPages();
       $pages = $modelPage->getPages($chapId);
        //Zend_Debug::dump($pages);die();

       $this->view->webPages = $pages;

        $languageUsersModel = new Pbo_Model_LanguageUsers();
        $languages = $languageUsersModel->getChapLanguages($chapId);
        $this->view->languages = $languages;
           
       if($this->_request->isPost())
        {
            $title = utf8_encode(trim($this->getRequest()->getPost('txtTitle')));
            $menuType = strtoupper(trim($this->_getParam('chkMenuType')));
            $url = trim($this->_getParam('txtUrl'));
            $url = !empty($url) ? $url : '';
            $webPageId = trim($this->_getParam('chkPage'));
            $webPageId = !empty($webPageId) ? $webPageId : '';
            $menuId = trim($this->_getParam('menuId'));
            $language = $this->_getParam('language');
           
            //If menu type is External, then its a external url, hence no need to have a web page id
            //if it's Internal, then no need to have a URL
            if($menuType == 'EXTERNAL')
            {
                $webPageId = null;
            }
            else
            {
                $url = null;
            }
            
            $targetWindow = trim($this->_getParam('chkTarget'));    
            $status = trim($this->_getParam('chkStatus'));
            
            $status = ($status == 'publish') ? '1' : '0';
            $order = 1;                
            
            $menuModel = new Pbo_Model_WlMenuItems();
            $modified = $menuModel->updateMenuItem($title, $targetWindow, $menuType, $url, $webPageId, $chapId, $status, $menuId, $language);


            if($modified)
            {           
                $this->_helper->flashMessenger->addMessage('Menu item successfully edited.');
                
            }            
            
            $this->_redirect('/menu/edit-menu/id/'.$menuId);  
        }
        else
        {  

           $menuModel = new Pbo_Model_WlMenuItems();
           $menuDetails = $menuModel->getMenuDetailsbyId($chapId, $menuId);

           $this->view->menuType = $menuDetails->type;
           
           $this->view->menuDetails = $menuDetails;

           $this->view->action = 'edit-menu';
           $this->view->menuId = $menuId;
           $this->view->title = "Page : Edit Menu Item";
           
           //Set the messages if exists
           $this->view->menuMessages = $this->_helper->flashMessenger->getMessages();
           
        }
    }

    /**
     * return pages by language id for ajax call
     * @param - language id
     */
    public function getPagesByLanguageIdAction(){

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        $language = $this->_request->getParam('language');

        $menuModel = new Pbo_Model_WlPages();
        $pages = $menuModel->getPagesByLanguageId($chapId, $language);

        echo json_encode($pages->toArray());

    }
    
}