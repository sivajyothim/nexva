<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MasterController
 *
 * @author Administrator
 */
class Nexva_Controller_Action_Cp_MasterBuildController extends Nexva_Controller_Action_Cp_MasterController {

    public function preDispatch() {
        if( $this->_request->getActionName() == "save") return ;
        
        if (!Zend_Auth::getInstance()->hasIdentity()) {

            if ($this->_request->getActionName() != "login" ) {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();
            }
            if ($this->_request->getActionName() != "login")
                $this->_redirect('/user/login');
        }
    }

    public function init() {
        // include Ketchup libs
        $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css');

        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js');
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        if(!in_array($chapId, array('585474','585480'))){
            $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages.js');
        }else{
            $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages_fr.js');
        }
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js');

        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/js/admin.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/cp/assets/js/cp.js');

        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/lazy-loader/jquery.lazyload.min.js');

        // Flash Messanger
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->flashMessenger = $this->_flashMessenger;
        // add uploadify libs
        $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/js/jquery/plugins/uploadify/uploadify.css');

        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/uploadify/swfobject.js');
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/uploadify/jquery.uploadify.v2.1.4.min.js');
    }

    /**
     *  Creare builds
     */
    public function createAction() {
        $productId = $this->_request->productid;
        $buildId = $this->_request->build;
        // get product details
        $product = new Model_Product();
        $productDetails = $product->getProductDetailsById($productId);
        $this->view->isUrlProduct = ($productDetails['content_type'] == 'URL') ? true : false;
        // Devices
        $form_devices = new Cpbo_Form_ProductDevices();
        $form_devices->setAction($this->getFrontController()->getBaseUrl() . '/build/device');
        $this->view->form_devices = $form_devices;
        // files form object
        $formFilesObj = new Cpbo_Form_ProductFiles();
        // create the file form
        // Ajax upload helper libs
        // $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ajax-upload/ajaxupload.js');
        
        $formFiles = $formFilesObj->createFileForm();
        $this->view->fileDesc = '';

        // create the URL form
        $formUrl = $formFilesObj->createUrlForm();
        $this->view->urlDesc = 'Please enter URLs separated by enter';
        $this->view->fileDesc = 'Please upload files';

        // files form
        $this->view->formFiles = $formFiles;
        // Url form
        $this->view->formUrl = $formUrl;
        $this->view->formFiles->id->setValue($productId);
        $this->view->formFiles->build_id->setValue($buildId);
        $this->view->builbname = $productDetails['name'] . ' Default Build';

        // get the platforms
        $platformTable = new Model_Platform();
        $this->view->platforms = $platformTable->getAllPlatforms();
        
        $languageTable = new Model_Language();
        $this->view->language = $languageTable->getLanguageList(1);
        $this->view->selectedPlatform = -1;
        
        
        

        // preload data
        if (isset($buildId)) {
            //set the build id to the view
            $this->view->build_id = $buildId;
            // set the product id
            $this->view->product_id = $productId;
            // get build files
            $buildFiles = new Model_ProductBuildFile();
            $this->view->files = $buildFiles->getFilesByBuid($buildId);
            //get build name
            $prodBuild = new Model_ProductBuild();
            $productBuildDetails    = $prodBuild->getBuildDetails($buildId);  
            if (!$productBuildDetails) {
                if ($productId) {
                    $this->_redirect('/build/show/id/' . $productId);
                } else {
                    $this->_redirect('/');
                }
            }
            
            $this->view->device_update_service = $productBuildDetails->device_update_service;
            $this->view->builbname = $prodBuild->getBuildName($buildId);
            // platform
            $this->view->selectedPlatform = $prodBuild->getBuildPlatform($buildId);
            // language
            $this->view->selectedLanguage = $prodBuild->getBuildLanguage($buildId);
            // get type of option selected
            $this->view->selected = $prodBuild->getBuildDeviceSelectionMethod($buildId);
            // get the build type
            $this->view->buildType = $prodBuild->getBuildContentType($buildId);
            // get attributes
            $productDevAttrib = new Model_productDeviceAttributes();
            $this->view->deviceAttributes = $productDevAttrib->getSelectedDeviceAttributesById($buildId);
            // get device update service
            
            
            $buildSupportedVersion = new Admin_Model_BuildSupportedVersions();
       		$this->view->buildSupportedVersions = $buildSupportedVersion->getBuildSupportedVersion($buildId);
                
             
             $tagModel  = new Model_ProductBuildTag();
             
             //get available tags 
             $avilableTags = $tagModel->getAvailableTags();
             $this->view->availableTags = $avilableTags;     
             
             //get selected tags for the product
             $selectedTagsPrefixed = $tagModel->getBuildTagsByBuildId($buildId);
             
             //$selectedTags = array();
             $selectedTags=null;
             foreach ($selectedTagsPrefixed as $buildTag) 
             {
                    $selectedTags = $tagModel->unPadTag($buildTag->tags);
             }  
             
             $selectedTagsDetails = explode(',', $selectedTags) ;
             $this->view->selectedTags = $selectedTagsDetails;       		
        
        }
    }

