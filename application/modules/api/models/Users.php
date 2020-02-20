<?php

class Api_Model_Users extends Zend_Db_Table_Abstract
{
    protected $_name = 'users';
    protected $_id = 'id';
    
    function createUser($userData) {

        if(trim($userData['password']) == "")
            $password = ''; 
        else
            $password = md5($userData['password']);
    
        $values = array(
          "username" => $userData['username'],
          "email" => $userData['email'],
          "password" => $password,
          "type" => $userData['type'],
          "login_type" => $userData['login_type'],
          "status" => $userData['status'],
          "mobile_no" => $userData['mobile_no'],
          "activation_code" => $userData['activation_code'],
          "chap_id" => $userData['chap_id']
        );
        

    $res = $this->insert($values);
    

    
    
    return Zend_Registry::get('db')->lastInsertId();
  }
  
  public function getUserCountById($chapId, $userId, $activationCode)
  {        
        $sql = $this->select();
        $sql->from($this->_name,array('COUNT(id) AS user_count'))
            ->where('chap_id = ?',$chapId)
            ->where('id = ?',$userId)
            ->where('activation_code = ?',$activationCode);        
         
     //   echo  $sql->assemble();
        return $this->fetchRow($sql)->user_count;        
 }
 
 /**
     * 
     * Update user verification status
     * @param $userId user Id
     * @param $status status 
     */
    public function updateVerificationStatus($userId, $status)
    {        
        $data = array
                (  
                    'status' => $status
                );
        
        $where = array 
                (
                    'id = ?' => $userId
                );
        
       $id =  $this->update($data, $where);
       
       if($id)
       {
           return true;
       }
       else
       {
           return false;
       }
    }
    
    /**
     * 
     * Update user verification status
     * @param $userId user Id
     * @param $status status 
     */
    public function updateActivationCode($userId, $activationCode, $status)
    {        
        $data = array
                (  
                    'activation_code' => $activationCode,
                    'status' => $status
                );
        
        $where = array 
                (
                    'id = ?' => $userId
                );
        
       $id =  $this->update($data, $where);
       
       if($id)
       {
           return true;
       }
       else
       {
           return false;
       }
    }
    
    
    /**
     * 
     * Update mobile number
     * @param $userId user Id
     * @param $mobileNo mobile number
     * @param $activationCode activation code 
     * @param $status status 
     * 
     */
    public function updateMobileNumebr($userId, $mobileNo, $activationCode, $status)
    {        
        $data = array
                (  
                    'mobile_no' => $mobileNo,
                    'activation_code' => $activationCode,
                    'status' => $status
                );
        
        $where = array 
                (
                    'id = ?' => $userId
                );
        
       $id =  $this->update($data, $where);
      
       if($id)
       {
           return true;
       }
       else
       {
           return false;
       }
    }
    
 
  /**
   * Checks whether a users email exists
   *
   * @param username $username email
   * @return bool true or false
   * Chathura
   */
  public function validateUserEmail($username) {
    $check_username = $this->fetchRow($this->select()->where("email=?", $username));
    if (!is_null($check_username)) {
      return false;
    } else {

      return true;
    }
  }
  
  
  public function validateUserMdn($mdn) {
  	$checkMobileNo = $this->fetchRow($this->select()->where("mobile_no=?", $mdn));
  	if (!is_null($checkMobileNo)) {
  		return false;
  	} else {
  
  		return true;
  	}
  }
  
  /*
   * This function checks if the user has registered with header enrichment(with common password)
   * @param mobile no $mdn - user mobile number
   * @param password $password - common password for chaps (for airtel - airtelpassword/ for mtn - mtnpassword)
   * @return boolean
   */
  public function validateUserMdnAndPwd($mdn, $password) {
  	$checkHeaderEnrichedUser = $this->fetchRow($this->select()
                            ->where("mobile_no=?", $mdn)
                            ->where("password=?", md5($password))
                            );
  	if (!is_null($checkHeaderEnrichedUser)) {
  		return false;
  	} else {
  		return true;
  	}
  }
  

    public function validateActivationcode($activationCode)
    {
    $checkActivationCode = $this->fetchRow($this->select()->where("activation_code=?", $activationCode));
    if (is_null($checkActivationCode)) {
        return false;
    } else {

        return true;
    }
    }
  
  /**
   * Get a user's details by email address
   *
   * @param string $username Email of the user
   * @return Zend_Db_Table_Row
   *
   */
  public function getUserByEmail($username)
  {
      $sql = $this->select();
      $sql->from($this->_name,array('id', 'email', 'mobile_no', 'password', 'status', 'activation_code', 'chap_id'))
            ->where('email = ?', $username);        
    
      $userRow = $this->fetchRow($sql);
     
     if (is_null($userRow)) 
     {
         return null;
     }
     
     return $userRow; 
  }
  
  public function getUserByphone($phoneNumber)
  {
  	$sql = $this->select();
  	$sql->from($this->_name,array('id', 'email', 'mobile_no', 'password', 'status', 'activation_code', 'chap_id'))
  	->where('mobile_no = ?', $phoneNumber);
  
  	$userRow = $this->fetchRow($sql);
  	 
  	if (is_null($userRow))
  	{
  		return null;
  	}
  	 
  	return $userRow;
  }
  
  /**
   * Get a user's mobile number
   *
   * @param string $userId user id
   * @return Zend_Db_Table_Row
   *
   */
  public function getUserMobileById($userId)
  {
      $sql = $this->select();
      $sql->from($this->_name, array('mobile_no'))
            ->where('id = ?', $userId);        
    
      $userRow = $this->fetchRow($sql);
     
     if (is_null($userRow)) 
     {
         return null;
     }
     
     return $userRow->mobile_no; 
  }

    public function getUserById($userId)
    {
        $sql = $this->select();
        $sql->from($this->_name,array('username','email','mobile_no','activation_code'))
            ->where('id = ?', $userId);

        $userRow = $this->fetchRow($sql);

        if (is_null($userRow))
        {
            return null;
        }
        return $userRow;
    }
    
    public function getUserByMobileNo($phoneNo)
    {
    	$sql = $this->select();
    	$sql->from($this->_name,array('id', 'email', 'mobile_no', 'password', 'status', 'activation_code', 'chap_id'))
    	    ->where('mobile_no = ?', $phoneNo);

    	$userRow = $this->fetchRow($sql);

    	if (is_null($userRow))
    	{
    		return null;
    	}
    	return $userRow;
    }
    
    
    
  public function listChapSupportNexPayer()
    {
    	$userSql = $this->select();
    	$userSql->setIntegrityCheck(false)
    	        ->from('users',array('id', 'nexpayer_name', 'country_code'))
    	        ->join(array('cu' => 'currency_users'), 'cu.user_id = users.id', array('') )
    	        ->join(array('c' => 'currencies'), 'c.id = cu.currency_id', array('c.code', 'c.name', 'c.rate') )
    	        ->where('users.status != ?','0')
    	->where("users.type =  'CHAP'")
    	->where("users.nexpayer_support =  1")
    	->order('users.created_date DESC');
    	

    
    	return $this->fetchAll($userSql);
    
    }

}