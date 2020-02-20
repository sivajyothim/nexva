<?php
/**
 * 
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */

class Nexva_Util_S3_S3 extends Zend_Service_Amazon_S3 {


    /**
     * Constructor
     *
     * @param string $accessKey
     * @param string $secretKey
     * @param string $region
     */
    public function __construct($accessKey=null, $secretKey=null, $region=null) {
        parent::__construct($accessKey, $secretKey, $region);
    }
    /**
     * Upload an object by a PHP string
     *
     * @param  string $source Object name
     * @param  string $destination   Object
     * @param  array  $meta   Metadata
     * @return boolean
     */
    public function copyObject($source, $destination, $meta=null) {
//        $object = $this->_fixupObjectName($data);
        $headers = (is_array($meta)) ? $meta : array();

        $headers['x-amz-copy-source'] = urlencode($source);
        $headers['x-amz-metadata-directive'] = 'REPLACE';

//        if (!isset($headers[self::S3_CONTENT_TYPE_HEADER])) {
//            $headers[self::S3_CONTENT_TYPE_HEADER] = self::getMimeType($object);
//        }

        $response = $this->_makeRequest('PUT', $destination, null, $headers);

        // Check the MD5 Etag returned by S3 against and MD5 of the buffer
        if ($response->getStatus() == 200) {
            return true;
        }
        return false;
    }
}
?>
