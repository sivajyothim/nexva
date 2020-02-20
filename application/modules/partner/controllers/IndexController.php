<?php

class Partner_IndexController extends Nexva_Controller_Action_Partner_MasterController {

	
	
public function init() {

	    parent::init();
	    


        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/partner/default/assets/js/jquery.carouFredSel-5.2.3-packed.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/partner/default/assets/js/jquery.tabify.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/partner/default/assets/js/slicss_js/easySlider1.7.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/partner/default/assets/js/jquery.easing.1.3.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/partner/default/assets/js/slides.min.jquery.js');

       $this->_helper->layout->setLayout('partner/web_home_page');
        


    }
        

    public function indexAction() {
    
        $chap = new Zend_Session_Namespace('partner');
        $this->view->chap_id = $chap->id;
        //$this->_chapId
    	//Get device Id, if choosen
        $deviceId = $this->getDeviceId();

        $chapProducts = new Partner_Model_ChapProducts();

        $noOfStaffPicks = !empty($this->_featuredApps) ? $this->_featuredApps : 15;
        $noOfBanners = !empty($this->_bannerCount) ? $this->_bannerCount : 6;

        /************** Bannered apps ******************************/
        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) 
        {
            $featuredApps = $chapProducts->getBanneredProductsbyDevice($this->_chapId, $deviceId, $noOfBanners, '',$this->_grade, $this->_userType);
        } 
        else 
        {
            $featuredApps = $chapProducts->getBanneredProductIds($this->_chapId, $noOfBanners, $this->_grade, $this->_userType);
        }

        //get Product details of bannered apps
        if (count($featuredApps) > 0) 
        {
            $featuredApps = $this->getProductDetails($featuredApps, $deviceId);
        }

        /*$themeMetaModel = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($this->_chapId);

        $this->view->showSlider = $themeMetaModel->PARTNER_SITE_SLIDER;*/

