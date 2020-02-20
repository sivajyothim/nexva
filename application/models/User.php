<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author jahufar
 */
class Model_User extends Nexva_Db_Model_MasterModel {

  protected $_name = 'users';
  protected $_id = 'id';
  protected $_dependentTables = array('orders', 'products');


  const PAYOUT_TYPE_DEFAULT = 'DEFAULT';
  const PAYOUT_TYPE_CP      = 'CP';
  const PAYOUT_TYPE_NEXVA   = 'NEXVA';

  /* protected $_referenceMap =   array(

    "orders" => array(

    "columns"   => array("id"),
    "refTableClass"     => "Model_Order",
    "refTableColumns"   => array("user_id")
    )

    ); */

  public function __construct() {
    parent::__construct();
  }

  // Password

  function registrationCommonCheck($formfields = null) {
    if ($formfields == null)
      $formfields = $this->getRequest()->getParams();

    $filters = array(
      '*' => 'StringTrim',
      'username' => array
        (new Zend_Filter_StripTags()
      ),
      'email' => new Zend_Filter_StripTags(),
      'email_unclaim' => new Zend_Filter_StripTags()
    );

    $validators = array(
      'username' => array(
        'allowEmpty' => false,
        'messages' => 'User name must be a valid string'
      ),
      'password' => array(
        'allowEmpty' => false,
        'messages' => array(
          0 => 'Password field is required',
        ),
      ),
      'retypepassword' => array(
        'allowEmpty' => false,
        'messages' => 'Please type the password again in "Retype password field'
      ),
      'email' => array(
        'allowEmpty' => false,
        'messages' => array
          (
          1 => 'Enter your email id'
        )
      ),
    );
    return New Zend_Filter_Input($filters, $validators, $formfields);
  }

  function validateUsername($username) {
    $check_username = $this->fetchRow($this->select()->where("username=?", $username));
    if (!is_null($check_username)) {
      $error = "This email is already taken. Try something else";
      return $error;
    }
    return true;
  }

  function validateEmail($email) {
    $check_email = $this->fetchRow($this->select()->where("email=?", $email));
    if (!is_null($check_email)) {
      $error['email'] = "This email has already been taken. ";
    }
    $email_validater = new Zend_Validate_EmailAddress();
    if (!$email_validater->isValid($email)) {
      $error['email'] = "Email id is not valid.";
    }

    if (!isset($error))
      return true;
    else
      return $error;
  }

  function checkPassword($password, $passwordretype) {
    if (0 != strcasecmp($password, $passwordretype)) {
      $error['password'] = "Passwords are not same";
      return $error;
    }
    return true;
  }

  function createUser($userData) {

    if (trim($userData['password']) == "")
      $password = ''; else
      $password = md5($userData['password']);
    $values = array(
      "id" => NULL,
      "username" => $userData['username'],
      "email" => $userData['email'],
      "password" => $password,
      "type" => $userData['type'],
      "login_type" => $userData['login_type'],
      "status" => '1',
//      "chap_id" => $userData['chap_id']
      "chap_id" => 1
    );
    $res = $this->insert($values);
    return Zend_Registry::get('db')->lastInsertId();
  }

  function generateMail($mail, $username) {
    // mail('cheran.uk@gmail.com','hi','hi','From: Cherankrish@gmail.com');

    $subject_text = "Your Nexva.com account is created.";
    $body_text = "Thankyou for registering  with nexva.";
    $mail_from['mail'] = "webmaster@nexva.com";
    $mail_from['name'] = 'Nexva';

    $mailobj = new Zend_Mail();

    $mailobj->addTo($mail, $username)
        ->setBodyText($body_text)
        ->setFrom($mail_from['mail'], $mail_from['name'])
        ->setSubject(ucfirst($username) . ", $subject_text")
        ->send();
  }

  // @TODO : Load full user object
  function getUserDetailsById($userId) {
    $userMeta = new Model_ProductMeta(); //?? WTF? Is this used somewhere?? @todo: check this shit
    $rowset = $this->find($userId);
    $productRow = $rowset->current();
    return $productRow;
  }

