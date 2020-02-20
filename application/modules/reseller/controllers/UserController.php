<?php

class Reseller_UserController extends Zend_Controller_Action {

    public function init() {

    }

    public function indexAction() {


    }

    public function loginAction() {

        $this->_helper->layout->setLayout('reseller/reseller_login');

        if ($this->_request->isPost()) {

            $this->__doLogin( $this->_request->username, $this->_request->password);


        }

    }

    public function logoutAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        Zend_Auth::getInstance()->clearIdentity();
        $session_logout = new Zend_Session_Namespace('logoutaction');
        $session_logout->logout = true;
        $this->_redirect('/user/login');
    }


	protected function __doLogin($username, $password)	{
            try {

                $db = Zend_Registry::get('db');
                $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'email', 'password', "MD5(?) and status=1 and type='RESELLER'");
                $authDbAdapter->setIdentity($username)->setCredential($password);
                $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);

                if ($result->isValid()) {
                    //auth sucess
                    Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));

               


					$user = new Model_UserMeta();
					$user->setEntityId(Zend_Auth::getInstance()->getIdentity()->id);
					$user->LAST_LOGIN = date ( 'Y-m-d h:i:s' );

                    $session = new Zend_Session_Namespace('lastRequest');
                    if (isset($session->lastRequestUri)) {
                        $newpage = $session->lastRequestUri;
                        $session->lastRequestUri = NULL;
                        $this->_redirect($newpage);
                        return;
                    }

                    $this->_redirect('/');
                }
            } catch (Exception $e) { }

			$this->view->error = "The email/password combination you entered is not valid.";

	}

	
    public function impersonateAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $timestamp = $this->_request->timestamp;
        if( strtotime("now") > $timestamp ) die("Invalid Request: Your request is stale. Please try again."); //request timeout - we keep it to <=10

        $userModel  = new Model_User();
        $user       = $userModel->fetchRow('id='.$this->_request->id);

        if( !$user ) throw new Zend_Exception('Invalid request: CP not found');

        $hash = md5($user->id.$user->password.$this->_request->id.Zend_Registry::get('config')->nexva->application->salt.$timestamp);
        
        if( $hash != $this->_request->hash) die("Invalid request: Access denied."); //couldn't verfiy request sig - most likely someone tried to tamper the URL

        //fix the session and goto dashboard

        $obj = new stdClass();
        $obj->id    = $user->id;
        $obj->email = $user->email;
        
        Zend_Auth::getInstance ()->getStorage ()->write($obj);
        $this->_redirect('/');
    }

}
?>