<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Admin_ProductController extends Zend_Controller_Action {

    protected $_flashMessenger;

    function preDispatch() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {

            if ($this->_request->getActionName() != "login") {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();
            }
            if ($this->_request->getActionName() != "login")
                $this->_redirect(PROJECT_BASEPATH.'/user/login');
        }
    }

    /* Initialize action controller here */

    public function init() {
        // include Ketchup libs
        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css');
        //$this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/ketchup/js/jquery.min.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js');
        // checkboxtree file for categories
        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/js/jquery/plugins/checkboxtree/css/checkboxtree.css');
        //$this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/checkboxtree/js/jquery-latest.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/checkboxtree/js/jquery.checkboxtree.js');
        // Ajax upload
//        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ajax-upload/ajaxupload.js');
        // adding admin JS file
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/admin/assets/js/admin.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'cp/assets/js/cp.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/cp/assets/js/category.js');

        // Flash Messanger
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->flashMessenger = $this->_flashMessenger;
    }

    function indexAction() {
        
    }

    function approveAction() {
        $this->_helper->viewRenderer->setNoRender();
        $id = $this->_request->id;

        $productModel = new Model_product();
        $productModel->update("status='APPROVED'", "id=$id");
        echo 'succesfully approved';

        reurn;
    }

    function viewAction() {
    	//ini_set('memory_limit', '200M');
        $this->view->search = $this->_request->search;
        $product = new Admin_Model_Product();
        $this->view->searchin = $this->_request->searchin;
        $config = Zend_Registry::get('config');
        $lastRequestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();

        $this->view->deleteUriAppend = str_replace("/product/view", "", $lastRequestUri);


        $sessionUser = new Zend_Session_Namespace('test');

        $sessionUser->link = "http://" . $config->nexva->application->admin->url . Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
        // $lastRequestUri = serialize($this->ecrypt($lastRequestUri));
        $this->view->lastRequestUri = $lastRequestUri;
        $this->view->product_form_submit_to = "http://" . $config->nexva->application->admin->url . "/" . $this->getRequest()->getControllerName() . "/" . $this->getRequest()->getActionName();

        $search = trim($this->_request->search);
        $searchin = trim($this->_request->searchin);

        
        if ($this->_request->tab == 'tab-all') {
            $productsAllPaginater = Zend_Paginator::factory($product->getProductByStatus($this->_request->status, $search, $searchin));
        } else {
            $productsAllPaginater = Zend_Paginator::factory($product->getProductByStatus($this->_request->status));
        }

        if (count($productsAllPaginater) == 0) {
            $this->view->show_products_all_empty_msg = true;
        }

        $productsAllPaginater->setCurrentPageNumber($this->_request->getParam('page', 0));
        $productsAllPaginater->setItemCountPerPage(10);
        $this->view->products_all = $productsAllPaginater;

        if ($this->_request->tab == 'tab-approved') {
            $productsApprovedPaginater = Zend_Paginator::factory($product->getProductByStatus('APPROVED', $search, $searchin));
        } else {
            $productsApprovedPaginater = Zend_Paginator::factory($product->getProductByStatus('APPROVED'));
        }
        if (count($productsApprovedPaginater) == 0) {
            $this->view->show_products_approved_empty_msg = true;
        }
        $productsApprovedPaginater->setCurrentPageNumber($this->_request->getParam('approved', 1));
        $productsApprovedPaginater->setItemCountPerPage(10);
        $this->view->products_approved = $productsApprovedPaginater;
        
        

        if ($this->_request->tab == 'tab-not-approved') {
            $productsNotApproved  = Zend_Paginator::factory($product->getProductByStatus('NOT_APPROVED', $search, $searchin));
        } else {
            $productsNotApproved = Zend_Paginator::factory($product->getProductByStatus('NOT_APPROVED'));
        }
        
        if (count($productsNotApproved) == 0) {
            $this->view->show_product_not_approved_empty_msg = true;
        }
        $productsNotApproved->setCurrentPageNumber($this->_request->getParam('non_approved', 1));
        $productsNotApproved->setItemCountPerPage(10);
        $this->view->products_not_approved = $productsNotApproved;


        if ($this->_request->tab == 'tab-under-review') {
            $productsUnderReview = Zend_Paginator::factory($product->getProductByStatus('UNDER_REVIEW', $search, $searchin));
        } else {
            $productsUnderReview = Zend_Paginator::factory($product->getProductByStatus('UNDER_REVIEW'));
        }

        if (count($productsUnderReview) == 0) {
            $this->view->show_products_under_review_empty_msg = true;
        }
        $productsUnderReview->setCurrentPageNumber($this->_request->getParam('under_review', 1));
        $productsUnderReview->setItemCountPerPage(10);
        $this->view->products_under_review = $productsUnderReview;
        
       if ($this->_request->tab == 'tab-pending') {
            $productsPending = Zend_Paginator::factory($product->getProductByStatus('PENDING_APPROVAL', $search, $searchin));
        } else {
            $productsPending = Zend_Paginator::factory($product->getProductByStatus('PENDING_APPROVAL'));
        }
        
        $productsPending->setCurrentPageNumber($this->_request->getParam('pending', 1));
        $productsPending->setItemCountPerPage(10);
        $this->view->productsPending  =  $productsPending;
        
        if (count ( $productsPending ) == 0) {
            $this->view->show_products_penidng_empty_msg = true;
        
        }


        if ($this->_request->tab == 'tab-rejected') {
            $productsRejected = Zend_Paginator::factory($product->getProductByStatus('REJECTED', $search, $searchin));
        } else {
            $productsRejected = Zend_Paginator::factory($product->getProductByStatus('REJECTED'));
        }
        
        if (count($productsUnderReview) == 0) {
            $this->view->show_products_rejected_empty_msg = true;
            
        }
        $productsRejected->setCurrentPageNumber($this->_request->getParam('rejected', 1));
        $productsRejected->setItemCountPerPage(10);
        $this->view->products_rejected = $productsRejected;
        
        
        $this->view->selected_tab = $this->_request->tab;
    }

    public function createAction() {
        $this->view->title = "Create product";
        // Load only the basic form
        // TODO : authentications etc
//        $productId = $this->_getParam('id');
        $form_basic = new Admin_Form_ProductBasicInfo();
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
        $form_basic->setAction(CP_PROJECT_BASEPATH. '/product/basic');

        $this->view->form_basic = $form_basic;
        $categories = $this->getCategories();
        $this->view->form_basic->category_parent->setMultiOptions($categories[0]);
        //get platforms
        $platforms = $this->getPlatforms();
        $userModel = new Model_User();
        $userMeta = new Model_UserMeta();
        //$cps = $userModel->getUserList();
        $cps = $userModel->getCpListOrderbyCompany();
        $cpList = array();
        foreach ($cps as $cp) {
            $userMeta->setEntityId($cp->id);
            $company = $userMeta->COMPANY_NAME;
            $firstName = $userMeta->FIRST_NAME;
            if (empty($company) && empty($firstName))
                continue;
            $cpList[$cp->id] = empty($company) ? $userMeta->LAST_NAME . ' ' . $firstName : $company;
        }

    //    $this->view->form_basic->user_id->setMultiOptions($cpList);

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

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        
        // TODO : authentications etc
        $productId = $this->_getParam('id');
        // get the step
        $step = $this->_getParam('6');
        // get the review if set
        $review = $this->_getParam('review');
        // basic info
        $form_basic = new Admin_Form_ProductBasicInfo();
        $product = new Model_Product();
        $formBasicInfo = $this->populateBasicForm($productId, $product);

        //Zend_Debug::dump($formBasicInfo);die();
        //new-test & new

        $form_basic->populate($formBasicInfo);
        $userModel = new Model_User();
        $userMeta = new Model_UserMeta();

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
        $form_basic->setAction(CP_PROJECT_BASEPATH. '/product/basic');
        $this->view->form_basic = $form_basic;

        //$cps = $userModel->getUserList();
        $cps = $userModel->getCpListOrderbyCompany();
        $cpList = array();
		$cpList[''] = 'No Company'; //
        foreach ($cps as $cp) {
            $userMeta->setEntityId($cp->id);
            $company = $userMeta->COMPANY_NAME;
            $firstName = $userMeta->FIRST_NAME;
            if (empty($company) && empty($firstName))
                continue;
            $cpList[$cp->id] = empty($company) ? $userMeta->LAST_NAME . ' ' . $firstName : $company;
        }

        //$this->view->form_basic->user_id->setMultiOptions($cpList);
        
        $this->view->form_basic->category_parent->setMultiOptions($categories[0]);
        $prodCatMdl = new Model_ProductCategories();
        $parentId   = $prodCatMdl->selectedParentCategory($productId);
        $subcat     = $prodCatMdl->selectedSubCategory($productId);
        
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

        // Product Registration
        $form_registration->setAction($this->getFrontController()->getBaseUrl() . '/product/registration');
        $this->view->form_registration = $form_registration;
        $this->view->form_registration->id->setValue($productId);
        $this->view->form_registration->review->setValue($review);
        $productKeys = new Model_ProductKey();
        $keys = array();
        
        
        $values = $this->_flashMessenger->getMessages();
        if (count($values) >= 1 and $this->_request->status == 'error') {
                $keys['dynamic'] = '';
                $keys['keys'] = '';
        } else {
        	 
        		$keys['dynamic'] = $productKeys->getKeysByProductId($productId);
        		$keys['keys'] = $productKeys->getKeysByProductId($productId);
        	
        }
        
        $keys['keys'] = $productKeys->getKeysByProductId($productId);
        $populatedData = array_merge($keys, $formBasicInfo);
        $form_registration->populate($populatedData);

        // Prodcut Files
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

        // Product Devices

        //Zend_Debug::dump($this->view->visualScreenshots);die();

        $form_devices->setAction($this->getFrontController()->getBaseUrl() . '/product/device');
        $this->view->form_devices = $form_devices;
        $this->view->form_devices->id->setValue($productId);
        $this->view->form_devices->review->setValue($review);
        $this->view->selected = $formBasicInfo['device_selection_type'];
        $productDevAttrib = new Model_productDeviceAttributes();
        $this->view->deviceAttributes = $productDevAttrib->getSelectedDeviceAttributesById($productId);

        // Tab selection
        $this->view->active = $step;
    }

    /**
     * Basic info action
     * @return <type>
     */
    public function basicAction() {

        // TODO : add validation on when no product ID
        $this->_helper->viewRenderer->setNoRender(true);
        $requestPerms = $this->_request->getParams();
    
        $productId = $requestPerms['id'];
//        if(empty ($productId))
//            $this->_redirect('product/edit/id/' . $productId . '/6/2');
        $review = $requestPerms['review'];
        $form_basic = new Admin_Form_ProductBasicInfo();
        // TODO : there is a way to send data directly to form
        $platforms = $this->getPlatforms();
//    $form_basic->platform_id->setMultiOptions($platforms);
        if ($this->_request->isPost()) {
//      if ($form_basic->isValid($this->_request->getParams())) {
//      $form_values = $form_basic->getValues();.
            $form_values = $this->_request->getParams();
            $product = new Model_Product();
            $form_values['created_date']  = date('Y-m-d');
            
            $lastInsertId = $this->save($form_values, $product);
            // if the form is updating then set it to the hidden id
            $productId = $form_values['product_id'];
            if (!isset($productId) && empty($productId))
                $form_values['created_date'] = new Zend_Db_Expr('NOW()');
            // product last update date
            $form_values['product_changed'] = time();
            $this->saveMeta($form_values);
            // save categories
            $prodCategoryMdl = new Model_ProductCategories();
            $categories = array($form_values ['category_parent'], $form_values ['category_sub']);
            $prodCategoryMdl->updateCategoriesByProdId($productId, $categories);

            //
            $this->_flashMessenger->addMessage(array('info' => 'Successfully saved.'));
            // redirect to next step
            if (empty($review))
                $this->_redirect('product/edit/id/' . $productId . '/6/2');
            else
                $this->_redirect('product/display/id/' . $productId);
        }
        else {
            $error = $this->formatErrorMsg($form_basic->getMessages());
            $this->_flashMessenger->addMessage(array('error' => $error));
            // loop  to same step
            if (empty($productId))
                $this->_redirect('product/create');
            else
                $this->_redirect('product/edit/id/' . $productId . '/6/1');
        }
    }

    /*
     * Upload visuals and save them in table
     */

    
    
    public function visualsAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $formVisuals = new Admin_Form_ProductVisuals();
        $requestPerms = $this->_request->getParams();
        $message = '';
        $productId = $requestPerms['id'];
        $review = $requestPerms ['review'];

        
        if ($this->_request->isPost()) {
    
            if ($formVisuals->isValid($this->_request->getParams())) {
				
            	$config = Zend_Registry::get('config');
   		$visualPath = $_SERVER['DOCUMENT_ROOT'] . $config->product->visuals->dirpath;
       
            	//  process screenshot(s) upload   
                if (is_array($formVisuals->screenshots->getFileInfo())) {
                	
   			// get the file trasfer adapter to reveive and rename array of screenshots 
                 	$fileAdapter = $formVisuals->screenshots->getTransferAdapter();
                        $i = 0;
                    
                    $productImages = new Model_ProductImages();
                    $savedVisuals = $this->populateFormData($productId, $productImages, 'filename');
                    
                    if (is_array($savedVisuals)){
                        $saveVisualIds = array_keys($savedVisuals);
                    }
                    
                    foreach ($formVisuals->screenshots->getFileInfo() as $screenshotInfo) {
                        
                    	//process the image fields. if image field is empty check the next one and process it  
                    	$name = $screenshotInfo['name'];
                        if (empty($name)){
                            continue;
                        }
                            
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

                    }
                    
                    $message .= 'Screenshots successfully saved<br/>';
                
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
                    
                    // use this receiving single image 
                    $formVisuals->thumbnail->receive();
                    
                    // save thumbnail in the db 
                    $product = new Model_Product();
                    $thumbanil = array('id' => $productId, 'thumbnail' => $fileName  );
                    $this->save($thumbanil, $product);
                    $message .= 'Thumbnail successfully saved';
                }
                  
                $cacheUtil  = new Model_CacheUtil();
            	$cacheUtil->clearProduct($productId);
                
                
                // Redirect to next step
                if (empty($review))	 {
					
                    $formVisuals = new Model_ProductImages ( );
                    $rowVisuals = $formVisuals->fetchAll ( 'product_id = ' . $productId );
					
					
                    $countVisuals = $rowVisuals->count();
                        if (empty ($countVisuals) or $thumbnailUploaded) { // if no screenshots
                            $this->_flashMessenger->addMessage ( array ('error' => 'Product visuals are not found!. Please upload atleast one thumbnail  and a screenshot.' ) );
                            $this->_redirect ( 'product/edit/id/' . $productId . '/6/2' );
			                            
                        } else {						
                            $this->_flashMessenger->addMessage ( array ('info' => $message ) );
                            $this->_redirect ( 'product/edit/id/' . $productId . '/6/3' );
                        }					 
					
		}else{
                    $this->_flashMessenger->addMessage ( array ('info' => $message ) );
                    $this->_redirect('product/display/id/' . $productId);
                  
		}
            } else {
//                echo "<pre>";
//                print_r($formVisuals->getErrorMessages());
//                echo "</pre>";die();
                ///var_dump($formVisuals);die("OK");
//                $this->view->errors = $formVisuals->getMessages();
                $error = $this->formatErrorMsg($formVisuals->getMessages());
                $this->_flashMessenger->addMessage(array('error' => $error));
                $this->_redirect('product/edit/id/' . $productId . '/6/2');
            }
        }
    }

  


    public function filesAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $requestPerms = $this->_request->getParams();
        $productId = $requestPerms['id'];
        $review = $requestPerms['review'];
        $formFilesObj = new Cpbo_Form_ProductFiles();
        $formFiles = $formFilesObj->create();
        if ($this->_request->isPost()) {
            if ($formFiles->isValid($this->_request->getParams())) {
                $formValues = $formFiles->getValues();
//                $productId = $formValues['id'];
                $productFiles = new Model_ProductFiles();
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
                    $this->_flashMessenger->addMessage(array('info' => 'Successfully Uploaded to Local Server!.'));
                }
                // redirect to next step
                if (empty($review))
                    $this->_redirect('product/edit/id/' . $productId . '/6/5');
                else
                    $this->_redirect('product/display/id/' . $productId);
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

                    $this->_flashMessenger->addMessage(array('info' => 'Successfully Add the  URL filed!.'));
                    // redirect to next step
                    if (empty($review))
                        $this->_redirect('product/edit/id/' . $productId . '/6/5');
                    else
                        $this->_redirect('product/display/id/' . $productId);
                }
                else {
                    $error = $this->formatErrorMsg($formFiles->getMessages());
                    $this->_flashMessenger->addMessage(array('error' => $error));
                    // redirect to next step
                    $this->_redirect('product/edit/id/' . $productId . '/6/4');
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
                    $this->_redirect('product/edit/id/' . $productId . '/6/3');
                }

                // if more than two categories seleted, one parent and one child
                if (count($categories) > 2) {
                    $error = 'You should select only one parent/sub category category.';
                    $this->_flashMessenger->addMessage(array('error' => $error));
                    // redirect to same page
                    $this->_redirect('product/edit/id/' . $productId . '/6/3');
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

                $this->_flashMessenger->addMessage(array('info' => 'Successfully Added to Categories!.'));
                // redirect to next step
                if (empty($review))
                    $this->_redirect('product/edit/id/' . $productId . '/6/4');
                else
                    $this->_redirect('product/display/id/' . $productId);
            }
            else {
                $error = $this->formatErrorMsg($formCategory->getMessages());
                $this->_flashMessenger->addMessage(array('error' => $error));
                // redirect to next step
                $this->_redirect('product/edit/id/' . $productId . '/6/3');
            }
        }
    }

    /**
     * device selection action
     */
    public function deviceAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $requestPerms = $this->_request->getParams();
        $productId = $requestPerms['id'];
        $review = $requestPerms['review'];
        $formDevices = new Cpbo_Form_ProductDevices();
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
                $this->_flashMessenger->addMessage(array('info' => 'Successfully Saved Selected Devices!.'));
                // redirect to next step
                if (empty($review))
                    $this->_redirect('product/edit/id/' . $productId . '/6/6');
                else
                    $this->_redirect('product/display/id/' . $productId);
            }
        }
        else {
            $error = $this->formatErrorMsg($formDevices->getMessages());
            $this->_flashMessenger->addMessage(array('error' => $error));
            // redirect to next step
            $this->_redirect('product/edit/id/' . $productId . '/6/5');
        }
    }
    
    public function registrationAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $requestPerms = $this->_request->getParams();
        
        $productId = $requestPerms['id'];
        $review = $requestPerms['review'];
        // if there is no any product id redirect to create page
        if (empty($productId)) {
            $this->_flashMessenger->addMessage(array('error' => 'You have not create an Application Profile to Preview it please create it now.'));
            $this->_redirect('product/create');
        }

        $formBasic = new Model_Platform();
        $row = $formBasic->find($productId);
        if (empty($row)) {// no Basic info found redirect to create page
            $this->_flashMessenger->addMessage(array('error' => 'You have not create an Application Profile to Preview it please create it now.'));
            $this->_redirect('product/create');
        }

        $formVisuals = new Model_ProductImages();
        $rowVisuals = $formVisuals->fetchAll(
                'product_id = ' . $productId
        );
        $countVisuals = $rowVisuals->count();
        if (empty($countVisuals)) {// if no screenshots
            $this->_flashMessenger->addMessage(array('error' => 'Product visuals are not found!. Please upload atleast one screenshot and thumbnail.'));
            $this->_redirect('product/edit/id/' . $productId . '/6/2');
        }

