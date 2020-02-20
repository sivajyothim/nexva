<?php

class Default_IndexController extends Nexva_Controller_Action_Web_MasterController {

  public function init() {
      

        parent::init();
        $this->setLastRequestedUrl();
      
        
        

    /* Initialize action controller here */
  }

  public function indexAction() {
                  
    $this->_helper->layout->setLayout('web/web_homepage');

    $productsModel = new Model_Product();
    
    


    /*$frontpageCategories = $this->__getProductsForHomepage(3, 4);
    $this->view->frontpageCategories = $frontpageCategories;*/

   //get newest items @param : number of categories, number of items for the cart
   $newestItems = $this->getNewest(1, 10);
   $this->view->newestItems = $newestItems;
   


   //get most viewed items @param number of items for the cart
   $mostViewedItems = $this->getMostViewed(10);
   $this->view->mostViewedItems = $mostViewedItems;
   
   


   // get newest apps
   $newestGames = $this->getNewestGames();
   $this->view->newestGames = $newestGames;

    //get Android apps
    $androidApps = $this->getAndroidApps(10);
    //Zend_Debug::dump($androidApps);die();
    $this->view->androidApps = $androidApps;

    // get payment gateways
    $paymentGatewaysModel = new Model_PaymentGateway();
    $paymentGateways = $paymentGatewaysModel->getPaymentGatewaysThatSupport('web');
    
    $this->view->paymentGateways = $paymentGateways;
    
    $this->view->featuredApps   = $this->__getFeatured();
    //$x = $this->__getFeatured();
    //Zend_Debug::dump($this->view->featuredApps);die();
    //die();

    
  }

    protected function getDeviceId() {
        $session = new Zend_Session_Namespace("devices");
        $devieArray = $session->selectedDevices;

        $deviceId = null;

        if ($devieArray !== null && !empty($devieArray)) {
            $deviceId = $devieArray['id'];
        }

        return $deviceId;
    }

  public function mobileAction() {
      $chapId   = (int) $this->_getParam('u', null);
      
      $themeMeta    = new Model_ThemeMeta();
      $themeMeta->setEntityId($chapId);
      $themeData    = $themeMeta->getAll();
      
      if (isset($themeData->WHITELABLE_WEB_ACCESS_URL)) { //redirect url set, redirect there
            $this->_redirect($themeData->WHITELABLE_WEB_ACCESS_URL);
      }
      
      $this->view->themeData  = $themeMeta;
  }

  private function __getFeatured() {
      $productsModel = new Model_Product();
      $output = array();


      $deviceModel = new Model_Device();
      $devices = $deviceModel->getSelectedDevicesFromSession();
      
      $products = $productsModel->getFeaturedProducts($devices);
      
      //Zend_Debug::dump($products);die();

      if (count($products) == 0)
      $products = $productsModel->getFeaturedProducts();

      $output = array();

      $i = 0;
      foreach ($products as $product) {
          $userModel = new Model_User();
          $cp_name = $userModel->getMetaValue($product['uid'], "COMPANY_NAME");

            $output[$i]['product']    = $product;
            preg_match_all('/[a-zA-Z0-9\s]+/', $cp_name, $match);
            $cp_name      = implode('', $match[0]);
            $output[$i]['cpname']   = $cp_name; 
            $output[$i]['linkname'] = str_replace(' ', '-', ($cp_name == "") ? "Unknown Vendor" : $cp_name);
            $i++;
      }
      return $output;
  }
  
  public function errorAction() {
    // action body
    //phpinfo();
  }

  /**
   * Returns a set of randomly selected parent (top level) categories
   * @todo Remove once the code is tested and on live
   * @deprecated 
   * @see __getProductsForHomepage instead
   * @param int $numberofCategories
   * @return Zend_Db_Table_Rowset_Abstract
   */
  protected function doGetFrontpageCategoryList($numberofCategories = 4, $numberOfDevicesPerCategory = 8) {

    $categoryModel = new Model_Category();

    $categories = $categoryModel->fetchAll('parent_id = 0 AND status = 1', 'RAND(NOW())', $numberofCategories);

    $productModel = new Model_Product();

    $i = 0;

    $devices = $this->getSelectedDeviceIds();

    foreach ($categories as $category) {
      $result[$i]['name'] = $category->name;
      $result[$i]['id'] = $category->id;
      $result[$i]['products'] = $productModel->getProductsForCategory($devices, $category->id, 4);
      $i++;
    }

    //return $categories;
    return $result;
  }
  