    /**
     * Save builds
     */
    public function saveAction() {
        

        $this->_helper->viewRenderer->setNoRender(true);
        $requestPerms   = $this->_request->getParams();
        $build_type     = $requestPerms['navigate_file_url'];
        $productId      = $requestPerms['id'];
        $id             = null;

        $changed = false;
        
        // get product details
        $product        = new Model_Product();
        $buildName      = str_replace(' ', '_', trim($requestPerms['build_name']));
        $formFilesObj   = new Cpbo_Form_ProductFiles();
        $formFiles = $formFilesObj->createFileForm();     

        
        if ($this->_request->isPost()) {
            // check produt has builds
            $numberofBuilds = $product->getBuilds($productId);
            if ($numberofBuilds == 0) {
                $product->setStatus('NOT_APPROVED', $productId);
            }
            $product->setUpdateDate($productId);
            // Build files
            $productBuildFiles = new Model_ProductBuildFile();
            // add build name to database
            $productBuild = new Model_ProductBuild();
            $data = array(
              'id' => !empty($requestPerms['build_id']) ? $requestPerms['build_id'] : null,
              'product_id' => $productId,
              'name' => $buildName,
              'build_type' => $build_type,
              'platform_id' => $requestPerms['device_platform'],
              'device_update_service' => $requestPerms['future_dev'],
              'language_id' => $requestPerms['build_language'],
              'device_selection_type' => $requestPerms['radio']
            );

            //get build details
            $buildDetails = $productBuild->getBuildDetails($requestPerms['build_id']);

            if($buildDetails != false){
                $val = array_diff_assoc($data,$buildDetails->toArray());
            }

            if($val){
                $changed = true;
            }
            unset($data['device_selection_type']);

            $newBuildId = $productBuild->save($data);
            // redirect to upload files and devices
            if (empty($requestPerms['build_id'])) {
                $this->_redirect('build/create/productid/' . $productId . '/build/' . $newBuildId);
            }
            // edit and insert new id'd
            $buildId = (!empty($requestPerms['build_id'])) ? $requestPerms['build_id'] : $newBuildId;
            
            if ($formFiles->isValid($this->_request->getParams()) && $build_type != 'urls') {
                $formValues = $formFiles->getValues();
            } elseif ($build_type == 'urls') {

                // create the URL FORM and use it
                $formFiles  = $formFilesObj->createUrlForm();
                $urls       = $requestPerms['url'];
                $urlsValid  = false;

                if (isset($urls) && !empty($urls)) {
                    $formValues = $formFiles->getValues();
                    $urls = explode("\n", $urls);
                    foreach ($urls as $url) {
                        if (filter_var($url, FILTER_VALIDATE_URL)) {
                            $urlsValid      = true;
                            $buildFileVal   = array('id' => $id, 'build_id' => $buildId, 'filename' => $url);
                            $productBuildFiles->save($buildFileVal);    
                        }
                    }
                    if ($urlsValid) {
                        $this->_flashMessenger->addMessage(array('info' => 'Successfully added the URL field'));
                    }
                }
            }




            // check the file entried are there if not rollback
            $files = $productBuildFiles->getFilesByBuid($buildId);
            
            
            
/*             if ($_SERVER['REMOTE_ADDR'] == '113.59.222.40'){
                Zend_Debug::dump($buildId);
                Zend_Debug::dump($newBuildId);
                Zend_Debug::dump($requestPerms['build_id']);
                Zend_Debug::dump($files->count());
            	Zend_Debug::dump($buildDetails);
            	Zend_Debug::dump($requestPerms);die();
            } */
            
            if ($files->count() == 0) {
                $this->_flashMessenger->addMessage(array('error' => 'Please add content to continue'));
                $this->_redirect("/build/create/productid/$productId/build/$buildId");
            }

            // Save devices
            $formDevices = new Cpbo_Form_ProductDevices();
            if ($formDevices->isValid($this->_request->getParams())) {
                $formValues = $formDevices->getValues();
                //$productId = $formValues['id'];
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
                $attributesArray[1] = !empty($this->_request->device_os) ? $this->_request->device_os : '';

                //This is only for BB 10
                if(!empty($this->_request->device_os) && !is_null($this->_request->device_os) && $this->_request->device_os == 'BB OS')
                {
                    $attributesArray[3] = '10.0';
                    $attributesArray[1] = 'RIM OS';
                }

                //add Device tags
                $deviceTags = $this->_request->getparam('tags');                
                
                $tagModel  = new Model_ProductBuildTag();
              
                //deleting existing tags for the app
                $tagModel->delete('product_build_id = ' . $buildId);
                
                $tagArray = array();
                
                // add prefix so that the minimum index limit is met in mysql
                foreach ($deviceTags as $tags) 
                {                       
                     if (trim($tags)) 
                     {
                          $tagArray[] = 'NXTAG_'. trim($tags);                          
                     }
                }
                
                $tagPrefixed = implode(',',$tagArray);                 
               
                //save tags
                $data   = array();
                $data['product_build_id']   = $buildId;
                $data['tags']               = $tagPrefixed;
                $tagModel->insert($data);

                // Save Attributes to the database
                $productDevAttrib = new Model_productDeviceAttributes();

                //check whether data has changed
                $attributeDetails = $productDevAttrib->getSelectedDeviceAttributesById($buildId);
                $val = array_diff_assoc($attributesArray,$attributeDetails);

                if($val){
                    $changed = true;
                }
                //

                // delete all existing attributes
                $productDevAttrib->delete('build_id = ' . $buildId);
                $attribString = '';
                
                foreach ($attributesArray as $id => $value) {
                    if (empty($value) || $value == 'any')
                        continue;
                    $devAttrib = array('build_id' => $buildId, 'device_attribute_definition_id' => $id, 'value' => $value);
                    $productDevAttrib->save($devAttrib);
                    
                    // add update 
                 $buildSupportedVersions = new Admin_Model_BuildSupportedVersions();
                    if($this->_request->minimum_version){
                    	$buildSupportedVersionsAttributes['build_id'] = $buildId;
                    	$buildSupportedVersionsAttributes['min_version'] = $this->_request->minimum_version_value;
                    	$buildSupportedVersionsAttributes['or_better'] =$this->_request->or_better;
                    	$buildSupportedVersionsAttributes['id'] = $this->_request->build_supported_versions_id; 
		               	$buildSupportedVersions->save($buildSupportedVersionsAttributes);

                    } else {
                    	if($this->_request->build_supported_versions_id)	{
                    		$buildSupportedVersionsAttributes['min_version'] = 0;
                    		$buildSupportedVersionsAttributes['id'] = $this->_request->build_supported_versions_id; 
		            		$buildSupportedVersions->save($buildSupportedVersionsAttributes);
                    	}
                    }

                    // contruct device attrib string
                    $attributeDefinitions = array(
                      4 => 'mp3_playback',
                      5 => 'java_midp_1',
                      6 => 'java_midp_2',
                      7 => 'width',
                      8 => 'height',
                      2 => 'navigation_method',
                      1 => 'device_os'
                    );
                    if ($id == 2 || $id == 7 || $id == 8 || $id == 1)
                        $attribString .= "$attributeDefinitions[$id]=$value&";
                    else
                        $attribString .= $attributeDefinitions[$id] . '&';
                }

                // save on product Table
                $formValues = array('id' => $buildId, 'device_selection_type' => $selected);
                $productBuild->save($formValues);

                // insert to product devices table
                $buildDevices = new Model_ProductBuildDevices();
                // get the deivices already in database
                // get devices if those are not there
                if (empty($devices)) {
                    $devicesTable = new Model_Device();
                    switch ($selected) {
                        case 'ALL_DEVICES':
                            $devices = $devicesTable->getAllDevies();
                            break;
                        case 'BY_ATTRIBUTE':
                            $attrib = substr($attribString, 0, -1);
                            $devices = array();
                            break;
                    }
                }

                //delete all devices and start adding new
                if (!empty($devices) && ($selected == 'BY_ATTRIBUTE' || $selected == 'ALL_DEVICES')) {
                    $buildDevices->delete('build_id = ' . $buildId);
                }

                foreach ($devices as $key => $value) {
                    // @TODO : fix this unstable code
                    if (is_array($value)) {
                        $value = $value['id'];
                    }
                    // insert to build devices
                    $dataValues = array('build_id' => $buildId, 'device_id' => $value);
                    if (!$buildDevices->isDeviceExists($buildId, $value))
                        $buildDevices->save($dataValues);
                }
                // start deleting unselected devices
                foreach ($savedDevices as $key => $value) {
                    // build model
                    $buildDevices->delete('id = ' . $value);
                }
                
               if ($selected == 'BY_ATTRIBUTE') {
                    //clear devices table
                    $buildDevices = new Model_ProductBuildDevices();
                    $buildDevices->delete('build_id = ' . $buildId);
                } else if ($selected == 'CUSTOM') {
                    $buildAttributes    = new Model_productDeviceAttributes();
                    $buildAttributes->delete('build_id = ' . $buildId);
                    //clear attributes table
                }

                if($changed){
                    $this->sendContentadminMail('Build info',$productId);
                }

                $this->_flashMessenger->addMessage(array('info' => 'Successfully saved selected devices'));
                // redirect to next step
//                if(empty($review))
//                    $this->_redirect('product/edit/id/' . $productId . '/6/6');
//                else
                $this->_redirect('/build/show/id/' . $productId);
            }
        } else {
            $error = $this->formatErrorMsg($formDevices->getMessages());
            $this->_flashMessenger->addMessage(array('error' => $error));
            // redirect to next step
            $this->_redirect("/build/create/productid/$productId/build/$buildId");
        }
    }

