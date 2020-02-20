<?php

class Api_NexpushController extends Zend_Controller_Action  {

	protected $userId = null;
	
    /** Hard coded platform values - the values come straight from the DB (platforms table) **/
    protected $PLATFORMS = array(
        "java"          => 3,
        "ios"           => 10,
        "blackberry"    => 6,
        "android"       => 12,
        "symbian"       => 7,
        "independent"	=> 0
    );

    /** Device attrs - comes directly from device_attribute_definitions table **/
    protected $ATTRIBUTES = array (
      "os"                      => 1,
      "supports.mp3_playback"   => 4,
      "supports.j2me_1.0"       => 5,
      "supports.j2me_2.0"       => 6,
      "min_resolution_height"   => 8,
      "min_resolution_width"    => 7,
      "navigation"              => 2
    );

    /** This map exists because of the peculiar way WURFL represents device OS. So to the outside, we expose them with our constants but internally it's mapped to the WURFL represenation **/
    protected $OS_ATTRIBUTES = array (
      "BLACKBERRY"  => "RIM OS",
      "ANDROID"     => "Android",
      "SYMBIAN"     => "Symbian OS",
      "JAVA"        => "Java",
      "IOS"         => "iPhone OS",
    );

    protected $_errors = null;

    public function submitAction() {

        

        if(!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_PW'])) {
            header('WWW-Authenticate: Basic realm="neXpusher: Please enter your CP credentials."');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Access denied';
            die();
        }
        else {
            $userModel = new Cpbo_Model_User();
            $user = $userModel->getUserDetailsByEmail($_SERVER['PHP_AUTH_USER']);
            if( is_null($user) ) {
                header('HTTP/1.0 403 Access denied for '.$_SERVER['PHP_AUTH_USER']);
                echo json_encode(array("status" => 0, "message" => "Access denied: Login incorrect" ));
                die();
            } 
            else {
            	$this->userId = $user->id;
            }         
        }
       
        if( !isset($_FILES['provision'])) {
            trigger_error('Provision archive not found in POST request. Please make sure you named it \'provision\' (case matters!).');
            //header('HTTP/1.0 400 Malformed Request');
            //echo json_encode(array("status" => 0, "message" => "File: provision.zip not uploaded." ));
            die();
        }

        //Zend_Debug::dump($_FILES);die();

        $provisionFilePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid();
        
        mkdir($provisionFilePath, 0755, true);
        
        $ret = move_uploaded_file($_FILES['provision']['tmp_name'], $provisionFilePath. DIRECTORY_SEPARATOR. 'provision.zip');
       	
        
        // PclZip doesn't work with large file uploads. Therefore we are using reglar unzip cli utility.
        //$archive = new Nexva_Util_FileFormat_Zip_PclZip($provisionFilePath. DIRECTORY_SEPARATOR. 'provision.zip');      
        //$list  =  $archive->extract(PCLZIP_OPT_PATH, $provisionFilePath);
        
        chdir($provisionFilePath);
        // using general zip command as the PCLZIP was terminating when doing >20 MB file upload.
        $ret = shell_exec('unzip '.$provisionFilePath. DIRECTORY_SEPARATOR. 'provision.zip'.' 2>&1');

        //validate XML
        libxml_use_internal_errors(true);
        $xml = new DOMDocument();
        $xml->loadXML(file_get_contents('provision.xml'));
        $this->validateXml($xml);
        
        
        $xml = simplexml_load_file('provision.xml');
        
        
        $db = Zend_Registry::get('db');
        
        

        $db->beginTransaction();

        //Save product details
        $productId = $this->writeProductDetails($xml,$user->id, $xml->attributes()->id);
        
        //Save prdicut builds
        if( $productId > 0 )
            $buildIds = $this->writeProductBuilds($xml, $productId);
        

        //check for simulation, if its a simulation, modify the status
        if( (isset($xml->attributes()->simulate) && "1" == (string)$xml->attributes()->simulate) || "1" == $this->_request->simulate )
        {
            $simulate = 1;
            $product = new Model_Product();
            $product->update(
                array(
                  "deleted" => 1, "deleted_date" => date('Y-m-d')
                ),
                array(
                  "id = ?" => $productId
                )
            );
        }    
        else
            $simulate = 0;

        if( 0 == $simulate)
            $db->commit();
        else 
            $db->rollback();
        
        $this->echoJson(
                array(
                    "status" => 1,
                    "message" => "Your content was provisioned successfully",
                    "simulate" => $simulate,
                    "id" => $productId,
                    "builds" => $buildIds
                )
            );
       
 
        

    }

