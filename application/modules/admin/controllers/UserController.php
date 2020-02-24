<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Admin_UserController extends Zend_Controller_Action {
	
	protected $_flashMessenger = null;
	
	function preDispatch() {
		
		
		if (! Zend_Auth::getInstance ()->hasIdentity ()) {
			
			if ($this->_request->getActionName () != "login") {
				$requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
				$session = new Zend_Session_Namespace ( 'lastRequest' );
				$session->lastRequestUri = $requestUri;
				$session->lock ();
			}
//			if ($this->_request->getActionName () != "login")
//				$this->_redirect ( ADMIN_PROJECT_BASEPATH.'user/login' );
		}
	}

  /* Initialize action controller here */

  public function init() {
    // include Ketchup libs
    $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css');
//        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/ketchup/js/jquery.min.js');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages.js');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js');
    // adding admin JS file
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/js/admin.js');
    
    $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
    $this->view->flashMessenger = $this->_flashMessenger;
  }

  public function indexAction() {
    // action body
  }

    public function loginAction() {
//print_r($_SESSION);exit;
//        $this->_helper->SslValidate ();
         if ($this->getRequest()->isPost()){
        //new login code start
             $db = Zend_Registry::get('db');
      $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'email', 'password', "MD5(?)");
      $authDbAdapter->setIdentity($this->getRequest()->getParam('username'));
      $authDbAdapter->setCredential($this->getRequest()->getParam('password'));
      try {
      $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);
//     print_r($result);exit;
      if ($result->isValid()) {
          
            Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));   
            $auth = Zend_Auth::getInstance();
        $user = new Model_UserMeta();
        $user->setEntityId($auth->getIdentity()->id);
//        $this->doLogin($this->getRequest()->getParam('email'), $this->getRequest()->getParam('password'));
//            $session = new Zend_Session_Namespace('lastRequest');
//                    if (isset($session->lastRequestUri)) {
//                        $newpage = $session->lastRequestUri;
//                        $session->lastRequestUri = NULL;
//                        $this->_redirect(ADMIN_PROJECT_BASEPATH.'newtest');
//                        return;
//                    }
                    $this->_redirect(ADMIN_PROJECT_BASEPATH);
      
        
            
         }
        
    
       
        } catch (Exception $e) {}

            $this->view->error = "The username/password combination you supplied is not valid.";
        }

         $this->_helper->layout->setLayout('admin_login');
    }
      
  public function logoutAction() {
    $this->_helper->viewRenderer->setNoRender(true);

    Zend_Auth::getInstance()->clearIdentity();
    $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/login');
  }

  public function forgotpasswordAction() {
    $this->_helper->viewRenderer->setNoRender(true);

    $user = new Admin_Model_User();
    $row = $user->fetchRow("username = 'admin'");

    //echo "<pre>"; print_r($row->password); die();

    $config = Zend_Registry::get('config');
    $timeout = strtotime("+1 hour"); //1 hour until the link expires

    $otp = new Nexva_Util_OTP_OneTimePassword($row->id, $row->password);

    $otp->setSalt($config->nexva->application->salt);
    $otp->setTimeout($timeout);

    $otphash = $otp->generateOTPHash();

    $link = "http://" . $config->nexva->application->admin->url . "/user/resetpassword/id/" . $row->id . "/time/$timeout/otphash/$otphash";

    echo $link;
  }

  public function resetpasswordAction() {
    $this->_helper->viewRenderer->setNoRender(true);

    //check if the link is expired.
    //if( strtotime("now") > $this->_request->getParam('time')   ) die("Link has expired");

    $config = Zend_Registry::get('config');
    $user = new Admin_Model_User();
    $row = $user->fetchRow("id=" . $this->_request->getParam('id'));

    $otp = new Nexva_Util_OTP_OneTimePassword($this->_request->getParam('id'), $row->password);
    $otp->setSalt($config->nexva->application->salt);
    $otp->setTimeout($this->_request->getParam('time'));

    if ($otp->verifyOTPHash($this->_request->getParam('otphash')))
      die("Verified!!! - allow user to reset password here"); else
      die("not verified!!");

    //Zend_Debug::dump($this->_request);
  }
  
  /**
   * 
   * Handy search method that returns results compatible with jquery autocomplete
   */
    public function searchjsonAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        
        $q      = $this->_request->getParam('q', null);
        $type   = $this->_request->getParam('type', null);
        $mode   = $this->_request->getParam('mode', 'COMPANY_MODE');
        if ($q === false) {
            echo json_encode(array());
            return;
        }
        
        $userModel      = new Model_User();
        $results        = $userModel->getUserListByQuery(strtoupper($mode), $q, strtoupper($type));
        if($results) {
            $dataArr    = array();
            foreach ($results as $item) {
                $dataArr[]    = array ('id' => $item->id, 'label' => $item->value, 'value' => $item->value);
            }
            echo json_encode($dataArr);
        } else {
            echo json_encode(array());    
        }
    }

    function createChapAction() {
        $userModel  = new Model_User();
        $data       = array();      
        if ($this->getRequest()->isPost()) { 
            $data               = $userModel->getPopulatedArray($this->getRequest()->getParams());
            $required   = array('username'=>'', 'email'=>'', 'password'=>'');
            $errors     = Nexva_Util_Validation_Validate::validate($data, $required); 
            if (empty($errors)) {
                //add in the constant fields
                $data['payout_id']      = null;
                $data['payout_type']    = 'DEFAULT';
                $data['type']       = 'CHAP';
                $data['login_type'] = 'NEXVA';
                $data['status']     = 1;
                $data['created_date']  =  new Zend_Db_Expr('NOW()');;
                $data['password']   = md5($data['password']);
                unset($data['chap_name']); //since that's not in the user model
                $id = $userModel->insert($data);
                
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/list/tab/tab-chaps');
            } else {
                $this->view->errors = $errors;
            }
        }
        $user = $userModel->getPopulatedObject($data); 
        $user->chap_name    = (!isset($user->chap_name)) ? @$data['chap_name'] : $user->chap_name; 
        $this->view->user   = $user;
    }
    
    
    
     function editContactChapAction() {

     	 $user = new Cpbo_Model_UserMeta();
     	 $user->setEntityId($this->_request->id);
 
        if ($this->getRequest()->isPost()) { 

        	$user->CHAP_TECHNICAL_CONTACT_NAME = $this->getRequest()->getParam('technical_contact_name' ,$user->CHAP_TECHNICAL_CONTACT_NAME );
  	 		$user->CHAP_TECHNICAL_CONTACT_EMAIL = $this->getRequest()->getParam('technical_contact_email' ,$user->CHAP_TECHNICAL_CONTACT_EMAIL );
  	 		$user->CHAP_TECHNICAL_CONTACT_PHONE = $this->getRequest()->getParam('technical_contact_phone' ,$user->CHAP_TECHNICAL_CONTACT_PHONE);

        	
            $user->CHAP_ADMIN_CONTACT_NAME = $this->getRequest()->getParam('admin_contact_name' ,$user->CHAP_ADMIN_CONTACT_NAME );
  	 		$user->CHAP_ADMIN_CONTACT_EMAIL = $this->getRequest()->getParam('admin_contact_email' ,$user->CHAP_ADMIN_CONTACT_EMAIL);
  	 		$user->CHAP_ADMIN_CONTACT_PHONE = $this->getRequest()->getParam('admin_contact_phone' ,$user->CHAP_ADMIN_CONTACT_PHONE );
  	 		
  	 		$this->_flashMessenger->addMessage(array('success' => 'Successfully saved user contacts.'));
  	 		$this->_redirect(ADMIN_PROJECT_BASEPATH.'user/list/tab/tab-chaps');

        }

		$this->view->user = $user;
		$this->view->userId = $this->_request->id;
    }
    
    
    
    
    /**
     * saves chap info to the meta table.
     */
    function chapDetailsAction() {
        $id     = $this->_request->getParam('id', false);
        
        if (!$id && !$this->getRequest()->isPost()) {
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/list/tab/tab-chaps');
        }
        
        //Conveninent to do the loops. A template for the WL object. Keep it updated
        //And yes, it's misspelled. Can't fix it now since it's used everywhere
        $keys   = array(
            'WHITELABLE_URL', 'WHITELABLE_APP_FILTER', 'WHITELABLE_SITE_NAME',
            'WHITELABLE_THEME_NAME', 'WHITELABLE_FB_APPID', 'WHITELABLE_FB_SECRET',
            'WHITELABLE_ACCESS_TYPE', 'WHITELABLE_EVA_SHOW_HOME', 'WHITELABLE_EVA_SHOW_MENU',
            'WHITELABLE_EVA_SHOW_APP', 'WHITELABLE_WEB_ACCESS_URL', 'WHITELABLE_NEXLINKER_STYLE',
            'WHITELABLE_SHOW_POWERED_BY', 'WHITELABLE_URL_WEB', 'APPSTORE_APP_ID','APPSTORE_APP_URL','WHITELABLE_PLATEFORM','CP_CUSTOM_CSS',
            'PAYMENT_URL','SMS_URL','ID_PAYMENT_SMS','PASSWORD_PAYMENT_SMS','SERVICE_ID','HEADER_ENRICHMENT','SITE_ALIGNMENT','COUNTRY_CODE_TELECOMMUNICATION',
            'WHITELABLE_URL_DEV_PORTAL','WHITELABLE_IP_HDR_IDENTIFIER',
            'SYMBIAN_URL','BB10_URL','JAVA_URL','MCC','MNC'
        );
        
        $themeKeys  = array(
            'basic'     => array(
                'sitename'  => '', 
                'css'       => ''
            ),
            'header'    => array(
                'logo'  => array(
                    'path'  => '',
                    'alt'  => ''
                )
            ),
            'content'   => array(
                'menu'  => ''
            ),
            'footer'    => array(
                'site'  => '',
                'menu'  => ''
            ),
            'buttons'   => array(
                'buy'  => '',
                'download'  => ''
            )
        );
        $chap   = array();
        $whiteLabelSettings = '';
        if ($this->getRequest()->isPost()) {
            $id         = $this->_request->getParam('id', $id);

            if (!$id) {
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/list/tab/tab-chaps');
            }
            
            $params     = $this->_getAllParams();
            //Zend_Debug::dump($params);die();

            //transform the menus
            $params['theme']['content']['menu'] = $this->__getGetChapLinks($params['theme']['content']['menu']); 
            $params['theme']['footer']['menu']  = $this->__getGetChapLinks($params['theme']['footer']['menu']);

            $themeMeta   = new Model_ThemeMeta();
            $themeMeta->setEntityId($id);

            foreach ($params['wl'] as $param => $value) {
                $themeMeta->$param   = trim($value);
                //if meta_value(WHITELABLE_URL_WEB) does have a value, sets the is_partner = 1
                if(($param == 'WHITELABLE_URL_WEB') AND (!empty($value)))
                {
                    $themeMeta->setIsPartner($id,$param,$value);
                }
            }
            $themeMeta->WHITELABEL_THEME = serialize($params['theme']);

            $currentSettings = $themeMeta->WHITELABLE_SETTINGS ;
            $currentSettingsDecoded = json_decode($currentSettings);

            $currentSettingsDecoded->{'custom_css'}= $params['custom_css'];

            $themeMeta->WHITELABLE_SETTINGS    = json_encode($currentSettingsDecoded);

            $themeMeta->reloadCache($id);
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/list/tab/tab-chaps');
        } else {
            $themeMeta   = new Model_ThemeMeta();
            $themeMeta->setEntityId($id);
            
            foreach ($keys as $key) {
                $chap[$key] = $themeMeta->$key;
            }

            $themeData                      = unserialize($themeMeta->WHITELABEL_THEME);
            $themeData['footer']['menu']    = isset($themeData['footer']['menu']) ? $themeData['footer']['menu'] : '' ;
            $themeData['content']['menu']   = isset($themeData['content']['menu']) ? $themeData['content']['menu'] : '' ;
            $themeData['footer']['menu']    = $this->__stringify($themeData['footer']['menu']);
            $themeData['content']['menu']   = $this->__stringify($themeData['content']['menu']);

            $themeData                      = array_merge($themeKeys, $themeData);

            $theme                          = new Zend_Config($themeData);
            $chap['id']         = $id;

            $whiteLabelSettings  = ($themeMeta->WHITELABLE_SETTINGS) ? json_decode($themeMeta->WHITELABLE_SETTINGS) : new stdClass();
        }
        $this->view->theme  = $theme;
        $this->view->chap   = (object)$chap;
        $this->view->whitelabelSettings = $whiteLabelSettings;
        //Zend_Debug::dump($whiteLabelSettings);die();
    }
  
    private function __getGetChapLinks($menuStr) {
        $menuItems      = explode("\r\n", $menuStr);
        $links          = array();
        foreach ($menuItems as $item) {
            $item   = trim($item);  
            if (!empty($item)) {
                $link   = explode('=>', $item);
                $links[trim(@$link[0])]   = trim(@$link[1]);
            }
        }
        return $links;
    }
    
    private function __stringify($arr, $sep = '=>') {
        if (!is_array($arr)) return '';
        
        $str    = '';
        foreach ($arr as $key => $val) {
            $str    .= "{$key} {$sep} {$val} \n";
        }
        return $str;
    }


    function createResellerAction() {
       
        if ($this->getRequest()->isPost()) {
                $userModel  = new Model_User();
                $userMetaModel  = new Model_UserMeta();
                $data['payout_id']  = null;
                $data['type']       = 'RESELLER';
                $data['login_type'] = 'NEXVA';
                $data['status']     = 1;

                $data['email'] = $this->_request->email;
                $data['password']   = md5( $this->_request->password);

                $id = $userModel->insert($data);
                
                $userMetaModel->setEntityId($id);

                $userMetaModel->FIRST_NAME = $this->_request->first_name;
                $userMetaModel->LAST_NAME = $this->_request->last_name;
                $userMetaModel->COMPANY_NAME = $this->_request->company_name;
                                               

                die("$id");

                $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/list/tab/tab-resellers');            
        }        
    }

  /**
   * Create New user account
   * user/new
   * @return <type>
   */
  function createAction() {
    $request = $this->getRequest();
    $form = new Admin_Form_User();
//        $form->removeElement('update_password');
    if ($this->getRequest()->isPost()) {
      if ($form->isValid($request->getPost())) {
        $user = new Admin_Model_User($form->getValues());
        $mapper = new Admin_Model_UserMapper();
        $mapper->save($user);
        $this->view->message = array('User account created.', 'success');
//                return $this->_helper->redirector('index');
      }
    }
//        $this->view->breadcrumbs['Create User'] = $this->getFrontController()->getBaseUrl() . '/admin/user/create';
    $form->setAction($this->getFrontController()->getBaseUrl() . '/user/create');
    $this->view->form = $form;
//        $this->view->toolbarLinks['Add to my bookmarks'] = $this->getFrontController()->getBaseUrl() . '/admin/index/bookmark/url/admin_user_create';
  }

  /**
   * Create New user account
   * user/new
   * @return <type>
   */
  function editAction() {
    $request = $this->getRequest();
    $form = new Admin_Form_User();
    // Get the ID
    $userid = $this->_getParam('id');
//        $form->removeElement('update_password');
    if ($this->getRequest()->isPost()) 
    {
      if ($form->isValid($request->getPost()))
      {
        $user = new Admin_Model_User($form->getValues());
        $mapper = new Admin_Model_UserMapper();
        $mapper->save($user);
        $this->view->message = array('User account updated.', 'success');
        //return $this->_helper->redirector('index');
      } else {
        $user = new Admin_Model_User($form->getValues());
        $mapper = new Admin_Model_UserMapper();

        foreach ($form->getMessages() as $msgs) {
          if (is_array($msgs))
            $msgis = array_pop($msgs);
          if (is_array($msgis))
            $msgis = array_pop($msgis);
        }
        $this->view->message = array('User account update failed.' . $msgis, 'error');
//                return;
      }
    }
    else {
      $user = new Admin_Model_User($form->getValues());
      $mapper = new Admin_Model_UserMapper();

      if ($mapper->find($userid, $user)) {
        // // redirect to create user form
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/create');
        $this->view->message = array('User account not exists.', 'error');
      }
    }
//        $this->view->breadcrumbs['Create User'] = $this->getFrontController()->getBaseUrl() . '/admin/user/create';
    // Set Form element valuse and action

    $form->setAction(ADMIN_PROJECT_BASEPATH . 'user/edit/id/' . $userid);
    $this->view->form = $form;
    $this->view->form->id->setValue($user->getId());
    $this->view->form->username->setValue($user->getUsername());
    $this->view->form->email->setValue($user->getEmail());
    $this->view->form->type->setValue($user->getType());

    $userModel = new Model_User();

    $userDetails = $userModel->getUserDetailsById($this->_request->id);


    if ($userDetails->type == 'CP') {
      $this->view->form->payout->setValue($user->getPayout());
    }


    $this->view->form->status->setValue($user->getStatus());
    $this->view->form->submit->setLabel('Update User');

    //  $this->view->toolbarLinks['Add to my bookmarks'] = $this->getFrontController()->getBaseUrl() . '/admin/index/bookmark/url/admin_user_create';
  }

  /**
   * Filter Users
   * user/filter
   */
  function filterAction() {
    // filter variable
    // TODO : Add filter to session to keep it untill it ends
    $filter = '';
    $request = $this->getRequest();
    // adding update options form
    $form_update = new Admin_Form_UpdateOptions();
    $this->view->updateOptions = $form_update;
//        $form_update->setAction($this->getFrontController()->getBaseUrl() . '/user/update/' . $userid);
    // adding filter form
    $form_filter = new Admin_Form_Filter();
    $this->view->filter = $form_filter;
//        $form_filter->setAction($this->getFrontController()->getBaseUrl() . '/user/filter/' . $userid);
    // get the query variable
    $query = $this->_getParam('q');
    $type = strtoupper($query);

    if ($this->getRequest()->isPost()) {
//            print_r($form_update->getValues());
//            exit;
      if ($form_filter->isValid($request->getPost())) {
//                die('filter');
        $values = $form_filter->getValues();
        // set the filter value
        if ($values['filter'] < 2)
          $filter = 'AND status = ' . $values['filter'];
      }
    }
    $mapper = new Admin_Model_UserMapper();
    $users = $mapper->fetchRows('type = "' . $type . '"' . $filter, 'id ASC');
    $this->view->users = $users;
  }

  /**
   * Delete User
   */
    function deleteAction() {
        $id = $this->_request->getParam('id');
        $user = new Admin_Model_UserMapper();
        
        $productModel   =   new Model_Product();
        $apps   =   $productModel->fetchAll("user_id = $id")->toArray();
        if(count($apps)==0)
           $user->delete($id);
        else
        	$this->_flashMessenger->addMessage(
        									array(
        										'error' => 'Sorry, but the CP you tried deleting has content associated with account and 
        										therefore cannot be deleted at this time.'));
        	
          
 
         $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/list');
        
       
    }

  function updateAction() {
    $request = $this->getRequest();
//        print_r($request);
    // adding update options form
    $form_update = new Admin_Form_UpdateOptions();
//        $this->view->updateOptions = $form_update;
//        $form_update->setAction($this->getFrontController()->getBaseUrl() . '/user/update/' . $userid);
    if ($this->getRequest()->isPost()) {
      if ($form_update->isValid($request->getPost())) {
        print_r($form_update->getValues());
        exit;
        die('oki done');
      }
    }
  }

  function appsAction() {

    $cpId = $this->_request->id;
    $productModel = new Model_Product();
    $apps = $productModel->fetchAll("user_id = $cpId and deleted <> 1" )->toArray();
    if (count($apps) == 0) {
      $this->view->showProductsAllEmptyMsg = true;
    }
    $pagination = Zend_Paginator::factory($apps);
    $pagination->setCurrentPageNumber($this->_request->getParam('page', 0));
    $pagination->setItemCountPerPage(20);
    $this->view->apps = $pagination;
    
    $appsDeleted = $productModel->fetchAll("user_id = $cpId and deleted = 1 ")->toArray();
    if (count($appsDeleted) == 0) {
      $this->view->showDeletedProductsEmptyMsg = true;
    }
    

    
    $paginationDeleted = Zend_Paginator::factory($appsDeleted);
    $paginationDeleted->setCurrentPageNumber($this->_request->getParam('page_deleted', 0));
    $paginationDeleted->setItemCountPerPage(20);
    
    $this->view->selected_tab = $this->_request->getParam('tab', 'tab-all');
    
    $this->view->appsDeleted = $paginationDeleted;
    
  }

  function listAction() {

    //ini_set('memory_limit', '512M');

    #Cps section
    $user = new Model_User();
    $this->view->show_app_icons = true;
    $this->view->selected_tab = $this->_request->getParam('tab', "tab-cps");
    $this->view->search = $this->_request->search;
    $this->view->status = ('1' == $this->_request->getParam('status', 1)) ? 'active' : 'nonactive';
    $config = Zend_Registry::get('config');
    $this->view->form_submit = 'http://' . $config->nexva->application->admin->url . "/" . $this->getRequest()->getControllerName() . "/" . $this->getRequest()->getActionName();

    
    $cps_list = $user->select();
    
    $search = trim($this->_request->search);

   if (0 == strcmp($this->_request->tab, 'tab-cps')) {

      if (isset($search)) {

        $cps_list->setIntegrityCheck(false)
            ->from('users')
            ->joinLeft('user_meta', 'users.id = user_meta.user_id', array('meta_name', 'meta_value'))
            ->where("type='CP'")
            ->where("username like '%%" . $search . "%%' or email like '%%" . $search . "%%' or (meta_name='COMPANY_NAME' and meta_value like '%" . $search . "%%')  
            		or (meta_name='FIRST_NAME' and meta_value like '" . $search . "%%') or (meta_name='LAST_NAME' and meta_value like '" . $search . "%%') ")
            ->where("status=". $this->_request->getParam("status", 1))
            ->group('users.id')
            ->order('users.id desc');
            
       //    die( $cps_list->__toString());
      } else {

        $cps_list->where("type = '" . $this->_request->getParam("type", "CP") . "'", 'id')
            ->order('users.id desc');
      }
    }	else {
    	
    	       $cps_list->where("type = '" . $this->_request->getParam("type", "CP") . "'", 'id')
            ->order('users.id desc');
    }
    
    //$cps_list->query();
    

    
   // $cps_list = $user->fetchAll($cps_list);
    $pagination = Zend_Paginator::factory($cps_list);
    if (count($pagination) == 0) {
      $this->view->is_cplist_empty = true;
    }
    $pagination->setCurrentPageNumber($this->_request->getParam('page_cps', 1));
    $pagination->setItemCountPerPage(10);
    $this->view->cps_list = $pagination;
    unset($pagination);

    #Customers secion
    $customers_list = $user->select();
    if (0 == strcmp($this->_request->tab, 'tab-customers')) {

      $customers_list->where("username like '%%" . $search . "%%' or email like '%%" . $search . "%%'")
          ->where("status = " . $this->_request->getParam('status', 1))
          ->order('users.id desc');
    }

    $customers_list->where("type = '" . $this->_request->getParam("type", "USER") . "'", 'id')->order('users.id desc');
    $pagination = Zend_Paginator::factory($customers_list);
    if (count($pagination) == 0) {
      $this->view->is_customerlist_empty = true;
    }
    $pagination->setCurrentPageNumber($this->_request->getParam('page_customers', 1));
    $pagination->setItemCountPerPage(10);
    $this->view->customers_list = $pagination;
    unset($pagination);


    #Administrators section
    $administrators_list = $user->select();
    if (0 == strcmp($this->_request->tab, 'tab-administrators')) {

      $administrators_list->where("username like '%%" . $search . "%%' or email like '%%" . $search . "%%'")
          ->where(" status = " . $this->_request->getParam('status', 1));
    }
    $administrators_list->where("type = '" . $this->_request->getParam("type", "ADMIN") . "'", 'id')->order('users.id desc');
    //    ->query()
    //    ->fetchAll();

    $pagination = Zend_Paginator::factory($administrators_list);
    if (count($pagination) == 0) {
      $this->view->is_adminlist_empty = true;
    }
    $pagination->setCurrentPageNumber($this->_request->getParam('page_administrators', 1));
    $pagination->setItemCountPerPage(10);

    $this->view->administrators_list = $pagination;
    unset($pagination);
    
    
    #CHAPS section
    $chaps_list = $user->select();
    if (0 == strcmp($this->_request->tab, 'tab-chaps')) {

      $chaps_list->where("username like '%%" . $search . "%%' or email like '%%" . $search . "%%'")
          ->where(" status = " . $this->_request->getParam('status', 1));
    }
    $chaps_list->where("type = '" . $this->_request->getParam("type", "CHAP") . "'", 'id')->order('users.id desc');
      //  ->query()
       // ->fetchAll();

    $pagination = Zend_Paginator::factory($chaps_list);
    if (count($pagination) == 0) {
      $this->view->is_chapslist_empty = true;
    }
    $pagination->setCurrentPageNumber($this->_request->getParam('page_chaps', 1));
    $pagination->setItemCountPerPage(10);

    $this->view->chaps_list = $pagination;
    unset($pagination);
    


    //Reseller section
    $reseller_list = $user->select();
    if (0 == strcmp($this->_request->tab, 'tab-resellers')) {

      $reseller_list->where("username like '%%" . $search . "%%' or email like '%%" . $search . "%%'")
          ->where(" status = " . $this->_request->getParam('status', 1));
    }
    $reseller_list->where("type = '" . $this->_request->getParam("type", "RESELLER") . "'", 'id')->order('users.id desc');
   //     ->query()
   //     ->fetchAll();

    $pagination = Zend_Paginator::factory($reseller_list);
   
    $pagination->setCurrentPageNumber($this->_request->getParam('page_resellers', 1));
    $pagination->setItemCountPerPage(10);

    $this->view->reseller_list = $pagination;
    unset($pagination);

     //nexpager section
     
      $nexpagerCpsList = $user->select();
      if (0 == strcmp($this->_request->tab, 'tab-nexpager') and isset($search)) {

	    $nexpagerCpsList->setIntegrityCheck(false)
	                    ->from('users')
	                    ->joinLeft('user_meta', 'users.id = user_meta.user_id', array('meta_name', 'meta_value'))
	                    ->where("type='CP'")
	                    ->where("username like '%%" . $search . "%%' or email like '%%" . $search . "%%'")
	                    ->where("meta_name='ACTIVE_NEXPAGE' and meta_value = 1")
	                    ->where(" status = " . $this->_request->getParam('status', 1))
	                    ->group('users.id')
	                    ->order('users.id desc');
      }
      else
      {
        $nexpagerCpsList->setIntegrityCheck(false)
                        ->from('users')
                        ->joinLeft('user_meta', 'users.id = user_meta.user_id', array('meta_name', 'meta_value'))
                        ->where("type = '" . $this->_request->getParam("type", "CP") . "'", 'id')
                        ->where("meta_name='ACTIVE_NEXPAGE' and meta_value = 1")
                        ->order('users.id desc');
      	
      }

    $pagination = Zend_Paginator::factory($nexpagerCpsList);
		
		if (count ( $pagination ) == 0) {
			
			$this->view->is_nexpagerListEmpty = true;
		
		}
    
    $pagination->setCurrentPageNumber($this->_request->getParam('page_nexpager', 1));
    $pagination->setItemCountPerPage(10);
    $this->view->nexpagerCpList = $pagination;
    unset($pagination);
    
    
    
  }

    public function impersonateAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $type   = $this->_getParam('type', 'cp');
        
        switch ($type) {
            case 'reseller' :
                $impersonateUrl = 'http://'.Zend_Registry::get('config')->nexva->application->reseller->url.'/user/impersonate';
                break; 

            case 'chap' :
                $impersonateUrl = 'http://'.Zend_Registry::get('config')->nexva->application->chap->url.'/user/impersonate';
                break;
                
            case 'cp' :
                $impersonateUrl = 'http://'.Zend_Registry::get('config')->nexva->application->cpbo->url.'/user/impersonate';
                break;
            
            case 'cp-an' :
                $impersonateUrl = 'http://'.Zend_Registry::get('config')->nexva->application->cpbo->url.'/user/impersonate-an';
                break;
                
            default :
                $impersonateUrl = 'http://'.Zend_Registry::get('config')->nexva->application->cpbo->url.'/user/impersonate';
                break;
        }
         
        $userModel  = new Model_User(); 
        $user       = $userModel->fetchRow('id='.$this->_request->id);

        if( !$user ) throw new Zend_Exception('Invalid request: User not found');
        
        $timestamp = strtotime("+10 seconds");
               
        $hash = md5($user->id.$user->password.$this->_request->id.Zend_Registry::get('config')->nexva->application->salt.$timestamp);

        $impersonateUrl .= '/id/'.$this->_request->id;
        $impersonateUrl .= '/hash/'.$hash;
        $impersonateUrl .= '/timestamp/'.$timestamp;

        $this->_redirect($impersonateUrl);                    
    }
    
    
    //Adding manual entry for crediting / debiting , CP or Reseller account
    public function manualPayAction()
    {
        $userId = $this->_request->id;
        $partnerType = $this->_request->type;
        
        $userAccount = new Model_UserAccount();  
        $userModel  = new Model_User();
        
        
        //retrieve user 
        $userData = $userModel->getUserById($userId);
        
        $this->view->userName = $userData->username;       
        
        $this->view->userId = $userId;
        $this->view->type = $partnerType;
        $this->view->totalAmount = $userAccount->getTotalByUid($userId);
        
        //check if its a form submission
        if ($this->getRequest()->isPost()) 
        {
            $actionType  = trim($this->_getParam('pay_manual_action'));
            $amount = trim($this->_getParam('pay_manual_amount'));
            $description = trim($this->_getParam('pay_manual_description'));
               
            
            $transParams = array();
            $transParams['uid'] = $userId;
            $transParams['desc'] = $description;
            
                     
            
            //select the method to save the record based on the type selected
            if($actionType == "credit")
            {
                $userAccount->credit($amount, $transParams);
            }
            else
            {
                $userAccount->debit($amount, $transParams);
            }
            
            $this->view->messagesSuccess = "Record has been saved";
            $this->view->totalAmount = $userAccount->getTotalByUid($userId);
        }        
        
    }

    
    public function orderDetailsAction()
    {
        $userId = $this->_request->id;        
        $isOrdersEmpty = FALSE;
        
        $userModel  = new Model_User();        
        
        //retrieve order details 
        $orderDetails = $userModel->getOrderDetailsByCustomer($userId);
           
        
        $pagination = Zend_Paginator::factory($orderDetails);
        
        if (count($pagination) == 0) 
        {
            $isOrdersEmpty = TRUE;
        }          
        
        $pagination->setCurrentPageNumber($this->_request->getParam('order_details', 1));
        $pagination->setItemCountPerPage(50);
        
        $this->view->isOrdersEmpty = $isOrdersEmpty;
        $this->view->orderDetails = $pagination;
        
        unset($pagination);    

    }

    /**
     * Assign Payouts for a CHAP
     */
    public function assignPayoutsAction()
    {
        $payoutsModel = new Admin_Model_Payout();
        $payouts = $payoutsModel->getPayoutDetails();

        $userModel = new Admin_Model_UserDetails();
        $user = $userModel->getUserDetails($this->_request->id);

        $this->view->user = $user;

        $pagination = Zend_Paginator::factory($payouts);
        $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
        $pagination->setItemCountPerPage(10);

        $this->view->payouts = $pagination;
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }

    /**
     * Assign Default Payouts for a CHAP
     */

    public function setDefaultPayoutAction()
    {
        $userModel = new Admin_Model_UserDetails();
        $userModel->updatePayout($this->_request->user,$this->_request->id);

        $this->_helper->flashMessenger->addMessage('Payout Schemes successfully set.');
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/assign-payouts/id/'.$this->_request->user);
    }

    /**
     * Assign Currency for a CHAP
     */
    function setCurrencyAction()
    {
        $userModel = new Admin_Model_UserDetails();
        $user = $userModel->getUserDetails($this->_request->id);
        $this->view->user = $user;

        $currencyModel = new Admin_Model_Currency();
        $currencies = $currencyModel->getAvailableCurrencies();
        $this->view->currencies = $currencies;

        $currencyUserModel = new Model_CurrencyUser();
        $currencyUser = $currencyUserModel->getCurrencyUser($this->_request->id);

        $this->view->currencyUser = $currencyUser;

        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }

    /**
     * Save currency details for a Particular CHAP
     */
    function saveCurrencyAction()
    {
        $id = $this->_request->id;
        $currency = $this->_request->currency;
        $status = $this->_request->status;

        $currencyUserModel = new Model_CurrencyUser();
        $status = $currencyUserModel->addCurrencyUser($id,$currency,$status);

        $this->_helper->flashMessenger->addMessage($status);
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/set-currency/id/'.$id);
    }

    /**
     * Assign Payment Gateway for CHAP
     */

    function paymentGatewayAction()
    {
        $id = $this->_request->id;
        $this->view->user_id = $id;

        $paymentGatewayModel = new Admin_Model_PaymentGateway();
        $paymentGateways = $paymentGatewayModel->getPaymentGateway();

        $pagination = Zend_Paginator::factory($paymentGateways);
        $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
        $pagination->setItemCountPerPage(10);

        $this->view->paymentGateways = $pagination;

        $paymentGatewayUserModel = new Admin_Model_PaymentGatewayUser();
        $userDetails = $paymentGatewayUserModel->getPaymentGatewayUserDetails($id);

        $this->view->userDetails = $userDetails;
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }

    /**
     * set Default Payment Gateway for a CHAP
     */
    public function setDefaultPaymentGatewayAction()
    {
        $paymentGatewayUserModel = new Admin_Model_PaymentGatewayUser();
        $paymentGatewayUserModel->disableGatewayByChap($this->_request->user);
        $paymentGatewayUserModel->updatePaymentGateway($this->_request->user,$this->_request->id);

        $this->_helper->flashMessenger->addMessage('Payment Gateway successfully set.');
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/payment-gateway/id/'.$this->_request->user);
    }
    
    /**
     * Assign Language for a CHAP
     */
    function setLanguageAction()
    {
        $userModel = new Admin_Model_UserDetails();
        $user = $userModel->getUserDetails($this->_request->id);
        $this->view->user = $user;

        $languageUserModel = new Admin_Model_LanguageUser();
        $languageUser = $languageUserModel->getLanguageUser($this->_request->id);

        $this->view->languages = $languageUser;

        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }
    
    /**
    * Save language details for a Particular CHAP
    */
    function saveLanguageAction()
    {
        $id = $this->_request->id;
        $languageIds = $this->_request->language;
        $defaultLanguage = $this->_request->default_language;

        $languageModel = new Admin_Model_Language();
        $languageData = $languageModel->getAvailableLanguages();

        $languages = array();
        foreach($languageData as $language){
            $languages[] = (string)$language['id'];
        }

        $languageUserModel = new Admin_Model_LanguageUser();

        if(!empty($languageIds)){
            foreach($languageIds as $languageId){
                $languageUserModel->addLanguageUser($id,$languageId);
            }
            $results = array_diff($languages, $languageIds);
            foreach($results as $result){
                $languageUserModel->removeLanguageUser($id,$result);
            }
        }

        $data = array(
                    'default_language' => 1
                );

        $languageUserModel->update($data,'language_id ='.$defaultLanguage.' AND user_id = '.$id);
        //die();

        $this->_helper->flashMessenger->addMessage('Language settings has being Changed.');
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/set-language/id/'.$id);
    }


    function supportChannelAction()
    {
        $productId = $this->_request->getParam('id');

        //channel details
        $userModel = new Cpbo_Model_User();
        $channelDetails = $userModel->getChannelDetails($productId);

        $pagination = Zend_Paginator::factory($channelDetails);
        $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
        $pagination->setItemCountPerPage(10);

        $this->view->paginator = $pagination;
        $this->view->productId = $productId;

    }


    function setChannelAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $productId  = $this->_getParam('id');
        $status  = $this->_getParam('status');
        $chapId = $this->_getParam('chap');

        //echo $productId,'-',$status,'-',$chapId;die();

        $productChannelModel = new Cpbo_Model_ProductChannel();

        if($status == 'subscribe')
        {
            $productChannelModel->setChannel($productId,$chapId);
        }
        else
        {
            $productChannelModel->removeChannel($productId,$chapId);
        }

        //$this->_redirect('user/edit/id/'.$productId.'/6/4');
        $this->_redirect('user/support-channel/id/'.$productId);
    }

    /**
     * Assign Partner site Home page options for a CHAP
     */
    function homePageOptionsAction(){

        $id     = $this->_request->getParam('id', false);

        if (!$id && !$this->getRequest()->isPost()) {
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/list/tab/tab-chaps');
        }

        $keys   = array(
            'PARTNER_SITE_SLIDER',
            'PARTNER_SITE_FEATURED_APPS',
            'PARTNER_SITE_TOP_FREE_APPS',
            'PARTNER_SITE_TOP_PAID_APPS',
            'PARTNER_SITE_TOP_DOWNLOADED_APPS',
            'PARTNER_SITE_REGISTER_LINK',
            'PARTNER_SITE_FORGOT_PASSWORD_LINK',
            'PARTNER_SITE_LOGIN_LINK',
            'PARTNER_SITE_TITLE'
        );

        $chap   = array();
        if ($this->getRequest()->isPost()) {

            $id = $this->_request->getParam('id', $id);

            if (!$id) {
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/list/tab/tab-chaps');
            }

            $params = $this->_getAllParams();

            $themeMetaModel   = new Model_ThemeMeta();
            $themeMetaModel->setEntityId($id);

            foreach ($params['meta'] as $param => $value) {
                $themeMetaModel->$param   = trim($value);
                $chap[$param] = $value;
            }
            $chap['id'] = $id;

            //after adding the meta data to the db, remove cache for that particular entity
            /*$cache  = Zend_Registry::get('cache');
            $cacheKey    = 'EAV_FULL_Model_ThemeMeta_' . trim($id);
            $cacheKey    = str_ireplace('-', '_', $cacheKey);
            $cache->remove($cacheKey);*/

            $themeMetaModel->reloadCache($id);
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/list/tab/tab-chaps');
        } else {

            //$themeMetaModel   = new Admin_Model_ThemeMeta();
            $themeMetaModel   = new Model_ThemeMeta();
            $themeMetaModel->setEntityId($id);

            foreach ($keys as $key) {
                $chap[$key] = $themeMetaModel->$key;
            }

            $chap['id'] = $id;
        }
        //Zend_Debug::dump($chap);die();
        $this->view->chap   = (object)$chap;
    }
    
    public function verifyAction(){
        
        $userId=$this->_request->getParam('id');
        $userModel = new Model_UserMeta();
       
         
        if($userModel->setVerified($userId)){
            $this->_flashMessenger->addMessage(array('success' => 'Successfully verified user account.'));
            $this->_redirect ( 'user/list/tab/tab-cps' );
        }else{
             $this->_flashMessenger->addMessage(array('error' => "Can't verified user account."));
             $this->_redirect ( 'user/list/tab/tab-cps' );
        }
    }
    
    
            function postDispatch() {

    }
    
}