//        $formFiles = new Model_ProductFiles();
//        $rowFiles = $formFiles->fetchAll(
//                'product_id = ' . $productId
//        );
//        $countFiles = $rowFiles->count();
//        if(empty ($countFiles)) {// if no files
//            $this->_flashMessenger->addMessage(array('error' => 'Application file is not found!, Please upload Applicatin Files!.'));
//            $this->_redirect('product/edit/id/' . $productId  . '/6/4');
//        }
//        $formDevices = new Model_ProductDevices();
//        $rowDevices = $formDevices->fetchAll(
//                'product_id = ' . $productId
//        );
//        $countDevices = $rowDevices->count();
//        if(empty ($countDevices)) {// if no devices are selected
//            $this->_flashMessenger->addMessage(array('error' => 'You must have at lease one compatible device selected in your application.'));
//            $this->_redirect('product/edit/id/' . $productId  . '/6/5');
//        }

//        $formCategories = new Model_ProductCategories();
//        $rowCategoris = $formCategories->fetchAll(
//                'product_id = ' . $productId
//        );
//        $countCategoris = $rowCategoris->count();
//        if (empty($countCategoris)) {// if no devices are selected
//            $this->_flashMessenger->addMessage(array('error' => 'Please select at least one category to continue.'));
//            $this->_redirect('product/edit/id/' . $productId . '/6/3');
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
                $this->save($form_values, $product);
                // save keys to product keys
                $productKeys = new Model_ProductKey();
                // get keys
                print_r($form_values);
