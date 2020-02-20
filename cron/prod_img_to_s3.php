<?php
error_reporting(E_ALL);
set_time_limit(0);
include_once ("../application/BootstrapCli.php");
include_once ("../public/vendors/phpThumb/phpthumb.class.php");
// S3 setup data
$config = Zend_Registry::get('config');
$awsKey = $config->aws->s3->publickey;
$awsSecretKey = $config->aws->s3->secretkey;
$bucketName = $config->aws->s3->bucketname;
$workingDir = '/var/www/vhosts/mobilereloaded.com/httpdocs/cron/s3/';
$imgs = array();

$bucketExists = false;
$s3 = new Zend_Service_Amazon_S3($awsKey, $awsSecretKey);
$s3->setHttpClient(new Zend_Http_Client(null, array('adapter' => 'Zend_Http_Client_Adapter_Curl')));
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
          /*
          //$appId = 14449;
          $chapProd = new Api_Model_ChapProducts();
          
          $appIds = $chapProd->getNewestProductIds(8056, 10000);
          foreach ($appIds as $appId) {

          //mkdir($workingDir.$appId['product_id'],0766);
          Zend_Debug::dump("Prodcut id = " . $appId['product_id']);
          $product = new Model_Product();
          //Get the prdocut details of the app
          $appDetails = $product->getProductDetailsById($appId['product_id'], TRUE);
          Zend_Debug::dump($appDetails['thumb']);
          Zend_Debug::dump($appDetails['screenshots']);
          Zend_Debug::dump("============================================");


          // Flatten screenshot array
          global $imgs;
          $imgs = array();
          array_walk_recursive($appDetails['screenshots'], 'test_print');
          Zend_Debug::dump($imgs);

          // Thumbnail image resize
          $thumbnail_width = 80;
          $thumbnail_height = 80;
          $productFileTypes = new Model_ProductFileTypes();
          $fileMime = $productFileTypes->getMimeByFile($appDetails['thumb']);

          Zend_Debug::dump("Creating thumb nail = " . $appDetails['thumb']);
          $phpThumb = new phpThumb();
          $phpThumb->resetObject();
          $phpThumb->setSourceFilename($appDetails['thumb']);
          $phpThumb->setParameter('config_document_root', '/var/www/vhosts/mobilereloaded.com/httpdocs/public');
          $phpThumb->setParameter('config_cache_directory', './cache/');
          $phpThumb->setParameter('w', $thumbnail_width);
          $phpThumb->setParameter('h', $thumbnail_height);
          $phpThumb->setParameter('aoe', '0');
          $phpThumb->setParameter('fltr', 'ric|20|20');
          $phpThumb->setParameter('q', '100');

          $phpThumb->setParameter('config_output_format', $fileMime);
          $phpThumb->setParameter('config_imagemagick_path', '/usr/local/bin/convert');
          $phpThumb->setParameter('config_allow_src_above_docroot', true);

          // Create dir
          Zend_Debug::dump("Creating directory ======" . $workingDir . $appId['product_id'] . '/thumbnails/' . $thumbnail_width . 'x' . $thumbnail_height);
          mkdir($workingDir . $appId['product_id'] . '/thumbnails/' . $thumbnail_width . 'x' . $thumbnail_height, 0766, true);

          $output_filename = $workingDir . $appId['product_id'] . '/thumbnails/' . $thumbnail_width . 'x' . $thumbnail_height . '/' . $appDetails['thumb_name'];

          if ($phpThumb->GenerateThumbnail()) {
          Zend_Debug::dump($output_filename);
          Zend_Debug::dump($phpThumb->RenderToFile($output_filename));
          $phpThumb->CleanUpCacheDirectory();
          } else {
          die("==============" . $phpThumb->phpThumbDebug());
          }
          $phpThumb->OutputThumbnail();

          // Screenshot image resize
          $screenshot_width = 200;
          $screenshot_height = 270;
          Zend_Debug::dump("Creating directory ======" . $workingDir . $appId['product_id'] . '/screenshot/' . $screenshot_width . 'x' . $screenshot_height);
          mkdir($workingDir . $appId['product_id'] . '/screenshot/' . $screenshot_width . 'x' . $screenshot_height, 0766, true);

          $productFileTypes = new Model_ProductFileTypes();
          foreach ($imgs as $image) {
          Zend_Debug::dump("Creating screenshot = " . $image);
          $imageName = basename($image);
          $fileMime = $productFileTypes->getMimeByFile($imageName);
          $phpThumb->resetObject();
          $phpThumb->setSourceFilename($image);
          $phpThumb->setParameter('config_document_root', '/var/www/vhosts/mobilereloaded.com/httpdocs/public');
          $phpThumb->setParameter('config_cache_directory', './cache/');
          $phpThumb->setParameter('w', $screenshot_width);
          $phpThumb->setParameter('h', $screenshot_height);
          $phpThumb->setParameter('aoe', '0');
          $phpThumb->setParameter('fltr', 'ric|0|0');
          $phpThumb->setParameter('q', '100');

          $phpThumb->setParameter('config_output_format', $fileMime);
          $phpThumb->setParameter('config_imagemagick_path', '/usr/local/bin/convert');
          $phpThumb->setParameter('config_allow_src_above_docroot', true);

          $output_filename = $workingDir . $appId['product_id'] . '/screenshot/' . $screenshot_width . 'x' . $screenshot_height . '/' . $imageName;

          if ($phpThumb->GenerateThumbnail()) {
          Zend_Debug::dump($output_filename);
          Zend_Debug::dump($phpThumb->RenderToFile($output_filename));
          $phpThumb->CleanUpCacheDirectory();
          } else {
          die("==============" . $phpThumb->phpThumbDebug());
          }
          $phpThumb->OutputThumbnail();
          }
          }
        */
        
        // S3 uploading
        Zend_Debug::dump("S3 uploading ............");
        $dir = $workingDir;
        $files1 = directoryToArray($dir, true, false, $listFiles = true, '');
        foreach ($files1 as $ff) {
            Zend_Debug::dump($ff);

            $productFileTypes = new Model_ProductFileTypes();
            $fileMime = $productFileTypes->getMimeByFile($ff);
            $ffpath = explode($workingDir, $ff);
            Zend_Debug::dump("From ==========" . $ffpath[1]);
            Zend_Debug::dump("To ==========" . $bucketName . '/productimg' . $ffpath[1]);
            try {
                // Set permissions
                $perms = array(
                    Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
                    Zend_Service_Amazon_S3::S3_CONTENT_TYPE_HEADER => $fileMime
                );
                $putObject = $s3->putObject(
                        $bucketName . '/productimg' . $ffpath[1], fopen($ff, 'r'), $perms
                );
                unlink($ff);
            } catch (Exception $ex) {
                echo $ex->getMessage();
            }
        }
       
    }
} catch (Exception $ex) {
    echo $ex->getMessage();
}

