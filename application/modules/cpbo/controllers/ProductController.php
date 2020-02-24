<?php

class Cpbo_ProductController extends Nexva_Controller_Action_Cp_MasterController {

    protected $_flashMessenger;
	
	function preDispatch() {
		if (! Zend_Auth::getInstance ()->hasIdentity ()) {
			$skip_action_names = array ('login', 'register', 'forgotpassword', 'resetpassword', 'claim' );
			if (! in_array ( $this->getRequest ()->getActionName (), $skip_action_names )) {
				$requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
				$session = new Zend_Session_Namespace ( 'lastRequest' );
				$session->lastRequestUri = $requestUri;
				$session->lock ();
				$this->_redirect ( '/user/login' );
			} else {
				if ($this->getRequest ()->getActionName () == 'claim') {
					$requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
					$session = new Zend_Session_Namespace ( 'lastRequest' );
					$session->lastRequestUri = $requestUri;
					$session->lock ();
					$this->_redirect ( '/user/login/ref/appclaim' );
				} else
					$this->_redirect ( '/user/login' );
			
			}
			/*
            if (!in_array($this->getRequest()->getActionName(), $skip_action_names)) {
                $this->_redirect (CPBO_PROJECT_BASEPATH.'user/login');
            }
            */
		}
	}

    /* Initialize action controller here */

