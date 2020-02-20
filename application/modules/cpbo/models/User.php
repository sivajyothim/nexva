<?php
/**
 * @copyright   neXva.com
 * @author      Cheran@nexva.com
 * @package     cpbo
 * @version     Starter
 *
 */
class Cpbo_Model_User extends Zend_Db_Table_Abstract {
    protected $_name    = 'users';
    protected $_primary = 'id';

    function  __construct() {
        parent::__construct();
    }

    function getAll($enabled=true) {
       //return $this->fetchAll("status=1 AND type='CP'");

       return $this->fetchAll( $this->select()->from('users')->where("status=1 AND type='CP'") );
    }


    /**
     * create a new user(insert data into user table) and return the last created user id
     * @return int
     * @access public
     * @author cheran cheran@nexva.com
     *
     */
    function createUser($userData) {


    	
        $values = array(
                "id"         => NULL,
                "username"   => $userData['username'],
                "email"      => $userData['email'],
                "password"   => md5($userData['password']),
                "type"       => $userData['type'],
                "login_type" => $userData['login_type'],
                "status" => '1',
                "payout_id" => 1,
                "chap_id" => $userData['chap_id'],
                "ip" => $userData['ip']
                
        );


        $res  =   $this->insert($values);
        return Zend_Registry::get('db')->lastInsertId();
    }

    /**
     * check whether this username is already exists or not(we can create this as a seperate Zend_Valideater custom class)
     * @return mix
     * @access public
     * @author cheran cheran@nexva.com
     *
     */
    function validateUsername($username) {


        $check_username  =   $this->fetchRow($this->select()->where("username=?",$username));

        if(!is_null($check_username)) {

            $error =  "This username is already taken.Try another name.";
            return $error;
        }

        return true;
    }

    /**
     * check whether this email is  already exists or not.(we can create this as a seperate Zend_Valideater custom class)
     * @return mix
     * @access public
     * @author cheran cheran@nexva.com
     *
     *
     */
    function validateEmail($email) {

        $check_email  =   $this->fetchRow($this->select()->where("email=?",$email));

        if(!is_null($check_email)) {
            $error[]= "The email address you provided '$email' is already taken.";

        }

        $email_validater    =   new Zend_Validate_EmailAddress();
        if(!$email_validater->isValid($email)) {

            $error[]= "Email id is not valid.";
        }
        
  
        
        if(!isset($error))
            return true;
        else
            return $error;
    }


    /**
     * validate and filterd data will be returned as Zend_Filter_Input
     * @return object
     * @access public
     * @author cheran cheran@nexva.com
     *
     *
     */
    function registrationCommonCheck($formfields) {


        $filters    =   array(
                '*'        => 'StringTrim',
                'username'        => array(new Zend_Filter_StripTags()),
                'email'           => array(new Zend_Filter_StripTags()),
                'first_name'      => array(new Zend_Filter_StripTags()),
                'last_name'       => array(new Zend_Filter_StripTags()),
                'login_name'      => array(new Zend_Filter_StripTags()),
                'company_name'    => array(new Zend_Filter_StripTags()),
                'department'      => array(new Zend_Filter_StripTags()),
                'designation'     => array(new Zend_Filter_StripTags()),
                'street'          => array(new Zend_Filter_StripTags()),
                'city'           => array(new Zend_Filter_StripTags()),
                'province'        => array(new Zend_Filter_StripTags()),
                'country'         => array(new Zend_Filter_StripTags()),
                'zip'           => array(new Zend_Filter_StripTags()),
                'telephone'       => array(new Zend_Filter_StripTags()),
                'fax'           => array(new Zend_Filter_StripTags()),
                'web'           => array(new Zend_Filter_StripTags()),
                'support_email'   => array(new Zend_Filter_StripTags()),
                'support_text'    => array(new Zend_Filter_StripTags())

        );

        $validators  =  array(
                'username' => array(
                        'allowEmpty' => false,
                        'messages' => 'User name must be a valid string.'
                ),
                'password'  => array(
                        'allowEmpty'  => false,
                        'messages' =>array(
                                0 => 'Password field is required.',
                        ),
                ),
                'retypepassword' => array(
                        'allowEmpty'  => false,
                        'messages' =>'Please type the password again in "Retype password field.'
                ),
                'email'    => array(
                        'allowEmpty'   => false,

                        'messages' => array
                        (
                                1=>'Enter your email id.'
                        )
                ),

                'first_name'    => array(
                        'allowEmpty'   => false,

                        'messages' => array
                        (
                                1=>'Enter your first name'
                        )
                ),

                'last_name'    => array(
                        'allowEmpty'   => false,

                        'messages' => array
                        (
                                1=>'Enter your last name'
                        )
                ),

                'company_name'    => array(
                        'allowEmpty'   => false,

                        'messages' => array
                        (
                                1=>'Enter your company name'
                        )
                ),

                'department'    => array(
                        'allowEmpty'   => true,

                        'messages' => array
                        (
                                1=>'Enter your company name'
                        )
                ),

                'support_email'    => array(
                        'allowEmpty'   => false,

                        'messages' => array
                        (
                                1=>'Enter your support email address'
                        )
                )

        );
        return New Zend_Filter_Input($filters,$validators,$formfields);
    }