//                echo $registrationModel = $form_values['registration_model'];
//                exit;

              

				if($this->_request->product_type == 'COMMERCIAL')	{
					
	                
					if($this->_request->registration_model != 'NO')	{	
						
		                if (isset($form_values['keys']) && ($registrationModel == 'POOL')) {
		                	
		                    if (!empty($form_values['keys']))	{
		                        $productKeys->delete('product_id = ' . $productId);
		                        $keys = $form_values['keys'];
		                        $keysArr = explode(',', $keys);
		                        foreach ($keysArr as $key) {
		                            $productKeys->saveData($productId, $key);
		                        }
		                    }	else	{
		                    	
		                    	$this->_flashMessenger->addMessage(array('error' => 'Please enter values for registration keys.'));
		                		// redirect to next step
		                		$this->_redirect('product/edit/id/' . $productId . '/6/3/status/error');
		                    	
		                    }
		                    
		                    
		                }
	                
		              if (isset($form_values['dynamic']) && ($registrationModel == 'DYNAMIC' || $registrationModel == 'STATIC')) {
								
		              			
		              	
								$urlValid = new Nexva_Util_Validation_UrlValidator();
			                    if ($registrationModel == 'DYNAMIC') {
			                    	
			                    	if($urlValid->isValid($form_values['dynamic']))    {
			                    	
				                        $productKeys->delete('product_id = ' . $productId);
				                        $keys = $form_values['dynamic'];
				                        $keysArr = explode(',', $keys);
				                        foreach ($keysArr as $key) {
				                            $productKeys->saveData($productId, $key);
				                        }
			                        
			                    	}  	else    {
			                    	
			                    	$this->_flashMessenger->addMessage(array('error' => 'Entered URL '.$form_values['dynamic'].', for the registration script is Invalid.'));

			                    	
			                    	// redirect to next step
			                		$this->_redirect('product/edit/id/' . $productId . '/6/3/status/error');
			                    	
			                    	}
			                    	
			                	}
	                
	                
	                    
			                 if ($registrationModel == 'STATIC') {
			                 	
			                       
			                    	
			                    	if(!empty($form_values['dynamic']))    {
			                    	
				                        $productKeys->delete('product_id = ' . $productId);
				                        $keys = $form_values['dynamic'];
				                        $keysArr = explode(',', $keys);
				                        foreach ($keysArr as $key) {
				                            $productKeys->saveData($productId, $key);
				                        }
			                        
			                    	}  	else 	{
			                    	
			                    	$this->_flashMessenger->addMessage(array('error' => 'Please enter value for registration key.'));
				                		// redirect to next step
			                		$this->_redirect('product/edit/id/' . $productId . '/6/3/status/error');
			                    	
			                    	}
			                    	
			                }
	                
	                
	              		}
	                
					} else {
						
						//$this->_flashMessenger->addMessage(array('error' => 'Please select the value for registration model.'));
			                		// redirect to next step
			            //$this->_redirect('product/edit/id/' . $productId . '/6/3/status/error');
					    $this->_flashMessenger->addMessage(array('info' => 'Successfully Saved!.'));
		                // redirect to next step
		                // if already have builds then redirect to buid mange page
		                $prodBuild = new Model_ProductBuild();
		                $builds = $prodBuild->getBuildsByProductId($productId);
		                if (isset($review) && !empty($review))
		                    $this->_redirect('product/display/id/' . $productId);
		                elseif (count($builds) > 0)
		                    $this->_redirect('build/show/id/' . $productId);
		                else
		                    $this->_redirect('build/create/productid/' . $productId);
						
						
					}
                
               }
                $this->_flashMessenger->addMessage(array('info' => 'Successfully Saved!.'));
                // redirect to next step
                // if already have builds then redirect to buid mange page
                $prodBuild = new Model_ProductBuild();
                $builds = $prodBuild->getBuildsByProductId($productId);
                if (isset($review) && !empty($review))
                    $this->_redirect('product/display/id/' . $productId);
                elseif (count($builds) > 0)
                    $this->_redirect('build/show/id/' . $productId);
                else
                    $this->_redirect('build/create/productid/' . $productId);
            }
            else {
                $error = $this->formatErrorMsg($form_registration->getMessages());
                $this->_flashMessenger->addMessage(array('error' => $error));
                // redirect to next step
                $this->_redirect('product/edit/id/' . $productId . '/6/3');
            }
        }
    }
    

    /**
     * Registration of the Product, validate all the details etc etc
     */
    public function registrationssAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $requestPerms = $this->_request->getParams();
        $productId = $requestPerms['id'];
        $review = $requestPerms['review'];
        // if there is no any product id redirect to create page
        if (empty($productId)) {
            $this->_flashMessenger->addMessage(array('error' => 'You have not create an Application Profile to Preview it please create it now.'));
            $this->_redirect('product/create');
        }

        $formBasic = new Model_Platform();
        $row = $formBasic->find($productId);
        if (empty($row)) {// no Basic info found redirect to create page
            $this->_flashMessenger->addMessage(array('error' => 'You have not create an Application Profile to Preview it please create it now.'));
            $this->_redirect('product/create');
        }

        $formVisuals = new Model_ProductImages();
        $rowVisuals = $formVisuals->fetchAll(
                'product_id = ' . $productId
        );
        $countVisuals = $rowVisuals->count();
        if (empty($countVisuals)) {// if no screenshots
            $this->_flashMessenger->addMessage(array('error' => 'Product visuals are not found!. Please upload atleast one screenshot and thumbnail.'));
            $this->_redirect('product/edit/id/' . $productId . '/6/2');
        }

