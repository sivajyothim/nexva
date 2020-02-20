<?php

class Chap_FeaturedProductController extends Zend_Controller_Action 
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
    
    function filterAction()
    {
        
        $platformModel = new Model_Platform();
        $platforms = $platformModel->getPlatforms();
        $this->view->platforms = $platforms;  
        
        $productFeaturedModel = new Chap_Model_ProductChapFeatured();
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;        
          
        
        if($this->_request->isPost() && $this->_getParam('platformId') && $this->_getParam('productId'))
        {
            $platformId = trim($this->_getParam('platformId'));
            $productId = trim($this->_getParam('productId'));
                 
            
            //check app is already featured
            if(!($productFeaturedModel->getAppCount($chapId,$platformId,$productId)))
            {
               
                $lastInsertId = $productFeaturedModel->addChapFeaturedProduct($productId, $platformId, $chapId);
           
                if(!empty($lastInsertId) && $lastInsertId > 0)
                {
                    $this->view->messagesSuccess = 'App was added to the featured apps list';
                } 
            }
            else
            {
                    $this->view->messagesError = 'This App has been already featured';
            }

        }   
        
        $defaultPlatfromId = 12;
        $this->view->isEmptyFeatureApps = FALSE;
        
        //get already featured platforms of the apps
        $featuredApps = $productFeaturedModel->getFeaturedProductsByPlatform($chapId, $defaultPlatfromId);
        
        if(count($featuredApps) > 0)
        {
            $pagination = Zend_Paginator::factory($featuredApps);        

            $pagination->setCurrentPageNumber($this->_request->getParam('featured_apps', 1));
            $pagination->setItemCountPerPage(20);        

            $this->view->featuredApps = $pagination;
            unset($pagination);        
        }
        else
        {
            $this->view->isEmptyFeatureApps = TRUE;
        }
        
    }
    
    function viewFeaturedProductsAction()
    {
        $platformModel = new Model_Platform();
        $platforms = $platformModel->getPlatforms();
        $this->view->platforms = $platforms; 
        
        $this->_helper->viewRenderer->setNoController(true);
        $this->_helper->viewRenderer('featured-product/filter');
        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        
        $platformId = 12;
        $this->view->isEmptyFeatureApps = FALSE;
        
        if($this->_request->isPost() && $this->_getParam('platformIdApps'))
        {
            $platformId = trim($this->_getParam('platformIdApps'));     
            
        }        
        
          $productFeaturedModel = new Chap_Model_ProductChapFeatured();
          $featuredApps =  $productFeaturedModel->getFeaturedProductsByPlatform($chapId, $platformId);
                
          if(count($featuredApps) > 0)
          {              
              $pagination = Zend_Paginator::factory($featuredApps);        
        
              $pagination->setCurrentPageNumber($this->_request->getParam('featured_apps', 1));
              $pagination->setItemCountPerPage(20);        

              $this->view->featuredApps = $pagination;
              unset($pagination);
          }
          else
          {
              
              $this->view->isEmptyFeatureApps = TRUE;
          }
          
        $this->view->selectedPlatform = $platformId;
        
    }
        
    
    //generate the serach results by platform id and given keyword of a product name 
    function searchProductsAction() 
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();        
         
        
        $key = trim($this->_request->getParam('q', false));
        $platformId = trim($this->_request->getParam('platformId', null));
        
        
        if ($key === false) {
            echo json_encode(array());
            return;
        }
        
        $productModel   = new Model_Product();
        $products     = $productModel->getProductsbyPlatform($key, $platformId);
        
        if($products) {
            $dataArr    = array();
            foreach ($products as $product) {
                $dataArr[]    = array ('id' => $product->product_id,'label' => $product->name , 'name' => $product->name);
            }
            echo json_encode($dataArr);
        } else {
            echo json_encode(array());    
        }
    }
    
    function deleteAction()
    {
        $this->_helper->viewRenderer->setNoController(true);
        $this->_helper->viewRenderer('featured-product/filter');
        
        $appId = trim($this->_request->id);        
        
        $productFeaturedModel = new Chap_Model_ProductChapFeatured();
        
        if($productFeaturedModel->deleteFeaturedApp($appId))
        {
            $this->view->messagesSuccess = 'App removed from the featured app list.';
        }
        else
        {
            $this->view->messagesError = 'You are not authorized to delete this app.';
        }
        
        $this->filterAction();
    }
    
}