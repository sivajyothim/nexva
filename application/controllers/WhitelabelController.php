<?php
class Default_WhitelabelController extends Nexva_Controller_Action_Web_MasterController {

    public function init() {
        parent::init();

        /*Initialize action controller here */
    }

    public function indexAction() {
        //$recommendations  = new  Nexva_Recommendation_Recommendation();
        //$recommendations->getRecommendationsForProduct(2122, 14707);
  //$this->_helper->layout ()->disableLayout ();
 // $this->_helper->viewRenderer->setNoRender ( true );
        Zend_Controller_Action_HelperBroker::addHelper(new Nexva_Controller_Action_Helper_CheckTheme());
        $theme  = $this->_helper->CheckTheme->CheckTheme();



        if(false !== $theme) {

            $cssPath =   'custom/support_files/'.$theme->getEntityId()."/css/default.css";
            $customCssPath =    'custom/support_files/'.$theme->getEntityId()."/css/".$theme->LAYOUT."/css/layout.css";

            if(file_exists($customCssPath)) {
                $cssPath =  $customCssPath;
            }

            if(is_readable($cssPath)) {
                $this->view->headLink()->appendStylesheet("$cssPath");
            }

            $layout =   $theme->LAYOUT;
            $this->_helper->layout->setLayout('custom/'.$layout."/".$layout);

            $userMeta   =   new Model_UserMeta();
            $userMeta->setEntityId($theme->getEntityId());

            $userModel  =   new Model_User();
            $this->view->user   =   $userModel->fetchAll($theme->getEntityId())->current();
            $this->view->user_meta   =   $userMeta;
            $this->view->theme  = $theme;
            $this->view->logo = "/custom/support_files/".$theme->getEntityId()."/logos/".$theme->LOGO;

            $productsModel      = new Model_Product();
            $featuredProducts   = $productsModel->getFeaturedProducts();
            $this->view->featuredProducts = $featuredProducts;
            $userMeta = new Model_UserMeta();
            
            $compnayName  =  $userMeta->getAttributeValue($featuredProducts[0]['uid'],'COMPANY_NAME');
            $this->view->venderName  = $compnayName;
            $this->view->venderId    = $featuredProducts[0]['uid'];
            
            //$userProducts = $productsModel->getProductsByCp($this->getSelectedDeviceIds(), $theme->getEntityId());
            //$paginator = Zend_Paginator::factory($userProducts);
            //$paginator->setItemCountPerPage(10);
            //$paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 0));
            //$this->view->userProducts = $paginator;
            
            //$this->view->userProductsForView    =  $productsModel->getProductsByCp($devices = $this->getSelectedDeviceIds(),$theme->getEntityId(),20);

           $temp = $productsModel->getProductsByCp($this->getSelectedDeviceIds(),$theme->getEntityId(),20);
           // Zend_Debug::dump($temp);
          

            $paginator  =  Zend_Paginator::factory($temp);
            $paginator->setItemCountPerPage(20);
            $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));


              if (!is_null($paginator)) {

                foreach ($paginator as $row) {

                    $products[] = $productsModel->getProductDetailsById($row->product_id);
                   
                }
            }

