<?php

/**
 * Private URL for S3 objects.
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Nexva_View_Helper_S3Url extends Zend_View_Helper_Abstract {

  /**
   *
   * @param <type> $bucket
   * @param <type> $object
   * @param <type> $lifetimeInSeconds
   * @return <type>
   */
  public function S3Url($object, $lifetimeInSeconds = 180, $isPrivate = true) {

    $config = Zend_Registry::get('config');
    $awsKey = $config->aws->s3->publickey;
    $awsSecretKey = $config->aws->s3->secretkey;
    $bucketName = $config->aws->s3->bucketname;
    $defaultS3Url = $config->aws->s3->defaulturl;
    $object = 'productfile/' . $object;

    if (strlen($object) == 0)
      return;
    //$lifetimeInSeconds  = strtotime("+$lifetimeInSeconds seconds");
    $lifetimeInSeconds = time() + $lifetimeInSeconds;
    $stringToSign = "GET\n\n\n{$lifetimeInSeconds}\n/{$bucketName}/{$object}";
    $signature = urlencode(base64_encode((hash_hmac("sha1", utf8_encode($stringToSign), $awsSecretKey, TRUE))));

    $authenticationParams = "AWSAccessKeyId=" . $awsKey;
    $authenticationParams.= "&Expires={$lifetimeInSeconds}";
    $authenticationParams.= "&Signature={$signature}";

    // Create the return filepath. We add the bucket file path if
    // the a custom domain is not being used
    // Create the return filepath.
    // We add the bucket file path if http(s)://s3.amazonaws.com is being used as the s3 url

    preg_match('@^(?:https?://)?([^/]+)@i', $defaultS3Url, $domain);
    if ($domain[1] == 's3.amazonaws.com') {
      $object = $bucketName . '/' . $object;
    }
//    if ($object == 'production.applications.nexva.com/productfile/7432/bestbuy.jad') {
//      return $defaultS3Url . $object;
////      exit;
//    }
////      return $defaultS3Url . $object;
//    else
      return $defaultS3Url . $object . "?{$authenticationParams}";
  }

}

?>