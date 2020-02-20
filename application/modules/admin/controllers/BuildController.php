<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BuildController
 *
 * @author Administrator
 */
class Admin_BuildController extends Nexva_Controller_Action_Cp_MasterBuildController {

    public function preDispatch() {
        parent::preDispatch();
    }

    public function init() {
        parent::init();
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/js/uploadfile.js'); 
    }

    public function saveAction() {
        parent::saveAction();
    }

    public function deleteAction() {
        // @TODO : add S3 file delete on this
        $productId = $this->_request->productid;
        $buildId = $this->_request->build;
        // check validations
        $product = new Model_Product();
        $formBasic = $product->getProductDetailsById($productId);

        $prodBuilds = new Model_ProductBuild();
        $prodBuilds->delete('id = ' . $buildId);
        $this->_flashMessenger->addMessage(array('info' => 'Successfully Deleted!.'));
        $this->_redirect("/build/show/id/$productId");
    }

    public function filedeleteAction() {
        // @TODO : add proper valitaions to get the correct user validaiton
        $fileId = $this->_getParam('id');
        $buildId = $this->_getParam('bid');
        $productId = $this->_getParam('pid');
        // get file name
        $productFiles = new Model_ProductBuildFile();
        $object = $productFiles->getFileNameById($fileId);
        // @TODO : Delete from the S3 and authenticate delete
        //get config
        $config = Zend_Registry::get('config');
        // delete from local file system
        $targetPath = $config->nexva->applicaiton->fileUploadDirectory . '/' . $productId . '/';
        @unlink($targetPath . $object);

        $awsKey = $config->aws->s3->publickey;
        $awsSecretKey = $config->aws->s3->secretkey;
        $bucketName = $config->aws->s3->bucketname;
        $s3 = new Zend_Service_Amazon_S3($awsKey, $awsSecretKey);

        $delete = $s3->removeObject($bucketName . '/productfile/' . $productId . '/' . $object);
        if ($delete) {
            $this->_flashMessenger->addMessage(array('info' => "File deleted successfully"));
            $productFiles->delete('id = ' . $fileId . ' and build_id = ' . $buildId);
        }
        $this->_redirect("build/create/productid/$productId/build/$buildId");
    }

}

?>
