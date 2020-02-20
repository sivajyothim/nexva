<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */

class Admin_Model_User {
    protected $_id;
    protected $_username;
    protected $_email;
    protected $_password;
    protected $_type;
    protected $_login_type;
    protected $_status;
    protected $_payout;

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }
    // Magic Set
    public function __set($name, $value) {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid user property');
        }
        $this->$method($value);
    }
    // Magic Get
    public function __get($name) {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid user property');
        }
        return $this->$method();
    }

    public function setOptions(array $options) {
        
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    // Id
    public function setId($id) {
        $this->_id = (int) $id;
        return $this;
    }

    public function getId() {
        return $this->_id;
    }

    // Username
    public function setUsername($username) {
        $this->_username = $username;
        return $this;
    }

    public function getUsername() {
        return $this->_username;
    }

    // Email
    public function setEmail($email) {
        $this->_email = (string) $email;
        return $this;
    }

    public function getEmail() {
        return $this->_email;
    }

    // Password
    public function setPassword($password) {
        if(0 === strlen($password)){

         $this->_password = '';
        }else{
        $this->_password = hash('md5', $password);
        }
        return $this;
    }

    public function getPassword($giveOld=false) {


        if(strlen($this->_password) == 0){
            if($giveOld === true){
                $userModel = new Model_User();
                $user   =   current($userModel->find($this->_id)->toArray());
                
                return $user['password'];
               
            }

        }else{
           
            
            return $this->_password;
        }
        //default
        return $this->_password;
        
    }
    
    // Type
    public function setType($type) {
        $this->_type = $type;
        return $this;
    }

    public function getType() {
        return $this->_type;
    }

    public function setPayout($type) {
        $this->_payout = $type;
        return $this;
    }

    public function getPayout() {
        return $this->_payout;
    }
    
    // Login type
    public function setLoginType($login_type) {
        $this->_login_type = $login_type;
        return $this;
    }

    public function getLoginType() {
        return $this->_login_type;
    }

    // Status
    public function setStatus($status) {
        $this->_status = $status;
        return $this;
    }

    public function getStatus() {
        return $this->_status;
    }

}

