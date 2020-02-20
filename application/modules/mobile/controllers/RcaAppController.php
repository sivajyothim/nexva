<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Mobile
 * @version     $Id$
 */

include_once APPLICATION_PATH."/../library/Nexva/PaymentGateway/Adapter/PaypalMobile/library/paypal.php";

class Mobile_RcaAppController extends Nexva_Controller_Action_Mobile_MasterController {


    
    protected $_deviceId;
    protected $_appCount;

    public function init() {
    	

        $config = Zend_Registry::get("config");
        if(substr($_SERVER['HTTP_HOST'], 0, 3) == 'www')
            $this->_redirect("http://" . $config->nexva->application->mobile->url);
        
        parent::init();
        $this->view->showToplinks = TRUE;
        
    }
   
    /**
     * show Full application detail
     */
    public function showAction() {
    	
        //show categories
        $this->view->enableCategories   = true;
        $this->view->enableSearch       = true;
        
        $this->view->userId = $this->themeMeta->USER_ID;
        
        // set no message
        $this->view->messages = array();
        // set session to get returinig url
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $_SERVER["REQUEST_URI"];
        $productModel = new Model_Product();
        $productModel->appFilter = $this->themeMeta->WHITELABLE_APP_FILTER; 
        $productId = $this->_request->id;

        $deviceSession = new Zend_Session_Namespace('Device');
        $deviceId = $deviceSession->deviceId;

 

        $product = $productModel->getProductDetailsById($productId);
        
                
        if (is_null($product) || (isset($product['deleted']) && $product['deleted'] == 1)) {
            $this->_redirect('/app/notfound/id/' . $productId);
        }
        $this->view->originialPrice = $product['cost']; 
                
        $deviceSession          = new Zend_Session_Namespace('Device');
        $this->view->osVersion  = $deviceSession->osVersion;

        // check the compatibility of the application with the device
	    $builds = new Model_ProductBuild();
 	    $buildMissing = $builds->getBuildByProductAndDevice($productId); 
        // check the compatibility of the application with the device
        if (!$buildMissing) {
            $this->view->warningMessage = true;
	        $buildProduct          = new Model_ProductBuild();
			$buildVersionInfo      = $buildProduct->getBuildByProductDeviceLanguageAndVersion($this->_request->id);
			if($buildVersionInfo)	{
     			$this->view->buildVersionInfo = $buildVersionInfo;
    		} else {
        		$buildInfo = $buildProduct->getBuildByProductDeviceAndLanguage($this->_request->id);
        		if($buildInfo) {
	        		$this->view->buildsInOtherLanguage   = true;
	        		$this->view->buildDetails            = $buildInfo;
        		}
    		}
        }
        

        
        $deviceId = 0;
        if($this->getDeviceId()) {
            $deviceId    = $this->getDeviceId();    
        }    else    {
            $deviceId  = '-1';
        }
        $chapId = isset ( $this->themeMeta->USER_ID ) ? $this->themeMeta->USER_ID : null;

        // increment view count
        $statics = new Model_StatisticProduct();
        $statics->updateProductStatistics($productId, $deviceId, $chapId,'MOBILE');

        
        $breadcrumbs    = array();
        $tempCats       = $product['categories'];
        if (is_array($product['categories'])) {
            $catid          = array_pop($tempCats); //getting the last id which is usually the leaf
            $categoryModel  = new Model_Category();
            $category       = $categoryModel->find($catid);
            $category       = $category->toArray(); 
            if (!empty($category)) {
                $breadcrumbs[]  = $category[0];
                
                if ($category[0]['parent_id'] != 0) {
                    $category   = $categoryModel->find($category[0]['parent_id']);
                    $category       = $category->toArray();
                    $breadcrumbs[]  = $category[0];
                }    
            } 
            //won't be more than 2 levels deep so loop not required
        }
        $this->view->breadcrumbs    = array_reverse($breadcrumbs);
        
        $session->productId = $productId;
        // Set categories
        // @TODO : choose the correct category
        // if this is a URL product
        $this->view->isUrl = false;
        if ($product['content_type'] == 'URL') {
            $this->view->isUrl = true;
        }
            
        
         /** Promotion codes **/
        $promoCodeLib       = new Nexva_PromotionCode_PromotionCode();
        $promoSession       = $promoCodeLib->getAppliedCode();
        $promotionApplied   = false;
        $codeValid          = false;
        $promocode          = null;
        if ($promoSession->codeApplied === true && $promoSession->codeActive === true) {
            $promotionApplied   = true;
            $promocode          = $promoSession->codeObject;
            foreach ($promocode['products'] as $promocodeProduct) {
                if ($promocodeProduct->id == $productId) {
                    $codeValid          = true;
                    break;
                }
            }
        }
        
        if ($codeValid  == true) {
            $promoCodeType      = Nexva_PromotionCode_Factory::getPromotionCodeType($promocode['code']);
            $product['cost']    = $promoCodeType->applyPriceModification($product['cost']);
        }
        
        $this->view->promocode  = $promocode;
        $this->view->validCode  = $codeValid;
        
        //apply promotion codes    


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
        
        
        /***** Rating *****/
        
        //setup the new rating model and pull avg from there
        $ratingModel    = new Mobile_Model_Rating();        
        
       
        $this->view->avgRating      = $ratingModel->getAverageRatingByProduct($this->_request->id);    
        $this->view->totalRatings   = $ratingModel->getTotalRatingByProduct($this->_request->id);
        
        $rateCount = $ratingModel->checkProductRatedByUser(@$_SERVER['REMOTE_ADDR'],$this->_request->id);
        //$this->view->productId = $this->_request->id;
        
        $productRated = FALSE;
        
        if($rateCount > 0)
        {
            $productRated = TRUE;
        }
        else
        {
            $productRated = FALSE;
        }
                                        
       $this->view->productRated = $productRated;
       
       $this->view->messageSuccess = $this->_helper->flashMessenger->getMessages();
      
        $this->view->user_id = $this->themeMeta->USER_ID;
    }

    
   
