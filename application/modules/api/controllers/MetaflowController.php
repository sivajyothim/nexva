<?php
/**
 * Description of MetaFlowController:
 * 
 * Controller to handle bulk uploads from MetaFlow. MetaFlow send it's own provisoning file along with Application & related 
 * resources through a POST request. 
 *  1. create Products
 *  2. create Product builds
 *  3. Uploads to S3.
 *  and returns a JSON response. 
 * There are no views associated with this action.
 * 
 * @author R. Sudha
 */
class Api_MetaflowController extends Zend_Controller_Action  {
    protected $config="";
    protected $platform_id=""; 
    protected $userId = null;
    protected $productId="";
    protected $minimum_version="";
    /** Hard coded platform values - the values come straight from the DB (platforms table) **/
    protected $PLATFORMS = array(
        "midlet"       => 3, // Java application is sent as Midlet from MetaFlow.
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

    
    
    public function init() 
    {
        // Set the display
        $this->_helper->layout ()->disableLayout ();
        $this->_helper->viewRenderer->setNoRender (true);
        set_error_handler(array($this, 'myErrorHandler'));
        
        if( APPLICATION_ENV == 'production')
        {
            ini_set('display_errors', '0');
        }
        // Get configurations.
        $this->config = Zend_Registry::get('config');
        
    }
    // Error handler 
    public function myErrorHandler($errno, $errstr, $errfile, $errline)
    {
        header('HTTP/1.0 400 Bad Request');
        $this->echoJson(
            array(
                "status" => 0,
                "message" => "1 or more errors occured while attempting to provision your content",
                'error' => strip_tags($errstr),
            )
        );
        return true;
    }
    // POST request handler.
    public function submitAction() 
    {
        // Restrict open web access.
        if(!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_PW'])) {
            header('WWW-Authenticate: Basic realm="neXpusher: Please enter your CP credentials."');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Access denied';
            die();
        }
        // Check authentication.
        else 
        {
            $userModel = new Cpbo_Model_User();
            $user = $userModel->getUserDetailsByEmail($_SERVER['PHP_AUTH_USER']);
	    if( is_null($user) ) 
            {
                header('HTTP/1.0 403 Access denied for '.$_SERVER['PHP_AUTH_USER']);
                echo json_encode(array("status" => 0, "message" => "Access denied: Login incorrect" ));
                die();
            } 
            else 
            {
            	$this->userId = $user->id;
            }
        }
        if( !isset($_FILES['provision'])) 
        {
            trigger_error('Provision archive not found in POST request. Please make sure you named it \'provision\' (case matters!).');
            die();
        }
        
        $provisionFilePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid();
        mkdir($provisionFilePath, 0755, true);
        
        //Zend_Debug::dump($provisionFilePath, 'd');
        
        //Zend_Debug::dump($_FILES['provision']['tmp_name'], 'c');
        
       // die();
        
        $ret = move_uploaded_file($_FILES['provision']['tmp_name'], $provisionFilePath. DIRECTORY_SEPARATOR. 'provision.zip');
        //$archive = new Nexva_Util_FileFormat_Zip_PclZip($provisionFilePath. DIRECTORY_SEPARATOR. 'provision.zip');
        //$list  =  $archive->extract(PCLZIP_OPT_PATH, $provisionFilePath);
        
        chdir($provisionFilePath);
        // using general zip command as the PCLZIP was terminating when doing >20 MB file upload.
        $ret = shell_exec('unzip '.$provisionFilePath. DIRECTORY_SEPARATOR. 'provision.zip'.' 2>&1');
        
        Zend_Debug::dump($ret);
        
        $xml = simplexml_load_file('META-INF/provisioning.xml');
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        $this->createProduct($xml,$user->id);
        $buildIds="";
        if( $this->productId > 0 )
        {
            $buildIds = $this->writeProductBuilds($xml);
        }
        $db->commit();
	$this->echoJson(
                        array(
                            "status" => 1,
                            "message" => "Your content was provisioned successfully",
                            //"simulate" => $simulate,
                            "id" => $this->productId,
                            "builds" => $buildIds
                            )
                        );
        
    }
    protected  function createProduct($xml,$user_id)
    {
        $user_id="0"; // Admin
        $keywords="";
        $title="";
        $brief="";
        $long="";
        $productRegistrationMethod="";
        $productType="";
        $thumb_nail="";
        $price="";
        $status="";
        $screen_shots[]="";
        $version="";
        $notifyEmail="";
        $category="";
        $subCategory="";
        // used for refering thumbnail image file while copying.
        $icon_name="";
        foreach($xml->{'tool-descriptions'}->description as $description) 
        {
            if( (string)$description->attributes()->locale == "en" && 
                (string)$description->attributes()->type == "keywords" ) 
            {
                $keywords = (string) $description;
            }
            if( (string) $description->attributes()->locale == "en" && 
                (string) $description->attributes()->type == "title" ) 
            {
                $title = (string) $description;
            }
            if( (string) $description->attributes()->locale == "en" && 
                (string) $description->attributes()->type == "short" ) 
            {
                $brief = nl2br((string) $description);
            }
            if( (string) $description->attributes()->locale == "en" && 
                (string) $description->attributes()->type == "long" ) 
            {
                $long = nl2br((string) $description);
            }
            // Get price data
            if( (string) $description->attributes()->type == "price" ) 
            {
                $price = nl2br((string) $description);
            }
        }
           // Get thumbnail name
        foreach($xml->{'tool-descriptions'}->icon as $icon) 
        {
            if( $icon->attributes()->type == "splash" ) 
            {
                $icon_name = basename((string)$icon);
                $thumb_nail = uniqid()."_".$icon_name;
            }
            if( $icon->attributes()->type == "ingame" ) 
            {
                $screen_shots[] = basename((string)$icon);
            }
        }
        foreach($xml->{'tool-property'} as $property) 
        {
            // Get Platform
            if( (string) $property->{'property-name'} == "nexva.os.name" ) 
            {
                    $this->platform_id= $this->PLATFORMS[strtolower((string) $property->{'property-value'})];
            }
            // Get product type
            if( (string) $property->{'property-name'} == "nexva.productType" ) 
            {
                    $productType = (string) $property->{'property-value'};
            }
            //Get product registration model
            if( (string) $property->{'property-name'} == "nexva.registrationModel" ) 
            {
                    $productRegistrationMethod = (string) $property->{'property-value'};
            }
            if( (string) $property->{'property-name'} == "nexva.contentVersion" ) 
            {
                    $version = (string) $property->{'property-value'};
            }
            if( (string) $property->{'property-name'} == "nexva.notificationEmail" ) 
            {
                    $notifyEmail = (string) $property->{'property-value'};
            }
            if( (string) $property->{'property-name'} == "nexva.category" ) 
            {
                    $category = (string) $property->{'property-value'};
            }
            if( (string) $property->{'property-name'} == "nexva.subcategory" ) 
            {
                    $subCategory = (string) $property->{'property-value'};
            }
            if( (string) $property->{'property-name'} == "nexva.os.version" ) 
            {
                    $this->minimum_version = (string) $property->{'property-value'};
            }
        }
        $data = array( "user_id"=>$this->userId, // hard code Metaflow cp ID
                       "platform_id"=>$this->platform_id,
                       "name"=>$title,
                       "thumbnail"=>$thumb_nail,
                       "product_type"=>$productType,
                       "registration_model"=>$productRegistrationMethod,
                       "price"=>$price,
                       "keywords"=>$keywords,
                       "created_date"=>new Zend_Db_Expr('NOW()') ,
                       "status"=>"APPROVED");
        
        //
        
        # create product
        $product = new Model_Product();
        $this->productId = $product->insert($data);
        // Copy thumnail images 
        copy($icon_name, 
                $_SERVER['DOCUMENT_ROOT'] .$this->config->product->visuals->dirpath.DIRECTORY_SEPARATOR.$thumb_nail);
        //save screenshots:
        $productImagesModel = new Model_ProductImages();
   
        foreach($screen_shots as $screenshot) 
        {
           if(!empty ($screenshot))
           {
                $data = array (
                    'product_id'    => $this->productId,
                    'filename'      => $this->productId . "_". basename($screenshot)
               );

               copy($screenshot, $_SERVER['DOCUMENT_ROOT'].$this->config->product->visuals->dirpath.DIRECTORY_SEPARATOR.$this->productId . "_". basename($screenshot));
               $productImagesModel->insert($data);       
           }
        }
       // Product Meta.   
       $productMeta = new Model_ProductMeta();
       $productMeta->setEntityId($this->productId);
       $productMeta->BRIEF_DESCRIPTION = $brief;
       $productMeta->FULL_DESCRIPTION = $long;
       $productMeta->PRODUCT_VERSION = $version;
       $productMeta->NOTIFY_EMAIL = $notifyEmail;

       $productCategoryModel = new Model_ProductCategories();
       $data = array (
            'product_id'   => $this->productId,
            'category_id'  => $category
            );
       $productCategoryModel->insert($data);

       $data = array (
             'product_id'   => $this->productId,
             'category_id'  => $subCategory
           );
       $productCategoryModel->insert($data);
   
       // This stores product activation keys. it's not used at the moment with this controller.
       //$productKeyModel = new Model_ProductKey();
       //$productKeyModel->saveData($this->productId, "MFW");
    }
    
    protected function writeProductBuilds($xml) {
        $bundle_name="";
        $minimum_version="0";
        $buildType="";
        $returnReponse = array();
        foreach($xml->{'client-bundle'} as $bundleInfo) 
        {
            $bundle_name = (string) $bundleInfo->{'content-id'};
            if("APPLICATION" == (string) $bundleInfo->{'bundle-type'})
            {
                $files[]= (string) $bundleInfo->{'content-file'};
                if($this->platform_id==6)
                {
                    $_temp =(string) $bundleInfo->{'descriptor-file'};
                    array_push ( $files ,$_temp);
                }
                
                $buildType="files";
            }
            //1. Save files in local filesystem. This would be temporary until we can sync it to S3
            $targetPath = $this->config->nexva->applicaiton->fileUploadDirectory . '/' . $this->productId . '/';
            if( !is_dir($targetPath))
            {
                    mkdir(str_replace('//', '/', $targetPath), 0755, true);
            }

            foreach($files as $file) 
            {
                copy($file, $targetPath.basename($file));
            }
            $languageModel = new Model_Language();
            $languageId = $languageModel->getLanguageIdByCode((string)$bundleInfo->locale);
            if(is_null($languageId))
            {
                $languageId = 1; //we couldn't find the language so we default to english (1)
            }
            else
            {
                $languageId = $languageId->id;
            }

            $productBuild = new Model_ProductBuild();
            $data = array(
                    'product_id' => $this->productId,
                    'name' => $bundle_name,
                    'device_selection_type' => "BY_ATTRIBUTE",
                    'platform_id' => $this->platform_id,
                    'build_type' => $buildType,
                    'language_id' => $languageId
                );

            $buildId = $productBuild->insert($data);
            $returnReponse = $buildId;
            
            $buildFile = new Model_ProductBuildFile();
            $queue = Nexva_Util_Queue_ZendQueue::getInstance('s3_file_sync');
            foreach($files as $file) 
            {
                $data = array(
                    'build_id' => $buildId,
                    'filename' => basename($file),
                    'filesize' => filesize($file)
                );
                $fileId = $buildFile->insert($data);
                //3.1. Push this file to our queue so it could be synced to S3
                $_TEMP['Filedata']['name'] = basename($file);
                $_TEMP['Filedata']['productId'] = $this->productId;
                $_TEMP['Filedata']['buildId'] = $buildId;
                $_TEMP['Filedata']['fileId'] = $fileId;
                $_TEMP['Filedata']['destination'] = $targetPath.basename($file);
                $queue->send(serialize($_TEMP['Filedata']));
            }
            $buildSupportedVersions = new  Model_BuildSupportedVersions();
            $data = array(
                "build_id" => $buildId,
                "min_version" =>  $this->minimum_version,
                "or_better" => $this->minimum_version
                );
            $buildSupportedVersions->insert($data);
            $productDeviceAttributes = new Model_productDeviceAttributes();
            $data = array (
                'build_id' => $buildId,
                'device_attribute_definition_id'    => 1, // For attribute "OS"
                'value' => array_search(12, $this->PLATFORMS)
                );
            $productDeviceAttributes->insert($data);
        }
        return $returnReponse;
    }
     protected function echoJson($json, $halt=1) {
            $this->getResponse()
                ->setHeader('Content-type', 'application/json')
                ;
            echo json_encode($json);
            if( $halt ) die();

        }    
}

?>