  public function getMetaValue($userId, $metaName) {
    $userMeta = new Model_UserMeta();
    $userMeta->setEntityId($userId);

    return $userMeta->$metaName;
  }

  public function getPurchasedProduct($devices = false) {
    $userId = Zend_Auth::getInstance()->getIdentity()->id;
    $orders = new Model_Order();
    $select = $orders->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
    $select->setIntegrityCheck(false)
        ->where('orders.user_id =' . $userId)
        ->join('order_details', 'order_details.order_id = orders.id')
        ->join('products', 'products.id = order_details.product_id');
    if ($devices) {
      $select->join('product_builds', 'product_builds.product_id = products.id')
          ->join('build_devices', 'product_builds.id = build_devices.build_id')
          ->where("build_devices.device_id IN($devices)");
    }
    return $orders->fetchAll($select);
  }

  /**
   * Send user password reset email
   *
   * @param string $email
   * @return boolean
   */
  public function sendPasswordResetMail($email) {


    $userRow = $this->fetchRow($this->select()->where("email=?", $email));
    if (is_null($userRow)) {
      throw new Zend_Exception("The email you supplied was not found.");
    } else {

      $config = Zend_Registry::get('config');

      $timeout = strtotime("+1 hour"); //1 hour until the link expires

      $otp = new Nexva_Util_OTP_OneTimePassword($userRow->id, $userRow->password);

      $otp->setSalt($config->nexva->application->salt);
      $otp->setTimeout($timeout);

      $otphash = $otp->generateOTPHash();

      $link = "http://" . $config->nexva->application->base->url . "/user/reset-password/id/" . $userRow->id . "/time/$timeout/otphash/$otphash";

      $userRow = $this->fetchRow($this->select()->where("email=?", $email));

      $userMeta = new Model_UserMeta();
      $fisrtName = $userMeta->getAttributeValue($userRow->id, 'FIRST_NAME');
      $lastName = $userMeta->getAttributeValue($userRow->id, 'LAST_NAME');


      if (empty($fisrtName)) {
        $name = $userRow->email;
      } else {
        $name = $fisrtName . ' ' . $lastName;
      }


      $mailer = new Nexva_Util_Mailer_Mailer();
      $mailer->addTo($userRow->email, $userRow->email)
          ->setSubject('Reset your neXva account password.')
          ->setLayout("generic_mail_template")
          ->setMailVar("name", $name)
          ->setMailVar("resetlink", $link)
          ->sendHTMLMail('forgotpassword.phtml');

      $this->view->message = "Instructions on how to reset your password has been emailed to " . $userRow->email;
      return true;
    }
  }

  /**
   * Send user password reset email for unclaimed accounts
   *
   * @param string $email
   * @param string $type inapp | cpbo | mobile | web
   * @return boolean
   */
  
  
  public function sendUnclaimedAccountMail($email, $type='inapp')
    {   
        
        $userRow  =   $this->fetchRow($this->select()->where("email=?",$email));
        if( is_null($userRow) )
        {
            throw new Zend_Exception("The email you supplied was not found.");
        }

        else // generate and email password
        {

            $config = Zend_Registry::get('config');

            $timeout =  strtotime("+1 day"); //10 years until the link expires

            $otp = new Nexva_Util_OTP_OneTimePassword($userRow->id, $userRow->password);
 
            $otp->setSalt($config->nexva->application->salt);
            $otp->setTimeout($timeout);

            $otphash = $otp->generateOTPHash();

   
            $userRow  =   $this->fetchRow($this->select()->where("email=?",$email));
            
            $userMeta = new Model_UserMeta();
            $fisrtName  =  $userMeta->getAttributeValue($userRow->id,'FIRST_NAME');
            $lastName  =  $userMeta->getAttributeValue($userRow->id,'LAST_NAME');
             
            
            if(empty($fisrtName)) 
            {
                $name = $userRow->email;
            
            }
            else
            {
                $name =  $fisrtName . ' ' .$lastName;
            }

            if($type=='web' || $type=='mobile')    {  
                
                $link = "http://".$config->nexva->application->base->url."/user/reset-password/id/".$userRow->id."/time/$timeout/otphash/$otphash";
                
                $mailer = new Nexva_Util_Mailer_Mailer();
                $mailer->addTo($userRow->email, $userRow->email)
                ->setSubject('About your neXva account')
                ->setLayout("generic_mail_template")
                ->setMailVar("name",$name)
                ->setMailVar("email",$userRow->email)
                ->setMailVar("resetlink",$link)
                ->sendHTMLMail('unclaimed_account_reset_password_web.phtml');
            }
            
            if($type=='inapp')    {  
                $link = "http://".$config->nexva->application->base->url."/user/claim/id/".$userRow->id."/time/$timeout/otphash/$otphash";
                
                $mailer = new Nexva_Util_Mailer_Mailer();
                $mailer->addTo($userRow->email, $userRow->email)
                ->setSubject('About your neXva account')
                ->setLayout("generic_mail_template")
                ->setMailVar("name",$name)
                ->setMailVar("email",$userRow->email)
                ->setMailVar("resetlink",$link)
                ->sendHTMLMail('unclaimed_account_reset_password.phtml');
            }
            
            
            return true;
            

        }
      
        
    }

