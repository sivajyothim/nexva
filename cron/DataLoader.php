<?php
include_once("../application/BootstrapCli.php");
$configuration = Zend_Registry::get('config');
/** Hard coded platform values - the values come straight from the DB (platforms table) **/
$PLATFORMS = array(
    "java"          => 3,
    "ios"           => 10,
    "blackberry"    => 6,
    "android"       => 12,
    "symbian"       => 7,
    "independent"	=> 0
);
/** Device attrs - comes directly from device_attribute_definitions table **/
     $ATTRIBUTES = array (
      "os"                      => 1,
      "supports.mp3_playback"   => 4,
      "supports.j2me_1.0"       => 5,
      "supports.j2me_2.0"       => 6,
      "min_resolution_height"   => 8,
      "min_resolution_width"    => 7,
      "navigation"              => 2
    );

    /** This map exists because of the peculiar way WURFL represents device OS. So to the outside, we expose them with our constants but internally it's mapped to the WURFL represenation **/
    $OS_ATTRIBUTES = array (
      "BLACKBERRY"  => "RIM OS",
      "ANDROID"     => "Android",
      "SYMBIAN"     => "Symbian OS",
      "JAVA"        => "Java",
      "IOS"         => "iPhone OS",
    );

// Metaflow data loader .
// steps 
// 0. Unzip 
// 1. Parse provision.xml
// 2. Create product & builds in local database.
// 3. Upload/Copy image files to local webserver.
// 4. Upload application files to local webserver.
// 5. Add to Zend_queue for S3 upload.

function parseXML($filePath){
    $provision_filePath=$filePath.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'META-INF'.DIRECTORY_SEPARATOR.'provisioning.xml';
    $xml = simplexml_load_file($provision_filePath);
    return ($xml);   
}

function createProductsFromXML($xml,$filePath,$configuration){
    /** Hard coded platform values - the values come straight from the DB (platforms table) **/
$PLATFORMS = array(
    "java"          => 3,
    "ios"           => 10,
    "blackberry"    => 6,
    "android"       => 12,
    "symbian"       => 7,
    "independent"	=> 0
);
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
    $platform_id="";
    $minimum_version="";
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
                $platform_id= $PLATFORMS[strtolower((string) $property->{'property-value'})];
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
                echo "Category processed ===== ".$category ;
        }
        if( (string) $property->{'property-name'} == "nexva.subcategory" ) 
        {
                $subCategory = (string) $property->{'property-value'};
        }
        if( (string) $property->{'property-name'} == "nexva.os.version" ) 
        {
                $minimum_version = (string) $property->{'property-value'};
        }
    }
    
    $data = array( "user_id"=>$user_id, // hard code Metaflow cp ID
                   "platform_id"=>$platform_id,
                   "name"=>$title,
                   "thumbnail"=>$thumb_nail,
                   "product_type"=>$productType,
                   "registration_model"=>$productRegistrationMethod,
                   "price"=>$price,
                   "keywords"=>$keywords,
                   "created_date"=>new Zend_Db_Expr('NOW()') ,
                   "status"=>"APPROVED");
    //print_r($data);
    # create product
    $product = new Model_Product();
    $prodId = $product->save($data);
    
    echo "============================".$prodId."\n";
    
    //copy($filePath.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$icon_name, $_SERVER['DOCUMENT_ROOT'] .$$configuration->product->visuals->dirpath.DIRECTORY_SEPARATOR.$thumb_nail);        
    
    //save screenshots:
    $productImagesModel = new Model_ProductImages();
   
    //print_r($screen_shots);
    
    foreach($screen_shots as $screenshot) 
    {
       $data = array (
            'product_id'    => $prodId,
            'filename'      => $prodId . "_". basename($screenshot)
       );
       //copy($filePath.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$screenshot, $_SERVER['DOCUMENT_ROOT'].$$configuration->product->visuals->dirpath.DIRECTORY_SEPARATOR.$prodId . "_". basename($screenshot));
       $productImagesModel->insert($data);       
   }
   
   // Product Meta.   
   $productMeta = new Model_ProductMeta();
   $productMeta->setEntityId($prodId);
   $productMeta->BRIEF_DESCRIPTION = $brief;
   $productMeta->FULL_DESCRIPTION = $long;
   $productMeta->PRODUCT_VERSION = $version;
   $productMeta->NOTIFY_EMAIL = $notifyEmail;
   
   $productCategoryModel = new Model_ProductCategories();
   $data = array (
        'product_id'   => $prodId,
        'category_id'  => $category
        );
   print_r($data);
   $productCategoryModel->insert($data);
   
   $data = array (
         'product_id'   => $prodId,
         'category_id'  => $subCategory
       );
    print_r($data);
   $productCategoryModel->insert($data);
   
   $productKeyModel = new Model_ProductKey();
   $productKeyModel->saveData($prodId, "MFW");
   
   
   return $prodId;
} // end createProducts

