<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 6/24/14
 * Time: 4:11 PM
 * To change this template use File | Settings | File Templates.
 */
class Nexva_RemoteLogin_Ycoins
{
    public function __construct(){
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }

    public function registerAndSignIn($password, $username, $chapId, $source = null, $data = null){

        //$url = "http://stage.centili.com/softbank-api/user/password";
        $url = "http://api.yencoins.com/softbank-api/user/password";
        
        $client = new Zend_Http_Client($url);

        $apiUn = 'nexva';
        //$username = 'rooban@nexva.com';//$username = 'ro3ban@nexva.com';
        //$password = 'neXva2014';//$password = 'neXva2013';
        $signKey = 'L2Q4zXQCRlU1x49g8c1H';

        $auth = 'BASIC '.base64_encode($username.':'.$password);
        $conc = $auth.$apiUn.$signKey;
        $signature = hash_hmac('sha256', $conc, $signKey);

        $client->setHeaders(array(
            'Authorization:'.$auth,
            'ibAuth:'.$apiUn,
            'ibSignature:'.$signature
        ));

        $response = $client->request(Zend_Http_Client::GET);
        $user = $response->getRawBody();

        $headerResponse = Zend_Http_Response::fromString($client->getLastResponse());
        $headerStatus = $headerResponse->getStatus();
            
        $returnResponse = array();
        $returnMsg = '';
        
        switch($headerStatus){
            case 400:
                $returnMsg = 'Please add a valid password';
                break;
            
            case 401:
                $returnMsg = 'Please add a valid email';
                break;
        }
        
        /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99' ){
            Zend_Debug::dump($user); 
            echo htmlspecialchars($client->getLastRequest(), ENT_QUOTES).'<br><br>';
            echo htmlspecialchars($client->getLastResponse(), ENT_QUOTES).'<br><br>';
            die();
        }*/
        
        if($headerStatus == 200){
            $userModel = new Api_Model_Users();
 
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
                //$userMeta->FIRST_NAME = $firstName;
                //$userMeta->LAST_NAME = $lastName;
                $userMeta->TELEPHONE = '';
                $userMeta->UNCLAIMED_ACCOUNT = '1';

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
        
        else{
            $returnResponse = array('status' => 'fail', 'fault_string' => $returnMsg,  'user_id' => '');
            return $returnResponse;
        }
        
    }

    public function signIn($password, $username, $chapId ){
	
        $userObj = new Api_Model_Users();
        $tmpUser = $userObj->getUserByEmail($username);

        /*$sessionUser = new Zend_Session_Namespace('partner_user');
        $sessionUser->id = $tmpUser->id;
        $sessionId = Zend_Session::getId();

        $response = array(
            'user' => $tmpUser->id,
            'token' => $sessionId,
            'username' => $tmpUser->email
        );*/
		
        $userDetails = new Zend_Session_Namespace('userDetails');
        $userDetails->userId = $tmpUser->id;

        $db = Zend_Registry::get('db');

        $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'username', 'username', "status=1 AND type='USER' AND mobile_no != '' AND chap_id =". $chapId);
        $authDbAdapter->setIdentity($username);
        $authDbAdapter->setCredential($username);

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