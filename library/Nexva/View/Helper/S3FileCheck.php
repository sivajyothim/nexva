<?php

/**
 * Private URL for S3 objects.
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Nexva_View_Helper_S3FileCheck extends Zend_View_Helper_Abstract {

    /**
     *
     * @param <type> $bucket
     * @param <type> $object
     * @param <type> $lifetimeInSeconds
     * @return <type>
     */
    public function S3FileCheck($object) {

    	$config = Zend_Registry::get('config');
        $awsKey = $config->aws->s3->publickey;
        $awsSecretKey = $config->aws->s3->secretkey;
        $bucketName = $config->aws->s3->bucketname;
        $defaultS3Url = $config->aws->s3->defaulturl;

      
        if (strlen($object) == 0)
            return;

        if (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $object))
            return true;
        // hardocde productfile
        $object = $bucketName . '/productfile/' . $object;

        $s3 = new Zend_Service_Amazon_S3($awsKey, $awsSecretKey);
	
        $fileExist = $s3->isObjectAvailable ( $object );
        
        return $fileExist;
        
   
//        print_r($s3->getInfo('development.applications.nexva.com/productfile/6925/CallbyName.cod'));
//        if (!($s3->getInfo($object)))
//            return false;
//            return '<img alt="File Not Found" src="<?php echo ADMIN_PROJECT_BASEPATH;?>assets/img/global/icons/thumb_down.png" title="File Not Found">';
//        else
//            return true;
//            return '<img alt="File Exists" src="<?php echo ADMIN_PROJECT_BASEPATH;?>assets/img/global/icons/thumb_up.png" title="File Exists">';
    }

}

?>