function test_print($item, $key) {
    global $imgs;
    array_push($imgs, $item);
}

function directoryToArray($directory, $recursive = true, $listDirs = false, $listFiles = true, $exclude = '') {
    $arrayItems = array();
    $skipByExclude = false;
    $handle = opendir($directory);
    if ($handle) {
        while (false !== ($file = readdir($handle))) {
            preg_match("/(^(([\.]){1,2})$|(\.(svn|git|md))|(Thumbs\.db|\.DS_STORE))$/iu", $file, $skip);
            if ($exclude) {
                preg_match($exclude, $file, $skipByExclude);
            }
            if (!$skip && !$skipByExclude) {
                if (is_dir($directory . DIRECTORY_SEPARATOR . $file)) {
                    if ($recursive) {
                        $arrayItems = array_merge($arrayItems, directoryToArray($directory . DIRECTORY_SEPARATOR . $file, $recursive, $listDirs, $listFiles, $exclude));
                    }
                    if ($listDirs) {
                        $file = $directory . DIRECTORY_SEPARATOR . $file;
                        $arrayItems[] = $file;
                    }
                } else {
                    if ($listFiles) {
                        $file = $directory . DIRECTORY_SEPARATOR . $file;
                        $arrayItems[] = $file;
                    }
                }
            }
        }
        closedir($handle);
    }
    return $arrayItems;
}
?>

?>
