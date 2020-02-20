<?php
class Admin_ProfileController extends Zend_Controller_Action
{
    public function preDispatch(){
       if( !Zend_Auth::getInstance()->hasIdentity() ) {

            if($this->_request->getActionName() != "login") {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();

            }
            if( $this->_request->getActionName() != "login" )
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/login');
        }
    }
    
    public function indexAction(){

        $this->_redirect(ADMIN_PROJECT_BASEPATH.'profile/save');
    }
    /**
     * check old password and end entered password is correct and reset to newly given password
     * @return boolean | if successfully updated the password
     */
    public function saveAction(){

        //Create session to count the incoming password reset requests
        $login_try = new Zend_Session_Namespace('login_try');
        if(isset($login_try->no_of_requests)){

            if($login_try->no_of_requests >5){
                Zend_auth::getInstance()->clearIdentity();
                Zend_Session::namespaceUnset('login_try');
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'');
            }
            $login_try->no_of_requests++;
        }else{
            $login_try->no_of_requests=1;
        }


        if('' == $this->_request->submit)return;
        if('' != $this->_request->password and '' != $this->_request->passwordretype){

               if( 0 == strcmp($this->_request->password,$this->_request->passwordretype)){


                   $user_id =   Zend_Auth::getInstance()->getIdentity()->id;
                   $user    = new Model_User();
                   
                    $user_det   =   $user->fetchRow("id = $user_id");
                   
                    if(0 === strcmp($user_det->password,md5($this->_request->passwordold))){

                        $user->update(array("password" => md5($this->_request->password)), "id = $user_id");
                        $this->view->message    =   "Password is updated.";
                        return true;
                    }else{
                        $this->view->error      =   "Old passwords are not matched.";
                    }
               }else{
                   
                   $this->view->error = "Passwords are not same.";
               }

        }else{

            $this->view->error  ="Password fields shouldn't be empty.";
        }
    }
}

?>