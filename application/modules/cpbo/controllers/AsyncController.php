<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Cpbo_AsyncController extends Nexva_Controller_Action_Cp_MasterController {

    // TODO : add authentications on predispatch and send a JSON error

    public function preDispatch() {
        if ($this->_request->getActionName() != "upload") {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
                if ($this->_request->getActionName() != "login") {
                    $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                    $session = new Zend_Session_Namespace('lastRequest');
                    $session->lastRequestUri = $requestUri;
                    $session->lock();
                }
                if ($this->_request->getActionName() != "login")
                    $this->_redirect('/user/login');
            }
        }
    }

    public function init() {
        
    }

    /**
     * Get the devices list from the database
     * @param <type> $css
     * @return <type>
     */
    public function getdevicesAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        $query = $this->_getParam('q');
        $search = $this->_getParam('search');
        $css = 'all';
        switch ($query) {
            case 'all':
                $output = $this->getAllDevies($search);
                break;
            case 'search':
                $output = $this->searchDevices($search);
                break;
            case 'attributes':
                $output = $this->attribFilterDevices($search);
                break;
            case 'saved': // get selected data when edit
                $product = new Model_ProductBuild();
                $output = $product->getSelectedDevices($search);
                break;
            default:
                $output = $this->getDemoDevices();
                break;
        }
        echo Zend_Json_Encoder::encode($output);
        die();