    public function init() {
        $this->_helper->layout ()->disableLayout ();
        $this->_helper->viewRenderer->setNoRender (true);
        set_error_handler(array($this, 'myErrorHandler'));
        
        //register_shutdown_function(array($this, 'handleFatalPhpError'));
        //if( APPLICATION_ENV != 'production')
        	//Zend_Controller_Front::getInstance()->setParam('noErrorHandler', true);

        if( APPLICATION_ENV == 'production')
            ini_set('display_errors', '0');
    }

    protected function writeProductDetails($xml, $userId, $nexpushId) {
    	
        $config = Zend_Registry::get('config');
        
        $updateMode = false;
        
        $updateMode =  trim((string) $xml->attributes()->id ) != "";
               
        //if( 'update' == $xml->attributes()->mode)  //we dont support updates yet.. fudge the error message like it's per account..
        //    trigger_error('Pushing updates not is supported with this account. Please contact support (contact@nexva.com) for more information.');

        
        
        //if( 'new' == $xml->attributes()->mode && $this->nexpushIdExists($nexpushId, $userId))
         //   trigger_error("Can't create: neXpush ID:".$nexpushId. " already exists", E_USER_ERROR);
         
        
        
         
        
        if( $updateMode ) {
        	$productId = trim( (string) $xml->attributes()->id);
        	$this->validateProduct($this->userId, $productId);               
        }
        
        
        
        foreach($xml->descriptions->description as $description) {
            if( $description->attributes()->type == "keywords") $keywords = $description;

            if( $description->attributes()->language == "en" && $description->attributes()->type == "title" ) $title = $description;
            if( $description->attributes()->language == "en" && $description->attributes()->type == "brief" ) $brief = nl2br($description);
            if( $description->attributes()->language == "en" && $description->attributes()->type == "long" ) $long = nl2br($description);

        }

        $productType = $xml->registration->attributes()->type;
        
        if( $xml->registration->model && $xml->registration->model->attributes()->method) {

	        switch($xml->registration->model->attributes()->method) {
	            case "static":
	                $productRegistrationMethod = "STATIC";
	                break;
	
	            case "pool":
	                $productRegistrationMethod = "POOL";
	                break;
	
	            case "dynamic":
	                $productRegistrationMethod = "DYNAMIC";
	                break;
	
	        }
        }
        else
        	$productRegistrationMethod = 'NULL';

        foreach($xml->properties->property as $property) {
            if( $property->attributes()->name == "price") $price = $property;
            if( $property->attributes()->name == "category.parent") $parentCategory = $property;
            if( $property->attributes()->name == "category.sub") $subCategory = $property;
            if( $property->attributes()->name == "version") $version = $property;
            if( $property->attributes()->name == "notification_email") $notifyEmail = $property;
        }

        foreach($xml->images->image as $image) {
            
            if(!file_exists($image)) trigger_error('Screenshot/Thumbnail not found: '.$image, E_USER_ERROR); //throw new  Zend_Exception('Screenshot/Thumbnail not found: '.$image);
            if($image->attributes()->type == "thumbnail") $thumbnail = $image;
            if($image->attributes()->type == "screenshot") $screenshots[] = $image;

        }


       //echo basename($screenshot[0]);

       $productModel = new Model_Product();
      

       $thumbPrefix = uniqid();

       $data = array(
        "user_id"   => $userId,
        //"nexpush_id" => $nexpushId,
        "name"      => $title,
        "thumbnail" => $thumbPrefix."_".basename($thumbnail),
        "product_type" => $productType,
        "registration_model" => $productRegistrationMethod,
        "price" => $price,
        "keywords" => $keywords,
        "created_date" => new Zend_Db_Expr('NOW()'),
        "deleted" => 0
       );

       //$productId = 121234;
       
       if(  !$updateMode )      
       		$productId = $productModel->insert($data);
       else
       		$productModel->update($data, "id = $productId");
       
       copy($thumbnail, $_SERVER['DOCUMENT_ROOT'] .$config->product->visuals->dirpath."/".$thumbPrefix."_".basename($thumbnail));
       $this->d("Created new product. Product ID: ". $productId);

       //save screenshots:
       $productImagesModel = new Model_ProductImages();

       foreach($screenshots as $screenshot) {
           $data = array (
                'product_id'    => $productId,
                'filename'      => $productId . "_". basename($screenshot)
           );
           
           
           if( !$updateMode )
           	$productImagesModel->insert($data);
           else
           	$productImagesModel->update($data, "id = ". $productId);

           copy($screenshot, $_SERVER['DOCUMENT_ROOT'] .$config->product->visuals->dirpath."/".$productId . "_". basename($screenshot));
       }

       $this->d("Saved screenshots");

       $productMeta = new Model_ProductMeta();

       $productMeta->setEntityId($productId);
       $productMeta->BRIEF_DESCRIPTION = $brief;
       $productMeta->FULL_DESCRIPTION = $long;
       $productMeta->PRODUCT_VERSION = $version;
       $productMeta->NOTIFY_EMAIL = $notifyEmail;

       $this->d("Saved product meta details");

       $productCategoryModel = new Model_ProductCategories();

       $data = array (
         'product_id'   => $productId,
         'category_id'  => $parentCategory
       );
       
       if( $updateMode ) {       	
       	//it's easier to delete the categories and then allow the code to insert them again
       	$productCategoryModel->delete("product_id = $productId");
       }
       
       
       $productCategoryModel->insert($data);

       $this->d("Saved product parent-category");

       $data = array (
         'product_id'   => $productId,
         'category_id'  => $subCategory
       );

       $productCategoryModel->insert($data);
       $this->d("Saved product sub-category");

	   if( !$updateMode ) {
	   		//we only worry about product keys if we are not in update mode, obviously
	   		
	       //save product key if applicable
	       if( isset($productRegistrationMethod) && $productRegistrationMethod != "") {
	           $productKeyModel = new Model_ProductKey();
	           $productKeyModel->saveData($productId, (string)$xml->registration->model->value);
	       }
	   }

       
       return $productId;

        

    }