function createProductsBuildsFromXML($xml,$filePath,$configuration,$prod_id)
{
    $PLATFORMS = array(
        "java"          => 3,
        "ios"           => 10,
        "blackberry"    => 6,
        "android"       => 12,
        "symbian"       => 7,
        "independent"	=> 0
    );
    
    
    $bundle_name="";
    $files[]="";
    $minimum_version="0";
    $buildType="";
    $platform_id;
    
    foreach($xml->{'tool-property'} as $property) 
    {
        // Get Platform
        if( (string) $property->{'property-name'} == "nexva.os.name" ) 
        {
                $platform_id= $PLATFORMS[strtolower((string) $property->{'property-value'})];
        }
    }
    
    foreach($xml->{'client-bundle'} as $bundleInfo) 
    {
        $bundle_name = (string) $bundleInfo->{'content-id'};
        if("APPLICATION" == (string) $bundleInfo->{'bundle-type'})
        {
            $files[]= (string) $bundleInfo->{'content-file'};
            $buildType="files";
        }
        //1. Save the files to the filesystem. This would be temporary until we can sync it to S3
        $targetPath = $configuration->nexva->applicaiton->fileUploadDirectory . '/' . $prod_id . '/';
        if( !is_dir($targetPath))
        {
                mkdir(str_replace('//', '/', $targetPath), 0755, true);
        }
        
        foreach($files as $file) 
        {
            //copy($filePath.'/tmp'.DIRECTORY_SEPARATOR.$file, $targetPath.basename($file));
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
                'product_id' => $prod_id,
                'name' => $bundle_name,
                'device_selection_type' => "BY_ATTRIBUTE",
                'platform_id' => $platform_id,
                'build_type' => $buildType,
                'language_id' => $languageId
            );
        
        
        
        $buildId = $productBuild->save($data);
        echo "=====================================================\n";
        echo $buildId."\n";

        $buildFile = new Model_ProductBuildFile();
        $queue = Nexva_Util_Queue_ZendQueue::getInstance('s3_file_sync');
        foreach($files as $file) 
        {
            $data = array(
                'build_id' => $buildId,
                'filename' => basename('/home/sudha/tmp/'.$file),
                'filesize' => filesize('/home/sudha/tmp/'.$file)
            );
            $fileId = $buildFile->save($data);
            
            //3.1. Push this file to our queue so it could be synced to S3
            $_TEMP['Filedata']['name'] = basename('/home/sudha/tmp/'.$file);
            $_TEMP['Filedata']['productId'] = $prod_id;
            $_TEMP['Filedata']['buildId'] = $buildId;
            $_TEMP['Filedata']['fileId'] = $fileId;
            $_TEMP['Filedata']['destination'] = $targetPath.basename('/home/sudha/tmp/'.$file);
            $queue->send(serialize($_TEMP['Filedata']));
        }
        
        $buildSupportedVersions = new  Model_BuildSupportedVersions();
        $data = array(
            "build_id" => $buildId,
            "min_version" =>  $minimum_version,
            "or_better" => $minimum_version
            );
        print_r($data);
        $buildSupportedVersions->insert($data);
        
        $productDeviceAttributes = new Model_productDeviceAttributes();
        $data = array (
            'build_id' => $buildId,
            'device_attribute_definition_id'    => 1, // For attribute "OS"
            'value' => "Android"
            );
        $productDeviceAttributes->save($data);
    }
}

function unzip($filePath) {
    $unzip_destination = $filePath.DIRECTORY_SEPARATOR.'tmp';
    $archive = new Nexva_Util_FileFormat_Zip_PclZip($filePath. DIRECTORY_SEPARATOR. 'nexva_01032012.zip');
    $list  =  $archive->extract($unzip_destination, $filePath);
    //return $list;
}// end unzip

$filePath ='/home/sudha';
//unzip($filePath);
$xml=parseXML($filePath);
//$prod_id=createProductsFromXML($xml,$filePath,$configuration);
createProductsBuildsFromXML($xml,$filePath,$configuration,13145);

?>
