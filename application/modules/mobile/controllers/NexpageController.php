<?php
class Mobile_NexpageController extends Nexva_Controller_Action_Mobile_MasterController {
	
    private $user   = null;
    
    private function __initLayoutElements() {
        $sessionLanguage = new Zend_Session_Namespace('application');
        $this->view->selectedLanguage = $sessionLanguage->language_id;
        
        $languageTable          = new Model_Language();
        $this->view->language   = $languageTable->getLanguageList(1);
        
        $cpId = $this->_request->getParam ('id', null);
        $userMeta = new Model_UserMeta ( );
        $this->user             = $userMeta;
        $compnayName            = $userMeta->getAttributeValue ( $cpId, 'COMPANY_NAME' );
        $compnayDescription     = $userMeta->getAttributeValue ( $cpId, 'COMPANY_DESCRIPTION' );
        $activeNexpage          = $userMeta->getAttributeValue ( $cpId, 'ACTIVE_NEXPAGE' );
        
        $this->view->headMeta ()->appendName ( 'keywords', $compnayName);
        $this->view->headMeta ()->appendName ( 'description', $compnayName . ' - ' . $compnayDescription );
        $this->view->active = 1;
        
        $this->view->pageTitle = 'neXpage - '.$compnayName; 
        $this->view->cpid = $cpId;
        $this->view->cpName = $compnayName;
        
    }
    
