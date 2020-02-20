<?php

class Chap_ProductController extends Zend_Controller_Action 
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

        }
        
        public function indexAction()
        {
            $chapId = Zend_Auth::getInstance()->getIdentity()->id;
            
            //Get all chap related applications, IF filtering rules has been done,
            // fetch only the filtered appsa, otherwise fetch all
            $modelProduct = new Chap_Model_Product();
            $chapApps = 0;
            
            $this->view->isEmptyApps = TRUE;
            
            //Check if it is a searching for an app
            if($this->_request->isPost() && $this->_getParam('appName'))
            {
                   $appSearchKey = trim($this->_getParam('appName'));                

                   $chapApps = $modelProduct->getSerachedChapProducts($chapId,$appSearchKey);
                   $this->view->searchKey = $appSearchKey;
            }
            else
            {
                   $chapApps = $modelProduct->getAllChapProducts($chapId);
            }
            //echo count($chapApps); die();
            //check if theree are apps      
            if(count($chapApps)> 0)
            {
                //echo "yeee";die();
                $this->view->isEmptyApps = FALSE;                
                
                $pagination = Zend_Paginator::factory($chapApps);        

                $pagination->setCurrentPageNumber($this->_request->getParam('chap_apps', 1));
                $pagination->setItemCountPerPage(20);        

                $this->view->chapApps = $pagination;
                unset($pagination);
            }          
                        
            
        }
        
        //Loading Nexlinker popup
        public function embededAction() 
        {
            $this->_helper->getHelper('layout')->disableLayout();
            
            $productId = $this->_request->id;
            
            $prodModel = new Model_Product();
            $this->view->content = $prodModel->getProductDetailsById($productId);

            //get the chap id
            $chapId = Zend_Auth::getInstance()->getIdentity()->id;
            
            
            if (isset($chapId) && !empty($chapId)) 
            {
                $themeMeta  = new Model_ThemeMeta();
                $themeMeta->setEntityId($chapId);
                $themeData  = $themeMeta->getAll();
                $this->view->themeData  = $themeData;
                $this->view->chapId     = $chapId;    
            }
    }
}
    