    /**
     * Show bild list
     */
    public function showAction() {
        // get the product id
        $productId = $this->_request->id;
        // get product details
        $product = new Model_Product();
        $productDetails = $product->getProductDetailsById($productId);
        $this->view->product = $productDetails;
        $builds = $product->getBuilds($productId);
        $this->view->builds = $builds;
        $prodBuild = new Model_ProductBuild();
        $buildFiles = array();
        foreach ($builds as $value) {
            $buildFiles[$value->id] = $prodBuild->getFilesFullDetails($value->id);
        }
        
        $this->view->files = $buildFiles;
        $config = Zend_Registry::get('config');
        $site_url = $config->nexva->application->base->url;
        

 
        $this->view->link = 'http://' . $site_url . "/app/" . $this->view->slug($productDetails['name'] . '-for-' . $productDetails['platform_name']) . '.' . $productDetails['id'] . '.en' . '.preview' . "." . Zend_Auth::getInstance()->getIdentity()->id;
        $this->view->isUrlProduct = ($productDetails['content_type'] == 'URL') ? true : false;

    }

    /**
     * Delete build
     */
    public function deleteAction() {
        // @TODO : add S3 file delete on this
        $productId = $this->_request->productid;
        $buildId = $this->_request->build;
        // check validations
        $product = new Model_Product();
        $formBasic = $product->getProductDetailsById($productId);
        $userId = Zend_Auth::getInstance()->getIdentity()->id;
        if ($formBasic['uid'] != $userId)
            $this->_redirect("/product/view");

        $prodBuilds = new Model_ProductBuild();
        $prodBuilds->delete('id = ' . $buildId);
        $this->_flashMessenger->addMessage(array('info' => 'Successfully deleted item'));
        $this->_redirect("/build/show/id/$productId");
    }