    protected function writeProductBuilds($xml, $productId) {

		$returnReponse = array();
		
        $config = Zend_Registry::get('config');
        
        $updateMode = false;
        
        
        foreach($xml->builds->build as $build) {

            //echo $build->attributes()->name ."<br>";
            $buildData['name'] = (string)$build->attributes()->name;
            $buildData['platform'] = (string)$build->attributes()->platform;
            
            $updateMode =  trim((string) $build->attributes()->id ) != "";
            
            if( $updateMode) $buildId = trim((string) $build->attributes()->id);

            if( $updateMode) $this->validateBuild($this->userId, $buildId);


            foreach($build->uris->uri as $uri) {
                //echo $uri->attributes()->mimetype."->".$uri."<br>";
                if( $build->attributes()->type == 'file' && !file_exists($uri) ) trigger_error('File not found: '.$uri, E_USER_ERROR);

                if( $build->attributes()->type == 'file')
                    $buildData['files'][] = (string)$uri;
                else
                    $buildData['urls'][] = (string)$uri;

                //print_r((string)$uri);
            }
            

            
            if (isset( $build->devices)) {
            	            
	            switch ($build->devices->attributes()->selection) {
	                case "attribute":
	                    $buildData['device_selection_method'] = "BY_ATTRIBUTE";
	
	                    foreach($build->devices->attributes->attribute as $attribute) {
	                        //echo $attribute->attributes()->name."->".$attribute."<br>";
	
	                        switch((string) $attribute->attributes()->name ) { //populate the attributes
	
	                            case "minimum_version":
	                                if ($attribute->attributes()->better =="1")
	                                    $buildData['minimum_version_or_better'] = 1;
	                                else
	                                    $buildData['minimum_version_or_better'] = 0;
	
	                                $buildData['minimum_version'] = (string)$attribute;
	
	                                break;
	
	                            default:
	                                $buildData['attribute'][(string)$attribute->attributes()->name] = (string)$attribute;
	                                break;
	
	                        }
	                    }
	
	                    break;
	
	                case "manual":
	                    $buildData['device_selection_method'] = "CUSTOM";
	
	                    if( count($build->devices->useragents->useragent )> 0 ) {
	
	                        foreach($build->devices->useragents->useragent as $useragent) {
	                            $device = $this->getDeviceIdByUseragent($useragent);
	                            if( !$device ) trigger_error('Unable to detect device. User-agent: '.$useragent, E_USER_ERROR);
	                            //echo $device."<br>";
	                            $buildData['devices'][] = (string)$device;
	                        }
	
	                    } else {
	
	
	                        foreach($build->devices->device as $device) {
	                            $buildData['devices'][] = (string)$device;
	                        }
	                    }
	                    break;
	            }
            }
            

            //trigger_error(Zend_Debug::dump($buildData));

            //1. Save the files to the filesystem. This would be temporary until we can sync it to S3
            $targetPath = $config->nexva->applicaiton->fileUploadDirectory . '/' . $productId . '/';
            if( !is_dir($targetPath))
                mkdir(str_replace('//', '/', $targetPath), 0755, true);

            if(  $build->attributes()->type == 'file' ) {
                foreach($buildData['files'] as $file) {
                    copy($file, $targetPath.basename($file));
                    $this->d("Copied $file to ".$targetPath.basename($file));
                }
            }

            //2. Create the build in the DB:
            $productBuild = new Model_ProductBuild();
            $languageModel = new Model_Language();
            $languageId = $languageModel->getLanguageIdByCode((string)$build->attributes()->language);

            if(is_null($languageId))
                $languageId = 1; //we couldn't find the language so we default to english (1)
            else
                $languageId = $languageId->id;


            //these 2 lines exist because of the peculiar way the ENUM field is named - couldn't have it properaged to the XML that would be seen by outside eyes
            if((string)$build->attributes()->type == "file") $buildType = "files";
            if((string)$build->attributes()->type == "url") $buildType = "urls";

            $data = array(
                'product_id' => $productId,
                'name' => $buildData['name'],
                'device_selection_type' => isset ($buildData['device_selection_method']) ? $buildData['device_selection_method'] : "BY_ATTRIBUTE",
                'platform_id' => $this->PLATFORMS[strtolower($buildData['platform'])],
                'build_type' => $buildType,
                'language_id' => $languageId
            );
            
            if( $updateMode ) {
            	$productBuild->update($data, "id = ". $buildId);
            }
            else {
            	$buildId = $productBuild->insert($data);
            }

            
            
            $returnReponse[$buildData['name']] = $buildId;

            $this->d("Saved build: ".(string)$build->attributes() ." to DB. Build id: ". $buildId);


            
            //3. Save the files to the DB:
            
            /** The easiest way to deal with updating file is to first delete the file entries and allow it to be recreated. **/
            if( $updateMode ) {
            	
            	$buildFile = new Model_ProductBuildFile();            	
            	$buildFile->delete("build_id = ".$buildId );
            }
            
            if(  $build->attributes()->type == 'file' ) {

                $buildFile = new Model_ProductBuildFile();
                $queue = Nexva_Util_Queue_ZendQueue::getInstance('s3_file_sync');

                
                foreach($buildData['files'] as $file) {
                    $data = array(
                        'build_id' => $buildId,
                        'filename' => basename($file),
                        'filesize' => filesize($file)
                    );
                    
                    
                    $fileId = $buildFile->insert($data);
                                                       
                    $this->d("Saved file: ".basename($file)." to DB");

                    //3.1. Push this file to our queue so it could be synced to S3
                    $_TEMP['Filedata']['name'] = basename($file);
                    $_TEMP['Filedata']['productId'] = $productId;
                    $_TEMP['Filedata']['buildId'] = $buildId;
                    $_TEMP['Filedata']['fileId'] = $fileId;
                    $_TEMP['Filedata']['destination'] = $targetPath.basename($file);

                    $queue->send(serialize($_TEMP['Filedata']));

                    $this->d("Pushed ".$targetPath.basename($file)." to queue");
                }
            } else {
                //It's a URL app.. populate URLs
                    foreach($buildData['urls'] as $url) {
                        $data = array(
                            'build_id' => $buildId,
                            'filename' => $url,
                            'filesize' => null
                        );

                        $fileId = $buildFile->insert($data);
                        $this->d("Saved URL: ".basename($file)." to DB");
                    }
            }

            //4. Associate devices with the build.
            
            /** The easiest way to deal with updating compatibilities is to first delete the  entries and allow it to be recreated. **/
            if( $updateMode ) {
            	$buildDevicesModel = new Model_BuildDevices();            	
            	$buildDevicesModel->delete("build_id = ".$buildId );
            	
            	$productDeviceAttributes = new Model_productDeviceAttributes();
            	$productDeviceAttributes->delete("build_id = ". $buildId);            
            }
            
            
            //trigger_error(Zend_Debug::dump($buildData));
            
            if( isset($buildData['device_selection_method'])) {
            	
            	
	            if( $buildData['device_selection_method'] == "CUSTOM") {
	
	                $buildDevicesModel = new Model_BuildDevices();
	
	                foreach($buildData['devices'] as $device) {
	                    $data = array (
	                      'device_id'   => $device,
	                      'build_id'    => $buildId
	                    );
	                    //try {
	                    $this->d("Associated device ID:".$device." with build ID:".$buildId);
	                        $buildDevicesModel->insert($data);
	                        $this->d("Associated device ID:".$device." with build ID:".$buildId);
	                   // } catch(Exception $e) { throw new Zend_Exception('Unable to associate with device: Device not recognized. Device ID:'.$device); }
	                }
	
	
	            } else { //by attribute
	                $productDeviceAttributes = new Model_productDeviceAttributes();
	                
	                
					//store the firmware version
	                $buildSupportedVersions = new  Model_BuildSupportedVersions();
	                if( $updateMode ) {
	                	$buildSupportedVersions->delete("build_id = $buildId");
	                }                
					if( isset($buildData['minimum_version'])) {	                	               	                                             
		                $data = array(
		                     "build_id" => $buildId,
		                     "min_version" =>  $buildData['minimum_version'],
		                     "or_better" => $buildData['minimum_version_or_better']
		                );
	                
	                	$buildSupportedVersions->insert($data);
					}
	                
	                                                              
					if( isset($buildData['attribute']) ) { 						
		                foreach($buildData['attribute'] as $attribute => $value ) {
		                               
		                    if("os" == $attribute && $value != "" ) $value = $this->OS_ATTRIBUTES[$value]; else $value = strtolower($value);
		
		                    if( $value != "") {
		
		                        $data = array (
		                            'build_id'                          => $buildId,
		                            'device_attribute_definition_id'    => $this->ATTRIBUTES[strtolower($attribute)],
		                            'value'                             => $value
		                        );
		
		                        $productDeviceAttributes->insert($data);
		                        $this->d("Associated device attribute:".$attribute." with build ID:".$buildId);
		                    }
		                }
					}
	                
	                
	
	            }
            }
            
            

            $buildData = null;


            //echo "<hr>";
        }

        //finally: submit this so that the admins can approve:
        
        $userMeta = new Model_UserMeta();
        $userMeta->setEntityId($this->userId);
        
        $productModel = new Model_Product();
        
        if($userMeta->CP_AUTO_APPROVE_CONTENT == "1") {
        	$productModel->setStatus('APPROVED', $productId);
        }
        else
        {        	     
	        $productModel->setStatus('PENDING_APPROVAL', $productId);
        }
        
        return $returnReponse;
        
    }



