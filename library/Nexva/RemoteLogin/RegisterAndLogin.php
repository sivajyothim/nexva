<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 6/24/14
 * Time: 4:11 PM
 * To change this template use File | Settings | File Templates.
 */
class Nexva_RemoteLogin_RegisterAndLogin
{
    public function __construct(){
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }

    public function registerAndSignIn($password, $username, $chapId, $source = null, $data = null){
 
        $userModel = new Api_Model_Users();
        $returnMsg = null;
        //Check the user exist
        $userInfo = $userModel->getUserByEmail($username);
        if($userInfo) {
            if($source == 'API'){
                $returnResponse = array('status' => 'success', 'fault_string' => $returnMsg, 'user_id' => $userInfo->id);
                return $returnResponse;
            }
            else{
                $loggedIn =  $this->signIn($password, $username, $chapId);
                $returnResponse = array('status' => ($loggedIn) ? 'success' : 'fail' , 'fault_string' => $returnMsg, 'user_id' => $userInfo->id);
                return $returnResponse;
            }
        }
        else {

            $userModel = new Api_Model_Users();
            //Generate activation code
            $activationCode = substr(md5(uniqid(rand(), true)), 5,8);

            $userData = array(
                'username' => $username,
                'email' => $username,
                'password' => $password,
                'type' => "USER",
                'login_type' => "NEXVA",
                'chap_id' => $chapId,
                'mobile_no' => '',
                'status' => 1,
                'activation_code' => $activationCode
            );

            $userId = $userModel->createUser($userData);

            $userMeta = new Model_UserMeta();
            $userMeta->setEntityId($userId);
            $userMeta->FIRST_NAME = $data['first_name'];
            $userMeta->LAST_NAME = $data['last_name'];
            $userMeta->UNCLAIMED_ACCOUNT = '1';
            
            $returnMsg = '';
            if($userId){
                if($source == 'API'){
                    $returnResponse = array('status' => 'success', 'fault_string' => $returnMsg, 'user_id' => $userId);
                    return $returnResponse;
                }
                else{
                    $loggedIn =  $this->signIn($password, $username, $chapId);
                    $returnResponse = array('status' => ($loggedIn) ? 'success' : 'fail' , 'fault_string' => $returnMsg, 'user_id' => $userId);
                    return $returnResponse;
                }
            }
            else{
                $returnResponse = array('status' => 'fail', 'fault_string' => 'Error in insert user',  'user_id' => '');
                return $returnResponse;
            }
        }
    }

    public function signIn($password, $username, $chapId ){
	
        $userObj = new Api_Model_Users();
        $tmpUser = $userObj->getUserByEmail($username);
		
		$userDetails = new Zend_Session_Namespace('userDetails');
		$userDetails->userId = $tmpUser->id;

		$db = Zend_Registry::get('db');

		$authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'email', 'password', "MD5(?) AND status=1 AND type='USER' AND chap_id =".$chapId);
		$authDbAdapter->setIdentity($username);
		$authDbAdapter->setCredential($password);

		$response = Zend_Auth::getInstance()->authenticate($authDbAdapter);

 		//If validated successfully
		if($response->isValid()) 
		{
			Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));
			return true;
		}
		else{
			return false;
		}

    }
}