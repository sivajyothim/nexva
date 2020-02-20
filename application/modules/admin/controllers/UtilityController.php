<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Admin_UtilityController extends Zend_Controller_Action{


    function striptagsAction(){

        $productModel    =   new Model_Product();

        $products  =   $productModel->fetchAll();

        foreach($products as $product){
             $new_string = ereg_replace("[^A-Za-z0-9]", "",   $product->name);

            echo  "Operation :".$product->name."  now: ".$new_string."  id:".$product->id."<br />";
             $productModel->update(array("name" => $new_string), "id=".$product->id);

            
        }
       

    }
    
    
  public function init() {
      
    $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages.js');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/js/admin.js');
    $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
    $this->view->flashMessenger = $this->_flashMessenger;
    
  }
  
    /*Don't delete this action. This action used for Phone number uploads.*/
    public function uploadNumbersAction() {
    }
    
    public function uploadPhoneNumbersAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        if (isset($_FILES['upl']) && $_FILES['upl']['error'] == 0 && $this->_request->isPost()) {
            $tempFile = $_FILES['upl']['tmp_name'];
            $targetPath = 'phone_numbers/';
            $targetFile = str_replace('//', '/', $targetPath).'phone_numbers.csv';
            $_FILES['upl']['destination'] = $targetFile;

            $temp = explode(".", $_FILES["upl"]["name"]);
            $extension = end($temp);            

            $allowedFileExtensions = array('csv');
            if (!in_array($extension, $allowedFileExtensions)) {
                echo Zend_Json_Encoder::encode(
                array(
                    'status' => "file format error"
                ));
                die();
            }

            if (!move_uploaded_file($tempFile, $targetFile)) {
                echo Zend_Json_Encoder::encode(
                array(
                    'status' => "upload error"
                ));
                die();
            }

            echo Zend_Json_Encoder::encode(
                array(
                    'status' => "success"
                ));
            die();
        }else{
             if($this->_request->isPost()){
                echo Zend_Json_Encoder::encode(
                array(
                    'status' => "error"
                ));
            }
            die();
        }
    }
    
    public function sendsmsAction() {

        if ($this->_request->isPost()) {
            
            $chapId = $this->_request->chapid; // MTN nigeria http://nextapps.mtnonline.com/
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
            $massage = $this->_request->message;
            $pgType = $pgDetails->gateway_id;
            $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
            /*get phone numbers*/
            $phone_numbers = explode(',',str_replace(' ', '', $this->_request->phone_numbers));            
            /* end */
            
            $smsLog = new Api_Model_SmsLog();
            try{
            
                foreach ($phone_numbers as $mobileNumber) {
                    //$mNumber = str_replace(' ', '', $mobileNumber);
                    if (!empty($mobileNumber)) {
                        $result = $pgClass->sendSms($mobileNumber, $massage, $chapId);
                        if ($result == 1) {
                            $smsLog->loggedSMS($massage, $mobileNumber, 'SEND');
                        } else {
                            $smsLog->loggedSMS($massage, $mobileNumber, 'FAIL');
                        }
                    }
                }
            
              $this->view->message = array('Messages are sent successfuly', 'success');  
           
            }catch(Exception $e){
                $this->view->message = array($e->getMessage(), 'error');
            }
            
        }
    }

}
?>