    protected function getDeviceIdByUseragent($ua) {
        $wurfl = Nexva_DeviceDetection_Factory::factory();

        $result = $wurfl->detectDeviceByUserAgent($ua);

        if( !$result) return false;
        return $wurfl->getNexvaDeviceId();

    }

    protected function d($msg) {
        static $messages = array();

        //echo $msg."<br>";
        array_push($messages, $msg);
    }

    protected function validateXml($xml) {
        if (!$xml->validate()) {
            trigger_error('The xml provisioning file failed to validate.', E_USER_ERROR);
        }
        
        return true;
    }

    public function myErrorHandler($errno, $errstr, $errfile, $errline)
    {

        header('HTTP/1.0 400 Bad Request');
        
        $this->echoJson(
            array(
                "status" => 0,
                "message" => "1 or more errors occured while attempting to provision your content",
                'error' => strip_tags($errstr),
                //'file' => $errfile,
                //'line' => $errline
            )
        );

        return true;
    }


    protected function handleFatalPhpError() {
    	
        $last_error = error_get_last();
        print_r($last_error); die();
        if( !is_null($last_error) ) {        	
           header('HTTP/1.0 500 Application Error');
           $this->echoJson(
               array(
                   'status' => 0,
                   'message' => 'An internal application error has occured. Please contact support (contact@nexva.com) quoting this error',
                   'error'  => $last_error
               )
           );
           die();
        }
           
        
    }