	function indexAction() {
	    
	    $this->__initLanguage();
	    $this->__initLayoutElements();
		$this->_helper->layout->setLayout ( 'mobile/nexpage' );
		
		$cpId = $this->_request->getParam ( 'id', null );
		if (! $cpId) {
			$this->_redirect ( '/' );
		}
		
		$productNames = '';
		$userMeta     = $this->user;
		
		$activeNexpage          = $userMeta->getAttributeValue ( $cpId, 'ACTIVE_NEXPAGE' );
		
		if ($activeNexpage) {
		    $product = new Model_Product ( );
		    
			$products = '';
			$sessionLanguage = new Zend_Session_Namespace('application');
			
			$user    = new Cpbo_Model_UserMeta ( );
			$user->setEntityId ( $cpId );
			$this->view->cpDetails = $user;
			
            $languageTable          = new Model_Language();
			$products_details        = $product->getFrontPageProducts ( $this->getDeviceId (), 100, $cpId, true, $sessionLanguage->language_id);
                
            $this->view->products = $this->enablePagenator($products_details);
            
            $this->view->productsCount = $product->countProductsByCpIdAndDeviceId ( $this->getDeviceId (), $cpId, true );
		


            $this->view->baseUrlfo = $_SERVER ['SERVER_NAME'] . '/' . 'np' . '/' . $cpId;
            
	        $deviceSession = new Zend_Session_Namespace('Device');
	        $deviceId      = $deviceSession->deviceId;
	        
	        
	        $session = new Zend_Session_Namespace('np');
	        if(isset($session->np))	{
	        }
	        else  {
	        	$neXpagerStats = new Model_StatisticNexpager();
	        	$neXpagerStats->updateNpStats($cpId, 'mobile', $deviceId, null );
	        	$session->np = 1;
	        }
			
			if ($this->view->productsCount && !empty($products_details[0]['id'])) {
				$recommendationsObj = new Nexva_Recommendation_Recommendation ( );
				$this->view->recommendedProducts = $recommendationsObj->getRecommendationsForProduct ( $products_details[0]['id'], $deviceId, 4, $sessionLanguage->language_id);
			}
            
			 /** Statistics **/
            $stats          = new Nexva_Analytics_NexpagerView();
    		$loggedDevice   = array();
            if ($this->getDeviceId() && $this->getDeviceName()) {
                $statsDeviceId      = $this->getDeviceId(); 
                $statsDeviceName    = $this->getDeviceName();    
            } else {
                $statsDeviceId      = '-1';
                $statsDeviceName    = 'Mobile Base';
            }
            $language       = $languageTable->fetchRow('id = ' . $sessionLanguage->language_id);
            $opts   = array(
                'cp_id'         => $cpId,
                'device_id'     => $statsDeviceId,
                'device_name'   => $statsDeviceName,
                'language_id'   => $sessionLanguage->language_id,
                'language_name' => $language->name,
            );    
            $stats->log($opts);
		}
		else  {
			$this->view->active = 0;
			$this->_redirect('http://'. Zend_Registry::get ( 'config' )->nexva->application->base->url . "/app/cpproducts/id/".$cpId);
		}
	
	}
	
	
	public function appAction() {
	    $this->__initLayoutElements();
        $this->_helper->layout->setLayout ('mobile/nexpage');
	    
	          //show categories
        $this->view->enableCategories   = true;
        $this->view->enableSearch       = true;
        // set no message
        $this->view->messages = array();
        // set session to get returinig url
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $_SERVER["REQUEST_URI"];
        $productModel = new Model_Product();
        $productModel->appFilter = $this->themeMeta->WHITELABLE_APP_FILTER; 
        $productId = $this->_request->appid;

        $deviceSession = new Zend_Session_Namespace('Device');
        $deviceId = $deviceSession->deviceId;



        $sessionLanguage = new Zend_Session_Namespace('application');
        
        $product = $productModel->getProductDetailsById($productId, false, $sessionLanguage->language_id, $sessionLanguage->language_id);
        if (is_null($product) || (isset($product['deleted']) && $product['deleted'] == 1)) {
            $this->_redirect('/app/notfound/id/' . $productId);
        }
        
                
        $deviceSession = new Zend_Session_Namespace('Device');
        $this->view->osVersion = $deviceSession->osVersion;

        // check the compatibility of the application with the device
        $builds = new Model_ProductBuild();
        $buildMissing = $builds->getBuildByProductAndDevice($productId); 
        // check the compatibility of the application with the device
        if (!$buildMissing) {
            $this->view->warningMessage = true;
            $buildProduct          = new Model_ProductBuild();
            $buildVersionInfo      = $buildProduct->getBuildByProductDeviceLanguageAndVersion($productId);
            if($buildVersionInfo)   {
                $this->view->buildVersionInfo = $buildVersionInfo;
            } else {
                $buildInfo = $buildProduct->getBuildByProductDeviceAndLanguage($productId);
                if($buildInfo) {
                    $this->view->buildsInOtherLanguage   = true;
                    $this->view->buildDetails            = $buildInfo;
                }
            }
        }
        
	    $deviceId  = 0;

        if($this->getDeviceId()) {
            $deviceId    = $this->getDeviceId();    
        }    else    {
            $deviceId   = '-1';
        }
        
        $chapId = isset ( $this->themeMeta->USER_ID ) ? $this->themeMeta->USER_ID : null;
      

        
        // increment view count
        $statics = new Model_StatisticProduct();
        $statics->updateProductStatistics($productId, $deviceId, $chapId, 'NEXPAGE');
        
        
        $breadcrumbs    = array();
        $tempCats       = $product['categories'];
        if (is_array($product['categories'])) {
            $catid          = array_pop($tempCats); //getting the last id which is usually the leaf
            $categoryModel  = new Model_Category();
            $category       = $categoryModel->find($catid);
            $category       = $category->toArray();
            $breadcrumbs[]  = $category[0];
            
            if ($category[0]['parent_id'] != 0) {
                $category   = $categoryModel->find($category[0]['parent_id']);
                $category       = $category->toArray();
                $breadcrumbs[]  = $category[0];
            }
            //won't be more than 2 levels deep so loop not required
        }
        $this->view->breadcrumbs    = array_reverse($breadcrumbs);
        
        $session->productId = $productId;
        // Set categories
        // @TODO : choose the correct category
        // if this is a URL product
        $this->view->isUrl = false;
        if ($product['content_type'] == 'URL')
            $this->view->isUrl = true;



        if (isset($product['categories'][1]))
            $this->view->selectedCategory = $product['categories'][1];
        $this->setCategories();
        $this->view->categories = $this->_categories;
        // check product is already downloaded then direct to download
        $order = new Model_Order();
        $this->view->purchased = false;
        if ($order->isPruchsed($productId))
            $this->view->purchased = true;
        $this->view->product = $product;
        // show page title
        $this->view->showPageTitle = false;
        // Page Title
        $this->view->pageTitle = $product['name'];
        $this->view->s3Object = $product['id'] . '/' . $product['file'];
        // get apps by same developer
        $this->view->appsByCp = $productModel->getAppsByCp($deviceId, $product['uid'], 4, $product['id']);
        
        /** Reviews **/
        $reviewModel    = new Model_Review();
        $reviews        = $reviewModel->getReviewsByContentId($productId, 2);
        $evasReviews    = array();
        if (!isset($this->themeMeta->WHITELABLE_EVA_SHOW_APP) || $this->themeMeta->WHITELABLE_EVA_SHOW_APP == 1) {
            $evasReviews    = $reviewModel->getReviewsByContentId($productId, 1, 'EVA');
        }
        $this->view->reviews    = $reviews;
        $this->view->evaReview  = $evasReviews;
	}
	
	
    /**
     * Updates language based on URL
     */
    private function __initLanguage() {
       
        //params key #3 holds the language
        $langCode     = $this->_getParam('3', false);
        if ($langCode) { 
            $langModel  = new Model_Language();
            $language   = $langModel->getLanguageIdByCode($langCode);
            if (isset($language['id'])) {
                $ns     = new Zend_Session_Namespace('application');
                $ns->language_id= $language['id'];
            }
        }      
    }
	
    function changelangAction() {       
        $id     = $this->_getParam('cpid', false);
        $lang   = $this->_getParam('langid', false);
        
        $langModel  = new Model_Language();
        $lang       = $langModel->find($lang);
        $code       = '';
        if (isset($lang[0]['id'])) {
            $ns             = new Zend_Session_Namespace('application');
            $ns->language_id= $lang[0]['id'];
            $code           = '.' . $lang[0]['code'];    
        }
        
        if ($id) {
            $this->_redirect('/np/' . $id . $code);           
        } else {
            $this->_redirect('/');
        }
    }
	
	
    /**
     * Enalbe the paginator for pages
     * @param <type> $results
     * @return <type>
     */
    protected function enablePagenator($results) {
        $paginater = Zend_Paginator::factory(
                $results
        );
        $paginater->setCurrentPageNumber($this->_request->getParam('go', 0));
        $paginater->setItemCountPerPage(8);
        return $paginater;
    }
}


