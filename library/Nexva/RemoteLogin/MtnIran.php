<?php

/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 6/23/14
 * Time: 6:13 PM
 * To change this template use File | Settings | File Templates.
 */
class Nexva_RemoteLogin_MtnIran {

    public function __construct() {
        include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
    }

    /* public function registerAndSignIn($password, $mobileNumber, $chapId, $source = null, $data = null) {

      $clientAuth = new nusoap_client('http://92.42.51.122:7001/MTNIranCell_Proxy', false);
      $clientAuth->soap_defencoding = 'UTF-8';
      $clientAuth->decode_utf8 = false;

      $xmlUserAuthentication = '<soap:Envelope soap:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
      <soap:Header>
      <pr:authentication xmlns:pr="http://webservices.irancell.com/ProxyService">
      <pr:user>mw_bks</pr:user>
      <pr:password>MW_bks032Mtn</pr:password>
      </pr:authentication>
      </soap:Header>
      <soap:Body>
      <open:clientRequest xmlns:open="http://www.openuri.org/" xmlns:env="http://eai.mtnn.iran/Envelope">
      <env:EaiEnvelope>
      <env:Domain>BookStoreAPI</env:Domain>
      <env:Service>Ecare</env:Service>
      <env:Language>en</env:Language>
      <env:UserId>989339900410</env:UserId>
      <env:Sender>abl_bks</env:Sender>
      <env:MessageId>062222014</env:MessageId>
      <env:Payload>
      <ec:EcareData xmlns:ec="http://eai.mtn.iran/Ecare">
      <ec:Request>
      <ec:Operation_Name>authenticateCustomer</ec:Operation_Name>
      <ec:CustDetails_InputData>
      <ec:MSISDN>' . $mobileNumber . '</ec:MSISDN>
      <ec:newPassword>' . $password . '</ec:newPassword>
      <ec:language>En</ec:language>
      </ec:CustDetails_InputData>
      </ec:Request>
      </ec:EcareData>
      </env:Payload>
      </env:EaiEnvelope>
      </open:clientRequest>
      </soap:Body>
      </soap:Envelope>
      ';

      $result = $clientAuth->send($xmlUserAuthentication, 'http://92.42.51.122:7001/MTNIranCell_Proxy');



      $returnResponse = Array();

      //if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
      //Zend_Debug::dump($result);
      //echo '<h2>Request</h2><pre>' . htmlspecialchars($clientAuth->request, ENT_QUOTES) . '</pre>';
      // echo '<h2>Response</h2><pre>' . htmlspecialchars($clientAuth->response, ENT_QUOTES) . '</pre>';
      //echo '<h2>Debug</h2><pre>' . htmlspecialchars($clientAuth->debug_str, ENT_QUOTES) . '</pre>';

      //}

      $returnMsg = $result['EaiEnvelope']['Payload']['EcareData']['Response']['Result_OutputData']['resultMessage'];

      if ($result['EaiEnvelope']['Payload']['EcareData']['Response']['Result_OutputData']['resultCode'] == 0) {

      //$firstName = $result['EaiEnvelope']['Payload']['EcareData']['Response']['CustDetails_OutputData']['firstName'];
      //$lastName = $result['EaiEnvelope']['Payload']['EcareData']['Response']['CustDetails_OutputData']['lastName'];
      //$username = $firstName;
      //echo $username; die();
      //--------------------registration process from our side-----------------------

      $user = new Api_Model_Users();

      //Check the user exist
      $userInfo = $user->getUserByMobileNo($mobileNumber);

      if ($userInfo) {
      if ($source == 'API') {
      $returnResponse = array('status' => 'success', 'fault_string' => $returnMsg, 'user_id' => $userInfo->id);
      return $returnResponse;

      } else {

      $loggedIn = $this->signIn($password, $mobileNumber, $chapId);
      $returnResponse = array('status' => ($loggedIn) ? 'success' : 'fail', 'fault_string' => $returnMsg, 'user_id' => $userInfo->id);
      return $returnResponse;
      }
      } else {

      //Generate activation code
      $activationCode = substr(md5(uniqid(rand(), true)), 5, 8);

      $userData = array(
      'username' => null,
      'email' => $activationCode . '@nexva.com',
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
      //$userMeta->FIRST_NAME = $firstName;
      //$userMeta->LAST_NAME = $lastName;
      $userMeta->TELEPHONE = $mobileNumber;
      $userMeta->UNCLAIMED_ACCOUNT = '1';

      if ($userId) {
      if ($source == 'API') {
      $returnResponse = array('status' => 'success', 'fault_string' => $returnMsg, 'user_id' => $userId);
      return $returnResponse;
      } else {
      $loggedIn = $this->signIn($password, $mobileNumber, $chapId);
      $returnResponse = array('status' => ($loggedIn) ? 'success' : 'fail', 'fault_string' => $returnMsg, 'user_id' => $userId);
      return $returnResponse;
      }
      } else {
      $returnResponse = array('status' => 'fail', 'fault_string' => 'Error in insert user', 'user_id' => '');
      return $returnResponse;
      }
      }
      } else {

      $returnResponse = array('status' => 'fail', 'fault_string' => $returnMsg, 'user_id' => '');
      return $returnResponse;
      }

      //Final code for prepaid and login
     * $client = new nusoap_client('http://92.42.51.113:7001/MTNIranCell_Proxy', false);

      $client->soap_defencoding = 'UTF-8';
      $client->decode_utf8 = false;

      $objDateTime = new DateTime('NOW');
      $dateTime = $objDateTime->format('c');

      $xmlGetMssidn = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:open="http://www.openuri.org/" xmlns:env="http://eai.mtnn.iran/Envelope">
      <soapenv:Header>
      <pr:authentication soapenv:actor="http://schemas.xmlsoap.org/soap/actor/next" soapenv:mustUnderstand="0" xmlns:pr="http://webservices.irancell.com/ProxyService">
      <pr:user>mw_bks</pr:user>
      <pr:password>MW_bks032Mtn</pr:password>
      </pr:authentication>
      </soapenv:Header>
      <soapenv:Body>
      <ns:clientRequest xmlns:ns="http://www.openuri.org/">
      <EaiEnvelope xmlns="http://eai.mtnn.iran/Envelope" xmlns:cus="http://eai.mtn.iran/CustomerProfile">
      <Domain>abl_Portal</Domain>
      <Service>CustomerProfile</Service>
      <Sender>abl_bks</Sender>
      <MessageId>2102011B111621</MessageId>
      <Language>En</Language>
      <UserId>'.$mobileNumber.'</UserId>
      <SentTimeStamp>'.$dateTime.'</SentTimeStamp>
      <Payload>
      <cus:CustomerProfile>
      <cus:Request>
      <cus:Operation_Name>GetMSISDNInfo</cus:Operation_Name>
      <cus:CustDetails_InputData>
      <cus:MSISDN>'.$mobileNumber.'</cus:MSISDN>
      </cus:CustDetails_InputData>
      </cus:Request>
      </cus:CustomerProfile>
      </Payload>
      </EaiEnvelope>
      </ns:clientRequest>
      </soapenv:Body>
      </soapenv:Envelope>';

      $resultMsisdn = $client->send($xmlGetMssidn, 'http://92.42.51.122:7001/MTNIranCell_Proxy');

      $headerResponse = Zend_Http_Response::fromString($client->response);
      $bodyGetMsisdn = $headerResponse->getRawBody();
      $doc = new DOMDocument();
      $doc->loadXML($bodyGetMsisdn);
      $customerType = $doc->getElementsByTagName('Customer_Type')->item(0)->nodeValue;
      $resultCode = $doc->getElementsByTagName('resultCode')->item(0)->nodeValue;

      if ($customerType == 'P' && $resultCode == 0) {

      $clientAuth = new nusoap_client('http://92.42.51.113:7001/MTNIranCell_Proxy', false);
      $clientAuth->soap_defencoding = 'UTF-8';
      $clientAuth->decode_utf8 = false;

      $xmlUserAuthentication = '<soap:Envelope soap:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
      <soap:Header>
      <pr:authentication xmlns:pr="http://webservices.irancell.com/ProxyService">
      <pr:user>mw_bks</pr:user>
      <pr:password>MW_bks032Mtn</pr:password>
      </pr:authentication>
      </soap:Header>
      <soap:Body>
      <open:clientRequest xmlns:open="http://www.openuri.org/" xmlns:env="http://eai.mtnn.iran/Envelope">
      <env:EaiEnvelope>
      <env:Domain>BookStoreAPI</env:Domain>
      <env:Service>Ecare</env:Service>
      <env:Language>en</env:Language>
      <env:UserId>989339900410</env:UserId>
      <env:Sender>abl_bks</env:Sender>
      <env:MessageId>062222014</env:MessageId>
      <env:Payload>
      <ec:EcareData xmlns:ec="http://eai.mtn.iran/Ecare">
      <ec:Request>
      <ec:Operation_Name>authenticateCustomer</ec:Operation_Name>
      <ec:CustDetails_InputData>
      <ec:MSISDN>' . $mobileNumber . '</ec:MSISDN>
      <ec:newPassword>' . $password . '</ec:newPassword>
      <ec:language>En</ec:language>
      </ec:CustDetails_InputData>
      </ec:Request>
      </ec:EcareData>
      </env:Payload>
      </env:EaiEnvelope>
      </open:clientRequest>
      </soap:Body>
      </soap:Envelope>
      ';

      $result = $clientAuth->send($xmlUserAuthentication, 'http://92.42.51.122:7001/MTNIranCell_Proxy');
      $returnResponse = Array();

      //if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
      // Zend_Debug::dump($result);
      // echo '<h2>Request</h2><pre>' . htmlspecialchars($clientAuth->request, ENT_QUOTES) . '</pre>';
      // echo '<h2>Response</h2><pre>' . htmlspecialchars($clientAuth->response, ENT_QUOTES) . '</pre>';
      // echo '<h2>Debug</h2><pre>' . htmlspecialchars($clientAuth->debug_str, ENT_QUOTES) . '</pre>';

      //}

      $headerResponseLogin = Zend_Http_Response::fromString($clientAuth->response);
      $bodyGetLogin = $headerResponseLogin->getRawBody();
      $doc = new DOMDocument();
      $doc->loadXML($bodyGetLogin);
      $resultCode = $doc->getElementsByTagName('resultCode')->item(0)->nodeValue;
      $returnMsg = $doc->getElementsByTagName('resultMessage')->item(0)->nodeValue;

      //$returnMsg = $result['EaiEnvelope']['Payload']['EcareData']['Response']['Result_OutputData']['resultMessage'];

      if ($resultCode == 0) {

      //$firstName = $result['EaiEnvelope']['Payload']['EcareData']['Response']['CustDetails_OutputData']['firstName'];
      //$lastName = $result['EaiEnvelope']['Payload']['EcareData']['Response']['CustDetails_OutputData']['lastName'];
      //$username = $firstName;
      //echo $username; die();
      //--------------------registration process from our side-----------------------

      $user = new Api_Model_Users();

      //Check the user exist
      $userInfo = $user->getUserByMobileNo($mobileNumber);

      if ($userInfo) {
      if ($source == 'API') {
      $returnResponse = array('status' => 'success', 'fault_string' => $returnMsg, 'user_id' => $userInfo->id);
      return $returnResponse;

      } else {

      $loggedIn = $this->signIn($password, $mobileNumber, $chapId);
      $returnResponse = array('status' => ($loggedIn) ? 'success' : 'fail', 'fault_string' => $returnMsg, 'user_id' => $userInfo->id);
      return $returnResponse;
      }
      } else {

      //Generate activation code
      $activationCode = substr(md5(uniqid(rand(), true)), 5, 8);

      $userData = array(
      'username' => null,
      'email' => $activationCode . '@nexva.com',
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
      //$userMeta->FIRST_NAME = $firstName;
      //$userMeta->LAST_NAME = $lastName;
      $userMeta->TELEPHONE = $mobileNumber;
      $userMeta->UNCLAIMED_ACCOUNT = '1';

      if ($userId) {
      if ($source == 'API') {
      $returnResponse = array('status' => 'success', 'fault_string' => $returnMsg, 'user_id' => $userId);
      return $returnResponse;
      } else {
      $loggedIn = $this->signIn($password, $mobileNumber, $chapId);
      $returnResponse = array('status' => ($loggedIn) ? 'success' : 'fail', 'fault_string' => $returnMsg, 'user_id' => $userId);
      return $returnResponse;
      }
      } else {
      $returnResponse = array('status' => 'fail', 'fault_string' => 'Error in insert user', 'user_id' => '');
      return $returnResponse;
      }
      }
      } else {

      $returnResponse = array('status' => 'fail', 'fault_string' => $returnMsg, 'user_id' => '');
      return $returnResponse;
      }
      } else {

      if($customerType != 'P' && $resultCode == 0)
      {
      $faultMsg = 'Please enter a prepaid MSISDN' ;
      }
      else{
      $faultMsg = 'Invalid MSISDN or password' ;
      }

      $returnResponse = array('status' => 'fail', 'fault_string' => $faultMsg, 'user_id' => '');
      return $returnResponse;
      }

      }
     */