            $this->view->userProductsForView = $products;
            $this->view->paginator = $paginator;
            $config =   Zend_Registry::get('config');
            $this->view->baseUrl = $_SERVER['SERVER_NAME']."/whitelabel/index/page/";

            

        }else {
            $this->_helper->layout->setLayout('web/web_homepage');
            $productsModel = new Model_Product();
            $featuredProducts = $productsModel->getFeaturedProducts();
            $this->view->featuredProducts = $featuredProducts;
            $userMeta = new Model_UserMeta();
            $companyName = $userMeta->getAttributeValue($featuredProducts[0]['uid'], 'COMPANY_NAME');

            $this->view->cpUserName = $companyName;
            $this->view->cpUserId = $featuredProducts[0]['uid'];
            $frontpageCategories = $this->doGetFrontpageCategoryList(3);
            $this->view->frontpageCategories = $frontpageCategories;
        }
    }

    public function featuredAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        $productsModel = new Model_Product();
        $output = array();

        if( is_null($this->_request->id)) {

            $deviceModel = new  Model_Device();
            $devices = $deviceModel->getSelectedDevicesFromSession();

            $products = $productsModel->getFeaturedProducts($devices);

            if( count($products)== 0 )
                $products = $productsModel->getFeaturedProducts();



            $output = array();

            $i=0;
            foreach($products as $product) {
                $userModel = new Model_User();
                $cp_name = $userModel->getMetaValue($product['uid'], "COMPANY_NAME");

                $output[$i]['id'] 	= $product['id'];
                $output[$i]['name']	= $product['name'];
                $output[$i]['userid'] = $product['uid'];
                $output[$i]['linkname']	= str_replace(' ', '-', ($cp_name == "") ? "Unknown Vendor" : $cp_name);
                $i++;

            }

        }
        else //send product that is being requested
        {
            $product = $productsModel->getProductDetailsById($this->_request->id);

            $userModel = new Model_User();

            $cp_name = $userModel->getMetaValue($product['uid'], "COMPANY_NAME");


            $platformModel = new Model_Platform();

            $output['id']           = $product['id'];
            $output['title']		= $product['name'];
            $output['desc'] 		= $product['desc'];
            $output['downloads']	= (12*$this->_request->id) + 224;
            $output['price']		= $product['cost'];
            $output['size']         = 1.15+$this->_request->id;
            $output['poster']		= $product['screenshots'][0];
            $output['platform']     = $platformModel->getPlatformName($product['platform_id']);
            $output['vendor']       = ($cp_name == "") ? "Unknown Vendor" : $cp_name;
            $output['rating']       = 5-(rand(0,4));
            $output['userid']       = $product['uid'];
            $output['linkname']     = str_replace(' ', '-', ($cp_name == "") ? "Unknown Vendor" : $cp_name);
        }

        echo json_encode($output);
    }



    public function errorAction() {
        // action body
        //phpinfo();
    }


    /**
     * Returns a set of randomly selected parent (top level) categories
     *
     * @param int $numberofCategories
     * @return Zend_Db_Table_Rowset_Abstract
     */
    protected function doGetFrontpageCategoryList($numberofCategories = 4, $numberOfDevicesPerCategory = 8) {

        $categoryModel = new Model_Category();

        $categories = $categoryModel->fetchAll('parent_id = 0 AND status = 1', 'RAND(NOW())', $numberofCategories);

        $productModel = new Model_Product();

        $i = 0;

        $devices = $this->getSelectedDeviceIds();

        foreach( $categories as $category ) {
            $result[$i]['name'] = $category->name;
            $result[$i]['id'] = $category->id;
            $result[$i]['products'] = $productModel->getProductsForCategory($devices, $category->id, 4);

            $i++;
        }

        //return $categories;
        return $result;


    }


    /**
     * Returns a set of randomly selected parent (top level) categories
     *
     * @param int $numberofCategories
     * @param int $cpId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    protected function doGetFrontpageCategoryListForCp($numberofCategories = 6, $numberOfDevicesPerCategory = 8,$cpId) {

        $categoryModel = new Model_Category();

        $categories = $categoryModel->fetchAll('parent_id = 0 AND status = 1', 'RAND(NOW())', $numberofCategories);

        $productModel = new Model_Product();
        $i = 0;
        $devices = $this->getSelectedDeviceIds();

        foreach( $categories as $category ) {
            $result[$i]['name'] = $category->name;
            $result[$i]['id']   = $category->id;
            $result[$i]['products'] = $productModel-> getProductsByCp($devices,  $cpId,4);
            $i++;
        }
        //return $categories;
        return $result;


    }

}