    public function downloadAction() {
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        $productId  = $this->_request->id;
        $languageId = $this->_getParam('language_id', false);
        

        // mobparter affiliate implementation
        // @TODO : add this to factory/observer
        $nexvaWidgetId = Zend_Registry::get("config")->nexva->application->widget->id;
        if ($nexvaWidgetId == $productId) 
        {
            $affiliateSession = new Zend_Session_Namespace('affiliate');
            $baseUrl = Zend_Registry::get("config")->nexva->application->base->url;
            $cookieValue = FALSE;
            $cookieValue = isset($_COOKIE['affiliate']);
            if (isset($affiliateSession->code) && !$cookieValue) {
                $affiliateCode = $affiliateSession->code;
                unset($affiliateSession->code);
                $mobPartner = new Nexva_Affiliate_MobPartner($affiliateCode, $productId);
                $mobPartner->track();
                setcookie("affiliate", TRUE, (time() + (3600 * 24 * 30)));

            }
        }

        $product = new Model_Product ();
        // redirect to download page
        $chapId = isset ( $this->themeMeta->USER_ID ) ? $this->themeMeta->USER_ID : null;
        
        $accessNs           = new Zend_Session_Namespace('Access');
        $sessionSet         = isset($accessNs->isAuthorized) ? $accessNs->isAuthorized : false;
        if ($sessionSet) {
            $chapId = null; //we don't want downloads to be tracked against CHAPS IF the access phrase has been set
        }
        
        //if the language is set this means default language build is not found so this is a request to download other language build
        
        //Fetch the S3 app URL to be downloaded
        $url               = $product->downloadProduct($productId, $chapId, true, true, $languageId);
        
    	$contentType       = $product->getProductContentType($productId, $languageId);
           
    	if ($url != '/app/buy') { //there doesn't seem to be any other way to do it. 
    	    //All I want to do is, to remove the promocode after download
    	    $promoCodeLib     = new Nexva_PromotionCode_PromotionCode(); 
            $promoCodeLib->clearPromotionCode();
    	}
	
        //This is to chek if the app has a build or just a URL to Itunes/google  play
        if($contentType != 'URL') {
		
            $type       = $product->getDownloadFileType($productId, $languageId);
            $fileName   = $product->getDownloadFile($productId, $languageId);

            if ($type == 'png' or $type == 'jpg' or $type == 'jpeg' or $type == 'gif') 
            {
                header('Content-Description: File Transfer');
                header('Content-Type: image/' . $type);
                header('Content-Disposition: attachment; filename=' . $fileName);
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($url));
                ob_clean();
		flush ();
		readfile($url);
		exit ();
            }
            else 
            {                
                $this->_redirect($url);
            }
	} else {
            $this->_redirect($url);
        }

			
            
    }

    public function notcompatibleAction() {
    	
        $deviceSession = new Zend_Session_Namespace('Device');
        $deviceId = $deviceSession->deviceId;
        $this->view->enableCategories = true;
        $this->view->showPageTitle = false;
        $this->view->pageTitle = $this->getDeviceName();
        $this->setCategories();
        $this->view->categories = $this->_categories;
        $productId = $this->_request->id;
        $productModel = new Model_Product();
        $product = $productModel->getProductDetailsById($productId);
        $this->view->appName = $product['name'];
        $this->view->product = $product;
        $this->view->msg = ' is not compatible with your device (' . $this->getDeviceName() . ')';
        $this->view->products = $productModel->getAppsByCp($deviceId, $productId, 4, $productId);
        
    }

    public function notfoundAction() {
    	
        $this->view->enableCategories = true;
        $this->view->showPageTitle = false;
        $this->view->pageTitle = 'Content not found.';
        $this->setCategories();
        $this->view->categories = $this->_categories;
        $productId = $this->_request->id;
        $productModel = new Model_Product();
        $product = $productModel->getProductDetailsById($productId);
        $this->view->messages = array('Content not found.');
        $this->view->product = $product;
        $this->view->deviceName = $this->getDeviceName();
        $this->view->productId =  $productId;
        
        $deviceSession = new Zend_Session_Namespace('Device');
        $this->view->osVersion = $deviceSession->osVersion;
        
        $buildProduct = new Model_ProductBuild();
     
        
        $buildVersionInfo = $buildProduct->getBuildByProductDeviceLanguageAndVersion($this->_request->id);
        
        if($buildVersionInfo)	{
         	$this->view->buildVersionInfo = $buildVersionInfo;
        }	else	{
	        $buildInfo = $buildProduct->getBuildByProductDeviceAndLanguage($this->_request->id);
	        $sessionLanguage = new Zend_Session_Namespace('application');
	        $languageId = $sessionLanguage->language_id;
	        
	        $language = new Model_Language();

	        $languageName =  $language->getLanguageByid($languageId);
	        
	        $this->view->currentLanguage = $languageName->name;
	        $this->view->buildInfo = $buildInfo;
        }
        
    }

    
    public function deviceAction() {
    	
    // Unset session objects
    $deviceSession = new Zend_Session_Namespace('Device');
    unset($deviceSession->deviceId);
    unset($deviceSession->deviceName);
    // remove cookie
    setcookie('device_id', NULL, time() - 3600, '/');
    setcookie('device_name', NULL, time() - 3600, '/');


    $this->view->showUtility = false;
    // Page Title
    $this->view->pageTitle = 'Search Device';
    // show page title
    $this->view->showPageTitle = false;
    $this->view->messages = array(
      'Search your device here.'
    );
    // Search Value
    $keyword = '';
    $devices = '';
    $devicesinfo = '';
    if (isset($this->_request->q))
      $keyword = $this->_request->q;
    $this->view->keyword = $keyword;
    if (isset($keyword) && !empty($keyword)) {
      $this->view->searchValue = $keyword;
      
      
      
    if (Zend_Registry::get ( 'config' )->nexva->application->search->sphinx->enable) {
                

                 $sphinx = new Nexva_Util_Sphinx_Sphinx ( );

                 $results = $sphinx->searchDevices($keyword, ' SPH_MATCH_ANY' );
                    if ($results === false) {
                        // for debug
                        //print "Query failed: " . $cl->GetLastError () . ".\n";
                    

                    } else {
                        
                        if ($results ['total'] != 0) {

                            if (is_array ( $results ["matches"] )) {
                                foreach ( $results ["matches"] as $device ) {
                                    // for debug 
                                    //print "$n. doc_id=$docinfo[id], weight=$docinfo[weight]";
                                    

                                    //fetch each and every matched content 
                                    $devicesinfo [] = $device['id'];
                                
                                }
                            }
                        
                        }
                    }
           
            }
            else {
                  $devices = $this->searchDevices($keyword);
               
            }
			
			if (Zend_Registry::get ( 'config' )->nexva->application->search->sphinx->enable and count ( $devicesinfo ) != 0) {
				$deviceModel = new Model_Device ( );
				$devices = $deviceModel->findDevicesByIds ( $devicesinfo, 12 );
				
				$i = 0;
				$results = array();
				
				foreach ( $devices as $device ) {
					$device = $device->toArray ();
					
					$results [$i] ['id'] = $device ['device_id'];
					$results [$i] ['img'] = file_exists ( './vendors/device_pix/' . $device ['wurfl_actual_root_device'] . '.gif' ) ? '/vendors/device_pix/' . $device ['wurfl_actual_root_device'] . '.gif' : '/web/images/unknown_phone_icon.png';
					
					$results [$i] ['phone'] = $device ['brand'] . " " . $device ['model'];
					
					$i ++;
				}
				
				
				$this->view->devices = ($this->enablePagenator ( $results ));
			
			}
			else
			    $this->view->devices = ($this->enablePagenator ( $devices ));
			 
      
       $controllerName = $this->getRequest()->getControllerName();
       $actionName = $this->getRequest()->getActionName();
       $this->view->keyword = $keyword;
       $this->view->baseUrlfo = $_SERVER ['SERVER_NAME'] . '/' . $controllerName . '/' . $actionName;
      
      
    }
  }
  
  
    public function searchAction() {
    	
    	
        // set session to get returinig url
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $_SERVER["REQUEST_URI"];

        $keyword    = $this->_request->q;
        $category   = $this->_getParam('selcat', null);
        
        $this->view->keyword = $keyword;
        $this->view->showPageTitle = true;
        $this->view->searchKeyword = $keyword;
        if (!empty($keyword)) {
            $this->view->pageTitle = 'Search Results for "' . $keyword . '"';
        } else {
            $this->_redirect('/app');
        }
        
        $this->setCategories();        
        $this->view->enableCategories = true;
        $this->view->categories = $this->_categories;
 
        $product = new Model_Product();
        $product->setAppFilterRules($this->themeMeta->USER_ID);
        $product->appFilter = $this->themeMeta->WHITELABLE_APP_FILTER;
        
        $limit      = Zend_Registry::get('config')->mobile->paginator->limit;
        $page       = $this->_getParam('page', 1); 
        $products = $product->getSearchProductsByKey($this->getDeviceId(), $keyword, $category, $limit, $page);
        $this->view->products   = $products;
        $this->view->pageNum    = $page;

        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();
        $paramQ = $this->getRequest()->getParam('q');
        if ($actionName == 'search') {
            $search = '/q/' . $paramQ . '/selcat/' . $category;
        }

        $this->view->baseUrlfo = $_SERVER ['SERVER_NAME'] . '/' . $controllerName . '/' . $actionName . $search;
    }
    
    

}

?>