    /**
     * @return none
     * @access public
     * @author cheran cheran@nexva.com
     *
     *
     */
    function generateWelcomeMail($mail,$username) {
        
        $translate = Zend_Registry::get('Zend_Translate');
        $subject_text   =    $translate->translate("Your content provider account has been verified. Welcome to neXva.");
        $body_text      =   $translate->translate("Thank you for registering with nexva.");
        $mail_from['mail']  =  "contact@nexva.com";
        $mail_from['name']  =  'Nexva';

        $mailobj    = new Zend_Mail();
        $config     =   Zend_Registry::get('config');
        
        $session=new Zend_Session_Namespace('chap');
        $chapFrenchChapsNames = explode(',', $config->nexva->application->frenchchapsname); 

        if (in_array($session->chap->username, $chapFrenchChapsNames)) {
            $template = 'registration_success_fr.phtml';
        } else {
            $template = 'registration_success.phtml';
        }


        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->addTo($mail,$username)
                ->setSubject($subject_text)
                ->setLayout("generic_mail_template")
                ->setMailVar("name",ucfirst($username))
                ->setMailVar("cpboUrl",CP_PROJECT_BASEPATH)
                ->sendHTMLMail($template);

    }
  

    /**
     * @return mix
     * @access public
     * @author cheran cheran@nexva.com
     *
     */
    function checkPassword($password,$passwordretype) {

        if(0 != strcasecmp($password,$passwordretype) ) {
            $error[]= "The passwords you entered do not match. Please try again.";
            return $error;
        }
        return true;
    }

    function saveUserMeta($userId,$userMetaValues) {

        $this->_name = 'user_meta';
        $this->_id   = 'id';


        foreach($userMetaValues as $key=>$value) {

            $insert_val     =  array(

                    'id'        => NULL,
                    'user_id'   => $userId,
                    'meta_name' => $key,
                    'meta_value'=> $value

            );

            $this->insert($insert_val);


        }

    }

    /**
     * return all meta deta of a user
     * @return object
     * @access public
     * @author cheran cheran@nexva.com
     */
    function getUserDetails($userID) {

        $row    =   $this->find($userID);
        return $row;

    }

    /**
     * Find a user by email address.
     *
     * @param string $email
     * @return Zend_Db_Table_Row_Abstract || NULL
     */
    public function getUserDetailsByEmail($email) {
        return $this->fetchRow("email = '$email'");
    }

    /**
     * Send CP password reset email
     *
     * @param string $email
     * @return boolean
     */
    public function sendPasswordResetMail($email, $userType = 'CP')
    {
    	
        $user = new Cpbo_Model_User();
        switch ($userType) {
            case 'CP':
                $userRow = $this->fetchRow($user->select()->where("email = ?", $email));
                break;
            case 'CHAP':
                $userRow = $this->fetchRow($user->select()->where("email = ?", $email)->where("type = ?", $userType));
                break;
            case 'RCA':
                $userRow = $this->fetchRow($user->select()->where("email = ?", $email));
                break;
            default:
                throw new Zend_Exception("Unknown operation attempted: ");
        }
        
 

        if(is_null($userRow) ){

            throw new Zend_Exception("The email you supplied was not found.");
            
        }

        else // generate and email password
        {

            $config = Zend_Registry::get('config');

            $timeout =  strtotime("+1 hour"); //1 hour until the link expires

            $otp = new Nexva_Util_OTP_OneTimePassword($userRow->id, $userRow->password);

            $otp->setSalt($config->nexva->application->salt);
            $otp->setTimeout($timeout);

            $otphash = $otp->generateOTPHash();

            $session =new Zend_Session_Namespace('chap');
            $chapFrenchChapsNames = explode(',', $config->nexva->application->frenchchapsname);          


            if ($userType == 'RCA') {
                
                if (in_array($session->chap->username, $chapFrenchChapsNames)) {
                    $template = 'forgotpassword-rca_fr.phtml';
                } else {
                    $template = 'forgotpassword-rca.phtml';
                }

                $link = "http://" . $_SERVER['HTTP_HOST'] . "/info/resetpassword/id/" . $userRow->id . "/time/$timeout/otphash/$otphash";

                $mailer = new Nexva_Util_Mailer_Mailer();
                $mailer->addTo($userRow->email, $userRow->email)
                        ->setSubject('Reset your neXva account password.')
                        ->setLayout("generic_mail_template")
                        ->setMailVar("name", $userRow->email) //@todo: fetch first name/last name from user_meta once they are available.
                        ->setMailVar("resetlink", $link)
                        ->sendHTMLMail($template);
            } else {
                
                if (in_array($session->chap->username, $chapFrenchChapsNames)) {
                    $template = 'forgotpassword_fr.phtml';
                } else {
                    $template = 'forgotpassword.phtml';
                }

                $link = "http://" . $_SERVER['HTTP_HOST'] . "/user/resetpassword/id/" . $userRow->id . "/time/$timeout/otphash/$otphash";

                $mailer = new Nexva_Util_Mailer_Mailer();
                $mailer->addTo($userRow->email, $userRow->email)
                        ->setSubject('Reset your neXva account password.')
                        ->setLayout("generic_mail_template")
                        ->setMailVar("name", $userRow->email) //@todo: fetch first name/last name from user_meta once they are available.
                        ->setMailVar("resetlink", $link)
                        ->sendHTMLMail($template);
            }
        }
      
        
    }