    protected function echoJson($json, $halt=1) {
        $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ;

        echo json_encode($json);
        if( $halt ) die();

    }

    protected function nexpushIdExists($nexpushId, $userId) {
        $productModel = new Model_Product();

        $exists = $productModel->fetchRow ('nexpush_id = '. $nexpushId. ' AND user_id = '.$userId);
                                               
        return !is_null($exists);
    }
    
    public function validateBuild($userId, $buildId) {
    	
    	$buildModel =  new Model_ProductBuild();
    	
    	$buildData = $buildModel->getBuildDetails($buildId);
    	
    	$productModel = new Model_Product();
    	
    	if( !$buildData ) trigger_error("Build ID you sent ($buildId) was not found");
    	
    	$productData = $productModel->getProductDetailsById($buildData->product_id);
    	
    	if( $productData['uid'] != $userId) trigger_error("The build ID you sent was found but it does not belong to your account (ID: ". $this->userId. ")" );
    	
    	return true;
    	    	     	    
    } 
    
    public function validateProduct($userId, $productId) {
    	
    	$productModel = new Model_Product();
    	
    	$product = $productModel->getProductDetailsById($productId);
    	
    	if( is_null($product) ) 
    		trigger_error("The app ID you sent ($productId) was not found");
    
    	if( $product['uid'] != $userId) trigger_error("The app ID you sent was found but it does not belong to your account (ID: ". $this->userId. ")" );
    	
    	return true;    	
    }
    

    
}

?>