//        $formFiles = new Model_ProductFiles();
//        $rowFiles = $formFiles->fetchAll(
//                'product_id = ' . $productId
//        );
//        $countFiles = $rowFiles->count();
//        if(empty ($countFiles)) {// if no files
//            $this->_flashMessenger->addMessage(array('error' => 'Application file is not found!, Please upload Applicatin Files!.'));
//            $this->_redirect('product/edit/id/' . $productId  . '/6/4');
//        }
//        $formDevices = new Model_ProductDevices();
//        $rowDevices = $formDevices->fetchAll(
//                'product_id = ' . $productId
//        );
//        $countDevices = $rowDevices->count();
//        if(empty ($countDevices)) {// if no devices are selected
//            $this->_flashMessenger->addMessage(array('error' => 'You must have at lease one compatible device selected in your application.'));
//            $this->_redirect('product/edit/id/' . $productId  . '/6/5');
//        }

        $formCategories = new Model_ProductCategories();
        $rowCategoris = $formCategories->fetchAll(
                'product_id = ' . $productId
        );
        $countCategoris = $rowCategoris->count();
        if (empty($countCategoris)) {// if no devices are selected
            $this->_flashMessenger->addMessage(array('error' => 'Please select at least one category to continue.'));
            $this->_redirect('product/edit/id/' . $productId . '/6/3');
        }

        $form_registration = new Cpbo_Form_ProductRegistration();
        // TODO Validate Product Details Exists
        // Validate product
        if ($this->_request->isPost()) {
            if ($form_registration->isValid($this->_request->getParams())) {
                $form_values = $form_registration->getValues();
                $productId = $form_values['id'];
                $registrationModel = $form_values['registration_model'];
                $product = new Model_Product();
                $this->save($form_values, $product);
                // save keys to product keys
                $productKeys = new Model_ProductKey();
                // get keys
//                print_r($form_values);
//                echo $registrationModel = $form_values['registration_model'];
//                exit;
                if (isset($form_values['keys']) && ($registrationModel == 'POOL' || $registrationModel == 'STATIC')) {
                    $productKeys->delete('product_id = ' . $productId);
                    $keys = $form_values['keys'];
                    $keysArr = explode(',', $keys);
                    foreach ($keysArr as $key) {
                        $productKeys->saveData($productId, $key);
                    }
                }
                $this->_flashMessenger->addMessage(array('info' => 'Successfully Saved!.'));
                // redirect to next step
                // if already have builds then redirect to buid mange page
                $prodBuild = new Model_ProductBuild();
                $builds = $prodBuild->getBuildsByProductId($productId);
                if (isset($review) && !empty($review))
                    $this->_redirect('product/display/id/' . $productId);
                elseif (count($builds) > 0)
                    $this->_redirect('build/show/id/' . $productId);
                else
                    $this->_redirect('build/create/productid/' . $productId);
            }
            else {
                $error = $this->formatErrorMsg($form_registration->getMessages());
                $this->_flashMessenger->addMessage(array('error' => $error));
                // redirect to next step
                $this->_redirect('product/edit/id/' . $productId . '/6/4');
            }
        }
    }

    function avgTest($fileName,$productId)
    {
        $awsKey = 'AKIAIB7MH7NAQK55BKOQ';
        $awsSecretKey = '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';
        $bucketName = 'production.applications.nexva.com';

        $s3 = new Zend_Service_Amazon_S3($awsKey, $awsSecretKey);
        $data = $s3->getObject($bucketName."/productfile/$productId/$fileName");
        file_put_contents( APPLICATION_PATH."/../files/avg/$fileName", $data);
        
//        $file = fopen("../files/avg/$fileName","w");
//        fwrite($file,$data);
//        fclose($file);
        
        /*start curl*/
        $service_url = 'http://scanner.avg-cs.appspot.com/scan';
        $curl = curl_init($service_url);
     
        /*for avg hear paramiters*/
        $objDateTime = new DateTime('NOW');
        $now = $objDateTime->format('c');
        $key="57611831397580804c22bac8-3609-11e6-ac61-9e71128cae77".$now;        
        $auth_key = sha1($key,false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/vnd.android.package-archive',
            'x-avg-client-id: 5761183139758080',
            'x-avg-time: '.$now,
            'x-avg-auth-token: '.$auth_key
            ));
        $file_name_with_full_path = APPLICATION_PATH."/../files/avg/$fileName";
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);        
        curl_setopt($curl, CURLOPT_POSTFIELDS, file_get_contents($file_name_with_full_path));
       
        $curl_response = curl_exec($curl);
        /*Remove when move to live*/
         
        /*End*/
        /*end*/
        