    public function init() {
        // include Ketchup libs
        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css');
        //$this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/ketchup/js/jquery.min.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js');
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        if(!in_array($chapId, array('585474','585480'))){
            $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages.js');
        }else{
            $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages_fr.js');
        }
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js');
        // checkboxtree file for categories
        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'common/js/jquery/plugins/checkboxtree/css/checkboxtree.css');
        //$this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/checkboxtree/js/jquery-latest.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/checkboxtree/js/jquery.checkboxtree.js');
        // Ajax upload
//        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ajax-upload/ajaxupload.js');
        // adding admin JS file
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/admin/assets/js/admin.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'cp/assets/js/cp.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/cp/assets/js/category.js');

        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/facebox/facebox.css');

        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/facebox/facebox.js');

        // Flash Messanger
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->flashMessenger = $this->_flashMessenger;
    }

    public function indexAction() {
        // action body
        echo "CP BO User Controller";
    }

    public function viewAction() {

        //$searchKey = $this->_getParam('search', '');
        
        $this->view->title = "View Content";
        $product_model = new Model_Product();
        $select_products = $product_model->select();
        $select_products->setIntegrityCheck(false)
            ->from("products")
            ->joinInner('platforms', 'products.platform_id = platforms.id', 'name as platform')
            //->joinLeft("statistics_products", "statistics_products.product_id = products.id",array('date as hitsdate','hits'))
            // ->joinLeft("statistics_downloads", "statistics_downloads.product_id = products.id",array('date as downloaddate','count'))
            ->where("user_id = ?", Zend_Auth::getInstance()->getIdentity()->id)
            ->where("deleted != ?", 1)
            ->group('products.id')
            ->order('products.id DESC') ;
        $products = $product_model->fetchAll($select_products);

        $rowCount = count($products);

        if (0 == $rowCount) {
            $this->view->show_empty_msg = true;
        }

        $page = $this->getRequest()->getParam('page', 0);
        $paginator = Zend_Paginator::factory($products);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($page);
        
        $this->view->products = $paginator;
        
        $userMeta   = new Model_UserMeta();
        $userMeta->setEntityId($this->_getCpId());
        $this->view->nexpagerState  = $userMeta->ACTIVE_NEXPAGE;
        
        
        
        
    }

    public function createAction() {
        $this->view->title = "Create Content";
        // Load only the basic form
        // TODO : authentications etc
//        $productId = $this->_getParam('id');
        $form_basic = new Cpbo_Form_ProductBasicInfo();
//        $form_visuals = new Cpbo_Form_ProductVisuals();
//        $form_registration = new Cpbo_Form_ProductRegistration();
//        $form_files = new Cpbo_Form_ProductFiles();
        // category
//        $form_category = new Cpbo_Form_ProductCategory();
        // devices
//        $form_devices = new Cpbo_Form_ProductDevices();
        // get pre-loading varialbles
//        $categories = $this->getCategories();
//        $this->view->form = $form;
        // Basic Form
        $form_basic->setAction(CP_PROJECT_BASEPATH . 'product/basic');
        $this->view->form_basic = $form_basic;
        $categories = $this->getCategories();

        $this->view->form_basic->category_parent->setMultiOptions($categories[0]);
        //get platforms
        $platforms = $this->getPlatforms();

        $this->view->form_basic->create->setValue('create');

        // set platforms
//    $this->view->form_basic->platform_id->setMultiOptions($platforms);
        // Product Registration
//        $form_registration->setAction($this->getFrontController()->getBaseUrl() . '/product/registration');
//        $this->view->form_registration = $form_registration;
//        $this->view->form_registration->id->setValue($productId);
        // Prodcut Files
//        $form_files->setAction($this->getFrontController()->getBaseUrl() . '/product/files');
//        $form_files->setAttrib('enctype', 'multipart/form-data');
//        $this->view->form_files = $form_files;
//        $this->view->form_files->id->setValue($productId);
        // Product Categry
        // TODO : Here I used a partial to display checkboxes tree, use custom element instead
//        $form_category->setAction($this->getFrontController()->getBaseUrl() . '/product/category');
//        $this->view->form_category = $form_category;
//        $this->view->categories = $categories;
//        $this->view->form_category->id->setValue($productId);
        // Prodcut Visuals
//        $form_visuals->setAction($this->getFrontController()->getBaseUrl() . '/product/visuals');
//        $form_visuals->setAttrib('enctype', 'multipart/form-data');
//        $this->view->form_visuals = $form_visuals;
//        $this->view->form_visuals->id->setValue($productId);
        // Product Devices
//        $form_devices->setAction($this->getFrontController()->getBaseUrl() . '/product/device');
//        $this->view->form_devices = $form_devices;
//        $this->view->form_devices->id->setValue($productId);
//        $this->_flashMessenger->addMessage(array('error' => 'message'));
    }

    public function editAction() {

        //Model_ProductMeta::
        //echo 'Inside edit';die();
        // TODO : authentications etc
        $productId = $this->_getParam('id');
        $this->__checkOwner($productId);
        $create = $this->_getParam('create');
        $this->view->create = $create;

        // get the step
        $step = $this->_getParam('6');
        // get the review if set
        $review = $this->_getParam('review');
        // basic info
        $form_basic = new Cpbo_Form_ProductBasicInfo();
        $product = new Model_Product();
        $formBasicInfo = $this->populateBasicForm($productId, $product);

        $form_basic->populate($formBasicInfo);

        // visuals
        $form_visuals = new Cpbo_Form_ProductVisuals();
        // registaration
        $form_registration = new Cpbo_Form_ProductRegistration();
        // files
        $form_files = new Cpbo_Form_ProductFiles();
        // category
        $form_category = new Cpbo_Form_ProductCategory();
        // devices
        $form_devices = new Cpbo_Form_ProductDevices();

        // get pre-loading varialbles
        $categories = $this->getCategories();
        $platforms = $this->getPlatforms();

        $this->view->productId = $productId;
        //$this->view->form = $form;
        // Basic Form
        $form_basic->setAction(CP_PROJECT_BASEPATH. 'product/basic');
        $this->view->form_basic = $form_basic;
        // set platforms
        // $this->view->form_basic->platform_id->setMultiOptions($platforms);
        $this->view->form_basic->category_parent->setMultiOptions($categories[0]);
        $prodCatMdl     = new Model_ProductCategories();
        $parentId       = $prodCatMdl->selectedParentCategory($productId);
        $subcat         = $prodCatMdl->selectedSubCategory($productId);

        $this->view->form_basic->subcategory->setValue($subcat);


        if (!empty($parentId)) {
            $this->view->form_basic->category_parent->setValue($parentId);
        } else {
            //get the parent category by looking at the child category
            $categoryModel  = new Model_Category();
            $breadcrumb     = $categoryModel->getCategoryBreadcrumb($subcat);

            if (isset($breadcrumb[0]['id'])) {
                $this->view->form_basic->category_parent->setValue($breadcrumb[0]['id']);
            }
        }

        $this->view->form_basic->review->setValue($review);

        $userpayouts = new Model_User();
        $userpayoutsType = $userpayouts->getRoyalty();

        $this->view->form_basic->payout->setValue($userpayoutsType->payout_cp);
        // setDescription('<a href="#">Link</a>')

        /*

          $newDescription = $this->view->form_basic->price->getDescription()."  &nbsp Your royalty subscription is <span style='color: #e77918'>"
          .$userpayoutsType->name." and you will get $userpayoutsType->payout_cp% </span>";
          $this->view->form_basic->price->setDescription($newDescription);

         */

        $newDescription = $this->view->form_basic->price->getDescription() . "  <div id='info' style='visibility:hidden;'>"
            . $userpayoutsType->name . " and you will get $userpayoutsType->payout_cp% </span> </span><div>";
        $this->view->form_basic->price->setDescription($newDescription);




        // Product Registration
        $form_registration->setAction($this->getFrontController()->getBaseUrl() . '/product/registration');
        $this->view->form_registration = $form_registration;
        $this->view->form_registration->id->setValue($productId);
        $this->view->form_registration->review->setValue($review);
        $this->view->form_registration->create->setValue($create);

        $productKeys = new Model_ProductKey();
        $keys = array();
        $keys['keys'] = $productKeys->getKeysByProductId($productId);

        $values = $this->_flashMessenger->getMessages();
        if (count($values) >= 1 and $this->_request->status == 'error') {
                $keys['dynamic'] = '';
                $keys['keys'] = '';
        } else {
        	 
        		$keys['dynamic'] = $productKeys->getKeysByProductId($productId);
        		$keys['keys'] = $productKeys->getKeysByProductId($productId);
        	
        }

        $populatedData = array_merge($keys, $formBasicInfo);
        $form_registration->populate($populatedData);

        // Product Files
        // Get the number of files by platforms
        $platformIdFiles = array(1 => 1, 2 => 1, 3 => 2, 4 => 1, 5 => 1,
          6 => 12, 7 => 2, 8 => 1, 9 => 1, 10 => 1, 11 => 1, 12 => 1, 0 => 1);
        $form_files->setMaxCount($platformIdFiles[$formBasicInfo['platform_id']]);
        // get file types support by platform
        $productFiletypes = new Model_ProductFileTypes();
        $form_files->setFileType($productFiletypes->getFileTypeByPlatform($formBasicInfo['platform_id']));
        if ($formBasicInfo['content_type'] == 'URL')
            $formFiles = $form_files->createUrlForm();
        else
            $formFiles = $form_files->createFileForm();
        $this->view->form_files = $formFiles;
        $formFiles->setAction($this->getFrontController()->getBaseUrl() . '/product/files');
        $formFiles->setAttrib('enctype', 'multipart/form-data');
        $this->view->form_files->id->setValue($productId);
        $this->view->form_files->review->setValue($review);
//        $productFiles = new Model_ProductFiles();
//        $prodctFiles = $this->populateFormData($productId, $productFiles, 'filename');
//        $this->view->files = $prodctFiles;
//        if(is_array($prodctFiles))
//            $formFiles->populate(array('file' => array_pop($prodctFiles)));
        // Product Category
        // TODO : Here I used a partial to display checkboxes tree, use custom element instead
        $form_category->setAction($this->getFrontController()->getBaseUrl() . '/product/category');
        $this->view->form_category = $form_category;
        $this->view->categories = $categories;
        $productCategories = new Model_ProductCategories();
        $this->view->categorySelected = $this->populateFormData($productId, $productCategories, 'category_id');
        $this->view->form_category->id->setValue($productId);
        $this->view->form_category->review->setValue($review);

        // Prodcut Visuals
        $form_visuals->setAction($this->getFrontController()->getBaseUrl() . '/product/visuals');
        $form_visuals->setAttrib('enctype', 'multipart/form-data');
        $this->view->form_visuals = $form_visuals;
        $this->view->form_visuals->id->setValue($productId);
        $this->view->form_visuals->review->setValue($review);
        $productImages = new Model_ProductImages();
        $this->view->visualScreenshots = $this->populateFormData($productId, $productImages, 'filename');
        $this->view->thumbnail = $formBasicInfo['thumbnail'];
        $this->view->banner = (isset($formBasicInfo['banner']))? $formBasicInfo['banner'] : NULL;

        // Product Devices
        $form_devices->setAction($this->getFrontController()->getBaseUrl() . '/product/device');
        $this->view->form_devices = $form_devices;
        $this->view->form_devices->id->setValue($productId);
        $this->view->form_devices->review->setValue($review);
        $this->view->selected = $formBasicInfo['device_selection_type'];
        $productDevAttrib = new Model_productDeviceAttributes();
        $this->view->deviceAttributes = $productDevAttrib->getSelectedDeviceAttributesById($productId);



        //channel details
        $userModel = new Cpbo_Model_User();
        $channelDetails = $userModel->getChannelDetails($productId);

        //$pagination = Zend_Paginator::factory($channelDetails);
        //$pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
        //$pagination->setItemCountPerPage(10);

        $this->view->paginator = $channelDetails;

        // Tab selection
        $this->view->active = $step;
        
        /*get Current user*/
        $session = new Zend_Session_Namespace('chap');   
        $currentUser = isset($session->chap->id)?$session->chap->id:Null; 
        /*Hide Chanels for following chaps from 13-07-2016*/
        $config = Zend_Registry::get('config');
        $chapIds= explode(',',$config->nexva->noadvertitment->chaps);
        $this->view->show=TRUE;
        if(in_array($currentUser, $chapIds)){
            $this->view->show=FALSE;
        }
        /*end*/

    }

    function setChannelAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $productId  = $this->_getParam('id');
        $status  = $this->_getParam('status');
        $chapId = $this->_getParam('chap');

        $productChannelModel = new Cpbo_Model_ProductChannel();

        if($status == 'subscribe')
        {
            $productChannelModel->setChannel($productId,$chapId);
        }
        else
        {
            $productChannelModel->removeChannel($productId,$chapId);
        }

        $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/'.$productId.'/6/4');
    }

    function channelAction()
    {
        
        $this->_helper->viewRenderer->setNoRender(true);
        $productId  = $this->_getParam('id');
        
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');
        
        // if there is no any product id redirect to create page
        if (empty($productId)) {
            $this->_flashMessenger->addMessage(array('error' => $translate->translate("You have not create an Application Profile to Preview it please create it now.")));
            $this->_redirect (CPBO_PROJECT_BASEPATH.'product/create');
        }

        $formBasic = new Model_Platform();
        $row = $formBasic->find($productId);
        if (empty($row)) {// no Basic info found redirect to create page
            $this->_flashMessenger->addMessage(array('error' => $translate->translate("You have not create an Application Profile to Preview it please create it now.")));
            $this->_redirect (CPBO_PROJECT_BASEPATH.'product/create');
        }

        $formVisuals = new Model_ProductImages();
        $rowVisuals = $formVisuals->fetchAll(
            'product_id = ' . $productId
        );

        $countVisuals = $rowVisuals->count();
        if (empty($countVisuals)) {// if no screenshots
            $this->_flashMessenger->addMessage(array('error' => $translate->translate("Product visuals are not found!. Please upload atleast one screenshot and thumbnail.")));
            $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/2');
        }

        $userModel = new Cpbo_Model_User();
        $channelDetails = $userModel->getChannelDetails($productId);

        $productChannelModel = new Cpbo_Model_ProductChannel();

        foreach($channelDetails as $channelDetail)
        {
            $productChannelModel->setChannel($productId,$channelDetail['id']);
        }

        if ($this->_request->isPost()) {
            $prodBuild = new Model_ProductBuild();
            $builds = $prodBuild->getBuildsByProductId($productId);
            if (isset($review) && !empty($review))
                $this->_redirect (CPBO_PROJECT_BASEPATH.'product/display/id/' . $productId);
            elseif (count($builds) > 0)
                $this->_redirect (CPBO_PROJECT_BASEPATH.'build/show/id/' . $productId);
            else
                $this->_redirect (CPBO_PROJECT_BASEPATH.'build/create/productid/' . $productId);
        }

        $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/'.$productId.'/6/4');

    }

    function translateAction() {
        $proId  = $this->_getParam('id', null);
        if (!$proId) {
            $this->_redirect (CPBO_PROJECT_BASEPATH.'');
        }
        $this->__checkOwner($proId);
    
        $productModel   = new Model_Product();
        $langModel      = new Model_Language();
        $productLangMeta    = new Model_ProductLanguageMeta();
        
        $languages      = $langModel->fetchAll('status = 1 AND code <> "en"');
        $product        = $productModel->getProductDetailsById($proId, true);
        
        $translation    = new stdClass();
        $translation->PRODUCT_NAME          = '';
        $translation->PRODUCT_DESCRIPTION   = '';
        $translation->PRODUCT_SUMMARY       = '';
        $translation->language_id           = '';
        
        
        $langId     = (int) $this->_getParam('langId', null);    
        if ($langId) {
            $productLangMeta    = new Model_ProductLanguageMeta();
            $translation        = $productLangMeta->loadTranslation($proId, $langId);
        }
        
        $this->view->translations   = $productLangMeta->getAllTranslations($proId);
        $this->view->translation    = $translation;
        $this->view->languages      = $languages;
        $this->view->product        = $product;
        $this->view->proid          = $proId;
    }
    
    function addtranslationAction() {
        $proId  = (int) $this->_getParam('id', false);
        
        if (!$proId) {
            $this->_redirect (CPBO_PROJECT_BASEPATH.'');
        }
        $this->__checkOwner($proId);
        $langId     = (int) $this->_getParam('langId', null);
        
        $proLangModel   = new Model_ProductLanguageMeta();
        $data           = array(
            'name'      => $this->_getParam('name'),
            'summary'   => $this->_getParam('summary'),
            'desc'      => $this->_getParam('desc')
        );
        $proLangModel->saveTranslation($proId, $langId, $data);
        $this->_redirect (CPBO_PROJECT_BASEPATH.'product/translate/id/' . $proId . '/');
    }
    
    function removeTranslationAction() {
        $proId  = (int) $this->_getParam('id', false);
        
        if (!$proId) {
            $this->_redirect (CPBO_PROJECT_BASEPATH.'');
        }
        $this->__checkOwner($proId);
        $langId     = (int) $this->_getParam('langId', null);
        
        $proModel   = new Model_ProductLanguageMeta();
        $proModel->delete("product_id = {$proId} AND language_id = {$langId}");
        
        $this->_redirect (CPBO_PROJECT_BASEPATH.'product/translate/id/' . $proId . '/');
    }
    
    /**
     * Checks if the current user in sesssion is the owner of the product 
     */
    private function __checkOwner($proId, $return = false) {
        $uid    = Zend_Auth::getInstance()->getIdentity()->id;
        $proModel   = new Model_Product();
        $stmnt      = $proModel->select()->from('products', 'user_id')->where('id = ' . $proId, array(''));
        $owner      = $proModel->fetchRow($stmnt);
        
        if ($owner) {
            $owner = $owner->toArray(); 
            if ($owner['user_id'] == $uid) {
                return true;
            }
        }
        
        if ($return) {
            return false;
        } else {
            $this->_redirect (CPBO_PROJECT_BASEPATH.'');
        } 
    }
    
    /**
     * Basic info action
     * @return <type>
     */
    public function basicAction() {

        $changed = false;

        // TODO : add validation on when no product ID
        $this->_helper->viewRenderer->setNoRender(true);
        $requestPerms = $this->_request->getParams();
        $productId = $requestPerms['id'];

        $create = $requestPerms['create'];
        //$this->view->edit = $edit;
//        if(empty ($productId))
//            $this->_redirect (CPBO_PROJECT_BASEPATH.'product/create');
        $review = $requestPerms['review'];
        $form_basic = new Cpbo_Form_ProductBasicInfo();
        // TODO : there is a way to send data directly to form
        $platforms = $this->getPlatforms();
        // Zend_Debug::dump($requestPerms);
        // $form_basic->platform_id->setMultiOptions($platforms);

        if ($this->_request->isPost()) {
            $form_values = $this->_request->getParams();

            $product = new Model_Product();
            $form_values['user_id'] = Zend_Auth::getInstance()->getIdentity()->id;
            $inapp['inapp'] = NULL;
            $form_values['created_date']  = date('Y-m-d');
            $form_values['updated_date']  = date('Y-m-d');
            
            if($form_values['ycoin-inapp']){
                $form_values['product_type'] = 'FREEWARE';
                $form_values['inapp'] = 1;
                $form_values['category_parent'] = 1;
                $form_values['category_sub'] = 2;
            }

            $productId = $this->saveBasicInfo($form_values, $product);
            //echo "jyothi".$productId;
            /*if($form_values['ycoin-inapp']){
                $this->_redirect (CPBO_PROJECT_BASEPATH.'page/ycoins');
            }*/

            //if anything has changed with basic information
            if($productId == 1){
                $changed = true;
            }
            // if the form is updating then set it to the hidden id
            $productId = $form_values ['product_id'];

            if(!$form_values['ycoin-inapp']){
                $product->update($inapp, array('id = ?' => $productId));
            }

            // product create time
            if (!isset($productId) && empty($productId))
                $form_values ['created_date'] = new Zend_Db_Expr('NOW()');
            // product last update date
            $form_values ['product_changed'] = time ();

            //compare with existing data
            $productMetaModel = new Cpbo_Model_ProductMeta();
            $comparedData = $productMetaModel->compareData($productId,$form_values);
            if($comparedData){
                $changed = true;
            }
            $this->saveMeta($form_values);

            // save categories
            $prodCategoryMdl = new Model_ProductCategories();
            $categories = array($form_values ['category_parent'], $form_values ['category_sub']);
            $prodCategoryMdl->updateCategoriesByProdId($productId, $categories);

            if($changed){
                $this->sendContentadminMail('Basic Info',$productId);
            }

            if($form_values['ycoin-inapp']){
                $this->_redirect (CPBO_PROJECT_BASEPATH.'page/ycoins');
            }
            /*get translater*/
            $translate = Zend_Registry::get('Zend_Translate');
            $this->_flashMessenger->addMessage(array('info' => $translate->translate("Successfully saved.")));
            // redirect to next step
            if (empty($review)){
                $url = 'product/edit/id/' . $productId . '/6/2';
                if($create){
                    $url.= '/create/create';
                }
                $this->_redirect($url);
            }
            else
                $this->_redirect (CPBO_PROJECT_BASEPATH.'product/display/id/' . $productId);
        }
        else {
            $error = $this->formatErrorMsg($form_basic->getMessages());
            $this->_flashMessenger->addMessage(array('error' => $error));
            // loop  to same step
            if (empty($productId))
                $this->_redirect (CPBO_PROJECT_BASEPATH.'product/create');
            else
                $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/1');
        }
    }
		
	/*
     * Upload visuals and save them in table
     */
    
    public function visualsAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $formVisuals = new Cpbo_Form_ProductVisuals();
        $requestPerms = $this->_request->getParams();
        $review = $requestPerms ['review'];
        $message = '';
        $productId = $requestPerms['id'];
        $product = new Model_Product();

        $create = $requestPerms['create'];
        //echo $create;die();
        $changed = false;
        
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');

        if ($this->_request->isPost()) {
    
            if ($formVisuals->isValid($this->_request->getParams())) {
				
            	 $config = Zend_Registry::get('config');
   				 $visualPath = $_SERVER['DOCUMENT_ROOT'] . $config->product->visuals->dirpath;
       
            	//  process screenshot(s) upload   
                if (is_array($formVisuals->screenshots->getFileInfo())) {

   					// get the file trasfer adapter to reveive and rename array of screenshots 
                 	$fileAdapter = $formVisuals->screenshots->getTransferAdapter();
                    $i = 0;$j=0;
                    
                    $productImages = new Model_ProductImages();
                    $savedVisuals = $this->populateFormData($productId, $productImages, 'filename');
                    if (is_array($savedVisuals))
                        $saveVisualIds = array_keys($savedVisuals);

                    foreach ($formVisuals->screenshots->getFileInfo() as $screenshotInfo) {
                        
                    	//process the image fields. if image field is empty check the next one and process it  
                    	$name = $screenshotInfo['name'];

                        if (empty($name))
                            continue;

                        //if screenshots are changed, $changed set to true, hence a mail will be sent to the contentadmins
                        $idd = $saveVisualIds[$j];
                        if((($productId.($j+1).'_'.$name) != $savedVisuals[$idd]) && (!empty($savedVisuals[$idd]))){
                            $changed = true;
                        }
                        $j++;

                        // process the image replacements it will replace the existing images upon new uploads   
                    	$id = null;
                        if (array_key_exists($i, $saveVisualIds)) {
                            $id = $saveVisualIds[$i];
                            unset($savedVisuals[$id]);
                         	$i++;
                        }

                      	//$fileExtension = $this->_findFileExtension($screenshotInfo['name']);
                       	$fileName = $productId . $i . '_'  . str_replace(' ', '', $screenshotInfo['name']);
                      	$fileAdapter->addFilter('Rename', array('target' => $visualPath .  DIRECTORY_SEPARATOR   . $fileName, 'overwrite' => true));
 
                       //use this when uploading single image. Left this for future debugging purpuses 
                       //$formVisuals->screenshots->receive($screenshotInfo['name']); 

                       // use this when uploading array of images. Left this for future debugging purpuses 
                       //if (!$fileAdapter->receive($screenshotInfo['name'])) 
                       //    die(Zend_Debug::dump($fileAdapter->getMessages()));
                            
                      	
                      	// use this when receiving array of images
                      	$fileAdapter->receive($screenshotInfo['name']);
                            
					   	$productImgVal = array('id' => $id, 'product_id' => $productId, 'filename' =>   $fileName);
                       	$this->save($productImgVal, $productImages);
                        if(isset($productId) && !empty($productId)){
                            $product = new Model_Product();
                            $product->setUpdateDate($productId);
                        }
                        

                    }
                                //die();
                    $message .= $translate->translate("Screenshots successfully saved").'<br/>';
                
                 }
                 
				//	Zend_Debug::dump($fileAdapter->receive($screenshotInfo['name']));  Left this for future debugging purpuses 

                 /* uncomment this if you want to replace/delete all the images upon new screenshot(s) upload 
                    delete schreenshots
                    if ($id != 0) {
                        foreach ($savedVisuals as $key => $value) {
                            $productImages->delete('id = ' . $key);
                        }
                    }
                  */ 

        	
                // process thumbnail upload     
                if ($formVisuals->thumbnail->isUploaded()) {

                	//$thumbnail = $formVisuals->thumbnail->getFileInfo(); we don't need this as we use getTransferAdapter()
                	
                    $fileAdapterThumbnail = $formVisuals->thumbnail->getTransferAdapter();
                    
                    $fileName = md5(uniqid()) . '.png';
                    $fileAdapterThumbnail->addFilter('Rename', array('target' => $visualPath .  DIRECTORY_SEPARATOR   . $fileName, 'overwrite' => true));

                    //if thumbnail is changed, $changed set to true, hence a mail will be sent to the contentadmins
                    $productModel = new Cpbo_Model_Product();
                    $productDetails = $productModel->getProductDetails($productId);

                    if($fileName != $productDetails->thumbnail){
                        $changed = true;
                    }

                    if($create){
                        $changed = false;
                    }

                    // use this receiving single image 
                    $formVisuals->thumbnail->receive();
                    
                    // save thumbnail in the db 
                    $product = new Model_Product();
                    $thumbanil = array('id' => $productId, 'thumbnail' => $fileName  );
                    $this->save($thumbanil, $product);
                    $message .= $translate->translate("Thumbnail successfully saved");
                    $thumbnailUploaded =  false;

                }
                
                // process banner upload     
                if ($formVisuals->banner->isUploaded()) {
                    
                    $fileAdapterThumbnail = $formVisuals->banner->getTransferAdapter();
                    $bannerArray=$formVisuals->banner->getFileInfo();
                    $fileName = $productId.'_banner_'.$bannerArray['banner']['name'];
                    $fileAdapterThumbnail->addFilter('Rename', array('target' => $visualPath .  DIRECTORY_SEPARATOR   . $fileName, 'overwrite' => true));

                    //if thumbnail is changed, $changed set to true, hence a mail will be sent to the contentadmins
                    $productModel = new Cpbo_Model_Product();
                    $productDetails = $productModel->getProductDetails($productId);

                    if($fileName != $productDetails->banner){
                        $changed = true;
                    }
                    
                    // use this receiving single image 
                    $formVisuals->banner->receive();
                    
                    // save thumbnail in the db 
                    $product = new Model_Product();
                    $banner = array('id' => $productId, 'banner' => $fileName  );
                    $this->save($banner, $product);
                    $message .= $translate->translate("Banner successfully saved"); 
                    $this->view->banner=$fileName;                    
                    if(isset($productDetails->banner)){
                        $this->view->banner =$productDetails->banner ;
                    }
                }
                  
                $cacheUtil  = new Model_CacheUtil();
            	$cacheUtil->clearProduct($productId);
				$productDetails = $product->getProductDetailsById($productId, 'true');
				
				if($productDetails['thumb_name'])    {
				    $thumbnailUploaded = false;
	
				}

                //if $changed is set to true mail function will be called
                if($changed){
                    $this->sendContentadminMail('Visual Info',$productId);
                }

				// Redirect to next step
				if (empty ( $review ))  {
					
					$formVisuals = new Model_ProductImages ( );
					$rowVisuals = $formVisuals->fetchAll ( 'product_id = ' . $productId );
					
					
				    $countVisuals = $rowVisuals->count();
					if (empty ($countVisuals) or $thumbnailUploaded) { // if no screenshots
						$this->_flashMessenger->addMessage ( array ('error' => $translate->translate("Product visuals are not found!. Please upload atleast one thumbnail  and a screenshot.") ) );
                        $url = 'product/edit/id/' . $productId . '/6/2';
                        if($create){
                            $url.= '/create/create';
                        }
						$this->_redirect ($url);
					} else    {
						
						$this->_flashMessenger->addMessage ( array ('info' => $message ) );
                        $url = 'product/edit/id/' . $productId . '/6/3';
                        if($create){
                            $url.= '/create/create';
                        }
                        $this->_redirect ($url);
					}
					 
					
				}    else	{
					$this->_flashMessenger->addMessage ( array ('info' => $message ) );
					$this->_redirect ( 'product/display/id/' . $productId );
					
				}
				
			} else {
				//                $this->view->errors = $formVisuals->getMessages();
				$error = $this->formatErrorMsg ( $formVisuals->getMessages () );
				$this->_flashMessenger->addMessage ( array ('error' => $error ) );
                $url = 'product/edit/id/' . $productId . '/6/2';
                if($create){
                    $url.= '/create/create';
                }
                $this->_redirect ($url);
			}
		}
	}
	
    
	
	
	/*
     * Upload visuals and save them in table
     */
	
	public function filesAction() {
		$this->_helper->viewRenderer->setNoRender ( true );
		$requestPerms = $this->_request->getParams ();
		$productId = $requestPerms ['id'];
		$review = $requestPerms ['review'];
		$formFilesObj = new Cpbo_Form_ProductFiles ( );
		$formFiles = $formFilesObj->create ();
                
                /*get translater*/
                $translate = Zend_Registry::get('Zend_Translate');

                
		if ($this->_request->isPost ()) {
			if ($formFiles->isValid ( $this->_request->getParams () )) {
				$formValues = $formFiles->getValues ();
				//                $productId = $formValues['id'];
				$productFiles = new Model_ProductFiles ( );
                // if the file is uploaded
//                $savedFiles = $this->populateFormData($productId, $productFiles, 'filename');
//                if(is_array($savedFiles))
//                    $savedFileIds = array_keys($savedFiles);
                // TODO : add file validation with the applicaitno mime types
                if (is_array($formFiles->file->getFileInfo())) {
                    $savedFiles = $this->populateFormData($productId, $productFiles, 'filename');
                    if (is_array($savedFiles))
                        $saveFileIds = array_keys($savedFiles);
                    $i = 0;
                    foreach ($formFiles->file->getFileInfo() as $fileInfo) {
                        $id = null;
                        if (array_key_exists($i, $saveFileIds))
                            $id = $saveFileIds[$i];
                        $i++;
                        $name = $fileInfo['name'];
                        if (empty($name))
                            continue;
                        // construct the form values array
                        $productFileVal = array('id' => $id, 'product_id' => $productId, 'filename' => $name);
                        $this->save($productFileVal, $productFiles);
                        // push to S3
                        $fileInfo['productId'] = $productId;
                        $this->pushToS3($fileInfo);
                    }
                    $this->_flashMessenger->addMessage(array('info' => $translate->translate("Successfully Uploaded to Local Server!.")));
                }
                // redirect to next step
                if (empty($review))
                    $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/5');
                else
                    $this->_redirect (CPBO_PROJECT_BASEPATH.'product/display/id/' . $productId);
            }
            else {
                // create the URL FORM and use it
                $formFiles->createUrl();
                if ($formFiles->isValid($this->_request->getParams())) {
                    $formValues = $formFiles->getValues();
                    // load DB tables
                    $productFiles = new Model_ProductFiles();
//                    $productFiles->delete('product_id = ' . $productId);
                    $savedFiles = $this->populateFormData($productId, $productFiles, 'filename');
                    if (is_array($savedFiles))
                        $saveFileIds = array_keys($savedFiles);
                    $id = $saveFileIds[0];
                    // construct the form values array
                    $productFileVal = array('id' => $id, 'product_id' => $productId, 'filename' => $formValues['file'], 'file_type' => 'URL');
                    $this->save($productFileVal, $productFiles);

                    $this->_flashMessenger->addMessage(array('info' => $translate->translate("Successfully Add the  URL filed!.")));
                    // redirect to next step
                    if (empty($review))
                        $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/5');
                    else
                        $this->_redirect (CPBO_PROJECT_BASEPATH.'product/display/id/' . $productId);
                }
                else {
                    $error = $this->formatErrorMsg($formFiles->getMessages());
                    $this->_flashMessenger->addMessage(array('error' => $error));
                    // redirect to next step
                    $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/4');
                }
            }
        }
    }

    /**
     * Adding categories to the product
     */
    public function categoryAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $requestPerms = $this->_request->getParams();
        $productId = $requestPerms['id'];
        $review = $requestPerms['review'];
        $formCategory = new Cpbo_Form_ProductCategory();
       
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');
        
        if ($this->_request->isPost()) {
            if ($formCategory->isValid($this->_request->getParams())) {
                $productCategories = new Model_ProductCategories();
                $formValues = $formCategory->getValues();
                $productId = $formValues['id'];
                $categories = $this->_request->getParam('categories');
                // get the deivices already in database
                $savedCategories = $this->populateFormData($productId, $productCategories, 'category_id');
                // no categories are selected
                if (!is_array($categories)) {
                    $error = 'You should select at least one category.';
                    $this->_flashMessenger->addMessage(array('error' => $error));
                    // redirect to same page
                    $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/3');
                }

                // if more than two categories seleted, one parent and one child
                if (count($categories) > 2) {
                    $error = 'You should select only one parent/sub category category.';
                    $this->_flashMessenger->addMessage(array('error' => $error));
                    // redirect to same page
                    $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/3');
                }

                foreach ($categories as $key => $value) {
                    if (is_array($savedCategories))
                        if (array_key_exists($value, $savedCategories)) { //  if already in databse skip
                            unset($savedCategories[$value]);
                            continue;
                        }
                    $formValues = array('product_id' => $productId, 'category_id' => $value);
                    $this->save($formValues, $productCategories);
                }
                // start deleting unselected categories
                foreach ($savedCategories as $key => $value) {
                    $productCategories->delete('id = ' . $value);
                }

                $this->_flashMessenger->addMessage(array('info' => $translate->translate("Successfully Added to Categories!.")));
                // redirect to next step
                if (empty($review))
                    $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/4');
                else
                    $this->_redirect (CPBO_PROJECT_BASEPATH.'product/display/id/' . $productId);
            }
            else {
                $error = $this->formatErrorMsg($formCategory->getMessages());
                $this->_flashMessenger->addMessage(array('error' => $error));
                // redirect to next step
                $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/3');
            }
        }
    }

    /**
     * device selection action
     */
    public function deviceAction() {
        //echo 'Yoooooooooooooooooooooooooooooo';die();
        $this->_helper->viewRenderer->setNoRender(true);
        $requestPerms = $this->_request->getParams();
        $productId = $requestPerms['id'];
        $review = $requestPerms['review'];
        $formDevices = new Cpbo_Form_ProductDevices();
        
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');

        if ($this->_request->isPost()) {
            if ($formDevices->isValid($this->_request->getParams())) {
                $formValues = $formDevices->getValues();
                $productId = $formValues['id'];
                $devices = null;
                $devices = $this->_request->getParam('devices');
                // Change the mode of devices selection on product table
                $selected = $this->_request->getParam('radio');
                // get attribute values
                // attributes array
                $attributesArray = array();
                $attributesArray[4] = !empty($this->_request->mp3_playback) ? 1 : '';
                $attributesArray[5] = !empty($this->_request->java_midp_1) ? 1 : '';
                $attributesArray[6] = !empty($this->_request->java_midp_2) ? 1 : '';
                $attributesArray[7] = !empty($this->_request->width) ? $this->_request->width : '';
                $attributesArray[8] = !empty($this->_request->height) ? $this->_request->height : '';
                $attributesArray[2] = !empty($this->_request->navigation_method) ? $this->_request->navigation_method : '';

                // Save Attributes to the databse
                $productDevAttrib = new Model_productDeviceAttributes();
                // delete all existing attributes
                $productDevAttrib->delete('product_id = ' . $productId);
                $attribString = '';
                foreach ($attributesArray as $id => $value) {
                    if (empty($value) || $value == 'any')
                        continue;
                    $devAttrib = array('product_id' => $productId, 'device_attribute_definition_id' => $id, 'value' => $value);
                    $this->save($devAttrib, $productDevAttrib);
                    // contruct device attrib string
                    $attributeDefinitions = array(
                      4 => 'mp3_playback',
                      5 => 'java_midp_1',
                      6 => 'java_midp_2',
                      7 => 'width',
                      8 => 'height',
                      2 => 'navigation_method'
                    );
                    if ($id == 2 || $id == 7 || $id == 8)
                        $attribString .= "$attributeDefinitions[$id]=$value&";
                    else
                        $attribString .= $attributeDefinitions[$id] . '&';
                }

//                die($attribString);
                // save on product Table
                $formValues = array('id' => $productId, 'device_selection_type' => $selected);
                $product = new Model_Product();
                $this->save($formValues, $product);
                // insert to product devices table
                $productDevices = new Model_ProductDevices();
                // get the deivices already in database
                $savedDevices = $this->populateFormData($productId, $productDevices, 'device_id');
                // get devices if those are not there
                if (empty($devices)) {
//                    $devicesAuto = array();
                    $devicesTable = new Model_Device();
                    switch ($selected) {
                        case 'ALL_DEVICES':
                            $devices = $devicesTable->getAllDevies();
                            break;
                        case 'BY_ATTRIBUTE':
                            $attrib = substr($attribString, 0, -1);
                            $devices = $devicesTable->attribFilterDevices($attrib);
                            break;
                    }
                }

                foreach ($devices as $key => $value) {
                    // @TODO : fix this unstable code
                    if (is_array($value)) {
                        $value = $value['id'];
                    }
                    // check value already in the database
                    if (is_array($savedDevices)) {
                        if (array_key_exists($value, $savedDevices)) { //  if already in databse skip
                            unset($savedDevices[$value]);
                            continue;
                        }
                    }
//                        $id = $savedDevices[$value];
                    $dataValues = array('product_id' => $productId, 'device_id' => $value);
                    $this->save($dataValues, $productDevices);
                }
                // start deleting unselected devices
                foreach ($savedDevices as $key => $value) {
                    $productDevices->delete('id = ' . $value);
                }
                $this->_flashMessenger->addMessage(array('info' =>  $translate->translate("Successfully Saved Selected Devices").'!.'));
                // redirect to next step
                if (empty($review))
                    $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/6');
                else
                    $this->_redirect (CPBO_PROJECT_BASEPATH.'product/display/id/' . $productId);
            }
        }
        else {
            $error = $this->formatErrorMsg($formDevices->getMessages());
            $this->_flashMessenger->addMessage(array('error' => $error));
            // redirect to next step
            $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/5');
        }
    }

    /**
     * Registration of the Product, validate all the details etc etc
     */
    public function registrationAction() {
        
        
        

        $this->_helper->viewRenderer->setNoRender(true);
        $requestPerms = $this->_request->getParams();
        
        $productId = $requestPerms['id'];
        $review = $requestPerms['review'];
        $create = $requestPerms['create'];//echo $create;die();
        $changed = false;

        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');

        // if there is no any product id redirect to create page
        if (empty($productId)) {
            $this->_flashMessenger->addMessage(array('error' => $translate->translate("You have not create an Application Profile to Preview it please create it now.")));
            $this->_redirect (CPBO_PROJECT_BASEPATH.'product/create');
        }

        $formBasic = new Model_Platform();
        $row = $formBasic->find($productId);
        if (empty($row)) {// no Basic info found redirect to create page
            $this->_flashMessenger->addMessage(array('error' => $translate->translate("You have not create an Application Profile to Preview it please create it now.")));
            $this->_redirect (CPBO_PROJECT_BASEPATH.'product/create');
        }

        $formVisuals = new Model_ProductImages();
        $rowVisuals = $formVisuals->fetchAll(
                'product_id = ' . $productId
        );
        $countVisuals = $rowVisuals->count();
        if (empty($countVisuals)) {// if no screenshots
            $this->_flashMessenger->addMessage(array('error' => $translate->translate("Product visuals are not found!. Please upload atleast one screenshot and thumbnail.")));
            $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/2');
        }
        
        


//        $formFiles = new Model_ProductFiles();
//        $rowFiles = $formFiles->fetchAll(
//                'product_id = ' . $productId
//        );
//        $countFiles = $rowFiles->count();
//        if(empty ($countFiles)) {// if no files
//            $this->_flashMessenger->addMessage(array('error' => 'Application file is not found!, Please upload Applicatin Files!.'));
//            $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId  . '/6/4');
//        }
//        $formDevices = new Model_ProductDevices();
//        $rowDevices = $formDevices->fetchAll(
//                'product_id = ' . $productId
//        );
//        $countDevices = $rowDevices->count();
//        if(empty ($countDevices)) {// if no devices are selected
//            $this->_flashMessenger->addMessage(array('error' => 'You must have at lease one compatible device selected in your application.'));
//            $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId  . '/6/5');
//        }

//        $formCategories = new Model_ProductCategories();
//        $rowCategoris = $formCategories->fetchAll(
//                'product_id = ' . $productId
//        );
//        $countCategoris = $rowCategoris->count();
//        if (empty($countCategoris)) {// if no devices are selected
//            $this->_flashMessenger->addMessage(array('error' => 'Please select at least one category to continue.'));
//            $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/3');
//        }

        $form_registration = new Cpbo_Form_ProductRegistration();
        // TODO Validate Product Details Exists
        // Validate product
        if ($this->_request->isPost()) {
            

            
        	
            if ($form_registration->isValid($this->_request->getParams())) {
   
                $form_values = $form_registration->getValues();
                $productId = $form_values['id'];
                $registrationModel = $form_values['registration_model'];
                $product = new Model_Product();

                $productModel = new Cpbo_Model_Product();
                $productDetails = $productModel->getProductDetails($productId);

                if(($productDetails->product_type != $form_values['product_type']) || ($productDetails->registration_model != $form_values['registration_model'])){
                    $changed = true;
                }

                if('IN-APP' == $form_values['product_type']){
                    //$form_values['product_type'] = 'FREEWARE';

                    /*$data = array(
                        'product_type' => 'FREEWARE',
                        'inapp'         =>  1
                    );*/
                }

                //$productModel->update($data,'id ='.$productId);
                $this->save($form_values, $product);

                // save keys to product keys
                $productKeys = new Model_ProductKey();
                // get keys

                $existKeys = array();
                
                


				if($this->_request->product_type == 'COMMERCIAL')	{
					
	                
					if($this->_request->registration_model != 'NO')	{	
						
		                if (isset($form_values['keys']) && ($registrationModel == 'POOL')) {
		                	
		                    if (!empty($form_values['keys']))	{

		                        $keys = $form_values['keys'];
		                        $keysArr = explode(',', $keys);

                                //check whether product keys are changed, if then set $changed to true hence a mail will send to contentadmins
                                $results = $productKeys->getProductKeys($productId);
                                foreach($results as $result){
                                    $existKeys[] = $result->key;
                                }
                                $val = array_diff($existKeys,$keysArr);
                                if($val){
                                    $changed = true;
                                }//

                                $productKeys->delete('product_id = ' . $productId);

		                        foreach ($keysArr as $key) {
		                            $productKeys->saveData($productId, $key);
		                        }

		                    }	else	{
		                    	
		                    	$this->_flashMessenger->addMessage(array('error' => $translate->translate("Please enter values for registration keys.")));
		                		// redirect to next step
                                $url = 'product/edit/id/' . $productId . '/6/3/status/error';
                                if($create){
                                    $url.= '/create/create';
                                }
		                		$this->_redirect($url);
		                    	
		                    }
		                    
		                    
		                }
	                
		              if (isset($form_values['dynamic']) && ($registrationModel == 'DYNAMIC' || $registrationModel == 'STATIC')) {
								
		              			
		              	
								$urlValid = new Nexva_Util_Validation_UrlValidator();
			                    if ($registrationModel == 'DYNAMIC') {
			                    	
			                    	if($urlValid->isValid($form_values['dynamic']))    {

				                        $keys = $form_values['dynamic'];
				                        $keysArr = explode(',', $keys);

                                        //check whether product keys are changed, if then set $changed to true hence a mail will send to contentadmins
                                        $results = $productKeys->getProductKeys($productId);
                                        foreach($results as $result){
                                            $existKeys[] = $result->key;
                                        }
                                        $val = array_diff($existKeys,$keysArr);
                                        if($val){
                                            $changed = true;
                                        }
                                        //

                                        $productKeys->delete('product_id = ' . $productId);

				                        foreach ($keysArr as $key) {
				                            $productKeys->saveData($productId, $key);
				                        }
			                        
			                    	}  	else    {
			                    	
			                    	$this->_flashMessenger->addMessage(array('error' => $translate->translate("Entered URL") .''.$form_values['dynamic'].', '.$translate->translate("for the registration script is Invalid.")));

			                    	
			                    	// redirect to next step
                                    $url = 'product/edit/id/' . $productId . '/6/3/status/error';
                                    if($create){
                                        $url.= '/create/create';
                                    }
                                    $this->_redirect($url);
			                		//$this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/3/status/error');
			                    	
			                    	}
			                    	
			                	}
	                
	                
	                    
			                 if ($registrationModel == 'STATIC') {
			                 	
			                       
			                    	
			                    	if(!empty($form_values['dynamic']))    {
			                    	

				                        $keys = $form_values['dynamic'];
				                        $keysArr = explode(',', $keys);

                                        //check whether product keys are changed, if then set $changed to true hence a mail will send to contentadmins
                                        $results = $productKeys->getProductKeys($productId);
                                        foreach($results as $result){
                                            $existKeys[] = $result->key;
                                        }
                                        $val = array_diff($existKeys,$keysArr);
                                        if($val){
                                            $changed = true;
                                        }
                                        //

                                        $productKeys->delete('product_id = ' . $productId);

				                        foreach ($keysArr as $key) {
				                            $productKeys->saveData($productId, $key);
				                        }
			                        
			                    	}  	else 	{
			                    	
			                    	$this->_flashMessenger->addMessage(array('error' => $translate->translate("Please enter value for registration key.")));
				                		// redirect to next step
                                        $url = 'product/edit/id/' . $productId . '/6/3/status/error';
                                    if($create){
                                        $url.= '/create/create';
                                    }
                                    $this->_redirect($url);
			                		//$this->_redirect();
			                    	
			                    	}
			                    	
			                }
	                
	                
	              		}
	                
					} else {
						
						//$this->_flashMessenger->addMessage(array('error' => 'Please select the value for registration model.'));
			            //redirect to next step
			            //$this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/3/status/error');
                        if($create){
                            $changed = false;
                        }
                        if($changed){
                            $this->sendContentadminMail('Registration info',$productId);
                        }

					    $this->_flashMessenger->addMessage(array('info' => $translate->translate("Successfully Saved!.")));
		                // redirect to next step
                                            
                                            
        /*get Current user*/

        $session = new Zend_Session_Namespace('chap');   
        $currentUser = isset($session->chap->id)?$session->chap->id:Null; 
        /*Hide Chanels for following chaps from 13-07-2016*/
        $config = Zend_Registry::get('config');
        $chapIds=  split(',',$config->nexva->noadvertitment->chaps);
        if(in_array($currentUser, $chapIds)){
         $prodBuild = new Model_ProductBuild();
            $builds = $prodBuild->getBuildsByProductId($productId);
            if (isset($review) && !empty($review))
                $this->_redirect (CPBO_PROJECT_BASEPATH.'product/display/id/' . $productId);
            elseif (count($builds) > 0)
                $this->_redirect (CPBO_PROJECT_BASEPATH.'build/show/id/' . $productId);
            else
                $this->_redirect (CPBO_PROJECT_BASEPATH.'build/create/productid/' . $productId);
        } else {
            
             $this->_redirect (CPBO_PROJECT_BASEPATH.'product/channel/id/'.$productId);
        }
        
                                            

                                            
                       
                        
		                // if already have builds then redirect to buid mange page
		                /*$prodBuild = new Model_ProductBuild();
		                $builds = $prodBuild->getBuildsByProductId($productId);
		                if (isset($review) && !empty($review))
		                    $this->_redirect (CPBO_PROJECT_BASEPATH.'product/display/id/' . $productId);
		                elseif (count($builds) > 0)
		                    $this->_redirect (CPBO_PROJECT_BASEPATH.'build/show/id/' . $productId);
		                else
		                    $this->_redirect (CPBO_PROJECT_BASEPATH.'build/create/productid/' . $productId);*/
			            
						
					}
                
               }
               
               
               
                if($create){
                    $changed = false;
                }
                
      
                
                if($changed){
                   $this->sendContentadminMail('Registration info',$productId);
                }
                $this->_flashMessenger->addMessage(array('info' => $translate->translate("Successfully Saved!.")));
                // redirect to next step
                
                
                
        $session = new Zend_Session_Namespace('chap');   
        $currentUser = isset($session->chap->id)?$session->chap->id:Null; 
        /*Hide Chanels for following chaps from 13-07-2016*/  
        $config = Zend_Registry::get('config');
        $chapIds= explode(',',$config->nexva->noadvertitment->chaps);
        if(in_array($currentUser, $chapIds)){
         $prodBuild = new Model_ProductBuild();
            $builds = $prodBuild->getBuildsByProductId($productId);
            if (isset($review) && !empty($review))
                $this->_redirect (CPBO_PROJECT_BASEPATH.'product/display/id/' . $productId);
            elseif (count($builds) > 0)
                $this->_redirect (CPBO_PROJECT_BASEPATH.'build/show/id/' . $productId);
            else
                $this->_redirect (CPBO_PROJECT_BASEPATH.'build/create/productid/' . $productId);
        } else {
               
                $this->_redirect (CPBO_PROJECT_BASEPATH.'product/channel/id/'.$productId);
        }

                // if already have builds then redirect to buid mange page
                /*$prodBuild = new Model_ProductBuild();
                $builds = $prodBuild->getBuildsByProductId($productId);
                if (isset($review) && !empty($review))
                    $this->_redirect (CPBO_PROJECT_BASEPATH.'product/display/id/' . $productId);
                elseif (count($builds) > 0)
                    //$this->_redirect (CPBO_PROJECT_BASEPATH.'build/show/id/' . $productId);
                    $this->_redirect (CPBO_PROJECT_BASEPATH.'product/channel/id/'.$productId);
                else
                    $this->_redirect (CPBO_PROJECT_BASEPATH.'build/create/productid/' . $productId);*/
            }
            else {
                $error = $this->formatErrorMsg($form_registration->getMessages());
                $this->_flashMessenger->addMessage(array('error' => $error));
                // redirect to next step
                $url = 'product/edit/id/' . $productId . '/6/3';
                if($create){
                    $url.= '/create/create';
                }
                $this->_redirect($url);
                //$this->_redirect();
            }
        }
    }

    /**
     * Display Overview
     *
     */