    /*private function __getProductsForHomepage($numCategories, $numProductsForCat) {
        $cache  = Zend_Registry::get('cache');
        $key    = 'FRONTPAGE_CATEGORIES';
        if (($categories = $cache->get($key)) === false) {
            $categoryModel  = new Model_Category();
            $categories     = $categoryModel->fetchAll('parent_id = 0 AND status = 1', 'RAND(NOW())', $numCategories);
            
            $cache->set($categories, $key, 3600);    
        }
        
        $devices        = $this->getSelectedDeviceIds();
        $productModel   = new Model_Product();
        $i = 0;
        foreach ($categories as $category) {
          $result[$i]['name'] = $category->name;
          $result[$i]['id'] = $category->id;
          $result[$i]['products'] = $productModel->getProductsForCategory($devices, $category->id, $numProductsForCat);
          if (empty($result[$i]['products'])) {
              //clear cache since we don't want empty cats in cache. better to query again on next request
              $cache->remove($key);
          }
          $i++;
        }
        return $result;
    }*/

    private function getNewest($numCategories, $numProductsForCat) {
        $cache  = Zend_Registry::get('cache');
        $key    = 'FRONTPAGE_CATEGORIES';
        if (($categories = $cache->get($key)) === false) {
            $categoryModel  = new Model_Category();
            $categories     = $categoryModel->fetchAll('parent_id = 0 AND status = 1', 'RAND(NOW())', $numCategories);

            $cache->set($categories, $key, 3600);
        }

        $devices        = $this->getSelectedDeviceIds();
        $productModel   = new Model_Product();
        $i = 0;
        foreach ($categories as $category) {
            $result[$i]['name'] = $category->name;
            $result[$i]['id'] = $category->id;
            $result[$i]['products'] = $productModel->getNewest($devices, $category->id, $numProductsForCat);
            if (empty($result[$i]['products'])) {
                //clear cache since we don't want empty cats in cache. better to query again on next request
                $cache->remove($key);
            }
            $i++;
        }
        return $result;
    }

    private function getNewestGames()
    {
        $productModel   = new Model_Product();
        return $productModel->getNewestGame(7,10);
    }

    private function getMostViewed( $numProductsForCat) {
        $productModel   = new Model_Product();
        return $productModel->getMostViewed($numProductsForCat);
    }

    private function getAndroidApps($numOfApps)
    {
        $productModel   = new Model_Product();
        return $productModel->getAndroidApps($numOfApps);
    }


  /**
   * Create productId
   */
  public function badgeoldAction() {
    $this->_helper->getHelper('layout')->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);
    $productId = $this->_request->id;
    $size = $this->_request->s;
    $template = isset($this->_request->template) ? $this->_request->template : 'nexva';

    
    $badge = new Nexva_Badge_Badge($productId);
    $badge->createBadge($size, $template);
  }
  
    /**
     * 
     * This is the new badge action. Leave it here till it's confired then remove the old badge action
     */
    public function badgeAction() {

        
        $this->_helper->getHelper('layout')->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $productId  = $this->_request->id;
        $size       = ($this->_request->s) ? $this->_request->s : 'large';
        $template   = isset($this->_request->template) ? strtolower($this->_request->template) : 'nexva';
        $chapId     = $this->_getParam('chap', false);
        if (!$productId) {
            die();   
        }

        $badge      = new Nexva_Badge_BadgeImagick($productId, $chapId);
        $opts       = array(
            'show_logo' => ($template == 'nexva') ? true : false,
            'type'      => $size,
        );
        $badge->displayBadge($opts);
        die();
    }

  
  /**
   * get the ratings
   */
  public function ratingAction() {
    $this->_helper->getHelper('layout')->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);
    $productId = $this->_request->id;
    $review = new Nexva_Badge_Fivestar($productId);
    $review->ratings();
  }

  /**
   * Share action to share images
   */
  public function shareAction() {
    $this->view->appName = 'Apps';
    $this->view->id = $productId = $this->_request->id;
    $product = new Model_Product();
    $productDetails = $product->getProductDetailsById($productId);
    if (empty($productDetails))
      $this->_redirect('/');
    $this->view->content = $productDetails;
  }
  
    public function testAction() {
    	
    	   $this->_helper->getHelper('layout')->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);
    	
    	Zend_Debug::dump(sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid());
    	
    }
    
}