  /**
   * Send verify account email 
   *
   * @param string $email
   * @param string $type  cpbo | mobile | web
   * @return boolean
   */
  
    public function sendVerifyAccountMail($email, $type = 'cpbo') {
		
		$userRow = $this->fetchRow ( $this->select ()->where ( "email=?", $email ) );
		if (is_null ( $userRow )) {
			throw new Zend_Exception ( "The email you supplied was not found." );
		} 

		else // generate and email password
{
			
			$config = Zend_Registry::get ( 'config' );
			
			$timeout = strtotime ( "+1 day" ); //10 years until the link expires
			

			if ($type != 'cpbo')
				$otp = new Nexva_Util_OTP_OneTimePassword ( $userRow->id, $userRow->password );
			else {
				
				$userMeta = new Model_UserMeta ( );
				$verified = $userMeta->getAttributeValue ( $userRow->id, 'VERIFIED_ACCOUNT' );
				
				$otp = new Nexva_Util_OTP_OneTimePassword ( $userRow->id, $verified );
			
			}
			
			$otp->setSalt ( $config->nexva->application->salt );
			$otp->setTimeout ( $timeout );
			
			$otphash = $otp->generateOTPHash ();
			
			$userRow = $this->fetchRow ( $this->select ()->where ( "email=?", $email ) );
			
			$userMeta = new Model_UserMeta ( );
			$fisrtName = $userMeta->getAttributeValue ( $userRow->id, 'FIRST_NAME' );
			$lastName = $userMeta->getAttributeValue ( $userRow->id, 'LAST_NAME' );
			
			if (empty ( $fisrtName )) {
				$name = $userRow->email;
			
			} else {
				$name = $fisrtName . ' ' . $lastName;
			}

            
            if($type=='cpbo')    {  
                $link = CP_PROJECT_BASEPATH."user/verify/id/".$userRow->id."/time/$timeout/otphash/$otphash";
                
                $mailer = new Nexva_Util_Mailer_Mailer();
                $session =new Zend_Session_Namespace('chap');
                
                $config = Zend_Registry::get('config');
                $chapFrenchChapsNames = explode(',', $config->nexva->application->frenchchapsname);          

                
                if (in_array($session->chap->username, $chapFrenchChapsNames)) {
                    $template = 'verify_account_cpbo_fr.phtml';
                } else {
                    $template = 'verify_account_cpbo.phtml';
                }
                
                $mailer->addTo($userRow->email, $userRow->email)
                ->setSubject('Verify your neXva account')
                ->setLayout("generic_mail_template")
                ->setMailVar("name",$name)
                ->setMailVar("email",$userRow->email)
                ->setMailVar("resetlink",$link)
                ->sendHTMLMail($template);
            }
            
            return true;
            

        }
      
        
    }
    
    

  /**
   * Checks whether a users email exists
   *
   * @param username $username email
   * @return bool true or false
   * Chathura
   */
  function validateUserEmail($username) {
    $check_username = $this->fetchRow($this->select()->where("email=?", $username));
    if (!is_null($check_username)) {
      return false;
    } else {

      return true;
    }
  }

