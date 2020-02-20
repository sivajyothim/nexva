<?php

/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 6/24/14
 * Time: 4:11 PM
 * To change this template use File | Settings | File Templates.
 */
class Nexva_RemoteLogin_Qelasy {

    public function __construct() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }

    public function registerAndSignIn($password, $username, $chapId, $type = null, $data = null) {
        
        $uri = 'http://silk-outsourcing.com/qelasysecurity/web/index.php/api/default/appStoreAuthentication';

        $config = array(
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => array(CURLOPT_FOLLOWLOCATION => true),
        );

        $client = new Zend_Http_Client($uri, $config);

        $client->setHeaders(array(
            'X-USERNAME:non-qelasy-stg',
            'X-PASSWORD:non-qelasy-stg',
            'Content-Type:application/x-www-form-urlencoded'
        ));

        if ($type == 1) {
            $data = 'email=' . $username . '&pin=' . $password . '&type=1';
        } else {
            $data = 'studentId=' . $username . '&pin=' . $password . '&type=0';
        }

        $response = $client->setRawData($data, 'application/x-www-form-urlencoded')->request('POST');

        /*echo htmlspecialchars($client->getLastRequest(), ENT_QUOTES).'<br><br>';
          echo '--------------------------------------------------------<br><br>';
          echo htmlspecialchars($client->getLastResponse(), ENT_QUOTES).'<br><br>';
          echo '---------------------------------------------------------<br><br>'; */

         
        $results = $response->getBody();
 
        $resultArray = Zend_Json::decode($results);
        
        $studentGrade = new Zend_Session_Namespace('studentGrade');
        
        if(isset($results['data']['grade'])){
            $studentGrade->gradeId = $results['data']['grade'];
        }
        else{
            $studentGrade->gradeId = null;
        }
        
        //print_r($resultArray); die();
        
        $returnResponse = array();
        $returnMsg = (isset($resultArray['message'])) ? $resultArray['message'] : '';
        
        if ($resultArray['status'] && $resultArray['code'] == 200) {

            //--------------------registration process from our side-----------------------

            $user = new Api_Model_Users();

            //Check the user exist
            $userInfo = $user->getUserByEmail($username);
            if($userInfo) {
                //return $this->signIn($password, $username, $chapId);
                $loggedIn =  $this->signIn($password, $username, $chapId);
                $returnResponse = array('status' => ($loggedIn) ? 'success' : 'fail' , 'fault_string' => $returnMsg);
                return $returnResponse;
                        
            }
            else {
                //Generate activation code
                $activationCode = substr(md5(uniqid(rand(), true)), 5, 8);
                $mobileNumber = '';
                $firstName = $resultArray['data']['firstName'];
                $lastName = $resultArray['data']['lastName'];
                $userData = array(
                    'username' => $username,
                    //'email' => ($type == 1) ? $username : $username.'@qelasy.com',
                    'email' => $username,
                    'password' => $password,
                    'type' => "USER",
                    'login_type' => "NEXVA",
                    'chap_id' => $chapId,
                    'mobile_no' => $mobileNumber,
                    'status' => 1,
                    'activation_code' => $activationCode
                );

                $userId = $user->createUser($userData);

                $userMeta = new Model_UserMeta();
                $userMeta->setEntityId($userId);
                $userMeta->FIRST_NAME = $firstName;
                $userMeta->LAST_NAME = $lastName;
                $userMeta->TELEPHONE = $mobileNumber;
                $userMeta->UNCLAIMED_ACCOUNT = '1';

                //return $this->signIn($password, $username, $chapId);
                
                $loggedIn =  $this->signIn($password, $username, $chapId);
                $returnResponse = array('status' => ($loggedIn) ? 'success' : 'fail' , 'fault_string' => $returnMsg);
                return $returnResponse;
            }
        } else {
            $returnResponse = array('status' => 'fail', 'fault_string' => $returnMsg);
            return $returnResponse;
        }

        exit();
    }

    public function signIn($password, $username, $chapId) {

        $userObj = new Api_Model_Users();
        $tmpUser = $userObj->getUserByEmail($username);

        /* $sessionUser = new Zend_Session_Namespace('partner_user');
          $sessionUser->id = $tmpUser->id;
          $sessionId = Zend_Session::getId();

          $response = array(
          'user' => $tmpUser->id,
          'token' => $sessionId,
          'username' => $tmpUser->email
          ); */

        $userDetails = new Zend_Session_Namespace('userDetails');
        $userDetails->userId = $tmpUser->id;

        $db = Zend_Registry::get('db');

        $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'username', 'username', "status=1 AND type='USER' AND mobile_no != '' AND chap_id =". $chapId);
        $authDbAdapter->setIdentity($username);
        $authDbAdapter->setCredential($username);

        $response = Zend_Auth::getInstance()->authenticate($authDbAdapter);

        //If validated successfully
        if ($response->isValid()) {
            Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));
            return true;
        } else {
            return false;
        }
    }

}