    public function registerAndSignIn($password, $mobileNumber, $chapId, $source = null, $data = null) {

        //error_reporting(E_ALL);
        //ini_set('display_errors', 1);
        //$client = new nusoap_client('http://92.42.51.113:7001/MTNIranCell_Proxy', false);
        //$client = new nusoap_client('http://92.42.51.122:7001/MTNIranCell_Proxy', false);

        $objDateTime = new DateTime('NOW');
        $dateTime = $objDateTime->format('c');

        $clientAuth = new nusoap_client('http://92.42.51.113:7001/MTNIranCell_Proxy', false);
        $clientAuth->soap_defencoding = 'UTF-8';
        $clientAuth->decode_utf8 = false;

        $xmlUserAuthentication = '<soap:Envelope soap:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
               <soap:Header>
                  <pr:authentication xmlns:pr="http://webservices.irancell.com/ProxyService">
                     <pr:user>mw_bks</pr:user>
                     <pr:password>MW_bks032Mtn</pr:password>
                  </pr:authentication>
               </soap:Header>
               <soap:Body>
                  <open:clientRequest xmlns:open="http://www.openuri.org/" xmlns:env="http://eai.mtnn.iran/Envelope">
                     <env:EaiEnvelope>
                        <env:Domain>BookStoreAPI</env:Domain>
                        <env:Service>Ecare</env:Service>
                        <env:Language>en</env:Language>
                        <env:UserId>' . $mobileNumber . '</env:UserId>
                        <env:Sender>abl_bks</env:Sender>
                        <env:MessageId>062222014</env:MessageId>
                        <env:Payload>
                           <ec:EcareData xmlns:ec="http://eai.mtn.iran/Ecare">
                              <ec:Request>
                                 <ec:Operation_Name>authenticateCustomer</ec:Operation_Name>
                                 <ec:CustDetails_InputData>
                                    <ec:MSISDN>' . $mobileNumber . '</ec:MSISDN>
                                    <ec:newPassword>' . $password . '</ec:newPassword>
                                    <ec:language>En</ec:language>
                                 </ec:CustDetails_InputData>
                              </ec:Request>
                           </ec:EcareData>
                        </env:Payload>
                     </env:EaiEnvelope>
                  </open:clientRequest>
               </soap:Body>
            </soap:Envelope>
            ';

        $result = $clientAuth->send($xmlUserAuthentication, 'http://92.42.51.122:7001/MTNIranCell_Proxy');
        $returnResponse = Array();

        //if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
        // Zend_Debug::dump($result); 
        // echo '<h2>Request</h2><pre>' . htmlspecialchars($clientAuth->request, ENT_QUOTES) . '</pre>';
        // echo '<h2>Response</h2><pre>' . htmlspecialchars($clientAuth->response, ENT_QUOTES) . '</pre>';
        // echo '<h2>Debug</h2><pre>' . htmlspecialchars($clientAuth->debug_str, ENT_QUOTES) . '</pre>';
        //}

        $headerResponseLogin = Zend_Http_Response::fromString($clientAuth->response);
        $bodyGetLogin = $headerResponseLogin->getRawBody();
        $doc = new DOMDocument();
        $doc->loadXML($bodyGetLogin);
        $resultCode = $doc->getElementsByTagName('resultCode')->item(0)->nodeValue;
        $returnMsg = $doc->getElementsByTagName('resultMessage')->item(0)->nodeValue;

        //$returnMsg = $result['EaiEnvelope']['Payload']['EcareData']['Response']['Result_OutputData']['resultMessage'];

        if ($resultCode == 0) {

            //$firstName = $result['EaiEnvelope']['Payload']['EcareData']['Response']['CustDetails_OutputData']['firstName'];
            //$lastName = $result['EaiEnvelope']['Payload']['EcareData']['Response']['CustDetails_OutputData']['lastName'];
            //$username = $firstName;
            //echo $username; die();
            //--------------------registration process from our side-----------------------

            $user = new Api_Model_Users();

            //Check the user exist
            $userInfo = $user->getUserByMobileNo($mobileNumber);

            if ($userInfo) {
                if ($source == 'API') {
                    $returnResponse = array('status' => 'success', 'fault_string' => $returnMsg, 'user_id' => $userInfo->id);
                    return $returnResponse;
                } else {

                    $loggedIn = $this->signIn($password, $mobileNumber, $chapId);
                    $returnResponse = array('status' => ($loggedIn) ? 'success' : 'fail', 'fault_string' => $returnMsg, 'user_id' => $userInfo->id);
                    return $returnResponse;
                }
            } else {

                //Generate activation code
                $activationCode = substr(md5(uniqid(rand(), true)), 5, 8);

                $userData = array(
                    'username' => null,
                    'email' => $activationCode . '@nexva.com',
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
                //$userMeta->FIRST_NAME = $firstName;
                //$userMeta->LAST_NAME = $lastName;
                $userMeta->TELEPHONE = $mobileNumber;
                $userMeta->UNCLAIMED_ACCOUNT = '1';

                if ($userId) {
                    if ($source == 'API') {
                        $returnResponse = array('status' => 'success', 'fault_string' => $returnMsg, 'user_id' => $userId);
                        return $returnResponse;
                    } else {
                        $loggedIn = $this->signIn($password, $mobileNumber, $chapId);
                        $returnResponse = array('status' => ($loggedIn) ? 'success' : 'fail', 'fault_string' => $returnMsg, 'user_id' => $userId);
                        return $returnResponse;
                    }
                } else {
                    $returnResponse = array('status' => 'fail', 'fault_string' => 'Error in insert user', 'user_id' => '');
                    return $returnResponse;
                }
            }
        } else {

            $returnResponse = array('status' => 'fail', 'fault_string' => $returnMsg, 'user_id' => '');
            return $returnResponse;
        }
    }
    
    public function getMsisdnInfo($mobileNo){
        
        $clientGetMsisdn = new nusoap_client('http://92.42.51.113:7001/MTNIranCell_Proxy', false);
        $clientGetMsisdn->soap_defencoding = 'UTF-8';
        $clientGetMsisdn->decode_utf8 = false;

        $objDateTime = new DateTime('NOW');
        $dateTime = $objDateTime->format('c');
        $responseArr = array();

        $msg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:open="http://www.openuri.org/" xmlns:env="http://eai.mtnn.iran/Envelope">
   <soapenv:Header>
      <pr:authentication soapenv:actor="http://schemas.xmlsoap.org/soap/actor/next" soapenv:mustUnderstand="0" xmlns:pr="http://webservices.irancell.com/ProxyService">
         <pr:user>mw_bks</pr:user>
         <pr:password>MW_bks032Mtn</pr:password>
      </pr:authentication>
   </soapenv:Header>
   <soapenv:Body>
      <ns:clientRequest xmlns:ns="http://www.openuri.org/">
         <EaiEnvelope xmlns="http://eai.mtnn.iran/Envelope" xmlns:cus="http://eai.mtn.iran/CustomerProfile">
            <Domain>abl_Portal</Domain>
            <Service>CustomerProfile</Service>
            <Sender>abl_bks</Sender>
            <MessageId>2102011B111621</MessageId>
            <Language>En</Language>
            <UserId>'.$mobileNo.'</UserId>
            <SentTimeStamp>'.$dateTime.'</SentTimeStamp>
            <Payload>
               <cus:CustomerProfile>
                  <cus:Request>
                     <cus:Operation_Name>GetMSISDNInfo</cus:Operation_Name>
                     <cus:CustDetails_InputData>
                        <cus:MSISDN>'.$mobileNo.'</cus:MSISDN>
                     </cus:CustDetails_InputData>
                  </cus:Request>
               </cus:CustomerProfile>
            </Payload>
         </EaiEnvelope>
      </ns:clientRequest>
   </soapenv:Body>
</soapenv:Envelope>
';

        $resultMsisdn = $clientGetMsisdn->send($msg, 'http://92.42.51.113:7001/MTNIranCell_Proxy');

        $headerResponse = Zend_Http_Response::fromString($clientGetMsisdn->response);
        $bodyGetMsisdn = $headerResponse->getRawBody();
        $doc = new DOMDocument();
        $doc->loadXML($bodyGetMsisdn);
        $customerType = $doc->getElementsByTagName('Customer_Type')->item(0)->nodeValue;
        $resultCode = $doc->getElementsByTagName('resultCode')->item(0)->nodeValue;

        /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            
            Zend_Debug::dump($resultMsisdn); 
            echo '<h2>Request</h2><pre>' . htmlspecialchars($clientGetMsisdn->request, ENT_QUOTES) . '</pre>';
	    echo '<h2>Response</h2><pre>' . htmlspecialchars($clientGetMsisdn->response, ENT_QUOTES) . '</pre>';
	    echo '<h2>Debug</h2><pre>' . htmlspecialchars($clientGetMsisdn->debug_str, ENT_QUOTES) . '</pre>';
            
            echo $customerType.'##'.$resultCode; die();
        }*/
        
        if($resultMsisdn) {
            return array('customer_type' => $customerType, 'result_code' => $resultCode); 
        }
        else{
            return array('customer_type' => 'null', 'result_code' => 'null'); 
        }

    }

    public function signIn($password, $mobileNumber, $chapId) {

        //echo $mobileNumber.'##'.$password.'##'.$chapId; die();
        $userObj = new Api_Model_Users();
        $tmpUser = $userObj->getUserByMobileNo($mobileNumber);

        $userDetails = new Zend_Session_Namespace('userDetails');
        $userDetails->userId = $tmpUser->id;

        $db = Zend_Registry::get('db');

        $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'mobile_no', 'mobile_no', "status=1 AND type='USER' AND mobile_no != '' AND chap_id =" . $chapId);
        $authDbAdapter->setIdentity($mobileNumber);
        $authDbAdapter->setCredential($mobileNumber);

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