  /**
   * Resets a USERS's password.
   *
   * @param id $userId User Id
   * @param string $newPassword The new password
   * @return int The number of rows updated
   *
   */
  function resetPassword($userId, $newPassword) {
    return $this->update(array("password" => md5($newPassword)), "id = $userId");
  }

  /**
   * Get a user's details by email address
   *
   * @param string $email Email of the user
   * @return Zend_Db_Table_Row
   *
   */
  function getUserByEmail($email) {
    if (trim($email) == "")
      return null;

    $user = $this->select()
            ->where("email=?", $email)
            ->where("status = 1");
    $userRow = $this->fetchRow($user);
    if (is_null($userRow)) {
      return null;
    }

    return $userRow;
  }
  
  function getUserByUsername($username) {
  	$user = $this->select()
  		->where("LCASE(username)=?", strtolower($username));
  	
  	return $this->fetchRow($user);
  	 
  }

  function getUserById($id) {
    if (trim($id) == "")
      return null;

    $user = $this->select()
            ->where("id=?", $id);
    $userRow = $this->fetchRow($user);
    if (is_null($userRow)) {
      return null;
    }

    return $userRow;
  }

  /**
   * Get a user's payouts
   *
   * 
   * @return Zend_Db_Table_Row royaly scheme
   *
   */
  public function getRoyalty() {

    $userId = Zend_Auth::getInstance ()->getIdentity()->id;
    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from('users')
            ->where('users.id =  ' . $userId)
            ->join('payouts', 'users.payout_id = payouts.id');
    return $this->fetchRow($select);
  }

  /**
   * Get CP payout ratios
   * @param <type> $uid
   * @return <type> 
   */
  function getPayoutRatio($uid) {
    $payoutsModel   = new Model_Payout();
    $user           = $this->getUserById($uid);
    $payoutId       = $user->payout_id;
    $payouts        = $payoutsModel->select()->where("id=?", $payoutId);
    $payoutsRow     = $payoutsModel->fetchRow($payouts);
    return $payoutsRow;
  }

  /**
   * Get payment gateway charges
   * @param <type> $id
   * @return <type>
   */
  function getPaymentGatewayCharges($id, $price) {
    $payementGatewayModel = new Model_PaymentGateway();
    $pg = $payementGatewayModel->select()
            ->where("id=?", $id);
    $pgRow = $payementGatewayModel->fetchRow($pg);
    return floatval($price * ($pgRow->charge / 100) + $pgRow->fixed_cost);
  }
  
  
    /**
     * This method is used for authenticating open ID users
     * This checks against our current records and creates a user if it doesn't exist
     * or load the existing user
     * @param $userData Array
     */
    function doLoginOpenId($userData, $chapId = null) {
        $userModel = new Model_User();
        $userRow = $userModel->getUserByEmail($userData['email']);
        
        if (is_null($userRow)) {
          //this Open ID user doesn't exist yet, so let's create
          $chapId   = ($chapId !== null) ? (int) $chapId: null;
          $userId = $userModel->createUser(array(
                "username"      => null,
                "email"         => $userData['email'],
                "password"      => null,
                "type"          => 'USER',
                "login_type"    => 'OPENID',
                "chap_id"       => $chapId
              ));
    
          //populate user's details from FB into user_meta
          $userMeta = new Model_UserMeta();
          $userMeta->setEntityId($userId);
          $userMeta->FIRST_NAME     = $userData['first_name'];
          $userMeta->LAST_NAME      = $userData['last_name'];
        }
        
        $db = Zend_Registry::get('db');
        $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'email', 'email');
    
        $authDbAdapter->setIdentity($userData['email']);
        $authDbAdapter->setCredential($userData['email']); 
        // I'm using setCredential($email) to satisfy Zend_Auth.
    