public function displayAction() {
		
		$inapp = 0;
		$requestPerms = $this->_request->getParams ();
		$productId = $requestPerms ['id'];
		$this->__checkOwner($productId);
		
		$inapp = $this->_request->inapp;
		$this->view->productid = $productId;
		// if the form is submitted
		if ($this->_request->isPost ()) {
            $this->_redirect ( 'product/view' );
        /**
         * This code used to set the product to NOT_APPROVED so admins had to approve their content
         * but since that would also take apps out of our catalog, we don't do that anymore
         * 		    
		    $productId = (empty ( $productId )) ? $requestPerms ['product_id'] : $productId;
			$product = new Model_Product ( );
			$formValues = array ('id' => $productId, 'status' => 'NOT_APPROVED' );
			$update = $this->save ( $formValues, $product );
			// add message and redirest to the next step
			$this->_redirect ( 'product/view' );
			*/
		}
		// get Basic Info
		$product = new Model_Product ( );
		$basicInfo = $this->populateBasicForm ( $productId, $product );
		// get user info
		$userMeta = new Model_UserMeta ( );
		$userMeta->setEntityId ( $basicInfo ['user_id'] );
		
		$basicInfo ['company_name'] = $userMeta->COMPANY_NAME;
		// un set unwanted data

		if ($inapp) {
			
			unset ( $basicInfo ['content_type'] );
			unset ( $basicInfo ['device_selection_type'] );
			unset ( $basicInfo ['is_featured'] );
			unset ( $basicInfo ['created_date'] );
			unset ( $basicInfo ['deleted_date'] );
			unset ( $basicInfo ['inapp'] );
			unset ( $basicInfo ['deleted_date'] );
			unset ( $basicInfo ['deleted_date'] );
			unset ( $basicInfo ['company_name'] );
			unset ( $basicInfo ['product_version'] );
			unset ( $basicInfo ['full_description'] );
			unset ( $basicInfo ['desktop_product'] );
			unset ( $basicInfo ['is_suggested'] );
			unset ( $basicInfo ['keywords'] );

			$basicInfo ['Application_Id'] = $this->view->ApplicationId($this->_request->id);

			$this->view->inapp = 1;
		
		}
		
		unset ( $basicInfo ['category_id'] );
		unset ( $basicInfo ['platform_id'] );
		unset ( $basicInfo ['user_id'] );
		unset ( $basicInfo ['deleted'] );
		unset ( $basicInfo ['id'] );
		//get platform name
		if (! empty ( $basicInfo ['platform_id'] )) {
			$platformId = $basicInfo ['platform_id'];
			$platformMdl = new Model_Platform ( );
			$platform = $platformMdl->getPlatformName ( $platformId );
			unset ( $basicInfo ['platform_id'] );
			$basicInfo ['platform'] = $platform;
		}
		
		$this->view->basicinfo = $basicInfo;
		
		// Categories
		$productCategories = new Model_ProductCategories ( );
		$categories = $this->getCategories ();
		$this->view->categories = $categories;
		$this->view->categorySelected = $this->populateFormData ( $productId, $productCategories, 'category_id' );
		
		// Visuals
		$productVisuals = new Model_ProductImages ( );
		$this->view->visuals = $this->populateFormData ( $productId, $productVisuals, 'filename' );
		
		// Builds
		$builds = array ();
		$product = new Model_Product ( );
		$builds = $product->getBuilds ( $productId );
		$this->view->builds = $builds;
		$prodBuild = new Model_ProductBuild ( );
		$buildFiles = array ();
		$buildDevices = array ();
		foreach ( $builds as $value ) {
			$buildFiles [$value->id] = $prodBuild->getFiles ( $value->id );
			$buildDevices [$value->id] = $prodBuild->getSelectedDevices ( $value->id );
		}
		
		$this->view->files = $buildFiles;
		$this->view->devices = $buildDevices;
	
	}

        /**
     * Put files to S3
     * @param <type> $file
     */
    private function pushToS3($file) {
        // local file nale
        $fileName = $file['name'];
        $filePath = $file['destination'];
        $productId = $file['productId'];
        // get the correct mime from the database
        $productFileTypes = new Model_ProductFileTypes();
        $fileMime = $productFileTypes->getMimeByFile($fileName);
        // get init configurations
        $config = Zend_Registry::get('config');
        $awsKey = $config->aws->s3->publickey;
        $awsSecretKey = $config->aws->s3->secretkey;
        $bucketName = $config->aws->s3->bucketname;

        $bucketExists = false;
        $s3 = new Zend_Service_Amazon_S3($awsKey, $awsSecretKey);
        
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');

        
        try {
            // get the list of buckets
            $buckets = $s3->getBuckets();
            if (in_array($bucketName, $buckets)) {
                $bucketExists = true;
            }
            // if bucket not exists then create
            if (!$bucketExists) {
                $createBucket = $s3->createBucket($bucketName);
                if ($createBucket) {
                    $bucketExists = true;
                } else {
                    $bucketExists = false;
                }
            }
            // try this if only bucket exists
            if ($bucketExists) {
                // Set permissions
                $perms = array(
                  Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PRIVATE,
                  Zend_Service_Amazon_S3::S3_CONTENT_TYPE_HEADER => $fileMime
                );
                $putObject = $s3->putObject(
                        $bucketName . '/productfile/' . $productId . '/' . $fileName,
                        $filePath . '/' . $fileName,
                        $perms
                );
                if ($putObject)
                    $this->_flashMessenger->addMessage(array('info' => $translate->translate("File").' <i>' . $fileName . '</i> '.$translate->translate("Saved on S3!.")));
                else
                    $this->_flashMessenger->addMessage(array('error' => $translate->translate("S3 file").' <i>' . $fileName . '</i> '.$translate->translate("upload failled!.")));
            }
        } catch (Exception $ex) {
            // TODO : set as messages
            $this->_flashMessenger->addMessage(array('error' => $translate->translate("S3 file upload failled!.")));
//            echo $ex->getMessage();
        }
    }

    /**
     * Save Product Data.
     * @param <type> $form_values
     * @param <type> $model
     */
    public function save(&$form_values, $model) {
        // TODO : Get user id from Session and save it

        $data = array();
        $last_insert_id = null;
        $table_info = $model->info();
        //Zend_Debug::dump($table_info);

        $columns = $table_info['cols'];
//    $db = Zend_Registry::get('db');
        foreach ($columns as $key => $value) {
            if (!empty($form_values[$value]))
                $data[$value] = $form_values[$value];
//      $data[$value] = $db->quote($form_values[$value]);
            unset($form_values[$value]);
        }


        if (null === ($id = $data['id'])) {
            unset($data['id']);

            $last_insert_id = $model->insert($data);
            $form_values['product_id'] = $last_insert_id;
        } else {
            
            if(get_class($model)=='Model_Product'){
                $data['updated_date']  = date('Y-m-d');
            }
            unset($data['id']);
            $last_insert_id = $model->update($data, array('id = ?' => $id));

            // enable this to debug

            /*Zend_Debug::dump($data);
            $db = Zend_Registry::get ( 'db' );
            $db->getProfiler()->setEnabled(true);
            $last_insert_id = $model->update($data, array('id = ?' => $id));
            $query = $db->getProfiler()->getLastQueryProfile()->getQuery();
            $queryParams = $db->getProfiler()->getLastQueryProfile()->getQueryParams();
            Zend_Debug::dump($db->quoteInto($query, $queryParams));
            Zend_Debug::dump($query);
            $db->getProfiler()->setEnabled(false);die();*/

            // enable this to debug

            $form_values['product_id'] = $id;

        }
        return $last_insert_id;
    }

    /**
     * Save Product Data.
     * @param <type> $form_values
     * @param <type> $model
     */
    public function saveBasicInfo(&$form_values, $model, $type='web') {
    // TODO : Get user id from Session and save it

    $data = array();
    $last_insert_id = null;
    $table_info = $model->info();

    $columns = $table_info['cols'];
//    $db = Zend_Registry::get('db');

    
    foreach ($columns as $key => $value) {
      if (!empty($form_values[$value]))
        $data[$value] = $form_values[$value];
//      $data[$value] = $db->quote($form_values[$value]);
      unset($form_values[$value]);
    }

    $filter = new Nexva_Util_Filter_ProductName();
    if($data['name'])
    	$data ['name'] = $filter->filter($data ['name']);

    if (null === ($id = $data ['id'])) {
			unset ( $data ['id'] );

			if (is_null ( $data ['price'] ))
				$data ['price'] = 0;
			
			if (is_null ( $data ['show_in_nexpager'] ))
				$data ['show_in_nexpager'] = 0;

			$productValid = $model->checkDuplicateProduct ( $data ['name'], $data ['price'], $data ['user_id'] );
			
			if ($productValid === true) {

				$last_insert_id = $model->insert ( $data );

				$form_values ['product_id'] = $last_insert_id;
			
			} else {
				
				$this->_flashMessenger->addMessage ( array ('error' => $productValid ) );
				$this->_redirect ( 'product/create' );
			
			}
		
		} else {

			if (is_null ( $data ['show_in_nexpager'] ))
				$data ['show_in_nexpager'] = 0;

            unset($data['created_date']);
			$last_insert_id = $model->update ( $data, array ('id = ?' => $id ) );
			$form_values ['product_id'] = $id;
			$cacheUtil  = new Model_CacheUtil();
            $cacheUtil->clearProduct($id);
			
		}
		return $last_insert_id;
	}
  

    /**
     * Save Product MetaDeta
     * @param <type> $metadata
     */
    public function saveMeta($metadata) {
        $productId = $metadata['product_id'];
        unset($metadata['product_id']);
        $productMeta = new Model_ProductMeta();
        $productMeta->setEntityId($productId); //a valid user id obtained from
        foreach ($metadata as $key => $value) {
            // Saving MetaData
            $productMeta->$key = $value;
        }
    }

    /**
     * get all active platforms
     * @return <type>
     */
    private function getPlatforms() {
        $platforms = new Model_Platform();
        return $platforms->getAllPlatforms();
    }

    /**
     * Get all active categories
     * @return <type>
     */
    private function getCategories() {

        //find out if a chap is associated with this CP
        $userModel  = new Model_User();
        $currentCp  = $userModel->find($this->_getCpId())->current();
        //var_dump($currentCp->chap_id);die();
        $categories = new Model_Category();
        /*get categories in  french */
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        if(in_array($chapId, array('585474','585480'))){
            return $categories->getCategorylistByLanguage(2);// get categories in french
        }
        /**/
        if(($currentCp->chap_id == 4348) || !($currentCp->chap_id)){
            return $categories->getCategorylist();
        } else {
            $chapCategoryModel = new Pbo_Model_ChapCategories();
            $result = $chapCategoryModel->getAllChapCategories($currentCp->chap_id);

            $chapCategories = array();
            foreach ($result as $row) {
                $chapCategories[$row->parent_id][$row->id] = $row->name;
            }
            return $chapCategories;
        }
    }

    // populate from product table
    private function populateBasicForm($id, $table) {
        $rowset = $table->find($id);
        // get from products table
        $row = $rowset->current();
        $product = $row->toArray();

        // now get data from product meta table.
        $productMeta = array();
        $productMetaTable = new Model_ProductMeta();
        $productMetaTable->setEntityId($id); //a valid user id obtained from
        $productMeta['product_version'] = $productMetaTable->product_version;
        $productMeta['brief_description'] = strip_tags($productMetaTable->brief_description,'<a></a>');
        $productMeta['full_description'] = strip_tags($productMetaTable->full_description, '<br><br /><a></a>');
        $productMeta['notify_email'] = $productMetaTable->notify_email;
        $productMeta['desktop_product'] = $productMetaTable->desktop_product;
        return array_merge($product, $productMeta);
    }

    // populate submitted from other tables
    /**
     *
     * @param <type> $id
     * @param <type> $table
     * @param <type> $column
     * @return <type>
     */
    private function populateFormData($id, $table, $column) {
        $resultSet = $table->fetchAll(
                    $table->select()
                    ->where('product_id = ?', $id)
        );
        $entries = '';
        foreach ($resultSet as $row) {
            if ($column == 'device_id' || $column == 'category_id')
                $entries[$row->$column] = $row->id;
            else
                $entries[$row->id] = $row->$column;
        }
        return $entries;
    }

    /**
     * Format Error messages
     * @param <type> $msg
     * @return <type>
     */
    private function formatErrorMsg($elements) {
        $error = '';
        foreach ($elements as $name => $msgs) {
            foreach ($msgs as $key => $value) {
                $error .= strtoupper($name) . ' : ' . $value . '<br/>';
            }
        }
        return $error;
    }

    /**
     *
     * @param <type> $productId
     * @return <type>
     */
    private function getSelectedDevices($productId) {
        $phones = array();
        $productTable = new Model_Product();
        $productRowset = $productTable->find($productId);
        $productIs = $productRowset->current();
        $devicesByProduct = $productIs->findDependentRowset('Model_ProductDevices');
        foreach ($devicesByProduct as $device) {
            $deviceTable = new Model_Device();
            $deviceRowset = $deviceTable->find($device->device_id);
            $deviceIs = $deviceRowset->current();
            $name = $this->trimAndElipsis($deviceIs->brand . ' ' . $deviceIs->model, 115, '...');
            // construct device array
            $phones[$deviceIs->id]['id'] = $deviceIs->id;
            $phones[$deviceIs->id]['phone'] = $name;
            $phones[$deviceIs->id]['img'] = '/admin/assets/img/phones/nokia_7610.jpg';
            $phones[$deviceIs->id]['css'] = 'all';
            $attributesByProduct = $deviceIs->findDependentRowset('Model_DeviceAttributes');
            $attributes = array();
            $resolution = array();
            foreach ($attributesByProduct as $attributed) {
//                print_r($attributed->toArray());
                // TODO : do a database synchronization with this array
                $attributeDefinitions = array('Device OS', 'Pointing Method', 'Device OS Version', 'Support MP3',
                  'Support MIDP 1.0', 'Support MIDP 2.0');
                $attributeDeff = array(1, 2, 3, 4, 5, 6);
                $arrtibDefId = $attributed->device_attribute_definition_id;
                if (!empty($attributed->value) && $arrtibDefId != 7 && $arrtibDefId != 8)
                    $attributes[] = str_replace($attributeDeff, $attributeDefinitions,
                            $attributed->device_attribute_definition_id);
                if ($arrtibDefId == 7 || $arrtibDefId == 8) { // device width and height
                    $resolution[] = $attributed->value;
                }
            }
            $attributes[] = 'Resolution : ' . implode('X', $resolution);
            $phones[$deviceIs->id]['info'] = $attributes;
        }
        return $phones;
    }

    /**
     *
     * @param <type> $string
     * @param <type> $count
     * @param <type> $ellipsis
     * @return <type>
     */
    protected function trimAndElipsis($string, $count, $ellipsis = FALSE) {
        if (strlen($string) > $count) {
            $length -= strlen($ellipsis);  // $length =  $length – strlen($end);
            $string = substr($string, 0, $count);
            $string .= $ellipsis;  //  $string =  $string . $end;
        }
        return $string;
    }

    function deleteAction() {
        
        $product = new Model_Product();
        $product->update(
            array(
              "deleted" => 1, "deleted_date" => date('Y-m-d')
            ),
            array(
              "id = ?" => $this->_request->id,
              "user_id = ?" => Zend_Auth::getInstance()->getIdentity()->id
            )
        );
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');
        $this->_flashMessenger->addMessage( array ('info' => $translate->translate("Product deleted successfully")));

        $this->_redirect("/product/view");
    }

    public function filedeleteAction() {
        $fileId = $this->_getParam('id');
        $appId = $this->_getParam('prod');

        // check validations
        $product = new Model_Product();
        $formBasic = $this->populateBasicForm($appId, $product);
        $userId = Zend_Auth::getInstance()->getIdentity()->id;
        
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');

        if ($formBasic['user_id'] != $userId)
            $this->_redirect("/product/edit/id/$appId/6/4");
        // @TODO : Delete from the S3 and authenticate delete
        //get config
        $config = Zend_Registry::get('config');
        $awsKey = $config->aws->s3->publickey;
        $awsSecretKey = $config->aws->s3->secretkey;
        $bucketName = $config->aws->s3->bucketname;
        $bucketExists = false;
        $s3 = new Zend_Service_Amazon_S3($awsKey, $awsSecretKey);
        // get file name
        $productFiles = new Model_ProductFiles();
        $object = $productFiles->getFileNameById($fileId);
        $delete = $s3->removeObject($bucketName . '/' . $appId . '/' . $object);
        if ($delete) {
            $this->_flashMessenger->addMessage(array('info' => $translate->translate("File deleted successfully")));
            $productFiles = new Model_ProductFiles();
            $row = $productFiles->find($fileId);
            $productFiles->delete('id = ' . $fileId . ' and product_id = ' . $appId);
        }
        $this->_redirect("/product/edit/id/$appId/6/4");
    }

    public function visualdeleteAction() {
        // @TODO : Delete from the S3 and authenticate delete
        $fileId = $this->_getParam('id');
        $appId = $this->_getParam('prod');
        // check validations
        $product = new Model_Product();
        $formBasic = $this->populateBasicForm($appId, $product);
        $userId = Zend_Auth::getInstance()->getIdentity()->id;
        if ($formBasic['user_id'] != $userId)
            $this->_redirect("/product/edit/id/$appId/6/2");

        $productFiles = new Model_ProductImages();
        $productFiles->delete('id = ' . $fileId . ' and product_id = ' . $appId);
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');
        $this->_flashMessenger->addMessage(array('info' => $translate->translate("File deleted successfully")));
        $this->_redirect("/product/edit/id/$appId/6/2");
    }

    public function publishAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $requestPerms = $this->_request->getParams();
        $productId = $requestPerms['id'];
        $this->view->productid = $productId;
        // if the form is submitted
        $product = new Model_Product();
        $formBasic = $this->populateBasicForm($productId, $product);
        $userId = Zend_Auth::getInstance()->getIdentity()->id;
        if ($formBasic['user_id'] != $userId)
            $this->_redirect("/product/view");
        if (isset($productId)) {
            $productId = (empty($productId)) ? $requestPerms['product_id'] : $productId;
            $formValues = array('id' => $productId, 'status' => 'PENDING_APPROVAL');
            $update = $this->save($formValues, $product);//echo $update;die();
            // add message and redirest to the next step
            $translate = Zend_Registry::get('Zend_Translate');
            if ($update) {
                $this->_flashMessenger->addMessage(array('info' => $translate->translate("Your product")." ". $formBasic['name'] . ' '.$translate->translate("is waiting to approve.")));
                $this->_redirect (CPBO_PROJECT_BASEPATH.'product/view');
            } else {
                $this->_flashMessenger->addMessage(array('error' => $translate->translate("Error occured while saving data, please re-try.")));
                $this->_redirect (CPBO_PROJECT_BASEPATH.'product/view');
            }
        }
    }

    public function embededAction() {
        
        
        ini_set('display_errors',1);
        ini_set('display_startup_errors',1);
        error_reporting(-1);
        

        $this->_helper->getHelper('layout')->disableLayout();
        $productId = $this->_request->id;
        $prodModel = new Model_Product();
        $this->view->content = $prodModel->getProductDetailsById($productId);

        //find out if a chap is associated with this CP
        $userModel  = new Model_User();
        $currentCp  = $userModel->find($this->_getCpId())->current();

        if (isset($currentCp->chap_id) && !empty($currentCp->chap_id)) {
            $themeMeta  = new Model_ThemeMeta();
            $themeMeta->setEntityId($currentCp->chap_id);
            $themeData  = $themeMeta->getAll();
            $this->view->themeData  = $themeData;
            $this->view->chapId     = $currentCp->chap_id;    
        }
    }

    public function badgeAction() {

        $this->_helper->getHelper('layout')->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $contentId = $this->_request->id;
        $size = $this->_request->s;
        $badge = new Nexva_Badge_Badge($contentId);
        $badge->createBadge($size);
    }

    public function royaltiesDisplayAction() {

        $this->_helper->getHelper('layout')->disableLayout();
        //     $this->_helper->viewRenderer->setNoRender ( true );
        
        die();

        $userpayouts = new Model_User ( );
        $userpayoutsType = $userpayouts->getRoyalty();

        $this->view->name = $userpayoutsType->name;
        $this->view->payoutCp = $userpayoutsType->payout_cp;
        $this->view->payoutNexva = $userpayoutsType->payout_nexva;

        $paymentGateway = new Model_PaymentGateway ( );

        $paymentGateways = $paymentGateway->getPaymentGatewayChargesForList();

        $this->view->paymentGateways = $paymentGateways;

        $this->view->amount = $this->_request->amount;
    }
	
	/**
	 * Display Select Edit Main options 
	 *
	 */
	public function selecteditAction() {
		
		$this->view->title = "Edit Content Type Publish Content / neXpayer Integration";
		
		$product = new Model_Product ( );
		$rowset = $product->find ( $this->_request->id );
		$productRow = $rowset->current ();
		
		if ($productRow->inapp) {
			$this->view->selectOptionInapp = 1;
			$this->view->selectOptionWeb = 0;
		} else {
			$this->view->selectOptionWeb = 1;
			$this->view->selectOptionInapp = 0;
		}
		
		$this->view->id = $this->_request->id;
		if ($this->_request->select == 'inapp')
			$this->_redirect ( '/product/editinapp/id/' . $this->_request->id );
		elseif ($this->_request->select == 'web')
			$this->_redirect ( '/product/edit/id/' . $this->_request->id );
	
	}
	
	
    public function editinappAction() {
    	
    	$this->view->title = "neXpayer Integration";
		
		$productId = $this->_getParam ( 'id' );
		
		$review = $this->_getParam ( 'review' );
		// basic info
		$form_basic = new Cpbo_Form_ProductBasicInappInfo ( );
		$product = new Model_Product ( );
		$formBasicInfo = $this->populateBasicForm ( $productId, $product );
		$form_basic->populate ( $formBasicInfo );
		
		$this->view->productId = $productId;
		
		$form_basic->setAction ( CP_PROJECT_BASEPATH . 'product/basic-inapp' );
		$this->view->form_basic = $form_basic;
		
		$this->view->form_basic->review->setValue ( $review );
		
		$userpayouts = new Model_User ( );

		$userpayoutsType = $userpayouts->getRoyalty ();
		$this->view->form_basic->status->setValue ( 'INCOMPLETE' );
		$this->view->form_basic->payout->setValue ( $userpayoutsType->payout_cp );
		
		if ($userpayoutsType->payout_cp > 0) {
			
			$newDescription = $this->view->form_basic->price->getDescription () . "  <div id='info' style='visibility:display;'>Your payout scheme " . $userpayoutsType->name . " and you will get $userpayoutsType->payout_cp% </span> </span><div>";
		
		}
		
		$this->view->form_basic->price->setDescription ( $newDescription );
	
	}
	
	
	  
