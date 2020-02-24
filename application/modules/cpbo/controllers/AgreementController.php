<?php

class Cpbo_AgreementController extends Nexva_Controller_Action_Cp_MasterController {


  function preDispatch() {

    $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/facebox/facebox.css');
    $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/facebox/facebox.js');
    if (!Zend_Auth::getInstance()->hasIdentity()) {

      $skip_action_names =
          array(
            "login",
            "register",
            "forgotpassword",
            "resetpassword"
      );

      if (!in_array($this->getRequest()->getActionName(), $skip_action_names)) {
        $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
        $session = new Zend_Session_Namespace('lastRequest');
        $session->lastRequestUri = $requestUri;
        $session->lock();
      }

      if (!in_array($this->getRequest()->getActionName(), $skip_action_names)) {
        $this->_redirect('/user/login');
      }
    }

  }

  function indexAction() {
    
  }

  function viewAction() {

    $this->view->title = "Agreement";
    try {
      $file = $this->checkCpAgreementExists();

      if (false != $file) {
        $this->view->agreement_special = "http://" . $_SERVER['HTTP_HOST'] . "/" . current($file);
        $content_type = $this->getMime(current($file));
        
       // echo   $this->view->agreement_special.' '. $content_type;
       // die();

        $this->view->content_type = $content_type;
      } else {
        $this->view->agreement_normal = $this->getReplacedContract();
      }
    } catch (Exception $ex) {

      $ex->getMessage();
    }
    //header("Content-type:application/pdf");
  }

  /**
   * check public/cp/static/agreements folder for agreement document with userid attached
   * didn't create seperate table to flag users with special agreements because the count of cps is less.
   *
   * @return boolean|filename if file is not exists this will return false else reutn filename
   */
  function checkCpAgreementExists() {

    $cp = Zend_Auth::getInstance()->getIdentity()->id;
    $salt = Zend_Registry::get('config')->nexva->application->salt;
    $secure_mix = md5($cp . $salt);
    

    $file_prefix = "agreement_";
    $allowd_types = explode(",", "pdf,doc,jpg,docx,html");
    $path = 'cp/static/agreements/';
    $handle = opendir($path);
    $file_exists = array();
    if ($handle) {

      while (false !== ($file = readdir($handle))) {
        $files[] = $file;
      }


      foreach ($allowd_types as $type) {

        $file_search = $file_prefix . $secure_mix . "." . $type;

        if (in_array($file_search, $files)) {

          $file_exists[] = $path . $file_search;
        }
      }
      if (count($file_exists) <= 0) {

        return false;
      } else {
        return $file_exists;
      }
    } else {
      throw new Zend_Exception('Folder has no read permission');
      //Folder has no read permission
      //Send mail
    }
  }

  function getMime($file) {

    if (is_null($file) and '' == $file) {
      throw new Zend_Exception('File name shouldn\'t be empty');
    }


    $file_ext = substr($file, strpos($file, ".") + 1);

    switch ($file_ext) {

      case 'doc':
        $ftype = 'application/msword';
        break;

      case 'dot':
        $ftype = 'application/msword';
        break;

      case 'docx':
        $ftype = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        break;

      case 'dotx':
        $ftype = 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
        break;

      case 'jpeg':
        $ftype = 'image/jpeg';
        break;

      case 'jpg' :
        $ftype = 'image/jpeg';
        break;

      case 'jpe' :
        $ftype = 'image/jpeg';
        break;

      case 'gif' :
        $ftype = 'image/gif';
        break;

      case 'pdf' :
        $ftype = 'application/pdf';
        break;
        
      case 'html' :
        $ftype = 'text/html';
        break;


      default:
        $ftype = 'application/x-pdf';
    }
    return $ftype;
  }

  function getNormalContract() {
    $page_languages = new Model_PageLanguages();

    $contract = $page_languages->fetchRow("title = 'PARTNER CONTRACT'");

    return $contract->content;
  }

  function getReplacedContract() {
    $auth = Zend_Auth::getInstance();
    $user = new Cpbo_Model_UserMeta();
    $user->setEntityId($auth->getIdentity()->id);
    $user_det = new Cpbo_Model_User();
    $user_det = $user_det->find($auth->getIdentity()->id)->current();


    $replacements = array(
      '$date' => $user->REGISTRATION_DATE,
      '$company' => $user->COMPANY_NAME,
      '$street' => $user->ADDRESS,
      '$zip' => $user->ZIP,
      '$city' => $user->CITY,
      '$country' => $user->COUNTRY,
      '$fax' => $user->FAX,
      '$contactname' => $user->FIRST_NAME . " " . $user->LAST_NAME,
      '$email' => $user_det['email'],
      '$web' => $user->WEB,
      '$phone' => $user->TELEPHONE
    );

    $replaced_contract = strtr($this->getNormalContract(), $replacements);

    return $replaced_contract;
  }

    function confirmAgreementAction(){
        //$this->view->title = "Agreement";

        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/admin/assets/js/jquery.min.js');
        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js');
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        if(!in_array($chapId, array('585474','585480'))){
            $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages.js');
        }else{
            $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages_fr.js');
        }
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages_cp_agreement.js');


        if($this->_request->isPost())
        {
            $userId = Zend_Auth::getInstance()->getIdentity()->id;
            $agree = $this->_getParam('agree');

            if($agree == 'agree'){
                $userModel = new Cpbo_Model_User();
                //$userModel->updateAgreementConfirmation($userId);
                $data = array(
                    'agreement_sign'      => '1'
                );
                $userModel->update($data, array('id = ?' => $userId));
                $this->_redirect(CP_PROJECT_BASEPATH);
            }
        }
       
        try {
            $file = $this->checkCpAgreementExists();

            if (false != $file) {
                $this->view->agreement_special = "http://" . $_SERVER['HTTP_HOST'] . "/" . current($file);
                $content_type = $this->getMime(current($file));

                // echo   $this->view->agreement_special.' '. $content_type;
                // die();

                $this->view->content_type = $content_type;
            } else {
                $this->view->agreement_normal = $this->getReplacedContract();
            }
        } catch (Exception $ex) {

            $ex->getMessage();
        }
    }

}

?>