        /************** Featured apps = Staff Picks (previously) ******************************/
        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId))
        {

            $staffPicks = $chapProducts->getFeaturedProductsbyDevice($this->_chapId, $deviceId, $noOfStaffPicks, '', $this->_grade, $this->_userType);
        } 
        else 
        {
            $staffPicks = $chapProducts->getFeaturedProductIds($this->_chapId, $noOfStaffPicks, $this->_grade, $this->_userType);
        }
        
        //get Product details of featured apps
        if (count($staffPicks) > 0) 
        {
            $staffPicks = $this->getProductDetails($staffPicks, $deviceId);
        }
         
        
      /************** Newest apps ******************************/
        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) 
        {
            $newestApps = $chapProducts->getNewestProductIdsByDevice($this->_chapId, $deviceId, 15, $this->_grade, $this->_userType);
        } 
        else 
        {
            $newestApps = $chapProducts->getNewestProductIds($this->_chapId, 15, $this->_grade, $this->_userType);
        }
        
        //get Product details of newest apps
        if (count($newestApps) > 0) 
        {
            $newestApps = $this->getProductDetails($newestApps, $deviceId);
        }
        
        //print_r($newestApps); die();
        /************** Free apps ******************************/
        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) 
        {
            $freeApps = $chapProducts->getFreeProductIdsByDevice($this->_chapId, $deviceId, 15, $this->_grade, $this->_userType);
        } 
        else 
        {
            $freeApps = $chapProducts->getFreeProductIds($this->_chapId, 15, $this->_grade, $this->_userType);
        }
        
        //get Product details of newest apps
        if (count($freeApps) > 0) 
        {
            $freeApps = $this->getProductDetails($freeApps, $deviceId);
        }
        
        
        /************** Paid apps ******************************/
        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) 
        {
            $paidApps = $chapProducts->getPaidProductIdsByDevice($this->_chapId, $deviceId, 15, $this->_grade, $this->_userType);
        } 
        else 
        {
            $paidApps = $chapProducts->getPaidProductIds($this->_chapId, 15, $this->_grade, $this->_userType);
        }
        
        //get Product details of newest apps
        if (count($paidApps) > 0) 
        {
            $paidApps = $this->getProductDetails($paidApps, $deviceId);
        }
                        
        /************** Most viewed apps ******************************/
        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) 
        {
            $mostViewd = $chapProducts->getMostViewedProductIdsByDevice($this->_chapId, $deviceId, 5 , $this->_grade, $this->_userType);
        } 
        else 
        {
            $mostViewd = $chapProducts->getMostViewedProductIds($this->_chapId, 5, $this->_grade, $this->_userType);
        }
        
        //get Product details of newest apps
        if (count($mostViewd) > 0) 
        {
            $mostViewd = $this->getProductDetails($mostViewd, $deviceId);
        }
        
        
        /************** Most downloaded apps ******************************/
        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId)) 
        {
            $topDownloads = $chapProducts->getTopProductIdsByDevice($this->_chapId, $deviceId, 15, $this->_grade, $this->_userType);
        } 
        else 
        {
            $topDownloads = $chapProducts->getTopProductIds($this->_chapId, 15, $this->_grade, $this->_userType);
        }
        
        //get Product details of newest apps
        if (count($topDownloads) > 0) 
        {
            $topDownloads = $this->getProductDetails($topDownloads, $deviceId);
        }
        

        
        $this->view->featuredApps = $featuredApps;
        $this->view->staffPicks = $staffPicks;        
        $this->view->newestApps = $newestApps;
        $this->view->freeApps = $freeApps;
        $this->view->paidApps = $paidApps;
        $this->view->mostViewd = $mostViewd;
        $this->view->topDownloads = $topDownloads;  
   
        

    }
    
    public function pageAction()
    {
        $this->_helper->layout->setLayout('partner/web_inner_page');
         
        $pageId = trim($this->_request->id);
        //$chapId = 7036;
        
        $pageModel = new Partner_Model_WlPage();
        $pageDetails = $pageModel->getPageDetailsById($this->_chapId, $pageId);
        
        $this->view->pageDetails = $pageDetails;
        $this->view->pageName = isset($pageDetails->title) ? $pageDetails->title : '';
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
        $productLanguageMetaModel = new Model_ProductLanguageMeta();

        foreach ($products as &$product) 
        {                         
            $product["thumbnail"] = $visualPath . '/' . $product["thumbnail"];

            $productMeta->setEntityId($product['product_id']);
            
            // Following 2 lines commented and added next below to get the translations
            //$product['description'] = stripslashes(nl2br(strip_tags($productMeta->FULL_DESCRIPTION, "<br>")));
            //$product['brief_description'] = stripslashes(strip_tags($productMeta->BRIEF_DESCRIPTION));

            /* Add translation. 
             * Get the translations for product name,description,brief_description and overite if the translations are available.
             * If not let it be with english language.
             */
            $productTranslation = $productLanguageMetaModel->loadTranslation($product['product_id'], $this->_chapLanguageId);

            //this is temporary for MTN Iran cell
            /* @todo: needs to be removed once we bags all translations
             */
            //if(23045 != $this->_chapId){
                $product["name"] = ($productTranslation->PRODUCT_NAME)? stripslashes(strip_tags($productTranslation->PRODUCT_NAME)) : $product["name"];
           // }
            $product["description"] = ($productTranslation->PRODUCT_DESCRIPTION)? stripslashes(strip_tags($productTranslation->PRODUCT_DESCRIPTION)) : stripslashes(strip_tags($productMeta->FULL_DESCRIPTION));
            $product["brief_description"] = ($productTranslation->PRODUCT_SUMMARY)? stripslashes(strip_tags($productTranslation->PRODUCT_SUMMARY)) : stripslashes(strip_tags($productMeta->BRIEF_DESCRIPTION));
            
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
            
            $product['supported_platforms'] = $productModel->getSupportedPlatforms($product['product_id'], $this->_chapPlatform);
            
            //check and add if the app is contain multiple platforms or only android
            $product['app_platform_type'] = $productModel->checkAppPlatforms($product['supported_platforms'], $product['product_id'],$this->_chapPlatform);

            //If platform count is 1 than get the platform id
            if(count($product['supported_platforms']) == 1):
                $product['build_id'] = $product['supported_platforms'][0]->build_id;
            endif;

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

	                 
//            if((!array_key_exists('build_id', $product)) and $deviceId)    
//            {                
//	                $apiModelChapProducts = new Api_Model_ChapProducts();
//	                $buildId = $apiModelChapProducts->getProductBuildId($product['product_id'], $deviceId);
//	                
//                
//	                $product['build_id'] =  $buildId;                
//                
//            }
//            if (isset($product['build_id'])) 
//            {
//                if ($s3file) 
//                {
//                    $productDownloadCls = new Nexva_Api_ProductDownload();
//                    $buildUrl = $productDownloadCls->getBuildFileUrl($product['product_id'], $product['build_id']);
//                    $product['download_app'] = $buildUrl;
//                }
//            }

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
    

        
    //this function will check and return app platforms
    /*public function checkAppPlatforms($supportedPlatforms, $appId)
    {
        $platformType = ''; //Android or Multiple
        $buildType = ''; //URL or File or Both
        $chapterPlatformType = $this->_chapPlatform; //Android or Multiple

        //get platform type for the appId  
        $countAppPlatforms = count($supportedPlatforms);
        if($countAppPlatforms > 1):
            $platformType = 'Multiple';
        else:
            //if platform is android
            //if($supportedPlatforms[0]->platform_id == 12):
              //$platformType = 'Android';
           // endif;
        endif;
        
        //get build type for the appId  
        $modelProductBuild = new Partner_Model_ProductBuilds();
        $buildTypes = $modelProductBuild->getFileTypesByBuildId($appId);
        
        $countBuildTypes = count($buildTypes);
        if($countBuildTypes > 1):
            $buildType = 'Both';
        else:
            $buildType = $buildTypes[0]->build_type;
        endif;

        
        //If the app is containing mulltiple platform and chapter platform is multiple return true
        if(($chapterPlatformType == 'MULTIPLE_PLATFORM') && ($platformType == 'Multiple') && (($buildType == 'Both') || ($buildType == 'files'))):
            return true;
        else:
            return false;
        endif;
    }*/
}