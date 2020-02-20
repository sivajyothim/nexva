<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_ProductBuildFile extends Zend_Db_Table_Abstract {

    protected $_name = 'build_files';
    protected $_id = 'id';
    protected $_referenceMap = array(
      'Model_ProductBuild' => array(
        'columns' => array('build_id'),
        'refTableClass' => 'Model_ProductBuild',
        'refColumns' => array('id')
      ),
    );

    function __construct() {
        parent::__construct();
    }

    public function save($data) {
        if (null === ($id = $data['id'])) {
            unset($data['id']);
            return $this->insert($data);
        } else {
            $this->update($data, array('id = ?' => $id));
            return false;
        }
    }

    public function getFilesByBuid($buildId) {
        $rows = $this->fetchAll(
                    $this->select()
                    ->where('build_id = ?', $buildId)
        );
        if (isset($rows))
            return $rows;
        else
            return FALSE;
    }

    public function getFileNameById($id) {
        $row = $this->find($id);
        return $row->current()->filename;
    }

    public function safeDelete($buildId, $filename) {
        return $this->delete('filename = ' . $filename . ' AND build_id = ' . $buildId);
    }

    /**
     * Put files to S3
     * @param <type> $file
     */
    public function pushToS3($file) {
        // local file nale
        $fileName = $file['name'];
        
        
        Zend_Debug::dump($fileName);
        
//        $filePath = $file['destination'];
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
                // Set permissions
                
                Zend_Debug::dump($fileMime. 'vvv');
                
                $perms = array(
                  Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PRIVATE,
                  Zend_Service_Amazon_S3::S3_CONTENT_TYPE_HEADER => $fileMime
                );
                $putObject = $s3->putObject(
                        $bucketName . '/productfile/' . $productId . '/' . $fileName,
//                                $filePath . '/' . $fileName,
                        fopen($file['destination'], 'r'),
                        $perms
                );
                
                Zend_Debug::dump($file, 'bbb');
                              Zend_Debug::dump($putObject, 'sss');
                
                if ($putObject){
                    // if success then remove the load file
                    @unlink($file['destination']);
                    return true;
                }
////                    $this->_flashMessenger->addMessage(array('info' => 'File <i>' . $fileName . '</i> Saved on S3!.'));
                else
                    return false;
//                    $this->_flashMessenger->addMessage(array('error' => 'S3 file <i>' . $fileName . '</i> upload failled!.'));
            }
        } catch (Exception $ex) {
            // TODO : set as messages
//            $this->_flashMessenger->addMessage(array('error' => 'S3 file upload failled!.'));
            echo $ex->getMessage();
        }
    }

    /**
     *
     * @param <type> $basename filename
     * @param <type> $directory firectory path
     * @return string
     */
    public function fileCreateFilename($basename, $directory) {
        $dest = $directory . '/' . $basename;

        if (file_exists($dest)) {
            // Destination file already exists, generate an alternative.
            if ($pos = strrpos($basename, '.')) {
                $name = substr($basename, 0, $pos);
                $ext = substr($basename, $pos);
            } else {
                $name = $basename;
            }

            $counter = 0;
            do {
                $dest = $directory . '/' . $name . '_' . $counter++ . $ext;
            } while (file_exists($dest));
        }

        return pathinfo($dest, PATHINFO_BASENAME);
    }

}

?>