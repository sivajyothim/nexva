<?php
class Default_WhitelabelindexController extends Nexva_Controller_Action_Web_MasterController {

    public function init() {
        parent::init();

        /*Initialize action controller here */
    }

    public function indexAction() {
        //$recommendations  = new  Nexva_Recommendation_Recommendation();
        //$recommendations->getRecommendationsForProduct(2122, 14707);

        Zend_Controller_Action_HelperBroker::addHelper(new Nexva_Controller_Action_Helper_CheckTheme());
        $theme = $this->_helper->CheckTheme->CheckTheme();

        $cssPath =   'custom/support_files/'.$theme->getEntityId()."/css/default.css";

            if(is_readable($cssPath)) {
                
                $this->view->headLink()->appendStylesheet("$cssPath");
                
                $theme = false;
            }

        if(false !== $theme){
           
            $layout =   $theme->LAYOUT;
            $this->_helper->layout->setLayout('custom/'.$layout."/".$layout);
            $this->view->theme  = $theme;         

        }else{           
            $this->_helper->layout->setLayout('web/web_homepage');
        }
        $productsModel = new Model_Product();        
        $featuredProducts = $productsModel->getFeaturedProducts();
        $this->view->featuredProducts = $featuredProducts;

        $userMeta = new Model_UserMeta();
        $compnayName  =  $userMeta->getAttributeValue($featuredProducts[0]['uid'],'COMPANY_NAME');

        $this->view->venderName  = $compnayName;
        $this->view->venderId  = $featuredProducts[0]['uid'];
        $frontpageCategories =  $this->doGetFrontpageCategoryList(3);

        $this->view->frontpageCategories = $frontpageCategories;

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
            $output['linkname']	= str_replace(' ', '-', ($cp_name == "") ? "Unknown Vendor" : $cp_name);

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







}

