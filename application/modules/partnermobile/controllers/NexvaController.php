<?php

class Partnermobile_NexvaController extends Nexva_Controller_Action_Partnermobile_MasterController {
    
    public function init() 
    { 
        parent::init(); 
       
            
    }
   
   
    
    //Free applications 
    public function indexAction()
    {
  
      
        $isLowEndDevice = $this->_isLowEndDevice; 
       
        $chapProducts = new Partner_Model_ChapProducts();
        $freeApps = $chapProducts->getNexvaProductIdsByDevice($this->_chapId, $this->_deviceId, 100, $this->_grade);
        
        $this->view->chap_id = $this->_chapId;

        //get Product details
        if (count($freeApps) > 0) 
        {
    	    $this->view->showResults = TRUE;
            $freeApps = $this->getProductDetails($freeApps, $this->_deviceId);
        }
   
        $pagination = Zend_Paginator::factory($freeApps);
        $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
        $pagination->setItemCountPerPage(10);
        
        $this->view->nexvaApps = $pagination;
        


        //currency details by chap
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($this->_chapId);
        $this->view->currencyDetails = $currencyDetails;
        
        $brandName = $this->_deviceDetails['brand_name'];
        $deviceOs = $this->_deviceDetails['device_os'];
        
        $lowEndDevicesOs = array('Android', 'iPhone OS', 'iOS', 'Windows Mobile OS', 'RIM OS', 'BlackBerry');
        $lowEndDeviceBrand = array('Nokia');
        
        //check the device and change the layout if the device old-end
        if(in_array($brandName,$lowEndDeviceBrand) || !in_array($deviceOs,$lowEndDevicesOs))
        {
        	$this->_isLowEndDevice = TRUE; // set true to low end devices
        	$this->_helper->layout->setLayout('partnermobile/home_low_end');
        }
        else
        {
        	$this->_isLowEndDevice = FALSE; // set false to modern devices
        	$this->_helper->layout->setLayout('partnermobile/home_nexpager');
        }
        
        /*App Wall Statics*/
        
            $appWall=new Partnermobile_Model_AppWall();
            $sessionId = Zend_Session::getId();            
            $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');
            $auth = Zend_Auth::getInstance();        
            $userId = isset($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;
            $platform= new Model_Platform();
            $platformId=$platform->getPlatformIdByName($this->_deviceDetails['device_os']);
            $appWall->saveStatics($sessionId, $ipAddress, $this->_chapId, $userId,$this->_chapLanguageId,$platformId['id'], $this->_deviceId);            
       
        /*End*/
        
        if($isLowEndDevice):
            $this->_helper->viewRenderer('top-nexva-low-end');
        endif;
    }
    
    
    /**
     * Returns product details based on product IDs
     * @param $products product ids array
     * @param $deviceId Device ID
     */
    public function getProductDetails($products, $deviceId)
    {
    	// Set image path from config parameter
    	$viewHelp = new Nexva_View_Helper_ProductImages ();
    
    	$this->serverPathThumb = $viewHelp->productImages() . '/vendors/phpThumb/phpThumb.php?src=';
    	$this->serverPath = $viewHelp->productImages() . '/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/';
    
    	$visualPath = Zend_Registry::get('config')->product->visuals->dirpath;
    
    	$productMeta = new Model_ProductMeta();
    	$productImages = new Model_ProductImages();
    	$userMeta = new Model_UserMeta();
    	$ratingModel = new Model_Rating();
    	$productModel = new Model_Product();
    	$productLanguageMeta = new Model_ProductLanguageMeta();
    	$modelDownloadStats = new Model_StatisticDownload();
    	$model_ProductBuild = new Model_ProductBuild();
    
    	foreach ($products as &$product)
    	{
    		//Add translation. get the translation for product name and overite if the translation available
    		/*$product_name = $productLanguageMeta->getTranslationValue($product['product_id'], $this->_chapLanguageId, 'PRODUCT_NAME');
    		 if($product_name):
    		$product["name"] = $product_name;
    		endif;*/
    
    		//Get build updates if available
    		$product["updates_available"] = 0;
    		$downloadedProduct = array();
    		if($this->_userId){
    			$downloadedProduct = $modelDownloadStats->getDownloadedBuildsByUserId($this->_userId, 'MOBILE', $product['product_id']);
    		}
    		if(count($downloadedProduct)>0){
    			$product["updates_available"] = $model_ProductBuild->checkProductBuildUpdated($downloadedProduct[0]['product_id'], $downloadedProduct[0]['platform_id'], $downloadedProduct[0]['language_id'], $downloadedProduct[0]['date']);
    		}
    
    		$product["thumbnail"] = $visualPath . '/' . $product["thumbnail"];
    
    		$productMeta->setEntityId($product['product_id']);
    
    		/* Add translation.
    		 * Get the translations for product name,description,brief_description and overite if the translations are available.
    		* If not let it be with english language.
    		*/
    
    
    		$productTranslation = $productLanguageMeta->loadTranslation($product['product_id'], $this->_chapLanguageId);
    		/*$appArr = array('id' => $product['product_id']); //Temp
    		$productTranslation = $productLanguageMeta->translateProduct($appArr, $this->_chapLanguageId, $this->_chapId);*/
    
    		//this is temporary for MTN Iran cell
    		/* @todo: needs to be removed once we bags all translations
    		*/
    
    		/*if($_SERVER['REMOTE_ADDR'] == '106.186.127.174'){
    		echo $this->_chapLanguageId.'###'.$this->_chapId;
    		print_r($productTranslation);
    		}*/
    
    		$product["name"] = ($productTranslation->PRODUCT_NAME)? stripslashes(strip_tags($productTranslation->PRODUCT_NAME)) : $product["name"];
    		 
    		$product["description"] = ($productTranslation->PRODUCT_DESCRIPTION)? stripslashes(strip_tags($productTranslation->PRODUCT_DESCRIPTION)) : stripslashes(strip_tags($productMeta->FULL_DESCRIPTION));
    		$product["brief_description"] = ($productTranslation->PRODUCT_SUMMARY)? stripslashes(strip_tags($productTranslation->PRODUCT_SUMMARY)) : stripslashes(strip_tags($productMeta->BRIEF_DESCRIPTION));
    
    		//below codes commented
    		/*$product['description'] = stripslashes(nl2br(strip_tags($productMeta->FULL_DESCRIPTION, "<br>")));
    		$product['brief_description'] = stripslashes(strip_tags($productMeta->BRIEF_DESCRIPTION));*/
    
    		//Add product images
    		$productImage = $productImages->getImageById($product['product_id']);
    
    		if (count($productImage) > 0) {
    
    		$product['image'] = $visualPath . '/' . $productImage->filename;
    		}
    
    		//Add Vendor Name
    		$userMeta->setEntityId($product['user_id']);
    				$product['vendor'] = $userMeta->COMPANY_NAME;
    
    				//Add Rating
    				$product['avg_rating'] = $ratingModel->getAverageRatingByProduct($product['product_id']);
    			$product['total_ratings'] = $ratingModel->getTotalRatingByProduct($product['product_id']);
    
    			$product['supported_platforms'] = $productModel->getSupportedPlatforms($product['product_id']);
    
    			//check if it's already rated
    			$ratingNamespace = new Zend_Session_Namespace('Ratings');
    
    			$productRated = false;
    			$userRating = 1;
    			if (isset($ratingNamespace->ratedProducts)) {
    			$ratedProducts = $ratingNamespace->ratedProducts;
    			if (isset($ratedProducts[$product['product_id']])) {
    			$productRated = true;
    			$userRating = $ratedProducts[$product['product_id']];
    	}
    	}
    
    	$product['product_rated'] = $productRated;
    
    	unset($product['id']);
    	unset($product['session_id']);
    	unset($product['ip']);
    	unset($product['chap_id']);
    	unset($product['language_id']);
    	unset($product['source']);
    	unset($product['platform_id']);
    	unset($product['user_id']);
    	unset($product['keywords']);
    	unset($product['pro_count']);
    	}
    
    		return $products;
    	}
    
}