//        $client = new Zend_Http_Client('http://scanner.avg-cs.appspot.com/scan');
//        $client->setHeaders(array(
//            'Content-Type: application/vnd.android.package-archive',
//            'x-avg-client-id: 5761183139758080',
//            'x-avg-time: '.$now,
//            'x-avg-auth-token: '.$auth_key
//        ));
        //$client->setFileUpload('../files/upload/Nexva-mtn.apk','loadApk');die('qqqqqqqqqq');
//        $client->setFileUpload($file,'loadApk');
//        $response = $client->request(Zend_Http_Client::POST);
        $decoded = json_decode($curl_response);
        switch($decoded->result){
            case 'OK':
                $message = 'OK';
                break;
            case 'INFECTED':
                $message = 'INFECTED';
                break;
            case 'NOTTESTED':
                $message = 'NOTTESTED';
                break;
            case 'NOT_AUTHORIZED':
                $message = 'NOT_AUTHORIZED';
                break;
        }
        unlink("../files/avg/$fileName");

        return $message;
    }

    /**
     * Display Overview
     *
     */
    public function displayAction() {
        $requestPerms = $this->_request->getParams();
        $productId = $requestPerms['id'];
        $this->view->productid = $productId;
        $sessionUser = new Zend_Session_Namespace('test');

        $this->view->lastRequestUrl = $sessionUser->link;
        $product = new Model_Product();
        $this->view->fileExists = $product->checkFilesinS3($productId);

        $productBuildModel = new Admin_Model_ProductBuild();
        // if the form is submitted
        if ($this->_request->isPost()) {
            $userMessages = new Model_UserMessage();
            $productId = (empty($productId)) ? $requestPerms['product_id'] : $productId;
            $submit = $requestPerms['submit'];
            $apkFiles = $requestPerms['apk_files'];
            
            switch ($submit) {
                case 'Approve':
                    $status = 'APPROVED';
                    $subject = 'Your content has been approved';
                    $mailTemplate = 'approve.phtml';
                    $body = $subject;
                    break;
                case 'Reject':
                    $status = 'REJECTED';
                    //$subject = 'Your content has been rejected';
                    //$mailTemplate = 'reject.phtml';
                    break;
                case 'Disable':
                    $status = 'UNDER_REVIEW';
                    //$subject = 'Your content has been disabled';
                    //$mailTemplate = 'disable.phtml';
                    break;
                case 'Send':
                    $subject = 'About your content ';
                    $mailTemplate = 'cp_mail.phtml';
                    break;
                case 'Featured':
                    $formValues = array('id' => $productId, 'is_featured' => 1);
                      
                    $update = $this->save($formValues, $product);
                    $this->_flashMessenger->addMessage(array('info' => 'Successfully added to featured product'));
                    $this->_redirect($sessionUser->link);
                    break;
                case 'AVG Approve':
                    if($apkFiles)
                    {
                        foreach($apkFiles as $apkFile)
                        {
                            //here we explode the namearray coming from the form and after the explotion the first element is buid's file name and second element is build id
                            $fileName = explode("_build_id_",$apkFile);
                            $response = $this->avgTest($fileName[0],$productId);
                            if($response == 'OK') { $productBuildModel->updateAvgStatus($fileName[1]); }
                            $this->_flashMessenger->addMessage(array('info' => 'Response from the AVG Scan - '.$response));
                        }
                        $this->_redirect('product/display/id/' . $productId);
                    }
                    break;
            }
            $formValues = array('id' => $productId, 'status' => $status);

            if ($submit != 'Send') {
                $update = $this->save($formValues, $product);
            }
            // add message and redirest to the next step
            if ($submit == 'Approve' or $submit == 'Send') {
                // send the Approve/Reject email
                // get product details
                $product = new Model_Product();
                $productDetail = $product->getProductDetailsById($productId); //Zend_Debug::dump($productDetail);die();
                // get user details
                $userModel = new Model_User();
                $userId = $productDetail['uid'];
                $user = $userModel->getUserDetailsById($userId);
                $userMeta = new Model_UserMeta();
                $userMeta->setEntityId($userId);
                $body = $requestPerms['mail_to_cp'];
                // @TODO : add body message with the Approve and reject with submit\

                $fisrtName = $userMeta->getAttributeValue($userId, 'FIRST_NAME');
                $lastName = $userMeta->getAttributeValue($userId, 'LAST_NAME');


                if (empty($fisrtName)) {
                    $name = $user->email;
                } else {
                    $name = $fisrtName . ' ' . $lastName;
                }

                $productName = preg_replace("/[^A-Z0-9\ \']/i", "", $productDetail['name']);

                $userMessages->sendMessageProductUpdates($productId, $userId, "$subject (" . $productName . ")", $body);

                $mailer = new Nexva_Util_Mailer_Mailer();
                $mailer->setSubject("$subject (" . $productName . ")");
                $mailer->addTo($user->email, $user->username)
                    ->setLayout("generic_mail_template")        
                    ->setMailVar("email", $user->email)
                    ->setMailVar("name", $name)
                    ->setMailVar("status", $status)
                    ->setMailVar("body", $body)
                    ->setMailVar("product", $productDetail)
                    ->sendHTMLMail($mailTemplate)
                    ->send();


                if ($submit != 'Send'){
                    /* Remove giveeway
                    if($status=='APPROVED'){
                        $mailerForGiveaway = new Nexva_Util_Mailer_Mailer();
                        $mailerForGiveaway->setSubject("neXva Samsung S8 giveaway confirmation!.");//
                        $mailerForGiveaway->addTo($user->email, $user->username)
                            ->setLayout("giveway_mail_template")        
                            ->setMailVar("email", $user->email)
                            ->setMailVar("app_name", $productName)
                            ->setMailVar("name", $name)
                            ->sendHTMLMail('giveway.phtml')
                            ->send();
                    }*/
                    
                     $this->_flashMessenger->addMessage(array('info' => 'Successfully update product status to ' . $status));
                    
                }else{
                    $this->_flashMessenger->addMessage(array('info' => 'You message to the CP sent successfully'));
                }
                
                if ($submit == 'Approve')
                    $this->_redirect($sessionUser->link);
                else
                    $this->_redirect('product/display/id/' . $productId);
            } else {


                $this->_flashMessenger->addMessage(array('info' => 'Successfully update product status to ' . $status));

                $this->_redirect('product/display/id/' . $productId);
            }
        }
        // get Basic Info
        $product = new Model_Product();

        $platforms = $product->getSupportedPlatforms($productId);


        $basicInfo = $this->populateBasicForm($productId, $product); //Zend_Debug::dump($basicInfo);die();

        if ($platforms)
            $basicInfo['platform_id'] = $platforms;

        $userMeta = new Model_UserMeta();
        $userMeta->setEntityId($basicInfo['user_id']);

        $basicInfo['company_name'] = $userMeta->COMPANY_NAME;
        // un set unwanted data
        unset($basicInfo['category_id']);
        //unset($basicInfo['user_id']);
        unset($basicInfo['deleted']);
//        unset($basicInfo['status']);
        unset($basicInfo['id']);

        $this->view->basicinfo = $basicInfo;
        // Categories
        $productCategories = new Model_ProductCategories();
        $categories = $this->getCategories();
        $this->view->categories = $categories;
        $this->view->categorySelected = $this->populateFormData($productId, $productCategories, 'category_id');
        // Visuals
        $productVisuals = new Model_ProductImages();
        $this->view->visuals = $this->populateFormData($productId, $productVisuals, 'filename');
        // Builds
        $builds = array();
        $product = new Model_Product();
        $builds = $product->getBuilds($productId);
        $this->view->builds = $builds;
        $prodBuild = new Model_ProductBuild();
        $buildFiles = array();
        $buildDevices = array();
        foreach ($builds as $value) {
            $buildFiles[$value->id] = $prodBuild->getFiles($value->id);
            $buildDevices[$value->id] = $prodBuild->getSelectedDevices($value->id);
        }
//        print_r($buildDevices);
//        exit;
        $this->view->files = $buildFiles;
        $this->view->devices = $buildDevices;

        $productBuildModel = new Admin_Model_ProductBuild();
        $avgApproved = $productBuildModel->getAvgApproved($productId);
        //echo count($avgApproved);die();
        $this->view->avgApproved = $avgApproved;

        $userMessages = new Model_UserMessage();
        $messages = $userMessages->getAllUserProductMessage($productId);

        $paginator = Zend_Paginator::factory($messages);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));


        if (count($paginator) == 0) {
            $this->view->isCpMessageListEmpty = true;
        }

        $this->view->paginator = $paginator;
        
        $productLanguageMeta = new Model_ProductLanguageMeta();
        $transtaltions = $productLanguageMeta->getAllTranslations($productId);
        
                	
  
        
     	$productLanguageSummery = array();
        $productLanguageDescription = array();
        $productLanguageTitle = array();
        foreach ($transtaltions as $value) {
        	
        	$translationsInfo = $productLanguageMeta->loadTranslation($productId, $value->language_id);

        	
            $productLanguageSummery[$value->id] = $translationsInfo->PRODUCT_SUMMARY ;
            $productLanguageDescription[$value->id] =  $translationsInfo->PRODUCT_DESCRIPTION;
            $productLanguageTitle[$value->id] =  $translationsInfo->PRODUCT_NAME;
        }
        

        

        
        $this->view->transtaltions =  $transtaltions;
        $this->view->productLanguageSummery = $productLanguageSummery;
        $this->view->productLanguageDescription = $productLanguageDescription;
        $this->view->productLanguageTitle = $productLanguageTitle;
        /*Get product chaps*/	
        $chapProductoBJ=new Admin_Model_ChapProducts();
        $this->view->chapProducts=$chapProductoBJ->getAllChapsByProduct($productId);
        /*End*/
    }
    
     public function showTranslationAction()	{
     	
     	        
        $this->_helper->layout ()->disableLayout ();
        $this->_helper->viewRenderer->setNoRender ( true );
		
        if($this->_request->id and $this->_request->language_id)	{
        	$productLanguageMeta = new Model_ProductLanguageMeta();
        	$translationsInfo = $productLanguageMeta->loadTranslation($this->_request->id, $this->_request->language_id);

        	if($this->_request->summary)
        		echo $translationsInfo->PRODUCT_SUMMARY;
        		
        	
        	if($this->_request->description)
        		echo $translationsInfo->PRODUCT_DESCRIPTION;
        
        }
        
     }

    function toArray($data) {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        return is_array($data) ? array_map(__FUNCTION__, $data) : $data;
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
                    $this->_flashMessenger->addMessage(array('info' => 'File <i>' . $fileName . '</i> Saved on S3!.'));
                else
                    $this->_flashMessenger->addMessage(array('error' => 'S3 file <i>' . $fileName . '</i> upload failled!.'));
            }
        } catch (Exception $ex) {
            // TODO : set as messages
            $this->_flashMessenger->addMessage(array('error' => 'S3 file upload failled!.'));
//            echo $ex->getMessage();
        }
    }

    /**
     * Save Product Data.
     * @param <type> $form_values
     * @param <type> $model
     */
    public function save(&$form_values, $model,  $type='web') {
        // TODO : Get user id from Session and save it
        $data = array();
        $last_insert_id = null;
        $table_info = $model->info();
        $columns = $table_info['cols'];

        foreach ($columns as $key => $value) {
            if (0 !== strlen($form_values[$value]))
                $data[$value] = $form_values[$value];
            unset($form_values[$value]);
        }
        
            
    	$filter = new Nexva_Util_Filter_ProductName();
    	
    	if($data['name'])
    		$data ['name'] = $filter->filter($data['name']);
        
//        print_r($data);
//        exit;
        if (null === ($id = $data['id'])) {
            unset($data['id']);
//      $data['created_date'] = new Zend_Db_Expr('NOW()');
            $last_insert_id = $model->insert($data);
            $form_values['product_id'] = $last_insert_id;
        } else {
            unset($data['created_date']);
            $last_insert_id = $model->update($data, array('id = ?' => $id));
            $form_values['product_id'] = $id;
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
        $categories = new Model_Category();
        return $categories->getCategorylist();
    }

    // populate from product table
    private function populateBasicForm($id, $table) {
        $rowset = $table->find($id);
        $row = $rowset->current();

        if (is_null($row))
            die('Product not found');
        $product = $row->toArray();
        // now get data from product meta table.
        $productMeta = array();
        $productMetaTable = new Model_ProductMeta();
        $productMetaTable->setEntityId($id); //a valid user id obtained from
        $productMeta['product_version'] = $productMetaTable->product_version;
        $productMeta['brief_description'] = strip_tags($productMetaTable->brief_description,'<a></a>');
        $productMeta['full_description'] = strip_tags($productMetaTable->full_description, '<br /><br><a></a>');
        $productMeta['notify_email'] = $productMetaTable->notify_email;
        $productMeta['desktop_product'] = $productMetaTable->desktop_product;

        return array_merge($product, $productMeta);
    }

    // populate submitted from other tables
    private function populateFormData($id, $table, $column) {
        $entries = '';
        $resultSet = $table->fetchAll(
                    $table->select()
                    ->where('product_id = ?', $id)
        );
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

    protected function trimAndElipsis($string, $count, $ellipsis = FALSE) {
        if (strlen($string) > $count) {
            $length -= strlen($ellipsis);  // $length =  $length – strlen($end);
            $string = substr($string, 0, $count);
            $string .= $ellipsis;  //  $string =  $string . $end;
        }
        return $string;
    }

    function listfeaturedAction() {

        $productModel = new Model_Product();
        $featuredProducts = $productModel->select()
                ->where('is_featured = ?', '1')
                ->order('name');
        $this->view->featuredProducts = $productModel->fetchAll($featuredProducts);
    }

    function deleteAction() {

        $product = new Model_Product();
        $reutrn = $product->update(
                array(
                  "deleted" => 1, "deleted_date" => date('Y-m-d')
                ),
                array(
                  "id = ?" => $this->_request->id
                )
        );
        $this->_flashMessenger->addMessage("Product deleted successfully");

        if ($this->_request->tab) {
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'product/view/tab/' . $this->_request->tab .
                '/page/' . $this->_request->page .
                '/search' . $this->_request->page .
                '/searchin' . $this->_request->searchin
            );
        } else {
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'product/view');
        }
        
    }

    /**
     *
     */
    public function filedeleteAction() {
        $fileId = $this->_getParam('id');
        $appId = $this->_getParam('prod');

        // check validations
//        $product = new Model_Product();
//        $formBasic = $this->populateBasicForm($appId, $product);
//        $userId = Zend_Auth::getInstance()->getIdentity()->id;
//        if($formBasic['user_id'] != $userId)
//            $this->_redirect("/product/edit/id/$appId/6/4");
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
            $this->_flashMessenger->addMessage(array('info' => "File deleted successfully"));
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
//        $product = new Model_Product();
//        $formBasic = $this->populateBasicForm($appId, $product);
//        $userId = Zend_Auth::getInstance()->getIdentity()->id;
//        if($formBasic['user_id'] != $userId)
//            $this->_redirect("/product/edit/id/$appId/6/2");

        $productFiles = new Model_ProductImages();
        $productFiles->delete('id = ' . $fileId . ' and product_id = ' . $appId);
        $this->_flashMessenger->addMessage(array('info' => "File deleted successfully"));
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
            $update = $this->save($formValues, $product);
            // add message and redirest to the next step
            if ($update) {
                $this->_flashMessenger->addMessage('Your product ' . $formBasic['name'] . ' is waiting to approve!.');
                $this->_redirect('product/view');
            } else {
                $this->_flashMessenger->addMessage(array('error' => 'Error occured while saving data, please re-try!.'));
                $this->_redirect('product/view');
            }
        }
    }
	
	public function selectAction() {
		
		$this->view->title = "Select Porduct Type";
		
		if ($this->_request->select == 'inapp')
			$this->_redirect ( '/product/create-inapp' );
		elseif ($this->_request->select == 'web')
			$this->_redirect ( '/product/create' );
	
	}
	
	public function createInappAction() {
		$this->view->title = "Create Inapp product";
		
		$form_basic_inapp = new Admin_Form_ProductBasicInappInfo ( );
		// Basic Form
		$form_basic_inapp->setAction ( CP_PROJECT_BASEPATH . '/product/basic-inapp' );
		$this->view->form_basic_inapp = $form_basic_inapp;
		
		$platforms = $this->getPlatforms ();
		$this->_flashMessenger->addMessage ( array ('error' => 'message' ) );
	
	}
	
