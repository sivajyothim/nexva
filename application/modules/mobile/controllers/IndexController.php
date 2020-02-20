<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Mobile
 * @version     $Id$
 */
class Mobile_IndexController extends Nexva_Controller_Action_Mobile_MasterController {

    protected $_categories;

    public function init() {
        

        parent::init();  
        
     	$sitename   = $_SERVER['HTTP_HOST'];
        $themeMeta   = new Model_ThemeMeta();       
        $row        = $themeMeta->fetchRow('meta_name = "WHITELABLE_URL" AND meta_value = "' .  mysql_escape_string($sitename) . '"');
		
        //Please add ID of the CHAP, to the array when you create mobile site for appstore apps, This has to be done in 
        //Nexva_Controller_Action_Mobile_MasterController controller's preDispatch function as well (Tentative Line no. 224), 
        $chapId = array(8056, 9860, 21677, 131024, 129330, 25022, 230676); 
        
        if(isset($row['user_id']) && in_array($row['user_id'], $chapId)) 
        {
            $this->_forward('home', 'rca-app');
           
        }
                
    }
    
    
    
    
    public function indexAction() {
        $this->view->showUtility = true;
        $this->view->htmlTitle = 'Apps for ' . $this->getDeviceName();
        $this->view->enableCategories = true;
        $this->view->showToplinks = true;
        
        $this->setCategories();
        $this->view->categories = $this->_categories;
        
        $product = new Model_Product();
        $product->setAppFilterRules($this->themeMeta->USER_ID);
        $product->appFilter = $this->themeMeta->WHITELABLE_APP_FILTER;
        //get promoted apps
        $promotedProducts   = null;
        if (strtolower($this->themeMeta->WHITELABLE_SITE_NAME) == 'nexva') {
            //don't want our nexva widget to show up for the other CHAPS
            $promotedProducts   = $product->getPromotedProducts($this->getDeviceId());    
        }
        $this->view->promotedProducts = ($promotedProducts) ? $promotedProducts : null;
        
        $reviews    = array();
        if (!isset($this->themeMeta->WHITELABLE_EVA_SHOW_HOME) || $this->themeMeta->WHITELABLE_EVA_SHOW_HOME == 1) {
            //get reviews
            $reviewModel    = new Model_Review();
            $reviews        = $reviewModel->getReviewsForCompatibleApps($this->getDeviceId(), 0, 1, $this->themeMeta->WHITELABLE_APP_FILTER);
        }
        $this->view->reviews    = $reviews;
        
        //get featured apps
        //$limit      = 11;
        
        
        //New function was added to get the featured apps. - 10/04/2012
        //$apps       = $product->getFeaturedProductsMobile($this->getDeviceId(), $limit);
        /*$apps       = $product->getFeaturedProductsMobile($this->getDeviceId(), $limit);
        //Zend_Debug::dump($apps);die();
        
        if (count($apps) < $limit) {
            $nonFeaturedApps   = $product->getFrontPageProducts($this->getDeviceId(), $limit - count($apps));
            $apps   = array_merge($apps, $nonFeaturedApps);   
        }
        //Zend_Debug::dump($apps);die();
        $this->view->featuredProducts = $apps;*/



        //New function was added to get the featured apps. - 09/05/2013
        $featuredApps = $product->featuredApps($this->getDeviceId(),10);
        //Zend_Debug::dump($featuredApps);die();
        $this->view->featuredApps = $featuredApps;
    }
    
    
    function mobileAction() {
        $this->_redirect('/');
    }
    
    
    function allAction() {
        //just load up all enabled products and dump the links
        $page   = (int) $this->_getParam('page', 0);
        $limit  = 100;
        $productModel   = new Model_Product();
        $query          = $productModel->select(false);
        $query
            ->from('products', array('id', 'name'))
            ->where('deleted <> 1')
            ->where('status = ?', 'APPROVED')
            ->limit($limit, $page * $limit);
        $results    = $productModel->fetchAll($query);
        $this->view->apps   = $results;
        $this->view->page = $page;
    }
    
    
    public function homeAction()    {
  	 
        $this->view->partnerHtmlTitle = $this->themeMeta->WHITELABLE_SITE_NAME;
               
        $product = new Model_Product();
        $product->setAppFilterRules($this->themeMeta->USER_ID);
        $product->appFilter = $this->themeMeta->WHITELABLE_APP_FILTER;
 
        $apps = $product->getFrontPageProducts($this->getDeviceId(),  $limit=11);
      
        $this->view->featuredProducts = $apps;
        
        $this->view->userId = $this->themeMeta->USER_ID;
    	
    }

    public function detectAppAction()
    {
        //die('yeeeeeeeeee');
    }
    
}

