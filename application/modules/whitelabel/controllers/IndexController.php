<?php


class Whitelabel_IndexController extends Nexva_Controller_Action_Whitelabel_MasterController {
	
    
	public function indexAction() {
	                
	    /**
	     * @var Whitelabel_Model_Product
	     */
	    $productModel  = Nexva_Factory_Whitelabel_Model::getModel('Product', $this->_getEnvironmentOptions());
	    
	    //get all featured (chap specific later)
	    $this->view->featuredApps  = $productModel->getFeaturedProducts();
	    $this->view->latestApps    = $productModel->getRandomRecentProducts($limit = 8);
                        
             //get banners
            
            $chapId = $this->getChapId();            
            $type = 'WEB';
            
            $bannerModel = new Chap_Model_Banner();
            $webBanners = $bannerModel->getChapBannersbyType($chapId, $type);            
                    
	    //show the whitelabel             
            $this->view->SHOW_SLIDESHOW    = false;
            
            if(count($webBanners) > 0)
            {
                
                $this->view->SHOW_SLIDESHOW    = true;
                $this->view->webBanners = $webBanners;
            }
            else
            {
                $this->view->SHOW_SLIDESHOW    = false;
            }
	}
}