public function basicInappAction() {
    // TODO : add validation on when no product ID
    $this->_helper->viewRenderer->setNoRender(true);
    $requestPerms = $this->_request->getParams();
    $productId = $requestPerms['id'];

    if ($this->_request->isPost()) {
      $form_values = $this->_request->getParams();
      $form_values['status'] = 'INCOMPLETE';

      $product = new Model_Product();
      $form_values['user_id'] = Zend_Auth::getInstance()->getIdentity()->id;
      $form_values['inapp'] = 1;
      
                $lastInsertId = $this->saveBasicInfo( $form_values, $product, 'inapp' );
                // if the form is updating then set it to the hidden id
                $productId = $form_values ['product_id'];
                
                // product create time
                if (! isset ( $productId ) && empty ( $productId ))
                    $form_values ['created_date'] = new Zend_Db_Expr ( 'NOW()' );
                    // product last update date
                $form_values ['product_changed'] = time ();
                $this->saveMeta ( $form_values );
                /*get translater*/
                $translate = Zend_Registry::get('Zend_Translate');
                
                $this->_flashMessenger->addMessage ( array ('info' =>  $translate->translate("Successfully saved.")) );
                // redirect to next step
                if (empty ( $review ))
                    $this->_redirect ( 'product/publishnow/id/'. $productId);
                else
                    $this->_redirect ( 'product/publishnow/id/'. $productId);
     
    }
    else {
      $error = $this->formatErrorMsg($form_basic->getMessages());
      $this->_flashMessenger->addMessage(array('error' => $error));
      // loop  to same step
      if (empty($productId))
        $this->_redirect (CPBO_PROJECT_BASEPATH.'product/create');
      else
        $this->_redirect (CPBO_PROJECT_BASEPATH.'product/edit/id/' . $productId . '/6/1');
    }
  }
	
	public function publishnowAction() {
		
		$config = Zend_Registry::get ( 'config' );
		$key = $config->nexva->application->salt;
		
		$this->view->uniqId = md5 ( $key . $this->_request->id );
		$this->view->productId = $this->_request->id;
	
	}
	
	/**
	 * Display Select Main options 
	 *
	 */
	public function selectAction() {
		
		$this->view->title = "Select Content Type";
		
		if ($this->_request->select == 'inapp')
			$this->_redirect ( '/product/create-inapp' );
		elseif ($this->_request->select == 'web')
			$this->_redirect ( '/product/create' );
	
	}
    
    public function createInappAction() {
		$this->view->title = "neXpayer Integration";
		
		$form_basic_inapp = new Cpbo_Form_ProductBasicInappInfo ( );
		// Basic Form
		$form_basic_inapp->setAction ( CP_PROJECT_BASEPATH . 'product/basic-inapp' );
		$this->view->form_basic_inapp = $form_basic_inapp;
		
		$platforms = $this->getPlatforms ();
                /*get translater*/
                $translate = Zend_Registry::get('Zend_Translate');
                
		$this->_flashMessenger->addMessage ( array ('error' => $translate->translate("message") ) );
	
	}

    public function claimAction() {
    	
    	$inapp = 0;
        $requestPerms = $this->_request->getParams ();
        $productId = $requestPerms ['id'];
        $inapp = $this->_request->inapp;
        $this->view->productid = $productId;
        // get Basic Info
        $product = new Model_Product ( );
        $basicInfo = $this->populateBasicForm ( $productId, $product );
        // get user info
        $userMeta = new Model_UserMeta ( );
        $userMeta->setEntityId ( $basicInfo ['user_id'] );
        
        $basicInfo ['company_name'] = $userMeta->COMPANY_NAME;
       
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');
           
        unset ( $basicInfo ['category_id'] );
        unset ( $basicInfo ['platform_id'] );
        unset ( $basicInfo ['user_id'] );
        unset ( $basicInfo ['deleted'] );
        unset ( $basicInfo ['id'] );
        unset ( $basicInfo ['created_date'] );
        unset ( $basicInfo ['deleted_date'] );
        unset ( $basicInfo ['inapp'] );
        unset ( $basicInfo ['created_date'] );
        unset ( $basicInfo ['deleted_date'] );
        unset ( $basicInfo ['inapp'] );
        unset ( $basicInfo ['device_selection_type'] );
        unset ( $basicInfo ['content_type'] );
        unset ( $basicInfo ['keywords'] );
        unset ( $basicInfo ['is_featured'] );
        unset ( $basicInfo ['is_suggested'] );
        unset ( $basicInfo ['product_version'] );
        unset ( $basicInfo ['notify_email'] );
        unset ( $basicInfo ['desktop_product'] );
        unset ( $basicInfo ['company_name'] );
        unset ( $basicInfo ['status'] );
        
         
        //get platform name
        if (! empty ( $basicInfo ['platform_id'] )) {
            $platformId = $basicInfo ['platform_id'];
            $platformMdl = new Model_Platform ( );
            $platform = $platformMdl->getPlatformName ( $platformId );
            unset ( $basicInfo ['platform_id'] );
            $basicInfo ['platform'] = $platform;
        }
        
        $this->view->basicinfo = $basicInfo;
        
        // Categories
        $productCategories = new Model_ProductCategories ( );
        $categories = $this->getCategories ();
        $this->view->categories = $categories;
        $this->view->categorySelected = $this->populateFormData ( $productId, $productCategories, 'category_id' );
        
        // Visuals
        $productVisuals = new Model_ProductImages ( );
        $this->view->visuals = $this->populateFormData ( $productId, $productVisuals, 'filename' );
        
        // Builds
        $builds = array ();
        $product = new Model_Product ( );
        $builds = $product->getBuilds ( $productId );
        $this->view->builds = $builds;
        $prodBuild = new Model_ProductBuild ( );
        $buildFiles = array ();
        $buildDevices = array ();
        foreach ( $builds as $value ) {
            $buildFiles [$value->id] = $prodBuild->getFiles ( $value->id );
            $buildDevices [$value->id] = $prodBuild->getSelectedDevices ( $value->id );
        }
        
        $this->view->files = $buildFiles;
        $this->view->devices = $buildDevices;

		
		$auth = Zend_Auth::getInstance ();
		
		$productModel = new Model_Product ( );
		
		$this->view->productInfo = $productModel->getProductDetailsById ( $this->_request->id );

		
		if ($this->view->productInfo['uid'] == 1) {
			
			if ($this->_request->applicationId and $this->_request->agree) {
				
				$data ['id'] = $this->_request->applicationId;
				$data ['user_id'] = $auth->getIdentity()->id;
				$productModel->save ( $data );
				
				$userMeta = new Model_UserMeta();
				$userModel = new Model_User();
			    $fisrtName = $userMeta->getAttributeValue($auth->getIdentity()->id, 'FIRST_NAME');
                $lastName = $userMeta->getAttributeValue($auth->getIdentity()->id, 'LAST_NAME');
                $companyName = $userMeta->getAttributeValue($auth->getIdentity()->id, 'COMPANY_NAME');
                $designation = $userMeta->getAttributeValue($auth->getIdentity()->id, 'DESIGNATION');
                $user = $userModel->getUserDetailsById($auth->getIdentity()->id);

                if (empty($fisrtName)) {
                    $name = $user->email;
                } else {
                    $name = $fisrtName . ' ' . $lastName;
                }
					//	
				$mailer = new Nexva_Util_Mailer_Mailer ( );
				$mailer->setSubject ( "$companyName has claimed application: " . $this->view->productInfo ['name'] );
				
				$contentAdmin = explode (",", Zend_Registry::get ( 'config' )->nexva->application->content_admin->contact );
				
				$mailer->addTo ( $contentAdmin );
                    
                    $session=new Zend_Session_Namespace('chap');
                    $config = Zend_Registry::get('config');
                    $chapIds= explode(',',$config->nexva->application->frenchchaps);
                    
                    if( in_array($session->chap->id, $chapIds) ){
                         $template = 'app_claim_fr.phtml';
                    }else{
                         $template = 'app_claim.phtml';
                    }            
                                
                    $mailer->setLayout("generic_mail_template")         //change if needed. remember, stylesheets cannot be linked in HTML email, so it has to be embedded. see the layout for more.
                    ->setMailVar("cp_email", $user->email)
                    ->setMailVar("cp_name", $name)
                    ->setMailVar("cp_company_name", $companyName)
                    ->setMailVar("designation", $designation)
                    ->setMailVar("cp_id", $user->id)
                    ->setMailVar("content_name", $this->view->productInfo['name'])
                    ->setMailVar("content_id", $this->view->productInfo['id'])
                    ->setMailVar("date", date("Y-m-d H:i:s"))
                    ->setMailVar("ip", $_SERVER["REMOTE_ADDR"])
                    ->sendHTMLMail($template); //change this. mail templates are in /views/scripts/mail-templates
                
				$this->_flashMessenger->addMessage ( array('info' => 'You have successfully claimed: ' . $this->view->productInfo ['name']));
				$this->_redirect ( 'product/view' );
			
			}
			
			$this->view->title = $translate->translate("Claim your App")."  - ".$this->view->productInfo ['name'];
		}
		else
		
		{
			
			$this->_flashMessenger->addMessage ( array('error' => $translate->translate("The application").', ' . $this->view->productInfo ['name'] . '  you are trying to claim has already been claimed.' ));
			$this->_redirect ( 'product/view' );
		}
        
        //Zend_Debug::Dump($productInfo);
        //die();

    }
	
    
    //Function for getting product search results for CPs
    public function searchAction()
    {
        
        //$searchKey = trim($this->_getParam('search'));
        $searchKey = trim($this->_request->q);
        
        
        if(!empty($searchKey) && strlen($searchKey) > 0)
        {
            $this->_helper->viewRenderer->setNoController(true);
            $this->_helper->viewRenderer('product/view');

            $productModel = new Model_Product();
            $productResults = $productModel->getProdcutDetailsbySearch($searchKey);

            $rowCount = count($productResults);

            if (0 == $rowCount) {
                $this->view->show_empty_msg = true;
            }


            $page = $this->getRequest()->getParam('page', 0);
            $paginator = Zend_Paginator::factory($productResults);
            $paginator->setItemCountPerPage(10);
            $paginator->setCurrentPageNumber($page);

            $this->view->products = $paginator;

            $userMeta   = new Model_UserMeta();
            $userMeta->setEntityId($this->_getCpId());
            $this->view->nexpagerState  = $userMeta->ACTIVE_NEXPAGE;
            $this->view->searchVal = $searchKey;
        } 
        else
        {
            $this->_redirect (CPBO_PROJECT_BASEPATH.'product/view');
        }
         
        
        
    }

    /**
     * @param $details
     * @param $productId
     * Send mails to contentadmins upon app detail changes
     */
    function sendContentadminMail($details,$productId){

        $productModel = new Cpbo_Model_Product();
        $productDetails = $productModel->getProductDetails($productId);

        $userId = $productDetails->user_id;

        $userModel = new Model_User();
        $userDetails = $userModel->getUserById($userId);

        $userMail = $userDetails->email;
        
        $session=new Zend_Session_Namespace('chap');
        $config = Zend_Registry::get('config');
            $chapIds= explode(',',$config->nexva->application->frenchchaps);
                    
            if( in_array($session->chap->id,$chapIds) ){
                $template = 'notify_product_detail_change_fr.phtml';
            }else{
                 $template = 'notify_product_detail_change.phtml';
            }
                    
        
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject('Product '.$details.' has changed');
        $mailer->addTo(Zend_Registry::get('config')->nexva->application->content_admin->contact)
                ->setMailVar("firstName", 'ContentAdmin')
                ->setMailVar("productId", $productId)
                ->setMailVar("email", $userMail)
                //->setMailVar("userId", $userId)
                ;
        $mailer->setLayout("generic_mail_template");
        $mailer->sendHTMLMail($template);
    }
}

?>