        $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);
    
        if ($result->isValid()) {
            Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));
            return true;
        } else {
            //This shouldn't happen, if it does, I want to know why
            
            $line       = "\n\n -------------------Auth - User Creation---------------------------------- \n\n ";
            $message    = "$line Authentication Error \n";
            $message    .= implode(' <|SEPERATOR|> ', $userData) . $line;
            
            Zend_Registry::get('logger')->err($message);
            return false;
        }
    }  

  /**
   * Authenticates a facebook user.
   *
   * @param string $email
   * @return void
   */
  function doLoginFb($email, $interface = 'web', $appId = null, $secret = null, $chapId = null) {
    $db = Zend_Registry::get('db');

    $fb = new Nexva_Facebook_FacebookConnect($interface, $appId, $secret);
    if (!$fb->hasUserAuthorizedApp())
      return '/'; //this shouldn't happen

      $fbUser = $fb->getLoggedFbUser();
      

    //check if the email address exists

    $userModel = new Model_User();
    $userRow = $userModel->getUserByEmail($fbUser['email']);

    if (is_null($userRow)) {
      //this FB user doesn't exist on our records; we will create this record.
      $chapId   = ($chapId !== null) ? (int) $chapId: null;
      $userId = $userModel->createUser(array(
            "username"      => null,
            "email"         => $fbUser['email'],
            "password"      => null,
            "type"          => 'USER',
            "login_type"    => 'FACEBOOK',
            "chap_id"       => $chapId
          ));

      //populate user's details from FB into user_meta
      $userMeta = new Model_UserMeta();
      $userMeta->setEntityId($userId);
      $userMeta->FIRST_NAME = $fbUser['first_name'];
      $userMeta->LAST_NAME = $fbUser['last_name'];
      $userMeta->FACEBOOK_ID = $fbUser['id'];
    }


    $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'email', 'email');

    $authDbAdapter->setIdentity($email);
    $authDbAdapter->setCredential($email); //since FB is going the authentication, there is really no need for us to authenticate.
    // I'm using setCredential($email) to satisfy Zend_Auth.

    $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);

    if ($result->isValid()) {
      Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));

      $session = new Zend_Session_Namespace('lastRequest');
      if (isset($session->lastRequestUri)) {
        return $newpage = $session->lastRequestUri;
      }
    }
  }

    /**
     * update  user status
     *
     * @param id $userId User Id
     * @return int The number of rows updated
     *
     */
    function updateUserStatus($email) {
        return $this->update( array("status" => '1'), " email ='$email'" );
        

    }
  
    
    /**
     * delete unverified users
     *
     * 
     * @return true
     *
     */
    function deleteUnverifiedUsers() {
    	
    	$select =  $this->select()
    	           ->setIntegrityCheck(false)
                   ->from('users', array( 'id', 'email', new Zend_Db_Expr("TIMESTAMPDIFF(day, users.created_date , now()) as days")))
                   ->where(new Zend_Db_Expr("TIMESTAMPDIFF(day, users.created_date , now()) > 1"))
                   ->where("user_meta.meta_name =  'VERIFIED_ACCOUNT'")
                   ->where("user_meta.meta_value =  '0'")
                   ->joinInner('user_meta', 'user_meta.user_id = users.id', array('user_meta.meta_name','user_meta.meta_value'))
                   ->query()
                   ->fetchAll();
    
                 
       // Zend_Debug::Dump($select->assemble());
                   
        foreach ($select as $user) {
        	
            
           $this->delete('id='.$user->id);
               	
        }

       return true;
        
    }

    public function getUserList($type = 'CP') {
        return $this->fetchAll('type = "' . $type . '"');
    }
    
    /**
     * Return CPs list by order by Company Name
     *
     * 
     * @return Obj user list
     *
     */
    
    public function getCpListOrderbyCompany() {

    	return $this->fetchAll($this->select()
                   ->setIntegrityCheck(false)
                   ->from('users', array( 'id', 'email'))
                   ->joinInner('user_meta', 'user_meta.user_id = users.id', array('user_meta.meta_name','user_meta.meta_value'))
                   ->where("users.type =  'CP'")
                   ->where("user_meta.meta_name = 'COMPANY_NAME' AND user_meta.meta_value <> ''")
                   ->order("user_meta.meta_value ASC"));

    }
  
  
    /**
     * Returns the number of total users signed up during the date range
     * @param $type
     * @param $startDate
     * @param $endDate
     */
    public function getTotalNumberOfUsersByRegisterDate($type = null, $startDate = null, $endDate = null) {
        $select = $this->select(false)->from('users', array('COUNT(id) AS totalRows'));
        
        if ($type) {$select->where('type = ?', $type);}
        if ($startDate) {$select->where('created_date >= ?', $startDate);}
        if ($endDate) {$select->where('created_date <= ?', $endDate);}
        return $this->fetchRow($select)->totalRows; 
    } 


    /**
     * Simple method that returns ID=>FIELD for a given search query 
     * @param $searchMode
     * @param $query
     */
    public function getUserListByQuery($searchMode = 'COMPANY_NAME', $query, $userType = null) {
        
        $select     = $this->select(false);
        $results    = null;
        $select ->setIntegrityCheck(false);
        if ($userType) {
            $select->where('users.type = ?', $userType);
        }
        
        switch ($searchMode) {
            case 'COMPANY_NAME' :
                $select ->from('users', array('users.id AS id'))
                        ->joinLeft('user_meta', 'user_meta.user_id = users.id AND user_meta.meta_name ="COMPANY_NAME"', 'user_meta.meta_value AS value')
                        ->where('meta_value LIKE "%' . $query . '%"');
                break;
                
            case 'EMAIL' : 
                //code to query by email
                break;
                
            case 'CHAP_NAME' :
                $select ->from('users', array('users.id AS id'))
                        ->joinLeft('theme_meta', 'theme_meta.user_id = users.id AND theme_meta.meta_name ="WHITELABLE_SITE_NAME"', 'theme_meta.meta_value AS value')
                        ->where('meta_value LIKE "%' . $query . '%"');
                break;     
        }
        
        $results    = $select->query()->fetchAll();
        return $results;
    }
    
    
    /**
     * Returns the user details registered under a particular Chap
     * @param $chapId - this is user id     
     */
    
    public function getRegstrationsForChaps($chapId)
    {
      
        $userSql   = $this->select();  
        $userSql->from('users',array( 'id', 'email', 'created_date'))
                    ->where('status != ?','0')
                    ->where('chap_id = ?',$chapId)
                    ->order('created_date DESC');             
       
        
        return $this->fetchAll($userSql);

    }
    
    
    /**
     * Returns the order details of a customer
     * @param $customerId - this is user id     
     */
    
    public function getOrderDetailsByCustomer($customerId)
    {

        $orderSql   = $this->select(); 
        $orderSql->from(array('u' => 'users'), array())
                ->setIntegrityCheck(false)  
                ->join(array('o' => 'orders'), 'o.user_id = u.id', array('o.id as order_id','o.order_date','o.payment_method','o.transaction_id','o.merchant_id','o.promo_id'))
                ->join(array('od' => 'order_details'), 'od.order_id = o.id', array('od.price'))
                ->join(array('p' => 'products'), 'od.product_id = p.id', array('p.name', 'p.id as product_id'))
                ->join(array('pc' => 'promocodes'),'o.promo_id = pc.id', array('pc.description', 'pc.use_type', 'pc.use_limit', 'pc.amount', 'pc.promo_type'))
                ->where('u.type = ?','USER')
                ->where('u.id = ?',$customerId)
                ->order('o.order_date DESC');      
        
        return $this->fetchAll($orderSql);

    }
    
    /**
     * Returns the number of total users signed up during the date range
     * @param $chap
     * @param $type
     * @param $startDate
     * @param $endDate
     */
    
    public function getTotalNumberOfUsersChap($chap, $type = null, $startDate = null, $endDate = null) {
        $select = $this->select(false)->from('users', array('COUNT(id) AS total'));
        $select->where('status = 1');
        if ($chap) {$select->where('chap_id = ?', $chap);}
        if ($type) {$select->where('type = ?', $type);}
           
        if ($startDate) {$select->where('created_date >= ?', $startDate);}
        if ($endDate) {$select->where('created_date <= ?', $endDate);}
        return $this->fetchRow($select)->total; 
    } 
    
    /**
     * Returns the user count of the given email belongs to the CHAP
     * @param $email user email
     * @param $chapId CHAP Id
     */
    public function getCountByEmailOfChap($email, $chapId)
    {
        $selectSql   = $this->select(); 
        $selectSql->from($this->_name, array( "user_count" => "count(id)"))                    
                    ->where('type = ?','USER')
                    ->where('status = ?',1)  
                    ->where('email = ?', $email)
                    ->where('chap_id != ?', $chapId);
        
        $userCount = $this->fetchRow($selectSql);
        
        return $userCount->user_count;
    }
    
    /**
     * Returns Chap Id of a user
     * @param $email user email
     */
    public function getChapIdByUser($email)
    {      
        $selectSql   = $this->select();  
        $selectSql->from($this->_name, array('chap_id'))
                    ->where('type = ?','USER')
                    ->where('status = ?',1)  
                    ->where('email = ?', $email);  
       
        return $this->fetchRow($selectSql);

    }


    /**
     * @return CHAPs from the user table
     */

    /*function getChaps()
    {
        $sql = $this->select()
            ->setIntegrityCheck(false)
            ->from( 'users AS u' ,array('u.*'))
            ->where('u.type = ?', 'CHAP')
            ->where('u.status = ?', '1');
        $users = $this->fetchAll($sql)->toArray();
        return $users;
    }*/


    /**
     * @return CHAP list
     */
    public function getCHAPs()
    {
        $sql = $this->select();
        $sql    ->from($this->_name,array('users.id AS user_id','theme_meta.meta_value'))
            ->setIntegrityCheck(false)
            ->join('theme_meta','users.id = theme_meta.user_id')
            ->where('users.type =?', 'CHAP')
            ->where('theme_meta.meta_name =?','WHITELABLE_SITE_NAME')
            ->where('theme_meta.meta_value <>?','');
        return $this->fetchAll($sql);
    }

    /**
     * @param $chap
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getCHAPbyID($chap)
    {
        $sql = $this->select();
        $sql    ->from($this->_name,array('users.id AS user_id','theme_meta.meta_value'))
                    ->setIntegrityCheck(false)
                    ->join('theme_meta','users.id = theme_meta.user_id')
                    ->where('theme_meta.user_id =?',$chap)
                    ->where('users.type =?', 'CHAP')
                    ->where('theme_meta.meta_name =?','WHITELABLE_SITE_NAME')
                    ->where('theme_meta.meta_value <>?','');
        return $this->fetchRow($sql);
    }
    
        
    /**
     * Returns Language Id of a user
     * @param $userId
     */
    public function getUserLanguage($userId){
        
        $cache  = Zend_Registry::get('cache');
        $key    = 'GET_USER_LANGUAGE_'.$userId;
        if (($userLanguageId = $cache->get($key)) !== false)
        {
        	return $userLanguageId['language_id'];
        
        }
        
        $sql = $this->select()
                    ->setIntegrityCheck(false)
                    ->from( 'language_users' ,array('language_id'))
                    ->where('user_id = ?', $userId)
                    ->where('status = ?', 1);

       
        $userLanguageId = $this->fetchRow($sql);
        
        $cache->set($userLanguageId, $key, 3600);
        
        return $userLanguageId['language_id'];
    }

    public function getChapDetails($chapId){
        $sql = $this    ->select()
            ->from(array('u'=>$this->_name))
            ->setIntegrityCheck(false)
            ->where('u.id = ?',$chapId)
        ;
        return $this->fetchRow($sql);
    }

    public function getCpListMail(){
        $sql = $this    ->select()
            ->from(array('u'=>$this->_name),array('u.email'))
            ->setIntegrityCheck(false)
            ->where('u.type = ?','CP')
            ->where('u.email IS NOT NULL')
            ->order('u.id DESC')
            ->limit(10)
        ;
        //return $sql->assemble();
        return $this->fetchAll($sql);
    }

    /**
     * Delete user row by user id and type = USER
     * @param $userId
     */
    function deleteUser($userId){
        
        //$result = $this->delete(array('id = ?' => $userId, 'type = ?' => 'USER'));
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->delete($this->_name, array(
            'id = ?' => $userId,
            'type = ?' => 'USER'
        ));
    }

    /**
     * Delete user row by user id and type = USER
     * @param $userId
     */
    function deleteUserByPhoneNumber($phoneNumber){        
        $db = Zend_Db_Table::getDefaultAdapter();
        return $db->delete($this->_name, array(
            'mobile_no = ?' => $phoneNumber,
            'type = ?' => 'USER'
        ));
    }
    
  /**
   * Deactcate user.
   * @param id $userId User Id
   * @param status 0 or 1
   * @return int The number of rows updated
   */
  function changeUserStatus($userId, $status) {
    return $this->update(array("status" => $status), "id = $userId");
  }

    /**
     * @param $password
     * @param $mobileNumber
     * @return mixed
     */
    function sendXml($password,$mobileNumber){
        include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );

        $client = new nusoap_client('http://92.42.51.113:7001/MTNIranCell_Proxy', false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $msg = '<?xml version="1.0" encoding="UTF-8"?>
        <SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/">
         <SOAP-ENV:Header>
            <ns1:authentication xmlns:ns1="http://webservices.irancell.com/ProxyService">
              <ns1:user xsi:type="xsd:string">mw_pn</ns1:user>              <!--$password,$mobileNumber-->
              <ns1:password xsi:type="xsd:string">MW_pn030Mtn</ns1:password>
            </ns1:authentication>
          </SOAP-ENV:Header>
          <SOAP-ENV:Body>
            <ns2:clientRequest xmlns:ns2="http://www.openuri.org/">
              <ns3:EaiEnvelope xmlns:ns3="http://eai.mtnn.iran/Envelope">
                <ns3:Domain xsi:type="xsd:string">Portal</ns3:Domain>
                <ns3:Service xsi:type="xsd:string">Ecare</ns3:Service>
                <ns3:Language xsi:type="xsd:string">en</ns3:Language>
                <ns3:UserId xsi:type="xsd:string">abl_care</ns3:UserId>
                <ns3:Sender xsi:type="xsd:string">abl_care</ns3:Sender>
                <ns3:MessageId xsi:type="xsd:string">504016000001401131554427972005</ns3:MessageId>
                <ns3:CorrelationId xsi:type="xsd:string">504016000001401131554427972005</ns3:CorrelationId>
                <ns3:GenTimeStamp xsi:type="xsd:string">2014-02-26T09:39:51</ns3:GenTimeStamp>
                <ns3:Payload>
                  <ns3:EcareData>
                    <ns4:Request xmlns:ns4="http://eai.mtn.iran/Ecare">
                      <ns4:Operation_Name xsi:type="xsd:string">authenticateCustomer</ns4:Operation_Name>
                      <ns4:CustDetails_InputData>
                        <ns4:MSISDN xsi:type="xsd:string">'.$mobileNumber.'</ns4:MSISDN>
                        <ns4:language xsi:type="xsd:string">en</ns4:language>
                        <ns4:newPassword xsi:type="xsd:string">'.$password.'</ns4:newPassword>
                      </ns4:CustDetails_InputData>
                    </ns4:Request>
                  </ns3:EcareData>
                </ns3:Payload>
              </ns3:EaiEnvelope>
            </ns2:clientRequest>
        </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

        $result=$client->send($msg, 'http://92.42.51.113:7001/MTNIranCell_Proxy');
        return $result;
        //echo '<br/><br/>'.$client->request.'<br/><br/>';

        //echo $client->response;
        //Zend_Debug::dump($result);
        //echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        //echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';

        die();
    }
    
    public function developerSingUp($from,$to){
        $sql = $this->select();
        $sql->from($this->_name,array( "sing_up" => "count(id)"))
                ->where("created_date between '".$from."' and '".$to."'")
                ->where('type = ?','CP')
                ->where('status = ?','APPROVED');
        //Zend_Debug::dump($sql->assemble());die();
    	$userCount = $this->fetchRow($sql);
        
        return $userCount->sing_up;
        
        
    }
}
?>

