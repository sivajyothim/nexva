<?php

class Partnermobile_Model_Users extends Zend_Db_Table_Abstract
{
    protected $_name = 'users';
    protected $_id = 'id';
    
    public function getUserStatusByEmail($email)
    {        
        $sql = $this->select();
        $sql->from($this->_name,array('status'))
            ->where('email = ?',$email);        
         
        return $this->fetchRow($sql)->status;
        
    }

    public function getUserByEmail($email)
    {
        $sql = $this->select();
        $sql->from($this->_name,array('id'))
            ->where('email = ?',$email);
        return $this->fetchRow($sql);
    }
    
    public function getUserStatusByMobileNo($mobileNo)
    {        
        $sql = $this->select();
        $sql->from($this->_name,array('status'))
            ->where('mobile_no = ?',$mobileNo);        
         
        return $this->fetchRow($sql)->status;
        
    }

    public function getUserByMobileNo($mobileNo)
    {
        $sql = $this->select();
        $sql->from($this->_name,array('id'))
            ->where('mobile_no = ?',$mobileNo);
        return $this->fetchRow($sql);
    }

    function doLoginOpenId($userData, $chapId = null)
    {
        
        $userModel = new Model_User();
        $userRow = $userModel->getUserByEmail($userData['email']);
        
        if (is_null($userRow)) {
            // this Open ID user doesn't exist yet, so let's create
            $chapId = ($chapId !== null) ? (int) $chapId : null;
            $userId = $userModel->createUser(
                    array("username" => null, 
                            "email" => $userData['email'], 
                            "password" => null, "type" => 'USER', 
                            "login_type" => 'OPENID', "chap_id" => $chapId));
            
            // populate user's details from FB into user_meta
            $userMeta = new Model_UserMeta();
            $userMeta->setEntityId($userId);
            $userMeta->FIRST_NAME = $userData['first_name'];
            $userMeta->LAST_NAME = $userData['last_name'];
        }
        
        $db = Zend_Registry::get('db');
        $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'email', 
                'email');
        
        $authDbAdapter->setIdentity($userData['email']);
        $authDbAdapter->setCredential($userData['email']);
        // I'm using setCredential($email) to satisfy Zend_Auth.
        
        $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);
        
        // Zend_Debug::dump($result->isValid());
        // Zend_Debug::dump($userRow);
        
        if ($result->isValid()) {
            Zend_Auth::getInstance()->getStorage()->write(
                    $authDbAdapter->getResultRowObject(null, 'password'));
            
             $auth = Zend_Auth::getInstance();
             $userId = $auth->getIdentity()->id;
             
       
            
            $session = new Zend_Session_Namespace('lastRequest');
            if (isset($session->lastRequestUri)) {

                $url = $session->lastRequestUri;

                unset($session->lastRequestUri);
                return $url;
            } else
                return '/';
        } else {
           return '/user/login';
        }
    }
}