    /**
     * delete file in a build
     */
    public function filedeleteAction() {
        // @TODO : add proper valitaions to get the correct user validaiton
        $fileId = $this->_getParam('id');
        $buildId = $this->_getParam('bid');
        $productId = $this->_getParam('pid');

        // check validations
        $product = new Model_Product();
        $formBasic = $product->getProductDetailsById($productId);
        $userId = Zend_Auth::getInstance()->getIdentity()->id;
        if ($formBasic['uid'] != $userId)
            $this->_redirect("/product/view");
        // get file name
        $productFiles = new Model_ProductBuildFile();
        $object = $productFiles->getFileNameById($fileId);
        // @TODO : Delete from the S3 and authenticate delete
        //get config
        $config = Zend_Registry::get('config');
        // delete from local file system
        $targetPath = $config->nexva->applicaiton->fileUploadDirectory . '/' . $productId . '/';
        @unlink($targetPath . $object);
        // @TODO : Delete from the S3 and authenticate delete
        $awsKey = $config->aws->s3->publickey;
        $awsSecretKey = $config->aws->s3->secretkey;
        $bucketName = $config->aws->s3->bucketname;
        $bucketExists = false;
        $s3 = new Zend_Service_Amazon_S3($awsKey, $awsSecretKey);
        $delete = $s3->removeObject($bucketName . '/productfile/' . $productId . '/' . $object);
        if ($delete) {
            $this->_flashMessenger->addMessage(array('info' => "File deleted successfully"));
            $productFiles->delete('id = ' . $fileId . ' and build_id = ' . $buildId);
        }
        $this->_redirect("build/create/productid/$productId/build/$buildId");
    }