public function basicInappAction() {
    // TODO : add validation on when no product ID
    $this->_helper->viewRenderer->setNoRender(true);
    $requestPerms = $this->_request->getParams();
    $productId = $requestPerms['id'];

    if ($this->_request->isPost()) {
      $form_values = $this->_request->getParams();

      $product = new Model_Product();
      $form_values['user_id'] = Zend_Auth::getInstance()->getIdentity()->id;
      
                $lastInsertId = $this->save( $form_values, $product, 'inapp' );
                // if the form is updating then set it to the hidden id
                $productId = $form_values ['product_id'];
                
                // product create time
                if (! isset ( $productId ) && empty ( $productId ))
                    $form_values ['created_date'] = new Zend_Db_Expr ( 'NOW()' );
                    // product last update date
                $form_values ['product_changed'] = time ();
                $this->saveMeta ( $form_values );
                $this->_flashMessenger->addMessage ( array ('info' => 'Successfully saved.' ) );
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
        $this->_redirect('product/create');
      else
        $this->_redirect('product/edit/id/' . $productId . '/6/1');
    }
  }
	
	public function publishnowAction() {
		
		$config = Zend_Registry::get ( 'config' );
		$key = $config->nexva->application->salt;
		
		$this->view->uniqId = md5 ( $key . $this->_request->id );
		$this->view->productId = $this->_request->id;
	
	}
	
	public function editinappAction() {
		
		$productId = $this->_getParam ( 'id' );
		
		$review = $this->_getParam ( 'review' );
		// basic info
		$form_basic = new Admin_Form_ProductBasicInappInfo ( );
		$product = new Model_Product ( );
		$formBasicInfo = $this->populateBasicForm ( $productId, $product );
		$form_basic->populate ( $formBasicInfo );
		
		$this->view->productId = $productId;
		
		$form_basic->setAction ( CP_PROJECT_BASEPATH. '/product/basic-inapp' );
		$this->view->form_basic = $form_basic;
		
		$this->view->form_basic->review->setValue ( $review );
		
		$userpayouts = new Model_User ( );
		$userpayoutsType = $userpayouts->getRoyalty ();
		$this->view->form_basic->status->setValue ( 'INCOMPLETE' );
		$this->view->form_basic->payout->setValue ( $userpayoutsType->payout_cp );
		
	
	}

    public function downloadAction() {

        $buildId = $this->_request->build;
        $productId = $this->_request->product;
        
        set_time_limit(0);
        
        $tempDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid();
        mkdir($tempDirectory, 0755, true);

        $config = Zend_Registry::get('config');
        $s3 = new Zend_Service_Amazon_S3($config->aws->s3->publickey, $config->aws->s3->secretkey);
        $s3->registerStreamWrapper("s3");

        $buildFiles = new Model_ProductBuildFile();

        $buildFiles = $buildFiles->getFilesByBuid($buildId);
        $productModel = new Model_Product();
        $buildModel = new Model_ProductBuild();

        $product = $productModel->getProductDetailsById($productId);
        //Zend_Debug::dump($buildFiles);die();
        //echo count($buildFiles);die();
        foreach($buildFiles as $buildFile) {

            if( APPLICATION_ENV == 'production') //why? because as the time of writing this code, we dont have a perfectly synced prod-to-stage/dev env setup and therefore this is for local testing only
                $filename = "s3://".$config->aws->s3->bucketname."/productfile/".$productId."/".$buildFile->filename;
            else
                $filename = "s3://".$config->aws->s3->bucketname."/productfile/".$productId."/".$buildFile->filename;

            if( !@copy($filename, $tempDirectory."/". basename($filename)) ) throw new Zend_Exception("Unable to get file: $filename from S3. Quitting..." );

        }

        $slug =  new Nexva_View_Helper_Slug();

        $zipFilename = $tempDirectory. DIRECTORY_SEPARATOR. $slug->slug($product['name']."-".$buildModel->getBuildName($buildId)).".zip";

        $archive = new Nexva_Util_FileFormat_Zip_PclZip($zipFilename);
        $archive->add($tempDirectory."/",
                          PCLZIP_OPT_REMOVE_PATH, $tempDirectory."/");


        $fp = fopen($zipFilename, 'rb');

        header("Content-Type: application/zip");
        header("Content-Length: " . filesize($zipFilename));
        header("Content-Disposition: inline; filename=".basename($zipFilename));
        header("Content-Transfer-Encoding: binary");
        fpassthru($fp);
        fclose($fp);
        die();

    }
	
   public function selecteditAction() {
        
        $this->view->title = "Edit Porduct Type web/Inapp";
        
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
  
  
    private function ecrypt($str) {
        $key = "123";
        $result = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $char = substr($str, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result.=$char;
        }
        return base64_encode($result);
    }

    private function decrypt($str) {
        $str = base64_decode($str);
        $result = '';
        $key = "123";
        for ($i = 0; $i < strlen($str); $i++) {
            $char = substr($str, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result.=$char;
        }
        return $result;
    }

	private function __findFileExtension($filename) {
        $filename = strtolower($filename) ;
        $exts = split("[/\\.]", $filename) ;
        $n = count($exts)-1;
        $exts = $exts[$n];
        return $exts;
    }
    /*added by sujith*/
    public function addChanelPartnerAction() {
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        
        $user = new Model_User();        
        $chaps=$user->getCHAPs();
        
        $appPayment = $this->_request->getParam('app_payment');
        $productId = $this->_request->getParam('pid');
        
        
        $themeMeta = new Model_ThemeMeta();
        $chapProduct=new Pbo_Model_ChapProducts();
        
        /*alresy exsits*/
        $alreadyExists=false;
        /**/
        
        /*insert new row*/
        $isInsert=false;
        /**/
        $webUrl=array();
        /*This is hard corded*/
        $config = Zend_Registry::get('config');
        $orangeChapIds=  explode(",",$config->nexva->noadvertitment->chaps);
        if($appPayment =='NO ADVERTISMENT' ) {  
            foreach ($orangeChapIds as $orangeChapId){
                $themeMeta->setEntityId($orangeChapId);
                if(!$chapProduct->checkProductsadded($orangeChapId,$productId )){
                    $chapProduct->addProductToChapToApproved($orangeChapId, $productId,1);
                    $isInsert=true;
                    $whitelableUrlWeb=str_replace(" ","",$themeMeta->WHITELABLE_URL_WEB);
                    if(!empty($whitelableUrlWeb)){
                        array_push($webUrl,$whitelableUrlWeb);
                    }
                } else{
                    $alreadyExists=true;
                }
            }
            /*end*/
        }else{
       
            foreach($chaps as $chap) {
                if(!$chapProduct->checkProductsadded($chap->user_id,$productId )){
                    $themeMeta->setEntityId($chap->user_id);                    
                    if($themeMeta->WHITELABLE_APP_FILTER ==$appPayment){ 
                            $chapProduct->addProductToChapToApproved($chap->user_id, $productId,1);
                            $isInsert=true;
                            $whitelableUrlWeb=str_replace(" ","",$themeMeta->WHITELABLE_URL_WEB);
                            if(!empty($whitelableUrlWeb)){
                                array_push($webUrl,$whitelableUrlWeb);
                            } 
                    }
                }else{
                    $alreadyExists=true;
                }
            } 
        }
        if($alreadyExists && ($isInsert==false)){
            echo json_encode($webUrl);
        }else{            
            echo json_encode($webUrl); 
        }
        die();
    }
    
    public function addToStoreAction(){
        /*Get Primium Chaps*/ 
            $premiumChaps = array();
            
            $productId=$this->_request->getParam('pid');
            
            $chapProduct=new Pbo_Model_ChapProducts();
            /*Delete exsisting stores*/
            $chapDetals=new Admin_Model_ChapProducts();
            $chaps=$chapDetals->getAllChapsByProduct($productId);
            foreach ($chaps as $chap){
                $chapProduct->deleteProductChap(trim($chap), trim($productId));
            }
            /*End*/
            parse_str($this->_request->getParam('premium'), $premiumChaps);
            if(isset($premiumChaps['premium'])){
                foreach ($premiumChaps['premium'] as $premiumChaps){                  
                    
                    
                    if(!$chapProduct->checkProductsadded($premiumChaps,$productId )){
                        $chapProduct->addProductToChapToApproved($premiumChaps, $productId,1);

                    }
                }
            }
        /*Get Free Chaps*/
            $freeChaps = array();
            parse_str($this->_request->getParam('free'), $freeChaps);
            if(isset($freeChaps['free'])){
                 foreach ($freeChaps['free'] as $freeChaps){
                    if(!$chapProduct->checkProductsadded($freeChaps,$productId )){
                        $chapProduct->addProductToChapToApproved($freeChaps, $productId,1);

                    }
                }
            }
        /*Get Free without ads Chaps*/   
            $fwaChaps = array();
            parse_str($this->_request->getParam('free_without_ads'), $fwaChaps);
            if(isset($fwaChaps['free_without_ads'])){
                 foreach ($fwaChaps['free_without_ads'] as $fwaChaps){
                    if(!$chapProduct->checkProductsadded($fwaChaps,$productId )){
                        $chapProduct->addProductToChapToApproved($fwaChaps, $productId,1);

                    }
                }
            }
        /*Get education ads Chaps*/   
            $educationChaps = array();
            parse_str($this->_request->getParam('educational_apps'), $educationChaps);            
            if(isset($educationChaps['educational_apps'])){
                 foreach ($educationChaps['educational_apps'] as $educationChaps){
                    if(!$chapProduct->checkProductsadded($educationChaps,$productId )){                       
                        $chapProduct->addProductToChapToApproved($educationChaps, $productId,1);

                    }
                }
            }
        die();
    }
}

?>