    /**
     * Resets a CP's password.
     *
     * @param id $userId User Id
     * @param string $newPassword The new password
     * @return int The number of rows updated
     *
     */
    function resetPassword($userId, $newPassword) {
        return $this->update( array("password" => md5($newPassword)), "id = $userId" );
    }

    /**
     * Sends an alert to the site admin(s) about a new CP signup
     *
     * @param int $id
     * @return null | void
     */
    function sendCPSignupAlert($id) {

        $user = new Cpbo_Model_User();
        $userRow = $this->fetchRow( $user->select()->where("id = ?",$id)  );

        if( is_null($userRow) ) return null;

        $session =new Zend_Session_Namespace('chap');
        $config = Zend_Registry::get('config');
        $chapFrenchChapsNames = explode(',', $config->nexva->application->frenchchapsname); 

        if (in_array($session->chap->username, $chapFrenchChapsNames)) {
            $template = 'cp_registered_alert_fr.phtml';
        } else {
            $template = 'cp_registered_alert.phtml';
        }


        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->addTo(Zend_Registry::get('config')->nexva->application->content_admin->contact)        
        ->setSubject('New CP Signed Up')
        ->setLayout("generic_mail_template")
        ->setMailVar("email",$userRow->email)
        ->setMailVar("id",$userRow->id)      
        ->sendHTMLMail($template);
    }

    /**
     * Generates a unique account ID
     *
     * Algorithm:
     *
     *   $accountId = strtoupper(md5($id + application_salt + current_timestamp))
     *   The output will be formatted in blocks of 4 for readability
     *
     * @param int $id The user (CP) Id
     * @return string
     */
    public function generateAccountId($id) {
        
        $temp = strtoupper(md5($id. Zend_Registry::get('config')->nexva->application->salt. time()));

        $accountId = "";
        for($x = 0; $x<strlen($temp); $x++ ) {
            if( $x>0 && ($x %4)==0 ) $accountId .= "-".$temp[$x]; else $accountId .=  $temp[$x];
        }

        return $accountId;
    }



    function getChannelDetails($productId)
    {
        $sql =      $this->select();
        $sql        ->from(array('u'=>$this->_name),array('u.username','pa.cp_payout_description','tm.meta_value AS meta_value','u.id'))
                    ->setIntegrityCheck(false)
                    ->join(array('pa'=>'payouts'),'u.payout_id = pa.id',array())
                    ->join(array('tm'=>'theme_meta'),'u.id = tm.user_id',array())
                    ->joinLeft(array('pc'=>'product_channel'),"u.id = pc.chap_id AND pc.product_id = $productId",array('pc.id AS pcId'))
                    ->where('u.payout_id != ?', 'NULL')
                    ->where('u.type = ?', 'CHAP')
                    ->where('tm.meta_name = ?','WHITELABLE_URL_WEB')
                    ;
                    
                    
                    

            
                 
        $result = $this->fetchAll($sql);
        return $result;

    }

    function getAllChannelDetails()
    {
        $sql =      $this->select();
        $sql        ->from(array('u'=>$this->_name),array('u.username','pa.cp_payout_description','tm.meta_value AS meta_value','u.id','u.nexpayer_support'))
                    ->setIntegrityCheck(false)
                    ->join(array('pa'=>'payouts'),'u.payout_id = pa.id',array())
                    ->join(array('tm'=>'theme_meta'),'u.id = tm.user_id',array())
                    ->where('u.payout_id != ?', 'NULL')
                    ->where('u.type = ?', 'CHAP')
                    //->where('u.nexpayer_support = ?', '1')
                    ->where('tm.meta_name = ?','WHITELABLE_URL_WEB')
                    ;
        $result = $this->fetchAll($sql);
        return $result;

    }

    function getUserAgreementConfirm($userId){
        $sql =      $this->select();
        $sql    ->from(array('u'=>$this->_name))
                ->where('u.id = ?',$userId)
                ->where('u.agreement_sign = ?',1)
                ;
        return $this->fetchRow($sql);
    }

    /*function updateAgreementConfirmation($userId){
        //$sql =      $this->select();
        //$sql->from(array('u'=>$this->_name))
    }*/
    
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
                            "password" => null,
                            "type" => 'CP', 
                            "login_type" => 'OPENID',
                            "chap_id" => $chapId));
            
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
?>