    /**
     * GEt the compatibility devices
     */
    public function showcompatibledevicesAction() {
        $this->_helper->getHelper('layout')->disableLayout();
        $productModel = new Model_Product();
        $this->view->productCompatibleDevices = $productModel->getFormattedProductCompatibleDevices($this->_request->id, $this->_request->build,  $this->_request->device_selection_type);
    }
    
  
    function learnmoreAction() {
    	
	    $this->_helper->layout()->disableLayout();
	    $modelPage = new Model_PageLanguages();
	    $this->view->learnMore = $modelPage->getPageByPageTitle('Learn More - Builds versions');
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

        $template = 'notify_product_detail_change.phtml';
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject('Product '.$details.' has changed');
        $mailer ->addTo('contentadmins@nexva.com')
                //->addTo('viraj@nexva.com')
                ->setMailVar("firstName", 'ContentAdmin')
                ->setMailVar("productId", $productId)
                ->setMailVar("email", $userMail)
                //->setMailVar("userId", $userId)
                ;
        $mailer->setLayout("generic_mail_template");
        $mailer->sendHTMLMail($template);
    }

    public function createNewAction(){
        $productId = $this->_request->productid;
        $buildId = $this->_request->build;
        // get product details
        $product = new Model_Product();
        $productDetails = $product->getProductDetailsById($productId);
        $this->view->isUrlProduct = ($productDetails['content_type'] == 'URL') ? true : false;
        // Devices
        $form_devices = new Cpbo_Form_ProductDevices();
        $form_devices->setAction($this->getFrontController()->getBaseUrl() . '/build/device');
        $this->view->form_devices = $form_devices;
        // files form object
        $formFilesObj = new Cpbo_Form_ProductFiles();
        // create the file form
        // Ajax upload helper libs
        // $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ajax-upload/ajaxupload.js');

        $formFiles = $formFilesObj->createFileForm();
        $this->view->fileDesc = '';

        // create the URL form
        $formUrl = $formFilesObj->createUrlForm();
        $this->view->urlDesc = 'Please enter URLs separated by enter';
        $this->view->fileDesc = 'Please upload files';

        // files form
        $this->view->formFiles = $formFiles;
        // Url form
        $this->view->formUrl = $formUrl;
        $this->view->formFiles->id->setValue($productId);
        $this->view->formFiles->build_id->setValue($buildId);
        $this->view->builbname = $productDetails['name'] . ' Default Build';

        // get the platforms
        $platformTable = new Model_Platform();
        $this->view->platforms = $platformTable->getAllPlatforms();

        $languageTable = new Model_Language();
        $this->view->language = $languageTable->getLanguageList(1);
        $this->view->selectedPlatform = -1;




        // preload data
        if (isset($buildId)) {
            //set the build id to the view
            $this->view->build_id = $buildId;
            // set the product id
            $this->view->product_id = $productId;
            // get build files
            $buildFiles = new Model_ProductBuildFile();
            $this->view->files = $buildFiles->getFilesByBuid($buildId);
            //get build name
            $prodBuild = new Model_ProductBuild();
            $productBuildDetails    = $prodBuild->getBuildDetails($buildId);
            if (!$productBuildDetails) {
                if ($productId) {
                    $this->_redirect('/build/show/id/' . $productId);
                } else {
                    $this->_redirect('/');
                }
            }

            $this->view->device_update_service = $productBuildDetails->device_update_service;
            $this->view->builbname = $prodBuild->getBuildName($buildId);
            // platform
            $this->view->selectedPlatform = $prodBuild->getBuildPlatform($buildId);
            // language
            $this->view->selectedLanguage = $prodBuild->getBuildLanguage($buildId);
            // get type of option selected
            $this->view->selected = $prodBuild->getBuildDeviceSelectionMethod($buildId);
            // get the build type
            $this->view->buildType = $prodBuild->getBuildContentType($buildId);
            // get attributes
            $productDevAttrib = new Model_productDeviceAttributes();
            $this->view->deviceAttributes = $productDevAttrib->getSelectedDeviceAttributesById($buildId);
            // get device update service


            $buildSupportedVersion = new Admin_Model_BuildSupportedVersions();
            $this->view->buildSupportedVersions = $buildSupportedVersion->getBuildSupportedVersion($buildId);


            $tagModel  = new Model_ProductBuildTag();

            //get available tags
            $avilableTags = $tagModel->getAvailableTags();
            $this->view->availableTags = $avilableTags;

            //get selected tags for the product
            $selectedTagsPrefixed = $tagModel->getBuildTagsByBuildId($buildId);

            //$selectedTags = array();
            foreach ($selectedTagsPrefixed as $buildTag)
            {
                $selectedTags = $tagModel->unPadTag($buildTag->tags);
            }

            $selectedTags = explode(',', $selectedTags) ;
            $this->view->selectedTags = $selectedTags;

        }
    }

}

?>