//        return $output;
    }

    /**
     * Get list of demo products
     * @return <type>
     */
    private function getDemoDevices() {
        for ($i = 1; $i < 20; $i++) {
            $phones[$i]['id'] = $i;
            $phones[$i]['img'] = 'http://update.nexva.com/admin/assets/img/phones/nokia_7610.jpg';
            $phones[$i]['phone'] = 'Nokia 7610';
            $phones[$i]['css'] = $css;
            $phones[$i]['info'] = array('Support MP3', 'Wifi Enabled', 'Display 128X128');
        }
        return $phones;
    }

    private function attribFilterDevices($search) {
        $devices = new Model_Device();
        return $devices->attribFilterDevices($search);
    }

    /**
     * Search devices
     */
    private function searchDevices($keywords) {
        $keywords = explode("+", $keywords);
        $devicesAttributes = array();
        foreach ($keywords as $key => $keyword) {
            $keyword = trim($keyword);
            $deviceMdl = new Model_Device();
            $resultSet = $deviceMdl->findDevicesByKeyword($keyword, 100);
            foreach ($resultSet as $device) {
                $deviceAttributes = array();
                $deviceAttributes = $deviceMdl->getDeviceAttributesById($device->device_id);
                $devicesAttributes = array_merge($devicesAttributes, $deviceAttributes);
            }
        }
        return $devicesAttributes;
    }

    /**
     *
     * @return <type>
     */
    private function getAllDevies() {
        $table = new Model_Device();
        return $table->getAllDevies();
    }

    /**
     * Get Device attributes
     * @param <type> $resultSet
     * @return <type>
     */
    private function getDeviceAttributes($resultSet, $override = false) {
        foreach ($resultSet as $row) {
            Zend_Debug::dump($row->toArray());
            die();
            $time_start = microtime(true);
            $name = $this->trimAndElipsis($row->brand . ' ' . $row->model, 100, '...');
            $phones[$row->id]['id'] = ($override) ? $row->device_id : $row->id;
            $phones[$row->id]['img'] = '/admin/assets/img/phones/nokia_7610.jpg';
            $phones[$row->id]['phone'] = $name;
            $phones[$row->id]['css'] = $css;

//            $entries[$row->id] = $row->$column;
            $attributesByProduct = $row->findDependentRowset('Model_DeviceAttributes');

            $attributes = array();
            $resolution = array();
            foreach ($attributesByProduct as $attributed) {
                $arrtibDefId = null;
//                print_r($attributed->toArray());
                // TODO : do a database synchronization with this array
                $attributeDefinitions = array('Device OS', 'Pointing Method', 'Device OS Version', 'Support MP3',
                  'Support MIDP 1.0', 'Support MIDP 2.0');
                $attributeDeff = array(1, 2, 3, 4, 5, 6);
                $arrtibDefId = $attributed->device_attribute_definition_id;
                if (!empty($attributed->value) && $arrtibDefId != 7 && $arrtibDefId != 8 && $arrtibDefId != 3 && $arrtibDefId != 1)
                    $attributes[] = str_replace($attributeDeff, $attributeDefinitions,
                            $attributed->device_attribute_definition_id);
                if ($arrtibDefId == 7 || $arrtibDefId == 8) { // device width and height
                    $resolution[] = $attributed->value;
                }
            }
            $time_end = microtime(true);
            if (is_array($resolution))
                $attributes[] = 'Resolution : ' . implode('X', $resolution);
            $attributes[] = 'Execution time :' . ($time_end - $time_start);
            $phones[$row->id]['info'] = $attributes;

            $i++;
        }
        return $phones;
    }

    /**
     *
     * @param <type> $productId
     * @return <type>
     */
    private function getDeviceAttributesById($productId) {
        $deviceTable = new Model_Device();
        return $deviceTable->getDeviceAttributesById($productId);
    }

    public function uploadAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        if (!empty($_FILES)) {

            $buildFiles = new Model_ProductBuildFile();
            $config = Zend_Registry::get('config');
            $requestPerms = $this->_request->getParams();
            $productId = $requestPerms['prod_id'];
            $buildId = isset($requestPerms['build_id']) ? $requestPerms['build_id'] : '';
            $buildName = $requestPerms['build_name'];
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $targetPath = $config->nexva->applicaiton->fileUploadDirectory . '/' . $productId . '/';
            $_FILES['Filedata']['name'] = $buildFiles->fileCreateFilename($_FILES['Filedata']['name'], $targetPath);
            $targetFile = str_replace('//', '/', $targetPath) . $buildId.'_'. $_FILES['Filedata']['name'];
            $_FILES['Filedata']['destination'] = $targetFile;

            $temp = explode(".", $_FILES["Filedata"]["name"]);
            $extension = end($temp);
            $notAllowedExts = array('php', 'pl', 'sh');

            if(in_array($extension, $notAllowedExts)) {
                header("HTTP/1.0 500 Server Error");
                die();
            }

            /*$allowedFileExtensions = array('jad','cod','sis','sisx','apk','cab','mp3','jar','ipk','wgz','prc','jpg','jpeg','gif','png','bar','txt','mp4');
            if(!in_array($extension, $allowedFileExtensions)) {
                header("HTTP/1.0 500 Server Error");
                die();
            }*/

            // $fileTypes  = str_replace('*.','',$_REQUEST['fileext']);
            // $fileTypes  = str_replace(';','|',$fileTypes);
            // $typesArray = split('\|',$fileTypes);
            // $fileParts  = pathinfo($_FILES['Filedata']['name']);
            // if (in_array($fileParts['extension'],$typesArray)) {
            // create the directory if it doesn't exist
            // @ for ignor warnings
            @mkdir(str_replace('//', '/', $targetPath), 0755, true);
            if (!move_uploaded_file($tempFile, $targetFile))
                header("HTTP/1.0 500 Server Error");
//            echo 'oki';
//            echo str_replace($_SERVER['DOCUMENT_ROOT'], '', $targetFile);
            // } else {
            // 	echo 'Invalid file type.';
            // }
            // ---------------------------------------
//        print_r($requestPerms);
//        return;
            if ($this->_request->isPost()) {
                $product = new Model_Product();
                $numberofBuilds = $product->getBuilds($productId);
                if ($numberofBuilds->count() == 0) {
                    $product->setStatus('NOT_APPROVED', $productId);
                }
                // create the build if not exists
                // add build name to database
                $productBuild = new Model_ProductBuild();
                $data = array('id' => (!empty($buildId) && $buildId != 'undefined' ) ? $buildId : null, 'product_id' => $productId, 'name' => $buildName);
                $newBuildId = $productBuild->save($data);
                $buildId = (!empty($buildId) && $buildId != 'undefined' ) ? $buildId : $newBuildId;

                // creating files entries associate with builds

                $fileName = $_FILES['Filedata']['name'] = str_replace(' ', '_', $_FILES['Filedata']['name']);
//            $upload->addFilter('Rename', $fileName);
                // construct the form values array
                $fileVal = array('id' => null, 'build_id' => $buildId, 'filename' => $buildId.'_'.$fileName, 'filesize' => $_FILES['Filedata']['size']);
                $fileId = $buildFiles->save($fileVal, $buildFiles);
                // push to S3
                // adding product id to create object with product id
//            $fileInfo['file']['productId'] = $productId;
                $_FILES['Filedata']['productId'] = $productId;
                $_FILES['Filedata']['buildId'] = $buildId;
                $_FILES['Filedata']['fileId'] = $fileId;
                // sending file to S3
//            $this->pushToS3($fileInfo['file']);
//                $this->pushToS3($_FILES['Filedata']);
//            echo "pid/$productId/bid/$buildId/id/$fileId";
//
                // Create job queue
                $options = array(
                  'name' => 's3_file_sync',
                  'driverOptions' => array(
                    'host' => $config->resources->multidb->default->host,
                    'username' => $config->resources->multidb->default->username,
                    'password' => $config->resources->multidb->default->password,
                    'dbname' => $config->resources->multidb->default->dbname,
                    'type' => 'pdo_mysql'
                  )
                );
                
                $_FILES['Filedata']['name'] = $buildId.'_'.$fileName;
                $queue = new Zend_Queue('Db', $options);
//                $queue->send('test'); //                $queue->createQueue('file');
                $queue->send(serialize($_FILES['Filedata']));

                echo Zend_Json_Encoder::encode(
                    array(
                      'build_id' => $buildId,
                      'product_id' => $productId,
                      'file_id' => $fileId
                ));
                die();
            }
        }
    }

    public function uploadFilesAction(){

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        if(isset($_FILES['upl']) && $_FILES['upl']['error'] == 0 && $this->_request->isPost())
        {
            $buildFiles = new Model_ProductBuildFile();
            $config = Zend_Registry::get('config');
            $requestPerms = $this->_request->getParams();
            $productId = $_POST['product_id'];
            $buildId = isset($_POST['build_id']) ? $_POST['build_id'] : '';
            $buildName = $_POST['build_name'];
            $tempFile = $_FILES['upl']['tmp_name'];
            $targetPath = $config->nexva->applicaiton->fileUploadDirectory . '/' . $productId . '/';
            $_FILES['upl']['name'] = $buildFiles->fileCreateFilename($_FILES['upl']['name'], $targetPath);
            $targetFile = str_replace('//', '/', $targetPath) . $buildId.'_'. $_FILES['upl']['name'];
            $_FILES['upl']['destination'] = $targetFile;

            $temp = explode(".", $_FILES["upl"]["name"]);
            $extension = end($temp);
            $notAllowedExts = array('php', 'pl', 'sh', 'zip', 'rar','txt','wgz','prc');

            if(in_array($extension, $notAllowedExts)) {
                echo Zend_Json_Encoder::encode(
                array(
                    'status' => "file format error"
                ));
                die();
            }

            $allowedFileExtensions = array('jad','cod','sis','sisx','apk','cab','mp3','jar','ipk','jpg','jpeg','gif','png','bar','mp4', 'pdf');
            if(!in_array($extension, $allowedFileExtensions)) {
                 echo Zend_Json_Encoder::encode(
                array(
                    'status' => "file format error"
                ));
                die();
            }

            @mkdir(str_replace('//', '/', $targetPath), 0755, true);
            if (!move_uploaded_file($tempFile, $targetFile))
                header("HTTP/1.0 500 Server Error");

            $product = new Model_Product();
            $numberofBuilds = $product->getBuilds($productId);
            if ($numberofBuilds->count() == 0) {
                $product->setStatus('NOT_APPROVED', $productId);
            }

            // create the build if not exists
            // add build name to database
            $productBuild = new Model_ProductBuild();
            $data = array('id' => (!empty($buildId) && $buildId != 'undefined' ) ? $buildId : null, 'product_id' => $productId, 'name' => $buildName);
            $newBuildId = $productBuild->save($data);
            $buildId = (!empty($buildId) && $buildId != 'undefined' ) ? $buildId : $newBuildId;

            // creating files entries associate with builds

            $fileName = $_FILES['upl']['name'] = str_replace(' ', '_', $_FILES['upl']['name']);
            // $upload->addFilter('Rename', $fileName);
            // construct the form values array
            $fileVal = array('id' => null, 'build_id' => $buildId, 'filename' => $buildId.'_'.$fileName, 'filesize' => $_FILES['upl']['size']);
            $fileId = $buildFiles->save($fileVal, $buildFiles);
            // push to S3
            // adding product id to create object with product id
//            $fileInfo['file']['productId'] = $productId;
            $_FILES['upl']['productId'] = $productId;
            $_FILES['upl']['buildId'] = $buildId;
            $_FILES['upl']['fileId'] = $fileId;
            // sending file to S3
//            $this->pushToS3($fileInfo['file']);
//                $this->pushToS3($_FILES['Filedata']);
//            echo "pid/$productId/bid/$buildId/id/$fileId";
//
            // Create job queue
            $options = array(
                'name' => 's3_file_sync',
                'driverOptions' => array(
                    'host' => $config->resources->multidb->default->host,
                    'username' => $config->resources->multidb->default->username,
                    'password' => $config->resources->multidb->default->password,
                    'dbname' => $config->resources->multidb->default->dbname,
                    'type' => 'pdo_mysql'
                )
            );

            $_FILES['upl']['name'] = $buildId.'_'.$fileName;
            $queue = new Zend_Queue('Db', $options);
            //$queue->send('test');
            //$queue->createQueue('file');
            $queue->send(serialize($_FILES['upl']));

            echo Zend_Json_Encoder::encode(
                array(
                    'build_id' => $buildId,
                    'product_id' => $productId,
                    'file_id' => $fileId
                ));
            die();
        }else{
            if($this->_request->isPost()){
                echo Zend_Json_Encoder::encode(
                array(
                    'status' => "error"
                ));
                die();
            }            
        }

    }

    public function deletefilesAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        $devId = $this->_getParam('id');
        $buildId = $this->_getParam('bid');
        $productDevices = new Model_ProductBuildDevices();
        $productDevices->delete('device_id = ' . $devId . ' and build_id = ' . $buildId);
        echo $devId;
        die();
    }

    public function getcategoriesAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        $id = $requestPerms = $this->_request->id;
        $prodId = $requestPerms = $this->_request->product_id;

        $userModel  = new Model_User();
        $currentCp  = $userModel->find($this->_getCpId())->current();
        
        if($currentCp->chap_id == 4348 || empty($currentCp->chap_id)){
            $categoryMdl = new Model_Category();
            $categories = $categoryMdl->getCategorylist();
            echo Zend_Json_Encoder::encode($categories[$id]);
            die();
        } else {  
            $chapCategoryModel = new Pbo_Model_ChapCategories();
            $result = $chapCategoryModel->getSubCategoriesForParentCategory($id, $currentCp->chap_id);
            $subCategories = array();
            foreach ($result as $row) {
                if($row->chap_category_status){
                    $subCategories[$row->parent_id][$row->id] = $row->name;
                }
            }
            echo Zend_Json_Encoder::encode($subCategories[$id]);
            die();
        }

    }

    public function getselectedsubcatAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        $id = $requestPerms = $this->_request->id;
        $prodId = $requestPerms = $this->_request->product_id;
        $categoryMdl = new Model_ProductCategories();
        $categoryId = $categoryMdl->selectedSubCategory($prodId);
//        print_r($categories);
        echo Zend_Json_Encoder::encode($categoryId);
        die();
    }

}

?>
