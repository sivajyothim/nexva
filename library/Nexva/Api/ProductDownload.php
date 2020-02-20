<?php
//App Download Class
class Nexva_Api_ProductDownload 
{
     /**
     * 
     * Returns the S3 URL of the relevant Build.
     * @param $appId App Id
     * @param $buildId Build Id
     * returns S3 file URL
     */
    public function getBuildFileUrl($appId, $buildId) {
        //Fetch the File name to be downloaded
        
        $productName = $this->getDownloadFile($buildId);
        
        $s3FileName = "$appId/$productName";
        


        //Check if the file exists in S3server 
        $s3FileExists = $this->s3FileExist($s3FileName);
        
        


        if($s3FileExists)
        {
            //set S3 headers
            $this->setS3FileHeaders($s3FileName);
            // construct the pvt url

            $s3Url = new Nexva_View_Helper_S3Url();
            $url = $s3Url->S3Url($s3FileName, 25200);

            return $url;
        }
        else 
        {
            return null;
        }
        
    }

    public function s3FileExist($fileName) {
        
   
        $config = Zend_Registry::get('config');
        $awsKey = $config->aws->s3->publickey;
        $awsSecretKey = $config->aws->s3->secretkey;
        $bucketName = $config->aws->s3->bucketname;
        $defaultS3Url = $config->aws->s3->defaulturl;

        $s3 = new Zend_Service_Amazon_S3($awsKey, $awsSecretKey);

        $object = $bucketName . '/productfile/' . $fileName;
        $fileExist = $s3->isObjectAvailable($object);
        


        return $fileExist;
    }

    /**
     * Set HTTP headers of the files at the S3
     * @param <type> $file
     */
    public function setS3FileHeaders($file) {
        // @TODO : remove hardcode values
        // hardocde productfile
        $file = 'productfile/' . $file;

        // get the mime type for android
        $fileMimeData = new Model_ProductFileTypes();
        $fileMime = $fileMimeData->getMimeByFile($file);
        // add the the cron job queue
        // @TODO : add validations
        // @TODO : update the field id it is there
        $s3PublicFiles = new Model_S3PublicFile();
        $id = $s3PublicFiles->getIdByFilename($file);
        $data = array('id' => $id, 'filename' => $file, 'time' => new Zend_Db_Expr('NOW()'));
        $s3PublicFiles->save($data);

        // load S3 configs
        $config = Zend_Registry::get('config');
        $awsKey = $config->aws->s3->publickey;
        $awsSecretKey = $config->aws->s3->secretkey;
        $bucketName = $config->aws->s3->bucketname;
        try {
            $s3 = new Nexva_Util_S3_S3($awsKey, $awsSecretKey);
            $meta = array(
                Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
                Zend_Service_Amazon_S3::S3_CONTENT_TYPE_HEADER => $fileMime
            );
            $error = $s3->copyObject($bucketName . '/' . $file, $bucketName . '/' . $file, $meta);
        } catch (Exception $ex) {
            
        }
    }

    public function getDownloadFile($buildId) {

        $buildProd = new Model_ProductBuild();


        $files = $buildProd->getFiles($buildId);
        
        
       
        
        $downloadablefileTypes = array('jad', 'apk', 'prc', 'sis', 'sisx', 'cab', 'mp3', 'alx', 'ipk', 'wgz', 'jpg', 'jpeg', 'png', 'gif', 'bar', 'jar', 'pdf');
        
        $downloadFile = '';
        foreach ($files as $file) {
            $filename = $file->filename;
            if (in_array(end(explode(".", $filename)), $downloadablefileTypes)) {
                $downloadFile = $filename;
            }
        }
        return $downloadFile;
    }

}
